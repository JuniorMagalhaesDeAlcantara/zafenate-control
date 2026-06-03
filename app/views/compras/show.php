<?php require VIEW_PATH . '/layouts/header.php'; ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap');

    :root {
        --c-bg: #f7f6f3;
        --c-surface: #ffffff;
        --c-surface-alt: #f2f1ee;
        --c-border: #e4e2dc;
        --c-border-strong: #ccc9c0;
        --c-text-primary: #1a1916;
        --c-text-secondary: #6b6860;
        --c-text-muted: #a09d97;
        --c-accent: #2563eb;
        --c-accent-soft: #eff4ff;
        --c-success: #16a34a;
        --c-success-soft: #f0fdf4;
        --c-warning: #d97706;
        --c-warning-soft: #fffbeb;
        --c-danger: #dc2626;
        --c-danger-soft: #fef2f2;
        --radius-sm: 6px;
        --radius-md: 10px;
        --radius-lg: 14px;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, .08), 0 2px 4px rgba(0, 0, 0, .04);
        --shadow-lg: 0 20px 40px rgba(0, 0, 0, .12), 0 8px 16px rgba(0, 0, 0, .06);
        font-family: 'DM Sans', sans-serif;
    }

    /* ── Layout shell ── */
    .po-page {
        background: var(--c-bg);
        min-height: 100vh;
    }

    .po-wrap {
        max-width: 1160px;
        margin: 0 auto;
        padding: 32px 24px 64px;
    }

    /* ── Breadcrumb + header ── */
    .po-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 500;
        color: var(--c-text-muted);
        text-transform: uppercase;
        letter-spacing: .6px;
        margin-bottom: 28px;
    }

    .po-breadcrumb a {
        color: var(--c-text-muted);
        text-decoration: none;
        transition: color .15s;
    }

    .po-breadcrumb a:hover {
        color: var(--c-text-primary);
    }

    .po-breadcrumb svg {
        width: 12px;
        height: 12px;
        flex-shrink: 0;
    }

    .po-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 32px;
        flex-wrap: wrap;
    }

    .po-header-left {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .po-title-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .po-number {
        font-size: 26px;
        font-weight: 700;
        letter-spacing: -.5px;
        color: var(--c-text-primary);
        line-height: 1;
    }

    .po-nf-tag {
        font-size: 11px;
        font-weight: 500;
        color: var(--c-text-muted);
        background: var(--c-surface-alt);
        border: 1px solid var(--c-border);
        border-radius: 20px;
        padding: 3px 10px;
        font-family: 'DM Mono', monospace;
        letter-spacing: .3px;
    }

    .po-meta {
        font-size: 13px;
        color: var(--c-text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .po-meta svg {
        width: 13px;
        height: 13px;
    }

    /* ── Badges ── */
    .po-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .4px;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 20px;
    }

    .po-badge--draft {
        background: var(--c-warning-soft);
        color: var(--c-warning);
    }

    .po-badge--ok {
        background: var(--c-success-soft);
        color: var(--c-success);
    }

    .po-badge--cancel {
        background: var(--c-danger-soft);
        color: var(--c-danger);
    }

    .po-badge svg {
        width: 11px;
        height: 11px;
    }

    /* ── Actions ── */
    .po-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .po-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: 'DM Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 18px;
        border-radius: var(--radius-sm);
        cursor: pointer;
        border: 1.5px solid transparent;
        transition: all .15s;
        text-decoration: none;
        white-space: nowrap;
        line-height: 1;
    }

    .po-btn svg {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
    }

    .po-btn--primary {
        background: var(--text-primary);
        color: #fff;
        border-color: var(--surface);
        box-shadow: 0 1px 4px rgba(37, 99, 235, .3);
    }

    .po-btn--primary:hover {
        background: var(--text-primary);
        border-color:var(--text-primary);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, .35);
    }

    .po-btn--outline {
        background: var(--c-surface);
        color: var(--c-text-secondary);
        border-color: var(--c-border);
        box-shadow: var(--shadow-sm);
    }

    .po-btn--outline:hover {
        border-color: var(--c-border-strong);
        color: var(--c-text-primary);
        background: var(--c-surface-alt);
    }

    .po-btn--danger {
        background: var(--c-surface);
        color: var(--c-danger);
        border-color: #fca5a5;
        box-shadow: var(--shadow-sm);
    }

    .po-btn--danger:hover {
        background: var(--c-danger-soft);
        border-color: var(--c-danger);
    }

    /* ── Grid ── */
    .po-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 20px;
        align-items: start;
    }

    /* ── Card ── */
    .po-card {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .po-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-bottom: 1px solid var(--c-border);
        background: var(--c-surface-alt);
    }

    .po-card-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .7px;
        color: var(--c-text-muted);
        margin: 0;
    }

    .po-card-count {
        font-size: 12px;
        font-weight: 500;
        color: var(--c-text-muted);
        background: var(--c-border);
        border-radius: 20px;
        padding: 2px 10px;
    }

    /* ── Table ── */
    .po-table {
        width: 100%;
        border-collapse: collapse;
    }

    .po-table th {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--c-text-muted);
        padding: 10px 16px;
        text-align: left;
        background: var(--c-surface-alt);
        border-bottom: 1px solid var(--c-border);
    }

    .po-table th:first-child {
        padding-left: 24px;
    }

    .po-table th:last-child {
        padding-right: 24px;
    }

    .po-table td {
        padding: 14px 16px;
        font-size: 13.5px;
        color: var(--c-text-primary);
        border-bottom: 1px solid var(--c-border);
        vertical-align: middle;
    }

    .po-table td:first-child {
        padding-left: 24px;
    }

    .po-table td:last-child {
        padding-right: 24px;
    }

    .po-table tbody tr:last-child td {
        border-bottom: none;
    }

    .po-table tbody tr {
        transition: background .1s;
    }

    .po-table tbody tr:hover {
        background: var(--c-bg);
    }

    .po-table .idx {
        font-family: 'DM Mono', monospace;
        font-size: 11px;
        color: var(--c-text-muted);
        width: 32px;
    }

    .po-table .prod-name {
        font-weight: 500;
        color: var(--c-text-primary);
    }

    .po-table .prod-code {
        font-size: 11px;
        color: var(--c-text-muted);
        font-family: 'DM Mono', monospace;
        margin-top: 2px;
    }

    .po-table .qty {
        font-family: 'DM Mono', monospace;
        font-weight: 500;
    }

    .po-table .price {
        font-family: 'DM Mono', monospace;
        text-align: right;
    }

    .po-table .disc {
        font-family: 'DM Mono', monospace;
        text-align: right;
        color: var(--c-danger);
    }

    .po-table .subtotal {
        font-family: 'DM Mono', monospace;
        font-weight: 700;
        text-align: right;
    }

    .po-stock-ok {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        background: var(--c-success-soft);
        border-radius: 50%;
        color: var(--c-success);
    }

    .po-stock-no {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        background: var(--c-surface-alt);
        border-radius: 50%;
        color: var(--c-text-muted);
    }

    .po-stock-ok svg,
    .po-stock-no svg {
        width: 12px;
        height: 12px;
    }

    /* ── Totals strip ── */
    .po-totals {
        padding: 16px 24px 20px;
        border-top: 1px solid var(--c-border);
        display: flex;
        justify-content: flex-end;
    }

    .po-totals-inner {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 280px;
    }

    .po-totals-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        font-size: 13px;
        color: var(--c-text-muted);
    }

    .po-totals-row .val {
        font-family: 'DM Mono', monospace;
        font-weight: 500;
        color: var(--c-text-secondary);
    }

    .po-totals-row.disc .val {
        color: var(--c-danger);
    }

    .po-totals-total {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        font-size: 18px;
        font-weight: 700;
        color: var(--c-text-primary);
        border-top: 1px solid var(--c-border);
        padding-top: 10px;
        margin-top: 4px;
    }

    .po-totals-total .val {
        font-family: 'DM Mono', monospace;
        color: var(--c-accent);
    }

    /* ── Empty state ── */
    .po-empty {
        text-align: center;
        padding: 56px 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .po-empty svg {
        width: 36px;
        height: 36px;
        color: var(--c-text-muted);
        opacity: .5;
    }

    .po-empty p {
        font-size: 13px;
        color: var(--c-text-muted);
        margin: 0;
    }

    /* ── Info cards (side) ── */
    .po-info-card {
        padding: 20px 22px;
    }

    .po-info-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--c-text-muted);
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 14px;
    }

    .po-info-label svg {
        width: 12px;
        height: 12px;
    }

    .po-supplier-name {
        font-size: 15px;
        font-weight: 700;
        color: var(--c-text-primary);
        margin-bottom: 4px;
    }

    .po-supplier-fantasy {
        font-size: 12px;
        color: var(--c-text-muted);
        margin-bottom: 10px;
    }

    .po-detail-row {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 12.5px;
        color: var(--c-text-secondary);
        margin-bottom: 6px;
    }

    .po-detail-row svg {
        width: 13px;
        height: 13px;
        color: var(--c-text-muted);
        flex-shrink: 0;
    }

    .po-detail-row:last-child {
        margin-bottom: 0;
    }

    .po-kv-list {
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .po-kv {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 8px;
    }

    .po-kv-key {
        font-size: 12px;
        color: var(--c-text-muted);
    }

    .po-kv-val {
        font-size: 13px;
        font-weight: 600;
        color: var(--c-text-primary);
        text-align: right;
    }

    .po-kv-val.mono {
        font-family: 'DM Mono', monospace;
        font-size: 12px;
    }

    /* ── Note / cancel reason cards ── */
    .po-note {
        padding: 20px 24px;
        font-size: 13.5px;
        line-height: 1.7;
        color: var(--c-text-secondary);
    }

    .po-cancel-card {
        border-left: 3px solid var(--c-danger);
    }

    .po-cancel-card .po-info-label {
        color: var(--c-danger);
    }

    /* ── Flash alerts ── */
    .po-flash {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 20px;
        border: 1px solid transparent;
    }

    .po-flash svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    .po-flash--success {
        background: var(--c-success-soft);
        color: var(--c-success);
        border-color: #bbf7d0;
    }

    .po-flash--danger {
        background: var(--c-danger-soft);
        color: var(--c-danger);
        border-color: #fecaca;
    }

    /* ── Warning inline ── */
    .po-warning-box {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        background: var(--c-danger-soft);
        border: 1px solid #fecaca;
        border-radius: var(--radius-sm);
        font-size: 12.5px;
        color: var(--c-danger);
        margin-bottom: 16px;
        line-height: 1.5;
    }

    .po-warning-box svg {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* ── Divider ── */
    .po-divider {
        height: 1px;
        background: var(--c-border);
        margin: 0 22px;
    }

    /* ── Modal ── */
    .po-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 500;
        background: rgba(15, 13, 10, .5);
        backdrop-filter: blur(2px);
        align-items: center;
        justify-content: center;
    }

    .po-modal-box {
        background: var(--c-surface);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        width: 480px;
        max-width: calc(100vw - 32px);
        overflow: hidden;
        animation: po-modal-in .2s ease;
    }

    @keyframes po-modal-in {
        from {
            opacity: 0;
            transform: translateY(8px) scale(.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .po-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px;
        border-bottom: 1px solid var(--c-border);
    }

    .po-modal-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--c-danger);
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .po-modal-title svg {
        width: 17px;
        height: 17px;
    }

    .po-modal-close {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--c-text-muted);
        padding: 4px;
        border-radius: 4px;
        display: flex;
        transition: color .15s;
    }

    .po-modal-close:hover {
        color: var(--c-text-primary);
    }

    .po-modal-close svg {
        width: 18px;
        height: 18px;
    }

    .po-modal-body {
        padding: 22px;
    }

    .po-field label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--c-text-muted);
        margin-bottom: 7px;
    }

    .po-field label span {
        color: var(--c-danger);
    }

    .po-field textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 10px 12px;
        font-family: 'DM Sans', sans-serif;
        font-size: 13.5px;
        border: 1.5px solid var(--c-border);
        border-radius: var(--radius-sm);
        background: var(--c-bg);
        color: var(--c-text-primary);
        resize: vertical;
        outline: none;
        transition: border-color .15s;
    }

    .po-field textarea:focus {
        border-color: var(--c-accent);
        background: var(--c-surface);
    }

    .po-modal-foot {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        padding: 0 22px 20px;
    }
</style>

<div class="zf-layout po-page">

    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>

    <div class="zf-main">

        <?php require VIEW_PATH . '/layouts/navbar.php'; ?>

        <div class="zf-content">
            <div class="po-wrap">

                <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
                    <div class="po-flash po-flash--success">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        <?= e($msg) ?>
                    </div>
                <?php endif; ?>
                <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                    <div class="po-flash po-flash--danger">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        <?= e($msg) ?>
                    </div>
                <?php endif; ?>

                <?php
                $badgeClass = match ($compra['status']) {
                    'confirmada' => 'po-badge--ok',
                    'cancelada'  => 'po-badge--cancel',
                    default      => 'po-badge--draft',
                };
                $badgeLabel = match ($compra['status']) {
                    'confirmada' => 'Confirmada',
                    'cancelada'  => 'Cancelada',
                    default      => 'Rascunho',
                };
                $isRascunho   = $compra['status'] === 'rascunho';
                $isConfirmada = $compra['status'] === 'confirmada';
                ?>

                <!-- Breadcrumb -->
                <nav class="po-breadcrumb">
                    <a href="/compras">Compras</a>
                    <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 2l4 4-4 4" />
                    </svg>
                    <?= e($compra['numero']) ?>
                </nav>

                <!-- Header -->
                <div class="po-header">
                    <div class="po-header-left">
                        <div class="po-title-row">
                            <h1 class="po-number"><?= e($compra['numero']) ?></h1>
                            <span class="po-badge <?= $badgeClass ?>">
                                <?php if ($compra['status'] === 'confirmada'): ?>
                                    <svg viewBox="0 0 12 12" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.22 2.97a.75.75 0 011.06 1.06l-5.5 5.5a.75.75 0 01-1.06 0l-2.5-2.5a.75.75 0 111.06-1.06l1.97 1.97 4.97-4.97z" clip-rule="evenodd" />
                                    </svg>
                                <?php elseif ($compra['status'] === 'cancelada'): ?>
                                    <svg viewBox="0 0 12 12" fill="currentColor">
                                        <path fill-rule="evenodd" d="M1.22 1.22a.75.75 0 011.06 0L6 4.94l3.72-3.72a.75.75 0 111.06 1.06L7.06 6l3.72 3.72a.75.75 0 11-1.06 1.06L6 7.06l-3.72 3.72a.75.75 0 01-1.06-1.06L4.94 6 1.22 2.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
                                    </svg>
                                <?php else: ?>
                                    <svg viewBox="0 0 12 12" fill="currentColor">
                                        <path d="M6 1a.75.75 0 01.75.75v3.5h3.5a.75.75 0 010 1.5h-3.5v3.5a.75.75 0 01-1.5 0v-3.5H1.75a.75.75 0 010-1.5h3.5v-3.5A.75.75 0 016 1z" />
                                    </svg>
                                <?php endif; ?>
                                <?= $badgeLabel ?>
                            </span>
                            <?php if ($compra['numero_nf']): ?>
                                <span class="po-nf-tag">NF <?= e($compra['numero_nf']) ?><?= $compra['serie_nf'] ? ' · Série ' . e($compra['serie_nf']) : '' ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="po-meta">
                            <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="7" cy="5" r="3" />
                                <path d="M1 13c0-3.3 2.7-6 6-6s6 2.7 6 6" />
                            </svg>
                            Criada por <strong style="color:var(--c-text-secondary)"><?= e($compra['usuario_nome']) ?></strong>
                            &nbsp;·&nbsp;
                            <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="1" y="2" width="12" height="11" rx="1.5" />
                                <path d="M1 6h12M5 2V1M9 2V1" />
                            </svg>
                            <?= date('d/m/Y', strtotime($compra['criado_em'])) ?>
                        </div>
                    </div>

                    <div class="po-actions">
                        <?php if ($isRascunho): ?>
                            <a href="/compras/<?= $compra['id'] ?>/editar" class="po-btn po-btn--outline">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path d="M11 2.5l2.5 2.5-8 8H3v-2.5l8-8z" />
                                </svg>
                                Editar
                            </a>
                            <form method="POST" action="/compras/<?= $compra['id'] ?>/confirmar" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="po-btn po-btn--primary"
                                    onclick="return confirm('Confirmar esta compra? O estoque será atualizado automaticamente.')">
                                    <svg viewBox="0 0 16 16" fill="currentColor">
                                        <path fill-rule="evenodd" d="M13.78 3.97a.75.75 0 010 1.06l-7.5 7.5a.75.75 0 01-1.06 0l-3.5-3.5a.75.75 0 111.06-1.06L5.75 10.94l6.97-6.97a.75.75 0 011.06 0z" clip-rule="evenodd" />
                                    </svg>
                                    Confirmar Compra
                                </button>
                            </form>
                        <?php endif; ?>
                        <?php if ($compra['status'] !== 'cancelada'): ?>
                            <button type="button" class="po-btn po-btn--danger" id="btn-cancelar">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <circle cx="8" cy="8" r="6.25" />
                                    <path d="M5.5 5.5l5 5M10.5 5.5l-5 5" />
                                </svg>
                                Cancelar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Body grid -->
                <div class="po-grid">

                    <!-- Left column -->
                    <div style="display:flex;flex-direction:column;gap:16px;">

                        <!-- Items table -->
                        <div class="po-card">
                            <div class="po-card-header">
                                <h2 class="po-card-title">Itens da Compra</h2>
                                <span class="po-card-count"><?= count($itens) ?> <?= count($itens) === 1 ? 'item' : 'itens' ?></span>
                            </div>

                            <?php if (empty($itens)): ?>
                                <div class="po-empty">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                    </svg>
                                    <p>Nenhum item nesta compra.</p>
                                </div>
                            <?php else: ?>
                                <table class="po-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Produto</th>
                                            <th style="text-align:center;">Qtd</th>
                                            <th style="text-align:right;">Preço Unit.</th>
                                            <th style="text-align:right;">Desconto</th>
                                            <th style="text-align:right;">Subtotal</th>
                                            <?php if ($isConfirmada): ?>
                                                <th style="text-align:center;">Estoque</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($itens as $i => $item): ?>
                                            <tr>
                                                <td class="idx"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></td>
                                                <td>
                                                    <div class="prod-name"><?= e($item['produto_nome']) ?></div>
                                                    <?php if ($item['produto_codigo']): ?>
                                                        <div class="prod-code"><?= e($item['produto_codigo']) ?> · <?= e($item['unidade_sigla']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:center;" class="qty">
                                                    <?= number_format($item['quantidade'], 3, ',', '.') ?>
                                                </td>
                                                <td class="price">R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                                                <td class="disc">
                                                    <?= $item['desconto_item'] > 0 ? '−&nbsp;R$&nbsp;' . number_format($item['desconto_item'], 2, ',', '.') : '<span style="color:var(--c-text-muted)">—</span>' ?>
                                                </td>
                                                <td class="subtotal">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></td>
                                                <?php if ($isConfirmada): ?>
                                                    <td style="text-align:center;">
                                                        <?php if ($item['estoque_atualizado']): ?>
                                                            <span class="po-stock-ok" title="Estoque atualizado">
                                                                <svg viewBox="0 0 12 12" fill="currentColor">
                                                                    <path fill-rule="evenodd" d="M10.22 2.97a.75.75 0 011.06 1.06l-5.5 5.5a.75.75 0 01-1.06 0l-2.5-2.5a.75.75 0 111.06-1.06l1.97 1.97 4.97-4.97z" clip-rule="evenodd" />
                                                                </svg>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="po-stock-no" title="Não atualizado">
                                                                <svg viewBox="0 0 12 12" fill="currentColor">
                                                                    <path d="M2 6h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                                </svg>
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <div class="po-totals">
                                    <div class="po-totals-inner">
                                        <div class="po-totals-row">
                                            <span>Subtotal itens</span>
                                            <span class="val">R$ <?= number_format($compra['subtotal'], 2, ',', '.') ?></span>
                                        </div>
                                        <?php if ($compra['frete'] > 0): ?>
                                            <div class="po-totals-row">
                                                <span>Frete</span>
                                                <span class="val">+ R$ <?= number_format($compra['frete'], 2, ',', '.') ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($compra['desconto_valor'] > 0): ?>
                                            <div class="po-totals-row disc">
                                                <span>Desconto</span>
                                                <span class="val">− R$ <?= number_format($compra['desconto_valor'], 2, ',', '.') ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="po-totals-total">
                                            <span>Total</span>
                                            <span class="val">R$ <?= number_format($compra['total'], 2, ',', '.') ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Observação -->
                        <?php if ($compra['observacao']): ?>
                            <div class="po-card">
                                <div class="po-card-header">
                                    <h2 class="po-card-title">Observação</h2>
                                </div>
                                <div class="po-note"><?= nl2br(e($compra['observacao'])) ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Motivo cancelamento -->
                        <?php if ($compra['status'] === 'cancelada' && !empty($compra['motivo_cancelamento'])): ?>
                            <div class="po-card po-cancel-card">
                                <div class="po-card-header">
                                    <h2 class="po-card-title" style="color:var(--c-danger);">Motivo do Cancelamento</h2>
                                </div>
                                <div class="po-note"><?= nl2br(e($compra['motivo_cancelamento'])) ?></div>
                            </div>
                        <?php endif; ?>

                    </div>

                    <!-- Right column (sticky) -->
                    <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:16px;">

                        <!-- Fornecedor -->
                        <div class="po-card">
                            <div class="po-info-card">
                                <div class="po-info-label">
                                    <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="1" y="3" width="12" height="9" rx="1.5" />
                                        <path d="M5 3V2a2 2 0 014 0v1" />
                                    </svg>
                                    Fornecedor
                                </div>
                                <div class="po-supplier-name"><?= e($compra['fornecedor_nome']) ?></div>
                                <?php if ($compra['fornecedor_fantasia']): ?>
                                    <div class="po-supplier-fantasy"><?= e($compra['fornecedor_fantasia']) ?></div>
                                <?php endif; ?>
                                <?php if ($compra['fornecedor_cnpj']): ?>
                                    <div class="po-detail-row">
                                        <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="2" y="2" width="10" height="10" rx="1.5" />
                                            <path d="M5 6h4M5 8.5h2" />
                                        </svg>
                                        <?= e($compra['fornecedor_cnpj']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($compra['fornecedor_telefone']): ?>
                                    <div class="po-detail-row">
                                        <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 3a1.5 1.5 0 011.5-1.5h.75c.2 0 .38.12.46.3l1 2.25a.5.5 0 01-.11.56L4.5 5.5a8.5 8.5 0 004 4l.89-1.11a.5.5 0 01.56-.11l2.25 1c.18.08.3.26.3.46V10.5A1.5 1.5 0 0111 12h-.5C5.701 12 2 8.3 2 3.5V3z" />
                                        </svg>
                                        <?= e($compra['fornecedor_telefone']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($compra['fornecedor_email']): ?>
                                    <div class="po-detail-row">
                                        <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="1" y="3" width="12" height="8.5" rx="1.5" />
                                            <path d="M1 4.5l6 4 6-4" />
                                        </svg>
                                        <?= e($compra['fornecedor_email']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Datas -->
                        <div class="po-card">
                            <div class="po-info-card">
                                <div class="po-info-label">
                                    <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="1" y="2" width="12" height="11" rx="1.5" />
                                        <path d="M1 6h12M5 2V1M9 2V1" />
                                    </svg>
                                    Datas
                                </div>
                                <div class="po-kv-list">
                                    <div class="po-kv">
                                        <span class="po-kv-key">Emissão</span>
                                        <span class="po-kv-val mono"><?= date('d/m/Y', strtotime($compra['data_emissao'])) ?></span>
                                    </div>
                                    <?php if ($compra['data_entrega']): ?>
                                        <div class="po-kv">
                                            <span class="po-kv-key">Entrega</span>
                                            <span class="po-kv-val mono"><?= date('d/m/Y', strtotime($compra['data_entrega'])) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Pagamento -->
                        <?php if ($compra['forma_pagamento'] || $compra['vencimento']): ?>
                            <div class="po-card">
                                <div class="po-info-card">
                                    <div class="po-info-label">
                                        <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="1" y="3.5" width="12" height="8" rx="1.5" />
                                            <path d="M1 7h12M4 7v4.5M4 3.5V2" />
                                        </svg>
                                        Pagamento
                                    </div>
                                    <div class="po-kv-list">
                                        <?php if ($compra['forma_pagamento']): ?>
                                            <div class="po-kv">
                                                <span class="po-kv-key">Forma</span>
                                                <span class="po-kv-val" style="text-transform:capitalize;"><?= e($compra['forma_pagamento']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($compra['prazo_pagamento']): ?>
                                            <div class="po-kv">
                                                <span class="po-kv-key">Prazo</span>
                                                <span class="po-kv-val"><?= $compra['prazo_pagamento'] ?> dias</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($compra['vencimento']): ?>
                                            <?php
                                            $venc     = new DateTime($compra['vencimento']);
                                            $hoje     = new DateTime();
                                            $diffDias = (int)$hoje->diff($venc)->format('%r%a');
                                            $vencCor  = $diffDias < 0 ? 'var(--c-danger)' : ($diffDias <= 7 ? 'var(--c-warning)' : 'var(--c-text-primary)');
                                            ?>
                                            <div class="po-kv">
                                                <span class="po-kv-key">Vencimento</span>
                                                <span class="po-kv-val mono" style="color:<?= $vencCor ?>;">
                                                    <?= date('d/m/Y', strtotime($compra['vencimento'])) ?>
                                                    <?php if ($compra['status'] !== 'cancelada'): ?>
                                                        <?php if ($diffDias < 0): ?>
                                                            &nbsp;<span style="font-family:'DM Sans',sans-serif;font-size:11px;font-weight:600;"><?= abs($diffDias) ?>d atraso</span>
                                                        <?php elseif ($diffDias === 0): ?>
                                                            &nbsp;<span style="font-family:'DM Sans',sans-serif;font-size:11px;font-weight:600;">hoje</span>
                                                        <?php elseif ($diffDias <= 7): ?>
                                                            &nbsp;<span style="font-family:'DM Sans',sans-serif;font-size:11px;font-weight:600;">em <?= $diffDias ?>d</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal: Cancelar Compra -->
<div id="modal-cancelar" class="po-modal-overlay">
    <div class="po-modal-box">
        <div class="po-modal-head">
            <h3 class="po-modal-title">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7">
                    <circle cx="8" cy="8" r="6.25" />
                    <path d="M5.5 5.5l5 5M10.5 5.5l-5 5" />
                </svg>
                Cancelar Compra
            </h3>
            <button type="button" id="modal-cancel-fechar" class="po-modal-close">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M3 3l10 10M13 3L3 13" />
                </svg>
            </button>
        </div>
        <div class="po-modal-body">
            <?php if ($isConfirmada): ?>
                <div class="po-warning-box">
                    <svg viewBox="0 0 16 16" fill="currentColor">
                        <path fill-rule="evenodd" d="M6.701 2.25c.577-1 2.02-1 2.598 0l5.5 9.5c.577 1-.144 2.25-1.299 2.25H2.5c-1.155 0-1.876-1.25-1.299-2.25l5.5-9.5zM8 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 018 5zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    <span><strong>Atenção:</strong> Esta compra já foi confirmada. O cancelamento irá <strong>estornar o estoque</strong> de todos os produtos.</span>
                </div>
            <?php endif; ?>

            <form method="POST" action="/compras/<?= $compra['id'] ?>/cancelar" id="form-cancelar">
                <?= csrf_field() ?>
                <div class="po-field" style="margin-bottom:16px;">
                    <label>Motivo do cancelamento <span>*</span></label>
                    <textarea name="motivo" rows="3" required placeholder="Descreva o motivo do cancelamento…"></textarea>
                </div>
            </form>
        </div>
        <div class="po-modal-foot">
            <button type="button" id="modal-cancel-cancelar" class="po-btn po-btn--outline">Voltar</button>
            <button type="submit" form="form-cancelar" class="po-btn po-btn--danger" style="background:var(--c-danger);color:#fff;border-color:var(--c-danger);">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7">
                    <circle cx="8" cy="8" r="6.25" />
                    <path d="M5.5 5.5l5 5M10.5 5.5l-5 5" />
                </svg>
                Confirmar Cancelamento
            </button>
        </div>
    </div>
</div>

<script>
    (function() {
        const modal = document.getElementById('modal-cancelar');
        const btnOpen = document.getElementById('btn-cancelar');
        const btnX = document.getElementById('modal-cancel-fechar');
        const btnBack = document.getElementById('modal-cancel-cancelar');
        const open = () => modal.style.display = 'flex';
        const close = () => modal.style.display = 'none';
        if (btnOpen) btnOpen.addEventListener('click', open);
        if (btnX) btnX.addEventListener('click', close);
        if (btnBack) btnBack.addEventListener('click', close);
        modal.addEventListener('click', e => {
            if (e.target === modal) close();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') close();
        });

        // Auto-close flash alerts
        document.querySelectorAll('[data-auto-close]').forEach(el => {
            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transition = 'opacity .4s';
                setTimeout(() => el.remove(), 400);
            }, 4000);
        });
    })();
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>