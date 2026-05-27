<?php
// 🌟 LÓGICA DE SEGURANÇA: Se o Controller não mandou $config, tentamos puxar do contêiner do sistema, banco ou sessão
if (empty($config)) {
    $config = function_exists('config') ? config() : ($_SESSION['config'] ?? []);
}
?>
<aside class="zf-sidebar">

    <div class="zf-brand">
        <?php if (!empty($config['empresa_logo'])): ?>
            <img
                src="/uploads/logo/<?= e($config['empresa_logo']) ?>"
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
            <div class="zf-brand-name">
                <?= e($config['empresa_nome'] ?? APP_NAME) ?>
            </div>
            <div class="zf-brand-sub">Gestão</div>
        </div>
    </div>

    <nav class="zf-nav">

        <div class="zf-nav-section">Principal</div>

        <a href="/dashboard" class="zf-nav-item <?= active('dashboard') ?>">
            <i class="ti ti-layout-dashboard"></i>
            Dashboard
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/caixa" class="zf-nav-item <?= active('caixa') ?>">
            <i class="ti ti-coin"></i>
            Caixa
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/vendas" class="zf-nav-item <?= active('vendas') ?>">
            <i class="ti ti-receipt"></i>
            Vendas
            <span class="zf-nav-dot"></span>
        </a>

        <!-- ✅ NOVO: Estoque -->
        <a href="/estoque" class="zf-nav-item <?= active('estoque') ?>">
            <i class="ti ti-packages"></i>
            Estoque
            <span class="zf-nav-dot"></span>
        </a>

        <div class="zf-nav-section">Cadastros</div>

        <a href="/produtos" class="zf-nav-item <?= active('produtos') ?>">
            <i class="ti ti-box"></i>
            Produtos
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/clientes" class="zf-nav-item <?= active('clientes') ?>">
            <i class="ti ti-users"></i>
            Clientes
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/fornecedores" class="zf-nav-item <?= active('fornecedores') ?>">
            <i class="ti ti-truck"></i>
            Fornecedores
            <span class="zf-nav-dot"></span>
        </a>

        <div class="zf-nav-section">Sistema</div>

        <a href="/relatorios" class="zf-nav-item <?= active('relatorios') ?>">
            <i class="ti ti-chart-bar"></i>
            Relatórios
            <span class="zf-nav-dot"></span>
        </a>

        <a href="/configuracoes" class="zf-nav-item <?= active('configuracoes') ?>">
            <i class="ti ti-settings"></i>
            Configurações
            <span class="zf-nav-dot"></span>
        </a>

    </nav>

    <div class="zf-sidebar-footer">
        <?= APP_NAME ?> &nbsp;·&nbsp; v1.0
    </div>

</aside>