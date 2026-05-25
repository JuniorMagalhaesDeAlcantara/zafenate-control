<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <style>
        body {
            font-family: sans-serif;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 360px;
        }

        h2 {
            margin-top: 0;
            color: #1f2937;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #4b5563;
            font-size: 14px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }
    </style>
</head>

<body>

    <div class="login-box">
        <h2>Zafenate Control</h2>

        <?php if ($error = old_flash('error')): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; text-align: center;">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required placeholder="admin@zafenate.com">
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="senha" required placeholder="••••••••">
            </div>

            <button type="submit">Entrar</button>
        </form>
    </div>

</body>