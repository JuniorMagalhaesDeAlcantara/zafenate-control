<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abertura de Caixa — <?= e($config['empresa_nome'] ?? APP_NAME) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary:   <?= e($config['empresa_cor'] ?? '#1A1A1A') ?>;
            --font:      'Plus Jakarta Sans', system-ui, sans-serif;
            --bg:        #F5F4F1;
            --card:      #FFFFFF;
            --border:    rgba(0,0,0,0.08);
            --border-md: rgba(0,0,0,0.14);
            --text:      #1A1A1A;
            --text-2:    #6B6B6B;
            --text-3:    #A3A3A3;
            --topbar-pdv: 52px;
        }
        html, body {
            font-family: var(--font);
            font-size: 14px;
            color: var(--text);
            background: var(--bg);
            height: 100%;
            -webkit-font-smoothing: antialiased;
        }
        .pdv-topbar {
            height: var(--topbar-pdv);
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
        }
        .pdv-brand { display: flex; align-items: center; gap: 10px; }
        .pdv-brand-name { font-size: 13px; font-weight: 600; color: #fff; }
        .pdv-brand-sub  { font-size: 10px; color: rgba(255,255,255,0.4); }
        .pdv-tbtn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 6px 12px; border-radius: 6px;
            font-family: var(--font); font-size: 12px; font-weight: 500;
            cursor: pointer; border: none; text-decoration: none;
            background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.85);
            transition: background 0.15s;
        }
        .pdv-tbtn:hover { background: rgba(255,255,255,0.18); color: #fff; }

        .container-abertura {
            max-width: 460px;
            margin: 80px auto padding: 0 20px;
        }
        .card-abertura {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        }
        .card-title { font-size: 16px; font-weight: 600; margin-bottom: 6px; color: var(--text); }
        .card-subtitle { font-size: 13px; color: var(--text-2); margin-bottom: 24px; }
        
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 12px; font-weight: 500; color: var(--text-2); margin-bottom: 6px; }
        .input-money {
            width: 100%; padding: 12px 14px;
            border: 2px solid var(--border-md); border-radius: 8px;
            font-family: var(--font); font-size: 18px; font-weight: 600;
            text-align: right; outline: none; transition: border-color 0.15s;
        }
        .input-money:focus { border-color: var(--primary); }
        .btn-submit {
            width: 100%; padding: 14px; background: var(--primary); color: #fff;
            border: none; border-radius: 8px; font-family: var(--font);
            font-size: 14px; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: opacity 0.15s;
        }
        .btn-submit:hover { opacity: 0.9; }
    </style>
</head>
<body>

    <header class="pdv-topbar">
        <div class="pdv-brand">
            <div>
                <div class="pdv-brand-name"><?= e($config['empresa_nome'] ?? APP_NAME) ?></div>
                <div class="pdv-brand-sub">Controle de Fluxo</div>
            </div>
        </div>
        <div>
            <a href="/dashboard" class="pdv-tbtn">
                <i class="ti ti-layout-dashboard"></i> Painel Geral
            </a>
        </div>
    </header>

    <div class="container-abertura">
        <div class="card-abertura">
            <h3 class="card-title">🔑 Iniciar Novo Turno</h3>
            <p class="card-subtitle">Informe o saldo inicial (fundo de troco) da gaveta para abrir.</p>

            <form action="/caixa/abrir" method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="saldo_abertura">Valor de Abertura (R$)</label>
                    <input 
                        type="text" 
                        name="saldo_abertura" 
                        id="saldo_abertura" 
                        value="0,00" 
                        class="input-money"
                        required
                        onclick="this.select()"
                    >
                </div>
                <button type="submit" class="btn-submit">
                    <i class="ti ti-key"></i> Abrir Caixa e Ir para PDV
                </button>
            </form>
        </div>
    </div>

    <script>
        // Mascara simples para real
        document.getElementById('saldo_abertura').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2).replace('.', ',');
            e.target.value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        });
    </script>
</body>
</html>