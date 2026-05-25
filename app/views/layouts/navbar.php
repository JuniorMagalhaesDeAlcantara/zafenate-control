<header class="zf-topbar">

    <div>
        <div class="zf-page-title"><?= e($pageTitle ?? '') ?></div>
        <?php if (!empty($breadcrumb)): ?>
            <div class="zf-breadcrumb">
                <?php foreach ($breadcrumb as $i => $item): ?>
                    <?php if ($i < count($breadcrumb) - 1): ?>
                        <a href="<?= e($item['url']) ?>"><?= e($item['label']) ?></a>
                        <span> / </span>
                    <?php else: ?>
                        <span><?= e($item['label']) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="zf-topbar-right">

        <?php if (!empty($caixaStatus) && $caixaStatus === 'fechado'): ?>
            <a href="/caixa/abrir" class="btn btn-outline btn-sm">
                <i class="ti ti-cash-register"></i>
                Abrir caixa
            </a>
        <?php endif; ?>

        <?php
        // 🌟 LÓGICA DE SEGURANÇA: Se o Controller não mandou o $authUser, puxamos da Sessão
        $nomeUsuario = $authUser['nome'] ?? \App\Core\Session::get('usuario_nome') ?? \App\Core\Session::get('usuario') ?? 'Usuário';
        $primeiraLetra = !empty($nomeUsuario) ? mb_strtoupper(mb_substr(trim($nomeUsuario), 0, 1)) : 'U';
        ?>

        <div class="zf-user-pill">
            <div class="zf-avatar">
                <?= $primeiraLetra ?>
            </div>
            <span><?= e($nomeUsuario) ?></span>
            <i class="ti ti-chevron-down" style="font-size:12px; color:var(--text-tertiary)"></i>
        </div>

        <form action="/logout" method="POST" style="margin:0">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline btn-sm" title="Sair">
                <i class="ti ti-logout"></i>
            </button>
        </form>

    </div>

</header>