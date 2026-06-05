<?php require VIEW_PATH . '/layouts/header.php'; ?>

<style>
    /* ── Nova Compra: estilos de página ── */
    .nc-page-hdr {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
    }

    .nc-back-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: var(--bg-primary, #fff);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-tertiary);
        text-decoration: none;
        transition: background .15s;
        flex-shrink: 0;
    }

    .nc-back-btn:hover {
        background: var(--bg-secondary);
    }

    .nc-badge-draft {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 500;
        padding: 3px 10px;
        border-radius: 20px;
        background: var(--bg-secondary, #f3f4f6);
        color: var(--text-tertiary);
        margin-left: auto;
    }

    /* Layout principal */
    .nc-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 20px;
        align-items: start;
    }

    .nc-col-main {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .nc-col-side {
        display: flex;
        flex-direction: column;
        gap: 16px;
        position: sticky;
        top: 16px;
    }

    /* Cards */
    .nc-card {
        background: var(--bg-primary, #fff);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 20px;
    }

    .nc-card-title {
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: var(--text-tertiary);
        margin: 0 0 16px;
    }

    /* Grid de campos */
    .nc-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .nc-grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
    }

    .nc-field {
        display: flex;
        flex-direction: column;
    }

    .nc-field label {
        font-size: 12px;
        color: var(--text-tertiary);
        margin-bottom: 5px;
    }

    /* Pills de forma de pagamento */
    .nc-pay-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 14px;
    }

    .nc-pill {
        height: 28px;
        padding: 0 12px;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        background: var(--bg-primary, #fff);
        color: var(--text-tertiary);
        font-size: 12px;
        cursor: pointer;
        transition: all .12s;
        font-family: inherit;
    }

    .nc-pill:hover {
        background: var(--bg-secondary);
    }

    .nc-pill.active {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #1d4ed8;
        font-weight: 500;
    }

    /* Tabela de itens */
    .nc-items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .nc-items-table th {
        font-size: 11px;
        font-weight: 500;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: .4px;
        text-align: left;
        padding: 8px 10px;
        border-bottom: 1px solid var(--border-color);
    }

    .nc-items-table td {
        padding: 10px;
        border-bottom: 1px solid var(--border-color);
        font-size: 13px;
        color: var(--text-primary);
        vertical-align: middle;
    }

    .nc-items-table tbody tr:last-child td {
        border-bottom: none;
    }

    .nc-items-table tbody tr:hover td {
        background: var(--bg-secondary);
    }

    .nc-prod-name {
        font-weight: 500;
    }

    .nc-prod-code {
        font-size: 11px;
        color: var(--text-tertiary);
        margin-top: 1px;
    }

    .nc-empty-state {
        text-align: center;
        padding: 36px 20px;
        color: var(--text-tertiary);
    }

    .nc-empty-state i {
        font-size: 28px;
        display: block;
        margin-bottom: 8px;
    }

    .nc-empty-state p {
        font-size: 13px;
    }

    /* Botões de ação na linha */
    .nc-row-actions {
        display: flex;
        gap: 4px;
        justify-content: flex-end;
    }

    .nc-btn-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        border: none;
        background: transparent;
        color: var(--text-tertiary);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .12s;
    }

    .nc-btn-icon:hover {
        background: var(--bg-secondary);
        color: var(--text-primary);
    }

    .nc-btn-icon.danger:hover {
        background: #fef2f2;
        color: #dc2626;
    }

    /* Resumo de itens */
    .nc-items-summary {
        display: flex;
        justify-content: flex-end;
        gap: 24px;
        padding: 12px 10px 0;
        border-top: 1px solid var(--border-color);
        margin-top: 4px;
    }

    .nc-items-summary span {
        font-size: 12px;
        color: var(--text-tertiary);
    }

    .nc-items-summary strong {
        color: var(--text-primary);
        font-weight: 500;
    }

    /* Totais laterais */
    .nc-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        padding: 4px 0;
        color: var(--text-tertiary);
    }

    .nc-total-row .val {
        color: var(--text-primary);
        font-weight: 500;
    }

    .nc-total-row.grand {
        padding-top: 12px;
        border-top: 1px solid var(--border-color);
        margin-top: 8px;
    }

    .nc-total-row.grand .lbl {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .nc-total-row.grand .val {
        font-size: 20px;
        font-weight: 700;
        color: var(--color-primary);
    }

    .nc-inline-input {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nc-inline-input label {
        white-space: nowrap;
        margin: 0;
    }

    .nc-inline-input input {
        width: 110px;
        text-align: right;
    }

    /* Modal overlay */
    .nc-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1000;
        background: rgba(0, 0, 0, .4);
        align-items: center;
        justify-content: center;
    }

    .nc-overlay.open {
        display: flex;
    }

    .nc-modal {
        background: var(--bg-primary, #fff);
        border-radius: 14px;
        width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .15);
    }

    .nc-modal-hdr {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 22px 0;
    }

    .nc-modal-hdr h3 {
        font-size: 15px;
        font-weight: 600;
        margin: 0;
    }

    .nc-modal-body {
        padding: 20px 22px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .nc-modal-foot {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 0 22px 20px;
    }

    /* Subtotal preview no modal */
    .nc-preview-box {
        background: var(--bg-secondary, #f9fafb);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 12px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .nc-preview-box .pv-lbl {
        font-size: 12px;
        color: var(--text-tertiary);
    }

    .nc-preview-box .pv-val {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
    }

    /* Hint de novo produto */
    .nc-new-prod-hint {
        font-size: 11px;
        color: var(--color-primary, #2563eb);
        cursor: pointer;
        margin-top: 5px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background: none;
        border: none;
        padding: 0;
        font-family: inherit;
    }

    .nc-new-prod-hint:hover {
        text-decoration: underline;
    }

    /* Modal Novo Produto — z-index maior */
    .nc-overlay-np {
        z-index: 1100;
    }
</style>

<div class="zf-layout">

    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>

    <div class="zf-main">

        <?php require VIEW_PATH . '/layouts/navbar.php'; ?>

        <div class="zf-content">

            <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close>
                    <i class="ti ti-alert-circle"></i> <?= e($msg) ?>
                </div>
            <?php endif; ?>

            <!-- Cabeçalho da página -->
            <div class="nc-page-hdr">
                <a href="/compras" class="nc-back-btn">
                    <i class="ti ti-arrow-left" style="font-size:16px;"></i>
                </a>
                <div>
                    <h1 style="font-size:18px; font-weight:600; margin:0;">Nova Compra</h1>
                    <p style="font-size:12px; color:var(--text-tertiary); margin:2px 0 0;">Preencha os dados do pedido e adicione os produtos</p>
                </div>
                <span class="nc-badge-draft">
                    <i class="ti ti-file-description" style="font-size:11px;"></i> Rascunho
                </span>
            </div>

            <form method="POST" action="/compras/criar" id="form-compra">
                <?= csrf_field() ?>
                <!-- Hidden: forma_pagamento controlada pelos pills -->
                <input type="hidden" name="forma_pagamento" id="input-forma-pagamento" value="<?= e(old('forma_pagamento')) ?>">

                <div class="nc-grid">

                    <!-- ── Coluna principal ── -->
                    <div class="nc-col-main">

                        <!-- Dados da compra -->
                        <div class="nc-card">
                            <p class="nc-card-title">Dados da compra</p>

                            <div style="display:flex; flex-direction:column; gap:14px;">
                                <div class="nc-field">
                                    <label>Fornecedor <span style="color:var(--color-danger,#ef4444);">*</span></label>
                                    <select name="fornecedor_id" class="form-control" required>
                                        <option value="">Selecione um fornecedor...</option>
                                        <?php foreach ($fornecedores as $f): ?>
                                            <option value="<?= $f['id'] ?>" <?= old('fornecedor_id') == $f['id'] ? 'selected' : '' ?>>
                                                <?= e($f['razao_social']) ?>
                                                <?php if ($f['nome_fantasia']): ?>— <?= e($f['nome_fantasia']) ?><?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="nc-grid-2">
                                    <div class="nc-field">
                                        <label>Nº Nota Fiscal</label>
                                        <input type="text" name="numero_nf" class="form-control"
                                            placeholder="Ex: 000123"
                                            value="<?= e(old('numero_nf')) ?>">
                                    </div>
                                    <div class="nc-field">
                                        <label>Série NF</label>
                                        <input type="text" name="serie_nf" class="form-control"
                                            placeholder="Ex: 001"
                                            value="<?= e(old('serie_nf')) ?>">
                                    </div>
                                    <div class="nc-field">
                                        <label>Data de Emissão <span style="color:var(--color-danger,#ef4444);">*</span></label>
                                        <input type="date" name="data_emissao" class="form-control" required
                                            value="<?= e(old('data_emissao', date('Y-m-d'))) ?>">
                                    </div>
                                    <div class="nc-field">
                                        <label>Previsão de Entrega</label>
                                        <input type="date" name="data_entrega" class="form-control"
                                            value="<?= e(old('data_entrega')) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Itens da compra -->
                        <div class="nc-card">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                                <p class="nc-card-title" style="margin:0;">Itens da compra</p>
                                <button type="button" class="btn btn-outline btn-sm" id="btn-add-item">
                                    <i class="ti ti-plus"></i> Adicionar item
                                </button>
                            </div>

                            <table class="nc-items-table" id="tabela-itens">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th style="width:70px; text-align:center;">Qtd</th>
                                        <th style="width:120px; text-align:right;">Preço unit.</th>
                                        <th style="width:110px; text-align:right;">Desconto</th>
                                        <th style="width:110px; text-align:right;">Subtotal</th>
                                        <th style="width:60px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="itens-tbody">
                                    <tr id="linha-vazia">
                                        <td colspan="6">
                                            <div class="nc-empty-state">
                                                <i class="ti ti-package-off"></i>
                                                <p>Nenhum item adicionado.<br>Clique em <strong>Adicionar item</strong> para começar.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div id="resumo-itens" class="nc-items-summary" style="display:none;">
                                <span>Subtotal itens: <strong id="resumo-subtotal">R$ 0,00</strong></span>
                                <span style="color:var(--color-danger,#dc2626);">
                                    Desconto itens: <strong id="resumo-desconto-itens">- R$ 0,00</strong>
                                </span>
                            </div>
                        </div>

                        <!-- Observação -->
                        <div class="nc-card" style="grid-column: 1 / -1;">
                            <p class="nc-card-title">Observações internas</p>
                            <textarea
                                name="observacao"
                                class="form-control"
                                rows="4"
                                placeholder="Informações adicionais sobre este pedido..."
                                style="width:100%; resize:vertical; min-height:120px;"><?= e(old('observacao')) ?></textarea>
                        </div>

                    </div>

                    <!-- ── Coluna lateral ── -->
                    <div class="nc-col-side">

                        <!-- Pagamento -->
                        <div class="nc-card">
                            <p class="nc-card-title">Pagamento</p>

                            <div class="nc-pay-pills" id="pay-pills">
                                <?php
                                $formas = [
                                    'boleto'       => 'Boleto',
                                    'pix'          => 'PIX',
                                    'dinheiro'     => 'Dinheiro',
                                    'cartao'       => 'Cartão',
                                    'cheque'       => 'Cheque',
                                    'transferencia' => 'Transferência',
                                    'prazo'        => 'A Prazo',
                                ];
                                $formaSelecionada = old('forma_pagamento', '');
                                foreach ($formas as $valor => $label):
                                ?>
                                    <button type="button"
                                        class="nc-pill <?= $formaSelecionada === $valor ? 'active' : '' ?>"
                                        data-value="<?= $valor ?>">
                                        <?= $label ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div class="nc-field">
                                    <label>Prazo (dias)</label>
                                    <input type="number" name="prazo_pagamento" class="form-control"
                                        placeholder="Ex: 30" min="0"
                                        value="<?= e(old('prazo_pagamento')) ?>">
                                </div>
                                <div class="nc-field">
                                    <label>Data de vencimento <span style="color:var(--color-danger,#ef4444);">*</span></label>
                                    <input type="date" name="vencimento" class="form-control"
                                        value="<?= e(old('vencimento')) ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Totais -->
                        <div class="nc-card">
                            <p class="nc-card-title">Resumo financeiro</p>

                            <div>
                                <div class="nc-total-row">
                                    <span class="lbl">Subtotal dos itens</span>
                                    <span class="val" id="total-subtotal">R$ 0,00</span>
                                </div>

                                <div class="nc-total-row" style="padding:8px 0; align-items:flex-start;">
                                    <label style="font-size:13px; color:var(--text-tertiary); padding-top:8px;">Frete (R$)</label>
                                    <input type="number" name="frete" id="input-frete" class="form-control"
                                        step="0.01" min="0" placeholder="0,00"
                                        value="<?= e(old('frete', '0')) ?>"
                                        style="width:110px; text-align:right;">
                                </div>

                                <div class="nc-total-row" style="padding:8px 0; align-items:flex-start;">
                                    <label style="font-size:13px; color:var(--text-tertiary); padding-top:8px;">Desconto geral (R$)</label>
                                    <input type="number" name="desconto_valor" id="input-desconto" class="form-control"
                                        step="0.01" min="0" placeholder="0,00"
                                        value="<?= e(old('desconto_valor', '0')) ?>"
                                        style="width:110px; text-align:right;">
                                </div>

                                <div class="nc-total-row grand">
                                    <span class="lbl">Total</span>
                                    <span class="val" id="total-final">R$ 0,00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ações -->
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <button type="submit" name="acao" value="salvar"
                                class="btn btn-primary"
                                style="width:100%; justify-content:center; height:40px; font-size:14px;">
                                <i class="ti ti-device-floppy"></i> Salvar rascunho
                            </button>
                            <a href="/compras" class="btn btn-outline"
                                style="width:100%; justify-content:center; height:38px; font-size:13px; color:var(--text-tertiary);">
                                Cancelar
                            </a>
                        </div>

                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ─── Modal: Adicionar / Editar Item ─── -->
<div class="nc-overlay" id="modal-item">
    <div class="nc-modal">
        <div class="nc-modal-hdr">
            <h3 id="modal-titulo">Adicionar item</h3>
            <button type="button" id="modal-fechar" class="nc-btn-icon">
                <i class="ti ti-x" style="font-size:16px;"></i>
            </button>
        </div>

        <div class="nc-modal-body">
            <div class="nc-field">
                <label>Produto <span style="color:var(--color-danger,#ef4444);">*</span></label>
                <select id="modal-produto" class="form-control">
                    <option value="">Selecione um produto...</option>
                    <?php foreach ($produtos as $p): ?>
                        <option value="<?= $p['id'] ?>"
                            data-nome="<?= e($p['nome']) ?>"
                            data-codigo="<?= e($p['codigo']) ?>"
                            data-custo="<?= number_format($p['preco_custo'] ?? 0, 2, '.', '') ?>">
                            <?= e($p['codigo']) ?> — <?= e($p['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="btn-novo-produto" class="nc-new-prod-hint">
                    <i class="ti ti-plus" style="font-size:11px;"></i> Produto não encontrado? Cadastrar novo
                </button>
            </div>

            <div class="nc-grid-3">
                <div class="nc-field">
                    <label>Quantidade <span style="color:var(--color-danger,#ef4444);">*</span></label>
                    <input type="number" id="modal-qtd" class="form-control"
                        step="0.001" min="0.001" value="1">
                </div>
                <div class="nc-field">
                    <label>Preço unit. (R$) <span style="color:var(--color-danger,#ef4444);">*</span></label>
                    <input type="number" id="modal-preco" class="form-control"
                        step="0.01" min="0" placeholder="0,00">
                </div>
                <div class="nc-field">
                    <label>Desconto (R$)</label>
                    <input type="number" id="modal-desconto-item" class="form-control"
                        step="0.01" min="0" placeholder="0,00" value="0">
                </div>
            </div>

            <div class="nc-preview-box">
                <span class="pv-lbl">Subtotal do item</span>
                <span class="pv-val" id="modal-subtotal-preview">R$ 0,00</span>
            </div>
        </div>

        <div class="nc-modal-foot">
            <button type="button" id="modal-cancelar" class="btn btn-outline">Cancelar</button>
            <button type="button" id="modal-confirmar" class="btn btn-primary">
                <i class="ti ti-check"></i> Confirmar item
            </button>
        </div>
    </div>
</div>

<!-- ─── Modal: Cadastro Rápido de Produto ─── -->
<div class="nc-overlay nc-overlay-np" id="modal-novo-produto">
    <div class="nc-modal" style="width:480px;">
        <div class="nc-modal-hdr">
            <div>
                <h3>Novo Produto</h3>
                <p style="font-size:12px; color:var(--text-tertiary); margin:2px 0 0;">Cadastro rápido — você pode completar depois</p>
            </div>
            <button type="button" id="mnp-fechar" class="nc-btn-icon">
                <i class="ti ti-x" style="font-size:16px;"></i>
            </button>
        </div>

        <div class="nc-modal-body">

            <div id="mnp-erro" style="display:none;" class="zf-alert zf-alert-danger">
                <i class="ti ti-alert-circle"></i> <span id="mnp-erro-msg"></span>
            </div>

            <div class="nc-field">
                <label>Nome <span style="color:var(--color-danger,#ef4444);">*</span></label>
                <input type="text" id="mnp-nome" class="form-control" placeholder="Ex: Camiseta Polo Azul">
            </div>

            <div class="nc-grid-2">
                <div class="nc-field">
                    <label>Unidade <span style="color:var(--color-danger,#ef4444);">*</span></label>
                    <select id="mnp-unidade" class="form-control">
                        <option value="">Selecione...</option>
                        <?php foreach ($unidades as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= e($u['sigla']) ?> — <?= e($u['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="nc-field">
                    <label>Categoria</label>
                    <select id="mnp-categoria" class="form-control">
                        <option value="">Sem categoria</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="nc-grid-2">
                <div class="nc-field">
                    <label>Preço de Custo (R$)</label>
                    <input type="number" id="mnp-custo" class="form-control" step="0.01" min="0" placeholder="0,00">
                </div>
                <div class="nc-field">
                    <label>Preço de Venda (R$)</label>
                    <input type="number" id="mnp-venda" class="form-control" step="0.01" min="0" placeholder="0,00">
                </div>
            </div>

            <div class="nc-field">
                <label>Código de Barras</label>
                <input type="text" id="mnp-barras" class="form-control" placeholder="Opcional">
            </div>

        </div>

        <div class="nc-modal-foot">
            <button type="button" id="mnp-cancelar" class="btn btn-outline">Cancelar</button>
            <button type="button" id="mnp-salvar" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i>
                <span id="mnp-salvar-txt">Salvar e Selecionar</span>
            </button>
        </div>
    </div>
</div>

<script>
    (function() {
        // ─── Estado ───────────────────────────────────────────────────────────────
        let itens = [];
        let editandoIndex = null;

        // ─── Elementos ────────────────────────────────────────────────────────────
        const tbody = document.getElementById('itens-tbody');
        const linhaVazia = document.getElementById('linha-vazia');
        const resumo = document.getElementById('resumo-itens');
        const modal = document.getElementById('modal-item');
        const btnAdd = document.getElementById('btn-add-item');
        const modalTitulo = document.getElementById('modal-titulo');
        const selProduto = document.getElementById('modal-produto');
        const inputQtd = document.getElementById('modal-qtd');
        const inputPreco = document.getElementById('modal-preco');
        const inputDesc = document.getElementById('modal-desconto-item');
        const spanPreview = document.getElementById('modal-subtotal-preview');
        const inputFrete = document.getElementById('input-frete');
        const inputDesconto = document.getElementById('input-desconto');

        // ─── Helpers ──────────────────────────────────────────────────────────────
        const fmt = v => 'R$ ' + Math.max(0, parseFloat(v) || 0).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        function calcSubtotal(qtd, preco, desc) {
            return Math.max(0, qtd * preco - desc);
        }

        // ─── Totais ───────────────────────────────────────────────────────────────
        function atualizarTotais() {
            let subItens = 0,
                descItens = 0;
            itens.forEach(i => {
                subItens += i.quantidade * i.preco_unitario;
                descItens += i.desconto_item;
            });
            const frete = parseFloat(inputFrete.value) || 0;
            const descomp = parseFloat(inputDesconto.value) || 0;
            const total = Math.max(0, subItens + frete - descItens - descomp);

            document.getElementById('resumo-subtotal').textContent = fmt(subItens);
            document.getElementById('resumo-desconto-itens').textContent = '- ' + fmt(descItens);
            document.getElementById('total-subtotal').textContent = fmt(subItens);
            document.getElementById('total-final').textContent = fmt(total);
        }

        // ─── Renderiza tabela ─────────────────────────────────────────────────────
        function renderTabela() {
            tbody.querySelectorAll('tr[data-idx]').forEach(r => r.remove());

            if (itens.length === 0) {
                linhaVazia.style.display = '';
                resumo.style.display = 'none';
            } else {
                linhaVazia.style.display = 'none';
                resumo.style.display = 'flex';

                itens.forEach((item, idx) => {
                    const sub = calcSubtotal(item.quantidade, item.preco_unitario, item.desconto_item);
                    const tr = document.createElement('tr');
                    tr.dataset.idx = idx;
                    tr.innerHTML = `
                    <td>
                        <div class="nc-prod-name">${item.produto_nome}</div>
                        <div class="nc-prod-code">${item.produto_codigo || ''}</div>
                        <input type="hidden" name="itens[${idx}][produto_id]"     value="${item.produto_id}">
                        <input type="hidden" name="itens[${idx}][produto_nome]"   value="${item.produto_nome}">
                        <input type="hidden" name="itens[${idx}][produto_codigo]" value="${item.produto_codigo}">
                        <input type="hidden" name="itens[${idx}][quantidade]"     value="${item.quantidade}">
                        <input type="hidden" name="itens[${idx}][preco_unitario]" value="${item.preco_unitario}">
                        <input type="hidden" name="itens[${idx}][desconto_item]"  value="${item.desconto_item}">
                    </td>
                    <td style="text-align:center;">
                        ${parseFloat(item.quantidade).toLocaleString('pt-BR', { maximumFractionDigits: 3 })}
                    </td>
                    <td style="text-align:right;">${fmt(item.preco_unitario)}</td>
                    <td style="text-align:right; color:var(--color-danger,#dc2626);">
                        ${item.desconto_item > 0 ? '- ' + fmt(item.desconto_item) : '—'}
                    </td>
                    <td style="text-align:right; font-weight:600;">${fmt(sub)}</td>
                    <td>
                        <div class="nc-row-actions">
                            <button type="button" class="nc-btn-icon btn-editar" data-idx="${idx}" title="Editar">
                                <i class="ti ti-pencil" style="font-size:14px;"></i>
                            </button>
                            <button type="button" class="nc-btn-icon danger btn-remover" data-idx="${idx}" title="Remover">
                                <i class="ti ti-trash" style="font-size:14px;"></i>
                            </button>
                        </div>
                    </td>
                `;
                    tbody.appendChild(tr);
                });
            }

            atualizarTotais();
        }

        // ─── Modal de item ────────────────────────────────────────────────────────
        function abrirModal(idx = null) {
            editandoIndex = idx;

            if (idx !== null) {
                const item = itens[idx];
                modalTitulo.textContent = 'Editar item';
                selProduto.value = item.produto_id;
                inputQtd.value = item.quantidade;
                inputPreco.value = item.preco_unitario;
                inputDesc.value = item.desconto_item;
            } else {
                modalTitulo.textContent = 'Adicionar item';
                selProduto.value = '';
                inputQtd.value = '1';
                inputPreco.value = '';
                inputDesc.value = '0';
            }

            atualizarPreview();
            modal.classList.add('open');
        }

        function fecharModal() {
            modal.classList.remove('open');
            editandoIndex = null;
        }

        function atualizarPreview() {
            const qtd = parseFloat(inputQtd.value) || 0;
            const preco = parseFloat(inputPreco.value) || 0;
            const desc = parseFloat(inputDesc.value) || 0;
            spanPreview.textContent = fmt(calcSubtotal(qtd, preco, desc));
        }

        // Preenche preço de custo ao selecionar produto
        selProduto.addEventListener('change', () => {
            const opt = selProduto.selectedOptions[0];
            if (opt && opt.dataset.custo) inputPreco.value = opt.dataset.custo;
            atualizarPreview();
        });

        [inputQtd, inputPreco, inputDesc].forEach(el => el.addEventListener('input', atualizarPreview));

        btnAdd.addEventListener('click', () => abrirModal());
        document.getElementById('modal-fechar').addEventListener('click', fecharModal);
        document.getElementById('modal-cancelar').addEventListener('click', fecharModal);
        modal.addEventListener('click', e => {
            if (e.target === modal) fecharModal();
        });

        document.getElementById('modal-confirmar').addEventListener('click', () => {
            const prodId = selProduto.value;
            const opt = selProduto.selectedOptions[0];
            const qtd = parseFloat(inputQtd.value);
            const preco = parseFloat(inputPreco.value);
            const desc = parseFloat(inputDesc.value) || 0;

            if (!prodId || !qtd || !preco) {
                alert('Preencha produto, quantidade e preço.');
                return;
            }

            const item = {
                produto_id: prodId,
                produto_nome: opt.dataset.nome || opt.text,
                produto_codigo: opt.dataset.codigo || '',
                quantidade: qtd,
                preco_unitario: preco,
                desconto_item: desc,
            };

            if (editandoIndex !== null) itens[editandoIndex] = item;
            else itens.push(item);

            fecharModal();
            renderTabela();
        });

        // ─── Delegação: editar / remover ──────────────────────────────────────────
        tbody.addEventListener('click', e => {
            const btnEdit = e.target.closest('.btn-editar');
            const btnRem = e.target.closest('.btn-remover');
            if (btnEdit) abrirModal(parseInt(btnEdit.dataset.idx));
            if (btnRem && confirm('Remover este item?')) {
                itens.splice(parseInt(btnRem.dataset.idx), 1);
                renderTabela();
            }
        });

        // ─── Frete / desconto recalculam total ────────────────────────────────────
        [inputFrete, inputDesconto].forEach(el => el.addEventListener('input', atualizarTotais));

        renderTabela();
    })();

    // ─── Pills de Forma de Pagamento ──────────────────────────────────────────────
    (function() {
        const pills = document.querySelectorAll('#pay-pills .nc-pill');
        const hidden = document.getElementById('input-forma-pagamento');

        pills.forEach(pill => {
            pill.addEventListener('click', () => {
                // Toggle: clicar no ativo desseleciona
                if (pill.classList.contains('active')) {
                    pill.classList.remove('active');
                    hidden.value = '';
                } else {
                    pills.forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                    hidden.value = pill.dataset.value;
                }
            });
        });
    })();

    // ─── Modal Novo Produto ───────────────────────────────────────────────────────
    (function() {
        const modalNP = document.getElementById('modal-novo-produto');
        const mnpNome = document.getElementById('mnp-nome');
        const mnpUnidade = document.getElementById('mnp-unidade');
        const mnpCategoria = document.getElementById('mnp-categoria');
        const mnpCusto = document.getElementById('mnp-custo');
        const mnpVenda = document.getElementById('mnp-venda');
        const mnpBarras = document.getElementById('mnp-barras');
        const mnpErro = document.getElementById('mnp-erro');
        const mnpErroMsg = document.getElementById('mnp-erro-msg');
        const mnpSalvarTxt = document.getElementById('mnp-salvar-txt');

        function abrir() {
            mnpNome.value = mnpUnidade.value = mnpCategoria.value = '';
            mnpCusto.value = mnpVenda.value = mnpBarras.value = '';
            mnpErro.style.display = 'none';
            modalNP.classList.add('open');
            setTimeout(() => mnpNome.focus(), 50);
        }

        function fechar() {
            modalNP.classList.remove('open');
        }

        document.getElementById('btn-novo-produto').addEventListener('click', abrir);
        document.getElementById('mnp-fechar').addEventListener('click', fechar);
        document.getElementById('mnp-cancelar').addEventListener('click', fechar);
        modalNP.addEventListener('click', e => {
            if (e.target === modalNP) fechar();
        });

        document.getElementById('mnp-salvar').addEventListener('click', async () => {
            mnpErro.style.display = 'none';
            mnpSalvarTxt.textContent = 'Salvando...';

            try {
                const res = await fetch('/produtos/rapido', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        _token: document.querySelector('input[name="_token"]').value,
                        nome: mnpNome.value.trim(),
                        unidade_id: mnpUnidade.value,
                        categoria_id: mnpCategoria.value,
                        preco_custo: mnpCusto.value || '0',
                        preco_venda: mnpVenda.value || '0',
                        codigo_barras: mnpBarras.value.trim(),
                    })
                });

                const json = await res.json();

                if (!json.success) {
                    mnpErroMsg.textContent = json.message || 'Erro ao cadastrar produto.';
                    mnpErro.style.display = '';
                    return;
                }

                const p = json.produto;
                const selProduto = document.getElementById('modal-produto');

                if (!selProduto) throw new Error('Select de produtos não encontrado.');

                const opt = new Option(`${p.codigo} — ${p.nome}`, p.id, true, true);
                opt.dataset.nome = p.nome;
                opt.dataset.codigo = p.codigo;
                opt.dataset.custo = parseFloat(p.preco_custo || 0).toFixed(2);
                opt.dataset.preco = parseFloat(p.preco_venda || 0).toFixed(2);

                selProduto.appendChild(opt);
                selProduto.value = p.id;
                selProduto.dispatchEvent(new Event('change'));

                fechar();

            } catch (err) {
                mnpErroMsg.textContent = err.message || 'Erro de conexão. Tente novamente.';
                mnpErro.style.display = '';
            } finally {
                mnpSalvarTxt.textContent = 'Salvar e Selecionar';
            }
        });
    })();
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>