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
            $where[]          = 'cp.status = :status';
            $params['status'] = $filtros['status'];
        }

        if (!empty($filtros['busca'])) {
            $where[]         = '(cp.descricao LIKE :busca OR f.razao_social LIKE :busca OR cp.documento LIKE :busca)';
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }

        if (!empty($filtros['de'])) {
            $where[]     = 'cp.vencimento >= :de';
            $params['de'] = $filtros['de'];
        }

        if (!empty($filtros['ate'])) {
            $where[]      = 'cp.vencimento <= :ate';
            $params['ate'] = $filtros['ate'];
        }

        if (!empty($filtros['categoria_id'])) {
            $where[]               = 'cp.categoria_id = :categoria_id';
            $params['categoria_id'] = (int)$filtros['categoria_id'];
        }

        // Marca vencidas automaticamente na query (sem UPDATE, só leitura)
        $sql = "
            SELECT
                cp.*,
                CASE
                    WHEN cp.status IN ('pago','cancelado') THEN cp.status
                    WHEN cp.vencimento < CURDATE()         THEN 'vencida'
                    ELSE 'pendente'
                END AS status_real,
                f.razao_social  AS fornecedor_nome,
                cf.nome         AS categoria_nome,
                cf.cor          AS categoria_cor
            FROM contas_pagar cp
            LEFT JOIN fornecedores f          ON f.id  = cp.fornecedor_id
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
                    THEN 'vencido' ELSE cp.status
                END AS status_real,
                f.razao_social AS fornecedor_nome,
                cf.nome        AS categoria_nome
            FROM contas_pagar cp
            LEFT JOIN fornecedores f            ON f.id  = cp.fornecedor_id
            LEFT JOIN categorias_financeiras cf  ON cf.id = cp.categoria_id
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
            'status'
        ];
        $dados = array_intersect_key($dados, array_flip($campos));
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
            'forma_pagamento'
        ];
        $dados = array_intersect_key($dados, array_flip($campos));
        $sets  = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($dados)));
        $dados['id'] = $id;
        return $this->db->execute("UPDATE contas_pagar SET {$sets} WHERE id = :id", $dados);
    }

    /**
     * Baixa (paga) uma conta a pagar — total ou parcial.
     */
    public function baixarPagar(int $id, float $valorPago, string $formaPagamento, string $dataPagamento): void
    {
        $conta = $this->buscarPagarPorId($id);
        if (!$conta) throw new \RuntimeException('Conta não encontrada.');
        if ($conta['status'] === 'pago') throw new \RuntimeException('Conta já está paga.');
        if ($conta['status'] === 'cancelado') throw new \RuntimeException('Conta cancelada.');

        $novoPago  = (float)$conta['valor_pago'] + $valorPago;
        $novoStatus = $novoPago >= (float)$conta['valor'] ? 'pago' : 'parcial';

        $this->db->execute(
            "UPDATE contas_pagar SET
                valor_pago = :vp, status = :st,
                data_pagamento = :dp, forma_pagamento = :fp
             WHERE id = :id",
            [
                'vp' => round($novoPago, 2),
                'st' => $novoStatus,
                'dp' => $novoStatus === 'pago' ? $dataPagamento : $conta['data_pagamento'],
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
        return $this->db->fetchOne("
        SELECT
            -- A vencer (não vencido, não pago, não cancelado)
            COALESCE(SUM(CASE WHEN status NOT IN ('pago','cancelado')
                              AND vencimento >= CURDATE()
                         THEN valor - valor_pago ELSE 0 END), 0)  AS a_vencer,
            SUM(status NOT IN ('pago','cancelado')
                AND vencimento >= CURDATE())                        AS qtd_a_vencer,

            -- Vencidas
            COALESCE(SUM(CASE WHEN status NOT IN ('pago','cancelado')
                              AND vencimento < CURDATE()
                         THEN valor - valor_pago ELSE 0 END), 0)  AS vencidas,
            SUM(status NOT IN ('pago','cancelado')
                AND vencimento < CURDATE())                         AS qtd_vencidas,

            -- Pagas no mês atual
            COALESCE(SUM(CASE WHEN status = 'pago'
                              AND MONTH(data_pagamento) = MONTH(CURDATE())
                              AND YEAR(data_pagamento)  = YEAR(CURDATE())
                         THEN valor_pago ELSE 0 END), 0)           AS pagas_mes,
            SUM(status = 'pago'
                AND MONTH(data_pagamento) = MONTH(CURDATE())
                AND YEAR(data_pagamento)  = YEAR(CURDATE()))        AS qtd_pagas_mes
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
            $where[]          = 'cr.status = :status';
            $params['status'] = $filtros['status'];
        }

        if (!empty($filtros['busca'])) {
            $where[]         = '(cr.descricao LIKE :busca OR c.nome LIKE :busca OR cr.documento LIKE :busca)';
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }

        if (!empty($filtros['de'])) {
            $where[]     = 'cr.vencimento >= :de';
            $params['de'] = $filtros['de'];
        }

        if (!empty($filtros['ate'])) {
            $where[]      = 'cr.vencimento <= :ate';
            $params['ate'] = $filtros['ate'];
        }

        $sql = "
            SELECT
                cr.*,
                CASE
                    WHEN cr.status NOT IN ('recebido','cancelado') AND cr.vencimento < CURDATE()
                    THEN 'vencido' ELSE cr.status
                END AS status_real,
                c.nome          AS cliente_nome,
                cf.nome         AS categoria_nome,
                cf.cor          AS categoria_cor
            FROM contas_receber cr
            LEFT JOIN clientes c                ON c.id  = cr.cliente_id
            LEFT JOIN categorias_financeiras cf  ON cf.id = cr.categoria_id
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
                    THEN 'vencido' ELSE cr.status
                END AS status_real,
                c.nome  AS cliente_nome,
                cf.nome AS categoria_nome
            FROM contas_receber cr
            LEFT JOIN clientes c                ON c.id  = cr.cliente_id
            LEFT JOIN categorias_financeiras cf  ON cf.id = cr.categoria_id
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
            'status'
        ];
        $dados = array_intersect_key($dados, array_flip($campos));
        $dados['status'] = $dados['status'] ?? 'aberto';

        $keys  = implode(', ', array_keys($dados));
        $holds = ':' . implode(', :', array_keys($dados));
        $this->db->execute("INSERT INTO contas_receber ({$keys}) VALUES ({$holds})", $dados);
        return (int)$this->db->lastInsertId();
    }

    public function baixarReceber(int $id, float $valorRecebido, string $formaRecebimento, string $dataRecebimento): void
    {
        $conta = $this->buscarReceberPorId($id);
        if (!$conta) throw new \RuntimeException('Conta não encontrada.');
        if ($conta['status'] === 'recebido') throw new \RuntimeException('Conta já recebida.');
        if ($conta['status'] === 'cancelado') throw new \RuntimeException('Conta cancelada.');

        $novoRecebido = (float)$conta['valor_recebido'] + $valorRecebido;
        $novoStatus   = $novoRecebido >= (float)$conta['valor'] ? 'recebido' : 'parcial';

        $this->db->execute(
            "UPDATE contas_receber SET
                valor_recebido = :vr, status = :st,
                data_recebimento = :dr, forma_recebimento = :fr
             WHERE id = :id",
            [
                'vr' => round($novoRecebido, 2),
                'st' => $novoStatus,
                'dr' => $novoStatus === 'recebido' ? $dataRecebimento : $conta['data_recebimento'],
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

            // Se veio de uma venda, cancela a venda e estorna estoque
            if (!empty($conta['venda_id'])) {
                $venda = $this->db->fetchOne(
                    "SELECT * FROM vendas WHERE id = :id",
                    ['id' => $conta['venda_id']]
                );

                if ($venda && $venda['status'] === 'finalizada') {
                    // Estorna estoque dos itens
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
                                'pid'   => $item['produto_id'],
                                'uid'   => $usuarioId,
                                'qty'   => $item['quantidade'],
                                'antes' => $antes,
                                'depois' => $depois,
                                'obs'   => 'Estorno por cancelamento da venda #' . $venda['numero'],
                            ]
                        );
                    }

                    // Cancela a venda
                    $this->db->execute(
                        "UPDATE vendas SET status = 'cancelada', cancelado_por = :uid,
                     motivo_cancelamento = :motivo, cancelado_em = NOW()
                     WHERE id = :id",
                        ['uid' => $usuarioId, 'motivo' => $motivo, 'id' => $conta['venda_id']]
                    );

                    // Estorna do saldo do caixa (só dinheiro efetivo)
                    $pgtos = $this->db->fetchAll(
                        "SELECT * FROM venda_pagamentos WHERE venda_id = :id",
                        ['id' => $conta['venda_id']]
                    );
                    $dinheiro = 0;
                    foreach ($pgtos as $p) {
                        if ($p['forma'] === 'dinheiro') {
                            $dinheiro += $p['valor'] - ($p['troco'] ?? 0);
                        }
                    }
                    if ($dinheiro > 0) {
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
                    }

                    // Cancela todas as parcelas da mesma venda
                    $this->db->execute(
                        "UPDATE contas_receber SET status = 'cancelado', motivo_cancelamento = :motivo
                     WHERE venda_id = :venda_id AND status != 'cancelado'",
                        ['motivo' => $motivo, 'venda_id' => $conta['venda_id']]
                    );
                    return; // já cancelou tudo acima, sai
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
        return $this->db->fetchOne("
        SELECT
            -- A vencer
            COALESCE(SUM(CASE WHEN status NOT IN ('recebido','cancelado')
                              AND vencimento >= CURDATE()
                         THEN valor - valor_recebido ELSE 0 END), 0) AS a_vencer,
            SUM(status NOT IN ('recebido','cancelado')
                AND vencimento >= CURDATE())                           AS qtd_a_vencer,

            -- Vencidas
            COALESCE(SUM(CASE WHEN status NOT IN ('recebido','cancelado')
                              AND vencimento < CURDATE()
                         THEN valor - valor_recebido ELSE 0 END), 0)  AS vencidas,
            SUM(status NOT IN ('recebido','cancelado')
                AND vencimento < CURDATE())                            AS qtd_vencidas,

            -- Recebidas no mês
            COALESCE(SUM(CASE WHEN status = 'recebido'
                              AND MONTH(data_recebimento) = MONTH(CURDATE())
                              AND YEAR(data_recebimento)  = YEAR(CURDATE())
                         THEN valor_recebido ELSE 0 END), 0)           AS recebidas_mes,
            SUM(status = 'recebido'
                AND MONTH(data_recebimento) = MONTH(CURDATE())
                AND YEAR(data_recebimento)  = YEAR(CURDATE()))          AS qtd_recebidas_mes
        FROM contas_receber
    ") ?? [];
    }

    // ================================================================
    // FLUXO DE CAIXA
    // ================================================================

    public function fluxoCaixa(string $de, string $ate, array $filtros = []): array
    {
        $whereE = ['cr.status = \'recebido\'', 'cr.data_recebimento BETWEEN :de AND :ate'];
        $whereS = ['cp.status = \'pago\'',     'cp.data_pagamento   BETWEEN :de AND :ate'];
        $params = ['de' => $de, 'ate' => $ate];

        if (!empty($filtros['categoria_id'])) {
            $whereE[] = 'cr.categoria_id = :cat';
            $whereS[] = 'cp.categoria_id = :cat';
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

        // Vendas PDV à vista (sem conta a receber vinculada)
        // Não aplica filtro de categoria pois vendas PDV não têm categoria financeira
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
              SELECT 1 FROM contas_receber cr
              WHERE cr.venda_id = v.id
          )
        ORDER BY v.criado_em ASC
    ", ['de' => $de, 'ate' => $ate]);

        // Mescla entradas manuais + vendas PDV
        $entradas = array_merge($entradas, $vendasPdv);

        // Mescla tudo e ordena por data para calcular saldo acumulado linha a linha
        $movimentacoes = array_merge($entradas, $saidas);
        usort(
            $movimentacoes,
            fn($a, $b) =>
            strcmp($a['data'], $b['data']) ?: ($a['tipo'] <=> $b['tipo'])
        );

        // Saldo anterior ao período (inclui PDV)
        $saldoAnterior = $this->saldoAntesde($de, $filtros);

        // Saldo acumulado por linha
        $acumulado = $saldoAnterior;
        foreach ($movimentacoes as &$m) {
            $acumulado += $m['tipo'] === 'receita' ? (float)$m['valor'] : -(float)$m['valor'];
            $m['saldo_acumulado'] = $acumulado;
        }
        unset($m);

        $totalEntradas = array_sum(array_column($entradas, 'valor'));
        $totalSaidas   = array_sum(array_column($saidas,   'valor'));

        // Agrupamento diário para o gráfico
        $graficoDias = $this->graficoPorDia($de, $ate, $entradas, $saidas, $saldoAnterior);

        // Projeção: próximos 30 dias
        $projecao = $this->projecao30Dias();

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

    private function saldoAntesde(string $de, array $filtros = []): float
    {
        $cat = !empty($filtros['categoria_id'])
            ? ' AND categoria_id = ' . (int)$filtros['categoria_id']
            : '';

        // Contas recebidas antes do período
        $rec = (float)($this->db->fetchOne("
        SELECT COALESCE(SUM(valor_recebido), 0) AS total
        FROM contas_receber
        WHERE status = 'recebido'
          AND data_recebimento < :de
          {$cat}
    ", ['de' => $de])['total'] ?? 0);

        // Vendas PDV à vista antes do período (sem filtro de categoria)
        $pdv = (float)($this->db->fetchOne("
        SELECT COALESCE(SUM(v.total), 0) AS total
        FROM vendas v
        WHERE v.status = 'finalizada'
          AND DATE(v.criado_em) < :de
          AND NOT EXISTS (
              SELECT 1 FROM contas_receber cr
              WHERE cr.venda_id = v.id
          )
    ", ['de' => $de])['total'] ?? 0);

        // Contas pagas antes do período
        $pag = (float)($this->db->fetchOne("
        SELECT COALESCE(SUM(valor_pago), 0) AS total
        FROM contas_pagar
        WHERE status = 'pago'
          AND data_pagamento < :de
          {$cat}
    ", ['de' => $de])['total'] ?? 0);

        return ($rec + $pdv) - $pag;
    }

    private function graficoPorDia(string $de, string $ate, array $entradas, array $saidas, float $saldoInicial): array
    {
        $idxE = [];
        foreach ($entradas as $e) {
            $idxE[$e['data']] = ($idxE[$e['data']] ?? 0) + $e['valor'];
        }
        $idxS = [];
        foreach ($saidas as $s) {
            $idxS[$s['data']] = ($idxS[$s['data']] ?? 0) + $s['valor'];
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
                'label'     => $cur->format('d/m'),
                'entradas'  => $ent,
                'saidas'    => $sai,
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
               COALESCE(f.razao_social,'Manual') AS origem
        FROM contas_pagar cp
        LEFT JOIN fornecedores f ON f.id = cp.fornecedor_id
        WHERE cp.status NOT IN ('pago','cancelado')
          AND cp.vencimento BETWEEN CURDATE() AND :ate
        ORDER BY cp.vencimento ASC
    ", ['ate' => $ate]);

        $receber = $this->db->fetchAll("
        SELECT vencimento AS data, descricao,
               (valor - valor_recebido) AS valor, 'receita' AS tipo,
               COALESCE(c.nome,'Manual') AS origem
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
    // HELPERS
    // ================================================================

    public function listarCategorias(string $tipo = ''): array
    {
        $where  = $tipo ? "WHERE tipo = :tipo AND ativo = 1" : "WHERE ativo = 1";
        $params = $tipo ? ['tipo' => $tipo] : [];
        return $this->db->fetchAll("SELECT * FROM categorias_financeiras {$where} ORDER BY nome", $params);
    }

    /**
     * Chamado pelo CompraController ao confirmar compra.
     * Cria automaticamente uma conta a pagar se houver vencimento definido.
     */
    public function gerarContaPagarDeCompra(array $compra, int $usuarioId): void
    {
        // Só gera se tiver vencimento definido (compra a prazo)
        if (empty($compra['vencimento'])) return;

        // Evita duplicidade
        $jaExiste = $this->db->fetchOne(
            "SELECT id FROM contas_pagar WHERE compra_id = :id",
            ['id' => $compra['id']]
        );
        if ($jaExiste) return;

        // Busca categoria "Fornecedores"
        $cat = $this->db->fetchOne(
            "SELECT id FROM categorias_financeiras WHERE nome = 'Fornecedores' AND tipo = 'despesa' LIMIT 1"
        );

        $this->criarPagar([
            'categoria_id'   => $cat['id'] ?? null,
            'fornecedor_id'  => $compra['fornecedor_id'],
            'usuario_id'     => $usuarioId,
            'compra_id'      => $compra['id'],
            'descricao'      => 'Compra ' . $compra['numero'] . ($compra['numero_nf'] ? ' — NF ' . $compra['numero_nf'] : ''),
            'valor'          => $compra['total'],
            'vencimento'     => $compra['vencimento'],
            'documento'      => $compra['numero_nf'],
            'forma_pagamento' => $compra['forma_pagamento'],
            'observacao'     => 'Gerado automaticamente ao confirmar a compra.',
            'status'         => 'aberto',
        ]);
    }

    // Adicionar após totaisReceber()

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

        // Indexa por dia
        $idxE = array_column($entradas, 'total', 'dia');
        $idxS = array_column($saidas,   'total', 'dia');

        $result = [];
        for ($i = 29; $i >= 0; $i--) {
            $dia = date('Y-m-d', strtotime("-{$i} days"));
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
        // ── 1. RECEITA BRUTA — vendas finalizadas no período ──────────
        $vendas = $this->db->fetchOne("
        SELECT
            COUNT(*)        AS qtd_vendas,
            COALESCE(SUM(total), 0)    AS receita_bruta,
            COALESCE(SUM(desconto_valor), 0) AS total_descontos
        FROM vendas
        WHERE status = 'finalizada'
          AND DATE(criado_em) BETWEEN :de AND :ate
    ", ['de' => $de, 'ate' => $ate]);

        // ── 2. CANCELAMENTOS no período ───────────────────────────────
        $cancelamentos = $this->db->fetchOne("
        SELECT COALESCE(SUM(total), 0) AS total
        FROM vendas
        WHERE status = 'cancelada'
          AND DATE(cancelado_em) BETWEEN :de AND :ate
    ", ['de' => $de, 'ate' => $ate]);

        // ── 3. CMV — Custo da Mercadoria Vendida ──────────────────────
        $cmv = $this->db->fetchOne("
        SELECT COALESCE(SUM(vi.quantidade * vi.preco_custo), 0) AS total
        FROM venda_itens vi
        INNER JOIN vendas v ON v.id = vi.venda_id
        WHERE v.status = 'finalizada'
          AND DATE(v.criado_em) BETWEEN :de AND :ate
    ", ['de' => $de, 'ate' => $ate]);

        // ── 4. OUTRAS RECEITAS — contas a receber manuais (sem venda) ─
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

        // ── 5. DESPESAS por categoria ─────────────────────────────────
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

        // ── 6. Ticket médio e produto mais vendido ────────────────────
        $ticketMedio = $vendas['qtd_vendas'] > 0
            ? $vendas['receita_bruta'] / $vendas['qtd_vendas']
            : 0;

        $topProdutos = $this->db->fetchAll("
        SELECT
            vi.produto_nome                         AS nome,
            SUM(vi.quantidade)                      AS qty,
            SUM(vi.subtotal)                        AS receita,
            SUM(vi.quantidade * vi.preco_custo)     AS custo,
            SUM(vi.subtotal) - SUM(vi.quantidade * vi.preco_custo) AS lucro
        FROM venda_itens vi
        INNER JOIN vendas v ON v.id = vi.venda_id
        WHERE v.status = 'finalizada'
          AND DATE(v.criado_em) BETWEEN :de AND :ate
        GROUP BY vi.produto_nome
        ORDER BY receita DESC
        LIMIT 8
    ", ['de' => $de, 'ate' => $ate]);

        // ── 7. Receita por forma de pagamento ─────────────────────────
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

        // ── 8. Cálculos finais ────────────────────────────────────────
        $receitaBruta      = (float)($vendas['receita_bruta']    ?? 0);
        $totalDescontos    = (float)($vendas['total_descontos']  ?? 0);
        $totalCancelamentos = (float)($cancelamentos['total']    ?? 0);
        $receitaLiquida    = $receitaBruta - $totalCancelamentos;

        $totalOutrasReceitas = array_sum(array_column($outrasReceitas, 'total'));
        $receitaTotal        = $receitaLiquida + $totalOutrasReceitas;

        $totalCmv     = (float)($cmv['total'] ?? 0);
        $lucroBruto   = $receitaTotal - $totalCmv;
        $margemBruta  = $receitaTotal > 0 ? ($lucroBruto / $receitaTotal * 100) : 0;

        $totalDespesas    = array_sum(array_column($despesas, 'total'));
        $resultadoLiquido = $lucroBruto - $totalDespesas;
        $margemLiquida    = $receitaTotal > 0 ? ($resultadoLiquido / $receitaTotal * 100) : 0;

        // Evolução mensal (últimos 12 meses para gráfico)
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
            // Receitas
            'receita_bruta'        => $receitaBruta,
            'total_descontos'      => $totalDescontos,
            'total_cancelamentos'  => $totalCancelamentos,
            'receita_liquida'      => $receitaLiquida,
            'outras_receitas'      => $outrasReceitas,
            'total_outras_receitas' => $totalOutrasReceitas,
            'receita_total'        => $receitaTotal,
            // CMV e lucro bruto
            'cmv'                  => $totalCmv,
            'lucro_bruto'          => $lucroBruto,
            'margem_bruta'         => $margemBruta,
            // Despesas
            'despesas'             => $despesas,
            'total_despesas'       => $totalDespesas,
            // Resultado
            'resultado_liquido'    => $resultadoLiquido,
            'margem_liquida'       => $margemLiquida,
            // Indicadores
            'qtd_vendas'           => (int)($vendas['qtd_vendas'] ?? 0),
            'ticket_medio'         => $ticketMedio,
            'top_produtos'         => $topProdutos,
            'por_forma'            => $porForma,
            // Gráfico
            'evolucao'             => $evolucao,
        ];
    }
}
