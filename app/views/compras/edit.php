<?php require VIEW_PATH . '/layouts/header.php'; ?>

<style>
    /* ── Editar Compra: estilos de página ── */
    .nc-page-hdr {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }

    .nc-back-btn {
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

    .nc-back-btn:hover {
        background: var(--bg-secondary);
        border-color: var(--color-border, #d1d5db);
        box-shadow: 0 2px 4px rgba(0, 0, 0, .04);
    }

    .nc-back-btn:active {
        transform: scale(.95);
    }

    .nc-badge-draft {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fef3c7;
        margin-left: auto;
        letter-spacing: .3px;
    }

    /* Layout principal */
    .nc-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 24px;
        align-items: start;
    }

    .nc-col-main {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .nc-col-side {
        display: flex;
        flex-direction: column;
        gap: 20px;
        position: sticky;
        top: 100px;
    }

    /* Cards */
    .nc-card {
        background: var(--bg-primary, #fff);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        transition: box-shadow .15s ease, border-color .15s ease;
    }

    .nc-card:hover {
        border-color: var(--color-border, #d1d5db);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
    }

    .nc-card-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--text-tertiary);
        margin: 0 0 18px;
        opacity: .85;
    }

    /* Grid de campos */
    .nc-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .nc-grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 14px;
    }

    .nc-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .nc-field label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        letter-spacing: .3px;
    }

    .nc-field input,
    .nc-field select,
    .nc-field textarea {
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

    .nc-field input:hover,
    .nc-field select:hover,
    .nc-field textarea:hover {
        border-color: var(--color-border, #d1d5db);
        background: var(--bg-primary, #fff);
    }

    .nc-field input:focus,
    .nc-field select:focus,
    .nc-field textarea:focus {
        border-color: var(--color-primary, #2563eb);
        background: var(--bg-primary, #fff);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .06);
    }

    /* Pills de forma de pagamento */
    .nc-pay-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 18px;
    }

    .nc-pill {
        height: 32px;
        padding: 0 14px;
        border-radius: 20px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-primary, #fff);
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all .15s ease;
        font-family: inherit;
    }

    .nc-pill:hover {
        background: var(--bg-secondary);
        border-color: var(--color-border, #d1d5db);
    }

    .nc-pill.active {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #1d4ed8;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(59, 130, 246, .15);
    }

    /* Tabela de itens */
    .nc-items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .nc-items-table th {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: .5px;
        text-align: left;
        padding: 12px 12px;
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-secondary, #f9fafb);
    }

    .nc-items-table td {
        padding: 14px 12px;
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
        font-weight: 600;
        color: var(--text-primary);
    }

    .nc-prod-code {
        font-size: 11px;
        color: var(--text-tertiary);
        margin-top: 3px;
        font-family: 'Courier New', monospace;
    }

    .nc-empty-state {
        text-align: center;
        padding: 48px 24px;
        color: var(--text-tertiary);
    }

    .nc-empty-state i {
        font-size: 32px;
        display: block;
        margin-bottom: 12px;
        opacity: .3;
    }

    .nc-empty-state p {
        font-size: 13px;
        margin: 0;
        opacity: .8;
    }

    /* Botões de ação na linha */
    .nc-row-actions {
        display: flex;
        gap: 4px;
        justify-content: flex-end;
    }

    .nc-btn-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        background: transparent;
        color: var(--text-tertiary);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .12s ease;
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
        gap: 28px;
        padding: 16px 12px 0;
        border-top: 1px solid var(--border-color);
        margin-top: 8px;
    }

    .nc-items-summary span {
        font-size: 12px;
        color: var(--text-tertiary);
    }

    .nc-items-summary strong {
        color: var(--text-primary);
        font-weight: 600;
    }

    .nc-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        padding: 8px 0;
        color: var(--text-tertiary);
    }

    .nc-total-row .val {
        color: var(--text-primary);
        font-weight: 600;
    }

    .nc-total-row.grand {
        padding-top: 14px;
        border-top: 1.5px solid var(--border-color);
        margin-top: 12px;
    }

    .nc-total-row.grand .lbl {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .nc-total-row.grand .val {
        font-size: 22px;
        font-weight: 700;
        color: var(--color-primary);
    }

    /* Modal overlay */
    .nc-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1000;
        background: rgba(0, 0, 0, .45);
        backdrop-filter: blur(2px);
        align-items: center;
        justify-content: center;
        animation: fadeIn .15s ease;
    }

    .nc-overlay.open {
        display: flex;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .nc-modal {
        background: var(--bg-primary, #fff);
        border-radius: 16px;
        width: 520px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px rgba(0, 0, 0, .15), 0 10px 25px rgba(0, 0, 0, .08);
        animation: slideUp .2s ease;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .nc-modal-hdr {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px 24px 0;
    }

    .nc-modal-hdr h3 {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        letter-spacing: -.3px;
    }

    .nc-modal-body {
        padding: 22px 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .nc-modal-foot {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 0 24px 24px;
    }

    /* Subtotal preview no modal */
    .nc-preview-box {
        background: var(--bg-secondary, #f9fafb);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .nc-preview-box .pv-lbl {
        font-size: 12px;
        color: var(--text-tertiary);
        font-weight: 500;
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
                <a href="/compras/<?= (int)$compra['id'] ?>" class="nc-back-btn">
                    <i class="ti ti-arrow-left" style="font-size:16px;"></i>
                </a>
                <div>
                    <h1 style="font-size:26px; font-weight:700; margin:0; letter-spacing:-.4px;">
                        Editar Compra <span style="color:var(--text-tertiary); font-weight:600;"><?= e($compra['numero']) ?></span>
                    </h1>
                    <p style="font-size:13px; color:var(--text-tertiary); margin:4px 0 0;">Preencha os dados do pedido e adicione os produtos</p>
                </div>
                <span class="nc-badge-draft">
                    <i class="ti ti-file-description" style="font-size:11px;"></i> Rascunho
                </span>
            </div>

            <form method="POST" action="/compras/<?= (int)$compra['id'] ?>/editar" id="form-compra">
                <?= csrf_field() ?>

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
                                            <option value="<?= $f['id'] ?>"
                                                <?= (int)$compra['fornecedor_id'] === (int)$f['id'] ? 'selected' : '' ?>>
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
                                            value="<?= e($compra['numero_nf']) ?>">
                                    </div>
                                    <div class="nc-field">
                                        <label>Série NF</label>
                                        <input type="text" name="serie_nf" class="form-control"
                                            placeholder="Ex: 001"
                                            value="<?= e($compra['serie_nf']) ?>">
                                    </div>
                                    <div class="nc-field">
                                        <label>Data de Emissão <span style="color:var(--color-danger,#ef4444);">*</span></label>
                                        <input type="date" name="data_emissao" class="form-control" required
                                            value="<?= e($compra['data_emissao']) ?>">
                                    </div>
                                    <div class="nc-field">
                                        <label>Previsão de Entrega</label>
                                        <input type="date" name="data_entrega" class="form-control"
                                            value="<?= e($compra['data_entrega']) ?>">
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
                                style="width:100%; resize:vertical; min-height:120px;"><?= e($compra['observacao']) ?></textarea>
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
                                $formaSelecionada = $compra['forma_pagamento'] ?? '';
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
                                        value="<?= e($compra['prazo_pagamento']) ?>">
                                </div>
                                <div class="nc-field">
                                    <label>Data de vencimento <span style="color:var(--color-danger,#ef4444);">*</span></label>
                                    <input type="date" name="vencimento" class="form-control"
                                        value="<?= e($compra['vencimento']) ?>" required>
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
                                        value="<?= e($compra['frete'] ?? 0) ?>"
                                        style="width:110px; text-align:right;">
                                </div>

                                <div class="nc-total-row" style="padding:8px 0; align-items:flex-start;">
                                    <label style="font-size:13px; color:var(--text-tertiary); padding-top:8px;">Desconto geral (R$)</label>
                                    <input type="number" name="desconto_valor" id="input-desconto" class="form-control"
                                        step="0.01" min="0" placeholder="0,00"
                                        value="<?= e($compra['desconto_valor'] ?? 0) ?>"
                                        style="width:110px; text-align:right;">
                                </div>

                                <div style="border-top:1.5px solid var(--border-color); padding-top:12px; display:flex; justify-content:space-between; align-items:baseline; margin-top:6px;">
                                    <span style="font-weight:700; font-size:13px; color:var(--text-primary);">Total</span>
                                    <span id="total-final" style="font-size:22px; font-weight:700; color:var(--color-primary);">R$ 0,00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ações -->
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:10px 16px; border-radius:8px; font-weight:700; box-shadow: 0 2px 8px rgba(99, 102, 241, .2); transition: all .15s ease;">
                                <i class="ti ti-device-floppy"></i> Salvar Alterações
                            </button>
                            <a href="/compras/<?= (int)$compra['id'] ?>" class="btn btn-outline" style="width:100%; justify-content:center; padding:10px 16px; border-radius:8px; font-weight:600;">
                                Cancelar
                            </a>
                        </div>

                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ── Modal: Adicionar / Editar Item ── -->
<div id="modal-item" style="
    display:none; position:fixed; inset:0; z-index:1000;
    background:rgba(0,0,0,.45); align-items:center; justify-content:center;">
    <div class="zf-table-card" style="width:520px; padding:28px; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="font-size:16px; font-weight:700; margin:0;" id="modal-titulo">Adicionar Item</h3>
            <button type="button" id="modal-fechar" style="background:none; border:none; cursor:pointer; color:var(--text-tertiary);">
                <i class="ti ti-x" style="font-size:20px;"></i>
            </button>
        </div>

        <div style="display:flex; flex-direction:column; gap:14px;">
            <div>
                <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">
                    Produto <span style="color:var(--color-danger,#ef4444);">*</span>
                </label>
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
                <div style="margin-top:6px;">
                    <button type="button" id="btn-novo-produto" class="btn btn-outline btn-sm">
                        <i class="ti ti-plus"></i> Produto não encontrado? Cadastrar novo
                    </button>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                <div>
                    <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">
                        Quantidade <span style="color:var(--color-danger,#ef4444);">*</span>
                    </label>
                    <input type="number" id="modal-qtd" class="form-control" step="0.001" min="0.001" placeholder="0" value="1">
                </div>
                <div>
                    <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">
                        Preço Unit. (R$) <span style="color:var(--color-danger,#ef4444);">*</span>
                    </label>
                    <input type="number" id="modal-preco" class="form-control" step="0.01" min="0" placeholder="0,00">
                </div>
                <div>
                    <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">
                        Desconto (R$)
                    </label>
                    <input type="number" id="modal-desconto-item" class="form-control" step="0.01" min="0" placeholder="0,00" value="0">
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center;
                background:var(--bg-secondary, #f8fafc); border-radius:8px; padding:12px 16px;">
                <span style="font-size:13px; color:var(--text-tertiary);">Subtotal do item</span>
                <span id="modal-subtotal-preview" style="font-size:18px; font-weight:700;">R$ 0,00</span>
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end; padding-top:4px;">
                <button type="button" id="modal-cancelar" class="btn btn-outline">Cancelar</button>
                <button type="button" id="modal-confirmar" class="btn btn-primary">
                    <i class="ti ti-check"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal: Cadastro Rápido de Produto ── -->
<div id="modal-novo-produto" style="
    display:none; position:fixed; inset:0; z-index:1100;
    background:rgba(0,0,0,.55); align-items:center; justify-content:center;">
    <div class="zf-table-card" style="width:480px; padding:28px; max-height:90vh; overflow-y:auto;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h3 style="font-size:16px; font-weight:700; margin:0;">Novo Produto</h3>
                <span style="font-size:12px; color:var(--text-tertiary);">Cadastro rápido — você pode completar depois</span>
            </div>
            <button type="button" id="mnp-fechar" style="background:none; border:none; cursor:pointer; color:var(--text-tertiary);">
                <i class="ti ti-x" style="font-size:20px;"></i>
            </button>
        </div>

        <div id="mnp-erro" style="display:none;" class="zf-alert zf-alert-danger">
            <i class="ti ti-alert-circle"></i> <span id="mnp-erro-msg"></span>
        </div>

        <div style="display:flex; flex-direction:column; gap:14px;">
            <div>
                <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">
                    Nome <span style="color:var(--color-danger,#ef4444);">*</span>
                </label>
                <input type="text" id="mnp-nome" class="form-control" placeholder="Ex: Camiseta Polo Azul">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">
                        Unidade <span style="color:var(--color-danger,#ef4444);">*</span>
                    </label>
                    <select id="mnp-unidade" class="form-control">
                        <option value="">Selecione...</option>
                        <?php foreach ($unidades as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= e($u['sigla']) ?> — <?= e($u['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">
                        Categoria
                    </label>
                    <select id="mnp-categoria" class="form-control">
                        <option value="">Sem categoria</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">Preço de Custo (R$)</label>
                    <input type="number" id="mnp-custo" class="form-control" step="0.01" min="0" placeholder="0,00">
                </div>
                <div>
                    <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">Preço de Venda (R$)</label>
                    <input type="number" id="mnp-venda" class="form-control" step="0.01" min="0" placeholder="0,00">
                </div>
            </div>

            <div>
                <label style="font-size:12px; color:var(--text-tertiary); display:block; margin-bottom:4px;">Código de Barras</label>
                <input type="text" id="mnp-barras" class="form-control" placeholder="Opcional">
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end; padding-top:4px;">
                <button type="button" id="mnp-cancelar" class="btn btn-outline">Cancelar</button>
                <button type="button" id="mnp-salvar" class="btn btn-primary">
                    <i class="ti ti-device-floppy"></i>
                    <span id="mnp-salvar-txt">Salvar e Selecionar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // ── Estado inicial: itens já salvos no banco ──────────────────────
        // PHP serializa os itens existentes para o JS populá-los na tabela
        let itens = <?= json_encode(array_map(fn($i) => [
                        'produto_id'     => (string)$i['produto_id'],
                        'produto_nome'   => $i['produto_nome'],
                        'produto_codigo' => $i['produto_codigo'],
                        'quantidade'     => (float)$i['quantidade'],
                        'preco_unitario' => (float)$i['preco_unitario'],
                        'desconto_item'  => (float)$i['desconto_item'],
                    ], $itens), JSON_UNESCAPED_UNICODE) ?>;

        let editandoIndex = null;

        // ── Elementos ────────────────────────────────────────────────────
        const tbody = document.getElementById('itens-tbody');
        const linhaVazia = document.getElementById('linha-vazia');
        const resumo = document.getElementById('resumo-itens');
        const modal = document.getElementById('modal-item');
        const btnAdd = document.getElementById('btn-add-item');
        const btnFechar = document.getElementById('modal-fechar');
        const btnCancelar = document.getElementById('modal-cancelar');
        const btnConfirmar = document.getElementById('modal-confirmar');
        const modalTitulo = document.getElementById('modal-titulo');
        const selProduto = document.getElementById('modal-produto');
        const inputQtd = document.getElementById('modal-qtd');
        const inputPreco = document.getElementById('modal-preco');
        const inputDesc = document.getElementById('modal-desconto-item');
        const spanPreview = document.getElementById('modal-subtotal-preview');
        const inputFrete = document.getElementById('input-frete');
        const inputDesconto = document.getElementById('input-desconto');

        // ── Helpers ──────────────────────────────────────────────────────
        const fmt = v => 'R$ ' + parseFloat(v || 0).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        function calcSubtotalItem(qtd, preco, desc) {
            return Math.max(0, qtd * preco - desc);
        }

        function atualizarTotais() {
            let subItens = 0,
                descItens = 0;
            itens.forEach(i => {
                subItens += i.quantidade * i.preco_unitario;
                descItens += i.desconto_item;
            });

            const frete = parseFloat(inputFrete.value) || 0;
            const descomp = parseFloat(inputDesconto.value) || 0;
            const total = subItens + frete - descomp - descItens;

            document.getElementById('resumo-subtotal').textContent = fmt(subItens);
            document.getElementById('resumo-desconto-itens').textContent = '- ' + fmt(descItens);
            document.getElementById('total-subtotal').textContent = fmt(subItens);
            document.getElementById('total-final').textContent = fmt(Math.max(0, total));
        }

        function renderTabela() {
            tbody.querySelectorAll('tr[data-idx]').forEach(r => r.remove());

            if (itens.length === 0) {
                linhaVazia.style.display = '';
                resumo.style.display = 'none';
            } else {
                linhaVazia.style.display = 'none';
                resumo.style.display = '';

                itens.forEach((item, idx) => {
                    const sub = calcSubtotalItem(item.quantidade, item.preco_unitario, item.desconto_item);
                    const tr = document.createElement('tr');
                    tr.dataset.idx = idx;
                    tr.innerHTML = `
                    <td>
                        <div style="font-weight:500;">${item.produto_nome}</div>
                        <div style="font-size:11px; color:var(--text-tertiary);">${item.produto_codigo || ''}</div>
                        <input type="hidden" name="itens[${idx}][produto_id]"     value="${item.produto_id}">
                        <input type="hidden" name="itens[${idx}][produto_nome]"   value="${item.produto_nome}">
                        <input type="hidden" name="itens[${idx}][produto_codigo]" value="${item.produto_codigo}">
                        <input type="hidden" name="itens[${idx}][quantidade]"     value="${item.quantidade}">
                        <input type="hidden" name="itens[${idx}][preco_unitario]" value="${item.preco_unitario}">
                        <input type="hidden" name="itens[${idx}][desconto_item]"  value="${item.desconto_item}">
                    </td>
                    <td style="text-align:center;">${parseFloat(item.quantidade).toLocaleString('pt-BR', {maximumFractionDigits:3})}</td>
                    <td style="text-align:right;">R$ ${parseFloat(item.preco_unitario).toLocaleString('pt-BR', {minimumFractionDigits:2})}</td>
                    <td style="text-align:right; color:var(--color-danger,#ef4444);">${item.desconto_item > 0 ? '- R$ ' + parseFloat(item.desconto_item).toLocaleString('pt-BR',{minimumFractionDigits:2}) : '—'}</td>
                    <td style="text-align:right; font-weight:600;">R$ ${sub.toLocaleString('pt-BR', {minimumFractionDigits:2})}</td>
                    <td>
                        <div style="display:flex; gap:4px;">
                            <button type="button" class="btn btn-outline btn-sm btn-editar" data-idx="${idx}" title="Editar">
                                <i class="ti ti-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-outline btn-sm btn-remover" data-idx="${idx}" title="Remover"
                                style="color:var(--color-danger,#ef4444);">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                    tbody.appendChild(tr);
                });
            }

            atualizarTotais();
        }

        // ── Modal ────────────────────────────────────────────────────────
        function abrirModal(idx = null) {
            editandoIndex = idx;
            if (idx !== null) {
                const item = itens[idx];
                modalTitulo.textContent = 'Editar Item';
                selProduto.value = item.produto_id;
                inputQtd.value = item.quantidade;
                inputPreco.value = item.preco_unitario;
                inputDesc.value = item.desconto_item;
            } else {
                modalTitulo.textContent = 'Adicionar Item';
                selProduto.value = '';
                inputQtd.value = '1';
                inputPreco.value = '';
                inputDesc.value = '0';
            }
            atualizarPreview();
            modal.style.display = 'flex';
        }

        function fecharModal() {
            modal.style.display = 'none';
            editandoIndex = null;
        }

        function atualizarPreview() {
            const qtd = parseFloat(inputQtd.value) || 0;
            const preco = parseFloat(inputPreco.value) || 0;
            const desc = parseFloat(inputDesc.value) || 0;
            spanPreview.textContent = fmt(calcSubtotalItem(qtd, preco, desc));
        }

        selProduto.addEventListener('change', () => {
            const opt = selProduto.selectedOptions[0];
            if (opt && opt.dataset.custo) inputPreco.value = opt.dataset.custo;
            atualizarPreview();
        });

        [inputQtd, inputPreco, inputDesc].forEach(el => el.addEventListener('input', atualizarPreview));

        btnAdd.addEventListener('click', () => abrirModal());
        btnFechar.addEventListener('click', fecharModal);
        btnCancelar.addEventListener('click', fecharModal);
        modal.addEventListener('click', e => {
            if (e.target === modal) fecharModal();
        });

        btnConfirmar.addEventListener('click', () => {
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

            if (editandoIndex !== null) {
                itens[editandoIndex] = item;
            } else {
                itens.push(item);
            }

            fecharModal();
            renderTabela();
        });

        tbody.addEventListener('click', e => {
            const btnEdit = e.target.closest('.btn-editar');
            const btnRem = e.target.closest('.btn-remover');
            if (btnEdit) abrirModal(parseInt(btnEdit.dataset.idx));
            if (btnRem && confirm('Remover este item?')) {
                itens.splice(parseInt(btnRem.dataset.idx), 1);
                renderTabela();
            }
        });

        [inputFrete, inputDesconto].forEach(el => el.addEventListener('input', atualizarTotais));

        // Render inicial com os itens já existentes
        renderTabela();
    })();

    // ── Modal Novo Produto ────────────────────────────────────────────
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

    function abrirModalNovoProduto() {
        mnpNome.value = mnpUnidade.value = mnpCategoria.value = '';
        mnpCusto.value = mnpVenda.value = mnpBarras.value = '';
        mnpErro.style.display = 'none';
        modalNP.style.display = 'flex';
        setTimeout(() => mnpNome.focus(), 50);
    }

    document.getElementById('btn-novo-produto').addEventListener('click', abrirModalNovoProduto);
    document.getElementById('mnp-fechar').addEventListener('click', () => modalNP.style.display = 'none');
    document.getElementById('mnp-cancelar').addEventListener('click', () => modalNP.style.display = 'none');
    modalNP.addEventListener('click', e => {
        if (e.target === modalNP) modalNP.style.display = 'none';
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
                    _csrf_token: document.querySelector('input[name="_csrf_token"]').value,
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
            const sel = document.getElementById('modal-produto');
            const opt = new Option(`${p.codigo} — ${p.nome}`, p.id, true, true);
            opt.dataset.nome = p.nome;
            opt.dataset.codigo = p.codigo;
            opt.dataset.custo = parseFloat(p.preco_custo || 0).toFixed(2);
            sel.appendChild(opt);
            sel.value = p.id;
            sel.dispatchEvent(new Event('change'));

            modalNP.style.display = 'none';

        } catch (err) {
            mnpErroMsg.textContent = err.message || 'Erro de conexão. Tente novamente.';
            mnpErro.style.display = '';
        } finally {
            mnpSalvarTxt.textContent = 'Salvar e Selecionar';
        }
    });
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>