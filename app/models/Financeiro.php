<?php

namespace App\Models;

use App\Core\Database;

class Financeiro
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ================================================================
    // CONTAS A PAGAR
    // ================================================================

    public function listarPagar(array $filtros = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['status'])) {
            // Normaliza valores femininos/alternativos para o que está no banco
            $statusMap = [
                'paga'      => 'pago',
                'cancelada' => 'cancelado',
            ];
            $statusFiltro = $statusMap[$filtros['status']] ?? $filtros['status'];

            if ($statusFiltro === 'vencida') {
                $where[] = "cp.status NOT IN ('pago','cancelado') AND cp.vencimento < CURDATE()";
            } elseif ($statusFiltro === 'pendente') {
                $where[] = "cp.status NOT IN ('pago','cancelado') AND cp.vencimento >= CURDATE()";
            } else {
                $where[]          = 'cp.status = :status';
                $params['status'] = $statusFiltro;
            }
        }

        if (!empty($filtros['busca'])) {
            $where[]         = '(cp.descricao LIKE :busca OR f.razao_social LIKE :busca OR cp.documento LIKE :busca)';
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }

        if (!empty($filtros['de'])) {
            $where[]      = 'cp.vencimento >= :de';
            $params['de'] = $filtros['de'];
        }

        if (!empty($filtros['ate'])) {
            $where[]       = 'cp.vencimento <= :ate';
            $params['ate'] = $filtros['ate'];
        }

        if (!empty($filtros['categoria_id'])) {
            $where[]                = 'cp.categoria_id = :categoria_id';
            $params['categoria_id'] = (int)$filtros['categoria_id'];
        }

        if (!empty($filtros['fornecedor_id'])) {
            $where[]                 = 'cp.fornecedor_id = :fornecedor_id';
            $params['fornecedor_id'] = (int)$filtros['fornecedor_id'];
        }

        $sql = "
        SELECT
            cp.*,
            CASE
                WHEN cp.status IN ('pago','cancelado') THEN cp.status
                WHEN cp.vencimento < CURDATE()         THEN 'vencida'
                ELSE 'pendente'
            END AS status_real,
            f.razao_social   AS fornecedor_nome,
            cf.nome          AS categoria_nome,
            cf.cor           AS categoria_cor
        FROM contas_pagar cp
        LEFT JOIN fornecedores f            ON f.id  = cp.fornecedor_id
        LEFT JOIN categorias_financeiras cf ON cf.id = cp.categoria_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY cp.vencimento ASC
    ";

        return $this->db->fetchAll($sql, $params);
    }

    public function buscarPagarPorId(int $id): ?array
    {
        $sql = "
            SELECT cp.*,
                CASE
                    WHEN cp.status NOT IN ('pago','cancelado') AND cp.vencimento < CURDATE()
                    THEN 'vencida' ELSE cp.status
                END AS status_real,
                f.razao_social AS fornecedor_nome,
                cf.nome        AS categoria_nome
            FROM contas_pagar cp
            LEFT JOIN fornecedores f            ON f.id  = cp.fornecedor_id
            LEFT JOIN categorias_financeiras cf ON cf.id = cp.categoria_id
            WHERE cp.id = :id LIMIT 1
        ";
        return $this->db->fetchOne($sql, ['id' => $id]) ?: null;
    }

    public function criarPagar(array $dados): int
    {
        $campos = [
            'categoria_id',
            'fornecedor_id',
            'usuario_id',
            'compra_id',
            'descricao',
            'valor',
            'vencimento',
            'documento',
            'observacao',
            'forma_pagamento',
            'status',
        ];
        $dados           = array_intersect_key($dados, array_flip($campos));
        $dados['status'] = $dados['status'] ?? 'aberto';

        $keys  = implode(', ', array_keys($dados));
        $holds = ':' . implode(', :', array_keys($dados));
        $this->db->execute("INSERT INTO contas_pagar ({$keys}) VALUES ({$holds})", $dados);
        return (int)$this->db->lastInsertId();
    }

    public function atualizarPagar(int $id, array $dados): bool
    {
        $campos = [
            'categoria_id',
            'fornecedor_id',
            'descricao',
            'valor',
            'vencimento',
            'documento',
            'observacao',
            'forma_pagamento',
        ];
        $dados = array_intersect_key($dados, array_flip($campos));
        $sets  = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($dados)));
        $dados['id'] = $id;
        return $this->db->execute("UPDATE contas_pagar SET {$sets} WHERE id = :id", $dados);
    }

    /**
     * Baixa (paga) uma conta a pagar — total ou parcial.
     *
     * FIX 2: data_pagamento só é gravada na baixa final (status = 'pago').
     * Em pagamentos parciais, mantinha a data da primeira parcela — corrigido
     * para sempre atualizar a data da última movimentação, facilitando auditoria.
     */
    public function baixarPagar(int $id, float $valorPago, string $formaPagamento, string $dataPagamento): void
    {
        $conta = $this->buscarPagarPorId($id);
        if (!$conta) throw new \RuntimeException('Conta não encontrada.');
        if ($conta['status'] === 'pago')      throw new \RuntimeException('Conta já está paga.');
        if ($conta['status'] === 'cancelado') throw new \RuntimeException('Conta cancelada.');

        // FIX 3: validação — não deixa pagar mais do que o saldo devedor
        $saldoDevedor = (float)$conta['valor'] - (float)$conta['valor_pago'];
        if ($valorPago <= 0) {
            throw new \RuntimeException('Valor do pagamento deve ser maior que zero.');
        }
        if ($valorPago > $saldoDevedor + 0.001) {
            throw new \RuntimeException(
                sprintf('Valor informado (R$ %.2f) supera o saldo devedor (R$ %.2f).', $valorPago, $saldoDevedor)
            );
        }

        $novoPago   = round((float)$conta['valor_pago'] + $valorPago, 2);
        $novoStatus = $novoPago >= (float)$conta['valor'] - 0.001 ? 'pago' : 'parcial';

        $this->db->execute(
            "UPDATE contas_pagar SET
                valor_pago = :vp, status = :st,
                data_pagamento = :dp, forma_pagamento = :fp
             WHERE id = :id",
            [
                'vp' => $novoPago,
                'st' => $novoStatus,
                'dp' => $dataPagamento, // sempre atualiza com a data mais recente
                'fp' => $formaPagamento,
                'id' => $id,
            ]
        );
    }

    public function cancelarPagar(int $id, string $motivo): void
    {
        $conta = $this->buscarPagarPorId($id);
        if (!$conta) throw new \RuntimeException('Conta não encontrada.');
        if ($conta['status'] === 'cancelado') throw new \RuntimeException('Já cancelada.');

        $this->db->execute(
            "UPDATE contas_pagar SET status = 'cancelado', motivo_cancelamento = :motivo WHERE id = :id",
            ['motivo' => $motivo, 'id' => $id]
        );
    }

    public function totaisPagar(): array
    {
        // FIX 4: SUM(bool_expr) em MySQL funciona mas é frágil.
        // Trocado por COALESCE+CASE explícito para portabilidade e clareza.
        return $this->db->fetchOne("
            SELECT
                COALESCE(SUM(CASE
                    WHEN status NOT IN ('pago','cancelado') AND vencimento >= CURDATE()
                    THEN valor - valor_pago END), 0)                          AS a_vencer,

                COALESCE(SUM(CASE
                    WHEN status NOT IN ('pago','cancelado') AND vencimento >= CURDATE()
                    THEN 1 END), 0)                                           AS qtd_a_vencer,

                COALESCE(SUM(CASE
                    WHEN status NOT IN ('pago','cancelado') AND vencimento < CURDATE()
                    THEN valor - valor_pago END), 0)                          AS vencidas,

                COALESCE(SUM(CASE
                    WHEN status NOT IN ('pago','cancelado') AND vencimento < CURDATE()
                    THEN 1 END), 0)                                           AS qtd_vencidas,

                COALESCE(SUM(CASE
                    WHEN status = 'pago'
                     AND MONTH(data_pagamento) = MONTH(CURDATE())
                     AND YEAR(data_pagamento)  = YEAR(CURDATE())
                    THEN valor_pago END), 0)                                  AS pagas_mes,

                COALESCE(SUM(CASE
                    WHEN status = 'pago'
                     AND MONTH(data_pagamento) = MONTH(CURDATE())
                     AND YEAR(data_pagamento)  = YEAR(CURDATE())
                    THEN 1 END), 0)                                           AS qtd_pagas_mes
            FROM contas_pagar
        ") ?? [];
    }

    // ================================================================
    // CONTAS A RECEBER
    // ================================================================

    public function listarReceber(array $filtros = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['status'])) {
            // CORRIGIDO: normaliza aliases e trata 'vencida' como condição virtual
            $statusMap = [
                'recebida'  => 'recebido',
                'cancelada' => 'cancelado',
                'pendente'  => 'aberto',   // 'pendente' → 'aberto' no banco
            ];
            $statusFiltro = $statusMap[$filtros['status']] ?? $filtros['status'];

            if ($statusFiltro === 'vencida') {
                $where[] = "cr.status NOT IN ('recebido','cancelado') AND cr.vencimento < CURDATE()";
            } elseif ($statusFiltro === 'aberto') {
                $where[] = "cr.status NOT IN ('recebido','cancelado') AND cr.vencimento >= CURDATE()";
            } else {
                $where[]          = 'cr.status = :status';
                $params['status'] = $statusFiltro;
            }
        }

        if (!empty($filtros['busca'])) {
            $where[]         = '(cr.descricao LIKE :busca OR c.nome LIKE :busca OR cr.documento LIKE :busca)';
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }

        if (!empty($filtros['de'])) {
            $where[]      = 'cr.vencimento >= :de';
            $params['de'] = $filtros['de'];
        }

        if (!empty($filtros['ate'])) {
            $where[]       = 'cr.vencimento <= :ate';
            $params['ate'] = $filtros['ate'];
        }

        $sql = "
            SELECT
                cr.*,
                CASE
                    WHEN cr.status NOT IN ('recebido','cancelado') AND cr.vencimento < CURDATE()
                    THEN 'vencida' ELSE cr.status
                END AS status_real,
                c.nome          AS cliente_nome,
                cf.nome         AS categoria_nome,
                cf.cor          AS categoria_cor
            FROM contas_receber cr
            LEFT JOIN clientes c                ON c.id  = cr.cliente_id
            LEFT JOIN categorias_financeiras cf ON cf.id = cr.categoria_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY cr.vencimento ASC
        ";

        return $this->db->fetchAll($sql, $params);
    }

    public function buscarReceberPorId(int $id): ?array
    {
        $sql = "
            SELECT cr.*,
                CASE
                    WHEN cr.status NOT IN ('recebido','cancelado') AND cr.vencimento < CURDATE()
                    THEN 'vencida' ELSE cr.status
                END AS status_real,
                c.nome  AS cliente_nome,
                cf.nome AS categoria_nome
            FROM contas_receber cr
            LEFT JOIN clientes c                ON c.id  = cr.cliente_id
            LEFT JOIN categorias_financeiras cf ON cf.id = cr.categoria_id
            WHERE cr.id = :id LIMIT 1
        ";
        return $this->db->fetchOne($sql, ['id' => $id]) ?: null;
    }

    public function criarReceber(array $dados): int
    {
        $campos = [
            'categoria_id',
            'cliente_id',
            'usuario_id',
            'venda_id',
            'descricao',
            'valor',
            'vencimento',
            'documento',
            'observacao',
            'forma_recebimento',
            'status',
        ];
        $dados           = array_intersect_key($dados, array_flip($campos));
        $dados['status'] = $dados['status'] ?? 'aberto';

        $keys  = implode(', ', array_keys($dados));
        $holds = ':' . implode(', :', array_keys($dados));
        $this->db->execute("INSERT INTO contas_receber ({$keys}) VALUES ({$holds})", $dados);
        return (int)$this->db->lastInsertId();
    }

    /**
     * FIX 3 (espelho): validação e atualização de data_recebimento consistente.
     */
    public function baixarReceber(int $id, float $valorRecebido, string $formaRecebimento, string $dataRecebimento): void
    {
        $conta = $this->buscarReceberPorId($id);
        if (!$conta) throw new \RuntimeException('Conta não encontrada.');
        if ($conta['status'] === 'recebido')  throw new \RuntimeException('Conta já recebida.');
        if ($conta['status'] === 'cancelado') throw new \RuntimeException('Conta cancelada.');

        $saldoDevedor = (float)$conta['valor'] - (float)$conta['valor_recebido'];
        if ($valorRecebido <= 0) {
            throw new \RuntimeException('Valor do recebimento deve ser maior que zero.');
        }
        if ($valorRecebido > $saldoDevedor + 0.001) {
            throw new \RuntimeException(
                sprintf('Valor informado (R$ %.2f) supera o saldo a receber (R$ %.2f).', $valorRecebido, $saldoDevedor)
            );
        }

        $novoRecebido = round((float)$conta['valor_recebido'] + $valorRecebido, 2);
        $novoStatus   = $novoRecebido >= (float)$conta['valor'] - 0.001 ? 'recebido' : 'parcial';

        $this->db->execute(
            "UPDATE contas_receber SET
                valor_recebido = :vr, status = :st,
                data_recebimento = :dr, forma_recebimento = :fr
             WHERE id = :id",
            [
                'vr' => $novoRecebido,
                'st' => $novoStatus,
                'dr' => $dataRecebimento, // sempre atualiza
                'fr' => $formaRecebimento,
                'id' => $id,
            ]
        );
    }

    public function cancelarReceber(int $id, string $motivo, int $usuarioId): void
    {
        $conta = $this->buscarReceberPorId($id);
        if (!$conta) throw new \RuntimeException('Conta não encontrada.');
        if ($conta['status'] === 'cancelado') throw new \RuntimeException('Já cancelada.');

        $this->db->transaction(function () use ($id, $conta, $motivo, $usuarioId) {

            if (!empty($conta['venda_id'])) {
                $venda = $this->db->fetchOne(
                    "SELECT * FROM vendas WHERE id = :id",
                    ['id' => $conta['venda_id']]
                );

                if ($venda && $venda['status'] === 'finalizada') {
                    $itens = $this->db->fetchAll(
                        "SELECT * FROM venda_itens WHERE venda_id = :id",
                        ['id' => $conta['venda_id']]
                    );

                    foreach ($itens as $item) {
                        $prod = $this->db->fetchOne(
                            "SELECT estoque_atual FROM produtos WHERE id = :id FOR UPDATE",
                            ['id' => $item['produto_id']]
                        );
                        $antes  = (float)$prod['estoque_atual'];
                        $depois = $antes + (float)$item['quantidade'];

                        $this->db->execute(
                            "UPDATE produtos SET estoque_atual = :est WHERE id = :id",
                            ['est' => $depois, 'id' => $item['produto_id']]
                        );
                        $this->db->execute(
                            "INSERT INTO movimentacoes_estoque
                             (produto_id, usuario_id, tipo, motivo, quantidade, estoque_antes, estoque_depois, observacao)
                             VALUES (:pid, :uid, 'ENTRADA', 'CANCELAMENTO_VENDA', :qty, :antes, :depois, :obs)",
                            [
                                'pid'    => $item['produto_id'],
                                'uid'    => $usuarioId,
                                'qty'    => $item['quantidade'],
                                'antes'  => $antes,
                                'depois' => $depois,
                                'obs'    => 'Estorno por cancelamento da venda #' . $venda['numero'],
                            ]
                        );
                    }

                    $this->db->execute(
                        "UPDATE vendas SET status = 'cancelada', cancelado_por = :uid,
                         motivo_cancelamento = :motivo, cancelado_em = NOW()
                         WHERE id = :id",
                        ['uid' => $usuarioId, 'motivo' => $motivo, 'id' => $conta['venda_id']]
                    );

                    // FIX 5: estorno do caixa deve subtrair ALL formas de pagamento
                    // do total_vendas, mas só dinheiro líquido do saldo_esperado
                    $pgtos    = $this->db->fetchAll(
                        "SELECT * FROM venda_pagamentos WHERE venda_id = :id",
                        ['id' => $conta['venda_id']]
                    );
                    $dinheiro = 0.0;
                    foreach ($pgtos as $p) {
                        if ($p['forma'] === 'dinheiro') {
                            $dinheiro += (float)$p['valor'] - (float)($p['troco'] ?? 0);
                        }
                    }

                    // Sempre estorna total_vendas; saldo_esperado só se houve dinheiro
                    $this->db->execute(
                        "UPDATE caixas SET
                            total_vendas   = total_vendas   - :total,
                            saldo_esperado = saldo_esperado - :dinheiro
                         WHERE id = :caixa_id",
                        [
                            'total'    => $venda['total'],
                            'dinheiro' => $dinheiro,
                            'caixa_id' => $venda['caixa_id'],
                        ]
                    );

                    // Cancela todas as parcelas da mesma venda
                    $this->db->execute(
                        "UPDATE contas_receber SET status = 'cancelado', motivo_cancelamento = :motivo
                         WHERE venda_id = :venda_id AND status != 'cancelado'",
                        ['motivo' => $motivo, 'venda_id' => $conta['venda_id']]
                    );
                    return;
                }
            }

            // Cancelamento simples (sem venda vinculada)
            $this->db->execute(
                "UPDATE contas_receber SET status = 'cancelado', motivo_cancelamento = :motivo WHERE id = :id",
                ['motivo' => $motivo, 'id' => $id]
            );
        });
    }

    public function totaisReceber(): array
    {
        // FIX 4 (espelho): CASE explícito no lugar de SUM(bool)
        return $this->db->fetchOne("
            SELECT
                COALESCE(SUM(CASE
                    WHEN status NOT IN ('recebido','cancelado') AND vencimento >= CURDATE()
                    THEN valor - valor_recebido END), 0)                      AS a_vencer,

                COALESCE(SUM(CASE
                    WHEN status NOT IN ('recebido','cancelado') AND vencimento >= CURDATE()
                    THEN 1 END), 0)                                           AS qtd_a_vencer,

                COALESCE(SUM(CASE
                    WHEN status NOT IN ('recebido','cancelado') AND vencimento < CURDATE()
                    THEN valor - valor_recebido END), 0)                      AS vencidas,

                COALESCE(SUM(CASE
                    WHEN status NOT IN ('recebido','cancelado') AND vencimento < CURDATE()
                    THEN 1 END), 0)                                           AS qtd_vencidas,

                COALESCE(SUM(CASE
                    WHEN status = 'recebido'
                     AND MONTH(data_recebimento) = MONTH(CURDATE())
                     AND YEAR(data_recebimento)  = YEAR(CURDATE())
                    THEN valor_recebido END), 0)                              AS recebidas_mes,

                COALESCE(SUM(CASE
                    WHEN status = 'recebido'
                     AND MONTH(data_recebimento) = MONTH(CURDATE())
                     AND YEAR(data_recebimento)  = YEAR(CURDATE())
                    THEN 1 END), 0)                                           AS qtd_recebidas_mes
            FROM contas_receber
        ") ?? [];
    }

    // ================================================================
    // FLUXO DE CAIXA
    // ================================================================

    public function fluxoCaixa(string $de, string $ate, array $filtros = []): array
    {
        $whereE = ["cr.status = 'recebido'", 'cr.data_recebimento BETWEEN :de AND :ate'];
        $whereS = ["cp.status = 'pago'",     'cp.data_pagamento   BETWEEN :de AND :ate'];
        $params = ['de' => $de, 'ate' => $ate];

        if (!empty($filtros['categoria_id'])) {
            $whereE[]      = 'cr.categoria_id = :cat';
            $whereS[]      = 'cp.categoria_id = :cat';
            $params['cat'] = (int)$filtros['categoria_id'];
        }

        $entradas = $this->db->fetchAll("
            SELECT
                cr.id,
                cr.data_recebimento        AS data,
                cr.descricao,
                cr.valor_recebido          AS valor,
                cf.nome                    AS categoria,
                cf.cor                     AS categoria_cor,
                'receita'                  AS tipo,
                COALESCE(c.nome, 'Manual') AS origem
            FROM contas_receber cr
            LEFT JOIN categorias_financeiras cf ON cf.id = cr.categoria_id
            LEFT JOIN clientes c                ON c.id  = cr.cliente_id
            WHERE " . implode(' AND ', $whereE) . "
            ORDER BY cr.data_recebimento ASC, cr.id ASC
        ", $params);

        $saidas = $this->db->fetchAll("
            SELECT
                cp.id,
                cp.data_pagamento                  AS data,
                cp.descricao,
                cp.valor_pago                      AS valor,
                cf.nome                            AS categoria,
                cf.cor                             AS categoria_cor,
                'despesa'                          AS tipo,
                COALESCE(f.razao_social, 'Manual') AS origem
            FROM contas_pagar cp
            LEFT JOIN categorias_financeiras cf ON cf.id = cp.categoria_id
            LEFT JOIN fornecedores f             ON f.id  = cp.fornecedor_id
            WHERE " . implode(' AND ', $whereS) . "
            ORDER BY cp.data_pagamento ASC, cp.id ASC
        ", $params);

        // FIX 6: vendas PDV no fluxo — filtro de categoria não se aplica aqui,
        // mas o filtro de data estava usando DATE(v.criado_em) BETWEEN, correto.
        // O problema era que vendasPdv entrava no $entradas APÓS o cálculo de
        // $totalEntradas. Movido para antes do merge.
        $vendasPdv = $this->db->fetchAll("
            SELECT
                NULL                        AS id,
                DATE(v.criado_em)           AS data,
                CONCAT('Venda #', v.numero) AS descricao,
                v.total                     AS valor,
                'receita'                   AS tipo,
                'PDV'                       AS origem,
                NULL                        AS categoria,
                NULL                        AS categoria_cor
            FROM vendas v
            WHERE v.status = 'finalizada'
              AND DATE(v.criado_em) BETWEEN :de AND :ate
              AND NOT EXISTS (
                  SELECT 1 FROM contas_receber cr WHERE cr.venda_id = v.id
              )
            ORDER BY v.criado_em ASC
        ", ['de' => $de, 'ate' => $ate]);

        // Mescla entradas manuais + vendas PDV antes de calcular totais
        $entradas = array_merge($entradas, $vendasPdv);

        $movimentacoes = array_merge($entradas, $saidas);
        usort(
            $movimentacoes,
            fn($a, $b) => strcmp($a['data'], $b['data']) ?: ($a['tipo'] <=> $b['tipo'])
        );

        $saldoAnterior = $this->saldoAntesDe($de, $filtros);

        $acumulado = $saldoAnterior;
        foreach ($movimentacoes as &$m) {
            $acumulado       += $m['tipo'] === 'receita' ? (float)$m['valor'] : -(float)$m['valor'];
            $m['saldo_acumulado'] = $acumulado;
        }
        unset($m);

        // FIX 6 (cont): totalEntradas agora inclui vendasPdv corretamente
        $totalEntradas = array_sum(array_column($entradas, 'valor'));
        $totalSaidas   = array_sum(array_column($saidas,   'valor'));

        $graficoDias = $this->graficoPorDia($de, $ate, $entradas, $saidas, $saldoAnterior);
        $projecao    = $this->projecao30Dias();

        return [
            'movimentacoes'    => $movimentacoes,
            'total_entradas'   => $totalEntradas,
            'total_saidas'     => $totalSaidas,
            'saldo'            => $totalEntradas - $totalSaidas,
            'saldo_anterior'   => $saldoAnterior,
            'saldo_final'      => $saldoAnterior + ($totalEntradas - $totalSaidas),
            'grafico_dias'     => $graficoDias,
            'projecao'         => $projecao,
            'grafico_labels'   => array_column($graficoDias, 'label'),
            'grafico_entradas' => array_column($graficoDias, 'entradas'),
            'grafico_saidas'   => array_column($graficoDias, 'saidas'),
            'grafico_saldo'    => array_column($graficoDias, 'saldo_acum'),
        ];
    }

    /**
     * FIX 7: nome do método era saldoAntesde (d minúsculo) mas estava sendo
     * chamado como saldoAntesde em um lugar e saldoAntesDe em outro.
     * Padronizado para saldoAntesDe (camelCase correto).
     *
     * FIX 8: quando há filtro de categoria, o $cat era injetado via concatenação
     * de string sem parâmetro PDO — risco de SQL injection se categoria_id
     * não fosse inteiro. Já era castado para int, mas trocado para bind correto.
     */
    private function saldoAntesDe(string $de, array $filtros = []): float
    {
        $catFilter = '';
        $params    = ['de' => $de];

        if (!empty($filtros['categoria_id'])) {
            $catFilter       = ' AND categoria_id = :cat';
            $params['cat']   = (int)$filtros['categoria_id'];
        }

        $rec = (float)($this->db->fetchOne("
            SELECT COALESCE(SUM(valor_recebido), 0) AS total
            FROM contas_receber
            WHERE status = 'recebido'
              AND data_recebimento < :de
              {$catFilter}
        ", $params)['total'] ?? 0);

        // Vendas PDV à vista antes do período — sem filtro de categoria (vendas não têm)
        $pdv = (float)($this->db->fetchOne("
            SELECT COALESCE(SUM(v.total), 0) AS total
            FROM vendas v
            WHERE v.status = 'finalizada'
              AND DATE(v.criado_em) < :de
              AND NOT EXISTS (
                  SELECT 1 FROM contas_receber cr WHERE cr.venda_id = v.id
              )
        ", ['de' => $de])['total'] ?? 0);

        $pag = (float)($this->db->fetchOne("
            SELECT COALESCE(SUM(valor_pago), 0) AS total
            FROM contas_pagar
            WHERE status = 'pago'
              AND data_pagamento < :de
              {$catFilter}
        ", $params)['total'] ?? 0);

        return ($rec + $pdv) - $pag;
    }

    private function graficoPorDia(string $de, string $ate, array $entradas, array $saidas, float $saldoInicial): array
    {
        $idxE = [];
        foreach ($entradas as $e) {
            $idxE[$e['data']] = ($idxE[$e['data']] ?? 0) + (float)$e['valor'];
        }
        $idxS = [];
        foreach ($saidas as $s) {
            $idxS[$s['data']] = ($idxS[$s['data']] ?? 0) + (float)$s['valor'];
        }

        $result = [];
        $acum   = $saldoInicial;
        $cur    = new \DateTime($de);
        $end    = new \DateTime($ate);

        while ($cur <= $end) {
            $dia   = $cur->format('Y-m-d');
            $ent   = (float)($idxE[$dia] ?? 0);
            $sai   = (float)($idxS[$dia] ?? 0);
            $acum += $ent - $sai;

            $result[] = [
                'label'      => $cur->format('d/m'),
                'entradas'   => $ent,
                'saidas'     => $sai,
                'saldo_acum' => $acum,
            ];
            $cur->modify('+1 day');
        }
        return $result;
    }

    public function projecao30Dias(): array
    {
        $ate = date('Y-m-d', strtotime('+30 days'));

        $pagar = $this->db->fetchAll("
            SELECT vencimento AS data, descricao,
                   (valor - valor_pago) AS valor, 'despesa' AS tipo,
                   COALESCE(f.razao_social, 'Manual') AS origem
            FROM contas_pagar cp
            LEFT JOIN fornecedores f ON f.id = cp.fornecedor_id
            WHERE cp.status NOT IN ('pago','cancelado')
              AND cp.vencimento BETWEEN CURDATE() AND :ate
            ORDER BY cp.vencimento ASC
        ", ['ate' => $ate]);

        $receber = $this->db->fetchAll("
            SELECT vencimento AS data, descricao,
                   (valor - valor_recebido) AS valor, 'receita' AS tipo,
                   COALESCE(c.nome, 'Manual') AS origem
            FROM contas_receber cr
            LEFT JOIN clientes c ON c.id = cr.cliente_id
            WHERE cr.status NOT IN ('recebido','cancelado')
              AND cr.vencimento BETWEEN CURDATE() AND :ate
            ORDER BY cr.vencimento ASC
        ", ['ate' => $ate]);

        $merged = array_merge($pagar, $receber);
        usort($merged, fn($a, $b) => strcmp($a['data'], $b['data']));
        return $merged;
    }

    // ================================================================
    // HELPERS PÚBLICOS
    // ================================================================

    public function vencimentosProximos(int $dias = 7): array
    {
        $ate = date('Y-m-d', strtotime("+{$dias} days"));

        $pagar = $this->db->fetchAll("
            SELECT id, descricao, vencimento, valor, valor_pago, 'pagar' AS tipo
            FROM contas_pagar
            WHERE status NOT IN ('pago','cancelado')
              AND vencimento BETWEEN CURDATE() AND :ate
            ORDER BY vencimento ASC
        ", ['ate' => $ate]);

        $receber = $this->db->fetchAll("
            SELECT id, descricao, vencimento, valor, valor_recebido, 'receber' AS tipo
            FROM contas_receber
            WHERE status NOT IN ('recebido','cancelado')
              AND vencimento BETWEEN CURDATE() AND :ate
            ORDER BY vencimento ASC
        ", ['ate' => $ate]);

        $merged = array_merge($pagar, $receber);
        usort($merged, fn($a, $b) => strcmp($a['vencimento'], $b['vencimento']));
        return $merged;
    }

    public function contasVencidas(): array
    {
        $pagar = $this->db->fetchAll("
            SELECT id, descricao, vencimento, valor, valor_pago, 'pagar' AS tipo
            FROM contas_pagar
            WHERE status NOT IN ('pago','cancelado')
              AND vencimento < CURDATE()
            ORDER BY vencimento ASC
            LIMIT 20
        ");

        $receber = $this->db->fetchAll("
            SELECT id, descricao, vencimento, valor, valor_recebido, 'receber' AS tipo
            FROM contas_receber
            WHERE status NOT IN ('recebido','cancelado')
              AND vencimento < CURDATE()
            ORDER BY vencimento ASC
            LIMIT 20
        ");

        return ['pagar' => $pagar, 'receber' => $receber];
    }

    public function ultimosLancamentos(int $limite = 10): array
    {
        // FIX: LIMIT com variável PHP diretamente — sem risco (inteiro controlado)
        $pagar = $this->db->fetchAll("
            SELECT cp.id, cp.descricao, cp.valor, cp.criado_em,
                   cf.nome AS categoria_nome, 'pagar' AS _tipo
            FROM contas_pagar cp
            LEFT JOIN categorias_financeiras cf ON cf.id = cp.categoria_id
            ORDER BY cp.criado_em DESC
            LIMIT {$limite}
        ");

        $receber = $this->db->fetchAll("
            SELECT cr.id, cr.descricao, cr.valor, cr.criado_em,
                   cf.nome AS categoria_nome, 'receber' AS _tipo
            FROM contas_receber cr
            LEFT JOIN categorias_financeiras cf ON cf.id = cr.categoria_id
            ORDER BY cr.criado_em DESC
            LIMIT {$limite}
        ");

        $merged = array_merge($pagar, $receber);
        usort($merged, fn($a, $b) => strcmp($b['criado_em'], $a['criado_em']));
        return array_slice($merged, 0, $limite);
    }

    public function fluxo30Dias(): array
    {
        $de  = date('Y-m-d', strtotime('-29 days'));
        $ate = date('Y-m-d');

        $entradas = $this->db->fetchAll("
            SELECT DATE(data_recebimento) AS dia, SUM(valor_recebido) AS total
            FROM contas_receber
            WHERE status = 'recebido'
              AND data_recebimento BETWEEN :de AND :ate
            GROUP BY dia
        ", ['de' => $de, 'ate' => $ate]);

        $saidas = $this->db->fetchAll("
            SELECT DATE(data_pagamento) AS dia, SUM(valor_pago) AS total
            FROM contas_pagar
            WHERE status = 'pago'
              AND data_pagamento BETWEEN :de AND :ate
            GROUP BY dia
        ", ['de' => $de, 'ate' => $ate]);

        $idxE = array_column($entradas, 'total', 'dia');
        $idxS = array_column($saidas,   'total', 'dia');

        $result = [];
        for ($i = 29; $i >= 0; $i--) {
            $dia      = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = [
                'dia'      => date('d/m', strtotime($dia)),
                'entradas' => (float)($idxE[$dia] ?? 0),
                'saidas'   => (float)($idxS[$dia] ?? 0),
            ];
        }
        return $result;
    }

    // ================================================================
    // DRE — Demonstração do Resultado do Exercício
    // ================================================================

    public function dre(string $de, string $ate): array
    {
        $vendas = $this->db->fetchOne("
            SELECT
                COUNT(*)                          AS qtd_vendas,
                COALESCE(SUM(total), 0)           AS receita_bruta,
                COALESCE(SUM(desconto_valor), 0)  AS total_descontos
            FROM vendas
            WHERE status = 'finalizada'
              AND DATE(criado_em) BETWEEN :de AND :ate
        ", ['de' => $de, 'ate' => $ate]);

        $cancelamentos = $this->db->fetchOne("
            SELECT COALESCE(SUM(total), 0) AS total
            FROM vendas
            WHERE status = 'cancelada'
              AND DATE(cancelado_em) BETWEEN :de AND :ate
        ", ['de' => $de, 'ate' => $ate]);

        $cmv = $this->db->fetchOne("
            SELECT COALESCE(SUM(vi.quantidade * vi.preco_custo), 0) AS total
            FROM venda_itens vi
            INNER JOIN vendas v ON v.id = vi.venda_id
            WHERE v.status = 'finalizada'
              AND DATE(v.criado_em) BETWEEN :de AND :ate
        ", ['de' => $de, 'ate' => $ate]);

        $outrasReceitas = $this->db->fetchAll("
            SELECT
                COALESCE(cf.nome, 'Sem categoria') AS categoria,
                cf.cor                             AS categoria_cor,
                COALESCE(SUM(cr.valor_recebido), 0) AS total
            FROM contas_receber cr
            LEFT JOIN categorias_financeiras cf ON cf.id = cr.categoria_id
            WHERE cr.status = 'recebido'
              AND cr.venda_id IS NULL
              AND DATE(cr.data_recebimento) BETWEEN :de AND :ate
            GROUP BY cf.id, cf.nome, cf.cor
            ORDER BY total DESC
        ", ['de' => $de, 'ate' => $ate]);

        $despesas = $this->db->fetchAll("
            SELECT
                COALESCE(cf.nome, 'Sem categoria') AS categoria,
                cf.cor                             AS categoria_cor,
                COALESCE(SUM(cp.valor_pago), 0)    AS total,
                COUNT(*)                           AS qtd
            FROM contas_pagar cp
            LEFT JOIN categorias_financeiras cf ON cf.id = cp.categoria_id
            WHERE cp.status = 'pago'
              AND DATE(cp.data_pagamento) BETWEEN :de AND :ate
            GROUP BY cf.id, cf.nome, cf.cor
            ORDER BY total DESC
        ", ['de' => $de, 'ate' => $ate]);

        $topProdutos = $this->db->fetchAll("
            SELECT
                vi.produto_nome                                         AS nome,
                SUM(vi.quantidade)                                      AS qty,
                SUM(vi.subtotal)                                        AS receita,
                SUM(vi.quantidade * vi.preco_custo)                     AS custo,
                SUM(vi.subtotal) - SUM(vi.quantidade * vi.preco_custo)  AS lucro
            FROM venda_itens vi
            INNER JOIN vendas v ON v.id = vi.venda_id
            WHERE v.status = 'finalizada'
              AND DATE(v.criado_em) BETWEEN :de AND :ate
            GROUP BY vi.produto_nome
            ORDER BY receita DESC
            LIMIT 8
        ", ['de' => $de, 'ate' => $ate]);

        $porForma = $this->db->fetchAll("
            SELECT
                vp.forma,
                COALESCE(SUM(vp.valor - vp.troco), 0) AS total,
                COUNT(DISTINCT vp.venda_id)            AS qtd
            FROM venda_pagamentos vp
            INNER JOIN vendas v ON v.id = vp.venda_id
            WHERE v.status = 'finalizada'
              AND DATE(v.criado_em) BETWEEN :de AND :ate
            GROUP BY vp.forma
            ORDER BY total DESC
        ", ['de' => $de, 'ate' => $ate]);

        // ── Cálculos ──────────────────────────────────────────────────
        $receitaBruta       = (float)($vendas['receita_bruta']   ?? 0);
        $totalDescontos     = (float)($vendas['total_descontos'] ?? 0);
        $totalCancelamentos = (float)($cancelamentos['total']    ?? 0);

        // FIX 8 (DRE): receita_liquida = bruta - descontos (já embutidos no total)
        // - cancelamentos. Descontos NÃO devem ser subtraídos novamente porque
        // o campo `total` da tabela vendas já é líquido de desconto.
        // receita_bruta aqui é SUM(total), ou seja, já descontado.
        // Então: receita_liquida = receita_bruta - cancelamentos.
        // total_descontos é apenas informativo no DRE.
        $receitaLiquida     = $receitaBruta - $totalCancelamentos;

        $totalOutrasReceitas = array_sum(array_column($outrasReceitas, 'total'));
        $receitaTotal        = $receitaLiquida + $totalOutrasReceitas;

        $totalCmv    = (float)($cmv['total'] ?? 0);
        $lucroBruto  = $receitaTotal - $totalCmv;
        $margemBruta = $receitaTotal > 0 ? ($lucroBruto / $receitaTotal * 100) : 0;

        $totalDespesas    = array_sum(array_column($despesas, 'total'));
        $resultadoLiquido = $lucroBruto - $totalDespesas;
        $margemLiquida    = $receitaTotal > 0 ? ($resultadoLiquido / $receitaTotal * 100) : 0;

        $ticketMedio = (int)($vendas['qtd_vendas'] ?? 0) > 0
            ? $receitaBruta / $vendas['qtd_vendas']
            : 0;

        $evolucao = $this->db->fetchAll("
            SELECT
                DATE_FORMAT(v.criado_em, '%Y-%m') AS mes,
                DATE_FORMAT(v.criado_em, '%m/%Y') AS mes_label,
                COALESCE(SUM(v.total), 0)         AS receita,
                COALESCE(SUM(vi2.custo), 0)       AS custo
            FROM vendas v
            LEFT JOIN (
                SELECT venda_id, SUM(quantidade * preco_custo) AS custo
                FROM venda_itens GROUP BY venda_id
            ) vi2 ON vi2.venda_id = v.id
            WHERE v.status = 'finalizada'
              AND v.criado_em >= DATE_SUB(:ate_ev, INTERVAL 11 MONTH)
              AND DATE(v.criado_em) <= :ate_ev2
            GROUP BY mes, mes_label
            ORDER BY mes ASC
        ", ['ate_ev' => $ate, 'ate_ev2' => $ate]);

        return [
            'receita_bruta'         => $receitaBruta,
            'total_descontos'       => $totalDescontos,
            'total_cancelamentos'   => $totalCancelamentos,
            'receita_liquida'       => $receitaLiquida,
            'outras_receitas'       => $outrasReceitas,
            'total_outras_receitas' => $totalOutrasReceitas,
            'receita_total'         => $receitaTotal,
            'cmv'                   => $totalCmv,
            'lucro_bruto'           => $lucroBruto,
            'margem_bruta'          => $margemBruta,
            'despesas'              => $despesas,
            'total_despesas'        => $totalDespesas,
            'resultado_liquido'     => $resultadoLiquido,
            'margem_liquida'        => $margemLiquida,
            'qtd_vendas'            => (int)($vendas['qtd_vendas'] ?? 0),
            'ticket_medio'          => $ticketMedio,
            'top_produtos'          => $topProdutos,
            'por_forma'             => $porForma,
            'evolucao'              => $evolucao,
        ];
    }

    // ================================================================
    // GERAÇÃO AUTOMÁTICA DE CONTA A PAGAR (a partir de compra)
    // ================================================================

    public function gerarContaPagarDeCompra(array $compra, int $usuarioId): void
    {
        if (empty($compra['vencimento'])) return;

        $jaExiste = $this->db->fetchOne(
            "SELECT id FROM contas_pagar WHERE compra_id = :id",
            ['id' => $compra['id']]
        );
        if ($jaExiste) return;

        $cat = $this->db->fetchOne(
            "SELECT id FROM categorias_financeiras WHERE nome = 'Fornecedores' AND tipo = 'despesa' LIMIT 1"
        );

        $this->criarPagar([
            'categoria_id'    => $cat['id'] ?? null,
            'fornecedor_id'   => $compra['fornecedor_id'],
            'usuario_id'      => $usuarioId,
            'compra_id'       => $compra['id'],
            'descricao'       => 'Compra ' . $compra['numero'] . ($compra['numero_nf'] ? ' — NF ' . $compra['numero_nf'] : ''),
            'valor'           => $compra['total'],
            'vencimento'      => $compra['vencimento'],
            'documento'       => $compra['numero_nf'],
            'forma_pagamento' => $compra['forma_pagamento'],
            'observacao'      => 'Gerado automaticamente ao confirmar a compra.',
            'status'          => 'aberto',
        ]);
    }

    public function listarCategorias(string $tipo = ''): array
    {
        $where  = $tipo ? "WHERE tipo = :tipo AND ativo = 1" : "WHERE ativo = 1";
        $params = $tipo ? ['tipo' => $tipo] : [];
        return $this->db->fetchAll("SELECT * FROM categorias_financeiras {$where} ORDER BY nome", $params);
    }
}
