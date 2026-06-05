<?php require VIEW_PATH . '/layouts/header.php'; ?>

<style>
    .cfg-page {
        padding: 28px 32px;
    }

    .perm-card {
        background: var(--color-background-primary, #fff);
        border: 1px solid var(--color-border-tertiary, #e5e7eb);
        border-radius: 10px;
        overflow: hidden;
    }

    .perm-card-head {
        padding: 16px 20px;
        border-bottom: 1px solid var(--color-border-tertiary, #e5e7eb);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Tabela de permissões */
    .perm-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .perm-table th,
    .perm-table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--color-border-tertiary, #e5e7eb);
    }

    .perm-table tbody tr:last-child td {
        border-bottom: none;
    }

    .perm-table tbody tr:hover td {
        background: var(--color-background-secondary, #f9fafb);
    }

    .perm-table thead th {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--text-tertiary);
        background: var(--color-background-secondary, #f9fafb);
        text-align: center;
    }

    .perm-table thead th:first-child {
        text-align: left;
        width: 160px;
    }

    .perm-table thead .th-perfil-nome {
        min-width: 130px;
    }

    .perm-modulo {
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    /* Célula de permissão — 3 estados */
    .perm-cell {
        text-align: center;
    }

    .perm-select {
        appearance: none;
        -webkit-appearance: none;
        width: 90px;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid transparent;
        cursor: pointer;
        text-align: center;
        font-family: inherit;
        outline: none;
        transition: background .15s, color .15s;
    }

    .perm-select.val-true {
        background: #f0fdf4;
        color: #16a34a;
        border-color: #bbf7d0;
    }

    .perm-select.val-view {
        background: #dbeafe;
        color: #2563eb;
        border-color: #bfdbfe;
    }

    .perm-select.val-false {
        background: #f1f5f9;
        color: #94a3b8;
        border-color: #e2e8f0;
    }

    /* Input nome do perfil */
    .perfil-nome-input {
        border: none;
        background: transparent;
        font-size: 13px;
        font-weight: 600;
        color: var(--color-text-primary);
        font-family: inherit;
        width: 100%;
        outline: none;
        border-bottom: 1px dashed transparent;
        transition: border-color .15s;
        padding: 2px 0;
    }

    .perfil-nome-input:hover,
    .perfil-nome-input:focus {
        border-color: var(--color-border-tertiary);
    }

    /* Legenda */
    .perm-legend {
        display: flex;
        gap: 14px;
        font-size: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .perm-legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .perm-legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 3px;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
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

            <div style="margin-bottom:24px;display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;">
                <div>
                    <h1 style="font-size:22px;font-weight:600;margin:0 0 3px;">Perfis de Acesso</h1>
                    <p style="font-size:13px;color:var(--text-tertiary);margin:0;">
                        Defina o que cada perfil pode ver e fazer no sistema.
                    </p>
                </div>
                <div class="perm-legend">
                    <span class="perm-legend-item">
                        <span class="perm-legend-dot" style="background:#22c55e;"></span>
                        <span style="color:var(--text-tertiary);">Acesso completo</span>
                    </span>
                    <span class="perm-legend-item">
                        <span class="perm-legend-dot" style="background:#3b82f6;"></span>
                        <span style="color:var(--text-tertiary);">Somente leitura</span>
                    </span>
                    <span class="perm-legend-item">
                        <span class="perm-legend-dot" style="background:#cbd5e1;"></span>
                        <span style="color:var(--text-tertiary);">Sem acesso</span>
                    </span>
                </div>
            </div>

            <form method="POST" action="/config/perfis">
                <?= csrf_field() ?>

                <div class="perm-card">
                    <div class="perm-card-head">
                        <span style="font-size:13px;font-weight:600;">Matriz de permissões</span>
                        <button type="submit" class="btn-primary">
                            <i class="ti ti-device-floppy"></i> Salvar permissões
                        </button>
                    </div>

                    <div style="overflow-x:auto;">
                        <table class="perm-table">
                            <thead>
                                <tr>
                                    <th>Módulo</th>
                                    <?php foreach ($perfis as $p): ?>
                                        <th class="th-perfil-nome">
                                            <input
                                                type="text"
                                                name="perfis[<?= $p['id'] ?>][nome]"
                                                value="<?= e($p['nome']) ?>"
                                                class="perfil-nome-input"
                                                title="Clique para renomear">
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $icones = [
                                    'dashboard'     => 'ti-layout-dashboard',
                                    'vendas'        => 'ti-shopping-cart',
                                    'compras'       => 'ti-truck',
                                    'financeiro'    => 'ti-coin',
                                    'estoque'       => 'ti-package',
                                    'clientes'      => 'ti-users',
                                    'fornecedores'  => 'ti-building-store',
                                    'relatorios'    => 'ti-chart-bar',
                                    'configuracoes' => 'ti-settings',
                                ];
                                foreach ($modulos as $mod => $label):
                                ?>
                                    <tr>
                                        <td>
                                            <span class="perm-modulo">
                                                <i class="ti <?= $icones[$mod] ?? 'ti-circle' ?>" style="font-size:14px;color:var(--color-primary,#6366f1);"></i>
                                                <?= e($label) ?>
                                            </span>
                                        </td>
                                        <?php foreach ($perfis as $p):
                                            $val = $p['permissoes'][$mod] ?? 'false';
                                            $val = in_array($val, ['true', 'view', 'false']) ? $val : 'false';
                                        ?>
                                            <td class="perm-cell">
                                                <select
                                                    name="perfis[<?= $p['id'] ?>][permissoes][<?= $mod ?>]"
                                                    class="perm-select val-<?= $val ?>"
                                                    onchange="this.className='perm-select val-'+this.value">
                                                    <option value="true" <?= $val === 'true'  ? 'selected' : '' ?>>✔ Completo</option>
                                                    <option value="view" <?= $val === 'view'  ? 'selected' : '' ?>>👁 Leitura</option>
                                                    <option value="false" <?= $val === 'false' ? 'selected' : '' ?>>✕ Bloqueado</option>
                                                </select>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="margin-top:16px;display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn-primary">
                        <i class="ti ti-device-floppy"></i> Salvar permissões
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>