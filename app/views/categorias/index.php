<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">

    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>

    <div class="zf-main">

        <?php
        $pageTitle  = 'Categorias de Produtos';
        $breadcrumb = [
            ['label' => 'Dashboard',  'url' => '/dashboard'],
            ['label' => 'Categorias', 'url' => '#'],
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
                    <div class="zf-stat-label">Total de categorias</div>
                    <div class="zf-stat-value"><?= $totais['total'] ?? 0 ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Ativas</div>
                    <div class="zf-stat-value success"><?= $totais['ativas'] ?? 0 ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Categorias raiz</div>
                    <div class="zf-stat-value"><?= $totais['raiz'] ?? 0 ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Subcategorias</div>
                    <div class="zf-stat-value"><?= $totais['subcategorias'] ?? 0 ?></div>
                </div>
            </div>

            <!-- Toolbar: busca + filtro de status + ação -->
            <div class="zf-toolbar">
                <form action="/categorias" method="GET" class="d-flex align-center gap-8" style="flex:1; flex-wrap:wrap;">
                    <div class="zf-search-wrap">
                        <i class="ti ti-search"></i>
                        <input
                            class="zf-search"
                            type="text"
                            name="busca"
                            value="<?= e($filtros['busca'] ?? '') ?>"
                            placeholder="Buscar por nome...">
                    </div>

                    <div class="pf-select-wrap" style="width:140px;">
                        <select name="ativo" class="pf-input pf-select" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="1" <?= ($filtros['ativo'] ?? '') === '1' ? 'selected' : '' ?>>Ativas</option>
                            <option value="0" <?= ($filtros['ativo'] ?? '') === '0' ? 'selected' : '' ?>>Inativas</option>
                        </select>
                        <i class="ti ti-chevron-down pf-select-arrow"></i>
                    </div>

                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>

                    <?php if (!empty($filtros['busca']) || $filtros['ativo'] !== ''): ?>
                        <a href="/categorias" class="btn btn-outline btn-sm" style="color:var(--color-danger)">
                            <i class="ti ti-x"></i> Limpar
                        </a>
                    <?php endif; ?>
                </form>

                <a href="/categorias/criar" class="btn btn-primary">
                    <i class="ti ti-plus"></i>
                    Nova categoria
                </a>
            </div>

            <!-- Tabela -->
            <div class="zf-table-card">
                <table class="zf-table">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th style="width:160px">Categoria Pai</th>
                            <th style="width:110px; text-align:center">Subcategorias</th>
                            <th style="width:110px; text-align:center">Produtos</th>
                            <th style="width:80px">Status</th>
                            <th style="width:120px; text-align:center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categorias)): ?>
                            <tr>
                                <td colspan="6" class="td-empty">
                                    <i class="ti ti-folder-off" style="font-size:28px; display:block; margin-bottom:8px; opacity:0.3"></i>
                                    <?= !empty($filtros['busca'])
                                        ? 'Nenhuma categoria encontrada para "' . e($filtros['busca']) . '"'
                                        : 'Nenhuma categoria cadastrada ainda.' ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categorias as $cat): ?>
                                <tr>
                                    <td>
                                        <div class="cat-name-wrap">
                                            <?php if (!empty($cat['parent_nome'])): ?>
                                                <span class="cat-sub-indicator">
                                                    <i class="ti ti-corner-down-right"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="cat-root-icon">
                                                    <i class="ti ti-folder"></i>
                                                </span>
                                            <?php endif; ?>
                                            <span class="td-name"><?= e($cat['nome']) ?></span>
                                        </div>
                                        <?php if (!empty($cat['descricao'])): ?>
                                            <div class="td-sub"><?= e($cat['descricao']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted text-sm">
                                        <?= !empty($cat['parent_nome']) ? e($cat['parent_nome']) : '<span class="badge badge-neutral">Raiz</span>' ?>
                                    </td>
                                    <td style="text-align:center">
                                        <?php if ($cat['qtd_filhas'] > 0): ?>
                                            <span class="badge badge-neutral"><?= $cat['qtd_filhas'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted text-sm">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:center">
                                        <?php if ($cat['qtd_produtos'] > 0): ?>
                                            <span class="fw-500"><?= $cat['qtd_produtos'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted text-sm">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($cat['ativo']): ?>
                                            <span class="badge badge-success">Ativa</span>
                                        <?php else: ?>
                                            <span class="badge badge-neutral">Inativa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="td-actions">
                                        <a href="/categorias/<?= $cat['id'] ?>/editar" class="act-link">
                                            Editar
                                        </a>
                                        <form action="/categorias/<?= $cat['id'] ?>/status" method="POST" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button
                                                type="submit"
                                                class="act-btn <?= $cat['ativo'] ? '' : 'activate' ?>"
                                                data-confirm="<?= $cat['ativo'] ? 'Desativar esta categoria?' : 'Ativar esta categoria?' ?>">
                                                <?= $cat['ativo'] ? 'Desativar' : 'Ativar' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div><!-- /.zf-table-card -->

        </div><!-- /.zf-content -->

    </div><!-- /.zf-main -->
</div><!-- /.zf-layout -->

<style>
    .cat-name-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cat-root-icon {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        background: #F3F4F6;
        color: #6B7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }

    .cat-sub-indicator {
        color: #9CA3AF;
        font-size: 13px;
        display: flex;
        align-items: center;
        flex-shrink: 0;
        padding-left: 4px;
    }

    /* reutiliza os estilos do pf-select para o filtro inline */
    .pf-select-wrap {
        position: relative;
    }

    .pf-select {
        appearance: none;
        -webkit-appearance: none;
        padding-right: 30px !important;
    }

    .pf-select-arrow {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 13px;
        color: #9CA3AF;
        pointer-events: none;
    }
</style>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>