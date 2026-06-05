<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">

    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>

    <div class="zf-main">

        <?php
        $pageTitle = 'Produtos';
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Produtos',  'url' => '/produtos'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <!-- Alertas flash -->
            <?php if ($success = \App\Core\Session::getFlash('success')): ?>
                <div class="zf-alert zf-alert-success" data-auto-close>
                    <i class="ti ti-circle-check"></i>
                    <?= e($success) ?>
                </div>
            <?php endif; ?>
            <?php if ($error = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close>
                    <i class="ti ti-alert-circle"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <!-- Cards de totais -->
            <div class="zf-stats">
                <div class="zf-stat-card">
                    <div class="zf-stat-icon neutral"><i class="ti ti-package"></i></div>
                    <div class="zf-stat-body">
                        <div class="zf-stat-label">Total de produtos</div>
                        <div class="zf-stat-value"><?= $totais['total'] ?? 0 ?></div>
                    </div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-icon success"><i class="ti ti-circle-check"></i></div>
                    <div class="zf-stat-body">
                        <div class="zf-stat-label">Ativos</div>
                        <div class="zf-stat-value success"><?= $totais['ativos'] ?? 0 ?></div>
                    </div>
                </div>
                <div class="zf-stat-card <?= ($totais['alerta_estoque'] ?? 0) > 0 ? 'zf-stat-card--warning' : '' ?>">
                    <div class="zf-stat-icon warning"><i class="ti ti-alert-triangle"></i></div>
                    <div class="zf-stat-body">
                        <div class="zf-stat-label">Alerta de estoque</div>
                        <div class="zf-stat-value <?= ($totais['alerta_estoque'] ?? 0) > 0 ? 'danger' : '' ?>">
                            <?= $totais['alerta_estoque'] ?? 0 ?>
                        </div>
                    </div>
                </div>
                <div class="zf-stat-card <?= ($totais['zerados'] ?? 0) > 0 ? 'zf-stat-card--danger' : '' ?>">
                    <div class="zf-stat-icon danger"><i class="ti ti-package-off"></i></div>
                    <div class="zf-stat-body">
                        <div class="zf-stat-label">Estoque zerado</div>
                        <div class="zf-stat-value <?= ($totais['zerados'] ?? 0) > 0 ? 'danger' : '' ?>">
                            <?= $totais['zerados'] ?? 0 ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toolbar: busca + filtros rápidos + ação -->
            <div class="zf-toolbar zf-toolbar--stacked">
                <div class="zf-toolbar-top">
                    <form action="/produtos" method="GET" class="d-flex align-center gap-8" style="flex:1; flex-wrap:wrap;">
                        <div class="zf-search-wrap">
                            <i class="ti ti-search"></i>
                            <input
                                class="zf-search"
                                type="text"
                                name="busca"
                                value="<?= e($filtros['busca'] ?? '') ?>"
                                placeholder="Buscar por nome, código ou barras...">
                        </div>
                        <!-- Preserva filtro rápido ao buscar -->
                        <?php if (!empty($filtros['status'])): ?>
                            <input type="hidden" name="status" value="<?= e($filtros['status']) ?>">
                        <?php endif; ?>
                        <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
                        <?php if (!empty($filtros['busca']) || !empty($filtros['status'])): ?>
                            <a href="/produtos" class="btn btn-outline btn-sm" style="color:var(--color-danger)">
                                <i class="ti ti-x"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </form>

                    <a href="/produtos/criar" class="btn btn-primary">
                        <i class="ti ti-plus"></i>
                        Novo produto
                    </a>
                </div>

                <!-- Filtros rápidos -->
                <div class="zf-quick-filters">
                    <span class="zf-quick-filters-label">Filtrar por:</span>
                    <a href="/produtos<?= !empty($filtros['busca']) ? '?busca=' . urlencode($filtros['busca']) : '' ?>"
                        class="zf-quick-filter <?= empty($filtros['status']) ? 'active' : '' ?>">
                        <i class="ti ti-list"></i> Todos
                        <span class="zf-quick-filter-count"><?= $totais['total'] ?? 0 ?></span>
                    </a>
                    <a href="/produtos?status=ativos<?= !empty($filtros['busca']) ? '&busca=' . urlencode($filtros['busca']) : '' ?>"
                        class="zf-quick-filter <?= ($filtros['status'] ?? '') === 'ativos' ? 'active' : '' ?>">
                        <i class="ti ti-circle-check"></i> Ativos
                        <span class="zf-quick-filter-count"><?= $totais['ativos'] ?? 0 ?></span>
                    </a>
                    <?php if (($totais['alerta_estoque'] ?? 0) > 0): ?>
                        <a href="/produtos?status=alerta<?= !empty($filtros['busca']) ? '&busca=' . urlencode($filtros['busca']) : '' ?>"
                            class="zf-quick-filter zf-quick-filter--warning <?= ($filtros['status'] ?? '') === 'alerta' ? 'active' : '' ?>">
                            <i class="ti ti-alert-triangle"></i> Alerta de estoque
                            <span class="zf-quick-filter-count"><?= $totais['alerta_estoque'] ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if (($totais['zerados'] ?? 0) > 0): ?>
                        <a href="/produtos?status=zerados<?= !empty($filtros['busca']) ? '&busca=' . urlencode($filtros['busca']) : '' ?>"
                            class="zf-quick-filter zf-quick-filter--danger <?= ($filtros['status'] ?? '') === 'zerados' ? 'active' : '' ?>">
                            <i class="ti ti-package-off"></i> Zerados
                            <span class="zf-quick-filter-count"><?= $totais['zerados'] ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Indicador de filtro ativo -->
            <?php if (!empty($filtros['status'])): ?>
                <?php
                $labelFiltro = match ($filtros['status']) {
                    'ativos'  => 'Exibindo apenas produtos ativos',
                    'alerta'  => 'Exibindo produtos com alerta de estoque',
                    'zerados' => 'Exibindo produtos com estoque zerado',
                    default   => ''
                };
                ?>
                <?php if ($labelFiltro): ?>
                    <div class="zf-filter-active-bar">
                        <i class="ti ti-filter"></i>
                        <?= $labelFiltro ?>
                        <a href="/produtos" class="zf-filter-clear"><i class="ti ti-x"></i> Remover filtro</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Tabela -->
            <div class="zf-table-card">
                <table class="zf-table">
                    <thead>
                        <tr>
                            <th style="width:90px">Código</th>
                            <th>Produto</th>
                            <th style="width:110px">Preço venda</th>
                            <th style="width:150px">Estoque atual</th>
                            <th style="width:80px">Mínimo</th>
                            <th style="width:80px">Status</th>
                            <th style="width:110px; text-align:center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produtos)): ?>
                            <tr>
                                <td colspan="7" class="td-empty">
                                    <i class="ti ti-package-off" style="font-size:28px; display:block; margin-bottom:8px; opacity:0.3"></i>
                                    <?php if (!empty($filtros['busca'])): ?>
                                        Nenhum produto encontrado para "<strong><?= e($filtros['busca']) ?></strong>"
                                    <?php elseif (($filtros['status'] ?? '') === 'zerados'): ?>
                                        Nenhum produto com estoque zerado. Ótimo!
                                    <?php elseif (($filtros['status'] ?? '') === 'alerta'): ?>
                                        Nenhum produto em alerta de estoque.
                                    <?php else: ?>
                                        Nenhum produto cadastrado ainda.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($produtos as $p): ?>
                                <?php
                                $estoqueZerado = ($p['estoque_atual'] ?? 0) <= 0;
                                $rowClass = $estoqueZerado ? 'tr-zerado' : ($p['alerta_estoque'] ? 'tr-alerta' : '');
                                ?>
                                <tr class="<?= $rowClass ?>">
                                    <td>
                                        <span class="td-code"><?= e($p['codigo']) ?></span>
                                    </td>
                                    <td>
                                        <div class="td-name"><?= e($p['nome']) ?></div>
                                        <div class="td-sub"><?= e($p['categoria_nome'] ?? 'Sem categoria') ?></div>
                                    </td>
                                    <td>
                                        <span class="fw-500">R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?></span>
                                    </td>
                                    <td>
                                        <?php if ($estoqueZerado): ?>
                                            <span class="badge badge-danger badge-stock-zero">
                                                <i class="ti ti-package-off" style="font-size:10px"></i>
                                                Zerado
                                            </span>
                                        <?php elseif ($p['alerta_estoque']): ?>
                                            <span class="badge badge-warning">
                                                <i class="ti ti-alert-triangle" style="font-size:10px"></i>
                                                <?= number_format($p['estoque_atual'], 3, ',', '.') ?> <?= e($p['unidade_sigla']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="fw-500">
                                                <?= number_format($p['estoque_atual'], 3, ',', '.') ?>
                                            </span>
                                            <span class="text-muted text-sm"><?= e($p['unidade_sigla']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted text-sm">
                                        <?= number_format($p['estoque_minimo'], 3, ',', '.') ?>
                                    </td>
                                    <td>
                                        <?php if ($p['ativo']): ?>
                                            <span class="badge badge-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge badge-neutral">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="td-actions">
                                        <a href="/produtos/<?= $p['id'] ?>/editar" class="act-link">
                                            Editar
                                        </a>

                                        <form action="/produtos/<?= $p['id'] ?>/status" method="POST" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button
                                                type="submit"
                                                class="act-btn <?= $p['ativo'] ? '' : 'activate' ?>"
                                                data-confirm="<?= $p['ativo'] ? 'Desativar este produto?' : 'Ativar este produto?' ?>">
                                                <?= $p['ativo'] ? 'Desativar' : 'Ativar' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Paginação -->
                <?php if (!empty($paginacao) && $paginacao['total_paginas'] > 1): ?>
                    <div class="zf-pagination">
                        <span>
                            Exibindo <?= $paginacao['inicio'] ?>–<?= $paginacao['fim'] ?> de <?= $paginacao['total'] ?> produtos
                        </span>
                        <div class="zf-pages">
                            <?php for ($i = 1; $i <= $paginacao['total_paginas']; $i++): ?>
                                <a href="?pagina=<?= $i ?><?= !empty($filtros['busca']) ? '&busca=' . urlencode($filtros['busca']) : '' ?><?= !empty($filtros['status']) ? '&status=' . urlencode($filtros['status']) : '' ?>"
                                    class="zf-page-btn <?= $i === $paginacao['pagina_atual'] ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div><!-- /.zf-table-card -->

        </div><!-- /.zf-content -->

    </div><!-- /.zf-main -->
</div><!-- /.zf-layout -->

<style>
    /* ── Stat cards com ícone ───────────────────────────── */
    .zf-stats {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .zf-stat-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        background: var(--color-surface, #fff);
        border: 1px solid var(--color-border, #e5e7eb);
        border-radius: 10px;
        transition: box-shadow .15s, border-color .15s;
    }

    .zf-stat-card--warning {
        border-color: var(--color-warning, #f59e0b);
        background: color-mix(in srgb, var(--color-warning, #f59e0b) 6%, var(--color-surface, #fff));
    }

    .zf-stat-card--danger {
        border-color: var(--color-danger, #ef4444);
        background: color-mix(in srgb, var(--color-danger, #ef4444) 6%, var(--color-surface, #fff));
    }

    .zf-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .zf-stat-icon.neutral {
        background: var(--color-bg, #f3f4f6);
        color: var(--color-text-muted, #6b7280);
    }

    .zf-stat-icon.success {
        background: color-mix(in srgb, var(--color-success, #22c55e) 15%, transparent);
        color: var(--color-success, #22c55e);
    }

    .zf-stat-icon.warning {
        background: color-mix(in srgb, var(--color-warning, #f59e0b) 15%, transparent);
        color: var(--color-warning, #f59e0b);
    }

    .zf-stat-icon.danger {
        background: color-mix(in srgb, var(--color-danger, #ef4444) 15%, transparent);
        color: var(--color-danger, #ef4444);
    }

    .zf-stat-body {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    /* ── Toolbar empilhada ──────────────────────────────── */
    .zf-toolbar--stacked {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
        padding-bottom: 12px;
    }

    .zf-toolbar-top {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* ── Filtros rápidos ────────────────────────────────── */
    .zf-quick-filters {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .zf-quick-filters-label {
        font-size: 12px;
        color: var(--color-text-muted, #6b7280);
        font-weight: 500;
        margin-right: 2px;
        white-space: nowrap;
    }

    .zf-quick-filter {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        border: 1px solid var(--color-border, #e5e7eb);
        background: var(--color-surface, #fff);
        color: var(--color-text, #374151);
        font-size: 12.5px;
        font-weight: 500;
        text-decoration: none;
        transition: background .15s, border-color .15s, color .15s;
        cursor: pointer;
        white-space: nowrap;
    }

    .zf-quick-filter:hover {
        background: var(--color-bg, #f3f4f6);
        border-color: var(--color-text-muted, #9ca3af);
    }

    .zf-quick-filter.active {
        background: var(--color-primary, #2563eb);
        border-color: var(--color-primary, #2563eb);
        color: #fff;
    }

    .zf-quick-filter.active .zf-quick-filter-count {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
    }

    .zf-quick-filter--warning {
        border-color: color-mix(in srgb, var(--color-warning, #f59e0b) 40%, var(--color-border, #e5e7eb));
        color: var(--color-warning-dark, #92400e);
    }

    .zf-quick-filter--warning:hover,
    .zf-quick-filter--warning.active {
        background: var(--color-warning, #f59e0b);
        border-color: var(--color-warning, #f59e0b);
        color: #fff;
    }

    .zf-quick-filter--danger {
        border-color: color-mix(in srgb, var(--color-danger, #ef4444) 40%, var(--color-border, #e5e7eb));
        color: var(--color-danger, #ef4444);
    }

    .zf-quick-filter--danger:hover,
    .zf-quick-filter--danger.active {
        background: var(--color-danger, #ef4444);
        border-color: var(--color-danger, #ef4444);
        color: #fff;
    }

    .zf-quick-filter-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 10px;
        background: var(--color-bg, #f3f4f6);
        color: var(--color-text-muted, #6b7280);
        font-size: 11px;
        font-weight: 600;
        line-height: 1;
    }

    /* ── Barra de filtro ativo ──────────────────────────── */
    .zf-filter-active-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        background: color-mix(in srgb, var(--color-primary, #2563eb) 8%, var(--color-surface, #fff));
        border: 1px solid color-mix(in srgb, var(--color-primary, #2563eb) 25%, var(--color-border, #e5e7eb));
        border-radius: 8px;
        font-size: 13px;
        color: var(--color-primary, #2563eb);
        margin-bottom: 4px;
    }

    .zf-filter-active-bar i {
        font-size: 14px;
    }

    .zf-filter-clear {
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        font-weight: 600;
        color: var(--color-danger, #ef4444);
        text-decoration: none;
        padding: 2px 8px;
        border-radius: 6px;
        transition: background .15s;
    }

    .zf-filter-clear:hover {
        background: color-mix(in srgb, var(--color-danger, #ef4444) 10%, transparent);
    }

    /* ── Linhas especiais na tabela ─────────────────────── */
    tr.tr-zerado {
        background: color-mix(in srgb, var(--color-danger, #ef4444) 4%, var(--color-surface, #fff));
    }

    tr.tr-zerado:hover {
        background: color-mix(in srgb, var(--color-danger, #ef4444) 8%, var(--color-surface, #fff));
    }

    tr.tr-alerta {
        background: color-mix(in srgb, var(--color-warning, #f59e0b) 4%, var(--color-surface, #fff));
    }

    tr.tr-alerta:hover {
        background: color-mix(in srgb, var(--color-warning, #f59e0b) 8%, var(--color-surface, #fff));
    }

    /* ── Badge zerado ───────────────────────────────────── */
    .badge-stock-zero {
        font-weight: 700;
        letter-spacing: 0.02em;
        animation: pulse-danger 2.5s ease-in-out infinite;
    }

    @keyframes pulse-danger {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    /* ── Badge danger (caso não exista no sistema) ──────── */
    .badge-danger {
        background: color-mix(in srgb, var(--color-danger, #ef4444) 12%, transparent);
        color: var(--color-danger, #ef4444);
        border: 1px solid color-mix(in srgb, var(--color-danger, #ef4444) 30%, transparent);
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }
</style>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>