<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">

        <?php
        $pageTitle  = 'Relatório de Vendas';
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Vendas',    'url' => '/vendas'],
            ['label' => 'Relatório', 'url' => '#'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <!-- Filtro de período -->
            <form method="GET" action="/vendas/relatorio"
                style="background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:10px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
                <div>
                    <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:5px;">De</label>
                    <input type="date" name="data_de" class="form-control" value="<?= e($data_de) ?>">
                </div>
                <div>
                    <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:5px;">Até</label>
                    <input type="date" name="data_ate" class="form-control" value="<?= e($data_ate) ?>">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search"></i> Gerar
                </button>
                <a href="/vendas?data_de=<?= e($data_de) ?>&data_ate=<?= e($data_ate) ?>" class="btn btn-outline">
                    <i class="ti ti-list"></i> Ver Vendas
                </a>
            </form>

            <!-- KPIs -->
            <div class="zf-stats" style="margin-bottom:20px;">
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Faturamento</div>
                    <div class="zf-stat-value success">
                        R$ <?= number_format($totais['faturamento'] ?? 0, 2, ',', '.') ?>
                    </div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Vendas Finalizadas</div>
                    <div class="zf-stat-value"><?= (int)($totais['total_vendas'] ?? 0) ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Ticket Médio</div>
                    <div class="zf-stat-value">
                        R$ <?= number_format($totais['ticket_medio'] ?? 0, 2, ',', '.') ?>
                    </div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Descontos Concedidos</div>
                    <div class="zf-stat-value <?= ($totais['total_descontos'] ?? 0) > 0 ? 'danger' : '' ?>">
                        R$ <?= number_format($totais['total_descontos'] ?? 0, 2, ',', '.') ?>
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">

                <!-- Faturamento por forma de pagamento -->
                <div class="zf-table-card">
                    <div style="padding:14px 18px;font-weight:600;font-size:14px;border-bottom:1px solid #f3f4f6;">
                        <i class="ti ti-credit-card"></i> Por Forma de Pagamento
                    </div>
                    <table class="zf-table">
                        <thead>
                            <tr>
                                <th>Forma</th>
                                <th style="text-align:center;width:80px">Vendas</th>
                                <th style="text-align:right;width:120px">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($por_forma)): ?>
                                <tr>
                                    <td colspan="3" class="td-empty">Sem dados no período.</td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $nomesPgto = [
                                    'dinheiro'       => '💵 Dinheiro',
                                    'pix'            => '⚡ PIX',
                                    'cartao_debito'  => '💳 Débito',
                                    'cartao_credito' => '💳 Crédito',
                                    'voucher'        => '🎟 Voucher',
                                    'outros'         => '• Outros',
                                ];
                                $totalGeral = array_sum(array_column($por_forma, 'total_recebido'));
                                ?>
                                <?php foreach ($por_forma as $row): ?>
                                    <?php $perc = $totalGeral > 0 ? round($row['total_recebido'] / $totalGeral * 100, 1) : 0; ?>
                                    <tr>
                                        <td>
                                            <?= e($nomesPgto[$row['forma']] ?? $row['forma']) ?>
                                            <div style="height:3px;background:#f3f4f6;border-radius:2px;margin-top:4px;">
                                                <div style="height:3px;background:#1A1A1A;border-radius:2px;width:<?= $perc ?>%;"></div>
                                            </div>
                                        </td>
                                        <td class="text-center text-sm"><?= (int)$row['qtd_vendas'] ?></td>
                                        <td style="text-align:right;font-weight:600;">
                                            R$ <?= number_format($row['total_recebido'], 2, ',', '.') ?>
                                            <div class="text-muted text-sm"><?= $perc ?>%</div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Top produtos -->
                <div class="zf-table-card">
                    <div style="padding:14px 18px;font-weight:600;font-size:14px;border-bottom:1px solid #f3f4f6;">
                        <i class="ti ti-trophy"></i> Top 10 Produtos
                    </div>
                    <table class="zf-table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th style="text-align:center;width:70px">Qtd</th>
                                <th style="text-align:right;width:110px">Faturamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($top_produtos)): ?>
                                <tr>
                                    <td colspan="3" class="td-empty">Sem dados no período.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($top_produtos as $i => $prod): ?>
                                    <tr>
                                        <td>
                                            <div class="td-name" style="display:flex;align-items:center;gap:6px;">
                                                <span style="background:#f3f4f6;border-radius:4px;padding:1px 5px;font-size:10px;font-weight:700;color:#6B7280;">
                                                    #<?= $i + 1 ?>
                                                </span>
                                                <?= e($prod['produto_nome']) ?>
                                            </div>
                                            <div class="td-sub"><?= e($prod['produto_codigo']) ?></div>
                                        </td>
                                        <td class="text-center text-sm">
                                            <?= number_format($prod['qtd_vendida'], 0, ',', '.') ?>
                                        </td>
                                        <td style="text-align:right;font-weight:600;">
                                            R$ <?= number_format($prod['faturamento'], 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Faturamento por dia -->
            <div class="zf-table-card">
                <div style="padding:14px 18px;font-weight:600;font-size:14px;border-bottom:1px solid #f3f4f6;">
                    <i class="ti ti-calendar-stats"></i> Faturamento por Dia
                </div>

                <?php if (!empty($por_dia)): ?>
                    <?php
                    $maxFat = max(array_column($por_dia, 'faturamento'));
                    ?>
                    <div style="padding:16px 20px; overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:13px;">
                            <thead>
                                <tr style="border-bottom:1px solid #f3f4f6;">
                                    <th style="text-align:left;padding:6px 8px;font-weight:600;color:#6B7280;font-size:11px;text-transform:uppercase;width:110px;">Data</th>
                                    <th style="padding:6px 8px;text-align:center;font-weight:600;color:#6B7280;font-size:11px;text-transform:uppercase;width:70px;">Vendas</th>
                                    <th style="padding:6px 8px;font-weight:600;color:#6B7280;font-size:11px;text-transform:uppercase;">Volume</th>
                                    <th style="text-align:right;padding:6px 8px;font-weight:600;color:#6B7280;font-size:11px;text-transform:uppercase;width:120px;">Faturamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($por_dia as $dia): ?>
                                    <?php $perc = $maxFat > 0 ? round($dia['faturamento'] / $maxFat * 100) : 0; ?>
                                    <tr style="border-bottom:1px solid #f9fafb;">
                                        <td style="padding:8px;">
                                            <?= date('d/m/Y', strtotime($dia['dia'])) ?>
                                            <div class="text-muted" style="font-size:10px;">
                                                <?= ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'][date('w', strtotime($dia['dia']))] ?>
                                            </div>
                                        </td>
                                        <td style="text-align:center;padding:8px;"><?= (int)$dia['qtd_vendas'] ?></td>
                                        <td style="padding:8px;">
                                            <div style="background:#f3f4f6;border-radius:4px;height:8px;overflow:hidden;">
                                                <div style="background:#1A1A1A;height:100%;width:<?= $perc ?>%;border-radius:4px;"></div>
                                            </div>
                                        </td>
                                        <td style="text-align:right;padding:8px;font-weight:600;">
                                            R$ <?= number_format($dia['faturamento'], 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="border-top:2px solid #e5e7eb;font-weight:700;">
                                    <td style="padding:10px 8px;">Total</td>
                                    <td style="text-align:center;padding:10px 8px;">
                                        <?= array_sum(array_column($por_dia, 'qtd_vendas')) ?>
                                    </td>
                                    <td></td>
                                    <td style="text-align:right;padding:10px 8px;">
                                        R$ <?= number_format(array_sum(array_column($por_dia, 'faturamento')), 2, ',', '.') ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="td-empty" style="padding:32px;">Nenhuma venda finalizada no período selecionado.</div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>