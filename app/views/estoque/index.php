<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">

        <?php
        $pageTitle  = 'Movimentações de Estoque';
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Estoque',   'url' => '/estoque'],
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
            <form method="GET" action="/estoque" class="zf-table-card" style="padding:16px 20px; margin-bottom:16px;">
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; align-items:end;">

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">Buscar produto</label>
                        <input type="search" name="q" class="form-control"
                            value="<?= e($filtros['q']) ?>" placeholder="Nome ou código...">
                    </div>

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">Tipo</label>
                        <select name="tipo" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach ($tipoLabels as $val => $lab): ?>
                                <option value="<?= e($val) ?>" <?= $filtros['tipo'] === $val ? 'selected' : '' ?>>
                                    <?= e($lab) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">Motivo</label>
                        <select name="motivo" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach ($motivoLabels as $val => $lab): ?>
                                <option value="<?= e($val) ?>" <?= $filtros['motivo'] === $val ? 'selected' : '' ?>>
                                    <?= e($lab) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">Operador</label>
                        <select name="usuario_id" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?= (int)$u['id'] ?>"
                                    <?= (int)$filtros['usuario_id'] === (int)$u['id'] ? 'selected' : '' ?>>
                                    <?= e($u['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">De</label>
                        <input type="date" name="de" class="form-control" value="<?= e($filtros['de']) ?>">
                    </div>

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">Até</label>
                        <input type="date" name="ate" class="form-control" value="<?= e($filtros['ate']) ?>">
                    </div>

                    <div style="display:flex; gap:8px;">
                        <button type="submit" class="btn btn-primary" style="flex:1;">
                            <i class="ti ti-search"></i> Filtrar
                        </button>
                        <a href="/estoque" class="btn btn-outline" title="Limpar filtros">
                            <i class="ti ti-x"></i>
                        </a>
                    </div>

                </div>
            </form>

            <!-- Tabela -->
            <div class="zf-table-card">

                <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-bottom:1px solid var(--border);">
                    <span style="font-size:13px; color:var(--text-secondary);">
                        <?= number_format($resultado['total']) ?> registro(s) encontrado(s)
                    </span>
                    <a href="/estoque/movimentar" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus"></i> Nova Movimentação
                    </a>
                </div>

                <table class="zf-table">
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th style="width:130px">Data/Hora</th>
                            <th>Produto</th>
                            <th style="width:90px;text-align:center">Tipo</th>
                            <th>Motivo</th>
                            <th style="width:110px;text-align:right">Quantidade</th>
                            <th style="width:160px">Estoque</th>
                            <th>Observação</th>
                            <th>Operador</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resultado['dados'])): ?>
                            <tr>
                                <td colspan="9" class="td-empty">
                                    <i class="ti ti-package-off" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3"></i>
                                    Nenhuma movimentação encontrada para os filtros selecionados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($resultado['dados'] as $mov): ?>
                                <?php
                                $tipo     = $mov['tipo'];
                                $badgeCls = match ($tipo) {
                                    'ENTRADA' => 'badge-success',
                                    'SAIDA'   => 'badge-danger',
                                    default   => 'badge-neutral',
                                };
                                $sinal = match ($tipo) {
                                    'ENTRADA' => '+',
                                    'SAIDA'   => '−',
                                    default   => '↔',
                                };
                                $corQty = match ($tipo) {
                                    'ENTRADA' => 'color:#15803D',
                                    'SAIDA'   => 'color:#B91C1C',
                                    default   => 'color:#1D4ED8',
                                };
                                ?>
                                <tr>
                                    <td><span class="td-code">#<?= (int)$mov['id'] ?></span></td>
                                    <td class="text-sm text-muted">
                                        <?= date('d/m/Y', strtotime($mov['criado_em'])) ?><br>
                                        <?= date('H:i', strtotime($mov['criado_em'])) ?>
                                    </td>
                                    <td>
                                        <span style="font-weight:500;"><?= e($mov['produto_nome']) ?></span><br>
                                        <span class="text-sm text-muted"><?= e($mov['produto_codigo']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $badgeCls ?>">
                                            <?= e($tipoLabels[$tipo] ?? $tipo) ?>
                                        </span>
                                    </td>
                                    <td class="text-sm"><?= e($motivoLabels[$mov['motivo']] ?? $mov['motivo']) ?></td>
                                    <td style="text-align:right; font-weight:600; font-variant-numeric:tabular-nums; <?= $corQty ?>">
                                        <?= $sinal ?> <?= number_format($mov['quantidade'], 3, ',', '.') ?>
                                        <span class="text-sm text-muted" style="font-weight:400;">
                                            <?= e($mov['unidade_sigla'] ?? 'UN') ?>
                                        </span>
                                    </td>
                                    <td class="text-sm">
                                        <span style="color:var(--text-secondary);">
                                            <?= number_format($mov['estoque_antes'],  3, ',', '.') ?>
                                        </span>
                                        <i class="ti ti-arrow-right" style="font-size:11px; color:var(--text-tertiary); margin:0 3px;"></i>
                                        <span style="font-weight:600;">
                                            <?= number_format($mov['estoque_depois'], 3, ',', '.') ?>
                                        </span>
                                    </td>
                                    <td class="text-sm text-muted" style="max-width:180px;">
                                        <?php if (!empty($mov['numero_nf'])): ?>
                                            <span class="text-sm" style="color:var(--text-tertiary);">NF <?= e($mov['numero_nf']) ?> · </span>
                                        <?php endif; ?>
                                        <?= e($mov['observacao'] ?? '—') ?>
                                    </td>
                                    <td class="text-sm"><?= e($mov['usuario_nome'] ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Paginação -->
                <?php if ($resultado['paginas'] > 1): ?>
                    <div class="zf-pagination">
                        <span>
                            <?= $resultado['total'] ?> registros — página <?= $resultado['pagina'] ?> de <?= $resultado['paginas'] ?>
                        </span>
                        <div class="zf-pages">
                            <?php
                            $qs = http_build_query(array_filter(array_merge($filtros, ['pagina' => null])));
                            ?>
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