<?php require VIEW_PATH . '/layouts/header.php'; ?>

<style>
    .cfg-page {
        padding: 28px 32px;
    }

    .cfg-section {
        background: var(--color-background-primary, #fff);
        border: 1px solid var(--color-border-tertiary, #e5e7eb);
        border-radius: 10px;
        margin-bottom: 20px;
        overflow: hidden;
    }

    .cfg-section-head {
        padding: 16px 24px;
        border-bottom: 1px solid var(--color-border-tertiary, #e5e7eb);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cfg-section-head-title {
        font-size: 14px;
        font-weight: 600;
    }

    .cfg-section-head-icon {
        font-size: 16px;
        color: var(--color-primary, #6366f1);
    }

    .cfg-section-body {
        padding: 24px;
    }

    .cfg-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .cfg-grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 16px;
    }

    .cfg-grid-4 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 16px;
    }

    .cfg-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .cfg-field label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--text-tertiary);
    }

    .cfg-input {
        padding: 9px 12px;
        border: 1px solid var(--color-border-tertiary, #e5e7eb);
        border-radius: 7px;
        font-size: 13px;
        font-family: inherit;
        background: var(--color-background-secondary, #f9fafb);
        color: var(--color-text-primary);
        outline: none;
        transition: border-color .15s, background .15s;
        width: 100%;
    }

    .cfg-input:focus {
        border-color: var(--color-primary, #6366f1);
        background: var(--color-background-primary, #fff);
    }

    /* Preview logo */
    .logo-preview-wrap {
        display: flex;
        align-items: flex-start;
        gap: 20px;
    }

    .logo-preview-box {
        width: 120px;
        height: 80px;
        border: 2px dashed var(--color-border-tertiary, #e5e7eb);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: var(--color-background-secondary, #f9fafb);
        flex-shrink: 0;
        cursor: pointer;
        transition: border-color .15s;
    }

    .logo-preview-box:hover {
        border-color: var(--color-primary, #6366f1);
    }

    .logo-preview-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .logo-upload-info {
        font-size: 12px;
        color: var(--text-tertiary);
        line-height: 1.6;
    }

    .logo-upload-info strong {
        color: var(--color-text-primary);
        font-size: 13px;
        display: block;
        margin-bottom: 4px;
    }

    /* Cor primária */
    .color-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .color-swatch {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid var(--color-border-tertiary, #e5e7eb);
        cursor: pointer;
        flex-shrink: 0;
    }

    .color-hex {
        width: 90px;
    }

    /* Rodapé sticky */
    .cfg-form-footer {
        position: sticky;
        bottom: 0;
        background: var(--color-background-primary, #fff);
        border-top: 1px solid var(--color-border-tertiary, #e5e7eb);
        padding: 14px 32px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        z-index: 10;
        margin: 0 -32px;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 20px;
        background: var(--color-primary, #6366f1);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity .15s;
        font-family: inherit;
    }

    .btn-primary:hover {
        opacity: .87;
    }

    .btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 14px;
        background: transparent;
        color: var(--color-text-secondary);
        border: 1px solid var(--color-border-tertiary, #e5e7eb);
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
    }
</style>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">
        <?php require VIEW_PATH . '/layouts/navbar.php'; ?>
        <div class="zf-content cfg-page">

            <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
                <div class="zf-alert zf-alert-success" data-auto-close><i class="ti ti-circle-check"></i> <?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close><i class="ti ti-alert-circle"></i> <?= e($msg) ?></div>
            <?php endif; ?>

            <div style="margin-bottom:24px;">
                <h1 style="font-size:22px;font-weight:600;margin:0 0 3px;">Dados da Empresa</h1>
                <p style="font-size:13px;color:var(--text-tertiary);margin:0;">Informações exibidas no sistema, cupons e relatórios.</p>
            </div>

            <form method="POST" action="/config/empresa" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- ── Identidade ── -->
                <div class="cfg-section">
                    <div class="cfg-section-head">
                        <i class="ti ti-building cfg-section-head-icon"></i>
                        <span class="cfg-section-head-title">Identidade da empresa</span>
                    </div>
                    <div class="cfg-section-body" style="display:flex;flex-direction:column;gap:16px;">

                        <!-- Logo + Cor -->
                        <div style="display:flex;gap:24px;flex-wrap:wrap;align-items:flex-start;">

                            <div class="cfg-field" style="flex:0 0 auto;">
                                <label>Logotipo</label>
                                <div class="logo-preview-wrap">
                                    <div class="logo-preview-box" onclick="document.getElementById('inp-logo').click()">
                                        <?php if (!empty($empresa['logo'])): ?>
                                            <img id="logo-img" src="/uploads/logo/<?= e($empresa['logo']) ?>" alt="">
                                        <?php else: ?>
                                            <i class="ti ti-photo" id="logo-placeholder" style="font-size:24px;color:var(--text-tertiary);opacity:.4;"></i>
                                            <img id="logo-img" src="" alt="" style="display:none;max-width:100%;max-height:100%;object-fit:contain;">
                                        <?php endif; ?>
                                    </div>
                                    <div class="logo-upload-info">
                                        <strong>Clique para enviar</strong>
                                        JPG, PNG, SVG ou WEBP<br>
                                        Máximo 2 MB<br>
                                        <span style="font-size:11px;">Recomendado: 300×100px</span>
                                    </div>
                                </div>
                                <input type="file" id="inp-logo" name="logo" accept=".jpg,.jpeg,.png,.svg,.webp" style="display:none" onchange="previewLogo(this)">
                            </div>

                            <div class="cfg-field" style="flex:0 0 auto;">
                                <label>Cor principal</label>
                                <div class="color-wrap">
                                    <input type="color" id="color-picker" value="<?= e($empresa['cor_primaria'] ?? '#1A1A1A') ?>"
                                        oninput="document.getElementById('color-hex').value=this.value"
                                        class="color-swatch" style="padding:2px;width:46px;height:46px;">
                                    <input type="text" id="color-hex" name="cor_primaria"
                                        value="<?= e($empresa['cor_primaria'] ?? '#1A1A1A') ?>"
                                        class="cfg-input color-hex"
                                        oninput="syncColor(this.value)"
                                        placeholder="#1A1A1A" maxlength="7">
                                </div>
                                <span style="font-size:11px;color:var(--text-tertiary);">Usada no topo, botões e PDV.</span>
                            </div>

                        </div>

                        <div class="cfg-grid-2">
                            <div class="cfg-field">
                                <label>Razão Social</label>
                                <input class="cfg-input" type="text" name="razao_social"
                                    value="<?= e($empresa['razao_social'] ?? '') ?>" placeholder="Empresa Ltda">
                            </div>
                            <div class="cfg-field">
                                <label>Nome Fantasia</label>
                                <input class="cfg-input" type="text" name="nome_fantasia"
                                    value="<?= e($empresa['nome_fantasia'] ?? '') ?>" placeholder="Minha Loja">
                            </div>
                        </div>

                        <div class="cfg-grid-3">
                            <div class="cfg-field">
                                <label>CNPJ</label>
                                <input class="cfg-input" type="text" name="cnpj" id="cnpj"
                                    value="<?= e($empresa['cnpj'] ?? '') ?>" placeholder="00.000.000/0000-00" maxlength="18">
                            </div>
                            <div class="cfg-field">
                                <label>Telefone</label>
                                <input class="cfg-input" type="text" name="telefone"
                                    value="<?= e($empresa['telefone'] ?? '') ?>" placeholder="(00) 0000-0000">
                            </div>
                            <div class="cfg-field">
                                <label>Celular / WhatsApp</label>
                                <input class="cfg-input" type="text" name="celular"
                                    value="<?= e($empresa['celular'] ?? '') ?>" placeholder="(00) 90000-0000">
                            </div>
                        </div>

                        <div class="cfg-grid-2">
                            <div class="cfg-field">
                                <label>E-mail</label>
                                <input class="cfg-input" type="email" name="email"
                                    value="<?= e($empresa['email'] ?? '') ?>" placeholder="contato@empresa.com">
                            </div>
                            <div class="cfg-field">
                                <label>Site</label>
                                <input class="cfg-input" type="text" name="site"
                                    value="<?= e($empresa['site'] ?? '') ?>" placeholder="https://empresa.com">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ── Endereço ── -->
                <div class="cfg-section">
                    <div class="cfg-section-head">
                        <i class="ti ti-map-pin cfg-section-head-icon"></i>
                        <span class="cfg-section-head-title">Endereço</span>
                    </div>
                    <div class="cfg-section-body" style="display:flex;flex-direction:column;gap:16px;">

                        <div style="display:grid;grid-template-columns:140px 1fr auto;gap:16px;align-items:flex-end;">
                            <div class="cfg-field">
                                <label>CEP</label>
                                <input class="cfg-input" type="text" name="cep" id="cep"
                                    value="<?= e($empresa['cep'] ?? '') ?>" placeholder="00000-000" maxlength="9"
                                    oninput="buscarCep(this.value)">
                            </div>
                            <div class="cfg-field">
                                <label>Logradouro</label>
                                <input class="cfg-input" type="text" name="logradouro" id="logradouro"
                                    value="<?= e($empresa['logradouro'] ?? '') ?>" placeholder="Rua, Avenida...">
                            </div>
                            <div class="cfg-field" style="width:90px;">
                                <label>Número</label>
                                <input class="cfg-input" type="text" name="numero"
                                    value="<?= e($empresa['numero'] ?? '') ?>" placeholder="123">
                            </div>
                        </div>

                        <div class="cfg-grid-4">
                            <div class="cfg-field">
                                <label>Complemento</label>
                                <input class="cfg-input" type="text" name="complemento"
                                    value="<?= e($empresa['complemento'] ?? '') ?>" placeholder="Sala, Apto...">
                            </div>
                            <div class="cfg-field">
                                <label>Bairro</label>
                                <input class="cfg-input" type="text" name="bairro" id="bairro"
                                    value="<?= e($empresa['bairro'] ?? '') ?>">
                            </div>
                            <div class="cfg-field">
                                <label>Cidade</label>
                                <input class="cfg-input" type="text" name="cidade" id="cidade"
                                    value="<?= e($empresa['cidade'] ?? '') ?>">
                            </div>
                            <div class="cfg-field">
                                <label>UF</label>
                                <input class="cfg-input" type="text" name="uf" id="uf"
                                    value="<?= e($empresa['uf'] ?? '') ?>" maxlength="2" placeholder="SP"
                                    style="text-transform:uppercase">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer -->
                <div class="cfg-form-footer">
                    <button type="submit" class="btn-primary">
                        <i class="ti ti-device-floppy"></i> Salvar dados da empresa
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

<script>
    // Preview logo
    function previewLogo(input) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('logo-img');
            const ph = document.getElementById('logo-placeholder');
            img.src = e.target.result;
            img.style.display = 'block';
            if (ph) ph.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }

    // Sincroniza input hex com color picker
    function syncColor(val) {
        if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
            document.getElementById('color-picker').value = val;
        }
    }

    // Máscara CNPJ
    document.getElementById('cnpj')?.addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '').slice(0, 14);
        v = v.replace(/^(\d{2})(\d)/, '$1.$2');
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
        v = v.replace(/(\d{4})(\d)/, '$1-$2');
        this.value = v;
    });

    // Busca CEP via ViaCEP
    let cepTimer;

    function buscarCep(val) {
        clearTimeout(cepTimer);
        const cep = val.replace(/\D/g, '');
        if (cep.length !== 8) return;
        cepTimer = setTimeout(async () => {
            try {
                const r = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const d = await r.json();
                if (d.erro) return;
                document.getElementById('logradouro').value = d.logradouro || '';
                document.getElementById('bairro').value = d.bairro || '';
                document.getElementById('cidade').value = d.localidade || '';
                document.getElementById('uf').value = d.uf || '';
            } catch (e) {}
        }, 400);
    }

    // Máscara CEP
    document.getElementById('cep')?.addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '').slice(0, 8);
        if (v.length > 5) v = v.slice(0, 5) + '-' + v.slice(5);
        this.value = v;
    });
</script>