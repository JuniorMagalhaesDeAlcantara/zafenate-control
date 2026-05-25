<?php

namespace App\Models;

use App\Core\Database;

class MovimentacaoEstoque
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ----------------------------------------------------------------
    // REGISTRO — coração do controle de estoque
    // ----------------------------------------------------------------

    /**
     * Registra uma movimentação e atualiza o estoque do produto atomicamente.
     *
     * @param array $dados {
     *   produto_id, tipo (ENTRADA|SAIDA|AJUSTE), motivo,
     *   quantidade, fornecedor_id?, preco_custo_unitario?,
     *   numero_nf?, observacao?, usuario_id?
     * }
     */
    public function registrar(array $dados): int
    {
        $this->validar($dados);

        return $this->db->transaction(function () use ($dados) {

            // 1. Busca estoque atual (com lock para evitar race condition)
            $produto = $this->db->fetchOne(
                "SELECT id, estoque_atual, preco_custo FROM produtos WHERE id = :id FOR UPDATE",
                ['id' => $dados['produto_id']]
            );

            if (!$produto) {
                throw new \RuntimeException("Produto não encontrado.");
            }

            $estoqueAntes = (float) $produto['estoque_atual'];
            $qtd          = (float) $dados['quantidade'];

            // 2. Calcula novo estoque
            $estoqueDepois = match ($dados['tipo']) {
                'ENTRADA' => $estoqueAntes + $qtd,
                'SAIDA'   => $estoqueAntes - $qtd,
                'AJUSTE'  => $qtd, // ajuste = valor absoluto
            };

            if ($dados['tipo'] === 'SAIDA' && $estoqueDepois < 0) {
                throw new \RuntimeException(
                    "Estoque insuficiente. Disponível: {$estoqueAntes}, solicitado: {$qtd}."
                );
            }

            // 3. Insere movimentação
            $sql = "
                INSERT INTO movimentacoes_estoque
                    (produto_id, fornecedor_id, tipo, motivo, quantidade,
                     estoque_antes, estoque_depois, preco_custo_unitario,
                     numero_nf, observacao, usuario_id)
                VALUES
                    (:produto_id, :fornecedor_id, :tipo, :motivo, :quantidade,
                     :estoque_antes, :estoque_depois, :preco_custo_unitario,
                     :numero_nf, :observacao, :usuario_id)
            ";

            $this->db->execute($sql, [
                'produto_id'            => $dados['produto_id'],
                'fornecedor_id'         => $dados['fornecedor_id'] ?? null,
                'tipo'                  => $dados['tipo'],
                'motivo'                => $dados['motivo'],
                'quantidade'            => $qtd,
                'estoque_antes'         => $estoqueAntes,
                'estoque_depois'        => $estoqueDepois,
                'preco_custo_unitario'  => $dados['preco_custo_unitario'] ?? null,
                'numero_nf'             => $dados['numero_nf'] ?? null,
                'observacao'            => $dados['observacao'] ?? null,
                'usuario_id'            => $dados['usuario_id'] ?? null,
            ]);

            $movId = (int) $this->db->lastInsertId();

            // 4. Atualiza estoque do produto
            if ($dados['tipo'] === 'ENTRADA' && !empty($dados['preco_custo_unitario'])) {
                // Entrada de compra: atualiza custo também
                $this->db->execute(
                    "UPDATE produtos SET estoque_atual = :est, preco_custo = :custo WHERE id = :id",
                    ['est' => $estoqueDepois, 'custo' => $dados['preco_custo_unitario'], 'id' => $dados['produto_id']]
                );
            } else {
                $this->db->execute(
                    "UPDATE produtos SET estoque_atual = :est WHERE id = :id",
                    ['est' => $estoqueDepois, 'id' => $dados['produto_id']]
                );
            }

            return $movId;
        });
    }

    // ----------------------------------------------------------------
    // LEITURA
    // ----------------------------------------------------------------

    /**
     * Histórico de movimentações de um produto.
     */
    public function historicoPorProduto(int $produtoId, int $limite = 50): array
    {
        $sql = "
            SELECT
                m.*,
                f.razao_social AS fornecedor_nome
            FROM movimentacoes_estoque m
            LEFT JOIN fornecedores f ON f.id = m.fornecedor_id
            WHERE m.produto_id = :produto_id
            ORDER BY m.criado_em DESC
            LIMIT :limite
        ";

        return $this->db->fetchAll($sql, ['produto_id' => $produtoId, 'limite' => $limite]);
    }

    /**
     * Movimentações recentes (dashboard).
     */
    public function recentes(int $limite = 20): array
    {
        $sql = "
            SELECT
                m.*,
                p.nome         AS produto_nome,
                p.codigo       AS produto_codigo,
                u.sigla        AS unidade_sigla,
                f.razao_social AS fornecedor_nome
            FROM movimentacoes_estoque m
            INNER JOIN produtos p    ON p.id = m.produto_id
            LEFT JOIN  unidades u    ON u.id = p.unidade_id
            LEFT JOIN  fornecedores f ON f.id = m.fornecedor_id
            ORDER BY m.criado_em DESC
            LIMIT :limite
        ";

        return $this->db->fetchAll($sql, ['limite' => $limite]);
    }

    /**
     * Entradas por fornecedor em um período.
     */
    public function entradasPorFornecedor(int $fornecedorId, string $de, string $ate): array
    {
        $sql = "
            SELECT
                m.*,
                p.nome   AS produto_nome,
                p.codigo AS produto_codigo,
                u.sigla  AS unidade_sigla
            FROM movimentacoes_estoque m
            INNER JOIN produtos p ON p.id = m.produto_id
            LEFT JOIN  unidades u ON u.id = p.unidade_id
            WHERE m.fornecedor_id = :fornecedor_id
              AND m.tipo = 'ENTRADA'
              AND DATE(m.criado_em) BETWEEN :de AND :ate
            ORDER BY m.criado_em DESC
        ";

        return $this->db->fetchAll($sql, [
            'fornecedor_id' => $fornecedorId,
            'de'            => $de,
            'ate'           => $ate,
        ]);
    }

    // ----------------------------------------------------------------
    // HELPERS PRIVADOS
    // ----------------------------------------------------------------

    private function validar(array $dados): void
    {
        if (empty($dados['produto_id'])) {
            throw new \InvalidArgumentException('produto_id é obrigatório.');
        }
        if (empty($dados['tipo']) || !in_array($dados['tipo'], ['ENTRADA', 'SAIDA', 'AJUSTE'])) {
            throw new \InvalidArgumentException('Tipo inválido. Use ENTRADA, SAIDA ou AJUSTE.');
        }
        if (empty($dados['motivo'])) {
            throw new \InvalidArgumentException('Motivo é obrigatório.');
        }
        if (!isset($dados['quantidade']) || (float)$dados['quantidade'] <= 0) {
            throw new \InvalidArgumentException('Quantidade deve ser maior que zero.');
        }
    }
}