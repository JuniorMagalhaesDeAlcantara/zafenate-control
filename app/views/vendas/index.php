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

            <!-- ── Cards de resumo ── -->
            <div class="zf-stats mb-20">
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Faturamento</div>
                    <div class="zf-stat-value success" style="font-size:20px">
                        R$ <?= number_format($totais['faturamento'] ?? 0, 2, ',', '.') ?>
                    </div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Vendas</div>
                    <div class="zf-stat-value"><?= (int)($totais['total_vendas'] ?? 0) ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Ticket médio</div>
                    <div class="zf-stat-value" style="font-size:20px">
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

            <!-- ── Filtros ── -->
            <form method="GET" action="/vendas"
                class="zf-form-card mb-20"
                style="padding:14px 16px">
                <div style="display:grid;
                             grid-template-columns:repeat(auto-fit,minmax(130px,1fr));
                             gap:10px;align-items:end">

                    <div class="form-group">
                        <label class="form-label">Nº venda</label>
                        <input type="number" name="numero" class="form-control"
                            value="<?= e($filtros['numero']) ?>" placeholder="Ex: 42">
                    </div>

                    <div class="form-group">
                        <label class="form-label">De</label>
                        <input type="date" name="data_de" class="form-control"
                            value="<?= e($filtros['data_de']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Até</label>
                        <input type="date" name="data_ate" class="form-control"
                            value="<?= e($filtros['data_ate']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="finalizada" <?= ($filtros['status'] ?? '') === 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
                            <option value="cancelada" <?= ($filtros['status'] ?? '') === 'cancelada'  ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pagamento</label>
                        <select name="forma_pagamento" class="form-control">
                            <option value="">Todas</option>
                            <option value="dinheiro" <?= ($filtros['forma_pagamento'] ?? '') === 'dinheiro'       ? 'selected' : '' ?>>Dinheiro</option>
                            <option value="pix" <?= ($filtros['forma_pagamento'] ?? '') === 'pix'            ? 'selected' : '' ?>>PIX</option>
                            <option value="cartao_debito" <?= ($filtros['forma_pagamento'] ?? '') === 'cartao_debito'  ? 'selected' : '' ?>>Débito</option>
                            <option value="cartao_credito" <?= ($filtros['forma_pagamento'] ?? '') === 'cartao_credito' ? 'selected' : '' ?>>Crédito</option>
                            <option value="fiado" <?= ($filtros['forma_pagamento'] ?? '') === 'fiado'          ? 'selected' : '' ?>>A Prazo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Operador</label>
                        <select name="usuario_id" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach ($operadores as $op): ?>
                                <option value="<?= $op['id'] ?>"
                                    <?= (int)($filtros['usuario_id'] ?? 0) === (int)$op['id'] ? 'selected' : '' ?>>
                                    <?= e($op['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display:flex;gap:6px;padding-bottom:1px">
                        <button type="submit" class="btn btn-primary" style="flex:1">
                            <i class="ti ti-search"></i> Filtrar
                        </button>
                        <a href="/vendas" class="btn btn-outline" title="Limpar filtros">
                            <i class="ti ti-x"></i>
                        </a>
                    </div>

                </div>
            </form>

            <!-- ── Tabela ── -->
            <div class="zf-table-card">
                <table class="zf-table" style="table-layout:fixed">
                    <colgroup>
                        <col style="width:64px">
                        <col style="width:120px">
                        <col>
                        <col style="width:100px">
                        <col style="width:130px">
                        <col style="width:52px">
                        <col style="width:110px">
                        <col style="width:90px">
                        <col style="width:88px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Data / hora</th>
                            <th>Cliente</th>
                            <th>Operador</th>
                            <th>Pagamento</th>
                            <th style="text-align:center">Itens</th>
                            <th style="text-align:right">Total</th>
                            <th style="text-align:center">Status</th>
                            <th style="text-align:center">Ações</th>
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
                            <?php
                            $labels = [
                                'dinheiro'       => 'Dinheiro',
                                'pix'            => 'PIX',
                                'cartao_debito'  => 'Débito',
                                'cartao_credito' => 'Crédito',
                                'fiado'          => 'A Prazo',
                            ];
                            foreach ($resultado['dados'] as $v):
                                $formas     = array_filter(explode(',', $v['formas_pagamento'] ?? ''));
                                $temFiado   = in_array('fiado', $formas);
                                $vencimento = $v['fiado_vencimento'] ?? null;
                            ?>
                                <tr>
                                    <td>
                                        <span class="td-code">#<?= $v['numero'] ?></span>
                                    </td>
                                    <td>
                                        <span class="text-muted text-sm">
                                            <?= date('d/m H:i', strtotime($v['criado_em'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="td-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                            <?= e($v['cliente_nome'] ?? 'Consumidor Final') ?>
                                        </div>
                                        <?php if ($temFiado && $vencimento): ?>
                                            <div style="font-size:11px;color:var(--color-warning);
                                                         display:flex;align-items:center;gap:3px;margin-top:2px">
                                                <i class="ti ti-clock" style="font-size:10px"></i>
                                                Vence <?= date('d/m/Y', strtotime($vencimento)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-sm text-muted"><?= e($v['operador_nome'] ?? '—') ?></span>
                                    </td>
                                    <td>
                                        <div style="display:flex;flex-wrap:wrap;gap:3px">
                                            <?php foreach ($formas as $forma): ?>
                                                <span class="badge <?= $forma === 'fiado' ? 'badge-warning' : 'badge-neutral' ?>"
                                                    style="font-size:10px">
                                                    <?= e($labels[$forma] ?? $forma) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td style="text-align:center" class="text-sm">
                                        <?= (int)$v['qtd_itens'] ?>
                                    </td>
                                    <td style="text-align:right;font-weight:500">
                                        R$ <?= number_format($v['total'], 2, ',', '.') ?>
                                    </td>
                                    <td style="text-align:center">
                                        <?php if ($v['status'] === 'finalizada'): ?>
                                            <span class="badge badge-success">Finalizada</span>
                                        <?php elseif ($v['status'] === 'cancelada'): ?>
                                            <span class="badge badge-danger">Cancelada</span>
                                        <?php else: ?>
                                            <span class="badge badge-neutral"><?= e($v['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:4px;justify-content:center">
                                            <a href="/vendas/<?= $v['id'] ?>"
                                                class="btn btn-outline btn-sm" title="Ver detalhes">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            <a href="/vendas/<?= $v['id'] ?>/cupom"
                                                target="_blank"
                                                class="btn btn-outline btn-sm" title="Imprimir cupom">
                                                <i class="ti ti-printer"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Paginação -->
                <?php if (($resultado['total_paginas'] ?? 1) > 1): ?>
                    <div class="zf-pagination">
                        <span>
                            <?= $resultado['total'] ?> vendas —
                            página <?= $resultado['pagina'] ?> de <?= $resultado['total_paginas'] ?>
                        </span>
                        <div class="zf-pages">
                            <?php
                            $pagAtual  = $resultado['pagina'];
                            $pagTotal  = $resultado['total_paginas'];
                            $qsBase    = http_build_query(array_diff_key($filtros, ['pagina' => '']));

                            // Mostra no máximo 7 botões + reticências
                            $range = [];
                            for ($i = 1; $i <= $pagTotal; $i++) {
                                if (
                                    $i === 1 || $i === $pagTotal
                                    || ($i >= $pagAtual - 1 && $i <= $pagAtual + 1)
                                ) {
                                    $range[] = $i;
                                }
                            }
                            $prev = null;
                            foreach ($range as $pg):
                                if ($prev !== null && $pg - $prev > 1): ?>
                                    <span style="padding:0 4px;color:var(--text-tertiary);font-size:12px">…</span>
                                <?php endif; ?>
                                <a href="?<?= $qsBase ?>&pagina=<?= $pg ?>"
                                    class="zf-page-btn <?= $pg === $pagAtual ? 'active' : '' ?>">
                                    <?= $pg ?>
                                </a>
                            <?php $prev = $pg;
                            endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ── Link de relatório ── -->
            <a href="/vendas/relatorio?data_de=<?= e($filtros['data_de']) ?>&data_ate=<?= e($filtros['data_ate']) ?>"
                style="display:flex;align-items:center;justify-content:space-between;
                      padding:14px 18px;background:#1A1A1A;color:#fff;
                      border-radius:var(--radius-md);text-decoration:none;
                      margin-top:16px;transition:opacity .15s"
                onmouseover="this.style.opacity='.85'"
                onmouseout="this.style.opacity='1'">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:32px;height:32px;border-radius:var(--radius-sm);
                                 background:rgba(255,255,255,.1);
                                 display:flex;align-items:center;justify-content:center">
                        <i class="ti ti-chart-bar" style="font-size:16px"></i>
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:500">Relatório do período</div>
                        <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:1px">
                            <?= date('d/m/Y', strtotime($filtros['data_de'])) ?>
                            a
                            <?= date('d/m/Y', strtotime($filtros['data_ate'])) ?>
                        </div>
                    </div>
                </div>
                <i class="ti ti-arrow-right" style="font-size:15px;opacity:.5"></i>
            </a>

        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>