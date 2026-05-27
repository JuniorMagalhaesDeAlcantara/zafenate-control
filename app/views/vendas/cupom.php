<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cupom #<?= (int)$venda['numero'] ?></title>
    <style>
        /* ── Impressão 80mm ── */
        @page {
            size: 80mm auto;
            margin: 4mm 3mm;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            width: 80mm;
            margin: 0 auto;
            padding: 6px 4px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .empresa-nome {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 2px;
        }

        .empresa-sub {
            font-size: 10px;
            text-align: center;
            color: #555;
        }

        .sep {
            border: none;
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .sep-solid {
            border: none;
            border-top: 1px solid #000;
            margin: 6px 0;
        }

        .titulo-cupom {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1px;
            margin: 4px 0;
        }

        /* Itens */
        .item-row {
            margin-bottom: 4px;
        }

        .item-nome {
            font-weight: bold;
            font-size: 11px;
        }

        .item-detalhe {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }

        /* Totais */
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding: 1px 0;
        }

        .total-row.grande {
            font-size: 14px;
            font-weight: bold;
            padding: 4px 0;
        }

        /* Pagamentos */
        .pgto-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding: 1px 0;
        }

        /* Rodapé */
        .rodape {
            text-align: center;
            font-size: 10px;
            color: #555;
            margin-top: 6px;
        }

        /* Status cancelada */
        .cancelado-aviso {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            border: 2px solid #000;
            padding: 4px;
            margin: 6px 0;
            letter-spacing: 2px;
        }

        /* Botões de tela — somem na impressão */
        .no-print {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            background: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .15);
            font-family: system-ui, sans-serif;
        }

        .btn-imprimir {
            padding: 10px 24px;
            background: #1A1A1A;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-fechar {
            padding: 10px 24px;
            background: #fff;
            color: #374151;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
    </style>
</head>

<body>

    <?php
    $nomeEmpresa = $config['empresa_nome']    ?? 'Estabelecimento';
    $slogan      = $config['empresa_slogan']  ?? '';
    $cnpj        = $config['empresa_cnpj']    ?? '';
    $telefone    = $config['empresa_telefone'] ?? '';
    $isCancelada = $venda['status'] === 'cancelada';

    $nomesPgto = [
        'dinheiro'       => 'Dinheiro',
        'pix'            => 'PIX',
        'cartao_debito'  => 'Cartão Débito',
        'cartao_credito' => 'Cartão Crédito',
        'voucher'        => 'Voucher',
        'outros'         => 'Outros',
    ];
    ?>

    <!-- Cabeçalho empresa -->
    <div class="empresa-nome"><?= e($nomeEmpresa) ?></div>
    <?php if ($slogan): ?>
        <div class="empresa-sub"><?= e($slogan) ?></div>
    <?php endif; ?>
    <?php if ($cnpj): ?>
        <div class="empresa-sub">CNPJ: <?= e($cnpj) ?></div>
    <?php endif; ?>
    <?php if ($telefone): ?>
        <div class="empresa-sub">Tel: <?= e($telefone) ?></div>
    <?php endif; ?>

    <hr class="sep-solid">

    <!-- Status cancelada -->
    <?php if ($isCancelada): ?>
        <div class="cancelado-aviso">*** CANCELADA ***</div>
    <?php endif; ?>

    <div class="titulo-cupom">CUPOM NÃO FISCAL</div>

    <div style="font-size:10px; margin-bottom:4px;">
        <span>Venda: <strong>#<?= (int)$venda['numero'] ?></strong></span>
        &nbsp;&nbsp;
        <span><?= date('d/m/Y H:i', strtotime($venda['criado_em'])) ?></span>
    </div>

    <div style="font-size:10px; margin-bottom:2px;">
        Operador: <?= e($venda['operador_nome'] ?? '—') ?>
    </div>

    <?php if (!empty($venda['cliente_nome']) && $venda['cliente_nome'] !== 'Consumidor Final'): ?>
        <div style="font-size:10px; margin-bottom:2px;">
            Cliente: <?= e($venda['cliente_nome']) ?>
            <?php if (!empty($venda['cliente_cpf'])): ?>
                (<?= e($venda['cliente_cpf']) ?>)
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <hr class="sep">

    <!-- Itens -->
    <?php foreach ($venda['itens'] as $item): ?>
        <div class="item-row">
            <div class="item-nome"><?= e($item['produto_nome']) ?></div>
            <div class="item-detalhe">
                <span>
                    <?= number_format($item['quantidade'], 3, ',', '.') ?>
                    <?= e($item['unidade_sigla']) ?>
                    × R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?>
                </span>
                <span class="bold">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></span>
            </div>
        </div>
    <?php endforeach; ?>

    <hr class="sep">

    <!-- Totais -->
    <div class="total-row">
        <span>Subtotal</span>
        <span>R$ <?= number_format($venda['subtotal'], 2, ',', '.') ?></span>
    </div>

    <?php if ((float)$venda['desconto_valor'] > 0): ?>
        <div class="total-row">
            <span>Desconto
                <?php if ($venda['desconto_tipo'] === 'percentual'): ?>
                    (<?= number_format($venda['desconto_perc'], 1) ?>%)
                <?php endif; ?>
            </span>
            <span>− R$ <?= number_format($venda['desconto_valor'], 2, ',', '.') ?></span>
        </div>
    <?php endif; ?>

    <hr class="sep-solid">

    <div class="total-row grande">
        <span>TOTAL</span>
        <span>R$ <?= number_format($venda['total'], 2, ',', '.') ?></span>
    </div>

    <hr class="sep">

    <!-- Pagamentos -->
    <?php
    $trocoTotal = 0;
    foreach ($venda['pagamentos'] as $pgto):
        $trocoTotal += (float)$pgto['troco'];
    ?>
        <div class="pgto-row">
            <span><?= e($nomesPgto[$pgto['forma']] ?? $pgto['forma']) ?></span>
            <span>R$ <?= number_format($pgto['valor'], 2, ',', '.') ?></span>
        </div>
    <?php endforeach; ?>

    <?php if ($trocoTotal > 0): ?>
        <div class="pgto-row bold">
            <span>Troco</span>
            <span>R$ <?= number_format($trocoTotal, 2, ',', '.') ?></span>
        </div>
    <?php endif; ?>

    <?php if ($isCancelada): ?>
        <hr class="sep-solid">
        <div class="cancelado-aviso">*** CANCELADA ***</div>
        <div style="font-size:10px; text-align:center;">
            Cancelada em <?= date('d/m/Y H:i', strtotime($venda['cancelado_em'])) ?><br>
            Por: <?= e($venda['cancelado_por_nome'] ?? '—') ?>
            <?php if (!empty($venda['motivo_cancelamento'])): ?>
                <br>Motivo: <?= e($venda['motivo_cancelamento']) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <hr class="sep">

    <div class="rodape">
        Obrigado pela preferência!<br>
        <?= date('d/m/Y H:i:s') ?> — Sistema Zafenate
    </div>

    <!-- Botões (somem na impressão) -->
    <div class="no-print">
        <button class="btn-imprimir" onclick="window.print()">
            🖨 Imprimir
        </button>
        <a href="javascript:window.close()" class="btn-fechar">Fechar</a>
    </div>

    <script>
        // Auto-print ao abrir — remova se preferir manual
        window.addEventListener('load', function() {
            setTimeout(() => window.print(), 400);
        });
    </script>

</body>

</html>