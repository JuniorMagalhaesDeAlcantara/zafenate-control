<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">

        <?php
        $pageTitle  = 'Editar Cliente';
        $breadcrumb = [
            ['label' => 'Dashboard',             'url' => '/dashboard'],
            ['label' => 'Clientes',              'url' => '/clientes'],
            ['label' => e($cliente['nome']),     'url' => '/clientes/' . (int)$cliente['id']],
            ['label' => 'Editar'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <?php if (!empty($erros['geral'])): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close>
                    <i class="ti ti-alert-circle"></i>
                    <?= e(implode(' ', $erros['geral'])) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/clientes/<?= (int)$cliente['id'] ?>/editar" id="form-cliente">
                <?= csrf_field() ?>

                <div style="display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start;">

                    <!-- Coluna principal -->
                    <div style="display:flex; flex-direction:column; gap:20px;">

                        <!-- Dados pessoais -->
                        <div class="zf-table-card" style="padding:20px 24px;">
                            <h3 class="card-section-title">
                                <i class="ti ti-user"></i> Dados Pessoais
                            </h3>

                            <div class="form-group">
                                <label class="form-label">Tipo de Pessoa</label>
                                <div style="display:flex; gap:8px;">
                                    <label class="radio-card <?= ($cliente['tipo_pessoa'] ?? 'fisica') === 'fisica' ? 'selected' : '' ?>" data-tipo="fisica">
                                        <input type="radio" name="tipo_pessoa" value="fisica"
                                            <?= ($cliente['tipo_pessoa'] ?? 'fisica') === 'fisica' ? 'checked' : '' ?>
                                            onchange="alternarTipoPessoa(this.value)">
                                        <i class="ti ti-user"></i> Pessoa Física
                                    </label>
                                    <label class="radio-card <?= ($cliente['tipo_pessoa'] ?? '') === 'juridica' ? 'selected' : '' ?>" data-tipo="juridica">
                                        <input type="radio" name="tipo_pessoa" value="juridica"
                                            <?= ($cliente['tipo_pessoa'] ?? '') === 'juridica' ? 'checked' : '' ?>
                                            onchange="alternarTipoPessoa(this.value)">
                                        <i class="ti ti-building"></i> Pessoa Jurídica
                                    </label>
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                                <div class="form-group" style="grid-column:1/-1;">
                                    <label class="form-label">
                                        Nome <span id="label-nome-pj" style="display:<?= ($cliente['tipo_pessoa'] ?? '') === 'juridica' ? 'inline' : 'none' ?>"> / Razão Social</span>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="nome" class="form-control <?= !empty($erros['nome']) ? 'is-invalid' : '' ?>"
                                        value="<?= e($cliente['nome'] ?? '') ?>"
                                        placeholder="Nome completo"
                                        maxlength="150" required autofocus>
                                    <?php if (!empty($erros['nome'])): ?>
                                        <div class="invalid-feedback"><?= e(implode(', ', $erros['nome'])) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group" id="campo-nome-fantasia"
                                    style="grid-column:1/-1; display:<?= ($cliente['tipo_pessoa'] ?? '') === 'juridica' ? 'block' : 'none' ?>">
                                    <label class="form-label">Nome Fantasia</label>
                                    <input type="text" name="nome_fantasia" class="form-control"
                                        value="<?= e($cliente['nome_fantasia'] ?? '') ?>"
                                        placeholder="Nome fantasia" maxlength="150">
                                </div>

                                <div class="form-group">
                                    <label class="form-label" id="label-cpf-cnpj">
                                        <?= ($cliente['tipo_pessoa'] ?? 'fisica') === 'juridica' ? 'CNPJ' : 'CPF' ?>
                                    </label>
                                    <input type="text" name="cpf_cnpj" id="cpf_cnpj" class="form-control"
                                        value="<?= e($cliente['cpf_cnpj'] ?? '') ?>"
                                        placeholder="<?= ($cliente['tipo_pessoa'] ?? 'fisica') === 'juridica' ? '00.000.000/0001-00' : '000.000.000-00' ?>"
                                        maxlength="18">
                                </div>

                                <div class="form-group" id="campo-ie"
                                    style="display:<?= ($cliente['tipo_pessoa'] ?? '') === 'juridica' ? 'block' : 'none' ?>">
                                    <label class="form-label">Inscrição Estadual</label>
                                    <input type="text" name="ie" class="form-control"
                                        value="<?= e($cliente['ie'] ?? '') ?>"
                                        placeholder="Inscrição Estadual" maxlength="20">
                                </div>

                                <div class="form-group" id="campo-nascimento"
                                    style="display:<?= ($cliente['tipo_pessoa'] ?? 'fisica') === 'fisica' ? 'block' : 'none' ?>">
                                    <label class="form-label">Data de Nascimento</label>
                                    <input type="date" name="data_nascimento" class="form-control"
                                        value="<?= e($cliente['data_nascimento'] ?? '') ?>">
                                </div>

                            </div>
                        </div>

                        <!-- Contato -->
                        <div class="zf-table-card" style="padding:20px 24px;">
                            <h3 class="card-section-title">
                                <i class="ti ti-phone"></i> Contato
                            </h3>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                                <div class="form-group">
                                    <label class="form-label">Celular</label>
                                    <input type="text" name="celular" class="form-control"
                                        value="<?= e($cliente['celular'] ?? '') ?>"
                                        placeholder="(00) 00000-0000" maxlength="20">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Telefone</label>
                                    <input type="text" name="telefone" class="form-control"
                                        value="<?= e($cliente['telefone'] ?? '') ?>"
                                        placeholder="(00) 0000-0000" maxlength="20">
                                </div>

                                <div class="form-group" style="grid-column:1/-1;">
                                    <label class="form-label">E-mail</label>
                                    <input type="email" name="email" class="form-control <?= !empty($erros['email']) ? 'is-invalid' : '' ?>"
                                        value="<?= e($cliente['email'] ?? '') ?>"
                                        placeholder="email@exemplo.com" maxlength="100">
                                    <?php if (!empty($erros['email'])): ?>
                                        <div class="invalid-feedback"><?= e(implode(', ', $erros['email'])) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group" style="grid-column:1/-1;" id="campo-contato-pj">
                                    <label class="form-label">Contato / Responsável</label>
                                    <input type="text" name="contato" class="form-control"
                                        value="<?= e($cliente['contato'] ?? '') ?>"
                                        placeholder="Nome do responsável" maxlength="100">
                                </div>

                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="zf-table-card" style="padding:20px 24px;">
                            <h3 class="card-section-title">
                                <i class="ti ti-map-pin"></i> Endereço
                            </h3>
                            <div style="display:grid; grid-template-columns:140px 1fr 80px; gap:16px;">

                                <div class="form-group">
                                    <label class="form-label">CEP</label>
                                    <input type="text" name="cep" class="form-control"
                                        value="<?= e($cliente['cep'] ?? '') ?>"
                                        placeholder="00000-000" maxlength="9"
                                        oninput="buscarCep(this.value)">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Logradouro</label>
                                    <input type="text" name="logradouro" class="form-control"
                                        value="<?= e($cliente['logradouro'] ?? '') ?>"
                                        placeholder="Rua, Av., etc." maxlength="150">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Número</label>
                                    <input type="text" name="numero" class="form-control"
                                        value="<?= e($cliente['numero'] ?? '') ?>"
                                        placeholder="Nº" maxlength="10">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Complemento</label>
                                    <input type="text" name="complemento" class="form-control"
                                        value="<?= e($cliente['complemento'] ?? '') ?>"
                                        placeholder="Apto, sala..." maxlength="100">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Bairro</label>
                                    <input type="text" name="bairro" class="form-control"
                                        value="<?= e($cliente['bairro'] ?? '') ?>"
                                        placeholder="Bairro" maxlength="80">
                                </div>

                                <div class="form-group" style="grid-column:1/-1;">
                                    <label class="form-label">Cidade</label>
                                    <input type="text" name="cidade" class="form-control"
                                        value="<?= e($cliente['cidade'] ?? '') ?>"
                                        placeholder="Cidade" maxlength="80">
                                </div>

                            </div>
                        </div>

                        <!-- Observações -->
                        <div class="zf-table-card" style="padding:20px 24px;">
                            <h3 class="card-section-title">
                                <i class="ti ti-notes"></i> Observações
                            </h3>
                            <div class="form-group" style="margin-bottom:0">
                                <textarea name="observacoes" class="form-control" rows="3"
                                    placeholder="Informações adicionais sobre o cliente..."
                                    maxlength="500"><?= e($cliente['observacoes'] ?? '') ?></textarea>
                            </div>
                        </div>

                    </div><!-- /col principal -->

                    <!-- Coluna lateral -->
                    <div style="display:flex; flex-direction:column; gap:20px;">

                        <!-- Ações -->
                        <div class="zf-table-card" style="padding:20px 24px;">
                            <h3 class="card-section-title">
                                <i class="ti ti-device-floppy"></i> Salvar
                            </h3>
                            <div style="display:flex; flex-direction:column; gap:10px;">
                                <button type="submit" class="btn btn-primary" style="width:100%;">
                                    <i class="ti ti-check"></i> Salvar Alterações
                                </button>
                                <a href="/clientes/<?= (int)$cliente['id'] ?>" class="btn btn-outline" style="width:100%; text-align:center;">
                                    <i class="ti ti-arrow-left"></i> Cancelar
                                </a>
                            </div>
                        </div>

                        <!-- Crédito -->
                        <div class="zf-table-card" style="padding:20px 24px;">
                            <h3 class="card-section-title">
                                <i class="ti ti-credit-card"></i> Crédito
                            </h3>
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label">Limite de crédito (R$)</label>
                                <input type="number" name="limite_credito" class="form-control"
                                    value="<?= e($cliente['limite_credito'] ?? '0.00') ?>"
                                    placeholder="0,00" min="0" step="0.01">
                                <span class="form-hint">0,00 = sem limite definido</span>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="zf-table-card" style="padding:20px 24px;">
                            <h3 class="card-section-title">
                                <i class="ti ti-toggle-right"></i> Status
                            </h3>
                            <label class="toggle-switch">
                                <input type="hidden" name="ativo" value="0">
                                <input type="checkbox" name="ativo" value="1"
                                    <?= ($cliente['ativo'] ?? 1) ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                                <span class="toggle-label">Cliente ativo</span>
                            </label>
                        </div>

                        <!-- Danger zone -->
                        <div class="zf-table-card" style="padding:20px 24px; border-color:var(--red-light, #fee2e2);">
                            <h3 class="card-section-title" style="color:var(--red);">
                                <i class="ti ti-alert-triangle"></i> Ações
                            </h3>
                            <form method="POST" action="/clientes/<?= (int)$cliente['id'] ?>/status"
                                onsubmit="return confirm('<?= $cliente['ativo'] ? 'Desativar' : 'Reativar' ?> este cliente?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn <?= $cliente['ativo'] ? 'btn-danger-outline' : 'btn-outline' ?>" style="width:100%;">
                                    <i class="ti ti-<?= $cliente['ativo'] ? 'ban' : 'circle-check' ?>"></i>
                                    <?= $cliente['ativo'] ? 'Desativar Cliente' : 'Reativar Cliente' ?>
                                </button>
                            </form>
                        </div>

                    </div><!-- /col lateral -->

                </div><!-- /grid -->

            </form>

        </div><!-- /zf-content -->
    </div><!-- /zf-main -->
</div><!-- /zf-layout -->

<script>
    function alternarTipoPessoa(tipo) {
        const pj = tipo === 'juridica';

        document.getElementById('campo-nome-fantasia').style.display = pj ? 'block' : 'none';
        document.getElementById('campo-ie').style.display = pj ? 'block' : 'none';
        document.getElementById('campo-nascimento').style.display = pj ? 'none' : 'block';
        document.getElementById('label-nome-pj').style.display = pj ? 'inline' : 'none';
        document.getElementById('label-cpf-cnpj').textContent = pj ? 'CNPJ' : 'CPF';

        const cpfEl = document.getElementById('cpf_cnpj');
        cpfEl.placeholder = pj ? '00.000.000/0001-00' : '000.000.000-00';

        document.querySelectorAll('.radio-card').forEach(el => {
            el.classList.toggle('selected', el.dataset.tipo === tipo);
        });
    }

    function buscarCep(raw) {
        const cep = raw.replace(/\D/g, '');
        if (cep.length !== 8) return;

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(r => r.json())
            .then(d => {
                if (d.erro) return;
                document.querySelector('[name=logradouro]').value = d.logradouro || '';
                document.querySelector('[name=bairro]').value = d.bairro || '';
                document.querySelector('[name=cidade]').value = d.localidade || '';
                const ufSel = document.querySelector('[name=uf]');
                if (ufSel) ufSel.value = d.uf || '';
                document.querySelector('[name=numero]').focus();
            })
            .catch(() => {});
    }
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>