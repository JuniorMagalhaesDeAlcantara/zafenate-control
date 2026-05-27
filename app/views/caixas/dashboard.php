<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>

    <div class="zf-main">
        <?php
        $pageTitle  = 'Caixa';
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Caixa',     'url' => '/caixa'],
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

              <!-- ── Ações rápidas ── -->
            <div style="margin-bottom:20px">
                <div style="font-size:13px;font-weight:500;color:var(--text-secondary);margin-bottom:12px">Ações Rápidas</div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px">

                    <?php if (!$caixaAberto): ?>

                        <a href="/caixa/gestao" style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:18px 16px;text-decoration:none;color:var(--text-primary);display:flex;flex-direction:column;gap:10px;transition:border-color .15s" onmouseover="this.style.borderColor='var(--border-md)'" onmouseout="this.style.borderColor='var(--border)'">
                            <i class="ti ti-cash-register" style="font-size:20px;color:var(--text-secondary)"></i>
                            <span style="font-size:13px;font-weight:500">Abrir Caixa</span>
                        </a>

                    <?php else: ?>

                        <a href="/pdv" style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:18px 16px;text-decoration:none;color:var(--text-primary);display:flex;flex-direction:column;gap:10px;transition:border-color .15s" onmouseover="this.style.borderColor='var(--border-md)'" onmouseout="this.style.borderColor='var(--border)'">
                            <i class="ti ti-shopping-cart" style="font-size:20px;color:var(--text-secondary)"></i>
                            <span style="font-size:13px;font-weight:500">Ir para PDV</span>
                        </a>

                        <a href="/caixa/sangria" style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:18px 16px;text-decoration:none;color:var(--text-primary);display:flex;flex-direction:column;gap:10px;transition:border-color .15s" onmouseover="this.style.borderColor='var(--border-md)'" onmouseout="this.style.borderColor='var(--border)'">
                            <i class="ti ti-arrow-bar-down" style="font-size:20px;color:var(--text-secondary)"></i>
                            <span style="font-size:13px;font-weight:500">Sangria / Suprimento</span>
                        </a>

                        <a href="/caixa/gestao" style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:18px 16px;text-decoration:none;color:var(--text-primary);display:flex;flex-direction:column;gap:10px;transition:border-color .15s" onmouseover="this.style.borderColor='var(--border-md)'" onmouseout="this.style.borderColor='var(--border)'">
                            <i class="ti ti-lock" style="font-size:20px;color:var(--text-secondary)"></i>
                            <span style="font-size:13px;font-weight:500">Fechar Caixa</span>
                        </a>

                    <?php endif; ?>

                </div>
            </div>

            <!-- ── Cards de totais ── -->
            <?php
            $totalVendas      = array_sum(array_column($caixas, 'total_vendas'));
            $totalSangrias    = array_sum(array_column($caixas, 'total_sangrias'));
            $totalSuprimentos = array_sum(array_column($caixas, 'total_suprimentos'));
            $totalDivergencia = $total_diferenca ?? 0;
            $totalCaixas      = count($caixas);
            ?>
            <div class="zf-stats mb-20">
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Total em vendas</div>
                    <div class="zf-stat-value">R$ <?= number_format($totalVendas, 2, ',', '.') ?></div>
                    <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px">no período filtrado</div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Total sangrias</div>
                    <div class="zf-stat-value warning">R$ <?= number_format($totalSangrias, 2, ',', '.') ?></div>
                    <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px">retiradas da gaveta</div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Total suprimentos</div>
                    <div class="zf-stat-value success">R$ <?= number_format($totalSuprimentos, 2, ',', '.') ?></div>
                    <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px">entradas manuais</div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Fora do esperado</div>
                    <div class="zf-stat-value <?= $totalDivergencia < 0 ? 'danger' : ($totalDivergencia > 0 ? 'warning' : '') ?>">
                        R$ <?= number_format($totalDivergencia, 2, ',', '.') ?>
                    </div>
                    <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px">soma das diferenças</div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Caixas auditados</div>
                    <div class="zf-stat-value"><?= $totalCaixas ?></div>
                    <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px">no período</div>
                </div>
            </div>

            <!-- ── Filtro de período ── -->
            <div class="zf-toolbar mb-20">
                <form method="GET" class="d-flex align-center gap-8" style="flex:1;flex-wrap:wrap">
                    <i class="ti ti-filter" style="font-size:15px;color:var(--text-tertiary)"></i>
                    <label style="font-size:12px;font-weight:500;color:var(--text-secondary)">De</label>
                    <input
                        type="date"
                        name="data_inicio"
                        value="<?= e($filtros['inicio']) ?>"
                        class="form-control"
                        style="width:auto;padding:7px 10px">
                    <span style="font-size:12px;color:var(--text-tertiary)">até</span>
                    <input
                        type="date"
                        name="data_fim"
                        value="<?= e($filtros['fim']) ?>"
                        class="form-control"
                        style="width:auto;padding:7px 10px">
                    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
                </form>
            </div>

            <!-- ── Tabela de histórico ── -->
            <div class="zf-table-card">
                <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:13px;font-weight:500">Histórico de caixas</span>
                    <span style="font-size:11px;color:var(--text-tertiary);background:var(--bg-input);padding:3px 8px;border-radius:20px">
                        <?= $totalCaixas ?> registro<?= $totalCaixas !== 1 ? 's' : '' ?>
                    </span>
                </div>

                <table class="zf-table">
                    <thead>
                        <tr>
                            <th style="width:100px">Data</th>
                            <th style="width:130px">Operador</th>
                            <th style="width:100px">Abertura</th>
                            <th style="width:110px">Vendas</th>
                            <th>Sangria / Suprimento</th>
                            <th style="width:110px">Diferença</th>
                            <th style="width:110px">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($caixas)): ?>
                            <tr>
                                <td colspan="7" class="td-empty">
                                    <i class="ti ti-cash-register" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3"></i>
                                    Nenhum registro encontrado para o período selecionado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($caixas as $c): ?>
                                <?php
                                $dif = (float)($c['diferenca'] ?? 0);
                                $difClass   = $dif == 0    ? 'diff-ok'   : (abs($dif) <= 5 ? 'diff-warn' : 'diff-bad');
                                $pillClass  = $dif == 0    ? 'badge-success' : (abs($dif) <= 5 ? 'badge-warning' : 'badge-danger');
                                $pillIcon   = $dif == 0    ? 'ti-check'      : (abs($dif) <= 5 ? 'ti-alert-triangle' : 'ti-x');
                                $pillLabel  = $dif == 0    ? 'Ok'            : (abs($dif) <= 5 ? 'Atenção'           : 'Divergência');
                                $dataFmt    = !empty($c['fechado_em'])
                                    ? date('d/m/Y', strtotime($c['fechado_em']))
                                    : '—';
                                ?>
                                <tr>
                                    <td>
                                        <span class="text-muted text-sm"><?= $dataFmt ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-500"><?= e($c['operador'] ?? '—') ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-500">R$ <?= number_format($c['saldo_abertura'], 2, ',', '.') ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-500">R$ <?= number_format($c['total_vendas'], 2, ',', '.') ?></span>
                                    </td>
                                    <td>
                                        <span style="color:var(--color-danger);font-size:12px">
                                            <i class="ti ti-arrow-bar-down" style="font-size:11px"></i>
                                            R$ <?= number_format($c['total_sangrias'], 2, ',', '.') ?>
                                        </span>
                                        <span style="color:var(--text-tertiary);margin:0 4px">/</span>
                                        <span style="color:var(--color-success);font-size:12px">
                                            <i class="ti ti-arrow-bar-up" style="font-size:11px"></i>
                                            R$ <?= number_format($c['total_suprimentos'], 2, ',', '.') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="<?= $difClass ?>" style="font-weight:500">
                                            R$ <?= number_format($dif, 2, ',', '.') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $pillClass ?>">
                                            <i class="ti <?= $pillIcon ?>" style="font-size:10px"></i>
                                            <?= $pillLabel ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Estilos locais de diferença (complementam o design system) -->
            <style>
                .diff-ok {
                    color: var(--color-success);
                }

                .diff-warn {
                    color: var(--color-warning);
                }

                .diff-bad {
                    color: var(--color-danger);
                }
            </style>

        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>