<?php

use App\Core\Session;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — <?= e($config['empresa_nome'] ?? APP_NAME) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --color-primary: <?= e($config['empresa_cor'] ?? '#1A1A1A') ?>;
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 14px;
            background: #F5F4F1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #1A1A1A;
            -webkit-font-smoothing: antialiased;
        }

        .login-wrap {
            width: 100%;
            max-width: 380px;
        }

        /* Cabeçalho com logo/nome da empresa */
        .login-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 28px;
            gap: 10px;
        }

        .login-logo-img {
            height: 52px;
            max-width: 180px;
            object-fit: contain;
        }

        .login-mark {
            width: 48px;
            height: 48px;
            background: var(--color-primary);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-mark svg {
            width: 24px;
            height: 24px;
        }

        .login-empresa-nome {
            font-size: 17px;
            font-weight: 600;
            color: #1A1A1A;
            letter-spacing: -0.01em;
        }

        .login-tagline {
            font-size: 12px;
            color: #A3A3A3;
        }

        /* Card */
        .login-card {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: var(--radius-lg);
            padding: 28px 24px 24px;
        }

        .login-card-title {
            font-size: 14px;
            font-weight: 600;
            color: #1A1A1A;
            margin-bottom: 20px;
        }

        /* Alerta de erro */
        .login-alert {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #FEE2E2;
            color: #DC2626;
            border-radius: var(--radius-sm);
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .login-alert .ti {
            font-size: 15px;
            flex-shrink: 0;
        }

        /* Campos */
        .form-group {
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #6B6B6B;
            margin-bottom: 5px;
        }

        .form-input-wrap {
            position: relative;
        }

        .form-input-wrap .ti-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 15px;
            color: #A3A3A3;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px 10px 34px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--radius-sm);
            background: #F8F7F5;
            color: #1A1A1A;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: border-color 0.15s, background 0.15s;
        }

        .form-input:focus {
            border-color: rgba(0, 0, 0, 0.25);
            background: #fff;
        }

        .form-input::placeholder {
            color: #C4C4C4;
        }

        .form-input.has-toggle {
            padding-right: 38px;
        }

        .pwd-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 2px;
            font-size: 15px;
            color: #A3A3A3;
            transition: color 0.12s;
            line-height: 1;
        }

        .pwd-toggle:hover {
            color: #6B6B6B;
        }

        /* Botão */
        .btn-login {
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            background: var(--color-primary);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: opacity 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        .btn-login:hover {
            opacity: 0.85;
        }

        /* Rodapé */
        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: #C4C4C4;
        }
    </style>
</head>

<body>

    <div class="login-wrap">

        <!-- Cabeçalho: logo ou ícone padrão + nome da empresa -->
        <div class="login-header">
            <?php if (!empty($config['empresa_logo'])): ?>
                <img
                    src="/uploads/logo/<?= e($config['empresa_logo']) ?>"
                    alt="<?= e($config['empresa_nome'] ?? APP_NAME) ?>"
                    class="login-logo-img">
            <?php else: ?>
                <div class="login-mark">
                    <svg viewBox="0 0 22 22" fill="none">
                        <path d="M4 6h14M4 11h9M4 16h12" stroke="#fff" stroke-width="1.8" stroke-linecap="round" />
                        <circle cx="17" cy="15.5" r="3.5" fill="none" stroke="#C9A84C" stroke-width="1.4" />
                        <path d="M16 15.5h2M17 14.5v2" stroke="#C9A84C" stroke-width="1.2" stroke-linecap="round" />
                    </svg>
                </div>
            <?php endif; ?>

            <div class="login-empresa-nome">
                <?= e($config['empresa_nome'] ?? APP_NAME) ?>
            </div>
            <div class="login-tagline">Gestão simplificada para o seu negócio</div>
        </div>

        <!-- Card de login -->
        <div class="login-card">

            <div class="login-card-title">Acesse sua conta</div>

            <?php if ($error = Session::getFlash('error')): ?>
                <div class="login-alert">
                    <i class="ti ti-alert-circle"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label" for="email">E-mail</label>
                    <div class="form-input-wrap">
                        <i class="ti ti-mail ti-icon"></i>
                        <input
                            class="form-input"
                            type="email"
                            id="email"
                            name="email"
                            value="<?= e(Session::getFlash('old_email', '')) ?>"
                            placeholder="seu@email.com"
                            required
                            autocomplete="email"
                            autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="senha">Senha</label>
                    <div class="form-input-wrap">
                        <i class="ti ti-lock ti-icon"></i>
                        <input
                            class="form-input has-toggle"
                            type="password"
                            id="senha"
                            name="senha"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password">
                        <button type="button" class="pwd-toggle" onclick="toggleSenha()" aria-label="Mostrar ou ocultar senha">
                            <i class="ti ti-eye" id="pwd-icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="ti ti-login"></i>
                    Entrar
                </button>

            </form>

        </div>

        <div class="login-footer">
            <?= e($config['empresa_nome'] ?? APP_NAME) ?> &nbsp;·&nbsp; <?= APP_NAME ?> v1.0
        </div>

    </div>

    <script>
        function toggleSenha() {
            var input = document.getElementById('senha');
            var icon = document.getElementById('pwd-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'ti ti-eye-off';
            } else {
                input.type = 'password';
                icon.className = 'ti ti-eye';
            }
        }
    </script>

</body>

</html>