<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
    <div class="zf-main">

        <?php
        $pageTitle  = 'Contas a Pagar';
        $breadcrumb = [
            ['label' => 'Dashboard',  'url' => '/dashboard'],
            ['label' => 'Financeiro', 'url' => '#'],
            ['label' => 'A Pagar',    'url' => '/financeiro/pagar'],
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

            <!-- ═══════════════════════════════════════
                 KPI CARDS
            ═══════════════════════════════════════ -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">

                <!-- A vencer -->
                <div class="fp-kpi" data-variant="neutral">
                    <div class="fp-kpi-top">
                        <span class="fp-kpi-label">A vencer</span>
                        <span class="fp-kpi-icon" style="background:#F3F4F6;color:#6B7280;">
                            <i class="ti ti-clock"></i>
                        </span>
                    </div>
                    <div class="fp-kpi-value">
                        R$ <?= number_format($totais['a_vencer'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div class="fp-kpi-sub"><?= $totais['qtd_a_vencer'] ?? 0 ?> conta(s) pendente(s)</div>
                    <div class="fp-kpi-bar" style="--p:<?= min(100, round(($totais['qtd_a_vencer'] ?? 0) / max(1, ($totais['qtd_a_vencer'] ?? 0) + ($totais['qtd_vencidas'] ?? 0)) * 100)) ?>%;--c:#D1D5DB;"></div>
                </div>

                <!-- Vencidas -->
                <div class="fp-kpi" data-variant="danger">
                    <div class="fp-kpi-top">
                        <span class="fp-kpi-label" style="color:#DC2626;">Vencidas</span>
                        <span class="fp-kpi-icon" style="background:#FEE2E2;color:#DC2626;">
                            <i class="ti ti-alert-triangle"></i>
                        </span>
                    </div>
                    <div class="fp-kpi-value" style="color:#DC2626;">
                        R$ <?= number_format($totais['vencidas'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div class="fp-kpi-sub"><?= $totais['qtd_vencidas'] ?? 0 ?> conta(s) em atraso</div>
                    <div class="fp-kpi-bar" style="--p:100%;--c:#FCA5A5;"></div>
                </div>

                <!-- Pagas no mês -->
                <div class="fp-kpi" data-variant="success">
                    <div class="fp-kpi-top">
                        <span class="fp-kpi-label" style="color:#16A34A;">Pagas (mês)</span>
                        <span class="fp-kpi-icon" style="background:#DCFCE7;color:#16A34A;">
                            <i class="ti ti-circle-check"></i>
                        </span>
                    </div>
                    <div class="fp-kpi-value" style="color:#16A34A;">
                        R$ <?= number_format($totais['pagas_mes'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div class="fp-kpi-sub"><?= $totais['qtd_pagas_mes'] ?? 0 ?> conta(s) quitada(s)</div>
                    <div class="fp-kpi-bar" style="--p:100%;--c:#86EFAC;"></div>
                </div>

                <!-- Total em aberto -->
                <?php
                $totalAberto = ($totais['a_vencer'] ?? 0) + ($totais['vencidas'] ?? 0);
                $percVencidas = $totalAberto > 0
                    ? round(($totais['vencidas'] ?? 0) / $totalAberto * 100)
                    : 0;
                ?>
                <div class="fp-kpi" data-variant="neutral">
                    <div class="fp-kpi-top">
                        <span class="fp-kpi-label">Total em aberto</span>
                        <span class="fp-kpi-icon" style="background:#EFF6FF;color:#2563EB;">
                            <i class="ti ti-report-money"></i>
                        </span>
                    </div>
                    <div class="fp-kpi-value">
                        R$ <?= number_format($totalAberto, 2, ',', '.') ?>
                    </div>
                    <div class="fp-kpi-sub">
                        <?php if ($percVencidas > 0): ?>
                            <span style="color:#DC2626;font-weight:600;"><?= $percVencidas ?>%</span> já vencido
                        <?php else: ?>
                            Tudo dentro do prazo
                        <?php endif; ?>
                    </div>
                    <div class="fp-kpi-bar" style="--p:<?= $percVencidas ?>%;--c:#FCA5A5;background:#DBEAFE;"></div>
                </div>

            </div>

            <!-- ═══════════════════════════════════════
                 FILTROS
            ═══════════════════════════════════════ -->
            <div class="zf-table-card fp-filter-card">
                <form method="GET" action="/financeiro/pagar" id="form-filtros">

                    <div class="fp-filter-row">

                        <!-- Busca livre -->
                        <div class="fp-filter-field fp-field-busca">
                            <label class="fp-label">Buscar</label>
                            <div class="fp-input-icon-wrap">
                                <i class="ti ti-search fp-input-icon"></i>
                                <input type="text" name="busca" value="<?= e($filtros['busca'] ?? '') ?>"
                                    placeholder="Descrição, nº documento, fornecedor..."
                                    class="fp-input fp-input-with-icon">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="fp-filter-field" style="min-width:140px;">
                            <label class="fp-label">Status</label>
                            <div class="fp-select-wrap">
                                <select name="status" class="fp-input fp-select">
                                    <option value="">Todos</option>
                                    <option value="pendente" <?= ($filtros['status'] ?? '') === 'pendente'  ? 'selected' : '' ?>>Pendente</option>
                                    <option value="vencida" <?= ($filtros['status'] ?? '') === 'vencida'   ? 'selected' : '' ?>>Vencida</option>
                                    <option value="paga" <?= ($filtros['status'] ?? '') === 'paga'      ? 'selected' : '' ?>>Paga</option>
                                    <option value="cancelada" <?= ($filtros['status'] ?? '') === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                </select>
                                <i class="ti ti-chevron-down fp-select-arrow"></i>
                            </div>
                        </div>

                        <!-- Categoria -->
                        <div class="fp-filter-field" style="min-width:160px;">
                            <label class="fp-label">Categoria</label>
                            <div class="fp-select-wrap">
                                <select name="categoria_id" class="fp-input fp-select">
                                    <option value="">Todas</option>
                                    <?php foreach ($categorias ?? [] as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"
                                            <?= ($filtros['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                            <?= e($cat['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="ti ti-chevron-down fp-select-arrow"></i>
                            </div>
                        </div>

                        <!-- Fornecedor -->
                        <div class="fp-filter-field" style="min-width:160px;">
                            <label class="fp-label">Fornecedor</label>
                            <div class="fp-select-wrap">
                                <select name="fornecedor_id" class="fp-input fp-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($fornecedores ?? [] as $f): ?>
                                        <option value="<?= $f['id'] ?>"
                                            <?= ($filtros['fornecedor_id'] ?? '') == $f['id'] ? 'selected' : '' ?>>
                                            <?= e($f['razao_social']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="ti ti-chevron-down fp-select-arrow"></i>
                            </div>
                        </div>

                        <!-- Período -->
                        <div class="fp-filter-field">
                            <label class="fp-label">Vencimento</label>
                            <div class="fp-date-range">
                                <input type="date" name="de" value="<?= e($filtros['de']  ?? '') ?>" class="fp-input fp-input-date" title="De">
                                <span class="fp-date-sep">–</span>
                                <input type="date" name="ate" value="<?= e($filtros['ate'] ?? '') ?>" class="fp-input fp-input-date" title="Até">
                            </div>
                        </div>

                        <!-- Ações -->
                        <div class="fp-filter-actions">
                            <button type="submit" class="fp-btn fp-btn-primary">
                                <i class="ti ti-search"></i> Filtrar
                            </button>
                            <?php
                            // CORRIGIDO: usa callback explícito para não tratar '0' como vazio
                            $temFiltroAtivo = !empty(array_filter($filtros ?? [], fn($v) => $v !== '' && $v !== null));
                            ?>
                            <?php if ($temFiltroAtivo): ?>
                                <a href="/financeiro/pagar" class="fp-btn fp-btn-ghost" title="Limpar filtros">
                                    <i class="ti ti-x"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Nova conta — empurrado para a direita -->
                        <div style="margin-left:auto;">
                            <a href="/financeiro/pagar/criar" class="fp-btn fp-btn-dark">
                                <i class="ti ti-plus"></i> Nova conta
                            </a>
                        </div>

                    </div>

                    <!-- Filtros rápidos de status -->
                    <?php
                    // CORRIGIDO: chips preservam os filtros ativos (categoria_id, fornecedor_id, de, ate)
                    // em vez de descartar tudo ao clicar
                    $filtrosBase = array_filter([
                        'categoria_id'  => $filtros['categoria_id']  ?? '',
                        'fornecedor_id' => $filtros['fornecedor_id'] ?? '',
                        'de'            => $filtros['de']            ?? '',
                        'ate'           => $filtros['ate']           ?? '',
                    ], fn($v) => $v !== '' && $v !== null);

                    $qsVencida   = http_build_query(array_merge($filtrosBase, ['status' => 'vencida']));
                    $qsPendente  = http_build_query(array_merge($filtrosBase, ['status' => 'pendente']));
                    $hoje        = date('Y-m-d');
                    $proxima     = date('Y-m-d', strtotime('+7 days'));
                    $qsProximas  = http_build_query(array_merge($filtrosBase, ['status' => 'pendente', 'de' => $hoje, 'ate' => $proxima]));
                    $qsPagas     = http_build_query(array_merge($filtrosBase, ['status' => 'paga', 'de' => date('Y-m-01'), 'ate' => date('Y-m-t')]));
                    ?>
                    <div class="fp-quick-filters">
                        <span class="fp-qf-label">Acesso rápido:</span>
                        <a href="/financeiro/pagar?<?= $qsVencida ?>"
                            class="fp-qf-chip fp-qf-danger <?= ($filtros['status'] ?? '') === 'vencida' ? 'active' : '' ?>">
                            <i class="ti ti-alert-triangle"></i> Vencidas
                            <?php if (($totais['qtd_vencidas'] ?? 0) > 0): ?>
                                <span class="fp-qf-badge"><?= $totais['qtd_vencidas'] ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="/financeiro/pagar?<?= $qsPendente ?>"
                            class="fp-qf-chip <?= ($filtros['status'] ?? '') === 'pendente' ? 'active' : '' ?>">
                            <i class="ti ti-clock"></i> Pendentes
                        </a>
                        <a href="/financeiro/pagar?<?= $qsProximas ?>"
                            class="fp-qf-chip">
                            <i class="ti ti-calendar-due"></i> Vencem em 7 dias
                        </a>
                        <a href="/financeiro/pagar?<?= $qsPagas ?>"
                            class="fp-qf-chip fp-qf-success <?= ($filtros['status'] ?? '') === 'paga' ? 'active' : '' ?>">
                            <i class="ti ti-circle-check"></i> Pagas este mês
                        </a>
                    </div>

                </form>
            </div>

            <!-- ═══════════════════════════════════════
                 TABELA
            ═══════════════════════════════════════ -->
            <div class="zf-table-card" style="overflow:hidden;">

                <?php if (empty($contas)): ?>
                    <div class="fp-empty">
                        <div class="fp-empty-icon"><i class="ti ti-inbox"></i></div>
                        <div class="fp-empty-title">Nenhuma conta encontrada</div>
                        <div class="fp-empty-sub">Tente ajustar os filtros ou <a href="/financeiro/pagar/criar">crie uma nova conta</a>.</div>
                    </div>
                <?php else: ?>

                    <table class="zf-table fp-table">
                        <thead>
                            <tr>
                                <th style="width:36px;">
                                    <input type="checkbox" id="sel-all" class="fp-checkbox" title="Selecionar todos">
                                </th>
                                <th>Descrição</th>
                                <th>Fornecedor</th>
                                <th style="width:110px;">Categoria</th>
                                <th style="width:110px;">Vencimento</th>
                                <th style="width:110px;text-align:right;">Valor</th>
                                <th style="width:100px;text-align:right;">Pago</th>
                                <th style="width:100px;">Forma</th>
                                <th style="width:100px;text-align:center;">Status</th>
                                <th style="width:120px;text-align:right;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contas as $c):
                                $isVencida   = $c['status_real'] === 'vencida';
                                $isPendente  = $c['status_real'] === 'pendente';
                                $isPaga      = $c['status_real'] === 'pago';
                                $isCancelada = $c['status_real'] === 'cancelado';

                                // Dias em atraso
                                $diasAtraso = 0;
                                if ($isVencida) {
                                    $diasAtraso = (int) floor((time() - strtotime($c['vencimento'])) / 86400);
                                }
                            ?>
                                <tr class="fp-tr <?= $isVencida ? 'fp-tr-danger' : '' ?>">
                                    <td>
                                        <input type="checkbox" class="fp-checkbox fp-sel-item" value="<?= $c['id'] ?>">
                                    </td>
                                    <td>
                                        <div class="fp-desc-nome"><?= e($c['descricao']) ?></div>
                                        <?php if (!empty($c['documento'])): ?>
                                            <div class="fp-desc-doc">
                                                <i class="ti ti-file-text"></i> Doc: <?= e($c['documento']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['fornecedor_nome'])): ?>
                                            <span class="fp-fornecedor"><?= e($c['fornecedor_nome']) ?></span>
                                        <?php else: ?>
                                            <span style="color:#D1D5DB;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['categoria'])): ?>
                                            <span class="fp-categoria-tag"><?= e($c['categoria']) ?></span>
                                        <?php else: ?>
                                            <span style="color:#D1D5DB;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fp-venc <?= $isVencida ? 'fp-venc-late' : '' ?>">
                                            <?= date('d/m/Y', strtotime($c['vencimento'])) ?>
                                        </div>
                                        <?php if ($isVencida && $diasAtraso > 0): ?>
                                            <div class="fp-atraso">
                                                <?= $diasAtraso ?>d em atraso
                                            </div>
                                        <?php elseif ($isPendente): ?>
                                            <?php
                                            $diasRestantes = (int) ceil((strtotime($c['vencimento']) - time()) / 86400);
                                            if ($diasRestantes >= 0 && $diasRestantes <= 7): ?>
                                                <div class="fp-atraso" style="color:#D97706;">
                                                    vence em <?= $diasRestantes ?>d
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <div class="fp-valor">R$ <?= number_format($c['valor'], 2, ',', '.') ?></div>
                                    </td>
                                    <td style="text-align:right;">
                                        <?php if ($c['valor_pago'] > 0): ?>
                                            <div style="font-size:13px;color:#16A34A;font-weight:500;">
                                                R$ <?= number_format($c['valor_pago'], 2, ',', '.') ?>
                                            </div>
                                            <?php if ($c['valor_pago'] < $c['valor']): ?>
                                                <div style="font-size:11px;color:#D97706;">parcial</div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color:#D1D5DB;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['forma_pagamento'])): ?>
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
                                                <?= $formaLabel[$c['forma_pagamento']] ?? e($c['forma_pagamento']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color:#D1D5DB;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php
                                        $statusConfig = [
                                            'pendente'  => ['label' => 'Pendente',  'color' => '#D97706', 'bg' => '#FEF3C7'],
                                            'vencida'   => ['label' => 'Vencida',   'color' => '#DC2626', 'bg' => '#FEE2E2'],
                                            'pago'      => ['label' => 'Paga',      'color' => '#16A34A', 'bg' => '#DCFCE7'],
                                            'cancelado' => ['label' => 'Cancelada', 'color' => '#9CA3AF', 'bg' => '#F3F4F6'],
                                        ];
                                        $sc = $statusConfig[$c['status_real']] ?? $statusConfig['pendente'];
                                        ?>
                                        <span class="fp-badge" style="color:<?= $sc['color'] ?>;background:<?= $sc['bg'] ?>;">
                                            <?= $sc['label'] ?>
                                        </span>
                                    </td>
                                    <td style="text-align:right;">
                                        <div class="fp-actions">
                                            <?php if ($isPendente || $isVencida): ?>
                                                <button type="button"
                                                    class="fp-act fp-act-pay"
                                                    onclick="abrirBaixa(<?= $c['id'] ?>, <?= (float)$c['valor'] ?>, <?= (float)$c['valor_pago'] ?>)"
                                                    title="Registrar pagamento">
                                                    <i class="ti ti-check"></i>
                                                    <span>Baixar</span>
                                                </button>
                                                <button type="button"
                                                    class="fp-act fp-act-del"
                                                    onclick="abrirCancelar(<?= $c['id'] ?>)"
                                                    title="Cancelar conta">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            <?php elseif ($isPaga): ?>
                                                <span class="fp-pago-data">
                                                    <i class="ti ti-circle-check"></i>
                                                    <?= !empty($c['data_pagamento'])
                                                        ? date('d/m/Y', strtotime($c['data_pagamento']))
                                                        : 'Paga' ?>
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

        </div><!-- /zf-content -->
    </div>
</div>

<!-- ═══════════════════════════════════════
     MODAL BAIXA
═══════════════════════════════════════ -->
<div id="modal-baixa" class="fp-modal-overlay" onclick="if(event.target===this)fecharModal('modal-baixa')">
    <div class="fp-modal">
        <div class="fp-modal-header">
            <div class="fp-modal-title">
                <span class="fp-modal-icon" style="background:#DCFCE7;color:#16A34A;">
                    <i class="ti ti-check"></i>
                </span>
                Registrar Baixa
            </div>
            <button type="button" class="fp-modal-close" onclick="fecharModal('modal-baixa')">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <form id="form-baixa" method="POST">
            <?= csrf_field() ?>
            <div class="fp-modal-body">

                <!-- Resumo do valor -->
                <div class="fp-baixa-resumo" id="baixa-resumo">
                    <div>
                        <div style="font-size:11px;color:#6B7280;text-transform:uppercase;letter-spacing:.5px;">Total da conta</div>
                        <div style="font-size:20px;font-weight:700;" id="baixa-total-display">R$ 0,00</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:11px;color:#6B7280;text-transform:uppercase;letter-spacing:.5px;">Saldo a pagar</div>
                        <div style="font-size:20px;font-weight:700;color:#DC2626;" id="baixa-saldo-display">R$ 0,00</div>
                    </div>
                </div>

                <div class="fp-modal-fields">

                    <div class="fp-field">
                        <label class="fp-label">Valor pago (R$) <span style="color:#DC2626">*</span></label>
                        <input type="text" name="valor_pago" id="baixa-valor"
                            class="fp-input fp-input-money" required
                            placeholder="0,00" onclick="this.select()">
                    </div>

                    <div class="fp-field">
                        <label class="fp-label">Forma de pagamento <span style="color:#DC2626">*</span></label>
                        <div class="fp-pgto-grid">
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
                                <label class="fp-pgto-opt">
                                    <input type="radio" name="forma_pagamento"
                                        value="<?= $f['val'] ?>"
                                        <?= $f['val'] === 'pix' ? 'checked' : '' ?>>
                                    <span class="fp-pgto-face">
                                        <i class="ti <?= $f['icon'] ?>"></i>
                                        <?= $f['label'] ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <!-- fallback hidden select para submit -->
                        <select name="_forma_backup" style="display:none;" id="forma-backup">
                            <?php foreach ($formas as $f): ?>
                                <option value="<?= $f['val'] ?>"><?= $f['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fp-field">
                        <label class="fp-label">Data do pagamento</label>
                        <input type="date" name="data_pagamento" id="baixa-data"
                            class="fp-input" value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="fp-field">
                        <label class="fp-label">Observação (opcional)</label>
                        <input type="text" name="observacao" class="fp-input"
                            placeholder="Ex: pago via app, comprovante #123...">
                    </div>

                </div>
            </div>

            <div class="fp-modal-footer">
                <button type="button" class="fp-btn fp-btn-ghost" onclick="fecharModal('modal-baixa')">
                    Cancelar
                </button>
                <button type="submit" class="fp-btn fp-btn-success">
                    <i class="ti ti-check"></i> Confirmar Baixa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════
     MODAL CANCELAR
═══════════════════════════════════════ -->
<div id="modal-cancelar" class="fp-modal-overlay" onclick="if(event.target===this)fecharModal('modal-cancelar')">
    <div class="fp-modal" style="max-width:400px;">
        <div class="fp-modal-header">
            <div class="fp-modal-title">
                <span class="fp-modal-icon" style="background:#FEE2E2;color:#DC2626;">
                    <i class="ti ti-alert-triangle"></i>
                </span>
                Cancelar Conta
            </div>
            <button type="button" class="fp-modal-close" onclick="fecharModal('modal-cancelar')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form id="form-cancelar" method="POST">
            <?= csrf_field() ?>
            <div class="fp-modal-body">
                <p style="font-size:13px;color:#6B7280;margin-bottom:16px;">
                    Esta ação não pode ser desfeita. A conta será marcada como cancelada.
                </p>
                <div class="fp-field">
                    <label class="fp-label">Motivo do cancelamento</label>
                    <textarea name="motivo" class="fp-input"
                        style="resize:vertical;min-height:80px;"
                        placeholder="Descreva brevemente o motivo..."></textarea>
                </div>
            </div>
            <div class="fp-modal-footer">
                <button type="button" class="fp-btn fp-btn-ghost" onclick="fecharModal('modal-cancelar')">
                    Voltar
                </button>
                <button type="submit" class="fp-btn fp-btn-danger">
                    <i class="ti ti-x"></i> Cancelar conta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════
     ESTILOS
═══════════════════════════════════════ -->
<style>
    /* ── KPI Cards ── */
    .fp-kpi {
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 12px;
        padding: 18px 20px 14px;
        position: relative;
        overflow: hidden;
        transition: box-shadow .15s;
    }

    .fp-kpi:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, .07);
    }

    .fp-kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .fp-kpi-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6B7280;
    }

    .fp-kpi-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .fp-kpi-value {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -.3px;
        line-height: 1;
        margin-bottom: 4px;
    }

    .fp-kpi-sub {
        font-size: 12px;
        color: #9CA3AF;
        margin-bottom: 12px;
    }

    .fp-kpi-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #F3F4F6;
    }

    .fp-kpi-bar::after {
        content: '';
        display: block;
        height: 100%;
        width: var(--p, 0%);
        background: var(--c, #D1D5DB);
        border-radius: 0 2px 2px 0;
        transition: width .6s ease;
    }

    /* ── Filter Card ── */
    .fp-filter-card {
        padding: 16px 20px 0;
    }

    .fp-filter-row {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        padding-bottom: 14px;
    }

    .fp-filter-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .fp-field-busca {
        flex: 1;
        min-width: 220px;
    }

    .fp-label {
        font-size: 11px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    /* Inputs */
    .fp-input {
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

    .fp-input:focus {
        border-color: #1A1A1A;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, .05);
    }

    .fp-input-icon-wrap {
        position: relative;
    }

    .fp-input-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
        font-size: 14px;
        pointer-events: none;
    }

    .fp-input-with-icon {
        padding-left: 32px;
    }

    .fp-select-wrap {
        position: relative;
    }

    .fp-select {
        appearance: none;
        padding-right: 28px;
        cursor: pointer;
    }

    .fp-select-arrow {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
        font-size: 12px;
        pointer-events: none;
    }

    .fp-date-range {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .fp-input-date {
        width: 130px;
    }

    .fp-date-sep {
        color: #9CA3AF;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* Filter actions */
    .fp-filter-actions {
        display: flex;
        gap: 6px;
        align-items: flex-end;
    }

    /* Quick filters */
    .fp-quick-filters {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 10px 0 14px;
        border-top: 1px solid rgba(0, 0, 0, .06);
        flex-wrap: wrap;
    }

    .fp-qf-label {
        font-size: 11px;
        color: #9CA3AF;
        font-weight: 500;
        margin-right: 2px;
    }

    .fp-qf-chip {
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

    .fp-qf-chip:hover {
        background: #F3F4F6;
        border-color: rgba(0, 0, 0, .18);
    }

    .fp-qf-chip.active {
        background: #1A1A1A;
        color: #fff;
        border-color: #1A1A1A;
    }

    .fp-qf-danger {
        color: #DC2626;
        border-color: #FECACA;
        background: #FEF2F2;
    }

    .fp-qf-danger:hover {
        background: #FEE2E2;
    }

    .fp-qf-danger.active {
        background: #DC2626;
        color: #fff;
        border-color: #DC2626;
    }

    .fp-qf-success {
        color: #16A34A;
        border-color: #BBF7D0;
        background: #F0FDF4;
    }

    .fp-qf-success:hover {
        background: #DCFCE7;
    }

    .fp-qf-badge {
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
        line-height: 1;
    }

    /* Buttons */
    .fp-btn {
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

    .fp-btn:hover {
        opacity: .88;
    }

    .fp-btn-primary {
        background: #1A1A1A;
        color: #fff;
    }

    .fp-btn-dark {
        background: #1A1A1A;
        color: #fff;
    }

    .fp-btn-ghost {
        background: #fff;
        color: #374151;
        border: 1px solid rgba(0, 0, 0, .14);
    }

    .fp-btn-ghost:hover {
        background: #F9FAFB;
    }

    .fp-btn-success {
        background: #16A34A;
        color: #fff;
    }

    .fp-btn-danger {
        background: #DC2626;
        color: #fff;
    }

    /* Table */
    .fp-table tbody tr {
        transition: background .1s;
    }

    .fp-tr-danger td:first-child {
        border-left: 3px solid #FCA5A5;
    }

    .fp-desc-nome {
        font-weight: 500;
        font-size: 13px;
    }

    .fp-desc-doc {
        font-size: 11px;
        color: #9CA3AF;
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .fp-fornecedor {
        font-size: 13px;
        color: #374151;
    }

    .fp-categoria-tag {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        background: #F3F4F6;
        color: #374151;
        font-size: 11px;
        font-weight: 500;
    }

    .fp-venc {
        font-size: 13px;
        font-weight: 500;
    }

    .fp-venc-late {
        color: #DC2626;
    }

    .fp-atraso {
        font-size: 11px;
        color: #DC2626;
        font-weight: 500;
        margin-top: 2px;
    }

    .fp-valor {
        font-size: 13px;
        font-weight: 600;
    }

    .fp-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    /* Row actions */
    .fp-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
    }

    .fp-act {
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

    .fp-act-pay {
        background: #F0FDF4;
        color: #16A34A;
        border-color: #BBF7D0;
    }

    .fp-act-pay:hover {
        background: #DCFCE7;
    }

    .fp-act-del {
        background: transparent;
        color: #9CA3AF;
        border-color: transparent;
        padding: 5px 7px;
    }

    .fp-act-del:hover {
        background: #FEE2E2;
        color: #DC2626;
    }

    .fp-pago-data {
        font-size: 12px;
        color: #16A34A;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Empty state */
    .fp-empty {
        padding: 56px 32px;
        text-align: center;
    }

    .fp-empty-icon {
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

    .fp-empty-title {
        font-size: 15px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .fp-empty-sub {
        font-size: 13px;
        color: #9CA3AF;
    }

    .fp-empty-sub a {
        color: #2563EB;
    }

    /* Modal */
    .fp-modal-overlay {
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

    .fp-modal-overlay.show {
        display: flex;
    }

    .fp-modal {
        background: #fff;
        border-radius: 14px;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
        overflow: hidden;
        animation: fpSlideUp .2s ease;
    }

    @keyframes fpSlideUp {
        from {
            opacity: 0;
            transform: translateY(16px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fp-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px 16px;
        border-bottom: 1px solid #F3F4F6;
    }

    .fp-modal-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 15px;
        font-weight: 600;
    }

    .fp-modal-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .fp-modal-close {
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

    .fp-modal-close:hover {
        background: #E5E7EB;
    }

    .fp-modal-body {
        padding: 20px 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .fp-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #F3F4F6;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    /* Baixa resumo */
    .fp-baixa-resumo {
        display: flex;
        justify-content: space-between;
        padding: 14px 16px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 10px;
    }

    /* Campo */
    .fp-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .fp-input-money {
        font-size: 22px;
        font-weight: 700;
        text-align: right;
        letter-spacing: .5px;
        padding: 10px 16px;
    }

    /* Formas de pagamento — grid de botões radio */
    .fp-pgto-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }

    .fp-pgto-opt {
        cursor: pointer;
    }

    .fp-pgto-opt input {
        display: none;
    }

    .fp-pgto-face {
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

    .fp-pgto-face i {
        font-size: 16px;
        color: #9CA3AF;
    }

    .fp-pgto-opt:hover .fp-pgto-face {
        border-color: #9CA3AF;
        background: #F9FAFB;
    }

    .fp-pgto-opt input:checked+.fp-pgto-face {
        border-color: #1A1A1A;
        background: #1A1A1A;
        color: #fff;
    }

    .fp-pgto-opt input:checked+.fp-pgto-face i {
        color: #fff;
    }

    /* checkbox */
    .fp-checkbox {
        width: 15px;
        height: 15px;
        accent-color: #1A1A1A;
        cursor: pointer;
    }
</style>

<script>
    // ─── Modais ───────────────────────────────────────
    function abrirBaixa(id, valor, pago) {
        const saldo = Math.max(0, valor - pago);

        document.getElementById('baixa-total-display').textContent =
            'R$ ' + valor.toFixed(2).replace('.', ',');
        document.getElementById('baixa-saldo-display').textContent =
            'R$ ' + saldo.toFixed(2).replace('.', ',');
        document.getElementById('baixa-valor').value =
            saldo.toFixed(2).replace('.', ',');

        document.getElementById('form-baixa').action =
            '/financeiro/pagar/' + id + '/baixar';

        document.getElementById('modal-baixa').classList.add('show');
        setTimeout(() => document.getElementById('baixa-valor').select(), 120);
    }

    function abrirCancelar(id) {
        document.getElementById('form-cancelar').action =
            '/financeiro/pagar/' + id + '/cancelar';
        document.getElementById('modal-cancelar').classList.add('show');
    }

    function fecharModal(id) {
        document.getElementById(id).classList.remove('show');
    }

    // Fecha com ESC
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            fecharModal('modal-baixa');
            fecharModal('modal-cancelar');
        }
    });

    // ─── Máscara monetária no campo de baixa ───────────
    document.getElementById('baixa-valor').addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '');
        v = (parseInt(v || 0) / 100).toFixed(2).replace('.', ',');
        this.value = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    });

    // ─── Checkbox "selecionar todos" ───────────────────
    document.getElementById('sel-all')?.addEventListener('change', function() {
        document.querySelectorAll('.fp-sel-item').forEach(cb => {
            cb.checked = this.checked;
        });
    });
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>