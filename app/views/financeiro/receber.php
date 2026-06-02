<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">

        <?php
        $pageTitle  = 'Contas a Receber';
        $breadcrumb = [
            ['label' => 'Dashboard',  'url' => '/dashboard'],
            ['label' => 'Financeiro', 'url' => '#'],
            ['label' => 'A Receber',  'url' => '/financeiro/receber'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content" style="display:flex;flex-direction:column;gap:20px;">

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

            <!-- KPI CARDS -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">

                <div class="fr-kpi">
                    <div class="fr-kpi-top">
                        <span class="fr-kpi-label">A receber</span>
                        <span class="fr-kpi-icon" style="background:#EFF6FF;color:#2563EB;">
                            <i class="ti ti-clock"></i>
                        </span>
                    </div>
                    <div class="fr-kpi-value">
                        R$ <?= number_format($totais['a_vencer'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div class="fr-kpi-sub"><?= $totais['qtd_a_vencer'] ?? 0 ?> conta(s) pendente(s)</div>
                    <div class="fr-kpi-bar" style="--p:<?= min(100, round(($totais['qtd_a_vencer'] ?? 0) / max(1, ($totais['qtd_a_vencer'] ?? 0) + ($totais['qtd_vencidas'] ?? 0)) * 100)) ?>%;--c:#BFDBFE;"></div>
                </div>

                <div class="fr-kpi">
                    <div class="fr-kpi-top">
                        <span class="fr-kpi-label" style="color:#DC2626;">Vencidas</span>
                        <span class="fr-kpi-icon" style="background:#FEE2E2;color:#DC2626;">
                            <i class="ti ti-alert-triangle"></i>
                        </span>
                    </div>
                    <div class="fr-kpi-value" style="color:#DC2626;">
                        R$ <?= number_format($totais['vencidas'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div class="fr-kpi-sub"><?= $totais['qtd_vencidas'] ?? 0 ?> conta(s) em atraso</div>
                    <div class="fr-kpi-bar" style="--p:100%;--c:#FCA5A5;"></div>
                </div>

                <div class="fr-kpi">
                    <div class="fr-kpi-top">
                        <span class="fr-kpi-label" style="color:#16A34A;">Recebidas (mês)</span>
                        <span class="fr-kpi-icon" style="background:#DCFCE7;color:#16A34A;">
                            <i class="ti ti-circle-check"></i>
                        </span>
                    </div>
                    <div class="fr-kpi-value" style="color:#16A34A;">
                        R$ <?= number_format($totais['recebidas_mes'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div class="fr-kpi-sub"><?= $totais['qtd_recebidas_mes'] ?? 0 ?> conta(s) quitada(s)</div>
                    <div class="fr-kpi-bar" style="--p:100%;--c:#86EFAC;"></div>
                </div>

                <?php
                $totalAberto  = ($totais['a_vencer'] ?? 0) + ($totais['vencidas'] ?? 0);
                $percVencidas = $totalAberto > 0
                    ? round(($totais['vencidas'] ?? 0) / $totalAberto * 100)
                    : 0;
                ?>
                <div class="fr-kpi">
                    <div class="fr-kpi-top">
                        <span class="fr-kpi-label">Total em aberto</span>
                        <span class="fr-kpi-icon" style="background:#F5F3FF;color:#7C3AED;">
                            <i class="ti ti-cash"></i>
                        </span>
                    </div>
                    <div class="fr-kpi-value">
                        R$ <?= number_format($totalAberto, 2, ',', '.') ?>
                    </div>
                    <div class="fr-kpi-sub">
                        <?php if ($percVencidas > 0): ?>
                            <span style="color:#DC2626;font-weight:600;"><?= $percVencidas ?>%</span> já vencido
                        <?php else: ?>
                            Tudo dentro do prazo
                        <?php endif; ?>
                    </div>
                    <div class="fr-kpi-bar" style="--p:<?= $percVencidas ?>%;--c:#FCA5A5;background:#EDE9FE;"></div>
                </div>

            </div>

            <!-- FILTROS -->
            <div class="zf-table-card fr-filter-card">
                <form method="GET" action="/financeiro/receber" id="form-filtros">

                    <div class="fr-filter-row">

                        <div class="fr-filter-field fr-field-busca">
                            <label class="fr-label">Buscar</label>
                            <div class="fr-input-icon-wrap">
                                <i class="ti ti-search fr-input-icon"></i>
                                <input type="text" name="busca"
                                    value="<?= e($filtros['busca'] ?? '') ?>"
                                    placeholder="Descrição, documento, cliente..."
                                    class="fr-input fr-input-with-icon">
                            </div>
                        </div>

                        <!-- Status -->
                        <!-- CORRIGIDO: values batem exatamente com o que o model/banco espera -->
                        <div class="fr-filter-field" style="min-width:140px;">
                            <label class="fr-label">Status</label>
                            <div class="fr-select-wrap">
                                <select name="status" class="fr-input fr-select">
                                    <option value="">Todos</option>
                                    <option value="pendente" <?= ($filtros['status'] ?? '') === 'pendente'   ? 'selected' : '' ?>>Pendente</option>
                                    <option value="parcial" <?= ($filtros['status'] ?? '') === 'parcial'    ? 'selected' : '' ?>>Parcial</option>
                                    <option value="recebido" <?= ($filtros['status'] ?? '') === 'recebido'   ? 'selected' : '' ?>>Recebida</option>
                                    <option value="cancelado" <?= ($filtros['status'] ?? '') === 'cancelado'  ? 'selected' : '' ?>>Cancelada</option>
                                    <option value="vencida" <?= ($filtros['status'] ?? '') === 'vencida'    ? 'selected' : '' ?>>Vencida</option>
                                </select>
                                <i class="ti ti-chevron-down fr-select-arrow"></i>
                            </div>
                        </div>

                        <div class="fr-filter-field">
                            <label class="fr-label">Vencimento</label>
                            <div class="fr-date-range">
                                <input type="date" name="de" value="<?= e($filtros['de']  ?? '') ?>" class="fr-input fr-input-date" title="De">
                                <span class="fr-date-sep">–</span>
                                <input type="date" name="ate" value="<?= e($filtros['ate'] ?? '') ?>" class="fr-input fr-input-date" title="Até">
                            </div>
                        </div>

                        <div class="fr-filter-actions">
                            <button type="submit" class="fr-btn fr-btn-primary">
                                <i class="ti ti-search"></i> Filtrar
                            </button>
                            <?php
                            // CORRIGIDO: callback explícito para não tratar '0' como vazio
                            $temFiltroAtivo = !empty(array_filter($filtros ?? [], fn($v) => $v !== '' && $v !== null));
                            ?>
                            <?php if ($temFiltroAtivo): ?>
                                <a href="/financeiro/receber" class="fr-btn fr-btn-ghost" title="Limpar filtros">
                                    <i class="ti ti-x"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div style="margin-left:auto;">
                            <a href="/financeiro/receber/criar" class="fr-btn fr-btn-dark">
                                <i class="ti ti-plus"></i> Nova conta
                            </a>
                        </div>

                    </div>

                    <!-- CORRIGIDO: chips preservam filtros ativos (de, ate) -->
                    <?php
                    $filtrosBase = array_filter([
                        'de'  => $filtros['de']  ?? '',
                        'ate' => $filtros['ate'] ?? '',
                    ], fn($v) => $v !== '' && $v !== null);

                    $hoje    = date('Y-m-d');
                    $proxima = date('Y-m-d', strtotime('+7 days'));

                    $qsVencida   = http_build_query(array_merge($filtrosBase, ['status' => 'vencida']));
                    $qsPendente  = http_build_query(array_merge($filtrosBase, ['status' => 'pendente']));
                    $qsParcial   = http_build_query(array_merge($filtrosBase, ['status' => 'parcial']));
                    $qsProximas  = http_build_query(array_merge($filtrosBase, ['status' => 'pendente', 'de' => $hoje, 'ate' => $proxima]));
                    $qsRecebidas = http_build_query(['status' => 'recebido', 'de' => date('Y-m-01'), 'ate' => date('Y-m-t')]);
                    ?>
                    <div class="fr-quick-filters">
                        <span class="fr-qf-label">Acesso rápido:</span>
                        <a href="/financeiro/receber?<?= $qsVencida ?>"
                            class="fr-qf-chip fr-qf-danger <?= ($filtros['status'] ?? '') === 'vencida' ? 'active' : '' ?>">
                            <i class="ti ti-alert-triangle"></i> Vencidas
                            <?php if (($totais['qtd_vencidas'] ?? 0) > 0): ?>
                                <span class="fr-qf-badge"><?= $totais['qtd_vencidas'] ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="/financeiro/receber?<?= $qsPendente ?>"
                            class="fr-qf-chip <?= ($filtros['status'] ?? '') === 'pendente' ? 'active' : '' ?>">
                            <i class="ti ti-clock"></i> Pendentes
                        </a>
                        <a href="/financeiro/receber?<?= $qsParcial ?>"
                            class="fr-qf-chip fr-qf-blue <?= ($filtros['status'] ?? '') === 'parcial' ? 'active' : '' ?>">
                            <i class="ti ti-percentage"></i> Parciais
                        </a>
                        <a href="/financeiro/receber?<?= $qsProximas ?>"
                            class="fr-qf-chip">
                            <i class="ti ti-calendar-due"></i> Vencem em 7 dias
                        </a>
                        <a href="/financeiro/receber?<?= $qsRecebidas ?>"
                            class="fr-qf-chip fr-qf-success <?= ($filtros['status'] ?? '') === 'recebido' ? 'active' : '' ?>">
                            <i class="ti ti-circle-check"></i> Recebidas este mês
                        </a>
                    </div>

                </form>
            </div>

            <!-- TABELA -->
            <div class="zf-table-card" style="overflow:hidden;">

                <?php if (empty($contas)): ?>
                    <div class="fr-empty">
                        <div class="fr-empty-icon"><i class="ti ti-inbox"></i></div>
                        <div class="fr-empty-title">Nenhuma conta encontrada</div>
                        <div class="fr-empty-sub">
                            Tente ajustar os filtros ou
                            <a href="/financeiro/receber/criar">cadastre uma nova conta</a>.
                        </div>
                    </div>
                <?php else: ?>

                    <table class="zf-table fr-table">
                        <thead>
                            <tr>
                                <th style="width:36px;">
                                    <input type="checkbox" id="sel-all" class="fr-checkbox" title="Selecionar todos">
                                </th>
                                <th>Descrição</th>
                                <th>Cliente</th>
                                <th style="width:110px;">Categoria</th>
                                <th style="width:130px;">Vencimento</th>
                                <th style="width:110px;text-align:right;">Valor</th>
                                <th style="width:110px;text-align:right;">Recebido</th>
                                <th style="width:100px;">Forma</th>
                                <th style="width:100px;text-align:center;">Status</th>
                                <th style="width:130px;text-align:right;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contas as $c):
                                // CORRIGIDO: status_real do model retorna 'vencida' (não 'vencido')
                                // e 'aberto'/'parcial' como estão no banco
                                $statusReal  = $c['status_real'] ?? $c['status'];
                                $isVencida   = $statusReal === 'vencida';
                                $isAberto    = $statusReal === 'aberto';
                                $isParcial   = $statusReal === 'parcial';
                                $isRecebido  = $statusReal === 'recebido';
                                $isCancelado = $statusReal === 'cancelado';
                                $isAtivo     = in_array($statusReal, ['aberto', 'vencida', 'parcial']);

                                $diasAtraso    = 0;
                                $diasRestantes = 0;
                                if ($isVencida) {
                                    $diasAtraso = (int) floor((time() - strtotime($c['vencimento'])) / 86400);
                                } elseif ($isAberto || $isParcial) {
                                    $diasRestantes = (int) ceil((strtotime($c['vencimento']) - time()) / 86400);
                                }
                            ?>
                                <tr class="fr-tr <?= $isVencida ? 'fr-tr-danger' : ($isParcial ? 'fr-tr-partial' : '') ?>">
                                    <td>
                                        <input type="checkbox" class="fr-checkbox fr-sel-item" value="<?= $c['id'] ?>">
                                    </td>
                                    <td>
                                        <div class="fr-desc-nome">
                                            <?= e(str_replace('Fiado', 'A Prazo', $c['descricao'])) ?>
                                        </div>
                                        <?php if (!empty($c['documento'])): ?>
                                            <div class="fr-desc-doc">
                                                <i class="ti ti-file-text"></i> Doc: <?= e($c['documento']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($c['venda_id'])): ?>
                                            <div class="fr-desc-doc" style="color:#7C3AED;">
                                                <i class="ti ti-tag"></i> Venda a prazo
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['cliente_nome'])): ?>
                                            <span class="fr-cliente"><?= e($c['cliente_nome']) ?></span>
                                        <?php else: ?>
                                            <span style="color:#D1D5DB;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['categoria_nome'])): ?>
                                            <span class="fr-categoria-tag"><?= e($c['categoria_nome']) ?></span>
                                        <?php else: ?>
                                            <span style="color:#D1D5DB;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fr-venc <?= $isVencida ? 'fr-venc-late' : '' ?>">
                                            <?= date('d/m/Y', strtotime($c['vencimento'])) ?>
                                        </div>
                                        <?php if (($c['total_parcelas'] ?? 1) > 1): ?>
                                            <div style="font-size:10px;color:#9CA3AF;margin-top:1px;">
                                                Parcela <?= $c['numero_parcela'] ?>/<?= $c['total_parcelas'] ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($isVencida && $diasAtraso > 0): ?>
                                            <div class="fr-atraso"><?= $diasAtraso ?>d em atraso</div>
                                        <?php elseif (($isAberto || $isParcial) && $diasRestantes >= 0 && $diasRestantes <= 7): ?>
                                            <div class="fr-atraso" style="color:#D97706;">vence em <?= $diasRestantes ?>d</div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <div class="fr-valor">R$ <?= number_format($c['valor'], 2, ',', '.') ?></div>
                                    </td>
                                    <td style="text-align:right;">
                                        <?php if (($c['valor_recebido'] ?? 0) > 0): ?>
                                            <div style="font-size:13px;color:#16A34A;font-weight:500;">
                                                R$ <?= number_format($c['valor_recebido'], 2, ',', '.') ?>
                                            </div>
                                            <?php if ($c['valor_recebido'] < $c['valor']): ?>
                                                <div style="font-size:11px;color:#2563EB;">parcial</div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color:#D1D5DB;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['forma_recebimento'])): ?>
                                            <?php
                                            $formaLabel = [
                                                'dinheiro'       => '💵 Dinheiro',
                                                'pix'            => '⚡ PIX',
                                                'boleto'         => '🔖 Boleto',
                                                'transferencia'  => '🏦 TED/PIX',
                                                'cartao_credito' => '💳 Crédito',
                                                'cartao_debito'  => '💳 Débito',
                                                'cheque'         => '📝 Cheque',
                                            ];
                                            ?>
                                            <span style="font-size:12px;color:#6B7280;">
                                                <?= $formaLabel[$c['forma_recebimento']] ?? e($c['forma_recebimento']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color:#D1D5DB;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php
                                        $statusConfig = [
                                            'aberto'    => ['label' => 'Pendente',  'color' => '#D97706', 'bg' => '#FEF3C7'],
                                            'parcial'   => ['label' => 'Parcial',   'color' => '#2563EB', 'bg' => '#DBEAFE'],
                                            'vencida'   => ['label' => 'Vencida',   'color' => '#DC2626', 'bg' => '#FEE2E2'],
                                            'recebido'  => ['label' => 'Recebida',  'color' => '#16A34A', 'bg' => '#DCFCE7'],
                                            'cancelado' => ['label' => 'Cancelada', 'color' => '#9CA3AF', 'bg' => '#F3F4F6'],
                                        ];
                                        $sc = $statusConfig[$statusReal] ?? $statusConfig['aberto'];
                                        ?>
                                        <span class="fr-badge" style="color:<?= $sc['color'] ?>;background:<?= $sc['bg'] ?>;">
                                            <?= $sc['label'] ?>
                                        </span>
                                    </td>
                                    <td style="text-align:right;">
                                        <div class="fr-actions">
                                            <?php if ($isAtivo): ?>
                                                <button type="button"
                                                    class="fr-act fr-act-recv"
                                                    onclick="abrirBaixa(<?= $c['id'] ?>, <?= (float)$c['valor'] ?>, <?= (float)($c['valor_recebido'] ?? 0) ?>)"
                                                    title="Registrar recebimento">
                                                    <i class="ti ti-check"></i>
                                                    <span>Receber</span>
                                                </button>
                                                <button type="button"
                                                    class="fr-act fr-act-del"
                                                    onclick="abrirCancelar(<?= $c['id'] ?>, <?= $c['venda_id'] ?? 'null' ?>)"
                                                    title="Cancelar conta">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            <?php elseif ($isRecebido): ?>
                                                <span class="fr-recebido-data">
                                                    <i class="ti ti-circle-check"></i>
                                                    <?= !empty($c['data_recebimento'])
                                                        ? date('d/m/Y', strtotime($c['data_recebimento']))
                                                        : 'Recebida' ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="font-size:12px;color:#D1D5DB;">Cancelada</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- MODAL RECEBIMENTO -->
<div id="modal-baixa" class="fr-modal-overlay" onclick="if(event.target===this)fecharModal('modal-baixa')">
    <div class="fr-modal">
        <div class="fr-modal-header">
            <div class="fr-modal-title">
                <span class="fr-modal-icon" style="background:#DCFCE7;color:#16A34A;">
                    <i class="ti ti-cash"></i>
                </span>
                Registrar Recebimento
            </div>
            <button type="button" class="fr-modal-close" onclick="fecharModal('modal-baixa')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form id="form-baixa" method="POST">
            <?= csrf_field() ?>
            <div class="fr-modal-body">

                <div class="fr-baixa-resumo">
                    <div>
                        <div class="fr-resumo-label">Total da conta</div>
                        <div class="fr-resumo-val" id="baixa-total-display">R$ 0,00</div>
                    </div>
                    <div style="text-align:right;">
                        <div class="fr-resumo-label">Saldo a receber</div>
                        <div class="fr-resumo-val" style="color:#2563EB;" id="baixa-saldo-display">R$ 0,00</div>
                    </div>
                </div>

                <div class="fr-modal-fields">

                    <div class="fr-field">
                        <label class="fr-label">Valor recebido (R$) <span style="color:#DC2626">*</span></label>
                        <input type="text" name="valor_recebido" id="baixa-valor"
                            class="fr-input fr-input-money" required
                            placeholder="0,00" onclick="this.select()">
                    </div>

                    <div class="fr-field">
                        <label class="fr-label">Forma de recebimento <span style="color:#DC2626">*</span></label>
                        <div class="fr-pgto-grid">
                            <?php
                            $formas = [
                                ['val' => 'dinheiro',       'icon' => 'ti-cash',          'label' => 'Dinheiro'],
                                ['val' => 'pix',            'icon' => 'ti-brand-cashapp', 'label' => 'PIX'],
                                ['val' => 'boleto',         'icon' => 'ti-file-text',     'label' => 'Boleto'],
                                ['val' => 'transferencia',  'icon' => 'ti-building-bank', 'label' => 'TED/DOC'],
                                ['val' => 'cartao_credito', 'icon' => 'ti-credit-card',   'label' => 'Crédito'],
                                ['val' => 'cartao_debito',  'icon' => 'ti-credit-card',   'label' => 'Débito'],
                            ];
                            foreach ($formas as $f): ?>
                                <label class="fr-pgto-opt">
                                    <input type="radio" name="forma_recebimento"
                                        value="<?= $f['val'] ?>"
                                        <?= $f['val'] === 'pix' ? 'checked' : '' ?>>
                                    <span class="fr-pgto-face">
                                        <i class="ti <?= $f['icon'] ?>"></i>
                                        <?= $f['label'] ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="fr-field">
                        <label class="fr-label">Data do recebimento</label>
                        <input type="date" name="data_recebimento" id="baixa-data"
                            class="fr-input" value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="fr-field">
                        <label class="fr-label">Observação (opcional)</label>
                        <input type="text" name="observacao" class="fr-input"
                            placeholder="Ex: pix recebido, transferência confirmada...">
                    </div>

                </div>
            </div>
            <div class="fr-modal-footer">
                <button type="button" class="fr-btn fr-btn-ghost" onclick="fecharModal('modal-baixa')">
                    Cancelar
                </button>
                <button type="submit" class="fr-btn fr-btn-success">
                    <i class="ti ti-check"></i> Confirmar Recebimento
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CANCELAR -->
<div id="modal-cancelar" class="fr-modal-overlay" onclick="if(event.target===this)fecharModal('modal-cancelar')">
    <div class="fr-modal" style="max-width:420px;">
        <div class="fr-modal-header">
            <div class="fr-modal-title">
                <span class="fr-modal-icon" style="background:#FEE2E2;color:#DC2626;">
                    <i class="ti ti-alert-triangle"></i>
                </span>
                Cancelar Conta
            </div>
            <button type="button" class="fr-modal-close" onclick="fecharModal('modal-cancelar')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form id="form-cancelar" method="POST">
            <?= csrf_field() ?>
            <div class="fr-modal-body">

                <div id="cancelar-aviso-venda"
                    style="display:none;padding:12px 14px;background:#FEF2F2;border:1px solid #FECACA;
                            border-radius:8px;font-size:13px;color:#DC2626;line-height:1.5;">
                    <strong><i class="ti ti-alert-triangle"></i> Atenção:</strong>
                    Esta conta está vinculada a uma venda a prazo.
                    Ao cancelar, a venda será cancelada e o estoque será estornado automaticamente.
                </div>

                <p style="font-size:13px;color:#6B7280;">
                    Esta ação não pode ser desfeita. A conta será marcada como cancelada.
                </p>

                <div class="fr-field">
                    <label class="fr-label">Motivo do cancelamento</label>
                    <textarea name="motivo" class="fr-input"
                        style="resize:vertical;min-height:80px;"
                        placeholder="Descreva brevemente o motivo..."></textarea>
                </div>

            </div>
            <div class="fr-modal-footer">
                <button type="button" class="fr-btn fr-btn-ghost" onclick="fecharModal('modal-cancelar')">
                    Voltar
                </button>
                <button type="submit" class="fr-btn fr-btn-danger">
                    <i class="ti ti-x"></i> Confirmar cancelamento
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .fr-kpi {
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 12px;
        padding: 18px 20px 14px;
        position: relative;
        overflow: hidden;
        transition: box-shadow .15s;
    }

    .fr-kpi:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, .07);
    }

    .fr-kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .fr-kpi-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6B7280;
    }

    .fr-kpi-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .fr-kpi-value {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -.3px;
        line-height: 1;
        margin-bottom: 4px;
    }

    .fr-kpi-sub {
        font-size: 12px;
        color: #9CA3AF;
        margin-bottom: 12px;
    }

    .fr-kpi-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #F3F4F6;
    }

    .fr-kpi-bar::after {
        content: '';
        display: block;
        height: 100%;
        width: var(--p, 0%);
        background: var(--c, #D1D5DB);
        border-radius: 0 2px 2px 0;
        transition: width .6s ease;
    }

    .fr-filter-card {
        padding: 16px 20px 0;
    }

    .fr-filter-row {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        padding-bottom: 14px;
    }

    .fr-filter-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .fr-field-busca {
        flex: 1;
        min-width: 220px;
    }

    .fr-label {
        font-size: 11px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .fr-input {
        font-family: inherit;
        font-size: 13px;
        color: #1A1A1A;
        background: #F9FAFB;
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 8px;
        padding: 8px 12px;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        width: 100%;
    }

    .fr-input:focus {
        border-color: #1A1A1A;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, .05);
    }

    .fr-input-icon-wrap {
        position: relative;
    }

    .fr-input-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
        font-size: 14px;
        pointer-events: none;
    }

    .fr-input-with-icon {
        padding-left: 32px;
    }

    .fr-select-wrap {
        position: relative;
    }

    .fr-select {
        appearance: none;
        padding-right: 28px;
        cursor: pointer;
    }

    .fr-select-arrow {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
        font-size: 12px;
        pointer-events: none;
    }

    .fr-date-range {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .fr-input-date {
        width: 130px;
    }

    .fr-date-sep {
        color: #9CA3AF;
        font-size: 13px;
        flex-shrink: 0;
    }

    .fr-filter-actions {
        display: flex;
        gap: 6px;
        align-items: flex-end;
    }

    .fr-quick-filters {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 10px 0 14px;
        border-top: 1px solid rgba(0, 0, 0, .06);
        flex-wrap: wrap;
    }

    .fr-qf-label {
        font-size: 11px;
        color: #9CA3AF;
        font-weight: 500;
        margin-right: 2px;
    }

    .fr-qf-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid rgba(0, 0, 0, .1);
        color: #374151;
        background: #fff;
        text-decoration: none;
        transition: all .12s;
    }

    .fr-qf-chip:hover {
        background: #F3F4F6;
        border-color: rgba(0, 0, 0, .18);
    }

    .fr-qf-chip.active {
        background: #1A1A1A;
        color: #fff;
        border-color: #1A1A1A;
    }

    .fr-qf-danger {
        color: #DC2626;
        border-color: #FECACA;
        background: #FEF2F2;
    }

    .fr-qf-danger:hover {
        background: #FEE2E2;
    }

    .fr-qf-danger.active {
        background: #DC2626;
        color: #fff;
        border-color: #DC2626;
    }

    .fr-qf-success {
        color: #16A34A;
        border-color: #BBF7D0;
        background: #F0FDF4;
    }

    .fr-qf-success:hover {
        background: #DCFCE7;
    }

    .fr-qf-blue {
        color: #2563EB;
        border-color: #BFDBFE;
        background: #EFF6FF;
    }

    .fr-qf-blue:hover {
        background: #DBEAFE;
    }

    .fr-qf-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 4px;
        border-radius: 9px;
        background: #DC2626;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
    }

    .fr-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        text-decoration: none;
        white-space: nowrap;
        transition: opacity .15s, background .12s;
    }

    .fr-btn:hover {
        opacity: .88;
    }

    .fr-btn-primary {
        background: #1A1A1A;
        color: #fff;
    }

    .fr-btn-dark {
        background: #1A1A1A;
        color: #fff;
    }

    .fr-btn-ghost {
        background: #fff;
        color: #374151;
        border: 1px solid rgba(0, 0, 0, .14);
    }

    .fr-btn-ghost:hover {
        background: #F9FAFB;
    }

    .fr-btn-success {
        background: #16A34A;
        color: #fff;
    }

    .fr-btn-danger {
        background: #DC2626;
        color: #fff;
    }

    .fr-table tbody tr {
        transition: background .1s;
    }

    .fr-tr-danger td:first-child {
        border-left: 3px solid #FCA5A5;
    }

    .fr-tr-partial td:first-child {
        border-left: 3px solid #BFDBFE;
    }

    .fr-desc-nome {
        font-weight: 500;
        font-size: 13px;
    }

    .fr-desc-doc {
        font-size: 11px;
        color: #9CA3AF;
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .fr-cliente {
        font-size: 13px;
        color: #374151;
    }

    .fr-categoria-tag {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        background: #F3F4F6;
        color: #374151;
        font-size: 11px;
        font-weight: 500;
    }

    .fr-venc {
        font-size: 13px;
        font-weight: 500;
    }

    .fr-venc-late {
        color: #DC2626;
    }

    .fr-atraso {
        font-size: 11px;
        color: #DC2626;
        font-weight: 500;
        margin-top: 2px;
    }

    .fr-valor {
        font-size: 13px;
        font-weight: 600;
    }

    .fr-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .fr-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
    }

    .fr-act {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 10px;
        border-radius: 6px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all .12s;
    }

    .fr-act-recv {
        background: #EFF6FF;
        color: #2563EB;
        border-color: #BFDBFE;
    }

    .fr-act-recv:hover {
        background: #DBEAFE;
    }

    .fr-act-del {
        background: transparent;
        color: #9CA3AF;
        border-color: transparent;
        padding: 5px 7px;
    }

    .fr-act-del:hover {
        background: #FEE2E2;
        color: #DC2626;
    }

    .fr-recebido-data {
        font-size: 12px;
        color: #16A34A;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .fr-empty {
        padding: 56px 32px;
        text-align: center;
    }

    .fr-empty-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: #F3F4F6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: #9CA3AF;
        margin: 0 auto 16px;
    }

    .fr-empty-title {
        font-size: 15px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .fr-empty-sub {
        font-size: 13px;
        color: #9CA3AF;
    }

    .fr-empty-sub a {
        color: #2563EB;
    }

    .fr-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1000;
        background: rgba(0, 0, 0, .45);
        backdrop-filter: blur(2px);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .fr-modal-overlay.show {
        display: flex;
    }

    .fr-modal {
        background: #fff;
        border-radius: 14px;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
        overflow: hidden;
        animation: frSlideUp .2s ease;
    }

    @keyframes frSlideUp {
        from {
            opacity: 0;
            transform: translateY(16px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fr-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px 16px;
        border-bottom: 1px solid #F3F4F6;
    }

    .fr-modal-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 15px;
        font-weight: 600;
    }

    .fr-modal-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .fr-modal-close {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        border: none;
        background: #F3F4F6;
        color: #6B7280;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: background .12s;
    }

    .fr-modal-close:hover {
        background: #E5E7EB;
    }

    .fr-modal-body {
        padding: 20px 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .fr-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #F3F4F6;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .fr-modal-fields {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .fr-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .fr-input-money {
        font-size: 22px;
        font-weight: 700;
        text-align: right;
        letter-spacing: .5px;
        padding: 10px 16px;
    }

    .fr-baixa-resumo {
        display: flex;
        justify-content: space-between;
        padding: 14px 16px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 10px;
    }

    .fr-resumo-label {
        font-size: 11px;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 4px;
    }

    .fr-resumo-val {
        font-size: 20px;
        font-weight: 700;
    }

    .fr-pgto-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }

    .fr-pgto-opt {
        cursor: pointer;
    }

    .fr-pgto-opt input {
        display: none;
    }

    .fr-pgto-face {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 10px 8px;
        border-radius: 8px;
        border: 1.5px solid #E5E7EB;
        font-size: 12px;
        font-weight: 500;
        color: #374151;
        background: #fff;
        transition: all .12s;
        text-align: center;
        line-height: 1.2;
    }

    .fr-pgto-face i {
        font-size: 16px;
        color: #9CA3AF;
    }

    .fr-pgto-opt:hover .fr-pgto-face {
        border-color: #9CA3AF;
        background: #F9FAFB;
    }

    .fr-pgto-opt input:checked+.fr-pgto-face {
        border-color: #16A34A;
        background: #16A34A;
        color: #fff;
    }

    .fr-pgto-opt input:checked+.fr-pgto-face i {
        color: #fff;
    }

    .fr-checkbox {
        width: 15px;
        height: 15px;
        accent-color: #1A1A1A;
        cursor: pointer;
    }
</style>

<script>
    function abrirBaixa(id, valor, recebido) {
        const saldo = Math.max(0, valor - recebido);
        document.getElementById('baixa-total-display').textContent = 'R$ ' + valor.toFixed(2).replace('.', ',');
        document.getElementById('baixa-saldo-display').textContent = 'R$ ' + saldo.toFixed(2).replace('.', ',');
        document.getElementById('baixa-valor').value = saldo.toFixed(2).replace('.', ',');
        document.getElementById('form-baixa').action = '/financeiro/receber/' + id + '/baixar';
        document.getElementById('modal-baixa').classList.add('show');
        setTimeout(() => document.getElementById('baixa-valor').select(), 120);
    }

    function abrirCancelar(id, vendaId) {
        document.getElementById('form-cancelar').action = '/financeiro/receber/' + id + '/cancelar';
        document.getElementById('cancelar-aviso-venda').style.display = vendaId ? 'block' : 'none';
        document.getElementById('modal-cancelar').classList.add('show');
    }

    function fecharModal(id) {
        document.getElementById(id).classList.remove('show');
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            fecharModal('modal-baixa');
            fecharModal('modal-cancelar');
        }
    });

    document.getElementById('baixa-valor').addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '');
        v = (parseInt(v || 0) / 100).toFixed(2).replace('.', ',');
        this.value = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    });

    document.getElementById('sel-all')?.addEventListener('change', function() {
        document.querySelectorAll('.fr-sel-item').forEach(cb => {
            cb.checked = this.checked;
        });
    });
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>