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

    public function fluxoCaixa(string $de, string $ate): array
    {
        // Entradas: contas recebidas no período
        $entradas = $this->db->fetchAll("
            SELECT
                cr.data_recebimento AS data,
                cr.descricao,
                cr.valor_recebido   AS valor,
                cf.nome             AS categoria,
                cf.cor,
                'receita'           AS tipo,
                c.nome              AS origem
            FROM contas_receber cr
            LEFT JOIN categorias_financeiras cf ON cf.id = cr.categoria_id
            LEFT JOIN clientes c                ON c.id  = cr.cliente_id
            WHERE cr.status = 'recebido'
              AND cr.data_recebimento BETWEEN :de AND :ate
            ORDER BY cr.data_recebimento ASC
        ", ['de' => $de, 'ate' => $ate]);

        // Saídas: contas pagas no período
        $saidas = $this->db->fetchAll("
            SELECT
                cp.data_pagamento  AS data,
                cp.descricao,
                cp.valor_pago      AS valor,
                cf.nome            AS categoria,
                cf.cor,
                'despesa'          AS tipo,
                COALESCE(f.razao_social, 'Manual') AS origem
            FROM contas_pagar cp
            LEFT JOIN categorias_financeiras cf ON cf.id = cp.categoria_id
            LEFT JOIN fornecedores f             ON f.id  = cp.fornecedor_id
            WHERE cp.status = 'pago'
              AND cp.data_pagamento BETWEEN :de AND :ate
            ORDER BY cp.data_pagamento ASC
        ", ['de' => $de, 'ate' => $ate]);

        $totalEntradas = array_sum(array_column($entradas, 'valor'));
        $totalSaidas   = array_sum(array_column($saidas,   'valor'));

        return [
            'entradas'        => $entradas,
            'saidas'          => $saidas,
            'total_entradas'  => $totalEntradas,
            'total_saidas'    => $totalSaidas,
            'saldo'           => $totalEntradas - $totalSaidas,
        ];
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
}
