<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">

        <?php
        $pageTitle  = 'Clientes';
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Clientes'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <?php if ($f = \App\Core\Session::getFlash('success')): ?>
                <div class="zf-alert zf-alert-success" data-auto-close>
                    <i class="ti ti-circle-check"></i> <?= e($f) ?>
                </div>
            <?php endif; ?>
            <?php if ($f = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close>
                    <i class="ti ti-alert-circle"></i> <?= e($f) ?>
                </div>
            <?php endif; ?>

            <!-- Filtros -->
            <form method="GET" action="/clientes" class="zf-table-card" style="padding:16px 20px; margin-bottom:16px;">
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; align-items:end;">

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">Buscar</label>
                        <input type="search" name="q" class="form-control"
                            value="<?= e($filtros['q']) ?>"
                            placeholder="Nome, CPF/CNPJ, telefone...">
                    </div>

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">Tipo</label>
                        <select name="tipo_pessoa" class="form-control">
                            <option value="">Todos</option>
                            <option value="fisica" <?= $filtros['tipo_pessoa'] === 'fisica'   ? 'selected' : '' ?>>Pessoa Física</option>
                            <option value="juridica" <?= $filtros['tipo_pessoa'] === 'juridica' ? 'selected' : '' ?>>Pessoa Jurídica</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">Status</label>
                        <select name="ativo" class="form-control">
                            <option value="">Todos</option>
                            <option value="1" <?= $filtros['ativo'] === '1' ? 'selected' : '' ?>>Ativo</option>
                            <option value="0" <?= $filtros['ativo'] === '0' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>

                    <div style="display:flex; gap:8px;">
                        <button type="submit" class="btn btn-primary" style="flex:1;">
                            <i class="ti ti-search"></i> Filtrar
                        </button>
                        <a href="/clientes" class="btn btn-outline" title="Limpar filtros">
                            <i class="ti ti-x"></i>
                        </a>
                    </div>

                </div>
            </form>

            <!-- Tabela -->
            <div class="zf-table-card">

                <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-bottom:1px solid var(--border);">
                    <span style="font-size:13px; color:var(--text-secondary);">
                        <?= number_format($resultado['total']) ?> cliente(s) encontrado(s)
                    </span>
                    <a href="/clientes/criar" class="btn btn-primary btn-sm">
                        <i class="ti ti-user-plus"></i> Novo Cliente
                    </a>
                </div>

                <table class="zf-table">
                    <thead>
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Nome</th>
                            <th style="width:130px">CPF/CNPJ</th>
                            <th style="width:130px">Telefone</th>
                            <th style="width:150px">Cidade/UF</th>
                            <th style="width:70px;text-align:center">Compras</th>
                            <th style="width:110px;text-align:right">Total gasto</th>
                            <th style="width:130px">Última compra</th>
                            <th style="width:80px;text-align:center">Status</th>
                            <th style="width:90px;text-align:center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resultado['dados'])): ?>
                            <tr>
                                <td colspan="10" class="td-empty">
                                    <i class="ti ti-users-off" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3"></i>
                                    Nenhum cliente encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($resultado['dados'] as $c): ?>
                                <tr>
                                    <td class="text-sm text-muted"><?= (int)$c['id'] ?></td>
                                    <td>
                                        <a href="/clientes/<?= (int)$c['id'] ?>" style="font-weight:500; text-decoration:none; color:inherit;">
                                            <?= e($c['nome']) ?>
                                        </a>
                                        <?php if (!empty($c['nome_fantasia'])): ?>
                                            <br><span class="text-sm text-muted"><?= e($c['nome_fantasia']) ?></span>
                                        <?php endif; ?>
                                        <span class="badge badge-neutral" style="font-size:10px; margin-left:4px;">
                                            <?= $c['tipo_pessoa'] === 'juridica' ? 'PJ' : 'PF' ?>
                                        </span>
                                    </td>
                                    <td class="text-sm"><?= e($c['cpf_cnpj'] ? formatarCpfCnpj($c['cpf_cnpj']) : '—') ?></td>
                                    <td class="text-sm"><?= e($c['celular'] ?? $c['telefone'] ?? '—') ?></td>
                                    <td class="text-sm text-muted">
                                        <?= $c['cidade'] ? e($c['cidade']) . '/' . e($c['uf']) : '—' ?>
                                    </td>
                                    <td class="text-center text-sm"><?= (int)$c['total_compras'] ?></td>
                                    <td style="text-align:right; font-weight:600; font-size:13px;">
                                        R$ <?= number_format($c['total_gasto'], 2, ',', '.') ?>
                                    </td>
                                    <td class="text-sm text-muted">
                                        <?= $c['ultima_compra']
                                            ? date('d/m/Y', strtotime($c['ultima_compra']))
                                            : '—' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($c['ativo']): ?>
                                            <span class="badge badge-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="td-actions">
                                        <a href="/clientes/<?= (int)$c['id'] ?>" class="act-link" title="Ver detalhes">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <a href="/clientes/<?= (int)$c['id'] ?>/editar" class="act-link" title="Editar">
                                            <i class="ti ti-pencil"></i>
                                        </a>
                                        <?php if ($c['id'] != 1): ?>
                                            <form method="POST" action="/clientes/<?= (int)$c['id'] ?>/status"
                                                style="display:inline;"
                                                onsubmit="return confirm('<?= $c['ativo'] ? 'Desativar' : 'Reativar' ?> este cliente?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="act-link" title="<?= $c['ativo'] ? 'Desativar' : 'Reativar' ?>">
                                                    <i class="ti ti-<?= $c['ativo'] ? 'ban' : 'circle-check' ?>"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Paginação -->
                <?php if ($resultado['paginas'] > 1): ?>
                    <div class="zf-pagination">
                        <span>
                            <?= $resultado['total'] ?> clientes — página <?= $resultado['pagina'] ?> de <?= $resultado['paginas'] ?>
                        </span>
                        <div class="zf-pages">
                            <?php $qs = http_build_query(array_filter(array_merge($filtros, ['pagina' => null]))); ?>
                            <?php for ($i = 1; $i <= $resultado['paginas']; $i++): ?>
                                <a href="?<?= $qs ?>&pagina=<?= $i ?>"
                                    class="zf-page-btn <?= $i === $resultado['pagina'] ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div><!-- /zf-table-card -->

        </div><!-- /zf-content -->
    </div><!-- /zf-main -->
</div><!-- /zf-layout -->

<?php require VIEW_PATH . '/layouts/footer.php'; ?>