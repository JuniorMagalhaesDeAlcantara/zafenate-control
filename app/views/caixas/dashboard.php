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

            <?php
            $totalVendas      = array_sum(array_column($caixas, 'total_vendas'));
            $totalSangrias    = array_sum(array_column($caixas, 'total_sangrias'));
            $totalDivergencia = $total_diferenca ?? 0;
            $totalCaixas      = count($caixas);
            ?>

            <!-- ══════════════════════════════════════════
                 HERO: STATUS DO CAIXA + AÇÕES PRIMÁRIAS
            ══════════════════════════════════════════ -->
            <div class="cx-hero">

                <!-- Indicador de status -->
                <div class="cx-status-pill <?= $caixaAberto ? 'cx-status-open' : 'cx-status-closed' ?>">
                    <span class="cx-status-dot"></span>
                    <?= $caixaAberto ? 'Caixa aberto' : 'Caixa fechado' ?>
                </div>

                <?php if ($caixaAberto): ?>

                    <!-- Caixa aberto: mostra operador, hora de abertura e saldo em tempo real -->
                    <div class="cx-hero-body">
                        <div class="cx-hero-left">
                            <h2 class="cx-hero-title">
                                R$ <?= number_format($caixaAberto['total_vendas'] ?? 0, 2, ',', '.') ?>
                            </h2>
                            <p class="cx-hero-sub">
                                <i class="ti ti-clock"></i>
                                Aberto às <?= date('H:i', strtotime($caixaAberto['aberto_em'])) ?>
                                &nbsp;·&nbsp;
                                <i class="ti ti-user"></i>
                                <?= e($caixaAberto['operador'] ?? 'Operador') ?>
                            </p>
                        </div>
                        <div class="cx-hero-actions">
                            <a href="/pdv" class="cx-btn cx-btn-primary">
                                <i class="ti ti-device-desktop"></i> Ir para PDV
                            </a>
                            <a href="/caixa/sangria" class="cx-btn cx-btn-ghost">
                                <i class="ti ti-arrow-bar-down"></i> Sangria
                            </a>
                            <a href="/caixa/gestao" class="cx-btn cx-btn-danger">
                                <i class="ti ti-lock"></i> Fechar Caixa
                            </a>
                        </div>
                    </div>

                <?php else: ?>

                    <!-- Caixa fechado: CTA para abrir -->
                    <div class="cx-hero-body cx-hero-body--closed">
                        <div>
                            <h2 class="cx-hero-title cx-hero-title--muted">Nenhum caixa aberto</h2>
                            <p class="cx-hero-sub">Abra o caixa para começar a registrar vendas.</p>
                        </div>
                        <a href="/caixa/gestao" class="cx-btn cx-btn-primary">
                            <i class="ti ti-cash-register"></i> Abrir Caixa
                        </a>
                    </div>

                <?php endif; ?>
            </div>

            <!-- ══════════════════════════════════════════
                 CARDS DE TOTAIS DO PERÍODO
            ══════════════════════════════════════════ -->
            <div class="cx-metrics">

                <div class="cx-metric-card">
                    <div class="cx-metric-icon cx-icon-blue">
                        <i class="ti ti-trending-up"></i>
                    </div>
                    <div class="cx-metric-body">
                        <span class="cx-metric-label">Vendas no período</span>
                        <span class="cx-metric-value">R$ <?= number_format($totalVendas, 2, ',', '.') ?></span>
                        <span class="cx-metric-sub"><?= $totalCaixas ?> caixa<?= $totalCaixas !== 1 ? 's' : '' ?> fechado<?= $totalCaixas !== 1 ? 's' : '' ?></span>
                    </div>
                </div>

                <div class="cx-metric-card">
                    <div class="cx-metric-icon cx-icon-red">
                        <i class="ti ti-arrow-bar-down"></i>
                    </div>
                    <div class="cx-metric-body">
                        <span class="cx-metric-label">Sangrias</span>
                        <span class="cx-metric-value cx-value-danger">R$ <?= number_format($totalSangrias, 2, ',', '.') ?></span>
                        <span class="cx-metric-sub">retiradas da gaveta</span>
                    </div>
                </div>

                <div class="cx-metric-card">
                    <div class="cx-metric-icon <?= $totalDivergencia == 0 ? 'cx-icon-green' : (abs($totalDivergencia) <= 5 ? 'cx-icon-yellow' : 'cx-icon-red') ?>">
                        <i class="ti ti-scale"></i>
                    </div>
                    <div class="cx-metric-body">
                        <span class="cx-metric-label">Divergência total</span>
                        <span class="cx-metric-value <?= $totalDivergencia == 0 ? 'cx-value-success' : (abs($totalDivergencia) <= 5 ? 'cx-value-warning' : 'cx-value-danger') ?>">
                            <?= $totalDivergencia >= 0 ? '+' : '' ?>R$ <?= number_format($totalDivergencia, 2, ',', '.') ?>
                        </span>
                        <span class="cx-metric-sub">soma das diferenças</span>
                    </div>
                </div>

            </div>

            <!-- ══════════════════════════════════════════
                 FILTRO + TABELA DE HISTÓRICO
            ══════════════════════════════════════════ -->
            <div class="cx-section">

                <!-- Cabeçalho da seção com filtro inline -->
                <div class="cx-section-head">
                    <div>
                        <span class="cx-section-title">Histórico de caixas</span>
                        <span class="cx-count-badge"><?= $totalCaixas ?> registro<?= $totalCaixas !== 1 ? 's' : '' ?></span>
                    </div>
                    <form method="GET" class="cx-filter-form">
                        <i class="ti ti-calendar" style="color:var(--text-tertiary);font-size:14px;"></i>
                        <input type="date" name="data_inicio" value="<?= e($filtros['inicio']) ?>" class="cx-date-input">
                        <span class="cx-filter-sep">→</span>
                        <input type="date" name="data_fim" value="<?= e($filtros['fim']) ?>" class="cx-date-input">
                        <button type="submit" class="cx-btn cx-btn-sm cx-btn-ghost">
                            <i class="ti ti-filter"></i> Filtrar
                        </button>
                    </form>
                </div>

                <!-- Tabela -->
                <div class="cx-table-wrap">
                    <table class="cx-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Operador</th>
                                <th class="cx-th-right">Abertura</th>
                                <th class="cx-th-right">Vendas</th>
                                <th>Sangria</th>
                                <th class="cx-th-right">Saldo esperado</th>
                                <th class="cx-th-right">Diferença</th>
                                <th class="cx-th-center">Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($caixas)): ?>
                                <tr>
                                    <td colspan="8" class="cx-td-empty">
                                        <i class="ti ti-inbox" style="font-size:32px;display:block;margin-bottom:10px;opacity:.25;"></i>
                                        Nenhum registro para o período selecionado.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($caixas as $c): ?>
                                    <?php
                                    $dif           = (float)($c['diferenca'] ?? 0);
                                    $isOk          = $dif == 0;
                                    $isWarn        = !$isOk && abs($dif) <= 5;
                                    $isBad         = !$isOk && abs($dif) > 5;
                                    $resultClass   = $isOk ? 'cx-result-ok' : ($isWarn ? 'cx-result-warn' : 'cx-result-bad');
                                    $resultLabel   = $isOk ? 'Correto' : ($isWarn ? 'Atenção' : 'Divergência');
                                    $resultIcon    = $isOk ? 'ti-circle-check' : ($isWarn ? 'ti-alert-triangle' : 'ti-circle-x');
                                    $dataFmt       = !empty($c['fechado_em'])
                                        ? date('d/m/Y', strtotime($c['fechado_em']))
                                        : '—';
                                    $horaFmt       = !empty($c['fechado_em'])
                                        ? date('H:i', strtotime($c['fechado_em']))
                                        : '';
                                    $saldoEsperado = ($c['saldo_abertura'] ?? 0)
                                        + ($c['total_vendas'] ?? 0)
                                        - ($c['total_sangrias'] ?? 0);
                                    ?>
                                    <tr class="cx-tr">
                                        <td>
                                            <div class="cx-td-date"><?= $dataFmt ?></div>
                                            <?php if ($horaFmt): ?>
                                                <div class="cx-td-sub"><?= $horaFmt ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="cx-avatar-cell">
                                                <div class="cx-avatar"><?= mb_strtoupper(mb_substr($c['operador'] ?? 'U', 0, 1)) ?></div>
                                                <span><?= e($c['operador'] ?? '—') ?></span>
                                            </div>
                                        </td>
                                        <td class="cx-td-right cx-td-mono">
                                            R$ <?= number_format($c['saldo_abertura'], 2, ',', '.') ?>
                                        </td>
                                        <td class="cx-td-right cx-td-mono cx-td-strong">
                                            R$ <?= number_format($c['total_vendas'], 2, ',', '.') ?>
                                        </td>
                                        <td>
                                            <span class="cx-flow-chip cx-chip-out">
                                                <i class="ti ti-minus"></i>
                                                R$ <?= number_format($c['total_sangrias'], 2, ',', '.') ?>
                                            </span>
                                        </td>
                                        <td class="cx-td-right cx-td-mono">
                                            R$ <?= number_format($saldoEsperado, 2, ',', '.') ?>
                                        </td>
                                        <td class="cx-td-right cx-td-mono cx-td-strong <?= $isOk ? 'cx-value-success' : ($isWarn ? 'cx-value-warning' : 'cx-value-danger') ?>">
                                            <?= $dif >= 0 ? '+' : '' ?>R$ <?= number_format($dif, 2, ',', '.') ?>
                                        </td>
                                        <td class="cx-td-center">
                                            <span class="cx-result-badge <?= $resultClass ?>">
                                                <i class="ti <?= $resultIcon ?>"></i>
                                                <?= $resultLabel ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </div><!-- /.zf-content -->
    </div><!-- /.zf-main -->
</div><!-- /.zf-layout -->

<style>
    /* ══════════════════════════════════════════════════════
   CAIXA — estilos locais (complementam o design system)
   Usa as CSS vars do zf-layout para consistência total
══════════════════════════════════════════════════════ */

    /* ── HERO ──────────────────────────────────────────── */
    .cx-hero {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg, 12px);
        padding: 24px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }

    .cx-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg,
                transparent 60%,
                rgba(var(--color-primary-rgb, 26, 26, 26), .03) 100%);
        pointer-events: none;
    }

    .cx-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .4px;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 20px;
        margin-bottom: 16px;
    }

    .cx-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .cx-status-open {
        background: rgba(34, 197, 94, .12);
        color: #16a34a;
    }

    .cx-status-open .cx-status-dot {
        background: #22c55e;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, .25);
        animation: cx-pulse 2s infinite;
    }

    .cx-status-closed {
        background: rgba(156, 163, 175, .1);
        color: var(--text-tertiary);
    }

    .cx-status-closed .cx-status-dot {
        background: var(--text-tertiary);
    }

    @keyframes cx-pulse {

        0%,
        100% {
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .25);
        }

        50% {
            box-shadow: 0 0 0 6px rgba(34, 197, 94, .08);
        }
    }

    .cx-hero-body {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .cx-hero-body--closed {
        gap: 16px;
    }

    .cx-hero-title {
        font-size: 32px;
        font-weight: 300;
        letter-spacing: -.02em;
        margin: 0 0 6px;
        line-height: 1;
        color: var(--text-primary);
    }

    .cx-hero-title--muted {
        color: var(--text-tertiary);
        font-size: 24px;
    }

    .cx-hero-sub {
        font-size: 12px;
        color: var(--text-tertiary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .cx-hero-sub i {
        font-size: 12px;
    }

    .cx-hero-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        flex-shrink: 0;
    }

    /* ── BOTÕES ────────────────────────────────────────── */
    .cx-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 16px;
        border-radius: var(--radius-md, 8px);
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        border: 1px solid transparent;
        transition: all .15s;
        white-space: nowrap;
    }

    .cx-btn i {
        font-size: 15px;
    }

    .cx-btn-primary {
        background: var(--text-primary);
        color: var(--bg-card);
        border-color: var(--text-primary);
    }

    .cx-btn-primary:hover {
        opacity: .85;
    }

    .cx-btn-ghost {
        background: transparent;
        color: var(--text-secondary);
        border-color: var(--border);
    }

    .cx-btn-ghost:hover {
        background: var(--bg-input);
        border-color: var(--border-md);
        color: var(--text-primary);
    }

    .cx-btn-danger {
        background: rgba(239, 68, 68, .08);
        color: #dc2626;
        border-color: rgba(239, 68, 68, .2);
    }

    .cx-btn-danger:hover {
        background: rgba(239, 68, 68, .15);
        border-color: rgba(239, 68, 68, .4);
    }

    .cx-btn-sm {
        padding: 7px 12px;
        font-size: 12px;
    }

    /* ── METRICS ───────────────────────────────────────── */
    .cx-metrics {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }

    @media (max-width: 900px) {
        .cx-metrics {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .cx-metric-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-md, 8px);
        padding: 18px 16px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        transition: border-color .15s;
    }

    .cx-metric-card:hover {
        border-color: var(--border-md);
    }

    .cx-metric-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .cx-icon-blue {
        background: rgba(59, 130, 246, .1);
        color: #2563eb;
    }

    .cx-icon-red {
        background: rgba(239, 68, 68, .1);
        color: #dc2626;
    }

    .cx-icon-green {
        background: rgba(34, 197, 94, .1);
        color: #16a34a;
    }

    .cx-icon-yellow {
        background: rgba(245, 158, 11, .1);
        color: #d97706;
    }

    .cx-metric-body {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .cx-metric-label {
        font-size: 11px;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: .4px;
        font-weight: 500;
    }

    .cx-metric-value {
        font-size: 20px;
        font-weight: 500;
        color: var(--text-primary);
        letter-spacing: -.01em;
    }

    .cx-metric-sub {
        font-size: 11px;
        color: var(--text-tertiary);
    }

    /* ── SECTION / TABELA ──────────────────────────────── */
    .cx-section {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg, 12px);
        overflow: hidden;
    }

    .cx-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        gap: 16px;
        flex-wrap: wrap;
    }

    .cx-section-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .cx-count-badge {
        font-size: 11px;
        color: var(--text-tertiary);
        background: var(--bg-input);
        padding: 3px 8px;
        border-radius: 20px;
        margin-left: 8px;
    }

    .cx-filter-form {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .cx-date-input {
        font-size: 12px;
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm, 6px);
        background: var(--bg-input);
        color: var(--text-primary);
        outline: none;
        transition: border-color .15s;
    }

    .cx-date-input:focus {
        border-color: var(--border-md);
    }

    .cx-filter-sep {
        font-size: 12px;
        color: var(--text-tertiary);
    }

    .cx-table-wrap {
        overflow-x: auto;
    }

    .cx-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .cx-table thead th {
        padding: 10px 16px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: var(--text-tertiary);
        background: var(--bg-input, rgba(0, 0, 0, .02));
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .cx-th-right {
        text-align: right;
    }

    .cx-th-center {
        text-align: center;
    }

    .cx-tr {
        border-bottom: 1px solid var(--border);
        transition: background .1s;
    }

    .cx-tr:last-child {
        border-bottom: none;
    }

    .cx-tr:hover {
        background: var(--bg-input, rgba(0, 0, 0, .015));
    }

    .cx-table td {
        padding: 14px 16px;
        vertical-align: middle;
    }

    .cx-td-right {
        text-align: right;
    }

    .cx-td-center {
        text-align: center;
    }

    .cx-td-mono {
        font-variant-numeric: tabular-nums;
    }

    .cx-td-strong {
        font-weight: 600;
    }

    .cx-td-date {
        font-weight: 500;
        color: var(--text-primary);
    }

    .cx-td-sub {
        font-size: 11px;
        color: var(--text-tertiary);
        margin-top: 2px;
    }

    .cx-td-empty {
        text-align: center;
        padding: 48px 20px !important;
        color: var(--text-tertiary);
    }

    /* Avatar do operador */
    .cx-avatar-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cx-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--bg-input);
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-secondary);
        flex-shrink: 0;
    }

    /* Chips de sangria/suprimento */
    .cx-flow-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 500;
        padding: 3px 8px;
        border-radius: 6px;
    }

    .cx-flow-chip i {
        font-size: 10px;
    }

    .cx-chip-out {
        background: rgba(239, 68, 68, .08);
        color: #dc2626;
    }

    .cx-chip-in {
        background: rgba(34, 197, 94, .08);
        color: #16a34a;
    }

    /* Badge de resultado */
    .cx-result-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        white-space: nowrap;
    }

    .cx-result-badge i {
        font-size: 11px;
    }

    .cx-result-ok {
        background: rgba(34, 197, 94, .1);
        color: #16a34a;
    }

    .cx-result-warn {
        background: rgba(245, 158, 11, .1);
        color: #d97706;
    }

    .cx-result-bad {
        background: rgba(239, 68, 68, .1);
        color: #dc2626;
    }

    /* Valores coloridos */
    .cx-value-success {
        color: #16a34a !important;
    }

    .cx-value-warning {
        color: #d97706 !important;
    }

    .cx-value-danger {
        color: #dc2626 !important;
    }
</style>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>