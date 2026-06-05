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

                                <div class="pf-tipo-toggle">
                                    <input type="hidden" name="tipo" id="input-tipo"
                                        value="<?= e($produto['tipo'] ?? 'produto') ?>">

                                    <button type="button"
                                        class="pf-tipo-btn <?= ($produto['tipo'] ?? 'produto') === 'produto' ? 'active' : '' ?>"
                                        data-tipo="produto"
                                        onclick="setTipo('produto')">
                                        <i class="ti ti-package"></i>
                                        <span>Produto</span>
                                        <small>Controla estoque</small>
                                    </button>

                                    <button type="button"
                                        class="pf-tipo-btn <?= ($produto['tipo'] ?? 'produto') === 'servico' ? 'active' : '' ?>"
                                        data-tipo="servico"
                                        onclick="setTipo('servico')">
                                        <i class="ti ti-settings-cog"></i>
                                        <span>Serviço</span>
                                        <small>Sem estoque</small>
                                    </button>
                                </div>

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

                                <!-- Inputs de custo e venda -->
                                <div class="pf-row">
                                    <div class="pf-field">
                                        <label class="pf-label">Preço de Custo (R$) <span class="pf-req">*</span></label>
                                        <div class="pf-input-wrap">
                                            <span class="pf-input-prefix">R$</span>
                                            <input type="number" step="0.01" min="0" name="preco_custo"
                                                class="pf-input pf-input-prefix-pad pf-money"
                                                placeholder="0,00"
                                                value="<?= e($produto['preco_custo'] ?? '') ?>"
                                                id="preco-custo"
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
                                                value="<?= e($produto['preco_venda'] ?? '') ?>"
                                                id="preco-venda"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Painel de métricas calculadas -->
                                <div class="price-metrics" id="price-metrics">
                                    <div class="price-metric metric-empty" id="metric-lucro">
                                        <div class="price-metric-label"><i class="ti ti-coin"></i> Lucro bruto</div>
                                        <div class="price-metric-value" id="val-lucro">—</div>
                                        <div class="price-metric-sub">venda − custo</div>
                                    </div>
                                    <div class="price-metric metric-empty" id="metric-margem">
                                        <div class="price-metric-label"><i class="ti ti-trending-up"></i> Margem</div>
                                        <div class="price-metric-value" id="val-margem">—</div>
                                        <div class="price-metric-sub">sobre o preço de venda</div>
                                    </div>
                                    <div class="price-metric metric-empty" id="metric-markup">
                                        <div class="price-metric-label"><i class="ti ti-percentage"></i> Markup</div>
                                        <div class="price-metric-value" id="val-markup">—</div>
                                        <div class="price-metric-sub">sobre o custo</div>
                                    </div>
                                </div>

                                <!-- Dica contextual dinâmica -->
                                <div class="price-context hidden" id="price-context">
                                    <i class="ti ti-info-circle"></i>
                                    <span id="price-context-text"></span>
                                </div>

                            </div>
                        </div>

                        <!-- Estoque -->
                        <div class="pf-card" id="card-estoque">
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
        max-width: 700px;
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

    .pf-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

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

    .pf-req {
        color: #DC2626;
    }

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

    .pf-input-wrap {
        position: relative;
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

    /* Toggle Produto / Serviço */
    .pf-tipo-toggle {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 4px;
    }

    .pf-tipo-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        padding: 14px 12px;
        border-radius: 10px;
        border: 2px solid rgba(0, 0, 0, .10);
        background: #F9FAFB;
        cursor: pointer;
        transition: all .18s;
        font-family: inherit;
        color: #6B7280;
    }

    .pf-tipo-btn i {
        font-size: 20px;
        margin-bottom: 2px;
    }

    .pf-tipo-btn span {
        font-size: 13px;
        font-weight: 600;
        color: inherit;
    }

    .pf-tipo-btn small {
        font-size: 10px;
        color: #9CA3AF;
    }

    .pf-tipo-btn:hover {
        border-color: rgba(0, 0, 0, .20);
        background: #F3F4F6;
    }

    /* Produto ativo */
    .pf-tipo-btn[data-tipo="produto"].active {
        background: #EFF6FF;
        border-color: #3B82F6;
        color: #1D4ED8;
    }

    .pf-tipo-btn[data-tipo="produto"].active small {
        color: #93C5FD;
    }

    /* Serviço ativo */
    .pf-tipo-btn[data-tipo="servico"].active {
        background: #F0FDF4;
        border-color: #22C55E;
        color: #15803D;
    }

    .pf-tipo-btn[data-tipo="servico"].active small {
        color: #86EFAC;
    }

    /* Aviso exibido quando tipo = serviço */
    .pf-servico-aviso {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 10px 14px;
        background: #F0FDF4;
        border: 1px solid #BBF7D0;
        border-radius: 8px;
        font-size: 12px;
        color: #15803D;
        line-height: 1.5;
        margin-bottom: 4px;
        animation: fadeIn .2s ease;
    }

    .pf-servico-aviso i {
        font-size: 15px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-4px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Card de estoque oculto */
    #card-estoque.hidden {
        display: none;
    }

    /* ── Painel de métricas ── */
    .price-metrics {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
        margin-top: 2px;
    }

    .price-metric {
        background: #F9FAFB;
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 10px;
        padding: 12px 14px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        transition: background .2s, border-color .2s;
    }

    .price-metric-label {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 10px;
        font-weight: 600;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .price-metric-label i {
        font-size: 12px;
    }

    .price-metric-value {
        font-size: 18px;
        font-weight: 700;
        color: #6B7280;
        line-height: 1;
        transition: color .2s;
        font-variant-numeric: tabular-nums;
    }

    .price-metric-sub {
        font-size: 10px;
        color: #D1D5DB;
        margin-top: 1px;
    }

    /* estados coloridos */
    .metric-good {
        background: #F0FDF4;
        border-color: #BBF7D0;
    }

    .metric-good .price-metric-value {
        color: #16A34A;
    }

    .metric-good .price-metric-label {
        color: #86EFAC;
    }

    .metric-warn {
        background: #FFFBEB;
        border-color: #FDE68A;
    }

    .metric-warn .price-metric-value {
        color: #D97706;
    }

    .metric-warn .price-metric-label {
        color: #FCD34D;
    }

    .metric-bad {
        background: #FFF1F2;
        border-color: #FECDD3;
    }

    .metric-bad .price-metric-value {
        color: #E11D48;
    }

    .metric-bad .price-metric-label {
        color: #FDA4AF;
    }

    /* ── Tooltip de contexto ── */
    .price-context {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 10px 12px;
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        border-radius: 8px;
        font-size: 11px;
        color: #1D4ED8;
        line-height: 1.5;
        transition: all .3s;
    }

    .price-context i {
        font-size: 14px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .price-context.hidden {
        display: none;
    }

    .price-context.warn-ctx {
        background: #FFFBEB;
        border-color: #FDE68A;
        color: #92400E;
    }

    .price-context.bad-ctx {
        background: #FFF1F2;
        border-color: #FECDD3;
        color: #9F1239;
    }

    /* placeholder state */
    .metric-empty .price-metric-value {
        color: #D1D5DB;
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
    // ── Toggle Produto / Serviço ────────────────────────────────
    function setTipo(tipo) {
        document.getElementById('input-tipo').value = tipo;

        document.querySelectorAll('.pf-tipo-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tipo === tipo);
        });

        const cardEstoque = document.getElementById('card-estoque');
        const aviso = document.getElementById('servico-aviso');
        const precoCusto = document.getElementById('preco-custo'); // <-- adiciona

        if (tipo === 'servico') {
            cardEstoque?.classList.add('hidden');
            // Remove required do custo — serviço não precisa
            if (precoCusto) {
                precoCusto.removeAttribute('required');
                precoCusto.value = precoCusto.value || '0'; // garante 0,00 se vazio
            }
            if (!aviso) {
                const el = document.createElement('div');
                el.id = 'servico-aviso';
                el.className = 'pf-servico-aviso';
                el.innerHTML = '<i class="ti ti-info-circle"></i> Serviços não controlam estoque. Os campos de quantidade serão ignorados automaticamente.';
                cardEstoque?.insertAdjacentElement('beforebegin', el);
            }
        } else {
            cardEstoque?.classList.remove('hidden');
            document.getElementById('servico-aviso')?.remove();
            // Restaura required ao voltar para produto
            precoCusto?.setAttribute('required', '');
        }
    }

    // Garante estado correto ao carregar (modo edição)
    setTipo(document.getElementById('input-tipo')?.value || 'produto');


    // ── Cálculo de margem em tempo real ─────────────────────────
    const brl = v => v.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });
    const pct = v => v.toFixed(1) + '%';

    function calcularPrecos() {
        const custo = parseFloat(document.querySelector('[name="preco_custo"]')?.value) || 0;
        const venda = parseFloat(document.querySelector('[name="preco_venda"]')?.value) || 0;

        const mLucro = document.getElementById('metric-lucro');
        const mMargem = document.getElementById('metric-margem');
        const mMarkup = document.getElementById('metric-markup');
        const ctx = document.getElementById('price-context');
        const ctxText = document.getElementById('price-context-text');

        if (!mLucro) return; // card de preços não carregado

        if (custo <= 0 || venda <= 0) {
            [mLucro, mMargem, mMarkup].forEach(el => el.className = 'price-metric metric-empty');
            document.getElementById('val-lucro').textContent = '—';
            document.getElementById('val-margem').textContent = '—';
            document.getElementById('val-markup').textContent = '—';
            if (ctx) ctx.className = 'price-context hidden';
            return;
        }

        const lucro = venda - custo;
        const margem = (lucro / venda) * 100;
        const markup = (lucro / custo) * 100;

        document.getElementById('val-lucro').textContent = brl(lucro);
        document.getElementById('val-margem').textContent = pct(margem);
        document.getElementById('val-markup').textContent = pct(markup);

        const estado = margem >= 30 ? 'good' : margem >= 10 ? 'warn' : 'bad';
        [mLucro, mMargem, mMarkup].forEach(el => el.className = `price-metric metric-${estado}`);

        if (ctx) {
            ctx.className = `price-context ${estado === 'good' ? '' : estado === 'warn' ? 'warn-ctx' : 'bad-ctx'}`.trim();

            if (lucro < 0) {
                ctxText.innerHTML = `<strong>Prejuízo por unidade.</strong> O preço de venda está abaixo do custo — cada venda gera perda de ${brl(Math.abs(lucro))}. <em>Margem: % do lucro no preço de venda. Markup: % adicionado sobre o custo para formar o preço.</em>`;
                ctx.className = 'price-context bad-ctx';
            } else if (margem < 10) {
                ctxText.innerHTML = `<strong>Margem apertada.</strong> Apenas ${pct(margem)} do preço de venda é lucro bruto. Verifique se cobre despesas operacionais. <em>Markup indica quanto foi acrescentado ao custo para chegar ao preço final.</em>`;
            } else if (margem < 30) {
                ctxText.innerHTML = `<strong>Margem moderada.</strong> ${pct(margem)} do preço de venda é lucro bruto. O markup de ${pct(markup)} é o percentual acrescentado ao custo para formar o preço.`;
            } else {
                ctxText.innerHTML = `<strong>Boa margem.</strong> ${pct(margem)} do preço de venda retorna como lucro bruto — ${brl(lucro)} por unidade disponíveis para custos fixos e resultado líquido.`;
            }
        }
    }

    document.querySelector('[name="preco_custo"]')?.addEventListener('input', calcularPrecos);
    document.querySelector('[name="preco_venda"]')?.addEventListener('input', calcularPrecos);
    calcularPrecos();


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
                }),
            });

            const data = await resp.json();
            if (!data.success) throw new Error(data.message || 'Erro ao criar categoria.');

            const select = document.getElementById('select-categoria');
            select.appendChild(new Option(data.categoria.nome, data.categoria.id, true, true));
            fecharModalCategoria();

        } catch (err) {
            errEl.textContent = err.message;
            errEl.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-check"></i> Criar categoria';
        }
    }

    document.getElementById('cat-nome')?.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            salvarCategoria();
        }
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') fecharModalCategoria();
    });
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>