<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">

        <?php
        $pageTitle  = 'Nova Movimentação de Estoque';
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Estoque',   'url' => '/estoque'],
            ['label' => 'Nova Movimentação'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <?php if ($f = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close>
                    <i class="ti ti-alert-circle"></i> <?= e($f) ?>
                </div>
            <?php endif; ?>

            <div style="max-width:680px;">
                <div class="zf-table-card" style="padding:28px 32px;">

                    <form action="/estoque/movimentar" method="POST" id="form-mov">
                        <?= csrf_field() ?>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

                            <!-- Motivo -->
                            <div style="grid-column:span 2;">
                                <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">
                                    Motivo da Movimentação <span style="color:#DC2626;">*</span>
                                </label>
                                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                                    <select name="motivo" id="sel_motivo" class="form-control" required style="flex:1; min-width:200px;">
                                        <option value="">— Selecione o motivo —</option>
                                        <optgroup label="── Entradas ──">
                                            <option value="COMPRA">Compra / Reposição</option>
                                            <option value="DEVOLUCAO">Devolução de Cliente</option>
                                            <option value="CANCELAMENTO_VENDA">Cancelamento de Venda</option>
                                        </optgroup>
                                        <optgroup label="── Saídas ──">
                                            <option value="VENDA">Venda (registro manual)</option>
                                            <option value="PERDA">Perda / Vencimento</option>
                                            <option value="AVARIA">Avaria</option>
                                            <option value="USO_INTERNO">Uso Interno</option>
                                            <option value="TRANSFERENCIA">Transferência</option>
                                        </optgroup>
                                        <optgroup label="── Ajustes ──">
                                            <option value="AJUSTE_MANUAL">Ajuste Manual</option>
                                            <option value="INVENTARIO">Ajuste de Inventário</option>
                                        </optgroup>
                                    </select>

                                    <!-- Badge de tipo dinâmico -->
                                    <span id="tipo_badge" style="display:inline-flex; align-items:center; gap:6px;
                                          padding:6px 12px; border-radius:8px; font-size:13px; font-weight:600;
                                          background:#F4F4F5; color:#71717A; transition:all .2s; white-space:nowrap;">
                                        <i class="ti ti-help-circle"></i>
                                        <span id="tipo_text">Selecione o motivo</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Produto -->
                            <div style="grid-column:span 2;">
                                <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">
                                    Produto <span style="color:#DC2626;">*</span>
                                </label>
                                <select name="produto_id" id="sel_produto" class="form-control" required>
                                    <option value="">— Selecione o produto —</option>
                                    <?php foreach ($produtos as $p): ?>
                                        <option value="<?= (int)$p['id'] ?>"
                                            data-estoque="<?= (float)$p['estoque_atual'] ?>"
                                            data-unidade="<?= e($p['unidade_sigla'] ?? 'UN') ?>">
                                            <?= e($p['nome']) ?>
                                            <?php if (!empty($p['codigo'])): ?>[<?= e($p['codigo']) ?>]<?php endif ?>
                                            — Estoque atual: <?= number_format($p['estoque_atual'], 3, ',', '.') ?> <?= e($p['unidade_sigla'] ?? 'UN') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Quantidade -->
                            <div>
                                <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">
                                    Quantidade <span style="color:#DC2626;">*</span>
                                    <span id="qty_hint" style="font-weight:400; font-size:10px; color:#9CA3AF; text-transform:none; margin-left:4px;"></span>
                                </label>
                                <input type="text" name="quantidade" id="inp_qty"
                                    class="form-control"
                                    placeholder="0,000"
                                    required
                                    autocomplete="off"
                                    style="font-size:16px; font-weight:600; text-align:right;">
                            </div>

                            <!-- Preview estoque -->
                            <div>
                                <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">
                                    Resultado no Estoque
                                </label>
                                <div id="stock_preview" style="display:none; padding:10px 14px;
                                     border:1px solid var(--border); border-radius:8px; background:var(--surface, #f9f9f7);">
                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <span id="prev_antes" style="font-size:16px; font-weight:700; color:#6b7280;"></span>
                                        <i class="ti ti-arrow-right" style="color:#d1d5db;"></i>
                                        <span id="prev_depois" style="font-size:16px; font-weight:700;"></span>
                                        <span id="prev_unidade" style="font-size:12px; color:#9CA3AF;"></span>
                                    </div>
                                    <div id="prev_alerta" style="display:none; margin-top:5px; font-size:12px; color:#DC2626;">
                                        <i class="ti ti-alert-triangle"></i> Estoque ficará negativo.
                                    </div>
                                </div>
                            </div>

                            <!-- Observação -->
                            <div style="grid-column:span 2;">
                                <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">
                                    Observação
                                </label>
                                <input type="text" name="observacao" class="form-control" maxlength="255"
                                    placeholder="Ex: produto vencido lote 0042, reposição semanal, ajuste de inventário...">
                            </div>

                            <!-- ── Campos extras: Compra ── -->
                            <div id="sec_divider" style="grid-column:span 2; display:none;">
                                <div style="display:flex; align-items:center; gap:10px; font-size:11px; font-weight:600;
                                     text-transform:uppercase; color:#9CA3AF;">
                                    <span style="flex:1; height:1px; background:var(--border);"></span>
                                    Dados da Nota Fiscal (opcional)
                                    <span style="flex:1; height:1px; background:var(--border);"></span>
                                </div>
                            </div>

                            <div id="sec_fornecedor" style="display:none;">
                                <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">
                                    Fornecedor
                                </label>
                                <select name="fornecedor_id" class="form-control">
                                    <option value="">— Nenhum —</option>
                                    <?php foreach ($fornecedores as $f): ?>
                                        <option value="<?= (int)$f['id'] ?>"><?= e($f['razao_social']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="sec_nf" style="display:none;">
                                <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">
                                    Número da NF
                                </label>
                                <input type="text" name="numero_nf" class="form-control"
                                    placeholder="000000" maxlength="20">
                            </div>

                            <div id="sec_custo" style="display:none;">
                                <label class="form-label" style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;">
                                    Custo Unitário (R$)
                                </label>
                                <input type="text" name="preco_custo_unitario" id="inp_custo"
                                    class="form-control" placeholder="0,00" autocomplete="off">
                            </div>

                        </div><!-- /grid -->

                        <div style="display:flex; gap:10px; margin-top:28px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check"></i> Registrar Movimentação
                            </button>
                            <a href="/estoque" class="btn btn-outline">Cancelar</a>
                        </div>

                    </form>

                </div>
            </div>

        </div><!-- /zf-content -->
    </div><!-- /zf-main -->
</div><!-- /zf-layout -->

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

<script>
    const MOTIVO_TIPO = <?= json_encode(\App\Models\MovimentacaoEstoque::MOTIVO_TIPO) ?>;

    const TIPO_CONFIG = {
        ENTRADA: {
            bg: '#DCFCE7',
            color: '#15803D',
            icon: 'ti-arrow-down-circle',
            label: 'Entrada no Estoque'
        },
        SAIDA: {
            bg: '#FEE2E2',
            color: '#B91C1C',
            icon: 'ti-arrow-up-circle',
            label: 'Saída do Estoque'
        },
        AJUSTE: {
            bg: '#EFF6FF',
            color: '#1D4ED8',
            icon: 'ti-adjustments-alt',
            label: 'Ajuste de Estoque'
        },
    };

    const selMotivo = document.getElementById('sel_motivo');
    const selProduto = document.getElementById('sel_produto');
    const inpQty = document.getElementById('inp_qty');
    const inpCusto = document.getElementById('inp_custo');
    const tipoBadge = document.getElementById('tipo_badge');
    const stockPreview = document.getElementById('stock_preview');
    const prevAntes = document.getElementById('prev_antes');
    const prevDepois = document.getElementById('prev_depois');
    const prevUnidade = document.getElementById('prev_unidade');
    const prevAlerta = document.getElementById('prev_alerta');
    const qtyHint = document.getElementById('qty_hint');

    const secsCompra = ['sec_divider', 'sec_fornecedor', 'sec_nf', 'sec_custo'];

    function atualizarTipo() {
        const motivo = selMotivo.value;
        const tipo = MOTIVO_TIPO[motivo] || null;

        if (tipo && TIPO_CONFIG[tipo]) {
            const cfg = TIPO_CONFIG[tipo];
            tipoBadge.style.background = cfg.bg;
            tipoBadge.style.color = cfg.color;
            tipoBadge.innerHTML = `<i class="ti ${cfg.icon}"></i><span>${cfg.label}</span>`;
        } else {
            tipoBadge.style.background = '#F4F4F5';
            tipoBadge.style.color = '#71717A';
            tipoBadge.innerHTML = `<i class="ti ti-help-circle"></i><span>Selecione o motivo</span>`;
        }

        qtyHint.textContent = tipo === 'AJUSTE' ? '(novo valor absoluto do estoque)' : '';

        secsCompra.forEach(id => {
            document.getElementById(id).style.display = motivo === 'COMPRA' ? '' : 'none';
        });

        atualizarPreview();
    }

    function atualizarPreview() {
        const tipo = MOTIVO_TIPO[selMotivo.value] || null;
        const opt = selProduto.options[selProduto.selectedIndex];
        const estoque = opt ? parseFloat(opt.dataset.estoque ?? 0) : null;
        const unidade = opt ? (opt.dataset.unidade || 'UN') : '';
        const qtd = parseFloat(inpQty.value.replace(',', '.')) || 0;

        if (!tipo || !selProduto.value || qtd <= 0) {
            stockPreview.style.display = 'none';
            return;
        }

        stockPreview.style.display = 'block';

        const depois = tipo === 'ENTRADA' ? estoque + qtd :
            tipo === 'SAIDA' ? estoque - qtd :
            qtd; // AJUSTE

        prevAntes.textContent = fmt(estoque);
        prevDepois.textContent = fmt(depois);
        prevDepois.style.color = depois < 0 ? '#DC2626' :
            tipo === 'ENTRADA' ? '#15803D' :
            tipo === 'SAIDA' ? '#B91C1C' :
            '#1D4ED8';
        prevUnidade.textContent = unidade;
        prevAlerta.style.display = depois < 0 ? 'block' : 'none';
    }

    function fmt(n) {
        return n.toLocaleString('pt-BR', {
            minimumFractionDigits: 3,
            maximumFractionDigits: 3
        });
    }

    inpQty.addEventListener('input', function() {
        // Permite apenas dígitos e vírgula
        this.value = this.value.replace(/[^\d,]/g, '').replace(/(,.*),/g, '$1');
        atualizarPreview();
    });

    inpCusto.addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '');
        v = (v / 100).toFixed(2).replace('.', ',');
        this.value = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    });

    selMotivo.addEventListener('change', atualizarTipo);
    selProduto.addEventListener('change', atualizarPreview);
</script>