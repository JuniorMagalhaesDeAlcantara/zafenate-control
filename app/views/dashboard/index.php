<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">
        <?php
        $breadcrumb = [['label' => 'Dashboard', 'url' => '/dashboard']];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
                <div class="zf-alert zf-alert-success" data-auto-close><i class="ti ti-circle-check"></i> <?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close><i class="ti ti-alert-circle"></i> <?= e($msg) ?></div>
            <?php endif; ?>

            <!-- Saudação -->
            <div style="margin-bottom:28px;">
                <h1 style="font-size:22px;font-weight:500;margin:0 0 4px;">Olá, <?= e($usuario_nome) ?>! 👋</h1>
                <p style="color:var(--text-tertiary);font-size:14px;margin:0;">
                    Aqui está o resumo do seu negócio em <?= date('d \d\e F \d\e Y') ?>
                    <?php if ($caixa_aberto): ?>
                        — <span style="color:var(--color-success,#22c55e);font-weight:500;">
                            <i class="ti ti-cash-register"></i> Caixa aberto por <?= e($caixa_aberto['operador']) ?>
                        </span>
                    <?php else: ?>
                        — <span style="color:var(--color-danger,#ef4444);">
                            <i class="ti ti-lock"></i> Caixa fechado
                        </span>
                    <?php endif; ?>
                </p>
            </div>

            <!-- ─── CARDS PRINCIPAIS ─── -->
            <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:24px;">

                <!-- Faturamento hoje -->
                <div class="zf-table-card" style="padding:20px;position:relative;overflow:hidden;">
                    <div style="font-size:12px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                        <i class="ti ti-trending-up"></i> Faturamento hoje
                    </div>
                    <div style="font-size:26px;font-weight:500;margin-bottom:4px;">
                        R$ <?= number_format($faturamento_hoje, 2, ',', '.') ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-tertiary);">
                        <?= $qtd_vendas_hoje ?> venda<?= $qtd_vendas_hoje != 1 ? 's' : '' ?> • ticket médio R$ <?= number_format($ticket_medio, 2, ',', '.') ?>
                    </div>
                    <i class="ti ti-cash" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.08;"></i>
                </div>

                <!-- Lucro bruto hoje -->
                <div class="zf-table-card" style="padding:20px;position:relative;overflow:hidden;">
                    <div style="font-size:12px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                        <i class="ti ti-coins"></i> Lucro bruto hoje
                    </div>
                    <div style="font-size:26px;font-weight:500;margin-bottom:4px;color:<?= $lucro_bruto_hoje >= 0 ? 'var(--color-success,#22c55e)' : 'var(--color-danger,#ef4444)' ?>;">
                        R$ <?= number_format($lucro_bruto_hoje, 2, ',', '.') ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-tertiary);">
                        <?php if ($faturamento_hoje > 0): ?>
                            Margem: <?= number_format(($lucro_bruto_hoje / $faturamento_hoje) * 100, 1) ?>%
                        <?php else: ?>
                            Sem vendas hoje
                        <?php endif; ?>
                    </div>
                    <i class="ti ti-chart-pie" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.08;"></i>
                </div>

                <!-- Faturamento do mês -->
                <div class="zf-table-card" style="padding:20px;position:relative;overflow:hidden;">
                    <div style="font-size:12px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                        <i class="ti ti-calendar-month"></i> Faturamento do mês
                    </div>
                    <div style="font-size:26px;font-weight:500;margin-bottom:4px;">
                        R$ <?= number_format($faturamento_mes, 2, ',', '.') ?>
                    </div>
                    <div style="font-size:12px;">
                        <?php if ($variacao_mes > 0): ?>
                            <span style="color:var(--color-success,#22c55e);">▲ <?= number_format(abs($variacao_mes), 1) ?>%</span>
                        <?php elseif ($variacao_mes < 0): ?>
                            <span style="color:var(--color-danger,#ef4444);">▼ <?= number_format(abs($variacao_mes), 1) ?>%</span>
                        <?php else: ?>
                            <span style="color:var(--text-tertiary);">= igual ao mês anterior</span>
                        <?php endif; ?>
                        <span style="color:var(--text-tertiary);"> vs mês anterior</span>
                    </div>
                    <i class="ti ti-chart-bar" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.08;"></i>
                </div>

                <!-- Lucro bruto do mês -->
                <div class="zf-table-card" style="padding:20px;position:relative;overflow:hidden;">
                    <div style="font-size:12px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                        <i class="ti ti-pig-money"></i> Lucro bruto do mês
                    </div>
                    <?php if (($produtos_sem_custo ?? 0) > 0): ?>
                        <div style="font-size:26px;font-weight:500;margin-bottom:4px;color:var(--color-warning,#f59e0b);">
                            R$ <?= number_format($lucro_bruto_mes, 2, ',', '.') ?>
                        </div>
                        <div style="font-size:12px;color:var(--color-warning,#f59e0b);">
                            <i class="ti ti-alert-triangle"></i>
                            <?= $produtos_sem_custo ?> produto<?= $produtos_sem_custo > 1 ? 's' : '' ?> sem custo cadastrado —
                            <a href="/produtos?sem_custo=1" style="color:inherit;text-decoration:underline;">corrigir</a>
                        </div>
                    <?php else: ?>
                        <div style="font-size:26px;font-weight:500;margin-bottom:4px;color:<?php echo $lucro_bruto_mes >= 0 ? 'var(--color-success,#22c55e)' : 'var(--color-danger,#ef4444)'; ?>">
                            R$ <?= number_format($lucro_bruto_mes, 2, ',', '.') ?>
                        </div>
                        <div style="font-size:12px;color:var(--text-tertiary);">
                            Margem: <?= number_format($margem_mes, 1) ?>%
                            • <?= $qtd_vendas_mes ?> venda<?= $qtd_vendas_mes != 1 ? 's' : '' ?>
                        </div>
                    <?php endif; ?>
                    <i class="ti ti-report-money" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.08;"></i>
                </div>

                <!-- Estoque -->
                <!-- Estoque + Lucro presumido (card duplo) -->
                <div class="zf-table-card" style="padding:20px;position:relative;overflow:hidden;display:flex;flex-direction:column;gap:12px;">
                    <div style="font-size:12px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.5px;">
                        <i class="ti ti-package"></i> Estoque
                    </div>

                    <!-- Valor em estoque -->
                    <div>
                        <div style="font-size:11px;color:var(--text-tertiary);margin-bottom:2px;">Valor investido</div>
                        <div style="font-size:20px;font-weight:600;">
                            R$ <?= number_format($valor_em_estoque, 2, ',', '.') ?>
                        </div>
                        <div style="font-size:11px;color:var(--text-tertiary);margin-top:2px;">
                            <?= $total_produtos ?> produtos
                            <?php if ($alerta_estoque > 0): ?>
                                • <span style="color:var(--color-warning,#f59e0b);font-weight:500;"><?= $alerta_estoque ?> em alerta</span>
                            <?php endif; ?>
                            <?php if ($sem_estoque > 0): ?>
                                • <span style="color:var(--color-danger,#ef4444);"><?= $sem_estoque ?> zerados</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Divisória -->
                    <div style="border-top:1px solid var(--color-border-tertiary);"></div>

                    <!-- Lucro presumido -->
                    <div>
                        <div style="font-size:11px;color:var(--text-tertiary);margin-bottom:2px;">Lucro presumido</div>
                        <?php if (($sem_preco_completo ?? 0) > 0): ?>
                            <div style="font-size:20px;font-weight:600;color:var(--color-warning,#f59e0b);">
                                R$ <?= number_format($lucro_presumido_estoque, 2, ',', '.') ?>
                            </div>
                            <div style="font-size:11px;color:var(--color-warning,#f59e0b);margin-top:2px;">
                                <i class="ti ti-alert-triangle"></i>
                                <?= $sem_preco_completo ?> sem custo/venda —
                                <a href="/produtos?sem_preco=1" style="color:inherit;text-decoration:underline;">corrigir</a>
                            </div>
                        <?php else: ?>
                            <div style="font-size:20px;font-weight:600;color:var(--color-success,#22c55e);">
                                R$ <?= number_format($lucro_presumido_estoque, 2, ',', '.') ?>
                            </div>
                            <div style="font-size:11px;color:var(--text-tertiary);margin-top:2px;">
                                Se vender tudo pelo preço atual
                            </div>
                        <?php endif; ?>
                    </div>

                    <i class="ti ti-box" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.08;"></i>
                </div>

            </div>

            <!-- ─── GRÁFICO + TOP PRODUTOS ─── -->
            <div style="display:grid;grid-template-columns:1fr 340px;gap:16px;margin-bottom:24px;">

                <!-- Gráfico 7 dias -->
                <div class="zf-table-card" style="padding:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                        <span style="font-weight:500;font-size:15px;">Faturamento — últimos 7 dias</span>
                        <span style="font-size:12px;color:var(--text-tertiary);">em R$</span>
                    </div>
                    <canvas id="chartFat7" height="90"></canvas>
                </div>

                <!-- Top 5 produtos -->
                <div class="zf-table-card" style="padding:20px;">
                    <div style="font-weight:500;font-size:15px;margin-bottom:16px;">
                        <i class="ti ti-trophy" style="color:var(--color-warning,#f59e0b);"></i> Top produtos (30 dias)
                    </div>
                    <?php if (empty($top_produtos)): ?>
                        <div style="color:var(--text-tertiary);font-size:13px;text-align:center;padding:20px 0;">
                            Nenhuma venda registrada.
                        </div>
                    <?php else: ?>
                        <?php
                        $maxQty = max(array_column($top_produtos, 'total_qty')) ?: 1;
                        foreach ($top_produtos as $i => $p):
                            $pct = round(($p['total_qty'] / $maxQty) * 100);
                        ?>
                            <div style="margin-bottom:14px;">
                                <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:4px;">
                                    <span style="font-size:13px;font-weight:500;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($p['produto_nome']) ?>">
                                        <?= $i + 1 ?>. <?= e($p['produto_nome']) ?>
                                    </span>
                                    <span style="font-size:12px;color:var(--text-tertiary);margin-left:8px;flex-shrink:0;">
                                        <?= number_format($p['total_qty'], 0, ',', '.') ?> un
                                    </span>
                                </div>
                                <div style="height:4px;background:var(--color-border-tertiary);border-radius:2px;">
                                    <div style="height:4px;width:<?= $pct ?>%;background:var(--color-primary,#1a1a1a);border-radius:2px;transition:width .5s;"></div>
                                </div>
                                <div style="font-size:11px;color:var(--text-tertiary);margin-top:2px;">
                                    R$ <?= number_format($p['total_receita'], 2, ',', '.') ?>
                                    • lucro R$ <?= number_format($p['total_lucro'], 2, ',', '.') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>

            <!-- ─── ALERTAS DE ESTOQUE + ÚLTIMAS VENDAS ─── -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">

                <!-- Alertas -->
                <div class="zf-table-card" style="padding:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <span style="font-weight:500;font-size:15px;">
                            <i class="ti ti-alert-triangle" style="color:var(--color-warning,#f59e0b);"></i>
                            Estoque em alerta
                        </span>
                        <?php if (!empty($produtos_alerta)): ?>
                            <a href="/estoque" style="font-size:12px;color:var(--color-primary);">Ver todos</a>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($produtos_alerta)): ?>
                        <div style="color:var(--text-tertiary);font-size:13px;text-align:center;padding:16px 0;">
                            <i class="ti ti-circle-check" style="font-size:24px;color:var(--color-success,#22c55e);display:block;margin-bottom:6px;"></i>
                            Tudo em ordem!
                        </div>
                    <?php else: ?>
                        <table style="width:100%;border-collapse:collapse;font-size:13px;">
                            <thead>
                                <tr style="color:var(--text-tertiary);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">
                                    <th style="text-align:left;padding-bottom:8px;">Produto</th>
                                    <th style="text-align:right;padding-bottom:8px;">Atual</th>
                                    <th style="text-align:right;padding-bottom:8px;">Mínimo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos_alerta as $pa): ?>
                                    <tr style="border-top:1px solid var(--color-border-tertiary);">
                                        <td style="padding:8px 0;">
                                            <div style="font-weight:500;"><?= e($pa['nome']) ?></div>
                                            <div style="font-size:11px;color:var(--text-tertiary);"><?= e($pa['codigo']) ?></div>
                                        </td>
                                        <td style="text-align:right;padding:8px 0;color:<?= $pa['estoque_atual'] <= 0 ? 'var(--color-danger,#ef4444)' : 'var(--color-warning,#f59e0b)' ?>;font-weight:500;">
                                            <?= number_format($pa['estoque_atual'], 3, ',', '.') ?> <?= e($pa['unidade_sigla']) ?>
                                        </td>
                                        <td style="text-align:right;padding:8px 0;color:var(--text-tertiary);">
                                            <?= number_format($pa['estoque_minimo'], 3, ',', '.') ?> <?= e($pa['unidade_sigla']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Últimas vendas -->
                <div class="zf-table-card" style="padding:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <span style="font-weight:500;font-size:15px;">
                            <i class="ti ti-receipt"></i> Últimas vendas
                        </span>
                        <a href="/vendas" style="font-size:12px;color:var(--color-primary);">Ver todas</a>
                    </div>
                    <?php if (empty($ultimas_vendas)): ?>
                        <div style="color:var(--text-tertiary);font-size:13px;text-align:center;padding:16px 0;">
                            Nenhuma venda ainda hoje.
                        </div>
                    <?php else: ?>
                        <table style="width:100%;border-collapse:collapse;font-size:13px;">
                            <thead>
                                <tr style="color:var(--text-tertiary);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">
                                    <th style="text-align:left;padding-bottom:8px;">#</th>
                                    <th style="text-align:left;padding-bottom:8px;">Cliente</th>
                                    <th style="text-align:right;padding-bottom:8px;">Total</th>
                                    <th style="text-align:right;padding-bottom:8px;">Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimas_vendas as $v): ?>
                                    <tr style="border-top:1px solid var(--color-border-tertiary);">
                                        <td style="padding:8px 0;">
                                            <a href="/vendas/<?= $v['numero'] ?>" style="font-weight:500;color:var(--color-primary);">#<?= $v['numero'] ?></a>
                                        </td>
                                        <td style="padding:8px 0;color:var(--text-secondary);">
                                            <?= e($v['cliente_nome'] ?? 'Consumidor Final') ?>
                                        </td>
                                        <td style="text-align:right;padding:8px 0;font-weight:500;">
                                            R$ <?= number_format($v['total'], 2, ',', '.') ?>
                                        </td>
                                        <td style="text-align:right;padding:8px 0;color:var(--text-tertiary);font-size:11px;">
                                            <?= date('H:i', strtotime($v['criado_em'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            </div>

            <!-- ─── AÇÕES RÁPIDAS ─── -->
            <div class="zf-table-card" style="padding:20px;">
                <div style="font-weight:500;font-size:15px;margin-bottom:16px;">Ações rápidas</div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
                    <?php
                    $acoes = [
                        ['/pdv',              'ti-device-desktop',    'Abrir PDV'],
                        ['/caixa',            'ti-cash-register',     'Caixa'],
                        ['/compras/criar',    'ti-shopping-cart-plus', 'Nova Compra'],
                        ['/produtos/criar',   'ti-circle-plus',       'Novo Produto'],
                        ['/estoque/movimentar', 'ti-database-import',  'Movimentar Estoque'],
                        ['/clientes/criar',   'ti-user-plus',         'Novo Cliente'],
                    ];
                    foreach ($acoes as [$href, $icon, $label]):
                    ?>
                        <a href="<?= $href ?>" style="
                        display:flex;align-items:center;gap:10px;padding:14px 16px;
                        border:1px solid var(--color-border-tertiary);border-radius:8px;
                        text-decoration:none;color:var(--color-text-primary);font-size:13px;font-weight:500;
                        transition:border-color .15s,background .15s;
                    " onmouseover="this.style.borderColor='var(--color-border-primary)';this.style.background='var(--color-background-secondary)'"
                            onmouseout="this.style.borderColor='var(--color-border-tertiary)';this.style.background='transparent'">
                            <i class="ti <?= $icon ?>" style="font-size:18px;opacity:.7;flex-shrink:0;"></i>
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
    (function() {
        const isDark = document.documentElement.classList.contains('dark') ||
            window.matchMedia('(prefers-color-scheme: dark)').matches;
        const gridColor = isDark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.06)';
        const textColor = isDark ? '#9c9a92' : '#888780';
        const lineColor = isDark ? '#c2c0b6' : '#1a1a1a';

        const labels = <?= json_encode(array_column($fat_7dias, 'dia')) ?>;
        const dados = <?= json_encode(array_column($fat_7dias, 'faturamento')) ?>;
        const lucros = <?= json_encode(array_column($fat_7dias, 'lucro_bruto')) ?>;
        const qtds = <?= json_encode(array_column($fat_7dias, 'qtd_vendas')) ?>;

        new Chart(document.getElementById('chartFat7'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                        label: 'Faturamento R$',
                        data: dados,
                        backgroundColor: isDark ? 'rgba(194,192,182,.15)' : 'rgba(26,26,26,.08)',
                        borderColor: lineColor,
                        borderWidth: 1.5,
                        borderRadius: 4,
                        borderSkipped: false,
                        order: 2,
                    },
                    {
                        label: 'Lucro bruto R$',
                        data: lucros,
                        type: 'line',
                        borderColor: isDark ? '#1D9E75' : '#0F6E56',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: isDark ? '#1D9E75' : '#0F6E56',
                        tension: 0.3,
                        order: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                const val = ' R$ ' + ctx.parsed.y.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2
                                });
                                return ctx.dataset.label + ':' + val;
                            },
                            afterBody: (items) => {
                                const i = items[0]?.dataIndex ?? 0;
                                return ['  ' + qtds[i] + ' venda(s)'];
                            },
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor,
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor,
                            font: {
                                size: 11
                            },
                            callback: v => 'R$ ' + v.toLocaleString('pt-BR')
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    })();
</script>