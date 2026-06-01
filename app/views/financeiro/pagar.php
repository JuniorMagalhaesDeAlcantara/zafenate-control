<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">
        <?php require VIEW_PATH . '/layouts/navbar.php'; ?>

        <div class="zf-content">

            <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
                <div class="zf-alert zf-alert-success" data-auto-close><i class="ti ti-circle-check"></i> <?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close><i class="ti ti-alert-circle"></i> <?= e($msg) ?></div>
            <?php endif; ?>

            <!-- ─── CARDS TOTAIS ─── -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">

                <div class="zf-table-card" style="padding:20px;position:relative;overflow:hidden;">
                    <div style="font-size:12px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                        <i class="ti ti-clock"></i> A vencer
                    </div>
                    <div style="font-size:24px;font-weight:500;margin-bottom:4px;">
                        R$ <?= number_format($totais['a_vencer'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-tertiary);"><?= $totais['qtd_a_vencer'] ?? 0 ?> conta(s)</div>
                    <i class="ti ti-clock" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.06;"></i>
                </div>

                <div class="zf-table-card" style="padding:20px;position:relative;overflow:hidden;">
                    <div style="font-size:12px;color:var(--color-danger,#ef4444);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                        <i class="ti ti-alert-triangle"></i> Vencidas
                    </div>
                    <div style="font-size:24px;font-weight:500;margin-bottom:4px;color:var(--color-danger,#ef4444);">
                        R$ <?= number_format($totais['vencidas'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-tertiary);"><?= $totais['qtd_vencidas'] ?? 0 ?> conta(s)</div>
                    <i class="ti ti-alert-triangle" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.06;"></i>
                </div>

                <div class="zf-table-card" style="padding:20px;position:relative;overflow:hidden;">
                    <div style="font-size:12px;color:var(--color-success,#22c55e);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                        <i class="ti ti-circle-check"></i> Pagas (mês)
                    </div>
                    <div style="font-size:24px;font-weight:500;margin-bottom:4px;color:var(--color-success,#22c55e);">
                        R$ <?= number_format($totais['pagas_mes'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-tertiary);"><?= $totais['qtd_pagas_mes'] ?? 0 ?> conta(s)</div>
                    <i class="ti ti-circle-check" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.06;"></i>
                </div>

                <div class="zf-table-card" style="padding:20px;position:relative;overflow:hidden;">
                    <div style="font-size:12px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                        <i class="ti ti-calendar-month"></i> Total mês
                    </div>
                    <div style="font-size:24px;font-weight:500;margin-bottom:4px;">
                        R$ <?= number_format(($totais['a_vencer'] ?? 0) + ($totais['vencidas'] ?? 0), 2, ',', '.') ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-tertiary);">Em aberto</div>
                    <i class="ti ti-report-money" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.06;"></i>
                </div>

            </div>

            <!-- ─── FILTROS + AÇÃO ─── -->
            <div class="zf-table-card" style="padding:16px 20px;margin-bottom:16px;">
                <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">

                    <div style="flex:1;min-width:180px;">
                        <label style="font-size:12px;color:var(--text-tertiary);display:block;margin-bottom:4px;">Buscar</label>
                        <input type="text" name="busca" value="<?= e($filtros['busca']) ?>" placeholder="Descrição, documento..." class="zf-input" style="width:100%;">
                    </div>

                    <div style="min-width:140px;">
                        <label style="font-size:12px;color:var(--text-tertiary);display:block;margin-bottom:4px;">Status</label>
                        <select name="status" class="zf-input">
                            <option value="">Todos</option>
                            <option value="pendente" <?= $filtros['status'] === 'pendente'  ? 'selected' : '' ?>>Pendente</option>
                            <option value="vencida" <?= $filtros['status'] === 'vencida'   ? 'selected' : '' ?>>Vencida</option>
                            <option value="paga" <?= $filtros['status'] === 'paga'      ? 'selected' : '' ?>>Paga</option>
                            <option value="cancelada" <?= $filtros['status'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>

                    <div style="min-width:160px;">
                        <label style="font-size:12px;color:var(--text-tertiary);display:block;margin-bottom:4px;">Categoria</label>
                        <select name="categoria_id" class="zf-input">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $filtros['categoria_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label style="font-size:12px;color:var(--text-tertiary);display:block;margin-bottom:4px;">Vencimento de</label>
                        <input type="date" name="de" value="<?= e($filtros['de']) ?>" class="zf-input">
                    </div>

                    <div>
                        <label style="font-size:12px;color:var(--text-tertiary);display:block;margin-bottom:4px;">até</label>
                        <input type="date" name="ate" value="<?= e($filtros['ate']) ?>" class="zf-input">
                    </div>

                    <button type="submit" class="btn btn-sm btn-outline">
                        <i class="ti ti-search"></i> Filtrar
                    </button>
                    <a href="/financeiro/pagar" class="btn btn-sm btn-outline">
                        <i class="ti ti-x"></i>
                    </a>

                    <div style="margin-left:auto;">
                        <a href="/financeiro/pagar/criar" class="btn btn-sm btn-primary">
                            <i class="ti ti-plus"></i> Nova conta
                        </a>
                    </div>
                </form>
            </div>

            <!-- ─── TABELA ─── -->
            <div class="zf-table-card">
                <?php if (empty($contas)): ?>
                    <div style="padding:48px;text-align:center;color:var(--text-tertiary);">
                        <i class="ti ti-inbox" style="font-size:40px;display:block;margin-bottom:12px;opacity:.4;"></i>
                        Nenhuma conta encontrada.
                    </div>
                <?php else: ?>
                    <table class="zf-table">
                        <thead>
                            <tr>
                                <th>Descrição</th>
                                <th>Fornecedor</th>
                                <th>Categoria</th>
                                <th>Vencimento</th>
                                <th style="text-align:right;">Valor</th>
                                <th style="text-align:right;">Pago</th>
                                <th>Forma</th>
                                <th>Status</th>
                                <th style="text-align:right;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contas as $c): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:500;"><?= e($c['descricao']) ?></div>
                                        <?php if (!empty($c['documento'])): ?>
                                            <div style="font-size:11px;color:var(--text-tertiary);">Doc: <?= e($c['documento']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:var(--text-tertiary);font-size:13px;"><?= e($c['fornecedor_nome'] ?? '—') ?></td>
                                    <td style="font-size:13px;"><?= e($c['categoria'] ?? '—') ?></td>
                                    <td style="font-size:13px;<?= ($c['status_real'] === 'vencida') ? 'color:var(--color-danger,#ef4444);font-weight:500;' : '' ?>">
                                        <?= date('d/m/Y', strtotime($c['vencimento'])) ?>
                                    </td>
                                    <td style="text-align:right;font-weight:500;">
                                        R$ <?= number_format($c['valor'], 2, ',', '.') ?>
                                    </td>
                                    <td style="text-align:right;color:var(--text-tertiary);font-size:13px;">
                                        <?= $c['valor_pago'] > 0 ? 'R$ ' . number_format($c['valor_pago'], 2, ',', '.') : '—' ?>
                                    </td>
                                    <td style="font-size:12px;color:var(--text-tertiary);">
                                        <?= e($c['forma_pagamento'] ?? '—') ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badges = [
                                            'pendente'  => ['color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.12)', 'label' => 'Pendente'],
                                            'vencida'   => ['color' => '#ef4444', 'bg' => 'rgba(239,68,68,.12)', 'label' => 'Vencida'],
                                            'pago'      => ['color' => '#22c55e', 'bg' => 'rgba(34,197,94,.12)',      'label' => 'Paga'],
                                            'cancelado' => ['color' => '#9ca3af', 'bg' => 'rgba(156,163,175,.12)',    'label' => 'Cancelada'],
                                        ];
                                        $b = $badges[$c['status_real']] ?? $badges['pendente'];
                                        ?>
                                        <span style="display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;color:<?= $b['color'] ?>;background:<?= $b['bg'] ?>;">
                                            <?= $b['label'] ?>
                                        </span>
                                    </td>
                                    <td style="text-align:right;white-space:nowrap;">
                                        <?php if (in_array($c['status_real'], ['pendente', 'vencida'])): ?>
                                            <button class="btn btn-xs btn-outline"
                                                onclick="abrirBaixa(<?= $c['id'] ?>, <?= $c['valor'] ?>, <?= $c['valor_pago'] ?>)"
                                                title="Registrar baixa">
                                                <i class="ti ti-check"></i> Baixar
                                            </button>
                                            <button class="btn btn-xs btn-outline"
                                                onclick="abrirCancelar(<?= $c['id'] ?>)"
                                                title="Cancelar"
                                                style="color:var(--color-danger,#ef4444);border-color:currentColor;">
                                                <i class="ti ti-x"></i>
                                            </button>
                                        <?php elseif ($c['status_real'] === 'pago'): ?>
                                            <span style="font-size:12px;color:var(--text-tertiary);">
                                                <?= $c['data_pagamento'] ? date('d/m/Y', strtotime($c['data_pagamento'])) : '' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        </div><!-- /zf-content -->
    </div><!-- /zf-main -->
</div>

<!-- ─── MODAL BAIXA ─── -->
<div id="modal-baixa" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
    <div class="zf-table-card" style="width:100%;max-width:420px;padding:28px;position:relative;">
        <div style="font-size:16px;font-weight:500;margin-bottom:20px;">
            <i class="ti ti-check"></i> Registrar Baixa
        </div>
        <form id="form-baixa" method="POST">
            <?= csrf_field() ?>
            <div style="display:grid;gap:14px;">
                <div>
                    <label style="font-size:12px;color:var(--text-tertiary);display:block;margin-bottom:4px;">Valor pago (R$)</label>
                    <input type="text" name="valor_pago" id="baixa-valor" class="zf-input" style="width:100%;" required>
                </div>
                <div>
                    <label style="font-size:12px;color:var(--text-tertiary);display:block;margin-bottom:4px;">Forma de pagamento</label>
                    <select name="forma_pagamento" class="zf-input" style="width:100%;">
                        <option value="">Selecione...</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="pix">PIX</option>
                        <option value="boleto">Boleto</option>
                        <option value="transferencia">Transferência</option>
                        <option value="cartao_credito">Cartão de Crédito</option>
                        <option value="cartao_debito">Cartão de Débito</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;color:var(--text-tertiary);display:block;margin-bottom:4px;">Data do pagamento</label>
                    <input type="date" name="data_pagamento" id="baixa-data" class="zf-input" style="width:100%;" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end;">
                <button type="button" class="btn btn-sm btn-outline" onclick="fecharModal('modal-baixa')">Cancelar</button>
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-check"></i> Confirmar</button>
            </div>
        </form>
    </div>
</div>

<!-- ─── MODAL CANCELAR ─── -->
<div id="modal-cancelar" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
    <div class="zf-table-card" style="width:100%;max-width:380px;padding:28px;position:relative;">
        <div style="font-size:16px;font-weight:500;margin-bottom:20px;">
            <i class="ti ti-alert-triangle" style="color:var(--color-danger,#ef4444);"></i> Cancelar Conta
        </div>
        <form id="form-cancelar" method="POST">
            <?= csrf_field() ?>
            <div>
                <label style="font-size:12px;color:var(--text-tertiary);display:block;margin-bottom:4px;">Motivo</label>
                <textarea name="motivo" class="zf-input" style="width:100%;height:80px;resize:vertical;" placeholder="Motivo do cancelamento..."></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end;">
                <button type="button" class="btn btn-sm btn-outline" onclick="fecharModal('modal-cancelar')">Voltar</button>
                <button type="submit" class="btn btn-sm" style="background:var(--color-danger,#ef4444);color:#fff;border:none;">
                    <i class="ti ti-x"></i> Cancelar conta
                </button>
            </div>
        </form>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

<script>
    function abrirBaixa(id, valor, pago) {
        const restante = (valor - pago).toFixed(2).replace('.', ',');
        document.getElementById('baixa-valor').value = restante;
        document.getElementById('form-baixa').action = '/financeiro/pagar/' + id + '/baixar';
        const m = document.getElementById('modal-baixa');
        m.style.display = 'flex';
    }

    function abrirCancelar(id) {
        document.getElementById('form-cancelar').action = '/financeiro/pagar/' + id + '/cancelar';
        const m = document.getElementById('modal-cancelar');
        m.style.display = 'flex';
    }

    function fecharModal(id) {
        document.getElementById(id).style.display = 'none';
    }
    document.getElementById('modal-baixa').addEventListener('click', function(e) {
        if (e.target === this) fecharModal('modal-baixa');
    });
    document.getElementById('modal-cancelar').addEventListener('click', function(e) {
        if (e.target === this) fecharModal('modal-cancelar');
    });
</script>