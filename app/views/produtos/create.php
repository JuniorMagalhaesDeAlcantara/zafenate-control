<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">

        <?php
        $isEdit     = !empty($produto);
        $pageTitle  = $isEdit ? 'Editar Produto' : 'Novo Produto';
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Produtos',  'url' => '/produtos'],
            ['label' => $isEdit ? 'Editar' : 'Novo', 'url' => '#'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
                <div class="zf-alert zf-alert-success" data-auto-close>
                    <i class="ti ti-circle-check"></i> <?= e($msg) ?>
                </div>
            <?php endif; ?>
            <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close>
                    <i class="ti ti-alert-circle"></i> <?= e($msg) ?>
                </div>
            <?php endif; ?>

            <form
                action="<?= $isEdit ? '/produtos/' . $produto['id'] . '/editar' : '/produtos/criar' ?>"
                method="POST"
                id="form-produto">

                <?= $csrf ?? '' ?>

                <div class="pf-layout">

                    <!-- ══════════════════════════════════════
                         COLUNA PRINCIPAL
                    ══════════════════════════════════════ -->
                    <div class="pf-main">

                        <!-- Identificação -->
                        <div class="pf-card">
                            <div class="pf-card-header">
                                <span class="pf-card-icon"><i class="ti ti-package"></i></span>
                                <span class="pf-card-title">Identificação</span>
                            </div>
                            <div class="pf-card-body">

                                <div class="pf-field pf-field-full">
                                    <label class="pf-label">Nome do Produto <span class="pf-req">*</span></label>
                                    <input type="text" name="nome" class="pf-input"
                                        placeholder="Ex: Coca-Cola Lata 350ml"
                                        value="<?= e($produto['nome'] ?? '') ?>"
                                        required autofocus>
                                </div>

                                <div class="pf-row">
                                    <div class="pf-field">
                                        <label class="pf-label">Código Interno</label>
                                        <div class="pf-input-locked">
                                            <i class="ti ti-lock"></i>
                                            <span><?= e($codigo ?? 'Gerado automaticamente') ?></span>
                                        </div>
                                    </div>
                                    <div class="pf-field">
                                        <label class="pf-label">Código de Barras / EAN</label>
                                        <div class="pf-input-wrap">
                                            <i class="ti ti-barcode pf-input-icon"></i>
                                            <input type="text" name="codigo_barras" class="pf-input pf-input-icon-pad"
                                                placeholder="Bipe ou digite"
                                                value="<?= e($produto['codigo_barras'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="pf-field pf-field-full">
                                    <label class="pf-label">Descrição / Observações</label>
                                    <textarea name="descricao" class="pf-input pf-textarea"
                                        placeholder="Informações adicionais sobre o produto..."
                                        rows="3"><?= e($produto['descricao'] ?? '') ?></textarea>
                                </div>

                            </div>
                        </div>

                        <!-- Preços -->
                        <div class="pf-card">
                            <div class="pf-card-header">
                                <span class="pf-card-icon"><i class="ti ti-currency-dollar"></i></span>
                                <span class="pf-card-title">Preços</span>
                            </div>
                            <div class="pf-card-body">

                                <div class="pf-row">
                                    <div class="pf-field">
                                        <label class="pf-label">Preço de Custo (R$) <span class="pf-req">*</span></label>
                                        <div class="pf-input-wrap">
                                            <span class="pf-input-prefix">R$</span>
                                            <input type="number" step="0.01" min="0" name="preco_custo"
                                                class="pf-input pf-input-prefix-pad pf-money"
                                                placeholder="0,00"
                                                value="<?= e($produto['preco_custo'] ?? '0.00') ?>"
                                                required>
                                        </div>
                                    </div>
                                    <div class="pf-field">
                                        <label class="pf-label">Preço de Venda (R$) <span class="pf-req">*</span></label>
                                        <div class="pf-input-wrap">
                                            <span class="pf-input-prefix">R$</span>
                                            <input type="number" step="0.01" min="0" name="preco_venda"
                                                class="pf-input pf-input-prefix-pad pf-money"
                                                placeholder="0,00"
                                                value="<?= e($produto['preco_venda'] ?? '0.00') ?>"
                                                id="preco-venda"
                                                required>
                                        </div>
                                    </div>
                                    <div class="pf-field pf-field-margem">
                                        <label class="pf-label">Margem</label>
                                        <div class="pf-margem-display" id="margem-display">
                                            <span id="margem-valor">—</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Estoque -->
                        <div class="pf-card">
                            <div class="pf-card-header">
                                <span class="pf-card-icon"><i class="ti ti-stack-2"></i></span>
                                <span class="pf-card-title">Estoque</span>
                            </div>
                            <div class="pf-card-body">

                                <div class="pf-row">
                                    <div class="pf-field">
                                        <label class="pf-label">Estoque Atual</label>
                                        <?php if ($isEdit): ?>
                                            <div class="pf-input-locked pf-input-locked-info">
                                                <i class="ti ti-lock"></i>
                                                <span><?= number_format((float)($produto['estoque_atual'] ?? 0), 3, ',', '.') ?></span>
                                            </div>
                                            <p class="pf-hint">
                                                <i class="ti ti-info-circle"></i>
                                                Altere via <a href="/estoque/movimentar" class="pf-hint-link">Movimentações de Estoque</a>
                                            </p>
                                        <?php else: ?>
                                            <input type="number" step="0.001" min="0" name="estoque_atual"
                                                class="pf-input"
                                                placeholder="0.000"
                                                value="0.000">
                                        <?php endif; ?>
                                    </div>
                                    <div class="pf-field">
                                        <label class="pf-label">Estoque Mínimo <span class="pf-hint-inline">(alerta)</span></label>
                                        <input type="number" step="0.001" min="0" name="estoque_minimo"
                                            class="pf-input"
                                            placeholder="0.000"
                                            value="<?= e($produto['estoque_minimo'] ?? '0.000') ?>">
                                    </div>
                                    <div class="pf-field">
                                        <label class="pf-label">Estoque Máximo</label>
                                        <input type="number" step="0.001" min="0" name="estoque_maximo"
                                            class="pf-input"
                                            placeholder="Opcional"
                                            value="<?= e($produto['estoque_maximo'] ?? '') ?>">
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <!-- ══════════════════════════════════════
                         COLUNA LATERAL
                    ══════════════════════════════════════ -->
                    <div class="pf-sidebar">

                        <!-- Publicação -->
                        <div class="pf-card">
                            <div class="pf-card-header">
                                <span class="pf-card-icon"><i class="ti ti-settings"></i></span>
                                <span class="pf-card-title">Publicação</span>
                            </div>
                            <div class="pf-card-body pf-card-body-gap">

                                <div class="pf-field">
                                    <label class="pf-label">Unidade de Medida <span class="pf-req">*</span></label>
                                    <div class="pf-select-wrap">
                                        <select name="unidade_id" class="pf-input pf-select" required>
                                            <?php foreach ($unidades as $un): ?>
                                                <option value="<?= $un['id'] ?>"
                                                    <?= (isset($produto['unidade_id']) && $produto['unidade_id'] == $un['id'])
                                                        ? 'selected'
                                                        : (empty($produto) && $un['sigla'] === 'UN' ? 'selected' : '') ?>>
                                                    <?= e($un['sigla']) ?> — <?= e($un['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <i class="ti ti-chevron-down pf-select-arrow"></i>
                                    </div>
                                </div>

                                <!-- Categoria com botão de criação rápida -->
                                <div class="pf-field">
                                    <div class="pf-label-row">
                                        <label class="pf-label">Categoria</label>
                                        <button type="button" class="pf-btn-add-cat" onclick="abrirModalCategoria()"
                                            title="Criar nova categoria">
                                            <i class="ti ti-plus"></i> Nova
                                        </button>
                                    </div>
                                    <div class="pf-select-wrap">
                                        <select name="categoria_id" class="pf-input pf-select" id="select-categoria">
                                            <option value="">Sem categoria</option>
                                            <?php foreach ($categorias as $cat): ?>
                                                <option value="<?= $cat['id'] ?>"
                                                    <?= (isset($produto['categoria_id']) && $produto['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                                                    <?= !empty($cat['parent_nome']) ? e($cat['parent_nome']) . ' › ' : '' ?><?= e($cat['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <i class="ti ti-chevron-down pf-select-arrow"></i>
                                    </div>
                                </div>

                                <?php if ($isEdit): ?>
                                    <div class="pf-status-row">
                                        <span class="pf-label">Status</span>
                                        <span class="pf-status-badge <?= $produto['ativo'] ? 'pf-status-active' : 'pf-status-inactive' ?>">
                                            <?= $produto['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <div class="pf-actions">
                                    <a href="/produtos" class="pf-btn pf-btn-ghost">Cancelar</a>
                                    <button type="submit" class="pf-btn pf-btn-primary">
                                        <i class="ti ti-device-floppy"></i>
                                        <?= $isEdit ? 'Salvar alterações' : 'Cadastrar produto' ?>
                                    </button>
                                </div>

                            </div>
                        </div>

                        <!-- Info card se edição -->
                        <?php if ($isEdit): ?>
                            <div class="pf-card pf-card-meta">
                                <div class="pf-meta-row">
                                    <span class="pf-meta-label">Cadastrado em</span>
                                    <span class="pf-meta-val"><?= date('d/m/Y', strtotime($produto['criado_em'])) ?></span>
                                </div>
                                <div class="pf-meta-row">
                                    <span class="pf-meta-label">Última atualização</span>
                                    <span class="pf-meta-val"><?= date('d/m/Y H:i', strtotime($produto['atualizado_em'])) ?></span>
                                </div>
                                <div class="pf-meta-row">
                                    <span class="pf-meta-label">Código interno</span>
                                    <span class="pf-meta-val pf-meta-code"><?= e($produto['codigo']) ?></span>
                                </div>
                                <a href="/estoque/<?= $produto['id'] ?>/historico" class="pf-meta-link">
                                    <i class="ti ti-history"></i> Ver histórico de estoque
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>
            </form>

        </div>
    </div>
</div>

<!-- ══════════════════════════════════════
     MODAL NOVA CATEGORIA
══════════════════════════════════════ -->
<div id="modal-categoria" class="pf-modal-overlay" onclick="if(event.target===this)fecharModalCategoria()">
    <div class="pf-modal">
        <div class="pf-modal-header">
            <div class="pf-modal-title">
                <span class="pf-modal-icon"><i class="ti ti-folder-plus"></i></span>
                Nova Categoria
            </div>
            <button type="button" class="pf-modal-close" onclick="fecharModalCategoria()">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="pf-modal-body">
            <div id="modal-cat-error" class="pf-modal-error" style="display:none;"></div>
            <div class="pf-field">
                <label class="pf-label">Nome da categoria <span class="pf-req">*</span></label>
                <input type="text" id="cat-nome" class="pf-input" placeholder="Ex: Bebidas, Laticínios...">
            </div>
            <div class="pf-field">
                <label class="pf-label">Categoria pai <span class="pf-hint-inline">(opcional)</span></label>
                <div class="pf-select-wrap">
                    <select id="cat-parent" class="pf-input pf-select">
                        <option value="">Nenhuma (categoria raiz)</option>
                        <?php foreach ($categorias as $cat): ?>
                            <?php if (empty($cat['parent_nome'])): // só raiz no select pai 
                            ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['nome']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <i class="ti ti-chevron-down pf-select-arrow"></i>
                </div>
            </div>
            <div class="pf-field">
                <label class="pf-label">Descrição <span class="pf-hint-inline">(opcional)</span></label>
                <input type="text" id="cat-descricao" class="pf-input" placeholder="Breve descrição...">
            </div>
        </div>
        <div class="pf-modal-footer">
            <button type="button" class="pf-btn pf-btn-ghost" onclick="fecharModalCategoria()">Cancelar</button>
            <button type="button" class="pf-btn pf-btn-primary" id="btn-salvar-cat" onclick="salvarCategoria()">
                <i class="ti ti-check"></i> Criar categoria
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════
     ESTILOS
══════════════════════════════════════ -->
<style>
    /* ── Layout 2 colunas ── */
    .pf-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 20px;
        align-items: start;
    }

    .pf-main {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .pf-sidebar {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* ── Cards ── */
    .pf-card {
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 12px;
        overflow: hidden;
    }

    .pf-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
        background: #FAFAFA;
    }

    .pf-card-icon {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        background: #1A1A1A;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    .pf-card-title {
        font-size: 13px;
        font-weight: 600;
        color: #1A1A1A;
    }

    .pf-card-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .pf-card-body-gap {
        gap: 16px;
    }

    .pf-card-meta {
        padding: 16px 18px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        background: #FAFAFA;
    }

    /* ── Grid rows ── */
    .pf-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .pf-field-full {
        grid-column: span 2;
    }

    .pf-field-margem {
        min-width: 90px;
    }

    /* ── Campos ── */
    .pf-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .pf-label {
        font-size: 11px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .pf-label-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pf-req {
        color: #DC2626;
    }

    .pf-hint-inline {
        font-size: 10px;
        font-weight: 400;
        color: #9CA3AF;
        text-transform: none;
        letter-spacing: 0;
    }

    .pf-hint {
        font-size: 11px;
        color: #9CA3AF;
        margin: 2px 0 0;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .pf-hint-link {
        color: #2563EB;
        text-decoration: none;
    }

    .pf-hint-link:hover {
        text-decoration: underline;
    }

    /* ── Inputs ── */
    .pf-input {
        font-family: inherit;
        font-size: 13px;
        color: #1A1A1A;
        background: #F9FAFB;
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 8px;
        padding: 9px 12px;
        outline: none;
        transition: border-color .15s, box-shadow .15s, background .15s;
        width: 100%;
        box-sizing: border-box;
    }

    .pf-input:focus {
        border-color: #1A1A1A;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, .06);
    }

    .pf-textarea {
        resize: vertical;
        min-height: 80px;
    }

    .pf-input-locked {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        background: #F3F4F6;
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 8px;
        font-size: 13px;
        color: #9CA3AF;
        cursor: not-allowed;
    }

    .pf-input-locked-info {
        color: #374151;
    }

    .pf-input-locked i {
        font-size: 13px;
        color: #D1D5DB;
    }

    .pf-input-wrap {
        position: relative;
    }

    .pf-input-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
        font-size: 14px;
        pointer-events: none;
    }

    .pf-input-icon-pad {
        padding-left: 32px;
    }

    .pf-input-prefix {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        font-weight: 600;
        color: #9CA3AF;
        pointer-events: none;
    }

    .pf-input-prefix-pad {
        padding-left: 30px;
    }

    /* ── Select ── */
    .pf-select-wrap {
        position: relative;
    }

    .pf-select {
        appearance: none;
        padding-right: 28px;
        cursor: pointer;
    }

    .pf-select-arrow {
        position: absolute;
        right: 9px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
        font-size: 12px;
        pointer-events: none;
    }

    /* ── Margem display ── */
    .pf-margem-display {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 39px;
        border-radius: 8px;
        background: #F9FAFB;
        border: 1px solid rgba(0, 0, 0, .12);
        font-size: 14px;
        font-weight: 700;
        color: #6B7280;
        transition: background .2s, color .2s;
    }

    .pf-margem-display.good {
        background: #DCFCE7;
        color: #16A34A;
        border-color: #BBF7D0;
    }

    .pf-margem-display.warn {
        background: #FEF3C7;
        color: #D97706;
        border-color: #FDE68A;
    }

    .pf-margem-display.bad {
        background: #FEE2E2;
        color: #DC2626;
        border-color: #FECACA;
    }

    /* ── Botão nova categoria ── */
    .pf-btn-add-cat {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-family: inherit;
        font-size: 11px;
        font-weight: 600;
        color: #2563EB;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 2px 4px;
        border-radius: 4px;
        transition: background .12s;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    .pf-btn-add-cat:hover {
        background: #EFF6FF;
    }

    /* ── Botões ── */
    .pf-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 16px;
        border-radius: 8px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        text-decoration: none;
        transition: opacity .15s;
        white-space: nowrap;
    }

    .pf-btn:hover {
        opacity: .86;
    }

    .pf-btn-primary {
        background: #1A1A1A;
        color: #fff;
        flex: 1;
        justify-content: center;
    }

    .pf-btn-ghost {
        background: #fff;
        color: #374151;
        border: 1px solid rgba(0, 0, 0, .14);
    }

    .pf-btn-ghost:hover {
        background: #F9FAFB;
    }

    .pf-actions {
        display: flex;
        gap: 8px;
        padding-top: 4px;
    }

    /* ── Status badge ── */
    .pf-status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 0;
        border-top: 1px solid rgba(0, 0, 0, .06);
    }

    .pf-status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .pf-status-active {
        background: #DCFCE7;
        color: #16A34A;
    }

    .pf-status-inactive {
        background: #F3F4F6;
        color: #9CA3AF;
    }

    /* ── Meta card ── */
    .pf-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pf-meta-label {
        font-size: 11px;
        color: #9CA3AF;
    }

    .pf-meta-val {
        font-size: 12px;
        color: #374151;
        font-weight: 500;
    }

    .pf-meta-code {
        font-family: monospace;
        background: #F3F4F6;
        padding: 1px 6px;
        border-radius: 4px;
    }

    .pf-meta-link {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: #2563EB;
        text-decoration: none;
        margin-top: 4px;
        padding-top: 10px;
        border-top: 1px solid rgba(0, 0, 0, .06);
    }

    .pf-meta-link:hover {
        text-decoration: underline;
    }

    /* ── Modal ── */
    .pf-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1000;
        background: rgba(0, 0, 0, .45);
        backdrop-filter: blur(2px);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .pf-modal-overlay.show {
        display: flex;
    }

    .pf-modal {
        background: #fff;
        border-radius: 14px;
        width: 100%;
        max-width: 440px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
        overflow: hidden;
        animation: pfSlide .2s ease;
    }

    @keyframes pfSlide {
        from {
            opacity: 0;
            transform: translateY(14px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .pf-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px 14px;
        border-bottom: 1px solid #F3F4F6;
    }

    .pf-modal-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 600;
    }

    .pf-modal-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #EFF6FF;
        color: #2563EB;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .pf-modal-close {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        border: none;
        background: #F3F4F6;
        color: #6B7280;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
    }

    .pf-modal-close:hover {
        background: #E5E7EB;
    }

    .pf-modal-body {
        padding: 18px 22px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .pf-modal-footer {
        padding: 14px 22px;
        border-top: 1px solid #F3F4F6;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .pf-modal-error {
        padding: 10px 12px;
        background: #FEE2E2;
        border: 1px solid #FECACA;
        border-radius: 8px;
        font-size: 12px;
        color: #DC2626;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .pf-layout {
            grid-template-columns: 1fr;
        }

        .pf-row {
            grid-template-columns: 1fr;
        }

        .pf-field-full {
            grid-column: span 1;
        }
    }
</style>

<script>
    // ── Cálculo de margem em tempo real ─────────────────────────
    function calcularMargem() {
        const custo = parseFloat(document.querySelector('[name="preco_custo"]')?.value) || 0;
        const venda = parseFloat(document.querySelector('[name="preco_venda"]')?.value) || 0;
        const el = document.getElementById('margem-display');
        const txt = document.getElementById('margem-valor');

        if (!el || !txt) return;

        if (custo <= 0 || venda <= 0) {
            txt.textContent = '—';
            el.className = 'pf-margem-display';
            return;
        }

        const margem = ((venda - custo) / venda * 100).toFixed(1);
        txt.textContent = margem + '%';

        el.className = 'pf-margem-display ' +
            (margem >= 30 ? 'good' : margem >= 10 ? 'warn' : 'bad');
    }

    document.querySelector('[name="preco_custo"]')?.addEventListener('input', calcularMargem);
    document.querySelector('[name="preco_venda"]')?.addEventListener('input', calcularMargem);
    calcularMargem(); // ao carregar

    // ── Modal de categoria ───────────────────────────────────────
    function abrirModalCategoria() {
        document.getElementById('modal-categoria').classList.add('show');
        setTimeout(() => document.getElementById('cat-nome').focus(), 100);
    }

    function fecharModalCategoria() {
        document.getElementById('modal-categoria').classList.remove('show');
        document.getElementById('cat-nome').value = '';
        document.getElementById('cat-descricao').value = '';
        document.getElementById('cat-parent').value = '';
        document.getElementById('modal-cat-error').style.display = 'none';
    }

    async function salvarCategoria() {
        const btn = document.getElementById('btn-salvar-cat');
        const errEl = document.getElementById('modal-cat-error');
        const nome = document.getElementById('cat-nome').value.trim();
        const descricao = document.getElementById('cat-descricao').value.trim();
        const parentId = document.getElementById('cat-parent').value;

        errEl.style.display = 'none';

        if (!nome) {
            errEl.textContent = 'Nome da categoria é obrigatório.';
            errEl.style.display = 'block';
            document.getElementById('cat-nome').focus();
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="ti ti-loader-2"></i> Salvando...';

        try {
            const resp = await fetch('/categorias/ajax', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    nome,
                    descricao,
                    parent_id: parentId
                })
            });

            const data = await resp.json();

            if (!data.success) throw new Error(data.message || 'Erro ao criar categoria.');

            // Adiciona ao select e seleciona
            const select = document.getElementById('select-categoria');
            const option = new Option(data.categoria.nome, data.categoria.id, true, true);
            select.appendChild(option);

            fecharModalCategoria();
        } catch (err) {
            errEl.textContent = err.message;
            errEl.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-check"></i> Criar categoria';
        }
    }

    // Enter no campo nome salva
    document.getElementById('cat-nome')?.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            salvarCategoria();
        }
    });

    // ESC fecha modal
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') fecharModalCategoria();
    });
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>