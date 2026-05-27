<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">

        <?php
        $pageTitle  = "Venda #{$venda['numero']}";
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Vendas',    'url' => '/vendas'],
            ['label' => "#{$venda['numero']}", 'url' => '#'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <?php if ($f = \App\Core\Session::getFlash('success')): ?>
                <div class="zf-alert zf-alert-success" data-auto-close>
                    <i class="ti ti-circle-check"></i> <?= e($f) ?>
                </div>
            <?php endif; ?>
            <?php if ($f = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close>
                    <i class="ti ti-alert-circle"></i> <?= e($f) ?>
                </div>
            <?php endif; ?>

            <!-- Header da venda -->
            <div class="zf-table-card" style="padding:20px 24px; margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">

                    <div>
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                            <h2 style="font-size:18px;font-weight:700;">Venda #<?= (int)$venda['numero'] ?></h2>
                            <?php if ($venda['status'] === 'finalizada'): ?>
                                <span class="badge badge-success" style="font-size:12px;">Finalizada</span>
                            <?php elseif ($venda['status'] === 'cancelada'): ?>
                                <span class="badge badge-danger" style="font-size:12px;">Cancelada</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-sm text-muted" style="display:flex;flex-direction:column;gap:3px;">
                            <span><i class="ti ti-calendar"></i> <?= date('d/m/Y \à\s H:i', strtotime($venda['criado_em'])) ?></span>
                            <span><i class="ti ti-user"></i> Operador: <?= e($venda['operador_nome'] ?? '—') ?></span>
                            <span><i class="ti ti-user-circle"></i> Cliente: <?= e($venda['cliente_nome'] ?? 'Consumidor Final') ?></span>
                        </div>
                    </div>

                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <a href="/vendas/<?= $venda['id'] ?>/cupom" target="_blank" class="btn btn-outline btn-sm">
                            <i class="ti ti-printer"></i> Reimprimir Cupom
                        </a>
                        <a href="/vendas" class="btn btn-outline btn-sm">
                            <i class="ti ti-arrow-left"></i> Voltar
                        </a>
                        <?php if ($venda['status'] === 'finalizada' && in_array($nivel, ['admin', 'gerente'])): ?>
                            <button class="btn btn-sm" onclick="abrirModalCancelamento()"
                                style="background:#FEE2E2;color:#DC2626;border:1px solid #FECACA;">
                                <i class="ti ti-ban"></i> Cancelar Venda
                            </button>
                        <?php endif; ?>
                    </div>

                </div>

                <?php if ($venda['status'] === 'cancelada'): ?>
                    <div style="margin-top:16px; padding:12px 16px; background:#FEF2F2; border:1px solid #FECACA; border-radius:8px; font-size:13px;">
                        <strong style="color:#DC2626;"><i class="ti ti-ban"></i> Venda cancelada</strong>
                        em <?= date('d/m/Y H:i', strtotime($venda['cancelado_em'])) ?>
                        por <?= e($venda['cancelado_por_nome'] ?? '—') ?><br>
                        <span style="color:#6B7280;">Motivo: <?= e($venda['motivo_cancelamento']) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div style="display:grid; grid-template-columns:1fr 300px; gap:16px;">

                <!-- Itens -->
                <div class="zf-table-card">
                    <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; font-weight:600; font-size:14px;">
                        <i class="ti ti-list"></i> Itens (<?= count($venda['itens']) ?>)
                    </div>
                    <table class="zf-table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th style="width:80px;text-align:center">Qtd</th>
                                <th style="width:100px;text-align:right">Unit.</th>
                                <th style="width:110px;text-align:right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($venda['itens'] as $item): ?>
                                <tr>
                                    <td>
                                        <div class="td-name"><?= e($item['produto_nome']) ?></div>
                                        <div class="td-sub"><?= e($item['produto_codigo']) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format($item['quantidade'], 3, ',', '.') ?>
                                        <span class="text-muted text-sm"><?= e($item['unidade_sigla']) ?></span>
                                    </td>
                                    <td style="text-align:right;">
                                        R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?>
                                    </td>
                                    <td style="text-align:right; font-weight:600;">
                                        R$ <?= number_format($item['subtotal'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Resumo + Pagamentos -->
                <div style="display:flex; flex-direction:column; gap:16px;">

                    <!-- Totais -->
                    <div class="zf-table-card" style="padding:20px;">
                        <div style="font-weight:600;font-size:13px;margin-bottom:14px;">Resumo</div>
                        <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;">
                            <div style="display:flex;justify-content:space-between;">
                                <span class="text-muted">Subtotal</span>
                                <span>R$ <?= number_format($venda['subtotal'], 2, ',', '.') ?></span>
                            </div>
                            <?php if ((float)$venda['desconto_valor'] > 0): ?>
                                <div style="display:flex;justify-content:space-between;color:#DC2626;">
                                    <span>Desconto
                                        <?php if ($venda['desconto_tipo'] === 'percentual'): ?>
                                            (<?= number_format($venda['desconto_perc'], 1) ?>%)
                                        <?php endif; ?>
                                    </span>
                                    <span>− R$ <?= number_format($venda['desconto_valor'], 2, ',', '.') ?></span>
                                </div>
                            <?php endif; ?>
                            <hr style="border:none;border-top:1px solid #f3f4f6;margin:4px 0;">
                            <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;">
                                <span>Total</span>
                                <span>R$ <?= number_format($venda['total'], 2, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Pagamentos -->
                    <div class="zf-table-card" style="padding:20px;">
                        <div style="font-weight:600;font-size:13px;margin-bottom:14px;">
                            <i class="ti ti-credit-card"></i> Pagamentos
                        </div>
                        <?php
                        $icones = [
                            'dinheiro'       => 'ti-cash',
                            'pix'            => 'ti-brand-cashapp',
                            'cartao_debito'  => 'ti-credit-card',
                            'cartao_credito' => 'ti-credit-card',
                            'voucher'        => 'ti-ticket',
                            'outros'         => 'ti-dots',
                        ];
                        $nomes = [
                            'dinheiro'       => 'Dinheiro',
                            'pix'            => 'PIX',
                            'cartao_debito'  => 'Cartão Débito',
                            'cartao_credito' => 'Cartão Crédito',
                            'voucher'        => 'Voucher',
                            'outros'         => 'Outros',
                        ];
                        ?>
                        <?php foreach ($venda['pagamentos'] as $pgto): ?>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f9fafb;font-size:13px;">
                                <span>
                                    <i class="ti <?= e($icones[$pgto['forma']] ?? 'ti-cash') ?>" style="margin-right:4px;"></i>
                                    <?= e($nomes[$pgto['forma']] ?? $pgto['forma']) ?>
                                </span>
                                <div style="text-align:right;">
                                    <div style="font-weight:600;">R$ <?= number_format($pgto['valor'], 2, ',', '.') ?></div>
                                    <?php if ((float)$pgto['troco'] > 0): ?>
                                        <div class="text-muted text-sm">Troco: R$ <?= number_format($pgto['troco'], 2, ',', '.') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de cancelamento -->
<?php if ($venda['status'] === 'finalizada' && in_array($nivel, ['admin', 'gerente'])): ?>
    <div id="modal-cancelamento" style="display:none; position:fixed; inset:0; z-index:999; background:rgba(0,0,0,.5); align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:12px; padding:28px; width:100%; max-width:440px; margin:20px;">
            <h3 style="font-size:16px;font-weight:700;margin-bottom:6px;color:#DC2626;">
                <i class="ti ti-ban"></i> Cancelar Venda #<?= (int)$venda['numero'] ?>
            </h3>
            <p style="font-size:13px;color:#6B7280;margin-bottom:20px;">
                Esta ação irá estornar o estoque de <?= count($venda['itens']) ?> item(s) e reverter os totais do caixa.
                <strong>Não pode ser desfeita.</strong>
            </p>

            <form action="/vendas/<?= $venda['id'] ?>/cancelar" method="POST">
                <?= \App\Core\Csrf::field() ?>
                <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">
                    Motivo do cancelamento <span style="color:#DC2626">*</span>
                </label>
                <textarea name="motivo_cancelamento" required minlength="5"
                    style="width:100%;padding:10px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;resize:none;font-family:inherit;"
                    rows="3" placeholder="Descreva o motivo..."></textarea>

                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button type="button" onclick="fecharModalCancelamento()"
                        style="flex:1;padding:11px;border:1px solid #D1D5DB;border-radius:8px;background:#fff;font-size:14px;font-weight:600;cursor:pointer;">
                        Cancelar
                    </button>
                    <button type="submit"
                        style="flex:1;padding:11px;border:none;border-radius:8px;background:#DC2626;color:#fff;font-size:14px;font-weight:600;cursor:pointer;">
                        Confirmar Cancelamento
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalCancelamento() {
            document.getElementById('modal-cancelamento').style.display = 'flex';
        }

        function fecharModalCancelamento() {
            document.getElementById('modal-cancelamento').style.display = 'none';
        }
        document.getElementById('modal-cancelamento').addEventListener('click', function(e) {
            if (e.target === this) fecharModalCancelamento();
        });
    </script>
<?php endif; ?>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>