<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>

    <div class="zf-main">
        <?php
        $pageTitle  = 'Fornecedores';
        $breadcrumb = [
            ['label' => 'Dashboard',    'url' => '/dashboard'],
            ['label' => 'Fornecedores', 'url' => '/fornecedores'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

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

            <!-- Stats -->
            <div class="zf-stats mb-20">
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Total</div>
                    <div class="zf-stat-value"><?= $totais['total'] ?? 0 ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Ativos</div>
                    <div class="zf-stat-value success"><?= $totais['ativos'] ?? 0 ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Inativos</div>
                    <div class="zf-stat-value"><?= $totais['inativos'] ?? 0 ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Pessoa Jurídica</div>
                    <div class="zf-stat-value"><?= $totais['pj'] ?? 0 ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Pessoa Física</div>
                    <div class="zf-stat-value"><?= $totais['pf'] ?? 0 ?></div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="zf-toolbar mb-20">
                <form action="/fornecedores" method="GET"
                    class="d-flex align-center gap-8" style="flex:1;flex-wrap:wrap">

                    <div class="zf-search-wrap">
                        <i class="ti ti-search"></i>
                        <input class="zf-search" type="text" name="busca"
                            value="<?= e($filtros['busca'] ?? '') ?>"
                            placeholder="Buscar por nome, CNPJ, telefone...">
                    </div>

                    <select name="tipo_pessoa" class="form-control"
                        style="width:auto;padding:8px 10px">
                        <option value="">Todos</option>
                        <option value="juridica" <?= ($filtros['tipo_pessoa'] ?? '') === 'juridica' ? 'selected' : '' ?>>Pessoa Jurídica</option>
                        <option value="fisica" <?= ($filtros['tipo_pessoa'] ?? '') === 'fisica'   ? 'selected' : '' ?>>Pessoa Física</option>
                    </select>

                    <select name="ativo" class="form-control"
                        style="width:auto;padding:8px 10px">
                        <option value="">Todos os status</option>
                        <option value="1" <?= ($filtros['ativo'] ?? '') === '1' ? 'selected' : '' ?>>Ativo</option>
                        <option value="0" <?= ($filtros['ativo'] ?? '') === '0' ? 'selected' : '' ?>>Inativo</option>
                    </select>

                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>

                    <?php if (!empty($filtros['busca']) || $filtros['ativo'] !== '' || $filtros['tipo_pessoa'] !== ''): ?>
                        <a href="/fornecedores" class="btn btn-outline btn-sm"
                            style="color:var(--color-danger)">
                            <i class="ti ti-x"></i> Limpar
                        </a>
                    <?php endif; ?>
                </form>

                <a href="/fornecedores/criar" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Novo fornecedor
                </a>
            </div>

            <!-- Tabela -->
            <div class="zf-table-card">
                <table class="zf-table">
                    <thead>
                        <tr>
                            <th>Fornecedor</th>
                            <th style="width:140px">CNPJ / CPF</th>
                            <th style="width:130px">Contato</th>
                            <th style="width:80px">Prazo</th>
                            <th style="width:90px">Compras</th>
                            <th style="width:90px">Avaliação</th>
                            <th style="width:80px">Status</th>
                            <th style="width:110px;text-align:center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($fornecedores)): ?>
                            <tr>
                                <td colspan="8" class="td-empty">
                                    <i class="ti ti-truck-off" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3"></i>
                                    <?= !empty($filtros['busca'])
                                        ? 'Nenhum fornecedor encontrado para "' . e($filtros['busca']) . '"'
                                        : 'Nenhum fornecedor cadastrado ainda.' ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($fornecedores as $f): ?>
                                <?php
                                $media = array_filter([
                                    $f['avaliacao_prazo'] ?? null,
                                    $f['avaliacao_qualidade'] ?? null,
                                    $f['avaliacao_atendimento'] ?? null,
                                ]);
                                $avaliacaoMedia = $media ? round(array_sum($media) / count($media), 1) : null;
                                ?>
                                <tr>
                                    <td>
                                        <div class="td-name">
                                            <?= e($f['razao_social']) ?>
                                            <?php if (!empty($f['nome_fantasia'])): ?>
                                                <span class="text-muted" style="font-weight:400">
                                                    — <?= e($f['nome_fantasia']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="td-sub">
                                            <?= $f['tipo_pessoa'] === 'juridica' ? 'PJ' : 'PF' ?>
                                            <?php if (!empty($f['cidade'])): ?>
                                                · <?= e($f['cidade']) ?>/<?= e($f['uf']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="td-code">
                                        <?= !empty($f['cnpj_cpf'])
                                            ? e(formatarDocumento($f['cnpj_cpf']))
                                            : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($f['telefone'])): ?>
                                            <div class="text-sm"><?= e($f['telefone']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($f['email'])): ?>
                                            <div class="td-sub"><?= e($f['email']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-sm text-muted">
                                        <?= $f['prazo_pagamento'] ? $f['prazo_pagamento'] . 'd' : '—' ?>
                                    </td>
                                    <td>
                                        <?php if ($f['total_compras'] > 0): ?>
                                            <div class="fw-500 text-sm"><?= $f['total_compras'] ?> compra<?= $f['total_compras'] != 1 ? 's' : '' ?></div>
                                            <?php if (!empty($f['ultima_compra'])): ?>
                                                <div class="td-sub"><?= date('d/m/Y', strtotime($f['ultima_compra'])) ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted text-sm">Sem compras</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($avaliacaoMedia !== null): ?>
                                            <span style="font-size:13px">
                                                <?= str_repeat('★', (int)round($avaliacaoMedia)) ?>
                                                <?= str_repeat('☆', 5 - (int)round($avaliacaoMedia)) ?>
                                            </span>
                                            <div class="td-sub"><?= number_format($avaliacaoMedia, 1) ?>/5</div>
                                        <?php else: ?>
                                            <span class="text-muted text-sm">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $f['ativo'] ? 'badge-success' : 'badge-neutral' ?>">
                                            <?= $f['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </td>
                                    <td class="td-actions">
                                        <a href="/fornecedores/<?= $f['id'] ?>" class="act-link">Ver</a>
                                        <a href="/fornecedores/<?= $f['id'] ?>/editar" class="act-link">Editar</a>
                                        <form action="/fornecedores/<?= $f['id'] ?>/status"
                                            method="POST" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                class="act-btn <?= $f['ativo'] ? '' : 'activate' ?>"
                                                data-confirm="<?= $f['ativo'] ? 'Desativar este fornecedor?' : 'Ativar este fornecedor?' ?>">
                                                <?= $f['ativo'] ? 'Desativar' : 'Ativar' ?>
                                            </button>
                                        </form>
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