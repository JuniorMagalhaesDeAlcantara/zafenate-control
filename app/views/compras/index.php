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

            <!-- Cards de totais -->
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
                <div class="zf-table-card" style="padding:20px; display:flex; flex-direction:column; gap:4px;">
                    <span style="font-size:12px; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:.5px;">Total Compras</span>
                    <span style="font-size:28px; font-weight:700;"><?= $totais['total'] ?? 0 ?></span>
                </div>
                <div class="zf-table-card" style="padding:20px; display:flex; flex-direction:column; gap:4px;">
                    <span style="font-size:12px; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:.5px;">Rascunhos</span>
                    <span style="font-size:28px; font-weight:700; color:var(--color-warning, #f59e0b);"><?= $totais['rascunhos'] ?? 0 ?></span>
                </div>
                <div class="zf-table-card" style="padding:20px; display:flex; flex-direction:column; gap:4px;">
                    <span style="font-size:12px; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:.5px;">Confirmadas</span>
                    <span style="font-size:28px; font-weight:700; color:var(--color-success, #22c55e);"><?= $totais['confirmadas'] ?? 0 ?></span>
                </div>
                <div class="zf-table-card" style="padding:20px; display:flex; flex-direction:column; gap:4px;">
                    <span style="font-size:12px; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:.5px;">Valor Confirmado</span>
                    <span style="font-size:22px; font-weight:700;">R$ <?= number_format($totais['valor_total'] ?? 0, 2, ',', '.') ?></span>
                </div>
            </div>

            <!-- Filtros + Ação -->
            <div class="zf-table-card" style="padding:16px 20px; margin-bottom:16px;">
                <form method="GET" action="/compras" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">

                    <div style="flex:1; min-width:200px;">
                        <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">Buscar</label>
                        <input type="text" name="busca" class="form-control" placeholder="Número, NF ou fornecedor..."
                               value="<?= e($filtros['busca']) ?>">
                    </div>

                    <div style="min-width:160px;">
                        <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="rascunho"   <?= $filtros['status'] === 'rascunho'   ? 'selected' : '' ?>>Rascunho</option>
                            <option value="confirmada" <?= $filtros['status'] === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                            <option value="cancelada"  <?= $filtros['status'] === 'cancelada'  ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>

                    <div style="min-width:200px;">
                        <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">Fornecedor</label>
                        <select name="fornecedor_id" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach ($fornecedores as $f): ?>
                                <option value="<?= $f['id'] ?>" <?= (int)$filtros['fornecedor_id'] === (int)$f['id'] ? 'selected' : '' ?>>
                                    <?= e($f['razao_social']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">De</label>
                        <input type="date" name="de" class="form-control" value="<?= e($filtros['de']) ?>">
                    </div>

                    <div>
                        <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">Até</label>
                        <input type="date" name="ate" class="form-control" value="<?= e($filtros['ate']) ?>">
                    </div>

                    <button type="submit" class="btn btn-outline">
                        <i class="ti ti-search"></i> Filtrar
                    </button>

                    <a href="/compras/criar" class="btn btn-primary" style="margin-left:auto;">
                        <i class="ti ti-plus"></i> Nova Compra
                    </a>

                </form>
            </div>

            <!-- Tabela -->
            <div class="zf-table-card">
                <table class="zf-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fornecedor</th>
                            <th>Emissão</th>
                            <th>Itens</th>
                            <th>Total</th>
                            <th>Pagamento</th>
                            <th>Status</th>
                            <th style="width:80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($compras)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:40px; color:var(--text-tertiary);">
                                    <i class="ti ti-shopping-cart-off" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                                    Nenhuma compra encontrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($compras as $c): ?>
                                <tr>
                                    <td>
                                        <a href="/compras/<?= $c['id'] ?>" style="font-weight:600; color:var(--color-primary);">
                                            <?= e($c['numero']) ?>
                                        </a>
                                        <?php if ($c['numero_nf']): ?>
                                            <div style="font-size:11px; color:var(--text-tertiary);">NF <?= e($c['numero_nf']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-weight:500;"><?= e($c['fornecedor_nome']) ?></div>
                                        <?php if ($c['fornecedor_fantasia']): ?>
                                            <div style="font-size:11px; color:var(--text-tertiary);"><?= e($c['fornecedor_fantasia']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($c['data_emissao'])) ?></td>
                                    <td style="text-align:center;"><?= $c['qtd_itens'] ?></td>
                                    <td style="font-weight:600;">R$ <?= number_format($c['total'], 2, ',', '.') ?></td>
                                    <td>
                                        <?php if ($c['forma_pagamento']): ?>
                                            <span style="text-transform:capitalize;"><?= e($c['forma_pagamento']) ?></span>
                                            <?php if ($c['vencimento']): ?>
                                                <div style="font-size:11px; color:var(--text-tertiary);">
                                                    Venc. <?= date('d/m/Y', strtotime($c['vencimento'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color:var(--text-tertiary);">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badge = match($c['status']) {
                                            'confirmada' => ['color' => '#22c55e', 'bg' => '#f0fdf4', 'label' => 'Confirmada'],
                                            'cancelada'  => ['color' => '#ef4444', 'bg' => '#fef2f2', 'label' => 'Cancelada'],
                                            default      => ['color' => '#f59e0b', 'bg' => '#fffbeb', 'label' => 'Rascunho'],
                                        };
                                        ?>
                                        <span style="
                                            background:<?= $badge['bg'] ?>;
                                            color:<?= $badge['color'] ?>;
                                            padding:2px 10px;
                                            border-radius:20px;
                                            font-size:12px;
                                            font-weight:600;
                                        "><?= $badge['label'] ?></span>
                                    </td>
                                    <td>
                                        <a href="/compras/<?= $c['id'] ?>" class="btn btn-outline btn-sm">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>