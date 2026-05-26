<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Caixa — <?= e($config['empresa_nome'] ?? APP_NAME) ?></title>

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
            --red: #DC2626;
            --topbar-pdv: 52px;
        }

        html,
        body {
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

        .pdv-brand {
            display: flex;
            align-items: center;
            gap: 10px;
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

        .pdv-top-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pdv-tbtn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 6px;
            font-family: var(--font);
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.85);
            transition: background 0.15s;
        }

        .pdv-tbtn:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
        }

        .container-gestao {
            max-width: 680px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .badge-aberto {
            background: var(--bg-green);
            color: var(--green);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }

        /* Grid de cards idênticos aos seus */
        .gestao-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }

        .gestao-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px;
        }

        .gestao-card-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-3);
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .gestao-card-val {
            font-size: 18px;
            font-weight: 600;
            color: var(--text);
        }

        .card-fechamento {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        }

        .input-flat {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-md);
            border-radius: 6px;
            font-family: var(--font);
            font-size: 14px;
            outline: none;
        }

        .input-flat:focus {
            border-color: var(--primary);
        }

        .btn-danger-submit {
            width: 100%;
            padding: 14px;
            background: var(--red);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: var(--font);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-danger-submit:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>

    <header class="pdv-topbar">
        <div class="pdv-brand">
            <div>
                <div class="pdv-brand-name"><?= e($config['empresa_nome'] ?? APP_NAME) ?></div>
                <div class="pdv-brand-sub">Gerenciamento Operacional</div>
            </div>
        </div>
        <div class="pdv-top-actions">
            <a href="/pdv" class="pdv-tbtn">
                <i class="ti ti-shopping-cart"></i> Ir para Frente de Loja (PDV)
            </a>
            <a href="/dashboard" class="pdv-tbtn">
                <i class="ti ti-layout-dashboard"></i> Painel Geral
            </a>
        </div>
    </header>

    <div class="container-gestao">
        <div class="header-section">
            <h2 style="font-size: 18px; font-weight: 600;">Fluxo Técnico do Caixa #<?= $caixa['id'] ?></h2>
            <span class="badge-aberto">Turno Ativo</span>
        </div>

        <div class="gestao-grid">
            <div class="gestao-card">
                <div class="gestao-card-label">Saldo de Abertura</div>
                <div class="gestao-card-val">R$ <?= number_format($caixa['saldo_abertura'], 2, ',', '.') ?></div>
            </div>
            <div class="gestao-card">
                <div class="gestao-card-label">Faturamento (Vendas)</div>
                <div class="gestao-card-val" style="color: var(--green);">R$ <?= number_format($caixa['total_vendas'] ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>

        <div class="card-fechamento">
            <h3 style="font-size: 15px; font-weight: 600; margin-bottom: 20px;">🔒 Conferência e Fechamento</h3>

            <form action="/caixa/fechar" method="POST">

                <?= $csrf ?>



                <input type="hidden" name="caixa_id" value="<?= $caixa['id'] ?>">

                <div style="margin-bottom: 16px;">
                    <label class="input-flat-label" style="display:block; font-size:12px; font-weight:500; margin-bottom:6px; color:var(--text-2)">Dinheiro Físico em Gaveta (R$)</label>
                    <input type="text" name="saldo_informado" id="saldo_informado" value="0,00" class="input-flat" style="font-size:16px; font-weight:600; text-align:right;" required onclick="this.select()">
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display:block; font-size:12px; font-weight:500; margin-bottom:6px; color:var(--text-2)">Observações Adicionais</label>
                    <textarea name="observacao_fechamento" class="input-flat" rows="3" placeholder="Insira notas de fechamento (ex: sangrias pendentes ou justificativas de quebra de caixa)..."></textarea>
                </div>

                <button type="submit" class="btn-danger-submit">
                    <i class="ti ti-lock"></i> Encerrar Turno e Salvar Relatório
                </button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('saldo_informado').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2).replace('.', ',');
            e.target.value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        });
    </script>
</body>

</html>