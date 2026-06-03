<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">

        <?php
        $isEdit    = !empty($categoria);
        $pageTitle = $isEdit ? 'Editar Categoria' : 'Nova Categoria';
        $breadcrumb = [
            ['label' => 'Dashboard',  'url' => '/dashboard'],
            ['label' => 'Categorias', 'url' => '/categorias'],
            ['label' => $isEdit ? 'Editar' : 'Nova', 'url' => '#'],
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
                action="<?= $isEdit ? '/categorias/' . $categoria['id'] . '/editar' : '/categorias/criar' ?>"
                method="POST"
                id="form-categoria">

                <?= $csrf ?? '' ?>

                <div class="pf-layout">

                    <!-- ══════════════════════════════════════
                         COLUNA PRINCIPAL
                    ══════════════════════════════════════ -->
                    <div class="pf-main">

                        <!-- Identificação -->
                        <div class="pf-card">
                            <div class="pf-card-header">
                                <span class="pf-card-icon"><i class="ti ti-folder"></i></span>
                                <span class="pf-card-title">Identificação</span>
                            </div>
                            <div class="pf-card-body">

                                <div class="pf-field pf-field-full">
                                    <label class="pf-label">Nome da Categoria <span class="pf-req">*</span></label>
                                    <input
                                        type="text"
                                        name="nome"
                                        class="pf-input"
                                        placeholder="Ex: Bebidas, Laticínios, Higiene..."
                                        value="<?= e($categoria['nome'] ?? '') ?>"
                                        required autofocus>
                                </div>

                                <div class="pf-field pf-field-full">
                                    <label class="pf-label">Descrição <span class="pf-hint-inline">(opcional)</span></label>
                                    <textarea
                                        name="descricao"
                                        class="pf-input pf-textarea"
                                        placeholder="Breve descrição do que esta categoria engloba..."
                                        rows="3"><?= e($categoria['descricao'] ?? '') ?></textarea>
                                </div>

                            </div>
                        </div>

                        <!-- Hierarquia -->
                        <div class="pf-card">
                            <div class="pf-card-header">
                                <span class="pf-card-icon"><i class="ti ti-sitemap"></i></span>
                                <span class="pf-card-title">Hierarquia</span>
                            </div>
                            <div class="pf-card-body">

                                <div class="pf-field">
                                    <label class="pf-label">Categoria Pai <span class="pf-hint-inline">(opcional — deixe vazio para categoria raiz)</span></label>
                                    <div class="pf-select-wrap">
                                        <select name="parent_id" class="pf-input pf-select">
                                            <option value="">Nenhuma (categoria raiz)</option>
                                            <?php foreach ($raiz as $r): ?>
                                                <?php
                                                // impede selecionar a si mesma como pai ao editar
                                                if ($isEdit && $r['id'] == $categoria['id']) continue;
                                                ?>
                                                <option value="<?= $r['id'] ?>"
                                                    <?= (isset($categoria['parent_id']) && $categoria['parent_id'] == $r['id']) ? 'selected' : '' ?>>
                                                    <?= e($r['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <i class="ti ti-chevron-down pf-select-arrow"></i>
                                    </div>
                                    <p class="pf-hint">
                                        <i class="ti ti-info-circle"></i>
                                        Categorias de nível 2 (subcategorias) aparecem agrupadas sob a sua categoria pai nos seletores de produto.
                                    </p>
                                </div>

                                <!-- Preview da hierarquia -->
                                <div class="cat-hierarchy-preview" id="hierarchy-preview">
                                    <span class="cat-preview-label">Exemplo de exibição:</span>
                                    <span class="cat-preview-path" id="preview-path">
                                        <i class="ti ti-folder"></i>
                                        <span id="preview-parent-text"></span>
                                        <span id="preview-sep" style="display:none"> › </span>
                                        <strong id="preview-nome"><?= e($categoria['nome'] ?? 'Nome da categoria') ?></strong>
                                    </span>
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

                                <?php if ($isEdit): ?>
                                    <div class="pf-status-row">
                                        <span class="pf-label">Status</span>
                                        <span class="pf-status-badge <?= $categoria['ativo'] ? 'pf-status-active' : 'pf-status-inactive' ?>">
                                            <?= $categoria['ativo'] ? 'Ativa' : 'Inativa' ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <div class="pf-actions">
                                    <a href="/categorias" class="pf-btn pf-btn-ghost">Cancelar</a>
                                    <button type="submit" class="pf-btn pf-btn-primary">
                                        <i class="ti ti-device-floppy"></i>
                                        <?= $isEdit ? 'Salvar alterações' : 'Criar categoria' ?>
                                    </button>
                                </div>

                            </div>
                        </div>

                        <!-- Meta info (somente edição) -->
                        <?php if ($isEdit): ?>
                            <div class="pf-card pf-card-meta">
                                <div class="pf-meta-row">
                                    <span class="pf-meta-label">ID</span>
                                    <span class="pf-meta-val pf-meta-code">#<?= $categoria['id'] ?></span>
                                </div>
                                <?php if (!empty($categoria['criado_em'])): ?>
                                    <div class="pf-meta-row">
                                        <span class="pf-meta-label">Cadastrada em</span>
                                        <span class="pf-meta-val"><?= date('d/m/Y', strtotime($categoria['criado_em'])) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($categoria['atualizado_em'])): ?>
                                    <div class="pf-meta-row">
                                        <span class="pf-meta-label">Última atualização</span>
                                        <span class="pf-meta-val"><?= date('d/m/Y H:i', strtotime($categoria['atualizado_em'])) ?></span>
                                    </div>
                                <?php endif; ?>
                                <a href="/produtos?categoria_id=<?= $categoria['id'] ?>" class="pf-meta-link">
                                    <i class="ti ti-box"></i> Ver produtos desta categoria
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Dica -->
                        <div class="pf-card cat-tip-card">
                            <div class="pf-card-body" style="gap:8px;">
                                <div class="cat-tip-title">
                                    <i class="ti ti-bulb"></i> Dica
                                </div>
                                <p class="cat-tip-text">
                                    Use categorias <strong>raiz</strong> para grupos grandes (ex: <em>Bebidas</em>) e
                                    <strong>subcategorias</strong> para segmentar (ex: <em>Bebidas › Alcoólicas</em>).
                                </p>
                            </div>
                        </div>

                    </div>

                </div>
            </form>

        </div>
    </div>
</div>

<!-- ══════════════════════════════════════
     ESTILOS
══════════════════════════════════════ -->
<style>
    /* ── Layout 2 colunas (reutiliza pf-*) ── */
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

    /* Cards */
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

    /* Campos */
    .pf-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .pf-field-full {
        grid-column: span 2;
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

    /* Inputs */
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

    /* Select */
    .pf-select-wrap {
        position: relative;
    }

    .pf-select {
        appearance: none;
        -webkit-appearance: none;
        padding-right: 30px !important;
    }

    .pf-select-arrow {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 13px;
        color: #9CA3AF;
        pointer-events: none;
    }

    /* Status badge */
    .pf-status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #F3F4F6;
    }

    .pf-status-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .pf-status-active {
        background: #D1FAE5;
        color: #065F46;
    }

    .pf-status-inactive {
        background: #F3F4F6;
        color: #6B7280;
    }

    /* Botões */
    .pf-actions {
        display: flex;
        gap: 8px;
        margin-top: 4px;
    }

    .pf-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        padding: 9px 16px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: opacity .15s, background .15s;
        flex: 1;
        justify-content: center;
    }

    .pf-btn-primary {
        background: #1A1A1A;
        color: #fff;
    }

    .pf-btn-primary:hover {
        opacity: .85;
    }

    .pf-btn-ghost {
        background: #F3F4F6;
        color: #374151;
    }

    .pf-btn-ghost:hover {
        background: #E5E7EB;
    }

    /* Meta info */
    .pf-meta-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
    }

    .pf-meta-label {
        color: #9CA3AF;
    }

    .pf-meta-val {
        color: #1A1A1A;
        font-weight: 500;
    }

    .pf-meta-code {
        font-family: monospace;
        background: #F3F4F6;
        padding: 1px 6px;
        border-radius: 4px;
    }

    .pf-meta-link {
        font-size: 12px;
        color: #2563EB;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
    }

    .pf-meta-link:hover {
        text-decoration: underline;
    }

    /* Preview hierarquia */
    .cat-hierarchy-preview {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #F9FAFB;
        border: 1px dashed rgba(0, 0, 0, .12);
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13px;
        flex-wrap: wrap;
        gap: 6px;
    }

    .cat-preview-label {
        font-size: 11px;
        font-weight: 600;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: .4px;
        white-space: nowrap;
    }

    .cat-preview-path {
        display: flex;
        align-items: center;
        gap: 4px;
        color: #374151;
    }

    .cat-preview-path i {
        color: #9CA3AF;
    }

    /* Card dica */
    .cat-tip-card {
        border-color: #FEF3C7;
        background: #FFFBEB;
    }

    .cat-tip-title {
        font-size: 12px;
        font-weight: 600;
        color: #92400E;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .cat-tip-text {
        font-size: 12px;
        color: #78350F;
        line-height: 1.5;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .pf-layout {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    // ── Preview de hierarquia em tempo real ────────────────────
    const inputNome = document.querySelector('[name="nome"]');
    const selectPai = document.querySelector('[name="parent_id"]');
    const previewNome = document.getElementById('preview-nome');
    const previewPai = document.getElementById('preview-parent-text');
    const previewSep = document.getElementById('preview-sep');

    function atualizarPreview() {
        const nome = inputNome?.value.trim() || 'Nome da categoria';
        const pai = selectPai?.options[selectPai.selectedIndex]?.text || '';

        if (previewNome) previewNome.textContent = nome;

        if (selectPai?.value && previewPai) {
            previewPai.textContent = pai;
            if (previewSep) previewSep.style.display = '';
        } else {
            if (previewPai) previewPai.textContent = '';
            if (previewSep) previewSep.style.display = 'none';
        }
    }

    inputNome?.addEventListener('input', atualizarPreview);
    selectPai?.addEventListener('change', atualizarPreview);
    atualizarPreview(); // estado inicial
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>