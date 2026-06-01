<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">
        <?php require VIEW_PATH . '/layouts/navbar.php'; ?>

        <div class="zf-content">

            <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
                <div class="zf-alert zf-alert-success" data-auto-close><i class="ti ti-circle-check"></i> <?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close><i class="ti ti-alert-circle"></i> <?= e($msg) ?></div>
            <?php endif; ?>

            <!-- Cabeçalho -->
            <div style="margin-bottom:24px;display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;">
                <div>
                    <h1 style="font-size:22px;font-weight:500;margin:0 0 4px;">Fluxo de Caixa</h1>
                    <p style="color:var(--text-tertiary);font-size:14px;margin:0;">
                        <?= date('d/m/Y', strtotime($de)) ?> até <?= date('d/m/Y', strtotime($ate)) ?>
                    </p>
                </div>
                <!-- Atalhos rápidos -->
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <?php
                    $atalhos = [
                        'hoje'         => 'Hoje',
                        'semana'       => 'Esta semana',
                        'mes'          => 'Este mês',
                        'mes_anterior' => 'Mês anterior',
                        '90dias'       => '90 dias',
                    ];
                    foreach ($atalhos as $key => $label):
                        $active = ($atalho === $key);
                    ?>
                        <a href="?atalho=<?= $key ?>"
                            style="padding:5px 12px;border-radius:6px;font-size:12px;font-weight:500;text-decoration:none;
                                  border:1px solid var(--color-border-tertiary);
                                  background:<?= $active ? 'var(--color-primary)' : 'transparent' ?>;
                                  color:<?= $active ? '#fff' : 'var(--color-text-secondary)' ?>;">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Filtros -->
            <form method="GET" style="margin-bottom:20px;">
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                    <div>
                        <label style="font-size:11px;color:var(--text-tertiary);display:block;margin-bottom:4px;">De</label>
                        <input type="date" name="de" value="<?= e($de) ?>"
                            style="padding:7px 10px;border:1px solid var(--color-border-tertiary);border-radius:6px;font-size:13px;background:var(--color-background-primary);color:var(--color-text-primary);">
                    </div>
                    <div>
                        <label style="font-size:11px;color:var(--text-tertiary);display:block;margin-bottom:4px;">Até</label>
                        <input type="date" name="ate" value="<?= e($ate) ?>"
                            style="padding:7px 10px;border:1px solid var(--color-border-tertiary);border-radius:6px;font-size:13px;background:var(--color-background-primary);color:var(--color-text-primary);">
                    </div>
                    <div>
                        <label style="font-size:11px;color:var(--text-tertiary);display:block;margin-bottom:4px;">Categoria</label>
                        <select name="categoria_id"
                            style="padding:7px 10px;border:1px solid var(--color-border-tertiary);border-radius:6px;font-size:13px;background:var(--color-background-primary);color:var(--color-text-primary);">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($filtros['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= e($cat['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="ti ti-filter"></i> Filtrar
                    </button>
                </div>
            </form>

            <!-- Cards resumo -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;">

                <div class="zf-table-card" style="padding:18px;position:relative;overflow:hidden;">
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-tertiary);margin-bottom:8px;">
                        <i class="ti ti-database"></i> Saldo anterior
                    </div>
                    <div style="font-size:22px;font-weight:500;color:<?= $saldo_anterior >= 0 ? 'var(--color-success,#22c55e)' : 'var(--color-danger,#ef4444)' ?>;">
                        R$ <?= number_format($saldo_anterior, 2, ',', '.') ?>
                    </div>
                    <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px;">Antes de <?= date('d/m/Y', strtotime($de)) ?></div>
                    <i class="ti ti-database" style="position:absolute;right:14px;top:14px;font-size:26px;opacity:.05;"></i>
                </div>

                <div class="zf-table-card" style="padding:18px;position:relative;overflow:hidden;">
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-tertiary);margin-bottom:8px;">
                        <i class="ti ti-arrow-down-circle"></i> Entradas
                    </div>
                    <div style="font-size:22px;font-weight:500;color:var(--color-success,#22c55e);">
                        R$ <?= number_format($total_entradas, 2, ',', '.') ?>
                    </div>
                    <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px;"><?= count(array_filter($movimentacoes, fn($m) => $m['tipo'] === 'receita')) ?> lançamentos</div>
                    <i class="ti ti-arrow-down-circle" style="position:absolute;right:14px;top:14px;font-size:26px;opacity:.05;"></i>
                </div>

                <div class="zf-table-card" style="padding:18px;position:relative;overflow:hidden;">
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-tertiary);margin-bottom:8px;">
                        <i class="ti ti-arrow-up-circle"></i> Saídas
                    </div>
                    <div style="font-size:22px;font-weight:500;color:var(--color-danger,#ef4444);">
                        R$ <?= number_format($total_saidas, 2, ',', '.') ?>
                    </div>
                    <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px;"><?= count(array_filter($movimentacoes, fn($m) => $m['tipo'] === 'despesa')) ?> lançamentos</div>
                    <i class="ti ti-arrow-up-circle" style="position:absolute;right:14px;top:14px;font-size:26px;opacity:.05;"></i>
                </div>

                <div class="zf-table-card" style="padding:18px;position:relative;overflow:hidden;">
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-tertiary);margin-bottom:8px;">
                        <i class="ti ti-scale"></i> Saldo final
                    </div>
                    <div style="font-size:22px;font-weight:500;color:<?= $saldo_final >= 0 ? 'var(--color-success,#22c55e)' : 'var(--color-danger,#ef4444)' ?>;">
                        R$ <?= number_format($saldo_final, 2, ',', '.') ?>
                    </div>
                    <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px;">
                        <?= $saldo >= 0 ? '+' : '' ?>R$ <?= number_format($saldo, 2, ',', '.') ?> no período
                    </div>
                    <i class="ti ti-scale" style="position:absolute;right:14px;top:14px;font-size:26px;opacity:.05;"></i>
                </div>

            </div>

            <!-- Gráfico -->
            <div class="zf-table-card" style="padding:20px;margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
                    <span style="font-weight:500;font-size:15px;">Entradas × Saídas × Saldo acumulado</span>
                    <div style="display:flex;gap:16px;font-size:12px;color:var(--text-tertiary);">
                        <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#22c55e;margin-right:4px;"></span>Entradas</span>
                        <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#ef4444;margin-right:4px;"></span>Saídas</span>
                        <span><span style="display:inline-block;width:10px;height:2px;background:#6366f1;margin-right:4px;vertical-align:middle;"></span>Saldo</span>
                    </div>
                </div>
                <canvas id="chartFluxo" height="<?= count($grafico_labels) > 20 ? '70' : '90' ?>"></canvas>
            </div>

            <!-- Tabela movimentações + Projeção lado a lado -->
            <div style="display:grid;grid-template-columns:1fr 340px;gap:16px;margin-bottom:20px;">

                <!-- Movimentações do período -->
                <div class="zf-table-card" style="padding:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <span style="font-weight:500;font-size:15px;">Movimentações</span>
                        <span style="font-size:12px;color:var(--text-tertiary);"><?= count($movimentacoes) ?> registro(s)</span>
                    </div>

                    <?php if (empty($movimentacoes)): ?>
                        <div style="text-align:center;padding:32px 0;color:var(--text-tertiary);font-size:13px;">
                            <i class="ti ti-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>
                            Nenhuma movimentação no período.
                        </div>
                    <?php else: ?>
                        <!-- Filtro rápido por tipo -->
                        <div style="display:flex;gap:6px;margin-bottom:14px;">
                            <button onclick="filtrarTabela('todos')" id="ftb-todos" class="ftb-btn ftb-ativo">Todos</button>
                            <button onclick="filtrarTabela('receita')" id="ftb-receita" class="ftb-btn">Entradas</button>
                            <button onclick="filtrarTabela('despesa')" id="ftb-despesa" class="ftb-btn">Saídas</button>
                        </div>

                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:13px;" id="tabela-mov">
                                <thead>
                                    <tr style="color:var(--text-tertiary);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">
                                        <th style="text-align:left;padding-bottom:10px;padding-right:12px;">Data</th>
                                        <th style="text-align:left;padding-bottom:10px;padding-right:12px;">Descrição</th>
                                        <th style="text-align:left;padding-bottom:10px;padding-right:12px;">Categoria</th>
                                        <th style="text-align:left;padding-bottom:10px;padding-right:12px;">Origem</th>
                                        <th style="text-align:right;padding-bottom:10px;padding-right:12px;">Valor</th>
                                        <th style="text-align:right;padding-bottom:10px;">Saldo acum.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($movimentacoes as $m): ?>
                                        <tr class="mov-row mov-<?= $m['tipo'] ?>"
                                            style="border-top:1px solid var(--color-border-tertiary);">
                                            <td style="padding:8px 12px 8px 0;white-space:nowrap;color:var(--text-tertiary);font-size:12px;">
                                                <?= date('d/m/Y', strtotime($m['data'])) ?>
                                            </td>
                                            <td style="padding:8px 12px 8px 0;max-width:180px;">
                                                <div style="font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($m['descricao']) ?>">
                                                    <?= e($m['descricao']) ?>
                                                </div>
                                            </td>
                                            <td style="padding:8px 12px 8px 0;">
                                                <?php if (!empty($m['categoria'])): ?>
                                                    <span style="font-size:11px;padding:2px 8px;border-radius:20px;
                                                        background:<?= !empty($m['categoria_cor']) ? $m['categoria_cor'] . '22' : 'var(--color-border-tertiary)' ?>;
                                                        color:<?= !empty($m['categoria_cor']) ? $m['categoria_cor'] : 'var(--text-tertiary)' ?>;">
                                                        <?= e($m['categoria']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span style="font-size:11px;color:var(--text-tertiary);">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="padding:8px 12px 8px 0;font-size:12px;color:var(--text-tertiary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px;">
                                                <?= e($m['origem'] ?? '—') ?>
                                            </td>
                                            <td style="padding:8px 12px 8px 0;text-align:right;font-weight:600;white-space:nowrap;
                                                color:<?= $m['tipo'] === 'receita' ? 'var(--color-success,#22c55e)' : 'var(--color-danger,#ef4444)' ?>;">
                                                <?= $m['tipo'] === 'receita' ? '+' : '−' ?> R$ <?= number_format($m['valor'], 2, ',', '.') ?>
                                            </td>
                                            <td style="padding:8px 0;text-align:right;font-size:12px;white-space:nowrap;
                                                color:<?= $m['saldo_acumulado'] >= 0 ? 'var(--color-success,#22c55e)' : 'var(--color-danger,#ef4444)' ?>;">
                                                R$ <?= number_format($m['saldo_acumulado'], 2, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Projeção 30 dias -->
                <div class="zf-table-card" style="padding:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <span style="font-weight:500;font-size:15px;">
                            <i class="ti ti-trending-up" style="color:var(--color-primary);"></i>
                            Projeção 30 dias
                        </span>
                        <span style="font-size:11px;color:var(--text-tertiary);">a vencer</span>
                    </div>

                    <?php if (empty($projecao)): ?>
                        <div style="text-align:center;padding:24px 0;color:var(--text-tertiary);font-size:13px;">
                            <i class="ti ti-circle-check" style="font-size:24px;color:var(--color-success,#22c55e);display:block;margin-bottom:6px;"></i>
                            Nenhum lançamento previsto.
                        </div>
                    <?php else: ?>
                        <?php
                        $saldoProj = $saldo_final;
                        foreach ($projecao as $p):
                            $saldoProj += $p['tipo'] === 'receita' ? $p['valor'] : -$p['valor'];
                        ?>
                            <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-top:1px solid var(--color-border-tertiary);">
                                <div style="flex-shrink:0;margin-top:2px;">
                                    <i class="ti <?= $p['tipo'] === 'receita' ? 'ti-arrow-down-circle' : 'ti-arrow-up-circle' ?>"
                                        style="font-size:14px;color:<?= $p['tipo'] === 'receita' ? 'var(--color-success,#22c55e)' : 'var(--color-danger,#ef4444)' ?>;"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:12px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($p['descricao']) ?>">
                                        <?= e($p['descricao']) ?>
                                    </div>
                                    <div style="font-size:11px;color:var(--text-tertiary);">
                                        <?= date('d/m', strtotime($p['data'])) ?> · <?= e($p['origem']) ?>
                                    </div>
                                </div>
                                <div style="text-align:right;flex-shrink:0;">
                                    <div style="font-size:12px;font-weight:600;
                                        color:<?= $p['tipo'] === 'receita' ? 'var(--color-success,#22c55e)' : 'var(--color-danger,#ef4444)' ?>;">
                                        <?= $p['tipo'] === 'receita' ? '+' : '−' ?> R$ <?= number_format($p['valor'], 2, ',', '.') ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Saldo projetado final -->
                        <div style="margin-top:14px;padding:12px;border-radius:8px;background:var(--color-background-secondary);text-align:center;">
                            <div style="font-size:11px;color:var(--text-tertiary);margin-bottom:4px;">Saldo projetado em 30 dias</div>
                            <div style="font-size:20px;font-weight:600;color:<?= $saldoProj >= 0 ? 'var(--color-success,#22c55e)' : 'var(--color-danger,#ef4444)' ?>;">
                                R$ <?= number_format($saldoProj, 2, ',', '.') ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div><!-- /zf-content -->
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

<style>
    .ftb-btn {
        padding: 4px 12px;
        border-radius: 6px;
        border: 1px solid var(--color-border-tertiary);
        background: transparent;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        color: var(--color-text-secondary);
        transition: all .15s;
    }

    .ftb-btn:hover {
        border-color: var(--color-border-primary);
    }

    .ftb-ativo {
        background: var(--color-primary);
        color: #fff;
        border-color: var(--color-primary);
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
    (function() {
        const isDark = document.documentElement.classList.contains('dark') ||
            window.matchMedia('(prefers-color-scheme: dark)').matches;
        const gridColor = isDark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.06)';
        const textColor = isDark ? '#9c9a92' : '#888780';

        const labels = <?= json_encode($grafico_labels)   ?>;
        const entradas = <?= json_encode($grafico_entradas) ?>;
        const saidas = <?= json_encode($grafico_saidas)   ?>;
        const saldo = <?= json_encode($grafico_saldo)    ?>;

        new Chart(document.getElementById('chartFluxo'), {
            data: {
                labels,
                datasets: [{
                        type: 'bar',
                        label: 'Entradas',
                        data: entradas,
                        backgroundColor: isDark ? 'rgba(34,197,94,.35)' : 'rgba(34,197,94,.25)',
                        borderColor: '#22c55e',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        borderSkipped: false,
                        yAxisID: 'y',
                        order: 2,
                    },
                    {
                        type: 'bar',
                        label: 'Saídas',
                        data: saidas,
                        backgroundColor: isDark ? 'rgba(239,68,68,.3)' : 'rgba(239,68,68,.2)',
                        borderColor: '#ef4444',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        borderSkipped: false,
                        yAxisID: 'y',
                        order: 2,
                    },
                    {
                        type: 'line',
                        label: 'Saldo acumulado',
                        data: saldo,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,.08)',
                        borderWidth: 2,
                        pointRadius: labels.length > 20 ? 0 : 3,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y2',
                        order: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false // legenda manual no HTML acima
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
                            },
                            maxTicksLimit: 15,
                        }
                    },
                    y: {
                        position: 'left',
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
                        beginAtZero: true,
                    },
                    y2: {
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            color: '#6366f1',
                            font: {
                                size: 11
                            },
                            callback: v => 'R$ ' + v.toLocaleString('pt-BR')
                        }
                    }
                }
            }
        });
    })();

    // Filtro rápido na tabela
    function filtrarTabela(tipo) {
        document.querySelectorAll('.mov-row').forEach(tr => {
            tr.style.display = (tipo === 'todos' || tr.classList.contains('mov-' + tipo)) ? '' : 'none';
        });
        document.querySelectorAll('.ftb-btn').forEach(b => b.classList.remove('ftb-ativo'));
        document.getElementById('ftb-' + tipo).classList.add('ftb-ativo');
    }
</script>