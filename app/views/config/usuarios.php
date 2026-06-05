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
        justify-content: space-between;
    }

    .cfg-card-head-title {
        font-size: 13px;
        font-weight: 600;
    }

    table.cfg-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    table.cfg-table thead tr {
        background: var(--color-background-secondary, #f9fafb);
        border-bottom: 1px solid var(--color-border-tertiary, #e5e7eb);
    }

    table.cfg-table thead th {
        padding: 10px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--text-tertiary, #9ca3af);
        white-space: nowrap;
    }

    table.cfg-table tbody tr {
        border-bottom: 1px solid var(--color-border-tertiary, #e5e7eb);
        transition: background .1s;
    }

    table.cfg-table tbody tr:last-child {
        border-bottom: none;
    }

    table.cfg-table tbody tr:hover {
        background: var(--color-background-secondary, #f9fafb);
    }

    table.cfg-table td {
        padding: 12px 16px;
        vertical-align: middle;
    }

    .usr-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }

    .usr-name {
        font-weight: 500;
        font-size: 13px;
    }

    .usr-email {
        font-size: 11px;
        color: var(--text-tertiary);
        margin-top: 1px;
    }

    .badge-nivel {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-admin {
        background: #fef3c7;
        color: #d97706;
    }

    .badge-gerente {
        background: #dbeafe;
        color: #2563eb;
    }

    .badge-operador {
        background: #f0fdf4;
        color: #16a34a;
    }

    .badge-status-on {
        background: #f0fdf4;
        color: #16a34a;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-status-off {
        background: #fef2f2;
        color: #dc2626;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .btn-sm-action {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        border: 1px solid var(--color-border-tertiary, #e5e7eb);
        color: var(--color-text-secondary);
        background: transparent;
        cursor: pointer;
        transition: all .12s;
        font-family: inherit;
    }

    .btn-sm-action:hover {
        border-color: var(--color-primary, #6366f1);
        color: var(--color-primary, #6366f1);
    }

    .btn-sm-danger:hover {
        border-color: #dc2626 !important;
        color: #dc2626 !important;
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
        text-decoration: none;
        transition: opacity .15s;
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

            <div class="cfg-topbar">
                <div>
                    <h1>Usuários</h1>
                    <p>Gerencie quem tem acesso ao sistema</p>
                </div>
                <a href="/config/usuarios/criar" class="btn-primary">
                    <i class="ti ti-plus"></i> Novo Usuário
                </a>
            </div>

            <div class="cfg-card">
                <div class="cfg-card-head">
                    <span class="cfg-card-head-title">Usuários cadastrados</span>
                    <span style="font-size:12px;color:var(--text-tertiary)"><?= count($usuarios) ?> usuário(s)</span>
                </div>
                <table class="cfg-table">
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Perfil</th>
                            <th>Nível</th>
                            <th>Desde</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center;padding:40px;color:var(--text-tertiary);">
                                    <i class="ti ti-users" style="font-size:28px;display:block;margin-bottom:8px;opacity:.2;"></i>
                                    Nenhum usuário cadastrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $u):
                                $cores = ['#6366f1', '#22c55e', '#f59e0b', '#ef4444', '#0ea5e9', '#8b5cf6'];
                                $cor   = $cores[crc32($u['nome']) % count($cores)];
                                $ini   = mb_strtoupper(mb_substr($u['nome'], 0, 1));
                                $logado = ((int)$u['id'] === (int)\App\Core\Session::get('usuario_id'));
                            ?>
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div class="usr-avatar" style="background:<?= $cor ?>;"><?= $ini ?></div>
                                            <div>
                                                <div class="usr-name">
                                                    <?= e($u['nome']) ?>
                                                    <?php if ($logado): ?>
                                                        <span style="font-size:10px;color:var(--text-tertiary);margin-left:4px;">(você)</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="usr-email"><?= e($u['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($u['perfil_nome']): ?>
                                            <span style="font-size:12px;font-weight:500;"><?= e($u['perfil_nome']) ?></span>
                                        <?php else: ?>
                                            <span style="font-size:12px;color:var(--text-tertiary);">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $badgeNivel = match ($u['nivel']) {
                                            'admin'   => 'badge-admin',
                                            'gerente' => 'badge-gerente',
                                            default   => 'badge-operador',
                                        }; ?>
                                        <span class="badge-nivel <?= $badgeNivel ?>">
                                            <i class="ti ti-shield" style="font-size:10px;"></i>
                                            <?= ucfirst(e($u['nivel'])) ?>
                                        </span>
                                    </td>
                                    <td style="color:var(--text-tertiary);font-size:12px;">
                                        <?= date('d/m/Y', strtotime($u['criado_em'])) ?>
                                    </td>
                                    <td>
                                        <?php if ($u['ativo']): ?>
                                            <span class="badge-status-on"><i class="ti ti-circle-filled" style="font-size:8px;"></i> Ativo</span>
                                        <?php else: ?>
                                            <span class="badge-status-off"><i class="ti ti-circle" style="font-size:8px;"></i> Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:6px;">
                                            <a href="/config/usuarios/<?= $u['id'] ?>/editar" class="btn-sm-action">
                                                <i class="ti ti-pencil"></i> Editar
                                            </a>
                                            <?php if (!$logado): ?>
                                                <form method="POST" action="/config/usuarios/<?= $u['id'] ?>/status" style="display:inline;">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn-sm-action btn-sm-danger"
                                                        onclick="return confirm('<?= $u['ativo'] ? 'Desativar' : 'Ativar' ?> este usuário?')">
                                                        <i class="ti <?= $u['ativo'] ? 'ti-ban' : 'ti-check' ?>"></i>
                                                        <?= $u['ativo'] ? 'Desativar' : 'Ativar' ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>