<?php require VIEW_PATH . '/layouts/header.php'; ?>

<style>
    /* ── Movimentação de Estoque: estilos de página ── */
    .mov-page-hdr {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }

    .mov-back-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: var(--bg-primary, #fff);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-tertiary);
        text-decoration: none;
        transition: background .15s ease, border-color .15s ease, box-shadow .15s ease;
        flex-shrink: 0;
    }

    .mov-back-btn:hover {
        background: var(--bg-secondary);
        border-color: var(--color-border, #d1d5db);
        box-shadow: 0 2px 4px rgba(0, 0, 0, .04);
    }

    .mov-back-btn:active {
        transform: scale(.95);
    }

    .mov-card {
        background: var(--bg-primary, #fff);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 28px;
        transition: box-shadow .15s ease, border-color .15s ease;
    }

    .mov-card:hover {
        border-color: var(--color-border, #d1d5db);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
    }

    .mov-field-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .mov-field-group label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        letter-spacing: .3px;
    }

    .mov-field-group input,
    .mov-field-group select {
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-secondary, #f9fafb);
        color: var(--text-primary);
        font-size: 13px;
        outline: none;
        transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
        font-family: inherit;
    }

    .mov-field-group input:hover,
    .mov-field-group select:hover {
        border-color: var(--color-border, #d1d5db);
        background: var(--bg-primary, #fff);
    }

    .mov-field-group input:focus,
    .mov-field-group select:focus {
        border-color: var(--color-primary, #2563eb);
        background: var(--bg-primary, #fff);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .06);
    }

    .mov-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .mov-grid-full {
        grid-column: 1 / -1;
    }

    /* Badges de tipo */
    .mov-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        transition: all .2s ease;
        white-space: nowrap;
    }

    .mov-type-badge.entrada {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #b7e4c7;
    }

    .mov-type-badge.saida {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fcacac;
    }

    .mov-type-badge.ajuste {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .mov-type-badge.default {
        background: #f4f4f5;
        color: #71717a;
        border: 1px solid #e4e4e7;
    }

    /* Stock preview */
    .mov-stock-preview {
        display: none;
        padding: 14px 16px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-secondary, #f9fafb);
    }

    .mov-stock-preview.show {
        display: block;
    }

    /* Divisor */
    .mov-divider {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #9ca3af;
        margin: 8px 0;
    }

    .mov-divider span:first-child,
    .mov-divider span:last-child {
        flex: 1;
        height: 1px;
        background: var(--border-color);
    }
</style>

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

            <!-- Cabeçalho da página -->
            <div class="mov-page-hdr">
                <a href="/estoque" class="mov-back-btn">
                    <i class="ti ti-arrow-left" style="font-size:16px;"></i>
                </a>
                <div>
                    <h1 style="font-size:26px; font-weight:700; margin:0; letter-spacing:-.4px;">
                        Nova Movimentação de Estoque
                    </h1>
                    <p style="font-size:13px; color:var(--text-tertiary); margin:4px 0 0;">Registre entradas, saídas e ajustes</p>
                </div>
            </div>

            <div style="max-width:720px;">
                <div class="mov-card">

                    <form action="/estoque/movimentar" method="POST" id="form-mov">
                        <?= csrf_field() ?>

                        <div class="mov-grid">

                            <!-- Motivo -->
                            <div class="mov-grid-full">
                                <div class="mov-field-group">
                                    <label>
                                        Motivo da Movimentação <span style="color:var(--color-danger,#ef4444);">*</span>
                                    </label>
                                    <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                                        <select name="motivo" id="sel_motivo" required style="flex:1; min-width:240px;">
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
                                        <span id="tipo_badge" class="mov-type-badge default">
                                            <i class="ti ti-help-circle"></i>
                                            <span id="tipo_text">Selecione o motivo</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Produto -->
                            <div class="mov-grid-full">
                                <div class="mov-field-group">
                                    <label>
                                        Produto <span style="color:var(--color-danger,#ef4444);">*</span>
                                    </label>
                                    <select name="produto_id" id="sel_produto" required>
                                        <option value="">— Selecione o produto —</option>
                                        <?php foreach ($produtos as $p): ?>
                                            <option value="<?= (int)$p['id'] ?>"
                                                data-estoque="<?= (float)$p['estoque_atual'] ?>"
                                                data-unidade="<?= e($p['unidade_sigla'] ?? 'UN') ?>">
                                                <?= e($p['nome']) ?>
                                                <?php if (!empty($p['codigo'])): ?>[<?= e($p['codigo']) ?>]<?php endif ?>
                                                — Estoque: <?= number_format($p['estoque_atual'], 3, ',', '.') ?> <?= e($p['unidade_sigla'] ?? 'UN') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Quantidade -->
                            <div class="mov-field-group">
                                <label>
                                    Quantidade <span style="color:var(--color-danger,#ef4444);">*</span>
                                    <span id="qty_hint" style="font-weight:400; font-size:11px; color:var(--text-tertiary); text-transform:none; margin-left:4px;"></span>
                                </label>
                                <input type="text" name="quantidade" id="inp_qty"
                                    placeholder="0,000"
                                    required
                                    autocomplete="off"
                                    style="font-size:16px; font-weight:600; text-align:right;">
                            </div>

                            <!-- Preview estoque -->
                            <div class="mov-field-group">
                                <label>Resultado no Estoque</label>
                                <div id="stock_preview" class="mov-stock-preview">
                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:8px;">
                                        <span id="prev_antes" style="font-size:16px; font-weight:700; color:#6b7280;"></span>
                                        <i class="ti ti-arrow-right" style="color:#d1d5db;"></i>
                                        <span id="prev_depois" style="font-size:16px; font-weight:700;"></span>
                                        <span id="prev_unidade" style="font-size:12px; color:#9CA3AF;"></span>
                                    </div>
                                    <div id="prev_alerta" style="display:none; font-size:12px; color:var(--color-danger,#dc2626);">
                                        <i class="ti ti-alert-triangle"></i> Estoque ficará negativo.
                                    </div>
                                </div>
                            </div>

                            <!-- Observação -->
                            <div class="mov-grid-full">
                                <div class="mov-field-group">
                                    <label>Observação</label>
                                    <input type="text" name="observacao" maxlength="255"
                                        placeholder="Ex: produto vencido lote 0042, reposição semanal...">
                                </div>
                            </div>

                            <!-- ── Campos extras: Compra ── -->
                            <div id="sec_divider" class="mov-grid-full" style="display:none;">
                                <div class="mov-divider">
                                    <span></span>
                                    <span>Dados da Nota Fiscal (opcional)</span>
                                    <span></span>
                                </div>
                            </div>

                            <div id="sec_fornecedor" style="display:none;">
                                <div class="mov-field-group">
                                    <label>Fornecedor</label>
                                    <select name="fornecedor_id">
                                        <option value="">— Nenhum —</option>
                                        <?php foreach ($fornecedores as $f): ?>
                                            <option value="<?= (int)$f['id'] ?>"><?= e($f['razao_social']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div id="sec_nf" style="display:none;">
                                <div class="mov-field-group">
                                    <label>Número da NF</label>
                                    <input type="text" name="numero_nf"
                                        placeholder="000000" maxlength="20">
                                </div>
                            </div>

                            <div id="sec_custo" style="display:none;">
                                <div class="mov-field-group">
                                    <label>Custo Unitário (R$)</label>
                                    <input type="text" name="preco_custo_unitario" id="inp_custo"
                                        placeholder="0,00" autocomplete="off">
                                </div>
                            </div>

                        </div><!-- /mov-grid -->

                        <div style="display:flex; gap:12px; margin-top:32px;">
                            <button type="submit" class="btn btn-primary" style="display:flex; align-items:center; gap:8px;">
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
            cssClass: 'entrada',
            icon: 'ti-arrow-down-circle',
            label: 'Entrada no Estoque'
        },
        SAIDA: {
            cssClass: 'saida',
            icon: 'ti-arrow-up-circle',
            label: 'Saída do Estoque'
        },
        AJUSTE: {
            cssClass: 'ajuste',
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
            tipoBadge.className = 'mov-type-badge ' + cfg.cssClass;
            tipoBadge.innerHTML = `<i class="ti ${cfg.icon}"></i><span>${cfg.label}</span>`;
        } else {
            tipoBadge.className = 'mov-type-badge default';
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
            stockPreview.classList.remove('show');
            return;
        }

        stockPreview.classList.add('show');

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