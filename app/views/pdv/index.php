<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDV — <?= e($config['empresa_nome'] ?? APP_NAME) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <style>
        /* ── Reset & Base ── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: <?= e($config['empresa_cor'] ?? '#1A1A1A') ?>;
            --font: 'Plus Jakarta Sans', system-ui, sans-serif;
            --bg: #F5F4F1;
            --card: #FFFFFF;
            --border: rgba(0, 0, 0, 0.08);
            --border-md: rgba(0, 0, 0, 0.14);
            --text: #1A1A1A;
            --text-2: #6B6B6B;
            --text-3: #A3A3A3;
            --green: #16A34A;
            --bg-green: #DCFCE7;
            --amber: #D97706;
            --bg-amber: #FEF3C7;
            --red: #DC2626;
            --bg-red: #FEE2E2;
            --blue: #2563EB;
            --bg-blue: #DBEAFE;
            --sidebar-pdv: 340px;
            --topbar-pdv: 52px;
        }

        html,
        body {
            font-family: var(--font);
            font-size: 14px;
            color: var(--text);
            background: var(--bg);
            height: 100%;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Layout PDV ── */
        .pdv {
            display: grid;
            grid-template-rows: var(--topbar-pdv) 1fr;
            grid-template-columns: 1fr var(--sidebar-pdv);
            height: 100vh;
            gap: 0;
        }

        /* ── Topbar ── */
        .pdv-topbar {
            grid-column: 1 / -1;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            gap: 16px;
        }

        .pdv-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .pdv-brand-mark {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pdv-brand-name {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }

        .pdv-brand-sub {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.4);
        }

        .pdv-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            flex: 1;
            justify-content: center;
        }

        .pdv-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }

        .pdv-meta-item strong {
            color: #fff;
            font-weight: 500;
        }

        .pdv-meta-item .ti {
            font-size: 14px;
        }

        .pdv-top-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .pdv-tbtn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 6px;
            font-family: var(--font);
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: opacity 0.15s;
        }

        .pdv-tbtn-ghost {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.85);
        }

        .pdv-tbtn-ghost:hover {
            background: rgba(255, 255, 255, 0.18);
        }

        .pdv-tbtn-danger {
            background: rgba(220, 38, 38, 0.2);
            color: #FCA5A5;
        }

        .pdv-tbtn-danger:hover {
            background: rgba(220, 38, 38, 0.3);
        }

        /* ── Área esquerda: busca + produtos ── */
        .pdv-left {
            background: var(--bg);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-right: 1px solid var(--border);
            padding: 16px;
            gap: 14px;
        }

        /* Busca principal */
        .pdv-search-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .pdv-search-wrap .ti-search {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: var(--text-3);
            pointer-events: none;
        }

        .pdv-search-wrap .pdv-search-shortcut {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            color: var(--text-3);
            background: var(--border);
            padding: 2px 6px;
            border-radius: 4px;
            pointer-events: none;
        }

        .pdv-search {
            width: 100%;
            padding: 14px 80px 14px 44px;
            border: 2px solid var(--border-md);
            border-radius: 10px;
            background: var(--card);
            font-family: var(--font);
            font-size: 16px;
            color: var(--text);
            outline: none;
            transition: border-color 0.15s;
        }

        .pdv-search:focus {
            border-color: var(--primary);
        }

        .pdv-search::placeholder {
            color: var(--text-3);
            font-size: 15px;
        }

        /* Resultados de busca */
        .pdv-results {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 100;
            max-height: 280px;
            overflow-y: auto;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .pdv-results.show {
            display: block;
        }

        .pdv-result-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
            transition: background 0.1s;
        }

        .pdv-result-item:last-child {
            border-bottom: none;
        }

        .pdv-result-item:hover,
        .pdv-result-item.focused {
            background: var(--bg);
        }

        .pdv-result-img {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            background: var(--bg);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .pdv-result-name {
            font-weight: 500;
            font-size: 13px;
        }

        .pdv-result-code {
            font-size: 11px;
            color: var(--text-3);
            margin-top: 1px;
        }

        .pdv-result-price {
            font-weight: 600;
            font-size: 14px;
            margin-left: auto;
            flex-shrink: 0;
        }

        .pdv-result-stock {
            font-size: 11px;
            color: var(--text-3);
            text-align: right;
            margin-top: 1px;
        }

        .pdv-result-empty {
            padding: 24px;
            text-align: center;
            color: var(--text-3);
            font-size: 13px;
        }

        /* Grid de produtos recentes */
        .pdv-section-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-3);
            flex-shrink: 0;
        }

        .pdv-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 10px;
            overflow-y: auto;
            padding-bottom: 4px;
            flex: 1;
        }

        .pdv-product-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 10px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            text-align: center;
            transition: border-color 0.15s, transform 0.1s;
            user-select: none;
        }

        .pdv-product-card:hover {
            border-color: var(--border-md);
            transform: translateY(-1px);
        }

        .pdv-product-card:active {
            transform: translateY(0);
        }

        .pdv-product-card.out-of-stock {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .pdv-product-icon {
            width: 44px;
            height: 44px;
            background: var(--bg);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .pdv-product-name {
            font-size: 12px;
            font-weight: 500;
            line-height: 1.3;
            color: var(--text);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .pdv-product-price {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
        }

        .pdv-product-stock {
            font-size: 10px;
            color: var(--text-3);
        }

        /* ── Sidebar direita: carrinho ── */
        .pdv-right {
            display: flex;
            flex-direction: column;
            background: var(--card);
            overflow: hidden;
        }

        /* Header do carrinho */
        .cart-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .cart-title {
            font-size: 14px;
            font-weight: 600;
        }

        .cart-count {
            background: var(--primary);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 10px;
            min-width: 22px;
            text-align: center;
        }

        .cart-clear {
            font-size: 11px;
            color: var(--text-3);
            background: none;
            border: none;
            cursor: pointer;
            transition: color 0.12s;
            font-family: var(--font);
        }

        .cart-clear:hover {
            color: var(--red);
        }

        /* Cliente */
        .cart-cliente {
            padding: 10px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .cart-cliente-select {
            flex: 1;
            padding: 7px 10px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--bg);
            font-family: var(--font);
            font-size: 12px;
            color: var(--text);
            outline: none;
        }

        .cart-cliente-select:focus {
            border-color: var(--border-md);
        }

        /* Força o input a ocupar o espaço total */
        #busca-cliente {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--card);
            font-size: 13px;
            outline: none;
        }

        #busca-cliente:focus {
            border-color: var(--primary);
        }

        /* Lista de itens */
        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 8px 0;
        }

        .cart-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            gap: 10px;
            color: var(--text-3);
            font-size: 13px;
        }

        .cart-empty .ti {
            font-size: 36px;
            opacity: 0.25;
        }

        .cart-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 16px;
            border-bottom: 1px solid var(--border);
            position: relative;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-info {
            flex: 1;
            min-width: 0;
        }

        .cart-item-name {
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cart-item-price {
            font-size: 11px;
            color: var(--text-3);
            margin-top: 2px;
        }

        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 0;
            flex-shrink: 0;
        }

        .qty-btn {
            width: 26px;
            height: 26px;
            border: 1px solid var(--border);
            background: var(--bg);
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-2);
            transition: background 0.12s;
        }

        .qty-btn:hover {
            background: var(--border);
        }

        .qty-display {
            width: 36px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            border: none;
            background: transparent;
            color: var(--text);
            font-family: var(--font);
            padding: 0;
        }

        .cart-item-subtotal {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            flex-shrink: 0;
            min-width: 60px;
            text-align: right;
        }

        .cart-item-remove {
            position: absolute;
            top: 8px;
            right: 6px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 13px;
            color: var(--text-3);
            padding: 2px;
            transition: color 0.12s;
            line-height: 1;
        }

        .cart-item-remove:hover {
            color: var(--red);
        }

        /* Totais */
        .cart-totals {
            padding: 12px 16px;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }

        .cart-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            margin-bottom: 6px;
            color: var(--text-2);
        }

        .cart-total-row:last-child {
            margin-bottom: 0;
        }

        .cart-total-row.big {
            font-size: 18px;
            font-weight: 600;
            color: var(--text);
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--border);
        }

        .discount-wrap {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .discount-input {
            width: 60px;
            padding: 4px 6px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-family: var(--font);
            font-size: 12px;
            text-align: right;
            outline: none;
            background: var(--bg);
        }

        .discount-type {
            padding: 4px 6px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-family: var(--font);
            font-size: 12px;
            background: var(--bg);
            outline: none;
        }

        /* Botão finalizar */
        .cart-footer {
            padding: 12px 16px 16px;
            flex-shrink: 0;
        }

        .btn-finalizar {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: var(--font);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: opacity 0.15s;
        }

        .btn-finalizar:hover:not(:disabled) {
            opacity: 0.87;
        }

        .btn-finalizar:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .btn-finalizar .ti {
            font-size: 18px;
        }

        .pdv-hint {
            font-size: 10px;
            text-align: center;
            color: var(--text-3);
            margin-top: 6px;
        }

        /* ── Modal de pagamento ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 200;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal {
            background: var(--card);
            border-radius: 12px;
            width: 100%;
            max-width: 480px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.2s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(16px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-head {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-head h3 {
            font-size: 15px;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: var(--text-3);
            padding: 2px;
            transition: color 0.12s;
        }

        .modal-close:hover {
            color: var(--text);
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
        }

        /* Resumo do pagamento */
        .pgto-total-box {
            background: var(--primary);
            color: #fff;
            border-radius: 8px;
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .pgto-total-label {
            font-size: 12px;
            opacity: 0.7;
        }

        .pgto-total-value {
            font-size: 26px;
            font-weight: 600;
        }

        /* Formas de pagamento */
        .pgto-formas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 16px;
        }

        .pgto-forma-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 12px 8px;
            border: 2px solid var(--border);
            border-radius: 8px;
            background: transparent;
            cursor: pointer;
            font-family: var(--font);
            font-size: 12px;
            font-weight: 500;
            color: var(--text-2);
            transition: border-color 0.15s, background 0.15s, color 0.15s;
        }

        .pgto-forma-btn .ti {
            font-size: 22px;
        }

        .pgto-forma-btn:hover {
            border-color: var(--border-md);
            color: var(--text);
        }

        .pgto-forma-btn.selected {
            border-color: var(--primary);
            background: rgba(0, 0, 0, 0.03);
            color: var(--primary);
        }

        /* Troco */
        .pgto-dinheiro-box {
            display: none;
            margin-bottom: 16px;
        }

        .pgto-dinheiro-box.show {
            display: block;
        }

        .pgto-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-2);
            margin-bottom: 6px;
        }

        .pgto-input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-family: var(--font);
            font-size: 16px;
            font-weight: 500;
            text-align: right;
            background: var(--bg);
            outline: none;
            transition: border-color 0.15s;
        }

        .pgto-input:focus {
            border-color: var(--border-md);
            background: var(--card);
        }

        .pgto-troco-box {
            background: var(--bg-green);
            border-radius: 6px;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .pgto-troco-label {
            font-size: 12px;
            color: var(--green);
            font-weight: 500;
        }

        .pgto-troco-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--green);
        }

        /* ── Seletor de parcelas ── */
        .parcelas-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
        }

        .parcela-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            padding: 10px 4px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            cursor: pointer;
            font-family: var(--font);
            transition: border-color 0.15s, background 0.15s;
        }

        .parcela-btn:hover {
            border-color: var(--border-md);
            background: #fff;
        }

        .parcela-btn.selected {
            border-color: var(--primary);
            background: var(--primary);
        }

        .parcela-btn.selected .parcela-num,
        .parcela-btn.selected .parcela-sub {
            color: #fff;
        }

        .parcela-num {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            line-height: 1;
        }

        .parcela-sub {
            font-size: 10px;
            color: var(--text-3);
            line-height: 1;
            white-space: nowrap;
        }

        /* Botões modais */
        .btn-modal {
            flex: 1;
            padding: 12px;
            border-radius: 6px;
            border: none;
            font-family: var(--font);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: opacity 0.15s;
        }

        .btn-modal-cancel {
            background: var(--bg);
            color: var(--text-2);
        }

        .btn-modal-cancel:hover {
            background: var(--border);
        }

        .btn-modal-confirm {
            background: var(--green);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-modal-confirm:hover {
            opacity: 0.87;
        }

        .btn-modal-confirm:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Scrollbar discreta */
        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-md);
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <div class="pdv">

        <!-- ── Topbar ── -->
        <header class="pdv-topbar">
            <div class="pdv-brand">
                <?php if (!empty($config['empresa_logo'])): ?>
                    <img src="/uploads/logo/<?= e($config['empresa_logo']) ?>" alt="" style="height:24px;border-radius:4px;">
                <?php else: ?>
                    <div class="pdv-brand-mark">
                        <svg width="14" height="14" viewBox="0 0 22 22" fill="none">
                            <path d="M4 6h14M4 11h9M4 16h12" stroke="#fff" stroke-width="1.8" stroke-linecap="round" />
                            <circle cx="17" cy="15.5" r="3.5" fill="none" stroke="#C9A84C" stroke-width="1.4" />
                            <path d="M16 15.5h2M17 14.5v2" stroke="#C9A84C" stroke-width="1.2" stroke-linecap="round" />
                        </svg>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="pdv-brand-name"><?= e($config['empresa_nome'] ?? APP_NAME) ?></div>
                    <div class="pdv-brand-sub">Frente de Caixa</div>
                </div>
            </div>

            <div class="pdv-meta">
                <div class="pdv-meta-item">
                    <i class="ti ti-user"></i>
                    Operador: <strong><?= e($authUser['nome'] ?? 'Operador') ?></strong>
                </div>
                <div class="pdv-meta-item">
                    <i class="ti ti-cash-register"></i>
                    Caixa <strong>#<?= $caixa['id'] ?? '—' ?></strong>
                </div>
                <div class="pdv-meta-item">
                    <i class="ti ti-clock"></i>
                    <strong id="pdv-clock">--:--</strong>
                </div>
                <div class="pdv-meta-item">
                    <i class="ti ti-trending-up"></i>
                    Vendas hoje: <strong>R$ <?= number_format($totalVendasDia ?? 0, 2, ',', '.') ?></strong>
                </div>
            </div>

            <div class="pdv-top-actions">
                <button class="pdv-tbtn pdv-tbtn-ghost" onclick="abrirSangria()">
                    <i class="ti ti-arrow-down-circle"></i> Sangria
                </button>
                <a href="/dashboard" class="pdv-tbtn pdv-tbtn-ghost">
                    <i class="ti ti-layout-dashboard"></i> Painel
                </a>
                <button class="pdv-tbtn pdv-tbtn-danger" onclick="fecharCaixa()">
                    <i class="ti ti-lock"></i> Fechar caixa
                </button>
            </div>
        </header>

        <!-- ── Área esquerda: busca + produtos ── -->
        <main class="pdv-left">

            <!-- Busca -->
            <div class="pdv-search-wrap" style="position:relative">
                <i class="ti ti-search ti-search"></i>
                <input
                    type="text"
                    id="pdv-busca"
                    class="pdv-search"
                    placeholder="Digite o nome, código ou código de barras..."
                    autocomplete="off"
                    autofocus>
                <span class="pdv-search-shortcut">F2</span>

                <!-- Dropdown de resultados -->
                <div class="pdv-results" id="pdv-results"></div>
            </div>

            <!-- Produtos recentes / favoritos -->
            <div class="pdv-section-label">Mais vendidos</div>

            <div class="pdv-grid" id="pdv-grid">
                <?php foreach ($produtos as $p): ?>
                    <div
                        class="pdv-product-card <?= $p['estoque_atual'] <= 0 ? 'out-of-stock' : '' ?>"
                        data-produto="<?= htmlspecialchars(json_encode([
                                            'id'            => (int)$p['id'],
                                            'nome'          => $p['nome'],
                                            'preco'         => (float)$p['preco_venda'],
                                            'preco_custo'   => (float)($p['preco_custo'] ?? 0),
                                            'estoque'       => (float)$p['estoque_atual'],
                                            'codigo'        => $p['codigo'] ?? '',
                                            'unidade_sigla' => $p['unidade_sigla'] ?? 'UN',
                                        ]), ENT_QUOTES, 'UTF-8') ?>"
                        title="<?= e($p['nome']) ?> — R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?>">
                        <div class="pdv-product-icon">📦</div>
                        <div class="pdv-product-name"><?= e($p['nome']) ?></div>
                        <div class="pdv-product-price">R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?></div>
                        <div class="pdv-product-stock"><?= number_format($p['estoque_atual'], 0, ',', '.') ?> <?= e($p['unidade_sigla']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

        </main>

        <!-- ── Sidebar direita: carrinho ── -->
        <aside class="pdv-right">

            <!-- Header carrinho -->
            <div class="cart-header">
                <div style="display:flex;align-items:center;gap:8px">
                    <span class="cart-title">Carrinho</span>
                    <span class="cart-count" id="cart-count">0</span>
                </div>
                <button class="cart-clear" onclick="limparCarrinho()">
                    <i class="ti ti-trash" style="font-size:13px"></i> Limpar
                </button>
            </div>

            <!-- Cliente -->
            <div class="cart-cliente">

                <i class="ti ti-user"
                    style="font-size:15px;color:var(--text-3)"></i>

                <div style="width: 100%; position: relative;">

                    <input
                        type="text"
                        id="busca-cliente"
                        class="cart-cliente-select"
                        placeholder="Buscar cliente..."
                        autocomplete="off">

                    <div
                        class="pdv-results"
                        id="cliente-results"
                        style="top:100%;left:0;right:0;"></div>

                </div>
            </div>

            <!-- Itens do carrinho -->
            <div class="cart-items" id="cart-items">
                <div class="cart-empty" id="cart-empty">
                    <i class="ti ti-shopping-cart"></i>
                    <span>Carrinho vazio</span>
                    <span style="font-size:11px">Busque ou clique em um produto</span>
                </div>
            </div>

            <!-- Totais -->
            <div class="cart-totals">
                <div class="cart-total-row">
                    <span>Subtotal</span>
                    <span id="cart-subtotal">R$ 0,00</span>
                </div>
                <div class="cart-total-row">
                    <span>Desconto</span>
                    <div class="discount-wrap">
                        <input
                            type="number"
                            id="desconto-valor"
                            class="discount-input"
                            value="0"
                            min="0"
                            step="0.01"
                            onchange="calcularTotais()">
                        <select class="discount-type" id="desconto-tipo" onchange="calcularTotais()">
                            <option value="valor">R$</option>
                            <option value="percentual">%</option>
                        </select>
                    </div>
                </div>
                <div class="cart-total-row big">
                    <span>Total</span>
                    <span id="cart-total">R$ 0,00</span>
                </div>
            </div>

            <!-- Botão finalizar -->
            <div class="cart-footer">
                <button class="btn-finalizar" id="btn-finalizar" onclick="abrirPagamento()" disabled>
                    <i class="ti ti-credit-card"></i>
                    Ir para pagamento
                </button>
                <div class="pdv-hint">F4 para pagar &nbsp;·&nbsp; ESC para cancelar item</div>
            </div>

        </aside>
    </div>


    <!-- ── Modal de Pagamento (misto) ── -->
    <div class="modal-overlay" id="modal-pagamento">
        <div class="modal" style="max-width:520px">
            <div class="modal-head">
                <h3>Finalizar Venda</h3>
                <button class="modal-close" onclick="fecharModal('modal-pagamento')">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="modal-body">

                <!-- Totais do topo -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:20px">
                    <div class="pgto-total-box" style="margin-bottom:0">
                        <div>
                            <div class="pgto-total-label">Total a pagar</div>
                            <div class="pgto-total-value" id="pgto-total-display">R$ 0,00</div>
                        </div>
                        <i class="ti ti-receipt" style="font-size:28px;opacity:0.3"></i>
                    </div>
                    <div class="pgto-total-box" style="margin-bottom:0;background:var(--bg);border:1px solid var(--border)">
                        <div>
                            <div class="pgto-total-label" style="color:var(--text-2)">Restante</div>
                            <div class="pgto-total-value" id="pgto-restante-display" style="color:var(--text);font-size:22px">R$ 0,00</div>
                        </div>
                        <i class="ti ti-coins" style="font-size:24px;opacity:0.2;color:var(--text)"></i>
                    </div>
                </div>

                <!-- Pagamentos já adicionados -->
                <div id="pgto-lista" style="display:none;margin-bottom:16px">
                    <div class="pgto-label" style="margin-bottom:8px">Pagamentos adicionados</div>
                    <div id="pgto-lista-itens"></div>
                </div>

                <!-- Adicionar pagamento -->
                <div class="pgto-label">Forma de pagamento</div>
                <div class="pgto-formas" style="grid-template-columns:repeat(4,1fr);margin-bottom:12px">
                    <button class="pgto-forma-btn selected" data-forma="dinheiro" onclick="selecionarForma('dinheiro')">
                        <i class="ti ti-cash"></i> Dinheiro
                    </button>
                    <button class="pgto-forma-btn" data-forma="pix" onclick="selecionarForma('pix')">
                        <i class="ti ti-qrcode"></i> PIX
                    </button>
                    <button class="pgto-forma-btn" data-forma="cartao_debito" onclick="selecionarForma('cartao_debito')">
                        <i class="ti ti-credit-card"></i> Débito
                    </button>
                    <button class="pgto-forma-btn" data-forma="cartao_credito" onclick="selecionarForma('cartao_credito')">
                        <i class="ti ti-credit-card"></i> Crédito
                    </button>
                </div>

                <!-- Parcelas — só aparece para crédito -->
                <!-- ✅ COLOCAR no lugar -->
                <div id="pgto-parcelas-wrap" style="display:none; margin-bottom:16px;">
                    <div class="pgto-label">Parcelas</div>
                    <div class="parcelas-grid" id="parcelas-grid">
                        <button type="button" class="parcela-btn selected" data-parcela="1" onclick="selecionarParcela(1)">
                            <span class="parcela-num">À vista</span>
                            <span class="parcela-sub">1×</span>
                        </button>
                        <button type="button" class="parcela-btn" data-parcela="2" onclick="selecionarParcela(2)">
                            <span class="parcela-num">2×</span>
                            <span class="parcela-sub" id="psub-2"></span>
                        </button>
                        <button type="button" class="parcela-btn" data-parcela="3" onclick="selecionarParcela(3)">
                            <span class="parcela-num">3×</span>
                            <span class="parcela-sub" id="psub-3"></span>
                        </button>
                        <button type="button" class="parcela-btn" data-parcela="4" onclick="selecionarParcela(4)">
                            <span class="parcela-num">4×</span>
                            <span class="parcela-sub" id="psub-4"></span>
                        </button>
                        <button type="button" class="parcela-btn" data-parcela="5" onclick="selecionarParcela(5)">
                            <span class="parcela-num">5×</span>
                            <span class="parcela-sub" id="psub-5"></span>
                        </button>
                        <button type="button" class="parcela-btn" data-parcela="6" onclick="selecionarParcela(6)">
                            <span class="parcela-num">6×</span>
                            <span class="parcela-sub" id="psub-6"></span>
                        </button>
                        <button type="button" class="parcela-btn" data-parcela="7" onclick="selecionarParcela(7)">
                            <span class="parcela-num">7×</span>
                            <span class="parcela-sub" id="psub-7"></span>
                        </button>
                        <button type="button" class="parcela-btn" data-parcela="8" onclick="selecionarParcela(8)">
                            <span class="parcela-num">8×</span>
                            <span class="parcela-sub" id="psub-8"></span>
                        </button>
                        <button type="button" class="parcela-btn" data-parcela="9" onclick="selecionarParcela(9)">
                            <span class="parcela-num">9×</span>
                            <span class="parcela-sub" id="psub-9"></span>
                        </button>
                        <button type="button" class="parcela-btn" data-parcela="10" onclick="selecionarParcela(10)">
                            <span class="parcela-num">10×</span>
                            <span class="parcela-sub" id="psub-10"></span>
                        </button>
                        <button type="button" class="parcela-btn" data-parcela="11" onclick="selecionarParcela(11)">
                            <span class="parcela-num">11×</span>
                            <span class="parcela-sub" id="psub-11"></span>
                        </button>
                        <button type="button" class="parcela-btn" data-parcela="12" onclick="selecionarParcela(12)">
                            <span class="parcela-num">12×</span>
                            <span class="parcela-sub" id="psub-12"></span>
                        </button>
                    </div>
                    <!-- input oculto que substitui o select para o restante do JS não quebrar -->
                    <input type="hidden" id="pgto-parcelas" value="1">
                </div>

                <!-- Valor do pagamento atual -->
                <div style="display:flex;gap:8px;align-items:flex-end;margin-bottom:10px">
                    <div style="flex:1">
                        <div class="pgto-label">Valor</div>
                        <input
                            type="number"
                            id="pgto-valor-parcial"
                            class="pgto-input"
                            placeholder="0,00"
                            step="0.01"
                            min="0.01"
                            oninput="atualizarTrocoParcial(); atualizarSubtextoParcelas();"
                            </div>
                        <button
                            id="btn-add-pgto"
                            onclick="adicionarPagamento()"
                            style="padding:11px 18px;background:var(--primary);color:#fff;border:none;border-radius:6px;
                               font-family:var(--font);font-size:13px;font-weight:600;cursor:pointer;
                               display:flex;align-items:center;gap:6px;white-space:nowrap;height:46px">
                            <i class="ti ti-plus"></i> Adicionar
                        </button>
                    </div>

                    <!-- Troco (só dinheiro) -->
                    <div class="pgto-dinheiro-box show" id="pgto-dinheiro-box">
                        <div class="pgto-troco-box" id="pgto-troco-box" style="display:none">
                            <span class="pgto-troco-label"><i class="ti ti-coins"></i> Troco estimado</span>
                            <span class="pgto-troco-value" id="pgto-troco">R$ 0,00</span>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button class="btn-modal btn-modal-cancel" onclick="fecharModal('modal-pagamento')">
                        Cancelar
                    </button>
                    <button class="btn-modal btn-modal-confirm" id="btn-confirmar-pgto" onclick="confirmarVenda()" disabled>
                        <i class="ti ti-check"></i> Confirmar venda
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Formulário de submissão (POST) ── -->
    <form id="form-venda" action="/pdv/finalizar" method="POST" style="display:none">
        <?= csrf_field() ?>
        <input type="hidden" name="caixa_id" value="<?= $caixa['id'] ?? 0 ?>">
        <input type="hidden" name="cliente_id" id="f-cliente-id" value="1">
        <input type="hidden" name="itens" id="f-itens" value="">
        <input type="hidden" name="desconto_tipo" id="f-desc-tipo" value="">
        <input type="hidden" name="desconto_valor" id="f-desc-valor" value="0">
        <input type="hidden" name="subtotal" id="f-subtotal" value="0">
        <input type="hidden" name="total" id="f-total" value="0">
        <!-- pagamentos como JSON array: [{forma, valor, troco}, ...] -->
        <input type="hidden" name="pagamentos" id="f-pagamentos" value="[]">
    </form>

    <!-- Modal: emitir cupom? -->
    <div class="modal-overlay" id="modal-cupom">
        <div class="modal" style="max-width:380px;text-align:center;">
            <div class="modal-head" style="justify-content:center;border-bottom:none;padding-bottom:0;">
                <h3>Venda confirmada!</h3>
            </div>
            <div class="modal-body" style="padding-top:8px;">
                <p style="color:var(--text-2);font-size:14px;margin-bottom:20px;">
                    Deseja emitir o cupom para o cliente?
                </p>
                <div style="display:flex;gap:10px;justify-content:center;">
                    <button class="btn-modal btn-modal-cancel" style="flex:1;"
                        onclick="submeterVenda(false)">
                        <i class="ti ti-x"></i> Não
                    </button>
                    <button class="btn-modal btn-modal-confirm" style="flex:1;"
                        onclick="submeterVenda(true)">
                        <i class="ti ti-printer"></i> Sim, imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Campo hidden para sinalizar impressão -->
    <input type="hidden" name="imprimir_cupom" id="f-imprimir-cupom" value="0" form="form-venda">

    <?php if ($cupomUrl = \App\Core\Session::getFlash('cupom_url')): ?>
        <script>
            window.open('<?= e($cupomUrl) ?>', '_blank', 'width=420,height=700,scrollbars=yes');
        </script>
    <?php endif; ?>

    <script>
        /* ─── Estado do carrinho ─── */
        let carrinho = []; // [{id, nome, preco, qty, estoque}]
        let formaPgto = 'dinheiro';

        /* ─── Relógio ─── */
        function atualizarRelogio() {
            const d = new Date();
            const h = String(d.getHours()).padStart(2, '0');
            const m = String(d.getMinutes()).padStart(2, '0');
            document.getElementById('pdv-clock').textContent = h + ':' + m;
        }
        atualizarRelogio();
        setInterval(atualizarRelogio, 10000);

        /* ─── Busca de produto ─── */
        const inputBusca = document.getElementById('pdv-busca');
        const divResults = document.getElementById('pdv-results');
        let searchTimer;

        inputBusca.addEventListener('input', function() {
            clearTimeout(searchTimer);
            const q = this.value.trim();
            if (q.length < 2) {
                divResults.classList.remove('show');
                return;
            }
            searchTimer = setTimeout(() => buscarProduto(q), 150);
        });

        inputBusca.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                divResults.classList.remove('show');
                this.value = '';
            }
            if (e.key === 'Enter') {
                const focused = divResults.querySelector('.focused');
                if (focused) focused.click();
            }
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                const items = divResults.querySelectorAll('.pdv-result-item');
                if (!items.length) return;
                e.preventDefault();
                let idx = Array.from(items).findIndex(i => i.classList.contains('focused'));
                items[idx]?.classList.remove('focused');
                idx = e.key === 'ArrowDown' ? Math.min(idx + 1, items.length - 1) : Math.max(idx - 1, 0);
                items[idx]?.classList.add('focused');
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.pdv-search-wrap')) divResults.classList.remove('show');
        });

        document.getElementById('pdv-grid').addEventListener('click', function(e) {
            const card = e.target.closest('.pdv-product-card');
            if (!card || card.classList.contains('out-of-stock')) return;

            const raw = card.dataset.produto;
            if (!raw) return;

            try {
                const produto = JSON.parse(raw);
                addItem(produto);
            } catch (err) {
                console.error('Erro ao parsear produto do grid:', err, raw);
            }
        });

        async function buscarProduto(q) {
            try {
                // Voltamos para o formato padrão passando na URL que o $_GET['q'] vai ler perfeitamente
                const res = await fetch('/pdv/buscar?q=' + encodeURIComponent(q), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                renderResults(data.produtos || []);
            } catch (e) {
                console.error('Erro na busca:', e);
            }
        }

        function renderResults(produtos) {
            if (!produtos.length) {
                divResults.innerHTML = '<div class="pdv-result-empty"><i class="ti ti-search-off"></i><br>Nenhum produto encontrado</div>';
            } else {
                divResults.innerHTML = produtos.map(p => `
            <div class="pdv-result-item" onclick='addItem(${JSON.stringify({
                id:            parseInt(p.id),
                nome:          p.nome,
                preco:         parseFloat(p.preco_venda),
                preco_custo:   parseFloat(p.preco_custo || 0),
                estoque:       parseFloat(p.estoque_atual),
                codigo:        p.codigo || '',
                unidade_sigla: p.unidade_sigla || "UN"
            })})'>
                <div class="pdv-result-img">📦</div>
                <div style="flex:1;min-width:0">
                    <div class="pdv-result-name">${escHtml(p.nome)}</div>
                    <div class="pdv-result-code">${escHtml(p.codigo || '')} ${p.codigo_barras ? '· ' + escHtml(p.codigo_barras) : ''}</div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div class="pdv-result-price">R$ ${formatMoney(p.preco_venda)}</div>
                    <div class="pdv-result-stock">${parseInt(p.estoque_atual)} ${escHtml(p.unidade_sigla || 'UN')}</div>
                </div>
            </div>
        `).join('');
            }
            divResults.classList.add('show');
        }


        /* ─── Adicionar item ─── */
        function addItem(produto) {
            if (typeof produto !== 'object' || produto === null) return;

            divResults.classList.remove('show');
            inputBusca.value = '';
            inputBusca.focus();

            const id = parseInt(produto.id);
            // Garante que o estoque é tratado como número puro (remove problemas de máscara ou strings)
            const estoque = parseFloat(produto.estoque);

            if (isNaN(estoque) || estoque <= 0) {
                alert('Produto sem estoque disponível no sistema!');
                return;
            }

            // Procura o item na variável 'carrinho' (nome correto no seu script)
            const idx = carrinho.findIndex(i => i.id === id);

            if (idx >= 0) {
                if (carrinho[idx].qty + 1 > estoque) {
                    alert('Estoque insuficiente! Estoque máximo: ' + estoque + ' UN');
                    return;
                }
                carrinho[idx].qty += 1;
            } else {
                carrinho.push({
                    id,
                    nome: produto.nome,
                    preco: parseFloat(produto.preco),
                    preco_custo: parseFloat(produto.preco_custo || 0),
                    qty: 1,
                    estoque: estoque,
                    codigo: produto.codigo || '',
                    unidade_sigla: produto.unidade_sigla || 'UN',
                });
            }

            // Chama a função correta do seu sistema para redesenhar a tabela
            renderCarrinho();
        }

        /* ─── Render do carrinho ─── */
        function renderCarrinho() {
            const container = document.getElementById('cart-items');
            const emptyMsg = document.getElementById('cart-empty');
            const countEl = document.getElementById('cart-count');

            // 1. Atualiza o contador de itens
            const totalItens = carrinho.reduce((acc, item) => acc + parseInt(item.qty), 0);
            if (countEl) countEl.textContent = totalItens;

            // 2. Limpa o container e decide se mostra a mensagem de vazio
            container.innerHTML = '';

            if (carrinho.length === 0) {
                if (emptyMsg) emptyMsg.style.display = 'block';
                calcularTotais();
                return;
            }

            if (emptyMsg) emptyMsg.style.display = 'none';

            // 3. Desenha cada item no carrinho
            carrinho.forEach((item, index) => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'cart-item';

                itemDiv.innerHTML = `
            <div class="cart-item-info">
                <div class="cart-item-name">${escHtml(item.nome)}</div>
                <div class="cart-item-price">R$ ${formatMoney(item.preco)} un</div>
            </div>
            <div class="cart-item-controls">
                <button type="button" class="qty-btn" onclick="changeQty(${index}, -1)">−</button>
                <input class="qty-display" type="number" value="${item.qty}" min="1" onchange="setQty(${index}, this.value)">
                <button type="button" class="qty-btn" onclick="changeQty(${index}, 1)">+</button>
            </div>
            <div class="cart-item-subtotal">R$ ${formatMoney(item.preco * item.qty)}</div>
        `;
                container.appendChild(itemDiv);
            });

            // 4. Força o recalculo dos totais
            calcularTotais();

            const btn = document.getElementById('btn-finalizar');
            if (carrinho.length > 0) {
                btn.disabled = false; // Garante que o botão seja habilitado
                console.log('Botão de pagamento liberado!');
            } else {
                btn.disabled = true;
            }
        }

        function changeQty(idx, delta) {
            const item = carrinho[idx];
            const nova = item.qty + delta;
            if (nova < 1) {
                removeItem(idx);
                return;
            }
            if (nova > item.estoque) {
                alert('Estoque insuficiente!');
                return;
            }
            item.qty = nova;
            renderCarrinho();
        }

        function setQty(idx, val) {
            const qty = parseInt(val);
            if (!qty || qty < 1) {
                removeItem(idx);
                return;
            }
            if (qty > carrinho[idx].estoque) {
                alert('Estoque insuficiente!');
                return;
            }
            carrinho[idx].qty = qty;
            renderCarrinho();
        }

        function removeItem(idx) {
            carrinho.splice(idx, 1);
            renderCarrinho();
        }

        function limparCarrinho() {
            if (!carrinho.length) return;
            if (!confirm('Limpar o carrinho?')) return;
            carrinho = [];
            renderCarrinho();
        }

        /* ─── Totais ─── */
        function calcularTotais() {
            const subtotal = carrinho.reduce((s, i) => s + (i.preco * i.qty), 0);
            const dv = parseFloat(document.getElementById('desconto-valor').value) || 0;
            const dt = document.getElementById('desconto-tipo').value;
            let desconto = dt === 'percentual' ? (subtotal * dv / 100) : dv;
            desconto = Math.min(desconto, subtotal);
            const total = Math.max(subtotal - desconto, 0);

            document.getElementById('cart-subtotal').textContent = 'R$ ' + formatMoney(subtotal);
            document.getElementById('cart-total').textContent = 'R$ ' + formatMoney(total);

            // Atualiza hidden inputs do formulário estruturado do PHP
            document.getElementById('f-subtotal').value = subtotal.toFixed(2);
            document.getElementById('f-total').value = total.toFixed(2);
            document.getElementById('f-desc-tipo').value = dt;
            document.getElementById('f-desc-valor').value = desconto.toFixed(2);
        }

        /* ─── Modal Pagamento (misto) ─── */
        let pagamentosMisto = []; // [{forma, valor, troco}]

        function abrirPagamento() {
            if (!carrinho.length) return;
            pagamentosMisto = [];
            const total = parseFloat(document.getElementById('f-total').value);
            document.getElementById('pgto-total-display').textContent = 'R$ ' + formatMoney(total);
            document.getElementById('pgto-valor-parcial').value = '';
            document.getElementById('pgto-troco-box').style.display = 'none';
            renderPagamentosMisto();
            atualizarRestante();
            document.getElementById('modal-pagamento').classList.add('show');
            selecionarForma('dinheiro');
            setTimeout(() => document.getElementById('pgto-valor-parcial').focus(), 100);
        }

        function fecharModal(id) {
            document.getElementById(id).classList.remove('show');
        }

        function selecionarForma(forma) {
            formaPgto = forma;
            document.querySelectorAll('.pgto-forma-btn').forEach(b => {
                b.classList.toggle('selected', b.dataset.forma === forma);
            });

            const dinheiroBox = document.getElementById('pgto-dinheiro-box');
            if (forma === 'dinheiro') {
                dinheiroBox.classList.add('show');
            } else {
                dinheiroBox.classList.remove('show');
                document.getElementById('pgto-troco-box').style.display = 'none';
            }

            const parcelasWrap = document.getElementById('pgto-parcelas-wrap');
            parcelasWrap.style.display = forma === 'cartao_credito' ? 'block' : 'none';
            if (forma !== 'cartao_credito') {
                document.getElementById('pgto-parcelas').value = '1';
            } else {
                selecionarParcela(1);
                atualizarSubtextoParcelas();
            }

            // Preenche o campo com o restante automaticamente
            const restante = calcularRestante();
            if (restante > 0) {
                document.getElementById('pgto-valor-parcial').value = restante.toFixed(2);
            }
            atualizarTrocoParcial();
        }

        function calcularRestante() {
            const total = parseFloat(document.getElementById('f-total').value) || 0;
            const pago = pagamentosMisto.reduce((s, p) => s + p.valor, 0);
            return Math.max(total - pago, 0);
        }

        function atualizarRestante() {
            const restante = calcularRestante();
            document.getElementById('pgto-restante-display').textContent = 'R$ ' + formatMoney(restante);
            const btnConf = document.getElementById('btn-confirmar-pgto');
            btnConf.disabled = restante > 0.001 || pagamentosMisto.length === 0;
        }

        function atualizarTrocoParcial() {
            if (formaPgto !== 'dinheiro') return;
            const restante = calcularRestante();
            const valor = parseFloat(document.getElementById('pgto-valor-parcial').value) || 0;
            const trocoBox = document.getElementById('pgto-troco-box');
            const trocoVal = document.getElementById('pgto-troco');
            if (valor > restante + 0.001) {
                const troco = valor - restante;
                trocoVal.textContent = 'R$ ' + formatMoney(troco);
                trocoBox.style.display = 'flex';
            } else {
                trocoBox.style.display = 'none';
            }
        }

        function adicionarPagamento() {
            const valorInput = parseFloat(document.getElementById('pgto-valor-parcial').value) || 0;
            if (valorInput <= 0) {
                alert('Informe um valor válido.');
                document.getElementById('pgto-valor-parcial').focus();
                return;
            }
            const restante = calcularRestante();
            if (restante <= 0.001) {
                alert('O total já está coberto pelos pagamentos adicionados.');
                return;
            }

            let troco = 0;
            let valorEfetivo = valorInput;

            if (formaPgto === 'dinheiro' && valorInput > restante + 0.001) {
                troco = parseFloat((valorInput - restante).toFixed(2));
                valorEfetivo = valorInput; // registra o que foi recebido; troco será devolvido
            } else {
                // Para cartão/pix, não pode receber mais do que o restante
                valorEfetivo = Math.min(valorInput, restante);
            }

            const parcelas = formaPgto === 'cartao_credito' ?
                parseInt(document.getElementById('pgto-parcelas').value) || 1 :
                1;
            pagamentosMisto.push({
                forma: formaPgto,
                valor: valorEfetivo,
                troco,
                parcelas
            });

            document.getElementById('pgto-valor-parcial').value = '';
            document.getElementById('pgto-troco-box').style.display = 'none';
            renderPagamentosMisto();
            atualizarRestante();

            // Preenche automaticamente o restante para agilizar
            const novoRestante = calcularRestante();
            if (novoRestante > 0.001) {
                document.getElementById('pgto-valor-parcial').value = novoRestante.toFixed(2);
                document.getElementById('pgto-valor-parcial').focus();
                document.getElementById('pgto-valor-parcial').select();
            }
        }

        function removerPagamento(idx) {
            pagamentosMisto.splice(idx, 1);
            renderPagamentosMisto();
            atualizarRestante();
            const restante = calcularRestante();
            if (restante > 0.001) {
                document.getElementById('pgto-valor-parcial').value = restante.toFixed(2);
            }
        }

        function renderPagamentosMisto() {
            const lista = document.getElementById('pgto-lista');
            const container = document.getElementById('pgto-lista-itens');

            if (!pagamentosMisto.length) {
                lista.style.display = 'none';
                return;
            }

            lista.style.display = 'block';
            const nomes = {
                dinheiro: '💵 Dinheiro',
                pix: '📱 PIX',
                cartao_debito: '💳 Débito',
                cartao_credito: '💳 Crédito'
            };
            container.innerHTML = pagamentosMisto.map((p, i) => `
                <div style="display:flex;align-items:center;justify-content:space-between;
                            padding:8px 12px;background:var(--bg);border-radius:6px;margin-bottom:6px;gap:8px">
                    <span style="font-size:13px;font-weight:500">${nomes[p.forma] ?? p.forma}</span>
                    <span style="font-size:13px;font-weight:600;margin-left:auto">
                        R$ ${formatMoney(p.valor)}
                        ${p.forma === 'cartao_credito' && p.parcelas > 1
                            ? `<span style="font-size:11px;color:var(--text-2);margin-left:4px">${p.parcelas}x R$ ${formatMoney(p.valor/p.parcelas)}</span>`
                            : ''}
                    </span>
                    ${p.troco > 0 ? `<span style="font-size:11px;color:var(--green)">(troco R$ ${formatMoney(p.troco)})</span>` : ''}
                    <button onclick="removerPagamento(${i})"
                        style="background:none;border:none;cursor:pointer;color:var(--text-3);
                               font-size:14px;padding:2px 4px;transition:color 0.12s"
                        onmouseover="this.style.color='var(--red)'"
                        onmouseout="this.style.color='var(--text-3)'">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            `).join('');
        }

        function confirmarVenda() {
            if (!carrinho.length) return;
            if (calcularRestante() > 0.001) {
                alert('Ainda há saldo restante a pagar.');
                return;
            }

            const campoId = document.getElementById('f-cliente-id');
            const clienteIdFinal = campoId.value; // Pega o valor real (que deve ser o id do cliente selecionado)

            console.log('INPUT HIDDEN:', document.getElementById('f-cliente-id'));
            console.log('VALOR:', document.getElementById('f-cliente-id').value);

            console.log("ID do cliente enviado para o form:", clienteIdFinal);

            // Agora, antes de enviar o form, certifique-se de que o input oculto esteja com esse ID
            document.getElementById('f-cliente-id').value = clienteIdFinal;
            document.getElementById('f-pagamentos').value = JSON.stringify(pagamentosMisto);

            const subtotal = carrinho.reduce((s, i) => s + (i.preco * i.qty), 0);
            document.getElementById('f-subtotal').value = subtotal.toFixed(2);

            document.getElementById('f-itens').value = JSON.stringify(carrinho.map(i => ({
                id: i.id,
                nome: i.nome,
                qty: i.qty,
                preco: i.preco,
                preco_custo: i.preco_custo || 0,
                codigo: i.codigo || 'SEM_COD',
                unidade_sigla: i.unidade_sigla || 'UN',
            })));

            fecharModal('modal-pagamento');
            document.getElementById('modal-cupom').classList.add('show');
        }

        /* ─── Atalhos de teclado ─── */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F2') {
                e.preventDefault();
                inputBusca.focus();
                inputBusca.select();
            }
            if (e.key === 'F4') {
                e.preventDefault();
                abrirPagamento();
            }
            if (e.key === 'Escape') {
                const modal = document.getElementById('modal-pagamento');
                if (modal.classList.contains('show')) {
                    fecharModal('modal-pagamento');
                }
            }
        });

        function submeterVenda(imprimirCupom) {
            document.getElementById('f-imprimir-cupom').value = imprimirCupom ? '1' : '0';
            document.getElementById('modal-cupom').classList.remove('show');
            document.getElementById('btn-confirmar-pgto').disabled = true;
            document.getElementById('form-venda').submit();
        }

        /* ─── Helpers ─── */
        function formatMoney(v) {
            return parseFloat(v).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function escHtml(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function fecharCaixa() {
            if (carrinho.length && !confirm('Há itens no carrinho. Fechar o caixa mesmo assim?')) return;
            window.location.href = '/caixa/gestao';
        }

        function abrirSangria() {
            window.location.href = '/caixa/sangria';
        }

        // ── Parcelas customizadas ──
        function selecionarParcela(n) {
            // marca o botão selecionado
            document.querySelectorAll('.parcela-btn').forEach(b => {
                b.classList.toggle('selected', parseInt(b.dataset.parcela) === n);
            });
            // atualiza o hidden que o restante do código já lê
            document.getElementById('pgto-parcelas').value = n;
            atualizarTrocoParcial();
        }

        function atualizarSubtextoParcelas() {
            const valor = parseFloat(document.getElementById('pgto-valor-parcial').value) || 0;
            for (let i = 2; i <= 12; i++) {
                const el = document.getElementById('psub-' + i);
                if (el) {
                    el.textContent = valor > 0 ? 'R$ ' + formatMoney(valor / i) : '';
                }
            }
        }

        // --- Busca de Clientes ---
        const inputBuscaCliente = document.getElementById('busca-cliente');
        const inputClienteId = document.getElementById('f-cliente-id');
        const clienteResults = document.getElementById('cliente-results');

        const CLIENTE_PADRAO_ID = '1';
        const CLIENTE_PADRAO_NOME = 'Consumidor Final';

        // Inicialização
        window.addEventListener('DOMContentLoaded', () => {

            inputBuscaCliente.value = CLIENTE_PADRAO_NOME;
            inputClienteId.value = CLIENTE_PADRAO_ID;
        });

        // Ao focar no campo
        inputBuscaCliente.addEventListener('focus', function() {

            // Se estiver no cliente padrão, limpa automaticamente
            if (this.value === CLIENTE_PADRAO_NOME) {

                this.value = '';
            }
        });

        // Busca
        inputBuscaCliente.addEventListener('input', function() {

            const termo = this.value.trim();

            // Se apagar tudo
            if (termo === '') {

                clienteResults.classList.remove('show');
                return;
            }

            // Busca mínima
            if (termo.length < 2) {

                clienteResults.classList.remove('show');
                return;
            }

            fetch(`/pdv/buscar-rapido?termo=${encodeURIComponent(termo)}`)
                .then(res => res.json())
                .then(data => {

                    clienteResults.innerHTML = '';

                    if (!data.length) {

                        clienteResults.innerHTML =
                            '<div class="pdv-result-empty">Cliente não encontrado</div>';

                    } else {

                        data.forEach(c => {

                            const div = document.createElement('div');

                            div.className = 'pdv-result-item';

                            div.innerHTML = `
                            <div>
                                <strong>${c.nome}</strong>
                            </div>
                        `;

                            // IMPORTANTE: usar mousedown
                            div.onmousedown = function(e) {

                                e.preventDefault();

                                inputBuscaCliente.value = c.nome;
                                inputClienteId.value = c.id;

                                clienteResults.classList.remove('show');
                            };

                            clienteResults.appendChild(div);
                        });
                    }

                    clienteResults.classList.add('show');
                })
                .catch(err => {

                    console.error(err);
                });
        });

        // Ao sair do campo
        inputBuscaCliente.addEventListener('blur', function() {

            setTimeout(() => {

                // Se não selecionou nada
                if (this.value.trim() === '') {

                    this.value = CLIENTE_PADRAO_NOME;
                    inputClienteId.value = CLIENTE_PADRAO_ID;
                }

                clienteResults.classList.remove('show');

            }, 150);
        });

        // Fecha lista clicando fora
        document.addEventListener('click', function(e) {

            if (
                !clienteResults.contains(e.target) &&
                e.target !== inputBuscaCliente
            ) {

                clienteResults.classList.remove('show');
            }
        });
    </script>

</body>

</html>