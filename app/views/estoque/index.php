<?php require VIEW_PATH . '/layouts/header.php'; ?>

<style>
    /* ── Estoque: estilos de página ── */
    .est-page-hdr {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }

    .est-card {
        background: var(--bg-primary, #fff);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        transition: box-shadow .15s ease, border-color .15s ease;
    }

    .est-card:hover {
        border-color: var(--color-border, #d1d5db);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
    }

    /* Filtros */
    .est-filters {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 14px;
        align-items: end;
    }

    .est-filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .est-filter-group label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        letter-spacing: .3px;
    }

    .est-filter-group input,
    .est-filter-group select {
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-secondary, #f9fafb);
        color: var(--text-primary);
        font-size: 13px;
        outline: none;
        transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
        font-family: inherit;
    }

    .est-filter-group input:hover,
    .est-filter-group select:hover {
        border-color: var(--color-border, #d1d5db);
        background: var(--bg-primary, #fff);
    }

    .est-filter-group input:focus,
    .est-filter-group select:focus {
        border-color: var(--color-primary, #2563eb);
        background: var(--bg-primary, #fff);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .06);
    }

    /* Tabela */
    .est-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .est-table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--text-tertiary);
        background: var(--bg-secondary, #f9fafb);
        border-bottom: 1px solid var(--border-color);
    }

    .est-table th:last-child {
        text-align: right;
    }

    .est-table tbody tr {
        transition: background .15s ease;
    }

    .est-table tbody tr:hover {
        background: var(--bg-secondary, #f9fafb);
    }

    .est-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
    }

    .est-table tr:last-child td {
        border-bottom: none;
    }

    /* Badges */
    .est-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid;
    }

    .est-badge.entrada {
        background: #dcfce7;
        color: #15803d;
        border-color: #b7e4c7;
    }

    .est-badge.saida {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fcacac;
    }

    .est-badge.ajuste {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    /* Paginação */
    .est-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-top: 1px solid var(--border-color);
        gap: 20px;
    }

    .est-pages {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .est-page-btn {
        min-width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        background: var(--bg-primary, #fff);
        color: var(--text-primary);
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        transition: all .15s ease;
    }

    .est-page-btn:hover {
        border-color: var(--color-primary, #2563eb);
        color: var(--color-primary, #2563eb);
        background: var(--bg-secondary, #f9fafb);
    }

    .est-page-btn.active {
        background: var(--color-primary, #2563eb);
        color: #fff;
        border-color: var(--color-primary, #2563eb);
        font-weight: 600;
    }

    .est-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 48px 20px;
        color: var(--text-tertiary);
        text-align: center;
    }

    .est-empty-state i {
        font-size: 36px;
        opacity: .3;
        display: block;
        margin-bottom: 12px;
    }
</style>

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

            <!-- Cabeçalho da página -->
            <div class="est-page-hdr">
                <div style="flex: 1;">
                    <h1 style="font-size:28px; font-weight:700; margin:0; letter-spacing:-.4px;">
                        Movimentações de Estoque
                    </h1>
                    <p style="font-size:13px; color:var(--text-tertiary); margin:4px 0 0;">Controle de entradas, saídas e ajustes</p>
                </div>
                <a href="/estoque/movimentar" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Nova Movimentação
                </a>
            </div>

            <!-- Filtros -->
            <form method="GET" action="/estoque" class="est-card" style="margin-bottom:20px;">
                <div class="est-filters">

                    <div class="est-filter-group">
                        <label>Buscar produto</label>
                        <input type="search" name="q"
                            value="<?= e($filtros['q']) ?>" placeholder="Nome ou código...">
                    </div>

                    <div class="est-filter-group">
                        <label>Tipo</label>
                        <select name="tipo">
                            <option value="">Todos</option>
                            <?php foreach ($tipoLabels as $val => $lab): ?>
                                <option value="<?= e($val) ?>" <?= $filtros['tipo'] === $val ? 'selected' : '' ?>>
                                    <?= e($lab) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="est-filter-group">
                        <label>Motivo</label>
                        <select name="motivo">
                            <option value="">Todos</option>
                            <?php foreach ($motivoLabels as $val => $lab): ?>
                                <option value="<?= e($val) ?>" <?= $filtros['motivo'] === $val ? 'selected' : '' ?>>
                                    <?= e($lab) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="est-filter-group">
                        <label>Operador</label>
                        <select name="usuario_id">
                            <option value="">Todos</option>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?= (int)$u['id'] ?>"
                                    <?= (int)$filtros['usuario_id'] === (int)$u['id'] ? 'selected' : '' ?>>
                                    <?= e($u['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="est-filter-group">
                        <label>De</label>
                        <input type="date" name="de" value="<?= e($filtros['de']) ?>">
                    </div>

                    <div class="est-filter-group">
                        <label>Até</label>
                        <input type="date" name="ate" value="<?= e($filtros['ate']) ?>">
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
            <div class="est-card">

                <div style="display:flex; justify-content:space-between; align-items:center; padding:0 0 16px; border-bottom:1px solid var(--border-color);">
                    <span style="font-size:13px; color:var(--text-secondary); font-weight:500;">
                        <?= number_format($resultado['total']) ?> registro(s) encontrado(s)
                    </span>
                </div>

                <table class="est-table">
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th style="width:140px">Data/Hora</th>
                            <th>Produto</th>
                            <th style="width:100px;text-align:center">Tipo</th>
                            <th>Motivo</th>
                            <th style="width:120px;text-align:right">Quantidade</th>
                            <th style="width:160px">Estoque</th>
                            <th>Observação</th>
                            <th>Operador</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resultado['dados'])): ?>
                            <tr>
                                <td colspan="9">
                                    <div class="est-empty-state">
                                        <i class="ti ti-package-off"></i>
                                        <p>Nenhuma movimentação encontrada para os filtros selecionados.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($resultado['dados'] as $mov): ?>
                                <?php
                                $tipo     = $mov['tipo'];
                                $badgeCls = match ($tipo) {
                                    'ENTRADA' => 'entrada',
                                    'SAIDA'   => 'saida',
                                    default   => 'ajuste',
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
                                    <td><span style="font-weight:600; color:var(--text-tertiary);">#<?= (int)$mov['id'] ?></span></td>
                                    <td style="font-size:12px; color:var(--text-tertiary);">
                                        <?= date('d/m/Y', strtotime($mov['criado_em'])) ?><br>
                                        <span style="font-size:11px;"><?= date('H:i', strtotime($mov['criado_em'])) ?></span>
                                    </td>
                                    <td>
                                        <span style="font-weight:500;"><?= e($mov['produto_nome']) ?></span><br>
                                        <span style="font-size:11px; color:var(--text-tertiary);"><?= e($mov['produto_codigo']) ?></span>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="est-badge <?= $badgeCls ?>">
                                            <?= e($tipoLabels[$tipo] ?? $tipo) ?>
                                        </span>
                                    </td>
                                    <td style="font-size:12px; color:var(--text-secondary);"><?= e($motivoLabels[$mov['motivo']] ?? $mov['motivo']) ?></td>
                                    <td style="text-align:right; font-weight:600; font-variant-numeric:tabular-nums; <?= $corQty ?>">
                                        <?= $sinal ?> <?= number_format($mov['quantidade'], 3, ',', '.') ?>
                                        <span style="font-size:11px; color:var(--text-tertiary); font-weight:400;">
                                            <?= e($mov['unidade_sigla'] ?? 'UN') ?>
                                        </span>
                                    </td>
                                    <td style="font-size:12px;">
                                        <span style="color:var(--text-secondary);">
                                            <?= number_format($mov['estoque_antes'],  3, ',', '.') ?>
                                        </span>
                                        <i class="ti ti-arrow-right" style="font-size:11px; color:var(--text-tertiary); margin:0 3px;"></i>
                                        <span style="font-weight:600;">
                                            <?= number_format($mov['estoque_depois'], 3, ',', '.') ?>
                                        </span>
                                    </td>
                                    <td style="font-size:12px; color:var(--text-tertiary); max-width:180px;">
                                        <?php if (!empty($mov['numero_nf'])): ?>
                                            <span style="color:var(--text-tertiary);">NF <?= e($mov['numero_nf']) ?> · </span>
                                        <?php endif; ?>
                                        <?= e($mov['observacao'] ?? '—') ?>
                                    </td>
                                    <td style="font-size:12px;"><?= e($mov['usuario_nome'] ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Paginação -->
                <?php if ($resultado['paginas'] > 1): ?>
                    <div class="est-pagination">
                        <span style="font-size:12px; color:var(--text-secondary);">
                            <?= $resultado['total'] ?> registros — página <?= $resultado['pagina'] ?> de <?= $resultado['paginas'] ?>
                        </span>
                        <div class="est-pages">
                            <?php
                            $qs = http_build_query(array_filter(array_merge($filtros, ['pagina' => null])));
                            ?>
                            <?php for ($i = 1; $i <= $resultado['paginas']; $i++): ?>
                                <a href="?<?= $qs ?>&pagina=<?= $i ?>"
                                    class="est-page-btn <?= $i === $resultado['pagina'] ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div><!-- /est-card -->

        </div><!-- /zf-content -->
    </div><!-- /zf-main -->
</div><!-- /zf-layout -->

<?php require VIEW_PATH . '/layouts/footer.php'; ?>