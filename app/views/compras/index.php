<?php require VIEW_PATH . '/layouts/header.php'; ?>

<style>
/* ── Compras: design refinado industrial/utilitário ── */
.cp-page { padding: 28px 32px; }

.cp-topbar {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 28px;
    gap: 16px;
    flex-wrap: wrap;
}
.cp-topbar-title { margin: 0; font-size: 24px; font-weight: 600; letter-spacing: -.4px; }
.cp-topbar-sub   { font-size: 13px; color: var(--text-tertiary); margin: 3px 0 0; }

/* Cards de métricas */
.cp-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
.cp-metric {
    background: var(--color-background-primary, #fff);
    border: 1px solid var(--color-border-tertiary, #e5e7eb);
    border-radius: 10px;
    padding: 18px 20px;
    position: relative;
    overflow: hidden;
    transition: box-shadow .18s, transform .18s;
}
.cp-metric:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); transform: translateY(-1px); }
.cp-metric-label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--text-tertiary, #9ca3af);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.cp-metric-value {
    font-size: 26px;
    font-weight: 700;
    letter-spacing: -.5px;
    line-height: 1;
}
.cp-metric-accent {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 0 0 10px 10px;
}

/* Filtros */
.cp-filters {
    background: var(--color-background-primary, #fff);
    border: 1px solid var(--color-border-tertiary, #e5e7eb);
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 16px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: flex-end;
}
.cp-field { display: flex; flex-direction: column; gap: 4px; }
.cp-field label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: var(--text-tertiary, #9ca3af);
}
.cp-field input,
.cp-field select {
    height: 36px;
    padding: 0 10px;
    border: 1px solid var(--color-border-tertiary, #e5e7eb);
    border-radius: 7px;
    font-size: 13px;
    background: var(--color-background-secondary, #f9fafb);
    color: var(--color-text-primary, #111);
    outline: none;
    transition: border-color .15s, background .15s;
    font-family: inherit;
}
.cp-field input:focus,
.cp-field select:focus {
    border-color: var(--color-primary, #6366f1);
    background: var(--color-background-primary, #fff);
}
.cp-field-busca input { width: 240px; padding-left: 34px; }
.cp-busca-wrap { position: relative; }
.cp-busca-wrap .ti-search {
    position: absolute;
    left: 10px; top: 50%;
    transform: translateY(-50%);
    font-size: 15px;
    color: var(--text-tertiary);
    pointer-events: none;
}
.cp-field-date input { width: 138px; }
.cp-filter-actions { display: flex; gap: 8px; align-items: flex-end; margin-left: auto; }

/* Tabela */
.cp-card {
    background: var(--color-background-primary, #fff);
    border: 1px solid var(--color-border-tertiary, #e5e7eb);
    border-radius: 10px;
    overflow: hidden;
}
.cp-table-header {
    padding: 14px 20px;
    border-bottom: 1px solid var(--color-border-tertiary, #e5e7eb);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.cp-table-header-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-text-primary);
}
.cp-table-count {
    font-size: 12px;
    color: var(--text-tertiary);
    background: var(--color-background-secondary, #f3f4f6);
    padding: 2px 8px;
    border-radius: 20px;
}

table.cp-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
table.cp-table thead tr {
    background: var(--color-background-secondary, #f9fafb);
    border-bottom: 1px solid var(--color-border-tertiary, #e5e7eb);
}
table.cp-table thead th {
    padding: 10px 16px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--text-tertiary, #9ca3af);
    white-space: nowrap;
}
table.cp-table tbody tr {
    border-bottom: 1px solid var(--color-border-tertiary, #e5e7eb);
    transition: background .1s;
}
table.cp-table tbody tr:last-child { border-bottom: none; }
table.cp-table tbody tr:hover { background: var(--color-background-secondary, #f9fafb); }
table.cp-table td { padding: 13px 16px; vertical-align: middle; }

/* Badge status */
.cp-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .03em;
    white-space: nowrap;
}
.cp-badge-confirmada { background: #f0fdf4; color: #16a34a; }
.cp-badge-cancelada  { background: #fef2f2; color: #dc2626; }
.cp-badge-rascunho   { background: #fffbeb; color: #d97706; }

/* Número da compra */
.cp-num {
    font-weight: 700;
    font-size: 13px;
    color: var(--color-primary, #6366f1);
    text-decoration: none;
    letter-spacing: -.2px;
}
.cp-num:hover { text-decoration: underline; }
.cp-nf {
    font-size: 11px;
    color: var(--text-tertiary);
    margin-top: 2px;
    font-family: 'SF Mono', 'Fira Code', monospace;
}

/* Fornecedor */
.cp-fornecedor-nome    { font-weight: 500; }
.cp-fornecedor-fantasia { font-size: 11px; color: var(--text-tertiary); margin-top: 2px; }

/* Valor */
.cp-valor { font-weight: 700; font-size: 14px; letter-spacing: -.2px; }

/* Pagamento */
.cp-pgto { font-size: 13px; font-weight: 500; }
.cp-pgto-venc { font-size: 11px; color: var(--text-tertiary); margin-top: 2px; }

/* Ação */
.cp-btn-view {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 12px;
    border: 1px solid var(--color-border-tertiary, #e5e7eb);
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    color: var(--color-text-secondary);
    text-decoration: none;
    transition: border-color .15s, color .15s, background .15s;
    background: transparent;
    white-space: nowrap;
}
.cp-btn-view:hover {
    border-color: var(--color-primary, #6366f1);
    color: var(--color-primary, #6366f1);
    background: rgba(99,102,241,.04);
}

/* Empty state */
.cp-empty {
    padding: 60px 20px;
    text-align: center;
    color: var(--text-tertiary);
}
.cp-empty i { font-size: 40px; display: block; margin-bottom: 12px; opacity: .25; }
.cp-empty-title { font-size: 15px; font-weight: 600; margin-bottom: 6px; color: var(--color-text-primary); opacity: .5; }
.cp-empty-sub   { font-size: 13px; }

/* Btn primário inline */
.cp-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: var(--color-primary, #6366f1);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: opacity .15s, transform .1s;
    white-space: nowrap;
}
.cp-btn-primary:hover  { opacity: .88; }
.cp-btn-primary:active { transform: scale(.98); }

.cp-btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: transparent;
    color: var(--color-text-secondary);
    border: 1px solid var(--color-border-tertiary, #e5e7eb);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: border-color .15s, color .15s;
}
.cp-btn-outline:hover {
    border-color: var(--color-border-primary, #d1d5db);
    color: var(--color-text-primary);
}
</style>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">
        <?php require VIEW_PATH . '/layouts/navbar.php'; ?>
        <div class="zf-content cp-page">

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

            <!-- Topbar -->
            <div class="cp-topbar">
                <div>
                    <h1 class="cp-topbar-title">Compras</h1>
                    <p class="cp-topbar-sub">Gerencie suas ordens de compra e entradas de estoque</p>
                </div>
                <a href="/compras/criar" class="cp-btn-primary">
                    <i class="ti ti-plus"></i> Nova Compra
                </a>
            </div>

            <!-- Métricas -->
            <div class="cp-metrics">
                <div class="cp-metric">
                    <div class="cp-metric-label"><i class="ti ti-shopping-bag"></i> Total</div>
                    <div class="cp-metric-value"><?= $totais['total'] ?? 0 ?></div>
                    <div class="cp-metric-accent" style="background:var(--color-primary,#6366f1)"></div>
                </div>
                <div class="cp-metric">
                    <div class="cp-metric-label"><i class="ti ti-clock"></i> Rascunhos</div>
                    <div class="cp-metric-value" style="color:#d97706"><?= $totais['rascunhos'] ?? 0 ?></div>
                    <div class="cp-metric-accent" style="background:#f59e0b"></div>
                </div>
                <div class="cp-metric">
                    <div class="cp-metric-label"><i class="ti ti-circle-check"></i> Confirmadas</div>
                    <div class="cp-metric-value" style="color:#16a34a"><?= $totais['confirmadas'] ?? 0 ?></div>
                    <div class="cp-metric-accent" style="background:#22c55e"></div>
                </div>
                <div class="cp-metric">
                    <div class="cp-metric-label"><i class="ti ti-cash"></i> Valor confirmado</div>
                    <div class="cp-metric-value" style="font-size:20px;">
                        R$ <?= number_format($totais['valor_total'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div class="cp-metric-accent" style="background:#6366f1"></div>
                </div>
            </div>

            <!-- Filtros -->
            <form method="GET" action="/compras" class="cp-filters">

                <div class="cp-field cp-field-busca">
                    <label>Buscar</label>
                    <div class="cp-busca-wrap">
                        <i class="ti ti-search"></i>
                        <input type="text" name="busca"
                               placeholder="Número, NF ou fornecedor..."
                               value="<?= e($filtros['busca']) ?>">
                    </div>
                </div>

                <div class="cp-field">
                    <label>Status</label>
                    <select name="status" style="width:140px">
                        <option value="">Todos</option>
                        <option value="rascunho"   <?= $filtros['status'] === 'rascunho'   ? 'selected' : '' ?>>Rascunho</option>
                        <option value="confirmada" <?= $filtros['status'] === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                        <option value="cancelada"  <?= $filtros['status'] === 'cancelada'  ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </div>

                <div class="cp-field">
                    <label>Fornecedor</label>
                    <select name="fornecedor_id" style="width:200px">
                        <option value="">Todos</option>
                        <?php foreach ($fornecedores as $f): ?>
                            <option value="<?= $f['id'] ?>"
                                <?= (int)$filtros['fornecedor_id'] === (int)$f['id'] ? 'selected' : '' ?>>
                                <?= e($f['razao_social']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cp-field cp-field-date">
                    <label>De</label>
                    <input type="date" name="de" value="<?= e($filtros['de']) ?>">
                </div>

                <div class="cp-field cp-field-date">
                    <label>Até</label>
                    <input type="date" name="ate" value="<?= e($filtros['ate']) ?>">
                </div>

                <div class="cp-filter-actions">
                    <?php if (array_filter($filtros)): ?>
                        <a href="/compras" class="cp-btn-outline">
                            <i class="ti ti-x"></i> Limpar
                        </a>
                    <?php endif; ?>
                    <button type="submit" class="cp-btn-outline">
                        <i class="ti ti-filter"></i> Filtrar
                    </button>
                </div>

            </form>

            <!-- Tabela -->
            <div class="cp-card">

                <div class="cp-table-header">
                    <span class="cp-table-header-title">Ordens de compra</span>
                    <?php if (!empty($compras)): ?>
                        <span class="cp-table-count"><?= count($compras) ?> registro(s)</span>
                    <?php endif; ?>
                </div>

                <table class="cp-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fornecedor</th>
                            <th>Emissão</th>
                            <th style="text-align:center">Itens</th>
                            <th style="text-align:right">Total</th>
                            <th>Pagamento</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($compras)): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="cp-empty">
                                        <i class="ti ti-shopping-cart-off"></i>
                                        <div class="cp-empty-title">Nenhuma compra encontrada</div>
                                        <div class="cp-empty-sub">Tente ajustar os filtros ou crie uma nova compra</div>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($compras as $c): ?>
                                <tr>
                                    <td>
                                        <a href="/compras/<?= $c['id'] ?>" class="cp-num">
                                            <?= e($c['numero']) ?>
                                        </a>
                                        <?php if ($c['numero_nf']): ?>
                                            <div class="cp-nf">NF <?= e($c['numero_nf']) ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="cp-fornecedor-nome"><?= e($c['fornecedor_nome']) ?></div>
                                        <?php if ($c['fornecedor_fantasia']): ?>
                                            <div class="cp-fornecedor-fantasia"><?= e($c['fornecedor_fantasia']) ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <td style="color:var(--text-tertiary);white-space:nowrap">
                                        <?= date('d/m/Y', strtotime($c['data_emissao'])) ?>
                                    </td>

                                    <td style="text-align:center">
                                        <span style="display:inline-flex;align-items:center;justify-content:center;
                                                     width:24px;height:24px;border-radius:6px;font-size:12px;font-weight:700;
                                                     background:var(--color-background-secondary,#f3f4f6);
                                                     color:var(--color-text-secondary);">
                                            <?= $c['qtd_itens'] ?>
                                        </span>
                                    </td>

                                    <td style="text-align:right">
                                        <span class="cp-valor">R$ <?= number_format($c['total'], 2, ',', '.') ?></span>
                                    </td>

                                    <td>
                                        <?php if ($c['forma_pagamento']): ?>
                                            <div class="cp-pgto" style="text-transform:capitalize">
                                                <?= e($c['forma_pagamento']) ?>
                                            </div>
                                            <?php if ($c['vencimento']): ?>
                                                <div class="cp-pgto-venc">
                                                    <i class="ti ti-calendar" style="font-size:10px"></i>
                                                    <?= date('d/m/Y', strtotime($c['vencimento'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color:var(--text-tertiary)">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php $badge = match($c['status']) {
                                            'confirmada' => 'cp-badge-confirmada',
                                            'cancelada'  => 'cp-badge-cancelada',
                                            default      => 'cp-badge-rascunho',
                                        };
                                        $label = match($c['status']) {
                                            'confirmada' => '<i class="ti ti-circle-check"></i> Confirmada',
                                            'cancelada'  => '<i class="ti ti-ban"></i> Cancelada',
                                            default      => '<i class="ti ti-pencil"></i> Rascunho',
                                        }; ?>
                                        <span class="cp-badge <?= $badge ?>"><?= $label ?></span>
                                    </td>

                                    <td>
                                        <a href="/compras/<?= $c['id'] ?>" class="cp-btn-view">
                                            <i class="ti ti-eye"></i> Ver
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
