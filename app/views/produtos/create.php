<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">

    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>

    <div class="zf-main">

        <?php
        $pageTitle = !empty($produto) ? 'Editar Produto' : 'Novo Produto';
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Produtos',  'url' => '/produtos'],
            ['label' => !empty($produto) ? 'Editar' : 'Cadastro', 'url' => '#'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <?php if ($success = \App\Core\Session::getFlash('success')): ?>
                <div class="zf-alert zf-alert-success" data-auto-close>
                    <i class="ti ti-circle-check"></i>
                    <?= e($success) ?>
                </div>
            <?php endif; ?>
            <?php if ($error = \App\Core\Session::getFlash('error')): ?>
                <div class="zf-alert zf-alert-danger" data-auto-close>
                    <i class="ti ti-alert-circle"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <div class="zf-table-card" style="padding: 24px;">
                <form action="<?= !empty($produto) ? '/produtos/' . $produto['id'] . '/editar' : '/produtos/criar' ?>" method="POST">

                    <?= $csrf ?? '' ?>

                    <div class="zf-form-grid cols-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">

                        <div class="form-group" style="grid-column: span 2; display: flex; flex-direction: column; gap: 6px;">
                            <label class="form-label" style="font-weight: 500; font-size: 14px;">Nome do Produto<span style="color: var(--color-danger)">*</span></label>
                            <input type="text" name="nome" class="form-control" placeholder="Ex: Coca-Cola Lata 350ml" value="<?= e($produto['nome'] ?? '') ?>" required style="width: 100%;">
                        </div>

                        <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                            <label class="form-label" style="font-weight: 500; font-size: 14px;">Código Interno</label>
                            <input type="text" class="form-control" value="<?= e($codigo ?? '') ?>" disabled style="background-color: var(--background-neutral, #f5f5f5); cursor: not-allowed; width: 100%;">
                        </div>

                        <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                            <label class="form-label" style="font-weight: 500; font-size: 14px;">Código de Barras / EAN</label>
                            <input type="text" name="codigo_barras" class="form-control" placeholder="Bipe ou digite o código" value="<?= e($produto['codigo_barras'] ?? '') ?>" style="width: 100%;">
                        </div>

                        <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                            <label class="form-label" style="font-weight: 500; font-size: 14px;">Categoria</label>
                            <select name="categoria_id" class="form-control" style="width: 100%;">
                                <option value="">-- Selecione uma Categoria --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (isset($produto['categoria_id']) && $produto['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= e($cat['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                            <label class="form-label" style="font-weight: 500; font-size: 14px;">Unidade de Medida<span style="color: var(--color-danger)">*</span></label>
                            <select name="unidade_id" class="form-control" required style="width: 100%;">
                                <?php foreach ($unidades as $un): ?>
                                    <option value="<?= $un['id'] ?>" <?= (isset($produto['unidade_id']) && $produto['unidade_id'] == $un['id']) ? 'selected' : (empty($produto) && $un['sigla'] === 'UN' ? 'selected' : '') ?>>
                                        <?= e($un['nome']) ?> (<?= e($un['sigla']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                            <label class="form-label" style="font-weight: 500; font-size: 14px;">Preço de Custo (R$)</label>
                            <input type="number" step="0.01" name="preco_custo" class="form-control" placeholder="0,00" value="<?= e($produto['preco_custo'] ?? '0.00') ?>" style="width: 100%;">
                        </div>

                        <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                            <label class="form-label" style="font-weight: 500; font-size: 14px;">Preço de Venda (R$)<span style="color: var(--color-danger)">*</span></label>
                            <input type="number" step="0.01" name="preco_venda" class="form-control" placeholder="0,00" value="<?= e($produto['preco_venda'] ?? '0.00') ?>" required style="width: 100%;">
                        </div>

                        <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                            <label for="estoque_atual">Estoque Atual</label>
                            <input
                                type="number"
                                name="estoque_atual"
                                id="estoque_atual"
                                step="0.001"
                                /* Use a mesma classe que você usa no input de Preço de Venda (ex: class="zf-input" ) */
                                class="zf-input"
                                value="<?= isset($produto) ? $produto['estoque_atual'] : '0.000' ?>"
                                <?= isset($produto) ? 'readonly style="background-color: #f5f5f5; border: 1px solid #e0e0e0; color: #6c757d; cursor: not-allowed;"' : '' ?>>
                            <?php if (isset($produto)): ?>
                                <small style="color: #6c757d; font-size: 0.85rem; margin-top: 4px; display: block;">
                                    Para alterar o estoque, utilize o módulo de Movimentações.
                                </small>
                            <?php endif; ?>
                        </div>

                        <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                            <label class="form-label" style="font-weight: 500; font-size: 14px;">Estoque Mínimo (Alerta)</label>
                            <input type="number" step="0.001" name="estoque_minimo" class="form-control" placeholder="0.000" value="<?= e($produto['estoque_minimo'] ?? '0.000') ?>" style="width: 100%;">
                        </div>

                        <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                            <label class="form-label" style="font-weight: 500; font-size: 14px;">Estoque Máximo</label>
                            <input type="number" step="0.001" name="estoque_maximo" class="form-control" placeholder="0.000" value="<?= e($produto['estoque_maximo'] ?? '') ?>" style="width: 100%;">
                        </div>

                        <div class="form-group" style="grid-column: span 3; display: flex; flex-direction: column; gap: 6px;">
                            <label class="form-label" style="font-weight: 500; font-size: 14px;">Descrição / Observações Complementares</label>
                            <textarea name="descricao" class="form-control" rows="3" placeholder="Informações adicionais sobre o produto..." style="width: 100%; resize: vertical; min-height: 80px;"><?= e($produto['descricao'] ?? '') ?></textarea>
                        </div>

                    </div>

                    <div class="d-flex align-center gap-12" style="margin-top: 24px; display: flex; align-items: center; gap: 12px; justify-content: flex-end;">
                        <a href="/produtos" class="btn btn-outline">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy"></i> Salvar Produto
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>