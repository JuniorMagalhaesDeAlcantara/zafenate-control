<?php
$f = $fornecedor;
$v = fn(string $campo, mixed $default = '—') => !empty($f[$campo]) ? e($f[$campo]) : $default;

$media = array_filter([
    $f['avaliacao_prazo']       ?? null,
    $f['avaliacao_qualidade']   ?? null,
    $f['avaliacao_atendimento'] ?? null,
]);
$mediaGeral = $media ? round(array_sum($media) / count($media), 1) : null;
?>

<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="zf-layout">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>

    <div class="zf-main">
        <?php
        $pageTitle  = $f['razao_social'];
        $breadcrumb = [
            ['label' => 'Dashboard',    'url' => '/dashboard'],
            ['label' => 'Fornecedores', 'url' => '/fornecedores'],
            ['label' => $f['razao_social'], 'url' => '#'],
        ];
        require VIEW_PATH . '/layouts/navbar.php';
        ?>

        <div class="zf-content">

            <!-- Cabeçalho do perfil -->
            <div style="display:flex;align-items:flex-start;justify-content:space-between;
                         margin-bottom:20px;gap:16px;flex-wrap:wrap">
                <div style="display:flex;align-items:center;gap:16px">
                    <!-- Avatar inicial -->
                    <div style="width:52px;height:52px;border-radius:var(--radius-md);
                                 background:var(--bg-input);border:1px solid var(--border);
                                 display:flex;align-items:center;justify-content:center;
                                 font-size:20px;font-weight:600;color:var(--text-secondary);
                                 flex-shrink:0">
                        <?= strtoupper(mb_substr($f['razao_social'], 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-size:18px;font-weight:600"><?= e($f['razao_social']) ?></div>
                        <?php if (!empty($f['nome_fantasia'])): ?>
                            <div class="text-muted text-sm"><?= e($f['nome_fantasia']) ?></div>
                        <?php endif; ?>
                        <div style="margin-top:6px;display:flex;gap:6px;flex-wrap:wrap">
                            <span class="badge <?= $f['ativo'] ? 'badge-success' : 'badge-neutral' ?>">
                                <?= $f['ativo'] ? 'Ativo' : 'Inativo' ?>
                            </span>
                            <span class="badge badge-info">
                                <?= $f['tipo_pessoa'] === 'juridica' ? 'Pessoa Jurídica' : 'Pessoa Física' ?>
                            </span>
                            <?php if ($mediaGeral): ?>
                                <span class="badge" style="background:var(--bg-warning);color:var(--color-warning)">
                                    ★ <?= number_format($mediaGeral, 1) ?>/5
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <a href="/fornecedores/<?= $f['id'] ?>/editar" class="btn btn-outline">
                        <i class="ti ti-pencil"></i> Editar
                    </a>
                    <form action="/fornecedores/<?= $f['id'] ?>/status" method="POST" style="margin:0">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline"
                            style="color:<?= $f['ativo'] ? 'var(--color-danger)' : 'var(--color-success)' ?>"
                            data-confirm="<?= $f['ativo'] ? 'Desativar este fornecedor?' : 'Ativar este fornecedor?' ?>">
                            <i class="ti <?= $f['ativo'] ? 'ti-lock' : 'ti-lock-open' ?>"></i>
                            <?= $f['ativo'] ? 'Desativar' : 'Ativar' ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stats rápidos -->
            <div class="zf-stats mb-20">
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Total de compras</div>
                    <div class="zf-stat-value"><?= $f['total_compras'] ?? 0 ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Valor total comprado</div>
                    <div class="zf-stat-value">R$ <?= number_format($f['valor_total_compras'] ?? 0, 2, ',', '.') ?></div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Última compra</div>
                    <div class="zf-stat-value" style="font-size:18px">
                        <?= !empty($f['ultima_compra']) ? date('d/m/Y', strtotime($f['ultima_compra'])) : '—' ?>
                    </div>
                </div>
                <div class="zf-stat-card">
                    <div class="zf-stat-label">Prazo padrão</div>
                    <div class="zf-stat-value" style="font-size:18px">
                        <?= !empty($f['prazo_pagamento']) ? $f['prazo_pagamento'] . ' dias' : '—' ?>
                    </div>
                </div>
            </div>

            <!-- Grid de informações -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">

                <!-- Dados básicos -->
                <div class="zf-form-card" style="padding:20px">
                    <div style="font-size:12px;font-weight:600;text-transform:uppercase;
                                 letter-spacing:.08em;color:var(--text-tertiary);margin-bottom:14px">
                        <i class="ti ti-building" style="margin-right:4px"></i> Dados Básicos
                    </div>
                    <?php
                    $linhas = [
                        'CNPJ/CPF'   => !empty($f['cnpj_cpf']) ? formatarDocumento($f['cnpj_cpf']) : null,
                        'IE'         => $f['ie']         ?? null,
                        'Cadastrado' => !empty($f['criado_em']) ? date('d/m/Y', strtotime($f['criado_em'])) : null,
                    ];
                    foreach ($linhas as $label => $val): if (!$val) continue; ?>
                        <div style="display:flex;justify-content:space-between;padding:8px 0;
                                     border-bottom:1px solid var(--border);font-size:13px">
                            <span class="text-muted"><?= $label ?></span>
                            <span class="fw-500"><?= e($val) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Contato -->
                <div class="zf-form-card" style="padding:20px">
                    <div style="font-size:12px;font-weight:600;text-transform:uppercase;
                                 letter-spacing:.08em;color:var(--text-tertiary);margin-bottom:14px">
                        <i class="ti ti-phone" style="margin-right:4px"></i> Contato
                    </div>
                    <?php
                    $contatos = [
                        'Telefone' => $f['telefone'] ?? null,
                        'Celular'  => $f['celular']  ?? null,
                        'WhatsApp' => $f['whatsapp'] ?? null,
                        'E-mail'   => $f['email']    ?? null,
                        'Site'     => $f['site']     ?? null,
                        'Contato'  => $f['contato']  ?? null,
                    ];
                    foreach ($contatos as $label => $val): if (!$val) continue; ?>
                        <div style="display:flex;justify-content:space-between;padding:8px 0;
                                     border-bottom:1px solid var(--border);font-size:13px">
                            <span class="text-muted"><?= $label ?></span>
                            <span class="fw-500"><?= e($val) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Endereço -->
                <?php if (!empty($f['logradouro']) || !empty($f['cidade'])): ?>
                    <div class="zf-form-card" style="padding:20px">
                        <div style="font-size:12px;font-weight:600;text-transform:uppercase;
                                 letter-spacing:.08em;color:var(--text-tertiary);margin-bottom:14px">
                            <i class="ti ti-map-pin" style="margin-right:4px"></i> Endereço
                        </div>
                        <div style="font-size:13px;line-height:1.7;color:var(--text-primary)">
                            <?php if (!empty($f['logradouro'])): ?>
                                <?= e($f['logradouro']) ?><?= !empty($f['numero']) ? ', ' . e($f['numero']) : '' ?>
                                <?= !empty($f['complemento']) ? ' — ' . e($f['complemento']) : '' ?><br>
                            <?php endif; ?>
                            <?= !empty($f['bairro']) ? e($f['bairro']) . ' · ' : '' ?>
                            <?= !empty($f['cidade']) ? e($f['cidade']) : '' ?>
                            <?= !empty($f['uf']) ? '/' . e($f['uf']) : '' ?>
                            <?= !empty($f['cep']) ? ' · CEP ' . e($f['cep']) : '' ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Financeiro -->
                <div class="zf-form-card" style="padding:20px">
                    <div style="font-size:12px;font-weight:600;text-transform:uppercase;
                                 letter-spacing:.08em;color:var(--text-tertiary);margin-bottom:14px">
                        <i class="ti ti-coin" style="margin-right:4px"></i> Financeiro
                    </div>
                    <?php
                    $financeiro = [
                        'Forma pagto.'   => $f['forma_pagamento'] ?? null,
                        'Prazo'          => !empty($f['prazo_pagamento']) ? $f['prazo_pagamento'] . ' dias' : null,
                        'Limite crédito' => !empty($f['limite_credito']) ? 'R$ ' . number_format($f['limite_credito'], 2, ',', '.') : null,
                        'Chave Pix'      => $f['chave_pix'] ?? null,
                        'Banco'          => $f['banco']    ?? null,
                    ];
                    foreach ($financeiro as $label => $val): if (!$val) continue; ?>
                        <div style="display:flex;justify-content:space-between;padding:8px 0;
                                     border-bottom:1px solid var(--border);font-size:13px">
                            <span class="text-muted"><?= $label ?></span>
                            <span class="fw-500"><?= e($val) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!empty($f['obs_financeiras'])): ?>
                        <div style="margin-top:10px;font-size:12px;color:var(--text-tertiary);
                                     background:var(--bg-input);padding:8px 10px;border-radius:var(--radius-sm)">
                            <?= e($f['obs_financeiras']) ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Avaliação + obs internas -->
            <?php if ($mediaGeral || !empty($f['obs_internas'])): ?>
                <div class="zf-form-card mb-20" style="padding:20px">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                        <?php if ($mediaGeral): ?>
                            <div>
                                <div style="font-size:12px;font-weight:600;text-transform:uppercase;
                                     letter-spacing:.08em;color:var(--text-tertiary);margin-bottom:14px">
                                    <i class="ti ti-star" style="margin-right:4px"></i> Avaliação
                                </div>
                                <?php
                                $criterios = [
                                    'avaliacao_prazo'       => 'Pontualidade',
                                    'avaliacao_qualidade'   => 'Qualidade',
                                    'avaliacao_atendimento' => 'Atendimento',
                                ];
                                foreach ($criterios as $campo => $label):
                                    $val = (int)($f[$campo] ?? 0);
                                    if (!$val) continue;
                                ?>
                                    <div style="margin-bottom:8px">
                                        <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:12px">
                                            <span class="text-muted"><?= $label ?></span>
                                            <span class="fw-500"><?= $val ?>/5</span>
                                        </div>
                                        <div style="height:4px;background:var(--border);border-radius:4px;overflow:hidden">
                                            <div style="height:100%;width:<?= ($val / 5 * 100) ?>%;
                                                 background:#F59E0B;border-radius:4px"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($f['obs_internas'])): ?>
                            <div>
                                <div style="font-size:12px;font-weight:600;text-transform:uppercase;
                                     letter-spacing:.08em;color:var(--text-tertiary);margin-bottom:14px">
                                    <i class="ti ti-notes" style="margin-right:4px"></i> Observações Internas
                                </div>
                                <div style="font-size:13px;color:var(--text-secondary);line-height:1.6;
                                     background:var(--bg-input);padding:12px;border-radius:var(--radius-sm)">
                                    <?= nl2br(e($f['obs_internas'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Histórico de compras -->
            <div class="zf-table-card">
                <div style="padding:14px 16px;border-bottom:1px solid var(--border);
                             display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:13px;font-weight:500">
                        <i class="ti ti-history" style="margin-right:6px"></i> Histórico de Compras
                    </span>
                    <span style="font-size:11px;color:var(--text-tertiary);
                                  background:var(--bg-input);padding:3px 8px;border-radius:20px">
                        Últimas 20 movimentações
                    </span>
                </div>
                <table class="zf-table">
                    <thead>
                        <tr>
                            <th style="width:110px">Data</th>
                            <th>Produto</th>
                            <th style="width:100px">Qtd</th>
                            <th style="width:120px">Custo Unit.</th>
                            <th style="width:120px">Total</th>
                            <th style="width:120px">Nº NF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historico)): ?>
                            <tr>
                                <td colspan="6" class="td-empty">
                                    <i class="ti ti-history" style="font-size:24px;display:block;margin-bottom:8px;opacity:.3"></i>
                                    Nenhuma compra registrada para este fornecedor.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historico as $h): ?>
                                <tr>
                                    <td class="text-muted text-sm">
                                        <?= date('d/m/Y', strtotime($h['criado_em'])) ?>
                                    </td>
                                    <td>
                                        <div class="td-name"><?= e($h['produto_nome']) ?></div>
                                        <div class="td-sub"><?= e($h['produto_codigo']) ?></div>
                                    </td>
                                    <td class="fw-500"><?= number_format($h['quantidade'], 3, ',', '.') ?></td>
                                    <td>
                                        <?= !empty($h['preco_custo_unitario'])
                                            ? 'R$ ' . number_format($h['preco_custo_unitario'], 2, ',', '.')
                                            : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td class="fw-500">
                                        <?php
                                        $total = ($h['quantidade'] ?? 0) * ($h['preco_custo_unitario'] ?? 0);
                                        echo $total > 0 ? 'R$ ' . number_format($total, 2, ',', '.') : '<span class="text-muted">—</span>';
                                        ?>
                                    </td>
                                    <td class="text-muted text-sm">
                                        <?= !empty($h['numero_nf']) ? e($h['numero_nf']) : '—' ?>
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