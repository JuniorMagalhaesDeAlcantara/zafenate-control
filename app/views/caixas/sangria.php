<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sangria') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
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
            --font: 'Plus Jakarta Sans', system-ui, sans-serif;
            --bg: #F5F4F1;
            --card: #fff;
            --border: rgba(0, 0, 0, .08);
            --border-md: rgba(0, 0, 0, .14);
            --text: #1A1A1A;
            --text-2: #6B6B6B;
            --text-3: #A3A3A3;
            --amber: #D97706;
            --bg-amber: #FEF3C7;
            --red: #DC2626;
            --primary: #1A1A1A;
            --topbar: 52px;
        }

        html,
        body {
            font-family: var(--font);
            font-size: 14px;
            color: var(--text);
            background: var(--bg);
            min-height: 100%;
            -webkit-font-smoothing: antialiased;
        }

        /* Topbar */
        .topbar {
            height: var(--topbar);
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .topbar-brand {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }

        .topbar-sub {
            font-size: 10px;
            color: rgba(255, 255, 255, .4);
        }

        .topbar-actions {
            display: flex;
            gap: 8px;
        }

        .tbtn {
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
            background: rgba(255, 255, 255, .1);
            color: rgba(255, 255, 255, .85);
            transition: background .15s;
        }

        .tbtn:hover {
            background: rgba(255, 255, 255, .18);
            color: #fff;
        }

        /* Layout */
        .page {
            max-width: 480px;
            margin: 48px auto;
            padding: 0 20px 48px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Flash */
        .flash {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .flash-error {
            background: #FEE2E2;
            color: var(--red);
        }

        .flash-success {
            background: #DCFCE7;
            color: #16A34A;
        }

        /* Card */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 28px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-sub {
            font-size: 13px;
            color: var(--text-2);
            margin-bottom: 24px;
        }

        /* Saldo disponível */
        .saldo-box {
            background: var(--bg-amber);
            border: 1px solid rgba(217, 119, 6, .2);
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .saldo-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--amber);
            text-transform: uppercase;
        }

        .saldo-val {
            font-size: 20px;
            font-weight: 700;
            color: var(--amber);
        }

        /* Form */
        .form-row {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-2);
            margin-bottom: 6px;
        }

        .form-label span {
            color: var(--red);
        }

        .input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--border-md);
            border-radius: 8px;
            font-family: var(--font);
            font-size: 14px;
            outline: none;
            transition: border-color .15s;
            background: #fff;
        }

        .input:focus {
            border-color: var(--amber);
            box-shadow: 0 0 0 3px rgba(217, 119, 6, .1);
        }

        .input-money {
            font-size: 20px;
            font-weight: 700;
            text-align: right;
            letter-spacing: .5px;
        }

        .input-hint {
            font-size: 11px;
            color: var(--text-3);
            margin-top: 5px;
            text-align: right;
        }

        .char-count {
            font-size: 11px;
            color: var(--text-3);
            float: right;
            margin-top: 4px;
        }

        /* Botões */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 8px;
            font-family: var(--font);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .15s;
        }

        .btn:hover {
            opacity: .88;
        }

        .btn-amber {
            background: var(--amber);
            color: #fff;
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid var(--border-md);
            color: var(--text-2);
            margin-top: 10px;
            font-size: 13px;
            padding: 10px;
        }

        .btn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <header class="topbar">
        <div>
            <div class="topbar-brand">Zafenate Control</div>
            <div class="topbar-sub">Sangria de Caixa</div>
        </div>
        <div class="topbar-actions">
            <a href="/pdv" class="tbtn"><i class="ti ti-arrow-left"></i> Voltar ao PDV</a>
            <a href="/caixa" class="tbtn"><i class="ti ti-layout-dashboard"></i> Gestão</a>
        </div>
    </header>

    <div class="page">

        <?php if ($flash = \App\Core\Session::getFlash('error')): ?>
            <div class="flash flash-error"><i class="ti ti-alert-circle"></i> <?= e($flash) ?></div>
        <?php endif; ?>
        <?php if ($flash = \App\Core\Session::getFlash('success')): ?>
            <div class="flash flash-success"><i class="ti ti-circle-check"></i> <?= e($flash) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-title">
                <i class="ti ti-arrow-down-circle" style="color:var(--amber)"></i>
                Registrar Sangria
            </div>
            <div class="card-sub">Retirada de dinheiro do caixa #<?= (int)$caixa['id'] ?></div>

            <div class="saldo-box">
                <span class="saldo-label">Saldo disponível na gaveta</span>
                <span class="saldo-val">R$ <?= number_format($caixa['saldo_esperado'] ?? 0, 2, ',', '.') ?></span>
            </div>

            <form action="/caixa/sangria" method="POST" id="form-sangria">
                <?= \App\Core\Csrf::field() ?>
                <input type="hidden" name="caixa_id" value="<?= (int)$caixa['id'] ?>">

                <div class="form-row">
                    <label class="form-label" for="valor">Valor da Retirada <span>*</span></label>
                    <input type="text" id="valor" name="valor" value="0,00"
                        class="input input-money" required autocomplete="off"
                        onclick="this.select()">
                    <div class="input-hint" id="hint-saldo"></div>
                </div>

                <div class="form-row">
                    <label class="form-label" for="motivo">Motivo <span>*</span></label>
                    <input type="text" id="motivo" name="motivo" maxlength="200"
                        placeholder="Ex: Pagamento de fornecedor, depósito bancário..."
                        class="input" required>
                    <span class="char-count" id="char-count">0 / 200</span>
                </div>

                <button type="submit" class="btn btn-amber" id="btn-confirmar">
                    <i class="ti ti-arrow-down-circle"></i> Confirmar Sangria
                </button>
            </form>

            <a href="/pdv" class="btn btn-ghost">
                <i class="ti ti-x"></i> Cancelar
            </a>
        </div>

    </div>

    <script>
        const saldoDisponivel = <?= (float)($caixa['saldo_esperado'] ?? 0) ?>;

        // Máscara monetária
        const inputValor = document.getElementById('valor');
        inputValor.addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '');
            v = (parseInt(v || 0) / 100).toFixed(2).replace('.', ',');
            this.value = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            validarSaldo();
        });

        function parseMoney(str) {
            return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
        }

        function validarSaldo() {
            const val = parseMoney(inputValor.value);
            const hint = document.getElementById('hint-saldo');
            const btn = document.getElementById('btn-confirmar');

            if (val <= 0) {
                hint.textContent = '';
                btn.disabled = true;
                return;
            }
            if (val > saldoDisponivel) {
                hint.textContent = '⚠ Valor maior que o saldo disponível';
                hint.style.color = 'var(--red)';
                btn.disabled = true;
            } else {
                const restante = saldoDisponivel - val;
                hint.textContent = 'Saldo restante após sangria: R$ ' + restante.toFixed(2).replace('.', ',');
                hint.style.color = 'var(--text-3)';
                btn.disabled = false;
            }
        }

        // Contador de caracteres
        const motivoEl = document.getElementById('motivo');
        const charCount = document.getElementById('char-count');
        motivoEl.addEventListener('input', () => {
            charCount.textContent = motivoEl.value.length + ' / 200';
        });

        // Confirmação antes de submeter
        document.getElementById('form-sangria').addEventListener('submit', function(e) {
            const val = parseMoney(inputValor.value);
            const motivo = motivoEl.value.trim();

            if (val <= 0) {
                e.preventDefault();
                inputValor.focus();
                return;
            }
            if (motivo.length < 3) {
                e.preventDefault();
                alert('Informe o motivo (mínimo 3 caracteres).');
                motivoEl.focus();
                return;
            }
            const valFmt = val.toFixed(2).replace('.', ',');
            if (!confirm(`Confirma a sangria de R$ ${valFmt}?\n\nMotivo: ${motivo}`)) {
                e.preventDefault();
            }
        });

        // Estado inicial
        validarSaldo();
    </script>

</body>

</html>