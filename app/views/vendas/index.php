<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">

        <?php
        $pageTitle  = 'Vendas';
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Vendas',    'url' => '/vendas'],
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

            <!-- Totalizadores -->
            <div class="zf-stats" style="margin-bottom:20px;">
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Faturamento</div>
                    <div class="zf-stat-value success">
                        R$ <?= number_format($totais['faturamento'] ?? 0, 2, ',', '.') ?>
                    </div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Vendas</div>
                    <div class="zf-stat-value"><?= (int)($totais['total_vendas'] ?? 0) ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Ticket Médio</div>
                    <div class="zf-stat-value">
                        R$ <?= number_format($totais['ticket_medio'] ?? 0, 2, ',', '.') ?>
                    </div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Canceladas</div>
                    <div class="zf-stat-value <?= ($totais['canceladas'] ?? 0) > 0 ? 'danger' : '' ?>">
                        <?= (int)($totais['canceladas'] ?? 0) ?>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <form method="GET" action="/vendas" class="zf-table-card" style="padding:16px 20px; margin-bottom:16px;">
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; align-items:end;">

                    <div style="">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">Nº da Venda</label>
                        <input type="number" name="numero" class="form-control"
                            value="<?= e($filtros['numero']) ?>" placeholder="Ex: 42">
                    </div>

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">De</label>
                        <input type="date" name="data_de" class="form-control" value="<?= e($filtros['data_de']) ?>">
                    </div>

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">Até</label>
                        <input type="date" name="data_ate" class="form-control" value="<?= e($filtros['data_ate']) ?>">
                    </div>

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="finalizada" <?= $filtros['status'] === 'finalizada'  ? 'selected' : '' ?>>Finalizada</option>
                            <option value="cancelada" <?= $filtros['status'] === 'cancelada'   ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">Pagamento</label>
                        <select name="forma_pagamento" class="form-control">
                            <option value="">Todas</option>
                            <option value="dinheiro" <?= $filtros['forma_pagamento'] === 'dinheiro'       ? 'selected' : '' ?>>Dinheiro</option>
                            <option value="pix" <?= $filtros['forma_pagamento'] === 'pix'            ? 'selected' : '' ?>>PIX</option>
                            <option value="cartao_debito" <?= $filtros['forma_pagamento'] === 'cartao_debito'  ? 'selected' : '' ?>>Débito</option>
                            <option value="cartao_credito" <?= $filtros['forma_pagamento'] === 'cartao_credito' ? 'selected' : '' ?>>Crédito</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">Operador</label>
                        <select name="usuario_id" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach ($operadores as $op): ?>
                                <option value="<?= $op['id'] ?>" <?= (int)$filtros['usuario_id'] === (int)$op['id'] ? 'selected' : '' ?>>
                                    <?= e($op['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display:flex; gap:8px;">
                        <button type="submit" class="btn btn-primary" style="flex:1;">
                            <i class="ti ti-search"></i> Filtrar
                        </button>
                        <a href="/vendas" class="btn btn-outline" title="Limpar filtros">
                            <i class="ti ti-x"></i>
                        </a>
                    </div>

                </div>
            </form>

            <!-- Tabela -->
            <div class="zf-table-card">
                <table class="zf-table">
                    <thead>
                        <tr>
                            <th style="width:70px">#</th>
                            <th style="width:140px">Data/Hora</th>
                            <th>Cliente</th>
                            <th>Operador</th>
                            <th style="width:100px">Pagamento</th>
                            <th style="width:60px;text-align:center">Itens</th>
                            <th style="width:110px;text-align:right">Total</th>
                            <th style="width:90px;text-align:center">Status</th>
                            <th style="width:100px;text-align:center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resultado['dados'])): ?>
                            <tr>
                                <td colspan="9" class="td-empty">
                                    <i class="ti ti-receipt-off" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3"></i>
                                    Nenhuma venda encontrada para os filtros selecionados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($resultado['dados'] as $v): ?>
                                <tr>
                                    <td><span class="td-code">#<?= $v['numero'] ?></span></td>
                                    <td class="text-sm text-muted"><?= date('d/m/Y H:i', strtotime($v['criado_em'])) ?></td>
                                    <td><?= e($v['cliente_nome'] ?? 'Consumidor Final') ?></td>
                                    <td class="text-sm"><?= e($v['operador_nome'] ?? '—') ?></td>
                                    <td>
                                        <?php
                                        $formas = array_filter(explode(',', $v['formas_pagamento'] ?? ''));
                                        $labels = ['dinheiro' => 'Dinheiro', 'pix' => 'PIX', 'cartao_debito' => 'Débito', 'cartao_credito' => 'Crédito'];
                                        foreach ($formas as $f): ?>
                                            <span class="badge badge-neutral" style="font-size:10px;">
                                                <?= e($labels[$f] ?? $f) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td class="text-center text-sm"><?= (int)$v['qtd_itens'] ?></td>
                                    <td style="text-align:right;font-weight:600;">
                                        R$ <?= number_format($v['total'], 2, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($v['status'] === 'finalizada'): ?>
                                            <span class="badge badge-success">Finalizada</span>
                                        <?php elseif ($v['status'] === 'cancelada'): ?>
                                            <span class="badge badge-danger">Cancelada</span>
                                        <?php else: ?>
                                            <span class="badge badge-neutral"><?= e($v['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="td-actions">
                                        <a href="/vendas/<?= $v['id'] ?>" class="act-link" title="Detalhes">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <a href="/vendas/<?= $v['id'] ?>/cupom" target="_blank"
                                            class="act-link" title="Imprimir cupom">
                                            <i class="ti ti-printer"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Paginação -->
                <?php if ($resultado['total_paginas'] > 1): ?>
                    <div class="zf-pagination">
                        <span>
                            <?= $resultado['total'] ?> vendas encontradas — página <?= $resultado['pagina'] ?> de <?= $resultado['total_paginas'] ?>
                        </span>
                        <div class="zf-pages">
                            <?php for ($i = 1; $i <= $resultado['total_paginas']; $i++): ?>
                                <a href="?<?= http_build_query(array_merge($filtros, ['pagina' => $i])) ?>"
                                    class="zf-page-btn <?= $i === $resultado['pagina'] ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Link relatório -->
            <div style="margin-top:16px;">
                <a href="/vendas/relatorio?data_de=<?= e($filtros['data_de']) ?>&data_ate=<?= e($filtros['data_ate']) ?>"
                    style="display:flex;align-items:center;justify-content:space-between;
              padding:14px 20px;background:#1A1A1A;color:#fff;border-radius:10px;
              text-decoration:none;font-weight:600;font-size:14px;"
                    onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    <span style="display:flex;align-items:center;gap:10px;">
                        <span style="background:rgba(255,255,255,.12);border-radius:7px;
                         width:34px;height:34px;display:flex;align-items:center;justify-content:center;">
                            <i class="ti ti-chart-bar" style="font-size:16px;"></i>
                        </span>
                        <span>
                            <span style="display:block;">Relatório do Período</span>
                            <span style="font-size:11px;font-weight:400;color:rgba(255,255,255,.55);">
                                <?= date('d/m/Y', strtotime($filtros['data_de'])) ?>
                                a
                                <?= date('d/m/Y', strtotime($filtros['data_ate'])) ?>
                            </span>
                        </span>
                    </span>
                    <i class="ti ti-arrow-right" style="font-size:16px;opacity:.6;"></i>
                </a>
            </div>

        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>