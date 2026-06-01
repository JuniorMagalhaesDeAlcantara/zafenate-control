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
                        <i class="ti ti-clock"></i> A receber
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
                        <i class="ti ti-circle-check"></i> Recebidas (mês)
                    </div>
                    <div style="font-size:24px;font-weight:500;margin-bottom:4px;color:var(--color-success,#22c55e);">
                        R$ <?= number_format($totais['recebidas_mes'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-tertiary);"><?= $totais['qtd_recebidas_mes'] ?? 0 ?> conta(s)</div>
                    <i class="ti ti-circle-check" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.06;"></i>
                </div>

                <div class="zf-table-card" style="padding:20px;position:relative;overflow:hidden;">
                    <div style="font-size:12px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                        <i class="ti ti-calendar-month"></i> Total em aberto
                    </div>
                    <div style="font-size:24px;font-weight:500;margin-bottom:4px;">
                        R$ <?= number_format(($totais['a_vencer'] ?? 0) + ($totais['vencidas'] ?? 0), 2, ',', '.') ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-tertiary);">Pendente de recebimento</div>
                    <i class="ti ti-cash" style="position:absolute;right:16px;top:16px;font-size:28px;opacity:.06;"></i>
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
                            <option value="aberto" <?= $filtros['status'] === 'aberto'    ? 'selected' : '' ?>>Pendente</option>
                            <option value="parcial" <?= $filtros['status'] === 'parcial'   ? 'selected' : '' ?>>Parcial</option>
                            <option value="recebido" <?= $filtros['status'] === 'recebido'  ? 'selected' : '' ?>>Recebida</option>
                            <option value="cancelado" <?= $filtros['status'] === 'cancelado' ? 'selected' : '' ?>>Cancelada</option>
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
                    <a href="/financeiro/receber" class="btn btn-sm btn-outline">
                        <i class="ti ti-x"></i>
                    </a>

                    <div style="margin-left:auto;">
                        <a href="/financeiro/receber/criar" class="btn btn-sm btn-primary">
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
                                <th>Cliente</th>
                                <th>Categoria</th>
                                <th>Vencimento</th>
                                <th style="text-align:right;">Valor</th>
                                <th style="text-align:right;">Recebido</th>
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
                                        <?php if (!empty($c['venda_id'])): ?>
                                            <div style="font-size:11px;color:var(--text-tertiary);">
                                                <i class="ti ti-tag" style="font-size:10px;"></i> Venda a prazo
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:var(--text-tertiary);font-size:13px;"><?= e($c['cliente_nome']   ?? '—') ?></td>
                                    <td style="font-size:13px;"><?= e($c['categoria_nome'] ?? '—') ?></td>
                                    <td style="font-size:13px;<?= ($c['status_real'] === 'vencido') ? 'color:var(--color-danger,#ef4444);font-weight:500;' : '' ?>">
                                        <?= date('d/m/Y', strtotime($c['vencimento'])) ?>
                                        <?php if (($c['total_parcelas'] ?? 1) > 1): ?>
                                            <div style="font-size:10px;color:var(--text-tertiary);">
                                                Parcela <?= $c['numero_parcela'] ?>/<?= $c['total_parcelas'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;font-weight:500;">
                                        R$ <?= number_format($c['valor'], 2, ',', '.') ?>
                                    </td>
                                    <td style="text-align:right;color:var(--text-tertiary);font-size:13px;">
                                        <?= ($c['valor_recebido'] ?? 0) > 0 ? 'R$ ' . number_format($c['valor_recebido'], 2, ',', '.') : '—' ?>
                                    </td>
                                    <td style="font-size:12px;color:var(--text-tertiary);">
                                        <?= e($c['forma_recebimento'] ?? '—') ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badges = [
                                            'aberto'    => ['color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.12)',   'label' => 'Pendente'],
                                            'parcial'   => ['color' => '#3b82f6', 'bg' => 'rgba(59,130,246,.12)',  'label' => 'Parcial'],
                                            'vencido'   => ['color' => '#ef4444', 'bg' => 'rgba(239,68,68,.12)',   'label' => 'Vencida'],
                                            'recebido'  => ['color' => '#22c55e', 'bg' => 'rgba(34,197,94,.12)',   'label' => 'Recebida'],
                                            'cancelado' => ['color' => '#9ca3af', 'bg' => 'rgba(156,163,175,.12)', 'label' => 'Cancelada'],
                                        ];
                                        $b = $badges[$c['status_real']] ?? $badges['aberto'];
                                        ?>
                                        <span style="display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;color:<?= $b['color'] ?>;background:<?= $b['bg'] ?>;">
                                            <?= $b['label'] ?>
                                        </span>
                                    </td>
                                    <td style="text-align:right;white-space:nowrap;">
                                        <?php if (in_array($c['status_real'], ['aberto', 'vencido', 'parcial'])): ?>
                                            <button class="btn btn-xs btn-outline"
                                                onclick="abrirBaixa(<?= $c['id'] ?>, <?= $c['valor'] ?>, <?= $c['valor_recebido'] ?? 0 ?>)"
                                                title="Registrar recebimento">
                                                <i class="ti ti-check"></i> Receber
                                            </button>
                                            <button class="btn btn-xs btn-outline"
                                                onclick="abrirCancelar(<?= $c['id'] ?>, <?= $c['venda_id'] ?? 'null' ?>)"
                                                title="Cancelar"
                                                style="color:var(--color-danger,#ef4444);border-color:currentColor;">
                                                <i class="ti ti-x"></i>
                                            </button>
                                        <?php elseif ($c['status_real'] === 'recebido'): ?>
                                            <span style="font-size:12px;color:var(--text-tertiary);">
                                                <?= !empty($c['data_recebimento']) ? date('d/m/Y', strtotime($c['data_recebimento'])) : '' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- ─── MODAL RECEBIMENTO ─── -->
<div id="modal-baixa" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
    <div class="zf-table-card" style="width:100%;max-width:420px;padding:28px;position:relative;">
        <div style="font-size:16px;font-weight:500;margin-bottom:20px;">
            <i class="ti ti-check"></i> Registrar Recebimento
        </div>
        <form id="form-baixa" method="POST">
            <?= csrf_field() ?>
            <div style="display:grid;gap:14px;">
                <div>
                    <label style="font-size:12px;color:var(--text-tertiary);display:block;margin-bottom:4px;">Valor recebido (R$)</label>
                    <input type="text" name="valor_recebido" id="baixa-valor" class="zf-input" style="width:100%;" required>
                </div>
                <div>
                    <label style="font-size:12px;color:var(--text-tertiary);display:block;margin-bottom:4px;">Forma de recebimento</label>
                    <select name="forma_recebimento" class="zf-input" style="width:100%;">
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
                    <label style="font-size:12px;color:var(--text-tertiary);display:block;margin-bottom:4px;">Data do recebimento</label>
                    <input type="date" name="data_recebimento" id="baixa-data" class="zf-input" style="width:100%;" value="<?= date('Y-m-d') ?>">
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
    <div class="zf-table-card" style="width:100%;max-width:380px;padding:28px;">
        <div style="font-size:16px;font-weight:500;margin-bottom:20px;">
            <i class="ti ti-alert-triangle" style="color:var(--color-danger,#ef4444);"></i> Cancelar Conta
        </div>
        <form id="form-cancelar" method="POST">
            <?= csrf_field() ?>

            <!-- ✅ Aviso quando conta está vinculada a uma venda — era esse o elemento que faltava -->
            <div id="cancelar-aviso-venda" style="display:none;margin-bottom:14px;padding:10px 14px;background:rgba(239,68,68,.1);border-radius:8px;font-size:13px;color:var(--color-danger,#ef4444);">
                <i class="ti ti-alert-triangle"></i>
                <strong>Atenção:</strong> Esta conta está vinculada a uma venda a prazo.
                Ao cancelar, a venda será cancelada e o estoque será estornado automaticamente.
            </div>

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
    function abrirBaixa(id, valor, recebido) {
        const restante = (valor - recebido).toFixed(2).replace('.', ',');
        document.getElementById('baixa-valor').value = restante;
        document.getElementById('form-baixa').action = '/financeiro/receber/' + id + '/baixar';
        document.getElementById('modal-baixa').style.display = 'flex';
    }

    function abrirCancelar(id, vendaId) {
        document.getElementById('form-cancelar').action = '/financeiro/receber/' + id + '/cancelar';
        const aviso = document.getElementById('cancelar-aviso-venda');
        aviso.style.display = vendaId ? '' : 'none';
        document.getElementById('modal-cancelar').style.display = 'flex';
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