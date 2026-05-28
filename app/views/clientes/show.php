<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">

        <?php
        $pageTitle  = e($cliente['nome']);
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Clientes',  'url' => '/clientes'],
            ['label' => e($cliente['nome'])],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <?php if ($f = \App\Core\Session::getFlash('success')): ?>
                <div class="zf-alert zf-alert-success" data-auto-close>
                    <i class="ti ti-circle-check"></i> <?= e($f) ?>
                </div>
            <?php endif; ?>

            <div style="display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start;">

                <!-- Coluna principal -->
                <div style="display:flex; flex-direction:column; gap:20px;">

                    <!-- Cabeçalho do perfil -->
                    <div class="zf-table-card" style="padding:24px;">
                        <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">

                            <!-- Avatar inicial -->
                            <div style="width:64px; height:64px; border-radius:50%;
                                        background:var(--primary); color:#fff;
                                        display:flex; align-items:center; justify-content:center;
                                        font-size:26px; font-weight:700; flex-shrink:0;">
                                <?= mb_strtoupper(mb_substr($cliente['nome'], 0, 1)) ?>
                            </div>

                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                    <h2 style="margin:0; font-size:20px; font-weight:700;">
                                        <?= e($cliente['nome']) ?>
                                    </h2>
                                    <span class="badge badge-neutral" style="font-size:11px;">
                                        <?= $cliente['tipo_pessoa'] === 'juridica' ? 'PJ' : 'PF' ?>
                                    </span>
                                    <?php if ($cliente['ativo']): ?>
                                        <span class="badge badge-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inativo</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($cliente['nome_fantasia'])): ?>
                                    <div style="font-size:13px; color:var(--text-secondary); margin-top:2px;">
                                        <?= e($cliente['nome_fantasia']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($cliente['cpf_cnpj'])): ?>
                                    <div style="font-size:13px; color:var(--text-secondary); margin-top:2px;">
                                        <?= e(formatarCpfCnpj($cliente['cpf_cnpj'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div style="display:flex; gap:8px;">
                                <a href="/clientes/<?= (int)$cliente['id'] ?>/editar" class="btn btn-primary btn-sm">
                                    <i class="ti ti-pencil"></i> Editar
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- KPIs -->
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">

                        <div class="zf-table-card" style="padding:18px 20px; text-align:center;">
                            <div style="font-size:26px; font-weight:700; color:var(--primary);">
                                <?= (int)$cliente['total_compras'] ?>
                            </div>
                            <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">
                                <i class="ti ti-shopping-bag"></i> Compras realizadas
                            </div>
                        </div>

                        <div class="zf-table-card" style="padding:18px 20px; text-align:center;">
                            <div style="font-size:22px; font-weight:700; color:var(--green);">
                                R$ <?= number_format($cliente['total_gasto'], 2, ',', '.') ?>
                            </div>
                            <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">
                                <i class="ti ti-coins"></i> Total gasto
                            </div>
                        </div>

                        <div class="zf-table-card" style="padding:18px 20px; text-align:center;">
                            <div style="font-size:22px; font-weight:700;">
                                R$ <?= number_format($cliente['ticket_medio'] ?? 0, 2, ',', '.') ?>
                            </div>
                            <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">
                                <i class="ti ti-chart-bar"></i> Ticket médio
                            </div>
                        </div>

                    </div>

                    <!-- Últimas vendas -->
                    <div class="zf-table-card">
                        <div style="display:flex; justify-content:space-between; align-items:center;
                                    padding:14px 20px; border-bottom:1px solid var(--border);">
                            <span style="font-weight:600; font-size:14px;">
                                <i class="ti ti-history"></i> Últimas Compras
                            </span>
                        </div>
                        <table class="zf-table">
                            <thead>
                                <tr>
                                    <th style="width:80px"># Venda</th>
                                    <th style="width:160px">Data</th>
                                    <th>Operador</th>
                                    <th>Produtos</th>
                                    <th style="width:100px">Status</th>
                                    <th style="width:120px; text-align:right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($vendas)): ?>
                                    <tr>
                                        <td colspan="5" class="td-empty">
                                            <i class="ti ti-receipt-off" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3"></i>
                                            Nenhuma compra registrada.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($vendas as $v): ?>
                                        <tr>
                                            <td class="text-sm">
                                                <a href="/vendas/<?= (int)$v['id'] ?>"
                                                    style="font-weight:600; color:var(--primary); text-decoration:none;">
                                                    #<?= str_pad($v['numero'], 6, '0', STR_PAD_LEFT) ?>
                                                </a>
                                            </td>
                                            <td class="text-sm text-muted">
                                                <?= date('d/m/Y H:i', strtotime($v['criado_em'])) ?>
                                            </td>
                                            <td class="text-sm"><?= e($v['operador'] ?? '—') ?></td>

                                            <td class="text-sm" style="max-width: 200px; white-space: normal;">
                                                <?php if (!empty($v['itens'])): ?>
                                                    <ul style="margin: 0; padding-left: 15px; font-size: 12px; color: #666;">
                                                        <?php foreach ($v['itens'] as $item): ?>
                                                            <li><?= e($item['produto_nome']) ?> (<?= (float)$item['quantidade'] ?>)</li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <span style="color:#ccc;">—</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?php $s = $v['status']; ?>
                                                <span class="badge badge-<?= $s === 'finalizada' ? 'success' : ($s === 'cancelada' ? 'danger' : 'neutral') ?>">
                                                    <?= ucfirst($s) ?>
                                                </span>
                                            </td>
                                            <td style="text-align:right; font-weight:600; font-size:13px;">
                                                R$ <?= number_format($v['total'], 2, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div><!-- /col principal -->

                <!-- Coluna lateral -->
                <div style="display:flex; flex-direction:column; gap:20px;">

                    <!-- Contato -->
                    <div class="zf-table-card" style="padding:20px 24px;">
                        <h3 class="card-section-title">
                            <i class="ti ti-phone"></i> Contato
                        </h3>
                        <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">

                            <?php if (!empty($cliente['celular'])): ?>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <i class="ti ti-device-mobile" style="color:var(--primary); width:16px;"></i>
                                    <span><?= e($cliente['celular']) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($cliente['telefone'])): ?>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <i class="ti ti-phone" style="color:var(--text-secondary); width:16px;"></i>
                                    <span><?= e($cliente['telefone']) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($cliente['email'])): ?>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <i class="ti ti-mail" style="color:var(--text-secondary); width:16px;"></i>
                                    <a href="mailto:<?= e($cliente['email']) ?>" style="color:var(--primary);">
                                        <?= e($cliente['email']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($cliente['contato'])): ?>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <i class="ti ti-user-circle" style="color:var(--text-secondary); width:16px;"></i>
                                    <span><?= e($cliente['contato']) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (empty($cliente['celular']) && empty($cliente['telefone']) && empty($cliente['email'])): ?>
                                <span class="text-muted" style="font-size:12px;">Nenhum contato cadastrado.</span>
                            <?php endif; ?>

                        </div>
                    </div>

                    <!-- Endereço -->
                    <?php if (!empty($cliente['logradouro']) || !empty($cliente['cidade'])): ?>
                        <div class="zf-table-card" style="padding:20px 24px;">
                            <h3 class="card-section-title">
                                <i class="ti ti-map-pin"></i> Endereço
                            </h3>
                            <address style="font-style:normal; font-size:13px; line-height:1.7; color:var(--text);">
                                <?php if (!empty($cliente['logradouro'])): ?>
                                    <?= e($cliente['logradouro']) ?>
                                    <?= !empty($cliente['numero']) ? ', ' . e($cliente['numero']) : '' ?>
                                    <?= !empty($cliente['complemento']) ? ' — ' . e($cliente['complemento']) : '' ?><br>
                                <?php endif; ?>
                                <?php if (!empty($cliente['bairro'])): ?>
                                    <?= e($cliente['bairro']) ?><br>
                                <?php endif; ?>
                                <?php if (!empty($cliente['cidade'])): ?>
                                    <?= e($cliente['cidade']) ?><?= !empty($cliente['uf']) ? '/' . e($cliente['uf']) : '' ?><br>
                                <?php endif; ?>
                                <?php if (!empty($cliente['cep'])): ?>
                                    CEP: <?= e($cliente['cep']) ?>
                                <?php endif; ?>
                            </address>
                        </div>
                    <?php endif; ?>

                    <!-- Informações fiscais -->
                    <?php if (!empty($cliente['ie']) || !empty($cliente['data_nascimento'])): ?>
                        <div class="zf-table-card" style="padding:20px 24px;">
                            <h3 class="card-section-title">
                                <i class="ti ti-file-invoice"></i> Dados Fiscais
                            </h3>
                            <div style="font-size:13px; display:flex; flex-direction:column; gap:8px;">
                                <?php if (!empty($cliente['ie'])): ?>
                                    <div>
                                        <span style="color:var(--text-secondary);">IE:</span>
                                        <?= e($cliente['ie']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($cliente['data_nascimento'])): ?>
                                    <div>
                                        <span style="color:var(--text-secondary);">Nascimento:</span>
                                        <?= date('d/m/Y', strtotime($cliente['data_nascimento'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Crédito -->
                    <div class="zf-table-card" style="padding:20px 24px;">
                        <h3 class="card-section-title">
                            <i class="ti ti-credit-card"></i> Crédito
                        </h3>
                        <div style="font-size:24px; font-weight:700; color:var(--primary);">
                            <?php if (($cliente['limite_credito'] ?? 0) > 0): ?>
                                R$ <?= number_format($cliente['limite_credito'], 2, ',', '.') ?>
                            <?php else: ?>
                                <span style="font-size:14px; color:var(--text-secondary); font-weight:400;">
                                    Sem limite definido
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($cliente['ultima_compra'])): ?>
                            <div style="font-size:12px; color:var(--text-secondary); margin-top:8px;">
                                Última compra: <?= date('d/m/Y', strtotime($cliente['ultima_compra'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Observações -->
                    <?php if (!empty($cliente['observacoes'])): ?>
                        <div class="zf-table-card" style="padding:20px 24px;">
                            <h3 class="card-section-title">
                                <i class="ti ti-notes"></i> Observações
                            </h3>
                            <p style="font-size:13px; color:var(--text); margin:0; line-height:1.6;">
                                <?= nl2br(e($cliente['observacoes'])) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Metadados -->
                    <div class="zf-table-card" style="padding:16px 20px;">
                        <div style="font-size:11px; color:var(--text-secondary); line-height:1.8;">
                            <div>Cadastrado em: <?= date('d/m/Y H:i', strtotime($cliente['criado_em'])) ?></div>
                            <?php if (!empty($cliente['atualizado_em'])): ?>
                                <div>Atualizado em: <?= date('d/m/Y H:i', strtotime($cliente['atualizado_em'])) ?></div>
                            <?php endif; ?>
                            <div>ID: #<?= (int)$cliente['id'] ?></div>
                        </div>
                    </div>

                </div><!-- /col lateral -->

            </div><!-- /grid -->

        </div><!-- /zf-content -->
    </div><!-- /zf-main -->
</div><!-- /zf-layout -->

<?php require VIEW_PATH . '/layouts/footer.php'; ?>