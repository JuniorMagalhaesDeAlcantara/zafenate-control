<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">
        <?php require VIEW_PATH . '/layouts/navbar.php'; ?>

        <div class="zf-content">

            <!-- Cabeçalho -->
            <div style="margin-bottom:24px;display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;">
                <div>
                    <h1 style="font-size:22px;font-weight:500;margin:0 0 4px;">DRE — Demonstração do Resultado</h1>
                    <p style="color:var(--text-tertiary);font-size:14px;margin:0;">
                        <?= date('d/m/Y', strtotime($de)) ?> até <?= date('d/m/Y', strtotime($ate)) ?>
                    </p>
                </div>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <?php foreach (
                        [
                            'mes'          => 'Este mês',
                            'mes_anterior' => 'Mês anterior',
                            'trimestre'    => 'Trimestre',
                            'ano'          => 'Este ano',
                        ] as $key => $label
                    ): ?>
                        <a href="?atalho=<?= $key ?>"
                            style="padding:5px 12px;border-radius:6px;font-size:12px;font-weight:500;
                                  text-decoration:none;border:1px solid var(--color-border-tertiary);
                                  background:<?= $atalho === $key ? 'var(--color-primary)' : 'transparent' ?>;
                                  color:<?= $atalho === $key ? '#fff' : 'var(--color-text-secondary)' ?>;">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                    <!-- Período customizado -->
                    <form method="GET" style="display:flex;gap:6px;align-items:center;">
                        <input type="date" name="de" value="<?= e($de) ?>"
                            style="padding:5px 8px;border:1px solid var(--color-border-tertiary);border-radius:6px;font-size:12px;background:var(--color-background-primary);color:var(--color-text-primary);">
                        <input type="date" name="ate" value="<?= e($ate) ?>"
                            style="padding:5px 8px;border:1px solid var(--color-border-tertiary);border-radius:6px;font-size:12px;background:var(--color-background-primary);color:var(--color-text-primary);">
                        <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                    </form>
                </div>
            </div>

            <!-- ── CARDS INDICADORES ── -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;">

                <?php
                $cards = [
                    ['Receita líquida',   $receita_liquida,    'ti-trending-up',      '#6366f1', 'Vendas − cancelamentos'],
                    ['Lucro bruto',       $lucro_bruto,        'ti-report-money',     $lucro_bruto  >= 0 ? '#22c55e' : '#ef4444', 'Após CMV · margem ' . number_format($margem_bruta, 1) . '%'],
                    ['Total despesas',    $total_despesas,     'ti-arrow-up-circle',  '#ef4444', count($despesas) . ' categorias'],
                    ['Resultado líquido', $resultado_liquido,  'ti-scale',            $resultado_liquido >= 0 ? '#22c55e' : '#ef4444', 'Margem ' . number_format($margem_liquida, 1) . '%'],
                ];
                foreach ($cards as [$titulo, $valor, $icon, $cor, $sub]):
                ?>
                    <div class="zf-table-card" style="padding:18px;position:relative;overflow:hidden;">
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-tertiary);margin-bottom:8px;">
                            <i class="ti <?= $icon ?>"></i> <?= $titulo ?>
                        </div>
                        <div style="font-size:22px;font-weight:500;color:<?= $cor ?>;">
                            R$ <?= number_format(abs($valor), 2, ',', '.') ?>
                            <?php if ($valor < 0): ?>
                                <span style="font-size:13px;">(negativo)</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px;"><?= $sub ?></div>
                        <i class="ti <?= $icon ?>" style="position:absolute;right:14px;top:14px;font-size:26px;opacity:.05;"></i>
                    </div>
                <?php endforeach; ?>

            </div>

            <!-- ── CORPO DO DRE + GRÁFICO ── -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">

                <!-- Demonstrativo estruturado -->
                <div class="zf-table-card" style="padding:24px;">
                    <div style="font-weight:600;font-size:15px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid var(--color-border-tertiary);">
                        Demonstrativo
                    </div>

                    <?php
                    // Helper linha DRE
                    $linha = function (string $label, float $valor, string $tipo = 'normal', string $sub = '') {
                        $cor = match ($tipo) {
                            'positivo' => '#22c55e',
                            'negativo' => '#ef4444',
                            'destaque' => $valor >= 0 ? '#22c55e' : '#ef4444',
                            'neutro'   => 'var(--text-tertiary)',
                            default    => 'var(--color-text-primary)',
                        };
                        $bold    = in_array($tipo, ['destaque', 'total']) ? '600' : '400';
                        $bg      = in_array($tipo, ['destaque', 'total']) ? 'background:var(--color-background-secondary);border-radius:6px;padding:8px 10px;margin:4px -10px;' : 'padding:6px 0;';
                        $sinal   = $tipo === 'negativo' ? '−' : ($tipo === 'positivo' ? '+' : '');
                        echo "<div style='display:flex;justify-content:space-between;align-items:center;{$bg}border-bottom:1px solid var(--color-border-tertiary);'>
                            <div>
                                <span style='font-size:13px;font-weight:{$bold};'>{$label}</span>
                                " . ($sub ? "<div style='font-size:11px;color:var(--text-tertiary);'>{$sub}</div>" : '') . "
                            </div>
                            <span style='font-size:13px;font-weight:{$bold};color:{$cor};white-space:nowrap;'>
                                {$sinal} R$ " . number_format(abs($valor), 2, ',', '.') . "
                            </span>
                        </div>";
                    };
                    ?>

                    <?php $linha('Receita Bruta (PDV)', $receita_bruta, 'positivo', $qtd_vendas . ' vendas · ticket médio R$ ' . number_format($ticket_medio, 2, ',', '.')); ?>
                    <?php if ($total_cancelamentos > 0): ?>
                        <?php $linha('(−) Cancelamentos', $total_cancelamentos, 'negativo'); ?>
                    <?php endif; ?>
                    <?php if ($total_descontos > 0): ?>
                        <?php $linha('(−) Descontos concedidos', $total_descontos, 'negativo'); ?>
                    <?php endif; ?>
                    <?php if ($total_outras_receitas > 0): ?>
                        <?php $linha('(+) Outras receitas', $total_outras_receitas, 'positivo'); ?>
                    <?php endif; ?>

                    <?php $linha('= Receita Líquida', $receita_total, 'destaque'); ?>

                    <div style="margin:12px 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-tertiary);">Custos</div>
                    <?php $linha('(−) CMV — Custo das Mercadorias', $cmv, 'negativo', 'Custo de compra dos produtos vendidos'); ?>
                    <?php $linha('= Lucro Bruto', $lucro_bruto, 'destaque', 'Margem bruta: ' . number_format($margem_bruta, 1) . '%'); ?>

                    <div style="margin:12px 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-tertiary);">Despesas operacionais</div>
                    <?php foreach ($despesas as $d): ?>
                        <?php $linha(
                            '(−) ' . e($d['categoria']),
                            (float)$d['total'],
                            'negativo',
                            $d['qtd'] . ' lançamento(s)'
                        ); ?>
                    <?php endforeach; ?>
                    <?php if (empty($despesas)): ?>
                        <div style="font-size:13px;color:var(--text-tertiary);padding:8px 0;">Nenhuma despesa no período.</div>
                    <?php endif; ?>

                    <div style="margin-top:8px;">
                        <?php $linha('= Resultado Líquido', $resultado_liquido, 'destaque', 'Margem líquida: ' . number_format($margem_liquida, 1) . '%'); ?>
                    </div>
                </div>

                <!-- Gráfico evolução 12 meses -->
                <div style="display:flex;flex-direction:column;gap:16px;">

                    <div class="zf-table-card" style="padding:20px;flex:1;">
                        <div style="font-weight:500;font-size:15px;margin-bottom:16px;">Evolução — últimos 12 meses</div>
                        <canvas id="chartEvolucao" height="140"></canvas>
                    </div>

                    <!-- Receita por forma de pagamento -->
                    <div class="zf-table-card" style="padding:20px;">
                        <div style="font-weight:500;font-size:15px;margin-bottom:14px;">Por forma de pagamento</div>
                        <?php
                        $formasLabel = [
                            'dinheiro'       => ['💵', 'Dinheiro'],
                            'pix'            => ['📱', 'PIX'],
                            'cartao_debito'  => ['💳', 'Débito'],
                            'cartao_credito' => ['💳', 'Crédito'],
                        ];
                        foreach ($por_forma as $f):
                            $pct = $receita_liquida > 0 ? ($f['total'] / $receita_liquida * 100) : 0;
                            [$emoji, $nome] = $formasLabel[$f['forma']] ?? ['💰', $f['forma']];
                        ?>
                            <div style="margin-bottom:10px;">
                                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                                    <span><?= $emoji ?> <?= $nome ?> <span style="color:var(--text-tertiary);font-size:11px;">(<?= $f['qtd'] ?> vendas)</span></span>
                                    <span style="font-weight:600;">R$ <?= number_format($f['total'], 2, ',', '.') ?> <span style="font-size:11px;color:var(--text-tertiary);"><?= number_format($pct, 1) ?>%</span></span>
                                </div>
                                <div style="height:5px;background:var(--color-border-tertiary);border-radius:4px;">
                                    <div style="height:5px;background:var(--color-primary);border-radius:4px;width:<?= min($pct, 100) ?>%;transition:width .4s;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($por_forma)): ?>
                            <div style="font-size:13px;color:var(--text-tertiary);">Nenhuma venda no período.</div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- ── TOP PRODUTOS ── -->
            <div class="zf-table-card" style="padding:20px;margin-bottom:20px;">
                <div style="font-weight:500;font-size:15px;margin-bottom:16px;">
                    <i class="ti ti-podium" style="color:var(--color-primary);"></i>
                    Top produtos do período
                </div>
                <?php if (empty($top_produtos)): ?>
                    <div style="color:var(--text-tertiary);font-size:13px;">Nenhuma venda no período.</div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:13px;">
                            <thead>
                                <tr style="color:var(--text-tertiary);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">
                                    <th style="text-align:left;padding-bottom:10px;">#</th>
                                    <th style="text-align:left;padding-bottom:10px;">Produto</th>
                                    <th style="text-align:right;padding-bottom:10px;">Qtd</th>
                                    <th style="text-align:right;padding-bottom:10px;">Receita</th>
                                    <th style="text-align:right;padding-bottom:10px;">Custo</th>
                                    <th style="text-align:right;padding-bottom:10px;">Lucro</th>
                                    <th style="text-align:right;padding-bottom:10px;">Margem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_produtos as $i => $p):
                                    $margem = $p['receita'] > 0 ? ($p['lucro'] / $p['receita'] * 100) : 0;
                                ?>
                                    <tr style="border-top:1px solid var(--color-border-tertiary);">
                                        <td style="padding:8px 12px 8px 0;color:var(--text-tertiary);font-size:12px;"><?= $i + 1 ?></td>
                                        <td style="padding:8px 12px 8px 0;font-weight:500;"><?= e($p['nome']) ?></td>
                                        <td style="padding:8px 12px 8px 0;text-align:right;color:var(--text-tertiary);"><?= number_format($p['qty'], 0, ',', '.') ?></td>
                                        <td style="padding:8px 12px 8px 0;text-align:right;font-weight:500;">R$ <?= number_format($p['receita'], 2, ',', '.') ?></td>
                                        <td style="padding:8px 12px 8px 0;text-align:right;color:var(--color-danger,#ef4444);">R$ <?= number_format($p['custo'], 2, ',', '.') ?></td>
                                        <td style="padding:8px 12px 8px 0;text-align:right;font-weight:600;color:<?= $p['lucro'] >= 0 ? 'var(--color-success,#22c55e)' : 'var(--color-danger,#ef4444)' ?>;">
                                            R$ <?= number_format($p['lucro'], 2, ',', '.') ?>
                                        </td>
                                        <td style="padding:8px 0;text-align:right;">
                                            <span style="font-size:12px;padding:2px 8px;border-radius:20px;
                                                background:<?= $margem >= 20 ? 'rgba(34,197,94,.12)' : ($margem >= 0 ? 'rgba(245,158,11,.12)' : 'rgba(239,68,68,.12)') ?>;
                                                color:<?= $margem >= 20 ? '#22c55e' : ($margem >= 0 ? '#d97706' : '#ef4444') ?>;">
                                                <?= number_format($margem, 1) ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
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

        const evolucao = <?= json_encode($evolucao) ?>;
        if (!evolucao.length) return;

        new Chart(document.getElementById('chartEvolucao'), {
            type: 'bar',
            data: {
                labels: evolucao.map(e => e.mes_label),
                datasets: [{
                        label: 'Receita',
                        data: evolucao.map(e => e.receita),
                        backgroundColor: isDark ? 'rgba(99,102,241,.4)' : 'rgba(99,102,241,.25)',
                        borderColor: '#6366f1',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        order: 2,
                    },
                    {
                        label: 'Lucro bruto',
                        data: evolucao.map(e => Math.max(0, e.receita - e.custo)),
                        backgroundColor: isDark ? 'rgba(34,197,94,.35)' : 'rgba(34,197,94,.2)',
                        borderColor: '#22c55e',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        order: 2,
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': R$ ' +
                                ctx.parsed.y.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2
                                })
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