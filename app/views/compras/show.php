<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">

    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>

    <div class="zf-main">

        <?php require VIEW_PATH . '/layouts/navbar.php'; ?>

        <div class="zf-content">

            <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
                <div class="zf-alert zf-alert-success" data-auto-close>
                    <i class="ti ti-circle-check"></i> <?= e($msg) ?>
                </div>
            <?php endif; ?>
            <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close>
                    <i class="ti ti-alert-circle"></i> <?= e($msg) ?>
                </div>
            <?php endif; ?>

            <?php
            $badge = match ($compra['status']) {
                'confirmada' => ['color' => '#22c55e', 'bg' => '#f0fdf4', 'label' => 'Confirmada'],
                'cancelada'  => ['color' => '#ef4444', 'bg' => '#fef2f2', 'label' => 'Cancelada'],
                default      => ['color' => '#f59e0b', 'bg' => '#fffbeb', 'label' => 'Rascunho'],
            };
            $isRascunho   = $compra['status'] === 'rascunho';
            $isConfirmada = $compra['status'] === 'confirmada';
            ?>

            <!-- Cabeçalho -->
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
                <a href="/compras" style="color:var(--text-tertiary); display:flex; align-items:center;">
                    <i class="ti ti-arrow-left" style="font-size:20px;"></i>
                </a>
                <div style="flex:1;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <h1 style="font-size:20px; font-weight:700; margin:0;"><?= e($compra['numero']) ?></h1>
                        <span style="
                            background:<?= $badge['bg'] ?>;
                            color:<?= $badge['color'] ?>;
                            padding:3px 12px;
                            border-radius:20px;
                            font-size:12px;
                            font-weight:600;
                        "><?= $badge['label'] ?></span>
                        <?php if ($compra['numero_nf']): ?>
                            <span style="font-size:12px; color:var(--text-tertiary);">NF <?= e($compra['numero_nf']) ?><?= $compra['serie_nf'] ? ' / Série ' . e($compra['serie_nf']) : '' ?></span>
                        <?php endif; ?>
                    </div>
                    <span style="font-size:13px; color:var(--text-tertiary);">
                        Criada por <?= e($compra['usuario_nome']) ?> em <?= date('d/m/Y', strtotime($compra['criado_em'])) ?>
                    </span>
                </div>

                <!-- Ações principais -->
                <div style="display:flex; gap:10px;">
                    <?php if ($isRascunho): ?>
                        <a href="/compras/<?= $compra['id'] ?>/editar" class="btn btn-outline">
                            <i class="ti ti-pencil"></i> Editar
                        </a>
                        <form method="POST" action="/compras/<?= $compra['id'] ?>/confirmar" style="display:inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary"
                                onclick="return confirm('Confirmar esta compra? O estoque será atualizado automaticamente.')">
                                <i class="ti ti-circle-check"></i> Confirmar Compra
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if (!($compra['status'] === 'cancelada')): ?>
                        <button type="button" class="btn btn-outline" id="btn-cancelar"
                            style="color:var(--color-danger,#ef4444);">
                            <i class="ti ti-ban"></i> Cancelar
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 360px; gap:20px; align-items:start;">

                <!-- Coluna principal -->
                <div style="display:flex; flex-direction:column; gap:20px;">

                    <!-- Itens -->
                    <div class="zf-table-card">
                        <div style="padding:20px 24px 16px; border-bottom:1px solid var(--border-color);">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <h2 style="font-size:14px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--text-tertiary); margin:0;">
                                    Itens da Compra
                                </h2>
                                <span style="font-size:13px; color:var(--text-tertiary);">
                                    <?= count($itens) ?> <?= count($itens) === 1 ? 'item' : 'itens' ?>
                                </span>
                            </div>
                        </div>

                        <table class="zf-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Produto</th>
                                    <th style="text-align:center;">Qtd</th>
                                    <th style="text-align:right;">Preço Unit.</th>
                                    <th style="text-align:right;">Desconto</th>
                                    <th style="text-align:right;">Subtotal</th>
                                    <?php if ($isConfirmada): ?>
                                        <th style="width:80px; text-align:center;">Estoque</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($itens)): ?>
                                    <tr>
                                        <td colspan="<?= $isConfirmada ? 7 : 6 ?>" style="text-align:center; padding:40px; color:var(--text-tertiary);">
                                            <i class="ti ti-package-off" style="font-size:28px; display:block; margin-bottom:8px;"></i>
                                            Nenhum item nesta compra.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($itens as $i => $item): ?>
                                        <tr>
                                            <td style="color:var(--text-tertiary); font-size:12px;"><?= $i + 1 ?></td>
                                            <td>
                                                <div style="font-weight:500;"><?= e($item['produto_nome']) ?></div>
                                                <?php if ($item['produto_codigo']): ?>
                                                    <div style="font-size:11px; color:var(--text-tertiary);"><?= e($item['produto_codigo']) ?> · <?= e($item['unidade_sigla']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align:center;">
                                                <?= number_format($item['quantidade'], 3, ',', '.') ?>
                                            </td>
                                            <td style="text-align:right;">
                                                R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?>
                                            </td>
                                            <td style="text-align:right; color:var(--color-danger,#ef4444);">
                                                <?= $item['desconto_item'] > 0 ? '- R$ ' . number_format($item['desconto_item'], 2, ',', '.') : '—' ?>
                                            </td>
                                            <td style="text-align:right; font-weight:600;">
                                                R$ <?= number_format($item['subtotal'], 2, ',', '.') ?>
                                            </td>
                                            <?php if ($isConfirmada): ?>
                                                <td style="text-align:center;">
                                                    <?php if ($item['estoque_atualizado']): ?>
                                                        <span title="Estoque atualizado" style="color:#22c55e;">
                                                            <i class="ti ti-check"></i>
                                                        </span>
                                                    <?php else: ?>
                                                        <span title="Não atualizado" style="color:var(--text-tertiary);">
                                                            <i class="ti ti-minus"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Totais da tabela -->
                        <?php if (!empty($itens)): ?>
                            <div style="padding:16px 24px; border-top:1px solid var(--border-color); display:flex; justify-content:flex-end;">
                                <div style="display:flex; flex-direction:column; gap:6px; min-width:260px;">
                                    <div style="display:flex; justify-content:space-between; font-size:13px; color:var(--text-tertiary);">
                                        <span>Subtotal itens</span>
                                        <span>R$ <?= number_format($compra['subtotal'], 2, ',', '.') ?></span>
                                    </div>
                                    <?php if ($compra['frete'] > 0): ?>
                                        <div style="display:flex; justify-content:space-between; font-size:13px; color:var(--text-tertiary);">
                                            <span>Frete</span>
                                            <span>+ R$ <?= number_format($compra['frete'], 2, ',', '.') ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($compra['desconto_valor'] > 0): ?>
                                        <div style="display:flex; justify-content:space-between; font-size:13px; color:var(--color-danger,#ef4444);">
                                            <span>Desconto</span>
                                            <span>- R$ <?= number_format($compra['desconto_valor'], 2, ',', '.') ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div style="display:flex; justify-content:space-between; font-size:16px; font-weight:700; border-top:1px solid var(--border-color); padding-top:8px; margin-top:2px;">
                                        <span>Total</span>
                                        <span style="color:var(--color-primary);">R$ <?= number_format($compra['total'], 2, ',', '.') ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Observação -->
                    <?php if ($compra['observacao']): ?>
                        <div class="zf-table-card" style="padding:24px;">
                            <h2 style="font-size:14px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--text-tertiary); margin:0 0 12px;">
                                Observação
                            </h2>
                            <p style="margin:0; font-size:14px; line-height:1.6; color:var(--text-secondary);">
                                <?= nl2br(e($compra['observacao'])) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Motivo cancelamento -->
                    <?php if ($compra['status'] === 'cancelada' && !empty($compra['motivo_cancelamento'])): ?>
                        <div class="zf-table-card" style="padding:24px; border-left:3px solid var(--color-danger,#ef4444);">
                            <h2 style="font-size:14px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--color-danger,#ef4444); margin:0 0 8px;">
                                Motivo do Cancelamento
                            </h2>
                            <p style="margin:0; font-size:14px; line-height:1.6; color:var(--text-secondary);">
                                <?= nl2br(e($compra['motivo_cancelamento'])) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- Coluna lateral -->
                <div style="display:flex; flex-direction:column; gap:20px; position:sticky; top:16px;">

                    <!-- Fornecedor -->
                    <div class="zf-table-card" style="padding:24px;">
                        <h2 style="font-size:14px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--text-tertiary); margin:0 0 16px;">
                            Fornecedor
                        </h2>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <div style="font-weight:600; font-size:15px;"><?= e($compra['fornecedor_nome']) ?></div>
                            <?php if ($compra['fornecedor_fantasia']): ?>
                                <div style="font-size:13px; color:var(--text-tertiary);"><?= e($compra['fornecedor_fantasia']) ?></div>
                            <?php endif; ?>
                            <?php if ($compra['fornecedor_cnpj']): ?>
                                <div style="display:flex; align-items:center; gap:6px; font-size:13px; color:var(--text-secondary);">
                                    <i class="ti ti-id-badge" style="color:var(--text-tertiary);"></i>
                                    <?= e($compra['fornecedor_cnpj']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($compra['fornecedor_telefone']): ?>
                                <div style="display:flex; align-items:center; gap:6px; font-size:13px; color:var(--text-secondary);">
                                    <i class="ti ti-phone" style="color:var(--text-tertiary);"></i>
                                    <?= e($compra['fornecedor_telefone']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($compra['fornecedor_email']): ?>
                                <div style="display:flex; align-items:center; gap:6px; font-size:13px; color:var(--text-secondary);">
                                    <i class="ti ti-mail" style="color:var(--text-tertiary);"></i>
                                    <?= e($compra['fornecedor_email']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Datas -->
                    <div class="zf-table-card" style="padding:24px;">
                        <h2 style="font-size:14px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--text-tertiary); margin:0 0 16px;">
                            Datas
                        </h2>
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <div style="display:flex; justify-content:space-between; font-size:13px;">
                                <span style="color:var(--text-tertiary);">Emissão</span>
                                <span style="font-weight:500;"><?= date('d/m/Y', strtotime($compra['data_emissao'])) ?></span>
                            </div>
                            <?php if ($compra['data_entrega']): ?>
                                <div style="display:flex; justify-content:space-between; font-size:13px;">
                                    <span style="color:var(--text-tertiary);">Entrega</span>
                                    <span style="font-weight:500;"><?= date('d/m/Y', strtotime($compra['data_entrega'])) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Pagamento -->
                    <?php if ($compra['forma_pagamento'] || $compra['vencimento']): ?>
                        <div class="zf-table-card" style="padding:24px;">
                            <h2 style="font-size:14px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--text-tertiary); margin:0 0 16px;">
                                Pagamento
                            </h2>
                            <div style="display:flex; flex-direction:column; gap:10px;">
                                <?php if ($compra['forma_pagamento']): ?>
                                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                                        <span style="color:var(--text-tertiary);">Forma</span>
                                        <span style="font-weight:500; text-transform:capitalize;"><?= e($compra['forma_pagamento']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($compra['prazo_pagamento']): ?>
                                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                                        <span style="color:var(--text-tertiary);">Prazo</span>
                                        <span style="font-weight:500;"><?= $compra['prazo_pagamento'] ?> dias</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($compra['vencimento']): ?>
                                    <?php
                                    $venc     = new DateTime($compra['vencimento']);
                                    $hoje     = new DateTime();
                                    $diffDias = (int)$hoje->diff($venc)->format('%r%a');
                                    $vencCor  = $diffDias < 0 ? '#ef4444' : ($diffDias <= 7 ? '#f59e0b' : 'var(--text-secondary)');
                                    ?>
                                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                                        <span style="color:var(--text-tertiary);">Vencimento</span>
                                        <span style="font-weight:500; color:<?= $vencCor ?>;">
                                            <?= date('d/m/Y', strtotime($compra['vencimento'])) ?>
                                            <?php if ($compra['status'] !== 'cancelada'): ?>
                                                <?php if ($diffDias < 0): ?>
                                                    <span style="font-size:11px;">(<?= abs($diffDias) ?>d atraso)</span>
                                                <?php elseif ($diffDias === 0): ?>
                                                    <span style="font-size:11px;">(hoje)</span>
                                                <?php elseif ($diffDias <= 7): ?>
                                                    <span style="font-size:11px;">(em <?= $diffDias ?>d)</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Cancelar Compra -->
<div id="modal-cancelar" style="
    display:none; position:fixed; inset:0; z-index:1000;
    background:rgba(0,0,0,.45); align-items:center; justify-content:center;">
    <div class="zf-table-card" style="width:460px; padding:28px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="font-size:16px; font-weight:700; margin:0; color:var(--color-danger,#ef4444);">
                <i class="ti ti-ban"></i> Cancelar Compra
            </h3>
            <button type="button" id="modal-cancel-fechar" style="background:none; border:none; cursor:pointer; color:var(--text-tertiary);">
                <i class="ti ti-x" style="font-size:20px;"></i>
            </button>
        </div>

        <?php if ($isConfirmada): ?>
            <div class="zf-alert zf-alert-danger" style="margin-bottom:16px;">
                <i class="ti ti-alert-triangle"></i>
                <strong>Atenção:</strong> Esta compra já foi confirmada. O cancelamento irá <strong>estornar o estoque</strong> de todos os produtos.
            </div>
        <?php endif; ?>

        <form method="POST" action="/compras/<?= $compra['id'] ?>/cancelar">
            <?= csrf_field() ?>
            <div style="margin-bottom:16px;">
                <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:6px;">
                    Motivo do cancelamento <span style="color:var(--color-danger,#ef4444);">*</span>
                </label>
                <textarea name="motivo" class="form-control" rows="3" required
                    placeholder="Descreva o motivo do cancelamento..."
                    style="resize:vertical;"></textarea>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" id="modal-cancel-cancelar" class="btn btn-outline">Voltar</button>
                <button type="submit" class="btn btn-primary" style="background:var(--color-danger,#ef4444); border-color:var(--color-danger,#ef4444);">
                    <i class="ti ti-ban"></i> Confirmar Cancelamento
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        const modal = document.getElementById('modal-cancelar');
        const btnAbrir = document.getElementById('btn-cancelar');
        const btnFechar = document.getElementById('modal-cancel-fechar');
        const btnVolta = document.getElementById('modal-cancel-cancelar');

        if (btnAbrir) {
            btnAbrir.addEventListener('click', () => modal.style.display = 'flex');
        }
        if (btnFechar) btnFechar.addEventListener('click', () => modal.style.display = 'none');
        if (btnVolta) btnVolta.addEventListener('click', () => modal.style.display = 'none');
        modal.addEventListener('click', e => {
            if (e.target === modal) modal.style.display = 'none';
        });
    })();
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>