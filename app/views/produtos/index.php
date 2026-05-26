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

            <div class="zf-stats">
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Total de produtos</div>
                    <div class="zf-stat-value"><?= $totais['total'] ?? 0 ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Ativos</div>
                    <div class="zf-stat-value success"><?= $totais['ativos'] ?? 0 ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Alerta de estoque</div>
                    <div class="zf-stat-value <?= ($totais['alerta_estoque'] ?? 0) > 0 ? 'danger' : '' ?>">
                        <?= $totais['alerta_estoque'] ?? 0 ?>
                    </div>
                </div>
            </div>

            <div class="zf-toolbar">
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
                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
                    <?php if (!empty($filtros['busca'])): ?>
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

            <div class="zf-table-card">
                <table class="zf-table">
                    <thead>
                        <tr>
                            <th style="width:90px">Código</th>
                            <th>Produto</th>
                            <th style="width:110px">Preço venda</th>
                            <th style="width:130px">Estoque atual</th>
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
                                    <?= !empty($filtros['busca']) ? 'Nenhum produto encontrado para "' . e($filtros['busca']) . '"' : 'Nenhum produto cadastrado ainda.' ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($produtos as $p): ?>
                                <tr>
                                    <td>
                                        <span class="td-code"><?= e($p['codigo']) ?></span>
                                    </td>
                                    <td>
                                        <div class="td-name"><?= e($p['nome']) ?></div>
                                        <div class="td-sub"><?= e($p['categoria_nome'] ?? 'Sem categoria') ?></div>
                                    </td>
                                    <td>
                                        <span class="fw-500">R$ <?= number_format($p['preco_venda'] ?? 0, 2, ',', '.') ?></span>
                                    </td>
                                    <td>
                                        <?php if (isset($p['alerta_estoque']) && $p['alerta_estoque']): ?>
                                            <span class="badge badge-warning">
                                                <i class="ti ti-alert-triangle" style="font-size:10px"></i>
                                                <?= number_format($p['estoque_atual'] ?? 0, 3, ',', '.') ?> <?= e($p['unidade_sigla'] ?? 'UN') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="fw-500">
                                                <?= number_format($p['estoque_atual'] ?? 0, 3, ',', '.') ?>
                                            </span>
                                            <span class="text-muted text-sm"><?= e($p['unidade_sigla'] ?? 'UN') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted text-sm">
                                        <?= number_format($p['estoque_minimo'] ?? 0, 3, ',', '.') ?>
                                    </td>
                                    <td>
                                        <?php if (isset($p['ativo']) && $p['ativo']): ?>
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
                                            <?= $csrf ?? '' ?>

                                            <button
                                                type="submit"
                                                class="act-btn <?= (isset($p['ativo']) && $p['ativo']) ? '' : 'activate' ?>"
                                                data-confirm="<?= (isset($p['ativo']) && $p['ativo']) ? 'Desativar este produto?' : 'Ativar este produto?' ?>">
                                                <?= (isset($p['ativo']) && $p['ativo']) ? 'Desativar' : 'Ativar' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if (!empty($paginacao) && $paginacao['total_paginas'] > 1): ?>
                    <div class="zf-pagination">
                        <span>
                            Exibindo <?= $paginacao['inicio'] ?>–<?= $paginacao['fim'] ?> de <?= $paginacao['total'] ?> produtos
                        </span>
                        <div class="zf-pages">
                            <?php for ($i = 1; $i <= $paginacao['total_paginas']; $i++): ?>
                                <a href="?pagina=<?= $i ?><?= !empty($filtros['busca']) ? '&busca=' . urlencode($filtros['busca']) : '' ?>"
                                    class="zf-page-btn <?= $i === $paginacao['pagina_atual'] ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div><?php require VIEW_PATH . '/layouts/footer.php'; ?>