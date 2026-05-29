<?php

namespace App\Models;

use App\Core\Database;

class Compra
{
    private Database $db;

    private array $fillable = [
        'fornecedor_id',
        'usuario_id',
        'numero',
        'numero_nf',
        'serie_nf',
        'status',
        'subtotal',
        'desconto_valor',
        'frete',
        'total',
        'forma_pagamento',
        'prazo_pagamento',
        'vencimento',
        'data_emissao',
        'data_entrega',
        'observacao'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ----------------------------------------------------------------
    // LEITURA
    // ----------------------------------------------------------------

    public function listar(array $filtros = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['busca'])) {
            $where[]         = '(c.numero LIKE :busca OR c.numero_nf LIKE :busca OR f.razao_social LIKE :busca)';
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }

        if (!empty($filtros['status'])) {
            $where[]          = 'c.status = :status';
            $params['status'] = $filtros['status'];
        }

        if (!empty($filtros['fornecedor_id'])) {
            $where[]               = 'c.fornecedor_id = :fornecedor_id';
            $params['fornecedor_id'] = (int)$filtros['fornecedor_id'];
        }

        if (!empty($filtros['de'])) {
            $where[]     = 'c.data_emissao >= :de';
            $params['de'] = $filtros['de'];
        }

        if (!empty($filtros['ate'])) {
            $where[]      = 'c.data_emissao <= :ate';
            $params['ate'] = $filtros['ate'];
        }

        $whereStr = implode(' AND ', $where);

        $sql = "
            SELECT
                c.*,
                f.razao_social   AS fornecedor_nome,
                f.nome_fantasia  AS fornecedor_fantasia,
                u.nome           AS usuario_nome,
                (SELECT COUNT(*) FROM compra_itens ci WHERE ci.compra_id = c.id) AS qtd_itens
            FROM compras c
            INNER JOIN fornecedores f ON f.id = c.fornecedor_id
            INNER JOIN usuarios u     ON u.id = c.usuario_id
            WHERE {$whereStr}
            ORDER BY c.criado_em DESC
        ";

        return $this->db->fetchAll($sql, $params);
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                c.*,
                f.razao_social   AS fornecedor_nome,
                f.nome_fantasia  AS fornecedor_fantasia,
                f.cnpj_cpf       AS fornecedor_cnpj,
                f.telefone       AS fornecedor_telefone,
                f.email          AS fornecedor_email,
                u.nome           AS usuario_nome
            FROM compras c
            INNER JOIN fornecedores f ON f.id = c.fornecedor_id
            INNER JOIN usuarios u     ON u.id = c.usuario_id
            WHERE c.id = :id
            LIMIT 1
        ";

        return $this->db->fetchOne($sql, ['id' => $id]) ?: null;
    }

    public function buscarItens(int $compraId): array
    {
        $sql = "
            SELECT
                ci.*,
                p.codigo_barras,
                p.estoque_atual,
                p.preco_custo AS preco_custo_atual
            FROM compra_itens ci
            INNER JOIN produtos p ON p.id = ci.produto_id
            WHERE ci.compra_id = :compra_id
            ORDER BY ci.id ASC
        ";

        return $this->db->fetchAll($sql, ['compra_id' => $compraId]);
    }

    public function totais(): array
    {
        $sql = "
            SELECT
                COUNT(*)                          AS total,
                SUM(status = 'rascunho')          AS rascunhos,
                SUM(status = 'confirmada')        AS confirmadas,
                SUM(status = 'cancelada')         AS canceladas,
                SUM(CASE WHEN status = 'confirmada' THEN total ELSE 0 END) AS valor_total
            FROM compras
        ";

        return $this->db->fetchOne($sql) ?? [];
    }

    // ----------------------------------------------------------------
    // ESCRITA
    // ----------------------------------------------------------------

    /**
     * Cria rascunho da compra (sem itens ainda).
     */
    public function criar(array $dados): int
    {
        $dados['numero'] = $this->gerarNumero();
        $dados['status'] = 'rascunho';
        $dados = $this->filtrarCampos($dados);

        $campos    = implode(', ', array_keys($dados));
        $placehold = ':' . implode(', :', array_keys($dados));

        $this->db->execute("INSERT INTO compras ({$campos}) VALUES ({$placehold})", $dados);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Atualiza cabeçalho da compra (apenas rascunhos).
     */
    public function atualizar(int $id, array $dados): bool
    {
        $this->garantirRascunho($id);

        $dados = $this->filtrarCampos($dados);
        unset($dados['numero']); // número nunca muda

        $sets      = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($dados)));
        $dados['id'] = $id;

        return $this->db->execute("UPDATE compras SET {$sets} WHERE id = :id", $dados);
    }

    /**
     * Salva/substitui todos os itens de uma compra em rascunho.
     */
    public function salvarItens(int $compraId, array $itens): void
    {
        $this->garantirRascunho($compraId);

        // Remove itens anteriores e reinserirá
        $this->db->execute("DELETE FROM compra_itens WHERE compra_id = :id", ['id' => $compraId]);

        $subtotal = 0.00;

        foreach ($itens as $item) {
            $itemSubtotal = ((float)$item['quantidade'] * (float)$item['preco_unitario']) - (float)($item['desconto_item'] ?? 0);
            $subtotal += $itemSubtotal;

            // Snapshot do produto
            $prod = $this->db->fetchOne(
                "SELECT nome, codigo, unidade_id FROM produtos WHERE id = :id",
                ['id' => $item['produto_id']]
            );
            $unidade = $this->db->fetchOne(
                "SELECT sigla FROM unidades WHERE id = :id",
                ['id' => $prod['unidade_id'] ?? 1]
            );

            $this->db->execute(
                "INSERT INTO compra_itens
                     (compra_id, produto_id, produto_nome, produto_codigo, unidade_sigla,
                      quantidade, preco_unitario, desconto_item, subtotal)
                 VALUES
                     (:compra_id, :produto_id, :produto_nome, :produto_codigo, :unidade_sigla,
                      :quantidade, :preco_unitario, :desconto_item, :subtotal)",
                [
                    'compra_id'      => $compraId,
                    'produto_id'     => $item['produto_id'],
                    'produto_nome'   => $prod['nome']            ?? $item['produto_nome'] ?? '',
                    'produto_codigo' => $prod['codigo']          ?? '',
                    'unidade_sigla'  => $unidade['sigla']        ?? 'UN',
                    'quantidade'     => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                    'desconto_item'  => $item['desconto_item']   ?? 0.00,
                    'subtotal'       => round($itemSubtotal, 2),
                ]
            );
        }

        // Recalcula totais no cabeçalho
        $compra     = $this->buscarPorId($compraId);
        $frete      = (float)($compra['frete']         ?? 0);
        $desconto   = (float)($compra['desconto_valor'] ?? 0);
        $total      = $subtotal + $frete - $desconto;

        $this->db->execute(
            "UPDATE compras SET subtotal = :sub, total = :total WHERE id = :id",
            ['sub' => round($subtotal, 2), 'total' => round($total, 2), 'id' => $compraId]
        );
    }

    /**
     * Confirma a compra: valida, atualiza estoque e registra movimentações.
     * Tudo dentro de uma transaction — ou tudo passa ou nada muda.
     */
    public function confirmar(int $id, int $usuarioId): void
    {
        $this->garantirRascunho($id);

        $itens = $this->buscarItens($id);
        if (empty($itens)) {
            throw new \RuntimeException('Não é possível confirmar uma compra sem itens.');
        }

        $this->db->transaction(function () use ($id, $itens, $usuarioId) {

            $compra = $this->buscarPorId($id);

            foreach ($itens as $item) {
                // Busca estoque atual com lock
                $prod = $this->db->fetchOne(
                    "SELECT estoque_atual FROM produtos WHERE id = :id FOR UPDATE",
                    ['id' => $item['produto_id']]
                );

                $antes  = (float)$prod['estoque_atual'];
                $depois = $antes + (float)$item['quantidade'];

                // Registra movimentação de estoque
                $this->db->execute(
                    "INSERT INTO movimentacoes_estoque
                         (produto_id, fornecedor_id, usuario_id, tipo, motivo,
                          quantidade, estoque_antes, estoque_depois,
                          preco_custo_unitario, numero_nf, observacao)
                     VALUES
                         (:pid, :fid, :uid, 'ENTRADA', 'COMPRA',
                          :qty, :antes, :depois,
                          :preco, :nf, :obs)",
                    [
                        'pid'   => $item['produto_id'],
                        'fid'   => $compra['fornecedor_id'],
                        'uid'   => $usuarioId,
                        'qty'   => $item['quantidade'],
                        'antes' => $antes,
                        'depois' => $depois,
                        'preco' => $item['preco_unitario'],
                        'nf'    => $compra['numero_nf'],
                        'obs'   => 'Compra ' . $compra['numero'],
                    ]
                );

                // Atualiza estoque e preço de custo do produto
                $this->db->execute(
                    "UPDATE produtos SET estoque_atual = :est, preco_custo = :custo WHERE id = :id",
                    ['est' => $depois, 'custo' => $item['preco_unitario'], 'id' => $item['produto_id']]
                );

                // Marca item como estoque atualizado
                $this->db->execute(
                    "UPDATE compra_itens SET estoque_atualizado = 1 WHERE id = :id",
                    ['id' => $item['id']]
                );
            }

            // Confirma a compra
            $this->db->execute(
                "UPDATE compras SET status = 'confirmada', data_entrega = COALESCE(data_entrega, CURDATE()) WHERE id = :id",
                ['id' => $id]
            );
        });
    }

    /**
     * Cancela uma compra (rascunho ou confirmada).
     * Se confirmada, estorna o estoque automaticamente.
     */
    public function cancelar(int $id, string $motivo, int $usuarioId): void
    {
        $compra = $this->buscarPorId($id);
        if (!$compra) {
            throw new \RuntimeException('Compra não encontrada.');
        }
        if ($compra['status'] === 'cancelada') {
            throw new \RuntimeException('Esta compra já está cancelada.');
        }

        $this->db->transaction(function () use ($id, $compra, $motivo, $usuarioId) {

            // Se já confirmada, estorna o estoque
            if ($compra['status'] === 'confirmada') {
                $itens = $this->buscarItens($id);

                foreach ($itens as $item) {
                    if (!$item['estoque_atualizado']) continue;

                    $prod   = $this->db->fetchOne(
                        "SELECT estoque_atual FROM produtos WHERE id = :id FOR UPDATE",
                        ['id' => $item['produto_id']]
                    );
                    $antes  = (float)$prod['estoque_atual'];
                    $depois = max(0, $antes - (float)$item['quantidade']);

                    $this->db->execute(
                        "INSERT INTO movimentacoes_estoque
                             (produto_id, fornecedor_id, usuario_id, tipo, motivo,
                              quantidade, estoque_antes, estoque_depois, observacao)
                         VALUES
                             (:pid, :fid, :uid, 'SAIDA', 'DEVOLUCAO',
                              :qty, :antes, :depois, :obs)",
                        [
                            'pid'   => $item['produto_id'],
                            'fid'   => $compra['fornecedor_id'],
                            'uid'   => $usuarioId,
                            'qty'   => $item['quantidade'],
                            'antes' => $antes,
                            'depois' => $depois,
                            'obs'   => 'Cancelamento da compra ' . $compra['numero'],
                        ]
                    );

                    $this->db->execute(
                        "UPDATE produtos SET estoque_atual = :est WHERE id = :id",
                        ['est' => $depois, 'id' => $item['produto_id']]
                    );
                }
            }

            $this->db->execute(
                "UPDATE compras SET status = 'cancelada', motivo_cancelamento = :motivo WHERE id = :id",
                ['motivo' => $motivo, 'id' => $id]
            );
        });
    }

    /**
     * Remove todos os itens de uma compra em rascunho.
     * Chamado explicitamente antes de salvarItens() no fluxo de edição.
     */
    public function removerItens(int $compraId): void
    {
        $this->garantirRascunho($compraId);
        $this->db->execute(
            "DELETE FROM compra_itens WHERE compra_id = :id",
            ['id' => $compraId]
        );
    }

    // ----------------------------------------------------------------
    // HELPERS
    // ----------------------------------------------------------------

    private function gerarNumero(): string
    {
        $row = $this->db->fetchOne(
            "SELECT MAX(CAST(SUBSTRING(numero, 5) AS UNSIGNED)) AS ultimo FROM compras WHERE numero REGEXP '^CMP-[0-9]+$'"
        );
        $proximo = ($row['ultimo'] ?? 0) + 1;
        return 'CMP-' . str_pad($proximo, 6, '0', STR_PAD_LEFT);
    }

    private function garantirRascunho(int $id): void
    {
        $row = $this->db->fetchOne("SELECT status FROM compras WHERE id = :id", ['id' => $id]);
        if (!$row) {
            throw new \RuntimeException('Compra não encontrada.');
        }
        if ($row['status'] !== 'rascunho') {
            throw new \RuntimeException('Esta operação só é permitida em compras com status rascunho.');
        }
    }

    private function filtrarCampos(array $dados): array
    {
        return array_intersect_key($dados, array_flip($this->fillable));
    }
}
