<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">

        <?php
        $pageTitle  = 'Nova Conta a Pagar';
        $breadcrumb = [
            ['label' => 'Dashboard',      'url' => '/dashboard'],
            ['label' => 'Contas a Pagar', 'url' => '/financeiro/pagar'],
            ['label' => 'Nova',           'url' => '#'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close>
                    <i class="ti ti-alert-circle"></i> <?= e($msg) ?>
                </div>
            <?php endif; ?>

            <div style="max-width:660px">

                <form action="/financeiro/pagar/criar" method="POST">
                    <?= csrf_field() ?>

                    <div class="zf-form-card" style="padding:0;overflow:hidden">

                        <!-- Cabeçalho -->
                        <div style="padding:18px 20px;border-bottom:1px solid var(--border)">
                            <div style="font-size:15px;font-weight:500;margin-bottom:2px">
                                Nova Conta a Pagar
                            </div>
                            <div style="font-size:12px;color:var(--text-tertiary)">
                                Preencha os dados da despesa
                            </div>
                        </div>

                        <!-- Corpo -->
                        <div style="padding:20px">

                            <!-- Descrição -->
                            <div class="form-group mb-16">
                                <label class="form-label">
                                    Descrição <span style="color:var(--color-danger)">*</span>
                                </label>
                                <input type="text" name="descricao" class="form-control" required
                                    placeholder="Ex: Aluguel, Energia, Fornecedor X...">
                            </div>

                            <!-- Valor + Vencimento -->
                            <div class="zf-form-grid mb-16">
                                <div class="form-group">
                                    <label class="form-label">
                                        Valor (R$) <span style="color:var(--color-danger)">*</span>
                                    </label>
                                    <input type="text" name="valor" id="inp-valor"
                                        class="form-control" required
                                        placeholder="0,00" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        Vencimento <span style="color:var(--color-danger)">*</span>
                                    </label>
                                    <input type="date" name="vencimento" class="form-control" required
                                        value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <!-- Divisor classificação -->
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
                                <span style="font-size:10px;font-weight:600;text-transform:uppercase;
                                             letter-spacing:.08em;color:var(--text-tertiary);white-space:nowrap">
                                    Classificação
                                </span>
                                <div style="flex:1;height:1px;background:var(--border)"></div>
                            </div>

                            <!-- Categoria + Fornecedor -->
                            <div class="zf-form-grid mb-16">
                                <div class="form-group">
                                    <label class="form-label">Categoria</label>
                                    <select name="categoria_id" class="form-control">
                                        <option value="">Sem categoria</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"><?= e($cat['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Fornecedor</label>
                                    <select name="fornecedor_id" class="form-control">
                                        <option value="">Nenhum</option>
                                        <?php foreach ($fornecedores as $f): ?>
                                            <option value="<?= $f['id'] ?>"><?= e($f['razao_social']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Documento + Forma -->
                            <div class="zf-form-grid mb-16">
                                <div class="form-group">
                                    <label class="form-label">Nº Documento / NF</label>
                                    <input type="text" name="documento" class="form-control"
                                        placeholder="Opcional">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Forma de pagamento</label>
                                    <select name="forma_pagamento" class="form-control">
                                        <option value="">Selecione...</option>
                                        <option value="dinheiro">Dinheiro</option>
                                        <option value="pix">PIX</option>
                                        <option value="boleto">Boleto</option>
                                        <option value="transferencia">Transferência</option>
                                        <option value="cartao_credito">Cartão de Crédito</option>
                                        <option value="cartao_debito">Cartão de Débito</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Observação -->
                            <div class="form-group">
                                <label class="form-label">Observação</label>
                                <textarea name="observacao" class="form-control"
                                    style="height:76px;resize:vertical"
                                    placeholder="Notas internas..."></textarea>
                            </div>

                        </div>

                        <!-- Rodapé -->
                        <div style="padding:14px 20px;border-top:1px solid var(--border);
                                     display:flex;align-items:center;justify-content:space-between">
                            <span style="font-size:11px;color:var(--text-tertiary);
                                          display:flex;align-items:center;gap:4px">
                                <i class="ti ti-info-circle" style="font-size:13px"></i>
                                Campos com
                                <span style="color:var(--color-danger);margin:0 1px">*</span>
                                são obrigatórios
                            </span>
                            <div style="display:flex;gap:8px">
                                <a href="/financeiro/pagar" class="btn btn-outline btn-sm">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="ti ti-device-floppy"></i>
                                    Cadastrar
                                </button>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('inp-valor').addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '');
        if (!v) {
            this.value = '';
            return;
        }
        v = (parseInt(v, 10) / 100).toFixed(2);
        this.value = v.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    });
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>