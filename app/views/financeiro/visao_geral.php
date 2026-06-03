<?php require VIEW_PATH . '/layouts/header.php'; ?>

<style>
    /* ── Visão Geral Financeira: estilos ── */
    .fin-page-hdr {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 28px;
    }

    .fin-page-hdr-left h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 6px;
        letter-spacing: -.4px;
    }

    .fin-page-hdr-left p {
        font-size: 13px;
        color: var(--text-tertiary);
        margin: 0;
    }

    .fin-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* KPI Cards */
    .fin-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .fin-kpi {
        background: var(--bg-primary, #fff);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        transition: box-shadow .15s ease, border-color .15s ease;
        position: relative;
        overflow: hidden;
    }

    .fin-kpi:hover {
        border-color: var(--color-border, #d1d5db);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
    }

    .fin-kpi-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--text-tertiary);
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 10px;
    }

    .fin-kpi-value {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .fin-kpi-sub {
        font-size: 12px;
        color: var(--text-tertiary);
    }

    .fin-kpi-icon {
        position: absolute;
        right: 16px;
        top: 16px;
        font-size: 32px;
        opacity: .06;
    }

    /* Tabelas de dados */
    .fin-data-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    @media (max-width: 1200px) {
        .fin-data-grid {
            grid-template-columns: 1fr;
        }
    }

    .fin-card {
        background: var(--bg-primary, #fff);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        transition: box-shadow .15s ease, border-color .15s ease;
    }

    .fin-card:hover {
        border-color: var(--color-border, #d1d5db);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
    }

    .fin-card-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--text-tertiary);
        margin: 0 0 18px;
        opacity: .85;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border-color);
    }

    .fin-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-color);
        font-size: 13px;
    }

    .fin-row:last-child {
        border-bottom: none;
    }

    .fin-row-label {
        color: var(--text-secondary);
        font-weight: 500;
    }

    .fin-row-value {
        font-weight: 600;
        color: var(--text-primary);
    }

    .fin-row-positive {
        color: #16a34a;
    }

    .fin-row-negative {
        color: #dc2626;
    }
</style>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">
        <?php
        $breadcrumb = [
            ['label' => 'Dashboard',         'url' => '/dashboard'],
            ['label' => 'Visão Geral Financeira', 'url' => '#'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content" style="display:flex;flex-direction:column;gap:20px;">

            <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
                <div class="zf-alert zf-alert-success" data-auto-close><i class="ti ti-circle-check"></i> <?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close><i class="ti ti-alert-circle"></i> <?= e($msg) ?></div>
            <?php endif; ?>

            <?php
            $meses = [
                1  => 'Janeiro',
                2  => 'Fevereiro',
                3  => 'Março',
                4  => 'Abril',
                5  => 'Maio',
                6  => 'Junho',
                7  => 'Julho',
                8  => 'Agosto',
                9  => 'Setembro',
                10 => 'Outubro',
                11 => 'Novembro',
                12 => 'Dezembro',
            ];
            ?>

            <!-- Cabeçalho -->
            <div class="fin-page-hdr">
                <div class="fin-page-hdr-left">
                    <h1>Visão Geral Financeira</h1>
                    <p>Resumo do mês de <?= $meses[(int)date('n')] ?> de <?= date('Y') ?></p>
                </div>
                <div class="fin-actions">
                    <a href="/financeiro/pagar/criar" class="btn btn-outline btn-sm">
                        <i class="ti ti-plus"></i> Conta a pagar
                    </a>
                    <a href="/financeiro/receber/criar" class="btn btn-outline btn-sm">
                        <i class="ti ti-plus"></i> Conta a receber
                    </a>
                    <a href="/financeiro/fluxo" class="btn btn-primary btn-sm">
                        <i class="ti ti-arrows-exchange"></i> Fluxo de caixa
                    </a>
                </div>
            </div>

            <!-- KPI CARDS -->
            <div class="fin-kpi-grid">

                <!-- Saldo do período -->
                <?php $saldo = ($totais_receber['recebidas_mes'] ?? 0) - ($totais_pagar['pagas_mes'] ?? 0); ?>
                <div class="fin-kpi">
                    <div class="fin-kpi-label">
                        <i class="ti ti-scale"></i> Saldo do mês
                    </div>
                    <div class="fin-kpi-value" style="color:<?= $saldo >= 0 ? '#16a34a' : '#dc2626' ?>;">
                        R$ <?= number_format($saldo, 2, ',', '.') ?>
                    </div>
                    <div class="fin-kpi-sub">Entradas − saídas pagas</div>
                    <i class="ti ti-scale fin-kpi-icon"></i>
                </div>

                <!-- A receber -->
                <div class="fin-kpi">
                    <div class="fin-kpi-label">
                        <i class="ti ti-clock"></i> A receber
                    </div>
                    <div class="fin-kpi-value">
                        R$ <?= number_format($totais_receber['a_receber'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div class="fin-kpi-sub"><?= $totais_receber['qtd_aberto'] ?? 0 ?> conta(s) pendente(s)</div>
                    <i class="ti ti-clock fin-kpi-icon"></i>
                </div>

                <!-- A pagar -->
                <div class="fin-kpi">
                    <div class="fin-kpi-label">
                        <i class="ti ti-alert-circle"></i> A pagar
                    </div>
                    <div class="fin-kpi-value">
                        R$ <?= number_format($totais_pagar['a_pagar'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div class="fin-kpi-sub"><?= $totais_pagar['qtd_aberto'] ?? 0 ?> conta(s) em aberto</div>
                    <i class="ti ti-alert-circle fin-kpi-icon"></i>
                </div>

                <!-- Fluxo do mês -->
                <div class="fin-kpi">
                    <div class="fin-kpi-label">
                        <i class="ti ti-arrows-exchange"></i> Fluxo mês
                    </div>
                    <div class="fin-kpi-value">
                        R$ <?= number_format($totais_receber['recebidas_mes'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div class="fin-kpi-sub">Entradas confirmadas</div>
                    <i class="ti ti-arrows-exchange fin-kpi-icon"></i>
                </div>

            </div>

            <!-- DADOS DETALHADOS -->
            <div class="fin-data-grid">
                <div class="zf-table-card" style="padding:20px;position:relative;overflow:hidden;">
                    <div style="font-size:12px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                        <i class="ti ti-arrow-down-circle"></i> A receber
                    </div>
                    <div style="font-size:26px;font-weight:500;margin-bottom:4px;color:var(--color-success,#22c55e);">
                        R$ <?= number_format(($totais_receber['a_vencer'] ?? 0) + ($totais_receber['vencidas'] ?? 0), 2, ',', '.') ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-tertiary);">
                        <?= ($totais_receber['qtd_a_vencer'] ?? 0) + ($totais_receber['qtd_vencidas'] ?? 0) ?> conta(s) em aberto
                        <?php if (($totais_receber['vencidas'] ?? 0) > 0): ?>
                            • <span style="color:var(--color-danger,#ef4444);"><?= $totais_receber['qtd_vencidas'] ?> vencida(s)</span>
                        <?php endif; ?>
                    </div>
                    <i class="ti ti-arrow-down-circle" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.06;"></i>
                </div>

                <!-- A pagar -->
                <div class="zf-table-card" style="padding:20px;position:relative;overflow:hidden;">
                    <div style="font-size:12px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                        <i class="ti ti-arrow-up-circle"></i> A pagar
                    </div>
                    <div style="font-size:26px;font-weight:500;margin-bottom:4px;color:var(--color-danger,#ef4444);">
                        R$ <?= number_format(($totais_pagar['a_vencer'] ?? 0) + ($totais_pagar['vencidas'] ?? 0), 2, ',', '.') ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-tertiary);">
                        <?= ($totais_pagar['qtd_a_vencer'] ?? 0) + ($totais_pagar['qtd_vencidas'] ?? 0) ?> conta(s) em aberto
                        <?php if (($totais_pagar['vencidas'] ?? 0) > 0): ?>
                            • <span style="color:var(--color-danger,#ef4444);"><?= $totais_pagar['qtd_vencidas'] ?> vencida(s)</span>
                        <?php endif; ?>
                    </div>
                    <i class="ti ti-arrow-up-circle" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.06;"></i>
                </div>

                <!-- Recebido no mês -->
                <div class="zf-table-card" style="padding:20px;position:relative;overflow:hidden;">
                    <div style="font-size:12px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                        <i class="ti ti-circle-check"></i> Movimentado (mês)
                    </div>
                    <div style="font-size:14px;font-weight:500;margin-bottom:6px;">
                        <span style="color:var(--color-success,#22c55e);">
                            ↑ R$ <?= number_format($totais_receber['recebidas_mes'] ?? 0, 2, ',', '.') ?>
                        </span>
                        <span style="color:var(--text-tertiary);font-weight:400;font-size:11px;margin-left:4px;">recebido</span>
                    </div>
                    <div style="font-size:14px;font-weight:500;">
                        <span style="color:var(--color-danger,#ef4444);">
                            ↓ R$ <?= number_format($totais_pagar['pagas_mes'] ?? 0, 2, ',', '.') ?>
                        </span>
                        <span style="color:var(--text-tertiary);font-weight:400;font-size:11px;margin-left:4px;">pago</span>
                    </div>
                    <i class="ti ti-report-money" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.06;"></i>
                </div>

            </div>

            <!-- ─── GRÁFICO FLUXO + VENCIMENTOS PRÓXIMOS ─── -->
            <div style="display:grid;grid-template-columns:1fr 340px;gap:16px;margin-bottom:24px;">

                <!-- Gráfico entradas x saídas 30 dias -->
                <div class="zf-table-card" style="padding:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                        <span style="font-weight:500;font-size:15px;">Entradas × Saídas — últimos 30 dias</span>
                        <a href="/financeiro/fluxo" style="font-size:12px;color:var(--color-primary);">Ver fluxo completo</a>
                    </div>
                    <canvas id="chartFluxo" height="90"></canvas>
                </div>

                <!-- Vencimentos próximos 7 dias -->
                <div class="zf-table-card" style="padding:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <span style="font-weight:500;font-size:15px;">
                            <i class="ti ti-calendar-event" style="color:var(--color-warning,#f59e0b);"></i>
                            Vencimentos próximos
                        </span>
                        <span style="font-size:11px;color:var(--text-tertiary);">próximos 7 dias</span>
                    </div>
                    <?php if (empty($vencimentos_proximos)): ?>
                        <div style="color:var(--text-tertiary);font-size:13px;text-align:center;padding:20px 0;">
                            <i class="ti ti-circle-check" style="font-size:24px;color:var(--color-success,#22c55e);display:block;margin-bottom:6px;"></i>
                            Nenhum vencimento nos próximos 7 dias.
                        </div>
                    <?php else: ?>
                        <?php foreach ($vencimentos_proximos as $v): ?>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-top:1px solid var(--color-border-tertiary);">
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:13px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($v['descricao']) ?>">
                                        <?= e($v['descricao']) ?>
                                    </div>
                                    <div style="font-size:11px;color:var(--text-tertiary);">
                                        <?= date('d/m', strtotime($v['vencimento'])) ?>
                                        • <?= $v['tipo'] === 'pagar' ? 'A pagar' : 'A receber' ?>
                                    </div>
                                </div>
                                <div style="font-size:13px;font-weight:500;margin-left:12px;flex-shrink:0;color:<?= $v['tipo'] === 'pagar' ? 'var(--color-danger,#ef4444)' : 'var(--color-success,#22c55e)' ?>;">
                                    <?= $v['tipo'] === 'pagar' ? '−' : '+' ?> R$ <?= number_format($v['valor'] - ($v['valor_pago'] ?? $v['valor_recebido'] ?? 0), 2, ',', '.') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>

            <!-- ─── CONTAS VENCIDAS + ÚLTIMOS LANÇAMENTOS ─── -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">

                <!-- Contas vencidas -->
                <div class="zf-table-card" style="padding:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <span style="font-weight:500;font-size:15px;">
                            <i class="ti ti-alert-triangle" style="color:var(--color-danger,#ef4444);"></i>
                            Contas vencidas
                        </span>
                    </div>
                    <?php
                    $vencidas_todas = array_filter(
                        array_merge(
                            array_map(fn($c) => array_merge($c, ['_tipo' => 'pagar']),   $contas_vencidas_pagar   ?? []),
                            array_map(fn($c) => array_merge($c, ['_tipo' => 'receber']), $contas_vencidas_receber ?? [])
                        ),
                        fn($c) => true
                    );
                    usort($vencidas_todas, fn($a, $b) => strcmp($a['vencimento'], $b['vencimento']));
                    ?>
                    <?php if (empty($vencidas_todas)): ?>
                        <div style="color:var(--text-tertiary);font-size:13px;text-align:center;padding:16px 0;">
                            <i class="ti ti-circle-check" style="font-size:24px;color:var(--color-success,#22c55e);display:block;margin-bottom:6px;"></i>
                            Nenhuma conta vencida!
                        </div>
                    <?php else: ?>
                        <table style="width:100%;border-collapse:collapse;font-size:13px;">
                            <thead>
                                <tr style="color:var(--text-tertiary);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">
                                    <th style="text-align:left;padding-bottom:8px;">Descrição</th>
                                    <th style="text-align:left;padding-bottom:8px;">Tipo</th>
                                    <th style="text-align:right;padding-bottom:8px;">Venceu</th>
                                    <th style="text-align:right;padding-bottom:8px;">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($vencidas_todas, 0, 6) as $c): ?>
                                    <tr style="border-top:1px solid var(--color-border-tertiary);">
                                        <td style="padding:7px 0;">
                                            <div style="font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px;" title="<?= e($c['descricao']) ?>">
                                                <?= e($c['descricao']) ?>
                                            </div>
                                        </td>
                                        <td style="padding:7px 0;">
                                            <?php if ($c['_tipo'] === 'pagar'): ?>
                                                <span style="font-size:11px;padding:2px 7px;border-radius:20px;background:rgba(239,68,68,.1);color:#ef4444;">Pagar</span>
                                            <?php else: ?>
                                                <span style="font-size:11px;padding:2px 7px;border-radius:20px;background:rgba(34,197,94,.1);color:#22c55e;">Receber</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:7px 0;text-align:right;color:var(--color-danger,#ef4444);font-size:12px;">
                                            <?= date('d/m/Y', strtotime($c['vencimento'])) ?>
                                        </td>
                                        <td style="padding:7px 0;text-align:right;font-weight:500;">
                                            R$ <?= number_format($c['valor'], 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (count($vencidas_todas) > 6): ?>
                            <div style="text-align:center;margin-top:12px;font-size:12px;color:var(--text-tertiary);">
                                + <?= count($vencidas_todas) - 6 ?> outras —
                                <a href="/financeiro/pagar?status=vencida" style="color:var(--color-primary);">ver pagar</a> /
                                <a href="/financeiro/receber?status=vencido" style="color:var(--color-primary);">ver receber</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Últimos lançamentos -->
                <div class="zf-table-card" style="padding:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <span style="font-weight:500;font-size:15px;">
                            <i class="ti ti-clock"></i> Últimos lançamentos
                        </span>
                    </div>
                    <?php if (empty($ultimos_lancamentos)): ?>
                        <div style="color:var(--text-tertiary);font-size:13px;text-align:center;padding:16px 0;">
                            Nenhum lançamento recente.
                        </div>
                    <?php else: ?>
                        <table style="width:100%;border-collapse:collapse;font-size:13px;">
                            <thead>
                                <tr style="color:var(--text-tertiary);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">
                                    <th style="text-align:left;padding-bottom:8px;">Descrição</th>
                                    <th style="text-align:left;padding-bottom:8px;">Tipo</th>
                                    <th style="text-align:right;padding-bottom:8px;">Valor</th>
                                    <th style="text-align:right;padding-bottom:8px;">Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimos_lancamentos as $l): ?>
                                    <tr style="border-top:1px solid var(--color-border-tertiary);">
                                        <td style="padding:7px 0;">
                                            <div style="font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px;" title="<?= e($l['descricao']) ?>">
                                                <?= e($l['descricao']) ?>
                                            </div>
                                            <?php if (!empty($l['categoria_nome'])): ?>
                                                <div style="font-size:11px;color:var(--text-tertiary);"><?= e($l['categoria_nome']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:7px 0;">
                                            <?php if ($l['_tipo'] === 'pagar'): ?>
                                                <span style="font-size:11px;padding:2px 7px;border-radius:20px;background:rgba(239,68,68,.1);color:#ef4444;">Pagar</span>
                                            <?php else: ?>
                                                <span style="font-size:11px;padding:2px 7px;border-radius:20px;background:rgba(34,197,94,.1);color:#22c55e;">Receber</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:7px 0;text-align:right;font-weight:500;color:<?= $l['_tipo'] === 'pagar' ? 'var(--color-danger,#ef4444)' : 'var(--color-success,#22c55e)' ?>;">
                                            R$ <?= number_format($l['valor'], 2, ',', '.') ?>
                                        </td>
                                        <td style="padding:7px 0;text-align:right;color:var(--text-tertiary);font-size:12px;">
                                            <?= date('d/m/Y', strtotime($l['criado_em'])) ?>
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
                        ['/financeiro/pagar',          'ti-arrow-up-circle',    'Contas a Pagar'],
                        ['/financeiro/receber',        'ti-arrow-down-circle',  'Contas a Receber'],
                        ['/financeiro/fluxo',          'ti-arrows-exchange',    'Fluxo de Caixa'],
                        ['/financeiro/pagar/criar',    'ti-circle-plus',        'Nova Conta Pagar'],
                        ['/financeiro/receber/criar',  'ti-circle-plus',        'Nova Conta Receber'],
                        ['/compras',                   'ti-shopping-cart',      'Compras'],
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

        const labels = <?= json_encode(array_column($fluxo_30dias, 'dia'))      ?>;
        const entradas = <?= json_encode(array_column($fluxo_30dias, 'entradas')) ?>;
        const saidas = <?= json_encode(array_column($fluxo_30dias, 'saidas'))   ?>;

        new Chart(document.getElementById('chartFluxo'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                        label: 'Entradas R$',
                        data: entradas,
                        backgroundColor: isDark ? 'rgba(29,158,117,.35)' : 'rgba(15,110,86,.2)',
                        borderColor: isDark ? '#1D9E75' : '#0F6E56',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        borderSkipped: false,
                        order: 2,
                    },
                    {
                        label: 'Saídas R$',
                        data: saidas,
                        backgroundColor: isDark ? 'rgba(239,68,68,.25)' : 'rgba(239,68,68,.15)',
                        borderColor: '#ef4444',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        borderSkipped: false,
                        order: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
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