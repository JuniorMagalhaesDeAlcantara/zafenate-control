<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">
        <?php require VIEW_PATH . '/layouts/navbar.php'; ?>

        <div class="zf-content">

            <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close><i class="ti ti-alert-circle"></i> <?= e($msg) ?></div>
            <?php endif; ?>

            <div style="max-width:680px;">
                <div class="zf-table-card" style="padding:28px;">

                    <div style="margin-bottom:24px;">
                        <h2 style="font-size:17px;font-weight:500;margin:0 0 4px;">Nova Conta a Receber</h2>
                        <p style="font-size:13px;color:var(--text-tertiary);margin:0;">Preencha os dados da receita.</p>
                    </div>

                    <form action="/financeiro/receber/criar" method="POST">
                        <?= csrf_field() ?>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                            <div style="grid-column:1/-1;">
                                <label class="zf-label">Descrição <span style="color:var(--color-danger,#ef4444);">*</span></label>
                                <input type="text" name="descricao" class="zf-input" style="width:100%;" required
                                    placeholder="Ex: Mensalidade, Serviço X, Parcela...">
                            </div>

                            <div>
                                <label class="zf-label">Valor (R$) <span style="color:var(--color-danger,#ef4444);">*</span></label>
                                <input type="text" name="valor" id="inp-valor" class="zf-input" style="width:100%;" required
                                    placeholder="0,00" autocomplete="off">
                            </div>

                            <div>
                                <label class="zf-label">Vencimento <span style="color:var(--color-danger,#ef4444);">*</span></label>
                                <input type="date" name="vencimento" class="zf-input" style="width:100%;" required
                                    value="<?= date('Y-m-d') ?>">
                            </div>

                            <div>
                                <label class="zf-label">Categoria</label>
                                <select name="categoria_id" class="zf-input" style="width:100%;">
                                    <option value="">Sem categoria</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= e($cat['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="zf-label">Cliente</label>
                                <select name="cliente_id" class="zf-input" style="width:100%;">
                                    <option value="">Nenhum</option>
                                    <?php foreach ($clientes as $cl): ?>
                                        <option value="<?= $cl['id'] ?>"><?= e($cl['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="zf-label">Nº Documento</label>
                                <input type="text" name="documento" class="zf-input" style="width:100%;" placeholder="Opcional">
                            </div>

                            <div>
                                <label class="zf-label">Forma de recebimento</label>
                                <select name="forma_recebimento" class="zf-input" style="width:100%;">
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

                            <div style="grid-column:1/-1;">
                                <label class="zf-label">Observação</label>
                                <textarea name="observacao" class="zf-input" style="width:100%;height:80px;resize:vertical;"
                                    placeholder="Notas internas..."></textarea>
                            </div>

                        </div>

                        <div style="display:flex;gap:12px;margin-top:24px;justify-content:flex-end;">
                            <a href="/financeiro/receber" class="btn btn-sm btn-outline">Cancelar</a>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="ti ti-plus"></i> Cadastrar
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

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