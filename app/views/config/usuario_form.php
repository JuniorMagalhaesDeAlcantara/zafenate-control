<?php require VIEW_PATH . '/layouts/header.php'; ?>

<style>
    .cfg-page {
        padding: 28px 32px;
    }

    .cfg-topbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .cfg-topbar h1 {
        font-size: 22px;
        font-weight: 600;
        margin: 0 0 3px;
        letter-spacing: -.3px;
    }

    .cfg-topbar p {
        font-size: 13px;
        color: var(--text-tertiary);
        margin: 0;
    }

    /* ── Layout 2 colunas ── */
    .uf-layout {
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 20px;
        align-items: start;
    }

    .uf-main,
    .uf-side {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* ── Cards ── */
    .cfg-card {
        background: var(--color-background-primary, #fff);
        border: 1px solid var(--color-border-tertiary, #e5e7eb);
        border-radius: 10px;
        overflow: hidden;
    }

    .cfg-card-head {
        padding: 14px 20px;
        border-bottom: 1px solid var(--color-border-tertiary, #e5e7eb);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cfg-card-head-icon {
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

    .cfg-card-head-title {
        font-size: 13px;
        font-weight: 600;
    }

    .cfg-card-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* ── Grid de campos ── */
    .uf-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .uf-row-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 14px;
    }

    .uf-field-full {
        grid-column: span 2;
    }

    /* ── Campos ── */
    .uf-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .uf-label {
        font-size: 11px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .uf-req {
        color: #DC2626;
    }

    .uf-hint {
        font-size: 11px;
        color: #9CA3AF;
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* ── Inputs ── */
    .uf-input {
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

    .uf-input:focus {
        border-color: #1A1A1A;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, .06);
    }

    .uf-input:disabled {
        background: #F3F4F6;
        color: #9CA3AF;
        cursor: not-allowed;
        border-color: rgba(0, 0, 0, .07);
    }

    /* ── Select ── */
    .uf-select-wrap {
        position: relative;
    }

    .uf-select {
        appearance: none;
        padding-right: 28px;
        cursor: pointer;
    }

    .uf-select-arrow {
        position: absolute;
        right: 9px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
        font-size: 12px;
        pointer-events: none;
    }

    /* ── Password toggle ── */
    .uf-pw-wrap {
        position: relative;
    }

    .uf-pw-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #9CA3AF;
        font-size: 15px;
        padding: 0;
        line-height: 1;
        transition: color .12s;
    }

    .uf-pw-toggle:hover {
        color: #374151;
    }

    .uf-pw-input {
        padding-right: 36px;
    }

    /* ── Força da senha ── */
    .uf-pw-strength {
        display: flex;
        gap: 4px;
        margin-top: 6px;
    }

    .uf-pw-bar {
        height: 3px;
        flex: 1;
        border-radius: 2px;
        background: #E5E7EB;
        transition: background .2s;
    }

    .uf-pw-bar.active-weak {
        background: #EF4444;
    }

    .uf-pw-bar.active-medium {
        background: #F59E0B;
    }

    .uf-pw-bar.active-strong {
        background: #22C55E;
    }

    .uf-pw-label {
        font-size: 11px;
        color: #9CA3AF;
        margin-top: 4px;
    }

    /* ── Avatar preview ── */
    .uf-avatar-preview {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
        transition: background .2s;
    }

    .uf-user-preview {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        background: #F9FAFB;
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 10px;
    }

    .uf-preview-name {
        font-size: 15px;
        font-weight: 600;
        color: #1A1A1A;
        line-height: 1.2;
    }

    .uf-preview-email {
        font-size: 12px;
        color: #9CA3AF;
        margin-top: 2px;
    }

    /* ── Nível radio cards ── */
    .uf-nivel-grid {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .uf-nivel-opt {
        cursor: pointer;
    }

    .uf-nivel-opt input {
        display: none;
    }

    .uf-nivel-face {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1.5px solid #E5E7EB;
        background: #fff;
        transition: all .12s;
    }

    .uf-nivel-opt:hover .uf-nivel-face {
        border-color: #9CA3AF;
        background: #F9FAFB;
    }

    .uf-nivel-opt input:checked+.uf-nivel-face {
        border-color: #1A1A1A;
        background: #1A1A1A;
        color: #fff;
    }

    .uf-nivel-opt input:checked+.uf-nivel-face .uf-nivel-desc {
        color: rgba(255, 255, 255, .6);
    }

    .uf-nivel-opt input:checked+.uf-nivel-face .uf-nivel-icon {
        background: rgba(255, 255, 255, .15);
        color: #fff;
    }

    .uf-nivel-icon {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    .uf-nivel-icon-admin {
        background: #FEF3C7;
        color: #D97706;
    }

    .uf-nivel-icon-gerente {
        background: #DBEAFE;
        color: #2563EB;
    }

    .uf-nivel-icon-oper {
        background: #F0FDF4;
        color: #16A34A;
    }

    .uf-nivel-info {
        flex: 1;
        min-width: 0;
    }

    .uf-nivel-name {
        font-size: 13px;
        font-weight: 600;
    }

    .uf-nivel-desc {
        font-size: 11px;
        color: #9CA3AF;
        margin-top: 1px;
    }

    /* ── Status toggle ── */
    .uf-status-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 8px;
        cursor: pointer;
        transition: background .12s;
    }

    .uf-status-toggle:hover {
        background: #F9FAFB;
    }

    .uf-status-toggle input {
        display: none;
    }

    .uf-toggle-label {
        font-size: 13px;
        font-weight: 500;
    }

    .uf-toggle-desc {
        font-size: 11px;
        color: #9CA3AF;
    }

    .uf-toggle-pill {
        width: 38px;
        height: 22px;
        border-radius: 11px;
        background: #D1D5DB;
        position: relative;
        transition: background .2s;
        flex-shrink: 0;
    }

    .uf-toggle-pill::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #fff;
        top: 3px;
        left: 3px;
        transition: transform .2s;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
    }

    .uf-toggle-pill.on {
        background: #22C55E;
    }

    .uf-toggle-pill.on::after {
        transform: translateX(16px);
    }

    /* ── Botões ── */
    .uf-actions {
        display: flex;
        gap: 8px;
        padding-top: 4px;
    }

    .uf-btn {
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

    .uf-btn:hover {
        opacity: .86;
    }

    .uf-btn-primary {
        background: #1A1A1A;
        color: #fff;
        flex: 1;
        justify-content: center;
    }

    .uf-btn-ghost {
        background: #fff;
        color: #374151;
        border: 1px solid rgba(0, 0, 0, .14);
    }

    .uf-btn-ghost:hover {
        background: #F9FAFB;
        opacity: 1;
    }

    /* ── Divider ── */
    .uf-divider {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 11px;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .uf-divider::before,
    .uf-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #E5E7EB;
    }

    /* ── Meta info (edição) ── */
    .uf-meta {
        padding: 14px 16px;
        background: #F9FAFB;
        border: 1px solid rgba(0, 0, 0, .07);
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .uf-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .uf-meta-label {
        font-size: 11px;
        color: #9CA3AF;
    }

    .uf-meta-val {
        font-size: 12px;
        color: #374151;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .uf-layout {
            grid-template-columns: 1fr;
        }

        .uf-row {
            grid-template-columns: 1fr;
        }

        .uf-row-3 {
            grid-template-columns: 1fr;
        }

        .uf-field-full {
            grid-column: span 1;
        }
    }
</style>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">
        <?php
        $isEdit     = !empty($usuario);
        $pageTitle  = $isEdit ? 'Editar Usuário' : 'Novo Usuário';
        $breadcrumb = [
            ['label' => 'Dashboard',    'url' => '/dashboard'],
            ['label' => 'Configurações', 'url' => '#'],
            ['label' => 'Usuários',     'url' => '/config/usuarios'],
            ['label' => $isEdit ? 'Editar' : 'Novo', 'url' => '#'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content cfg-page">

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

            <div class="cfg-topbar">
                <div>
                    <h1><?= $isEdit ? 'Editar Usuário' : 'Novo Usuário' ?></h1>
                    <p><?= $isEdit ? 'Atualize os dados do usuário' : 'Preencha os dados para criar o acesso' ?></p>
                </div>
                <a href="/config/usuarios" class="uf-btn uf-btn-ghost">
                    <i class="ti ti-arrow-left"></i> Voltar
                </a>
            </div>

            <form
                method="POST"
                action="<?= $isEdit ? '/config/usuarios/' . $usuario['id'] . '/editar' : '/config/usuarios/criar' ?>"
                id="form-usuario">
                <?= csrf_field() ?>

                <div class="uf-layout">

                    <!-- ══════════════════════
                         COLUNA PRINCIPAL
                    ══════════════════════ -->
                    <div class="uf-main">

                        <!-- Dados pessoais -->
                        <div class="cfg-card">
                            <div class="cfg-card-head">
                                <span class="cfg-card-head-icon"><i class="ti ti-user"></i></span>
                                <span class="cfg-card-head-title">Dados do Usuário</span>
                            </div>
                            <div class="cfg-card-body">

                                <!-- Preview do avatar -->
                                <div class="uf-user-preview">
                                    <div class="uf-avatar-preview" id="avatar-preview" style="background:#6B7280;">
                                        <span id="avatar-ini">?</span>
                                    </div>
                                    <div>
                                        <div class="uf-preview-name" id="preview-nome">
                                            <?= $isEdit ? e($usuario['nome']) : 'Nome do usuário' ?>
                                        </div>
                                        <div class="uf-preview-email" id="preview-email">
                                            <?= $isEdit ? e($usuario['email']) : 'email@empresa.com' ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="uf-row">
                                    <div class="uf-field uf-field-full">
                                        <label class="uf-label">Nome completo <span class="uf-req">*</span></label>
                                        <input type="text" name="nome" class="uf-input"
                                            id="input-nome"
                                            placeholder="Ex: João da Silva"
                                            value="<?= e($usuario['nome'] ?? '') ?>"
                                            required autofocus>
                                    </div>
                                </div>

                                <div class="uf-row">
                                    <div class="uf-field uf-field-full">
                                        <label class="uf-label">E-mail <span class="uf-req">*</span></label>
                                        <input type="email" name="email" class="uf-input"
                                            id="input-email"
                                            placeholder="joao@empresa.com"
                                            value="<?= e($usuario['email'] ?? '') ?>"
                                            required>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Senha -->
                        <div class="cfg-card">
                            <div class="cfg-card-head">
                                <span class="cfg-card-head-icon"><i class="ti ti-lock"></i></span>
                                <span class="cfg-card-head-title">Senha de Acesso</span>
                            </div>
                            <div class="cfg-card-body">

                                <?php if ($isEdit): ?>
                                    <p class="uf-hint">
                                        <i class="ti ti-info-circle"></i>
                                        Deixe em branco para manter a senha atual.
                                    </p>
                                <?php endif; ?>

                                <div class="uf-row">
                                    <div class="uf-field">
                                        <label class="uf-label">
                                            <?= $isEdit ? 'Nova senha' : 'Senha' ?>
                                            <?php if (!$isEdit): ?><span class="uf-req">*</span><?php endif; ?>
                                        </label>
                                        <div class="uf-pw-wrap">
                                            <input
                                                type="password"
                                                name="senha"
                                                id="campo-senha"
                                                class="uf-input uf-pw-input"
                                                placeholder="Mínimo 6 caracteres"
                                                <?= !$isEdit ? 'required' : '' ?>
                                                autocomplete="new-password">
                                            <button type="button" class="uf-pw-toggle" onclick="toggleSenha('campo-senha', this)">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                        </div>
                                        <div class="uf-pw-strength" id="pw-strength">
                                            <div class="uf-pw-bar" id="bar1"></div>
                                            <div class="uf-pw-bar" id="bar2"></div>
                                            <div class="uf-pw-bar" id="bar3"></div>
                                            <div class="uf-pw-bar" id="bar4"></div>
                                        </div>
                                        <span class="uf-pw-label" id="pw-label"></span>
                                    </div>
                                    <div class="uf-field">
                                        <label class="uf-label">
                                            <?= $isEdit ? 'Confirmar nova senha' : 'Confirmar senha' ?>
                                            <?php if (!$isEdit): ?><span class="uf-req">*</span><?php endif; ?>
                                        </label>
                                        <div class="uf-pw-wrap">
                                            <input
                                                type="password"
                                                name="senha_confirmar"
                                                id="campo-confirmar"
                                                class="uf-input uf-pw-input"
                                                placeholder="Repita a senha"
                                                <?= !$isEdit ? 'required' : '' ?>
                                                autocomplete="new-password">
                                            <button type="button" class="uf-pw-toggle" onclick="toggleSenha('campo-confirmar', this)">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                        </div>
                                        <span class="uf-hint" id="pw-match" style="margin-top:6px;"></span>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <!-- ══════════════════════
                         COLUNA LATERAL
                    ══════════════════════ -->
                    <div class="uf-side">

                        <!-- Acesso -->
                        <div class="cfg-card">
                            <div class="cfg-card-head">
                                <span class="cfg-card-head-icon"><i class="ti ti-shield"></i></span>
                                <span class="cfg-card-head-title">Nível de Acesso</span>
                            </div>
                            <div class="cfg-card-body">

                                <div class="uf-nivel-grid">
                                    <?php
                                    $nivelAtual = $usuario['nivel'] ?? 'operador';
                                    $niveis = [
                                        'admin'    => ['icon' => 'ti-crown',      'cls' => 'uf-nivel-icon-admin',   'desc' => 'Acesso total ao sistema'],
                                        'gerente'  => ['icon' => 'ti-briefcase',  'cls' => 'uf-nivel-icon-gerente', 'desc' => 'Gestão sem configurações'],
                                        'operador' => ['icon' => 'ti-user-check', 'cls' => 'uf-nivel-icon-oper',    'desc' => 'Vendas e operações'],
                                    ];
                                    foreach ($niveis as $val => $info): ?>
                                        <label class="uf-nivel-opt">
                                            <input type="radio" name="nivel" value="<?= $val ?>"
                                                <?= $nivelAtual === $val ? 'checked' : '' ?>>
                                            <div class="uf-nivel-face">
                                                <span class="uf-nivel-icon <?= $info['cls'] ?>">
                                                    <i class="ti <?= $info['icon'] ?>"></i>
                                                </span>
                                                <div class="uf-nivel-info">
                                                    <div class="uf-nivel-name"><?= ucfirst($val) ?></div>
                                                    <div class="uf-nivel-desc"><?= $info['desc'] ?></div>
                                                </div>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <?php if (!empty($perfis)): ?>
                                    <div class="uf-divider">Perfil personalizado</div>
                                    <div class="uf-field">
                                        <label class="uf-label">Perfil de acesso</label>
                                        <div class="uf-select-wrap">
                                            <select name="perfil_id" class="uf-input uf-select">
                                                <option value="">Nenhum perfil específico</option>
                                                <?php foreach ($perfis as $p): ?>
                                                    <option value="<?= $p['id'] ?>"
                                                        <?= (isset($usuario['perfil_id']) && $usuario['perfil_id'] == $p['id']) ? 'selected' : '' ?>>
                                                        <?= e($p['nome']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <i class="ti ti-chevron-down uf-select-arrow"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($isEdit): ?>
                                    <div class="uf-divider">Status</div>
                                    <?php
                                    $logado   = ((int)($usuario['id'] ?? 0) === (int)\App\Core\Session::get('usuario_id'));
                                    $ativoAtual = (bool)($usuario['ativo'] ?? 1);
                                    ?>
                                    <?php if ($logado): ?>
                                        <p class="uf-hint">
                                            <i class="ti ti-info-circle"></i>
                                            Não é possível desativar sua própria conta.
                                        </p>
                                    <?php else: ?>
                                        <label class="uf-status-toggle" id="toggle-status-label">
                                            <input type="checkbox" id="toggle-ativo"
                                                <?= $ativoAtual ? 'checked' : '' ?>
                                                onchange="sincronizarStatus(this)">
                                            <div>
                                                <div class="uf-toggle-label" id="toggle-texto">
                                                    <?= $ativoAtual ? 'Usuário ativo' : 'Usuário inativo' ?>
                                                </div>
                                                <div class="uf-toggle-desc">Clique para alternar</div>
                                            </div>
                                            <div class="uf-toggle-pill <?= $ativoAtual ? 'on' : '' ?>" id="toggle-pill"></div>
                                        </label>
                                        <!-- campo hidden que o controller lê — o checkbox não é enviado quando desmarcado -->
                                        <input type="hidden" name="ativo" id="input-ativo" value="<?= $ativoAtual ? '1' : '0' ?>">
                                    <?php endif; ?>
                                <?php endif; ?>

                            </div>
                        </div>

                        <!-- Ações -->
                        <div class="cfg-card">
                            <div class="cfg-card-body">
                                <div class="uf-actions">
                                    <a href="/config/usuarios" class="uf-btn uf-btn-ghost">Cancelar</a>
                                    <button type="submit" class="uf-btn uf-btn-primary" id="btn-submit">
                                        <i class="ti ti-device-floppy"></i>
                                        <?= $isEdit ? 'Salvar alterações' : 'Criar usuário' ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Meta (só em edição) -->
                        <?php if ($isEdit): ?>
                            <div class="cfg-card">
                                <div class="cfg-card-body">
                                    <div class="uf-meta">
                                        <div class="uf-meta-row">
                                            <span class="uf-meta-label">Cadastrado em</span>
                                            <span class="uf-meta-val"><?= date('d/m/Y', strtotime($usuario['criado_em'])) ?></span>
                                        </div>
                                        <div class="uf-meta-row">
                                            <span class="uf-meta-label">Última atualização</span>
                                            <span class="uf-meta-val"><?= date('d/m/Y H:i', strtotime($usuario['atualizado_em'])) ?></span>
                                        </div>
                                        <div class="uf-meta-row">
                                            <span class="uf-meta-label">ID</span>
                                            <span class="uf-meta-val">#<?= $usuario['id'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // ── Preview de avatar em tempo real ─────────────────────────
    const cores = ['#6366f1', '#22c55e', '#f59e0b', '#ef4444', '#0ea5e9', '#8b5cf6', '#ec4899', '#14b8a6'];

    function atualizarPreview() {
        const nome = document.getElementById('input-nome').value.trim();
        const email = document.getElementById('input-email').value.trim();
        const ini = nome ? nome.charAt(0).toUpperCase() : '?';
        const idx = nome ? Math.abs(nome.split('').reduce((a, c) => a + c.charCodeAt(0), 0)) % cores.length : 0;

        document.getElementById('avatar-ini').textContent = ini;
        document.getElementById('avatar-preview').style.background = nome ? cores[idx] : '#6B7280';
        document.getElementById('preview-nome').textContent = nome || 'Nome do usuário';
        document.getElementById('preview-email').textContent = email || 'email@empresa.com';
    }

    document.getElementById('input-nome')?.addEventListener('input', atualizarPreview);
    document.getElementById('input-email')?.addEventListener('input', atualizarPreview);
    atualizarPreview();

    // ── Força da senha ───────────────────────────────────────────
    function calcularForca(senha) {
        let forca = 0;
        if (senha.length >= 6) forca++;
        if (senha.length >= 10) forca++;
        if (/[A-Z]/.test(senha) && /[a-z]/.test(senha)) forca++;
        if (/[0-9]/.test(senha)) forca++;
        if (/[^A-Za-z0-9]/.test(senha)) forca++;
        return Math.min(4, Math.ceil(forca * 4 / 5));
    }

    document.getElementById('campo-senha')?.addEventListener('input', function() {
        const val = this.value;
        const forca = val ? calcularForca(val) : 0;
        const labels = ['', 'Fraca', 'Razoável', 'Boa', 'Forte'];
        const classes = {
            1: 'active-weak',
            2: 'active-medium',
            3: 'active-strong',
            4: 'active-strong',
        };

        for (let i = 1; i <= 4; i++) {
            const bar = document.getElementById('bar' + i);
            bar.className = 'uf-pw-bar' + (i <= forca ? ' ' + (classes[forca] || '') : '');
        }

        document.getElementById('pw-label').textContent = val ? labels[forca] || '' : '';
        verificarConfirmacao();
    });

    function verificarConfirmacao() {
        const s1 = document.getElementById('campo-senha').value;
        const s2 = document.getElementById('campo-confirmar').value;
        const el = document.getElementById('pw-match');
        if (!s2) {
            el.textContent = '';
            return;
        }
        if (s1 === s2) {
            el.innerHTML = '<i class="ti ti-circle-check" style="color:#16A34A"></i> <span style="color:#16A34A">Senhas conferem</span>';
        } else {
            el.innerHTML = '<i class="ti ti-circle-x" style="color:#DC2626"></i> <span style="color:#DC2626">Senhas não conferem</span>';
        }
    }

    document.getElementById('campo-confirmar')?.addEventListener('input', verificarConfirmacao);

    // ── Mostrar/ocultar senha ────────────────────────────────────
    function toggleSenha(id, btn) {
        const input = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'ti ti-eye-off';
        } else {
            input.type = 'password';
            icon.className = 'ti ti-eye';
        }
    }

    // ── Toggle de status ─────────────────────────────────────────
    function sincronizarStatus(checkbox) {
        const pill = document.getElementById('toggle-pill');
        const texto = document.getElementById('toggle-texto');
        const input = document.getElementById('input-ativo');

        if (checkbox.checked) {
            pill.classList.add('on');
            texto.textContent = 'Usuário ativo';
            input.value = '1';
        } else {
            pill.classList.remove('on');
            texto.textContent = 'Usuário inativo';
            input.value = '0';
        }
    }

    // ── Validação no submit ──────────────────────────────────────
    document.getElementById('form-usuario')?.addEventListener('submit', function(e) {
        const s1 = document.getElementById('campo-senha').value;
        const s2 = document.getElementById('campo-confirmar').value;

        if (s1 && s1 !== s2) {
            e.preventDefault();
            document.getElementById('campo-confirmar').focus();
            document.getElementById('pw-match').innerHTML =
                '<i class="ti ti-circle-x" style="color:#DC2626"></i> <span style="color:#DC2626">As senhas não conferem.</span>';
            return;
        }

        const btn = document.getElementById('btn-submit');
        btn.disabled = true;
        btn.innerHTML = '<i class="ti ti-loader-2"></i> Salvando...';
    });
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>