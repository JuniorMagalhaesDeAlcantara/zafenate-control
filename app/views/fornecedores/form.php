<?php
$editando = !empty($fornecedor);
$action   = $editando
    ? '/fornecedores/' . $fornecedor['id'] . '/editar'
    : '/fornecedores/criar';
$v = fn(string $campo, mixed $default = '') => e($fornecedor[$campo] ?? $default);
?>

<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>

    <div class="zf-main">
        <?php
        $pageTitle  = $editando ? 'Editar Fornecedor' : 'Novo Fornecedor';
        $breadcrumb = [
            ['label' => 'Dashboard',    'url' => '/dashboard'],
            ['label' => 'Fornecedores', 'url' => '/fornecedores'],
            ['label' => $pageTitle,     'url' => '#'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close>
                    <i class="ti ti-alert-circle"></i> <?= e($msg) ?>
                </div>
            <?php endif; ?>

            <form action="<?= $action ?>" method="POST" id="form-fornecedor">
                <?= csrf_field() ?>

                <!-- Abas de navegação -->
                <div style="display:flex;gap:0;border-bottom:1px solid var(--border);margin-bottom:20px">
                    <?php
                    $abas = [
                        'basico'     => ['icon' => 'ti-building', 'label' => 'Dados Básicos'],
                        'contato'    => ['icon' => 'ti-phone',    'label' => 'Contato'],
                        'endereco'   => ['icon' => 'ti-map-pin',  'label' => 'Endereço'],
                        'financeiro' => ['icon' => 'ti-coin',     'label' => 'Financeiro'],
                        'comercial'  => ['icon' => 'ti-star',     'label' => 'Comercial'],
                    ];
                    foreach ($abas as $id => $aba): ?>
                        <button type="button"
                            class="tab-btn"
                            data-tab="<?= $id ?>"
                            style="display:inline-flex;align-items:center;gap:6px;padding:10px 16px;
                                   border:none;background:transparent;font-family:inherit;
                                   font-size:13px;font-weight:500;cursor:pointer;
                                   color:var(--text-tertiary);border-bottom:2px solid transparent;
                                   margin-bottom:-1px;transition:color .15s,border-color .15s"
                            onclick="ativarAba('<?= $id ?>')">
                            <i class="ti <?= $aba['icon'] ?>"></i>
                            <?= $aba['label'] ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- ── Aba: Dados Básicos ── -->
                <div id="tab-basico" class="tab-pane">
                    <div class="zf-form-card">
                        <div class="zf-form-grid">

                            <div class="form-group full">
                                <label class="form-label">Tipo de Pessoa <span>*</span></label>
                                <div style="display:flex;gap:10px">
                                    <?php foreach (['juridica' => 'Pessoa Jurídica (CNPJ)', 'fisica' => 'Pessoa Física (CPF)'] as $val => $label): ?>
                                        <label style="display:flex;align-items:center;gap:7px;cursor:pointer;
                                                       padding:9px 16px;border:1px solid var(--border);
                                                       border-radius:var(--radius-sm);font-size:13px;
                                                       transition:border-color .15s">
                                            <input type="radio" name="tipo_pessoa" value="<?= $val ?>"
                                                <?= ($fornecedor['tipo_pessoa'] ?? 'juridica') === $val ? 'checked' : '' ?>
                                                onchange="alternarTipo(this.value)"
                                                style="accent-color:var(--primary)">
                                            <?= $label ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Razão Social <span>*</span></label>
                                <input type="text" name="razao_social" class="form-control"
                                    value="<?= $v('razao_social') ?>" required maxlength="150"
                                    placeholder="Nome completo ou razão social">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nome Fantasia</label>
                                <input type="text" name="nome_fantasia" class="form-control"
                                    value="<?= $v('nome_fantasia') ?>" maxlength="150"
                                    placeholder="Como é conhecido no mercado">
                            </div>

                            <div class="form-group">
                                <label class="form-label" id="label-doc">CNPJ</label>
                                <input type="text" name="cnpj_cpf" class="form-control"
                                    id="campo-doc"
                                    value="<?= !empty($fornecedor['cnpj_cpf']) ? formatarDocumento($fornecedor['cnpj_cpf']) : '' ?>"
                                    maxlength="18" placeholder="00.000.000/0000-00"
                                    oninput="mascaraDoc(this)">
                            </div>

                            <div class="form-group" id="grupo-ie">
                                <label class="form-label">Inscrição Estadual</label>
                                <input type="text" name="ie" class="form-control"
                                    value="<?= $v('ie') ?>" maxlength="20"
                                    placeholder="Opcional">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="ativo" class="form-control">
                                    <option value="1" <?= ($fornecedor['ativo'] ?? 1) == 1 ? 'selected' : '' ?>>Ativo</option>
                                    <option value="0" <?= ($fornecedor['ativo'] ?? 1) == 0 ? 'selected' : '' ?>>Inativo</option>
                                </select>
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Observações Gerais</label>
                                <textarea name="observacoes" class="form-control" rows="3"
                                    placeholder="Informações adicionais sobre o fornecedor"><?= $v('observacoes') ?></textarea>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ── Aba: Contato ── -->
                <div id="tab-contato" class="tab-pane" style="display:none">
                    <div class="zf-form-card">
                        <div class="zf-form-grid">

                            <div class="form-group">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="telefone" class="form-control"
                                    value="<?= $v('telefone') ?>" maxlength="20"
                                    placeholder="(00) 0000-0000">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Celular</label>
                                <input type="text" name="celular" class="form-control"
                                    value="<?= $v('celular') ?>" maxlength="20"
                                    placeholder="(00) 00000-0000">
                            </div>

                            <div class="form-group">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" name="whatsapp" class="form-control"
                                    value="<?= $v('whatsapp') ?>" maxlength="20"
                                    placeholder="(00) 00000-0000">
                            </div>

                            <div class="form-group">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?= $v('email') ?>" maxlength="100"
                                    placeholder="contato@fornecedor.com.br">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Site</label>
                                <input type="url" name="site" class="form-control"
                                    value="<?= $v('site') ?>" maxlength="150"
                                    placeholder="https://www.fornecedor.com.br">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Pessoa de Contato</label>
                                <input type="text" name="contato" class="form-control"
                                    value="<?= $v('contato') ?>" maxlength="100"
                                    placeholder="Nome do responsável pelo contato">
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ── Aba: Endereço ── -->
                <div id="tab-endereco" class="tab-pane" style="display:none">
                    <div class="zf-form-card">
                        <div class="zf-form-grid">

                            <div class="form-group">
                                <label class="form-label">CEP</label>
                                <input type="text" name="cep" id="cep" class="form-control"
                                    value="<?= $v('cep') ?>" maxlength="9"
                                    placeholder="00000-000"
                                    oninput="mascaraCep(this)"
                                    onblur="buscarCep(this.value)">
                            </div>

                            <div class="form-group">
                                <label class="form-label">UF</label>
                                <input type="text" name="uf" id="uf" class="form-control"
                                    value="<?= $v('uf') ?>" maxlength="2"
                                    placeholder="SP" style="text-transform:uppercase">
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Logradouro</label>
                                <input type="text" name="logradouro" id="logradouro"
                                    class="form-control"
                                    value="<?= $v('logradouro') ?>" maxlength="150">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Número</label>
                                <input type="text" name="numero" class="form-control"
                                    value="<?= $v('numero') ?>" maxlength="10">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Complemento</label>
                                <input type="text" name="complemento" class="form-control"
                                    value="<?= $v('complemento') ?>" maxlength="100">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Bairro</label>
                                <input type="text" name="bairro" id="bairro" class="form-control"
                                    value="<?= $v('bairro') ?>" maxlength="80">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Cidade</label>
                                <input type="text" name="cidade" id="cidade" class="form-control"
                                    value="<?= $v('cidade') ?>" maxlength="80">
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ── Aba: Financeiro ── -->
                <div id="tab-financeiro" class="tab-pane" style="display:none">
                    <div class="zf-form-card">
                        <div class="zf-form-grid">

                            <div class="form-group">
                                <label class="form-label">Forma de Pagamento Padrão</label>
                                <select name="forma_pagamento" class="form-control">
                                    <option value="">Selecione...</option>
                                    <?php
                                    $formas = [
                                        'boleto'    => 'Boleto',
                                        'pix'       => 'PIX',
                                        'deposito'  => 'Depósito',
                                        'cartao'    => 'Cartão',
                                        'dinheiro'  => 'Dinheiro',
                                        'cheque'    => 'Cheque',
                                        'outros'    => 'Outros',
                                    ];
                                    foreach ($formas as $val => $label): ?>
                                        <option value="<?= $val ?>"
                                            <?= ($fornecedor['forma_pagamento'] ?? '') === $val ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Prazo Padrão (dias)</label>
                                <input type="number" name="prazo_pagamento" class="form-control"
                                    value="<?= $v('prazo_pagamento') ?>" min="0" max="365"
                                    placeholder="Ex: 30">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Limite de Crédito (R$)</label>
                                <input type="text" name="limite_credito" class="form-control"
                                    value="<?= !empty($fornecedor['limite_credito']) ? number_format($fornecedor['limite_credito'], 2, ',', '.') : '' ?>"
                                    placeholder="0,00">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Chave Pix</label>
                                <input type="text" name="chave_pix" class="form-control"
                                    value="<?= $v('chave_pix') ?>" maxlength="150"
                                    placeholder="CPF, CNPJ, e-mail, telefone ou chave aleatória">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Banco</label>
                                <input type="text" name="banco" class="form-control"
                                    value="<?= $v('banco') ?>" maxlength="80"
                                    placeholder="Nome do banco">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Agência</label>
                                <input type="text" name="agencia" class="form-control"
                                    value="<?= $v('agencia') ?>" maxlength="20">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Conta</label>
                                <input type="text" name="conta" class="form-control"
                                    value="<?= $v('conta') ?>" maxlength="30">
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Observações Financeiras</label>
                                <textarea name="obs_financeiras" class="form-control" rows="3"
                                    placeholder="Ex: melhor preço no boleto, desconto acima de R$1000..."><?= $v('obs_financeiras') ?></textarea>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ── Aba: Comercial ── -->
                <div id="tab-comercial" class="tab-pane" style="display:none">
                    <div class="zf-form-card">
                        <div class="zf-form-grid">

                            <div class="form-group">
                                <label class="form-label">Prazo de Entrega (dias)</label>
                                <input type="number" name="prazo_entrega" class="form-control"
                                    value="<?= $v('prazo_entrega') ?>" min="0" max="365"
                                    placeholder="Ex: 7">
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Observações Internas</label>
                                <textarea name="obs_internas" class="form-control" rows="3"
                                    placeholder="Ex: só atende até 17h, entrega atrasada, melhor preço no boleto..."><?= $v('obs_internas') ?></textarea>
                            </div>

                            <!-- Avaliação -->
                            <div class="form-group full">
                                <label class="form-label" style="margin-bottom:12px">Avaliação do Fornecedor</label>
                                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
                                    <?php
                                    $criterios = [
                                        'avaliacao_prazo'       => 'Pontualidade no Prazo',
                                        'avaliacao_qualidade'   => 'Qualidade dos Produtos',
                                        'avaliacao_atendimento' => 'Qualidade do Atendimento',
                                    ];
                                    foreach ($criterios as $campo => $label): ?>
                                        <div>
                                            <div style="font-size:12px;font-weight:500;color:var(--text-secondary);margin-bottom:8px">
                                                <?= $label ?>
                                            </div>
                                            <div style="display:flex;gap:4px">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <label style="cursor:pointer;font-size:20px;color:var(--text-tertiary);
                                                                   transition:color .12s;line-height:1"
                                                        title="<?= $i ?> estrela<?= $i > 1 ? 's' : '' ?>">
                                                        <input type="radio"
                                                            name="<?= $campo ?>"
                                                            value="<?= $i ?>"
                                                            style="display:none"
                                                            <?= ($fornecedor[$campo] ?? 0) == $i ? 'checked' : '' ?>
                                                            onchange="colorirEstrelas('<?= $campo ?>', <?= $i ?>)">
                                                        <span class="estrela-<?= $campo ?>"
                                                            data-val="<?= $i ?>"
                                                            style="color:<?= ($fornecedor[$campo] ?? 0) >= $i ? '#F59E0B' : 'var(--text-tertiary)' ?>"
                                                            onmouseover="hoverEstrelas('<?= $campo ?>', <?= $i ?>)"
                                                            onmouseout="restoreEstrelas('<?= $campo ?>')">★</span>
                                                    </label>
                                                <?php endfor; ?>
                                                <!-- botão limpar -->
                                                <button type="button"
                                                    style="font-size:11px;color:var(--text-tertiary);background:none;border:none;cursor:pointer;margin-left:4px"
                                                    onclick="limparAvaliacao('<?= $campo ?>')">limpar</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Rodapé fixo com ações -->
                <div style="display:flex;align-items:center;justify-content:space-between;
                             margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
                    <a href="/fornecedores" class="btn btn-outline">
                        <i class="ti ti-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy"></i>
                        <?= $editando ? 'Salvar alterações' : 'Cadastrar fornecedor' ?>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    // ── Abas ──
    function ativarAba(id) {
        document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.style.color = 'var(--text-tertiary)';
            b.style.borderColor = 'transparent';
        });
        document.getElementById('tab-' + id).style.display = 'block';
        const btn = document.querySelector('[data-tab="' + id + '"]');
        if (btn) {
            btn.style.color = 'var(--text-primary)';
            btn.style.borderColor = 'var(--primary)';
        }
    }
    ativarAba('basico');

    // ── Tipo de pessoa ──
    function alternarTipo(tipo) {
        const doc = document.getElementById('campo-doc');
        const label = document.getElementById('label-doc');
        const ie = document.getElementById('grupo-ie');
        if (tipo === 'fisica') {
            label.textContent = 'CPF';
            doc.placeholder = '000.000.000-00';
            doc.maxLength = 14;
            ie.style.display = 'none';
        } else {
            label.textContent = 'CNPJ';
            doc.placeholder = '00.000.000/0000-00';
            doc.maxLength = 18;
            ie.style.display = '';
        }
    }
    // Init
    const tipoAtual = document.querySelector('input[name="tipo_pessoa"]:checked');
    if (tipoAtual) alternarTipo(tipoAtual.value);

    // ── Máscara CPF/CNPJ ──
    function mascaraDoc(input) {
        let v = input.value.replace(/\D/g, '');
        const tipo = document.querySelector('input[name="tipo_pessoa"]:checked')?.value;
        if (tipo === 'fisica') {
            v = v.slice(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        } else {
            v = v.slice(0, 14);
            v = v.replace(/^(\d{2})(\d)/, '$1.$2')
                .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/\.(\d{3})(\d)/, '.$1/$2')
                .replace(/(\d{4})(\d)/, '$1-$2');
        }
        input.value = v;
    }

    // ── Máscara CEP ──
    function mascaraCep(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 8);
        if (v.length > 5) v = v.slice(0, 5) + '-' + v.slice(5);
        input.value = v;
    }

    // ── Busca CEP via ViaCEP ──
    async function buscarCep(cep) {
        const c = cep.replace(/\D/g, '');
        if (c.length !== 8) return;
        try {
            const r = await fetch('https://viacep.com.br/ws/' + c + '/json/');
            const d = await r.json();
            if (!d.erro) {
                document.getElementById('logradouro').value = d.logradouro || '';
                document.getElementById('bairro').value = d.bairro || '';
                document.getElementById('cidade').value = d.localidade || '';
                document.getElementById('uf').value = d.uf || '';
            }
        } catch (e) {
            /* silencia */ }
    }

    // ── Avaliação por estrelas ──
    function colorirEstrelas(campo, val) {
        document.querySelectorAll('.estrela-' + campo).forEach(el => {
            el.style.color = parseInt(el.dataset.val) <= val ? '#F59E0B' : 'var(--text-tertiary)';
        });
    }

    function hoverEstrelas(campo, val) {
        document.querySelectorAll('.estrela-' + campo).forEach(el => {
            el.style.color = parseInt(el.dataset.val) <= val ? '#FBBF24' : 'var(--text-tertiary)';
        });
    }

    function restoreEstrelas(campo) {
        const checked = document.querySelector('input[name="' + campo + '"]:checked');
        const val = checked ? parseInt(checked.value) : 0;
        colorirEstrelas(campo, val);
    }

    function limparAvaliacao(campo) {
        document.querySelectorAll('input[name="' + campo + '"]').forEach(r => r.checked = false);
        colorirEstrelas(campo, 0);
    }
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>