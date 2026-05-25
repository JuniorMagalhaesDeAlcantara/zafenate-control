<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">

    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>

    <div class="zf-main">

        <?php
        $pageTitle = 'Dashboard';
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard']
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <?php if ($success = \App\Core\Session::getFlash('success')): ?>
                <div class="zf-alert zf-alert-success" data-auto-close>
                    <i class="ti ti-circle-check"></i>
                    <?= e($success) ?>
                </div>
            <?php endif; ?>
            <?php if ($error = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close>
                    <i class="ti ti-alert-circle"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <header class="welcome-header" style="margin-bottom: 32px;">
                <h1 class="welcome-title" style="font-size: 24px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 4px;">
                    Olá, <?= e($usuario_nome ?? 'Usuário') ?>! 👋
                </h1>
                <p class="welcome-subtitle" style="color: #6B6B6B; font-size: 14px;">
                    Bem-vindo de volta ao Zafenate Control. Veja o resumo do seu negócio hoje:
                </p>
            </header>

            <div class="zf-stats" style="margin-bottom: 32px;">
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Produtos Cadastrados</div>
                    <div class="zf-stat-value"><?= $total_produtos ?? 0 ?></div>
                </div>

                <div class="zf-stat-card">
                    <div class="zf-stat-label">Alertas de Estoque</div>
                    <div class="zf-stat-value <?= ($alerta_estoque ?? 0) > 0 ? 'danger' : '' ?>">
                        <?= $alerta_estoque ?? 0 ?>
                    </div>
                </div>

                <div class="zf-stat-card">
                    <div class="zf-stat-label">Fornecedores Parceiros</div>
                    <div class="zf-stat-value success"><?= $total_fornecedores ?? 0 ?></div>
                </div>
            </div>

            <section>
                <h2 class="section-title" style="font-size: 16px; font-weight: 600; margin-bottom: 16px; color: #1A1A1A;">
                    Ações Rápidas
                </h2>
                <div class="shortcuts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <a href="/produtos/criar" class="shortcut-btn" style="background: #fff; border: 1px solid rgba(0,0,0,0.06); padding: 20px; border-radius: var(--radius-md); text-decoration: none; color: #1A1A1A; font-weight: 600; display: flex; flex-direction: column; gap: 12px; transition: all 0.15s;">
                        <i class="ti ti-circle-plus" style="font-size: 20px; color: var(--color-primary, #1A1A1A);"></i>
                        Novo Produto
                    </a>
                    <a href="/produtos" class="shortcut-btn" style="background: #fff; border: 1px solid rgba(0,0,0,0.06); padding: 20px; border-radius: var(--radius-md); text-decoration: none; color: #1A1A1A; font-weight: 600; display: flex; flex-direction: column; gap: 12px; transition: all 0.15s;">
                        <i class="ti ti-list" style="font-size: 20px; color: var(--color-primary, #1A1A1A);"></i>
                        Gerenciar Catálogo
                    </a>
                    <a href="/movimentacoes" class="shortcut-btn" style="background: #fff; border: 1px solid rgba(0,0,0,0.06); padding: 20px; border-radius: var(--radius-md); text-decoration: none; color: #1A1A1A; font-weight: 600; display: flex; flex-direction: column; gap: 12px; transition: all 0.15s;">
                        <i class="ti ti-database-import" style="font-size: 20px; color: var(--color-primary, #1A1A1A);"></i>
                        Lançar Entrada/Saída
                    </a>
                </div>
            </section>

        </div>
    </div>
</div><?php require VIEW_PATH . '/layouts/footer.php'; ?>