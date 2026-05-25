<?php

use App\Core\Session;

// Busca o nome do usuário direto da sessão ativa
$nomeUsuarioSessao = Session::get('usuario_nome') ?? 'Usuário';

// Extrai a primeira letra do nome para o Avatar do sistema
$letraAvatar = !empty($nomeUsuarioSessao) ? mb_strtoupper(mb_substr(trim($nomeUsuarioSessao), 0, 1)) : 'U';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Painel') ?> — <?= e($config['empresa_nome'] ?? APP_NAME) ?></title>

    <?php if (!empty($config['empresa_favicon'])): ?>
        <link rel="icon" type="image/x-icon" href="/uploads/logo/<?= e($config['empresa_favicon']) ?>">
    <?php else: ?>
        <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <style>
        /* ─── Reset & Base ─── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --font-main: 'Plus Jakarta Sans', system-ui, sans-serif;

            /* Cor primária — pode ser sobrescrita por JS via configuracoes */
            --color-primary: <?= e($config['empresa_cor'] ?? '#1A1A1A') ?>;
            --color-primary-hover: color-mix(in srgb, var(--color-primary) 85%, white);

            /* Superfícies */
            --bg-page: #F5F4F1;
            --bg-card: #FFFFFF;
            --bg-input: #F8F7F5;
            --bg-sidebar: var(--color-primary);

            /* Bordas */
            --border: rgba(0, 0, 0, 0.08);
            --border-md: rgba(0, 0, 0, 0.14);

            /* Texto */
            --text-primary: #1A1A1A;
            --text-secondary: #6B6B6B;
            --text-tertiary: #A3A3A3;
            --text-on-dark: rgba(255, 255, 255, 0.85);
            --text-on-dark-m: rgba(255, 255, 255, 0.45);

            /* Semânticas */
            --color-success: #16A34A;
            --bg-success: #DCFCE7;
            --color-danger: #DC2626;
            --bg-danger: #FEE2E2;
            --color-warning: #D97706;
            --bg-warning: #FEF3C7;
            --color-info: #2563EB;
            --bg-info: #DBEAFE;

            /* Dimensões */
            --sidebar-w: 208px;
            --topbar-h: 52px;
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
        }

        html,
        body {
            font-family: var(--font-main);
            font-size: 14px;
            color: var(--text-primary);
            background: var(--bg-page);
            height: 100%;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
            font-size: inherit;
        }

        /* ─── Layout principal ─── */
        .zf-layout {
            display: flex;
            min-height: 100vh;
        }

        /* ─── Sidebar ─── */
        .zf-sidebar {
            width: var(--sidebar-w);
            background: var(--bg-sidebar);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            overflow-y: auto;
        }

        .zf-brand {
            padding: 18px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            min-height: var(--topbar-h);
        }

        .zf-brand-logo {
            width: 30px;
            height: 30px;
            border-radius: var(--radius-sm);
            object-fit: contain;
            flex-shrink: 0;
            background: rgba(255, 255, 255, 0.1);
            padding: 2px;
        }

        .zf-brand-mark {
            width: 30px;
            height: 30px;
            border-radius: var(--radius-sm);
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .zf-brand-mark svg {
            width: 16px;
            height: 16px;
        }

        .zf-brand-text {
            min-width: 0;
        }

        .zf-brand-name {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            letter-spacing: -0.01em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .zf-brand-sub {
            font-size: 10px;
            color: var(--text-on-dark-m);
            letter-spacing: 0.02em;
            margin-top: 1px;
        }

        /* Nav */
        .zf-nav {
            padding: 10px 0;
            flex: 1;
        }

        .zf-nav-section {
            padding: 10px 14px 4px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-on-dark-m);
        }

        .zf-nav-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 14px;
            font-size: 12.5px;
            font-weight: 400;
            color: var(--text-on-dark);
            opacity: 0.65;
            transition: opacity 0.12s, background 0.12s;
            cursor: pointer;
        }

        .zf-nav-item:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.05);
        }

        .zf-nav-item.active {
            opacity: 1;
            background: rgba(255, 255, 255, 0.1);
            font-weight: 500;
        }

        .zf-nav-item .ti {
            font-size: 15px;
            flex-shrink: 0;
        }

        .zf-nav-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: transparent;
            flex-shrink: 0;
            margin-left: auto;
        }

        .zf-nav-item.active .zf-nav-dot {
            background: #C9A84C;
        }

        .zf-sidebar-footer {
            padding: 12px 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            font-size: 10px;
            color: var(--text-on-dark-m);
        }

        /* ─── Main ─── */
        .zf-main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* ─── Topbar ─── */
        .zf-topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            height: var(--topbar-h);
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .zf-page-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .zf-breadcrumb {
            font-size: 12px;
            color: var(--text-tertiary);
            margin-top: 1px;
        }

        .zf-breadcrumb a {
            color: var(--text-secondary);
        }

        .zf-breadcrumb a:hover {
            color: var(--text-primary);
        }

        .zf-topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .zf-user-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 10px 4px 4px;
            border: 1px solid var(--border);
            border-radius: 20px;
            font-size: 12px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: border-color 0.15s;
        }

        .zf-user-pill:hover {
            border-color: var(--border-md);
        }

        .zf-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--bg-sidebar);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
            color: #fff;
        }

        /* ─── Content ─── */
        .zf-content {
            padding: 24px;
            flex: 1;
        }

        /* ─── Cards de stat ─── */
        .zf-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .zf-stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 16px;
        }

        .zf-stat-label {
            font-size: 11px;
            font-weight: 500;
            color: var(--text-tertiary);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 8px;
        }

        .zf-stat-value {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1;
        }

        .zf-stat-value.success {
            color: var(--color-success);
        }

        .zf-stat-value.danger {
            color: var(--color-danger);
        }

        .zf-stat-value.warning {
            color: var(--color-warning);
        }

        /* ─── Toolbar ─── */
        .zf-toolbar {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .zf-search-wrap {
            position: relative;
            flex: 1;
            min-width: 200px;
        }

        .zf-search-wrap .ti {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: var(--text-tertiary);
            pointer-events: none;
        }

        .zf-search {
            width: 100%;
            padding: 8px 10px 8px 32px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--bg-input);
            color: var(--text-primary);
            outline: none;
            transition: border-color 0.15s;
        }

        .zf-search:focus {
            border-color: var(--border-md);
            background: var(--bg-card);
        }

        .zf-search::placeholder {
            color: var(--text-tertiary);
        }

        /* ─── Botões ─── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            font-weight: 500;
            font-size: 13px;
            cursor: pointer;
            border: none;
            transition: opacity 0.15s, background 0.15s;
            white-space: nowrap;
        }

        .btn .ti {
            font-size: 14px;
        }

        .btn-primary {
            background: var(--color-primary);
            color: #fff;
        }

        .btn-primary:hover {
            opacity: 0.85;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-md);
            color: var(--text-secondary);
        }

        .btn-outline:hover {
            background: var(--bg-input);
            color: var(--text-primary);
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        /* ─── Tabela ─── */
        .zf-table-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
        }

        .zf-table {
            width: 100%;
            border-collapse: collapse;
        }

        .zf-table thead th {
            padding: 10px 14px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-tertiary);
            background: var(--bg-input);
            border-bottom: 1px solid var(--border);
            text-align: left;
        }

        .zf-table tbody tr {
            border-bottom: 1px solid var(--border);
        }

        .zf-table tbody tr:last-child {
            border-bottom: none;
        }

        .zf-table tbody tr:hover {
            background: #FAFAF9;
        }

        .zf-table td {
            padding: 12px 14px;
            vertical-align: middle;
        }

        .zf-table .td-actions {
            text-align: center;
            white-space: nowrap;
        }

        .td-code {
            font-family: monospace;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .td-name {
            font-weight: 500;
            font-size: 13px;
            color: var(--text-primary);
        }

        .td-sub {
            font-size: 11px;
            color: var(--text-tertiary);
            margin-top: 2px;
        }

        .td-empty {
            text-align: center;
            padding: 48px !important;
            color: var(--text-tertiary);
            font-size: 13px;
        }

        /* ─── Badges ─── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-success {
            background: var(--bg-success);
            color: var(--color-success);
        }

        .badge-danger {
            background: var(--bg-danger);
            color: var(--color-danger);
        }

        .badge-warning {
            background: var(--bg-warning);
            color: var(--color-warning);
        }

        .badge-info {
            background: var(--bg-info);
            color: var(--color-info);
        }

        .badge-neutral {
            background: var(--bg-input);
            color: var(--text-secondary);
        }

        /* ─── Ações de tabela ─── */
        .act-link {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            margin-right: 10px;
            transition: color 0.12s;
        }

        .act-link:hover {
            color: var(--text-primary);
        }

        .act-btn {
            font-size: 12px;
            font-weight: 500;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            color: var(--text-tertiary);
            transition: color 0.12s;
        }

        .act-btn:hover {
            color: var(--color-danger);
        }

        .act-btn.activate:hover {
            color: var(--color-success);
        }

        /* ─── Formulários ─── */
        .zf-form-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 24px;
        }

        .zf-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .zf-form-grid.cols-3 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .zf-form-grid.col-full {
            grid-column: 1 / -1;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .form-label span {
            color: var(--color-danger);
            margin-left: 2px;
        }

        .form-control {
            padding: 9px 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--bg-input);
            color: var(--text-primary);
            outline: none;
            transition: border-color 0.15s, background 0.15s;
        }

        .form-control:focus {
            border-color: var(--border-md);
            background: var(--bg-card);
        }

        .form-control::placeholder {
            color: var(--text-tertiary);
        }

        .form-hint {
            font-size: 11px;
            color: var(--text-tertiary);
        }

        .form-error {
            font-size: 11px;
            color: var(--color-danger);
        }

        /* ─── Alertas flash ─── */
        .zf-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: var(--radius-md);
            font-size: 13px;
            margin-bottom: 16px;
        }

        .zf-alert .ti {
            font-size: 16px;
            margin-top: 1px;
            flex-shrink: 0;
        }

        .zf-alert-success {
            background: var(--bg-success);
            color: var(--color-success);
        }

        .zf-alert-danger {
            background: var(--bg-danger);
            color: var(--color-danger);
        }

        .zf-alert-warning {
            background: var(--bg-warning);
            color: var(--color-warning);
        }

        /* ─── Paginação ─── */
        .zf-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            border-top: 1px solid var(--border);
            font-size: 12px;
            color: var(--text-secondary);
        }

        .zf-pages {
            display: flex;
            gap: 4px;
        }

        .zf-page-btn {
            width: 28px;
            height: 28px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: transparent;
            color: var(--text-secondary);
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.12s;
        }

        .zf-page-btn:hover {
            background: var(--bg-input);
        }

        .zf-page-btn.active {
            background: var(--color-primary);
            color: #fff;
            border-color: var(--color-primary);
        }

        /* ─── Utilitários ─── */
        .d-flex {
            display: flex;
        }

        .align-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .gap-8 {
            gap: 8px;
        }

        .gap-12 {
            gap: 12px;
        }

        .mb-4 {
            margin-bottom: 4px;
        }

        .mb-8 {
            margin-bottom: 8px;
        }

        .mb-16 {
            margin-bottom: 16px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .text-muted {
            color: var(--text-secondary);
        }

        .text-sm {
            font-size: 12px;
        }

        .fw-500 {
            font-weight: 500;
        }
    </style>
</head>

<body>