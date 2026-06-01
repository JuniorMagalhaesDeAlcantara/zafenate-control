<?php
if (empty($config)) {
    $config = function_exists('config') ? config() : ($_SESSION['config'] ?? []);
}

/**
 * Retorna a classe 'active' se o segmento atual da URL casar com $slug.
 */
if (!function_exists('activeRoute')) {
    function activeRoute(string $route): string
    {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        return str_starts_with($uri, trim($route, '/')) ? 'active' : '';
    }
}
?>
<aside class="zf-sidebar">

    <div class="zf-brand">
        <?php if (!empty($config['empresa_logo'])): ?>
            <img src="/uploads/logo/<?= e($config['empresa_logo']) ?>"
                alt="<?= e($config['empresa_nome'] ?? APP_NAME) ?>"
                class="zf-brand-logo">
        <?php else: ?>
            <div class="zf-brand-mark">
                <svg viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6h14M4 11h9M4 16h12" stroke="#fff" stroke-width="1.8" stroke-linecap="round" />
                    <circle cx="17" cy="15.5" r="3.5" fill="none" stroke="#C9A84C" stroke-width="1.4" />
                    <path d="M16 15.5h2M17 14.5v2" stroke="#C9A84C" stroke-width="1.2" stroke-linecap="round" />
                </svg>
            </div>
        <?php endif; ?>
        <div class="zf-brand-text">
            <div class="zf-brand-name"><?= e($config['empresa_nome'] ?? APP_NAME) ?></div>
            <div class="zf-brand-sub">Gestão</div>
        </div>
    </div>

    <nav class="zf-nav">

        <!-- ── Principal ──────────────────────────────────────── -->
        <div class="zf-nav-section">Principal</div>

        <a href="/dashboard" class="zf-nav-item <?= activeRoute('dashboard') ?>">
            <i class="ti ti-layout-dashboard"></i>
            Dashboard
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/caixa" class="zf-nav-item <?= activeRoute('caixa') ?>">
            <i class="ti ti-coin"></i>
            Caixa
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/pdv" class="zf-nav-item <?= activeRoute('pdv') ?>">
            <i class="ti ti-device-desktop"></i>
            PDV
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/vendas" class="zf-nav-item <?= activeRoute('vendas') ?>">
            <i class="ti ti-receipt"></i>
            Vendas
            <span class="zf-nav-dot"></span>
        </a>

        <!-- ── Financeiro ─────────────────────────────────────── -->
        <div class="zf-nav-section">Financeiro</div>

        <a href="/financeiro" class="zf-nav-item <?= activeRoute('financeiro') ?>">
            <i class="ti ti-wallet"></i>
            Visão Geral
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/financeiro/receber" class="zf-nav-item <?= activeRoute('financeiro/receber') ?>">
            <i class="ti ti-cash-banknote"></i>
            Contas a Receber
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/financeiro/pagar" class="zf-nav-item <?= activeRoute('financeiro/pagar') ?>">
            <i class="ti ti-credit-card-pay"></i>
            Contas a Pagar
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/financeiro/fluxo" class="zf-nav-item <?= activeRoute('financeiro/fluxo') ?>">
            <i class="ti ti-chart-line"></i>
            Fluxo de Caixa
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/financeiro/dre" class="zf-nav-item <?= activeRoute('financeiro/dre') ?>">
            <i class="ti ti-report-money"></i>
            DRE
            <span class="zf-nav-dot"></span>
        </a>

        <!-- ── Estoque ────────────────────────────────────────── -->
        <div class="zf-nav-section">Estoque</div>

        <a href="/compras" class="zf-nav-item <?= activeRoute('compras') ?>">
            <i class="ti ti-shopping-cart-plus"></i>
            Compras
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/estoque" class="zf-nav-item <?= activeRoute('estoque') ?>">
            <i class="ti ti-package"></i>
            Movimentações
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/produtos" class="zf-nav-item <?= activeRoute('produtos') ?>">
            <i class="ti ti-box"></i>
            Produtos
            <span class="zf-nav-dot"></span>
        </a>

        <!-- ── Cadastros ──────────────────────────────────────── -->
        <div class="zf-nav-section">Cadastros</div>

        <a href="/clientes" class="zf-nav-item <?= activeRoute('clientes') ?>">
            <i class="ti ti-users"></i>
            Clientes
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/fornecedores" class="zf-nav-item <?= activeRoute('fornecedores') ?>">
            <i class="ti ti-truck"></i>
            Fornecedores
            <span class="zf-nav-dot"></span>
        </a>

        <!-- ── Sistema ────────────────────────────────────────── -->
        <div class="zf-nav-section">Sistema</div>

        <a href="/relatorios/estoque" class="zf-nav-item <?= activeRoute('relatorios') ?>">
            <i class="ti ti-chart-bar"></i>
            Relatórios
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/configuracoes" class="zf-nav-item <?= activeRoute('configuracoes') ?>">
            <i class="ti ti-settings"></i>
            Configurações
            <span class="zf-nav-dot"></span>
        </a>

    </nav>

    <div class="zf-sidebar-footer">
        <?= APP_NAME ?> &nbsp;·&nbsp; v1.0
    </div>

</aside>