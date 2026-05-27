<?php

namespace App\Models;

use App\Core\Database;

class MovimentacaoEstoque
{
    private Database $db;

    // Mapeamento: motivo → tipo padrão (usado na validação cruzada)
    public const MOTIVO_TIPO = [
        'COMPRA'             => 'ENTRADA',
        'DEVOLUCAO'          => 'ENTRADA',   // devolução de cliente = entra
        'CANCELAMENTO_VENDA' => 'ENTRADA',   // cancela venda = devolve estoque
        'VENDA'              => 'SAIDA',
        'PERDA'              => 'SAIDA',
        'AVARIA'             => 'SAIDA',
        'USO_INTERNO'        => 'SAIDA',
        'TRANSFERENCIA'      => 'SAIDA',
        'AJUSTE_MANUAL'      => 'AJUSTE',
        'INVENTARIO'         => 'AJUSTE',
    ];

    // Labels amigáveis para views
    public const TIPO_LABELS = [
        'ENTRADA' => 'Entrada',
        'SAIDA'   => 'Saída',
        'AJUSTE'  => 'Ajuste',
    ];

    public const MOTIVO_LABELS = [
        'COMPRA'             => 'Compra / Reposição',
        'DEVOLUCAO'          => 'Devolução de Cliente',
        'CANCELAMENTO_VENDA' => 'Cancelamento de Venda',
        'VENDA'              => 'Venda (PDV)',
        'PERDA'              => 'Perda / Vencimento',
        'AVARIA'             => 'Avaria',
        'USO_INTERNO'        => 'Uso Interno',
        'TRANSFERENCIA'      => 'Transferência',
        'AJUSTE_MANUAL'      => 'Ajuste Manual',
        'INVENTARIO'         => 'Ajuste de Inventário',
    ];

    // Origens possíveis (campo livre — armazenado em observacao como prefixo)
    public const ORIGEM_LABELS = [
        'PDV'              => 'PDV (Frente de Loja)',
        'MANUAL'           => 'Ajuste Manual',
        'CANCELAMENTO'     => 'Cancelamento de Venda',
        'ENTRADA_MERCADORIA' => 'Entrada de Mercadoria',
        'SISTEMA'          => 'Sistema',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ----------------------------------------------------------------
    // REGISTRO
    // ----------------------------------------------------------------

    /**
     * Registra uma movimentação e atualiza o estoque atomicamente.
     *
     * @param array $dados {
     *   produto_id, tipo, motivo, quantidade,
     *   fornecedor_id?, preco_custo_unitario?,
     *   numero_nf?, observacao?, usuario_id?, origem?
     * }
     */
    public function registrar(array $dados): int
    {
        $this->validar($dados);

        return $this->db->transaction(function () use ($dados) {

            // 1. Lock no produto
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
                'AJUSTE'  => $qtd,                  // valor absoluto
            };

            if ($dados['tipo'] === 'SAIDA' && $estoqueDepois < 0) {
                throw new \RuntimeException(
                    "Estoque insuficiente. Disponível: {$estoqueAntes}, solicitado: {$qtd}."
                );
            }

            // 3. Observação com prefixo de origem
            $origem     = $dados['origem'] ?? 'MANUAL';
            $obsBase    = $dados['observacao'] ?? null;
            $observacao = $obsBase;

            // 4. Insere movimentação
            $this->db->execute("
                INSERT INTO movimentacoes_estoque
                    (produto_id, fornecedor_id, tipo, motivo, quantidade,
                     estoque_antes, estoque_depois, preco_custo_unitario,
                     numero_nf, observacao, usuario_id)
                VALUES
                    (:produto_id, :fornecedor_id, :tipo, :motivo, :quantidade,
                     :estoque_antes, :estoque_depois, :preco_custo_unitario,
                     :numero_nf, :observacao, :usuario_id)
            ", [
                'produto_id'           => $dados['produto_id'],
                'fornecedor_id'        => $dados['fornecedor_id']        ?? null,
                'tipo'                 => $dados['tipo'],
                'motivo'               => $dados['motivo'],
                'quantidade'           => $qtd,
                'estoque_antes'        => $estoqueAntes,
                'estoque_depois'       => $estoqueDepois,
                'preco_custo_unitario' => $dados['preco_custo_unitario'] ?? null,
                'numero_nf'            => $dados['numero_nf']            ?? null,
                'observacao'           => $observacao,
                'usuario_id'           => $dados['usuario_id']           ?? null,
            ]);

            $movId = (int) $this->db->lastInsertId();

            // 5. Atualiza estoque (e custo se for COMPRA com preço informado)
            if ($dados['tipo'] === 'ENTRADA' && !empty($dados['preco_custo_unitario'])) {
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
    // LISTAGEM COM FILTROS
    // ----------------------------------------------------------------

    /**
     * Lista movimentações com filtros opcionais e paginação.
     *
     * @param array $filtros { produto_id?, tipo?, motivo?, usuario_id?, de?, ate?, q? }
     */
    public function listar(array $filtros = [], int $pagina = 1, int $porPagina = 30): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['produto_id'])) {
            $where[]                  = 'm.produto_id = :produto_id';
            $params['produto_id']     = $filtros['produto_id'];
        }
        if (!empty($filtros['tipo'])) {
            $where[]          = 'm.tipo = :tipo';
            $params['tipo']   = $filtros['tipo'];
        }
        if (!empty($filtros['motivo'])) {
            $where[]            = 'm.motivo = :motivo';
            $params['motivo']   = $filtros['motivo'];
        }
        if (!empty($filtros['usuario_id'])) {
            $where[]                = 'm.usuario_id = :usuario_id';
            $params['usuario_id']   = $filtros['usuario_id'];
        }
        if (!empty($filtros['de'])) {
            $where[]      = 'DATE(m.criado_em) >= :de';
            $params['de'] = $filtros['de'];
        }
        if (!empty($filtros['ate'])) {
            $where[]       = 'DATE(m.criado_em) <= :ate';
            $params['ate'] = $filtros['ate'];
        }
        if (!empty($filtros['q'])) {
            $where[]      = '(p.nome LIKE :q OR p.codigo LIKE :q2)';
            $params['q']  = '%' . $filtros['q'] . '%';
            $params['q2'] = '%' . $filtros['q'] . '%';
        }

        $whereStr = implode(' AND ', $where);

        // Total para paginação
        $total = (int) $this->db->fetchOne(
            "SELECT COUNT(*) AS total
             FROM movimentacoes_estoque m
             INNER JOIN produtos p ON p.id = m.produto_id
             WHERE {$whereStr}",
            $params
        )['total'];

        $offset         = ($pagina - 1) * $porPagina;
        $params['limit']  = $porPagina;
        $params['offset'] = $offset;

        $rows = $this->db->fetchAll("
            SELECT
                m.id,
                m.tipo,
                m.motivo,
                m.quantidade,
                m.estoque_antes,
                m.estoque_depois,
                m.preco_custo_unitario,
                m.numero_nf,
                m.observacao,
                m.criado_em,
                p.id       AS produto_id,
                p.nome     AS produto_nome,
                p.codigo   AS produto_codigo,
                un.sigla   AS unidade_sigla,
                u.nome     AS usuario_nome,
                f.razao_social AS fornecedor_nome
            FROM movimentacoes_estoque m
            INNER JOIN produtos p     ON p.id  = m.produto_id
            LEFT JOIN  unidades un    ON un.id = p.unidade_id
            LEFT JOIN  usuarios u     ON u.id  = m.usuario_id
            LEFT JOIN  fornecedores f ON f.id  = m.fornecedor_id
            WHERE {$whereStr}
            ORDER BY m.criado_em DESC
            LIMIT :limit OFFSET :offset
        ", $params);

        return [
            'dados'       => $rows,
            'total'       => $total,
            'pagina'      => $pagina,
            'por_pagina'  => $porPagina,
            'paginas'     => (int) ceil($total / $porPagina),
        ];
    }

    /**
     * Histórico de movimentações de um produto.
     */
    public function historicoPorProduto(int $produtoId, int $limite = 50): array
    {
        return $this->db->fetchAll("
            SELECT m.*, f.razao_social AS fornecedor_nome, u.nome AS usuario_nome
            FROM movimentacoes_estoque m
            LEFT JOIN fornecedores f ON f.id = m.fornecedor_id
            LEFT JOIN usuarios u     ON u.id = m.usuario_id
            WHERE m.produto_id = :produto_id
            ORDER BY m.criado_em DESC
            LIMIT :limite
        ", ['produto_id' => $produtoId, 'limite' => $limite]);
    }

    /**
     * Movimentações recentes (dashboard / widget).
     */
    public function recentes(int $limite = 20): array
    {
        return $this->db->fetchAll("
            SELECT
                m.*,
                p.nome         AS produto_nome,
                p.codigo       AS produto_codigo,
                un.sigla       AS unidade_sigla,
                u.nome         AS usuario_nome,
                f.razao_social AS fornecedor_nome
            FROM movimentacoes_estoque m
            INNER JOIN produtos p     ON p.id  = m.produto_id
            LEFT JOIN  unidades un    ON un.id = p.unidade_id
            LEFT JOIN  usuarios u     ON u.id  = m.usuario_id
            LEFT JOIN  fornecedores f ON f.id  = m.fornecedor_id
            ORDER BY m.criado_em DESC
            LIMIT :limite
        ", ['limite' => $limite]);
    }

    /**
     * Entradas por fornecedor em um período.
     */
    public function entradasPorFornecedor(int $fornecedorId, string $de, string $ate): array
    {
        return $this->db->fetchAll("
            SELECT m.*, p.nome AS produto_nome, p.codigo AS produto_codigo, un.sigla AS unidade_sigla
            FROM movimentacoes_estoque m
            INNER JOIN produtos p  ON p.id  = m.produto_id
            LEFT JOIN  unidades un ON un.id = p.unidade_id
            WHERE m.fornecedor_id = :fornecedor_id
              AND m.tipo = 'ENTRADA'
              AND DATE(m.criado_em) BETWEEN :de AND :ate
            ORDER BY m.criado_em DESC
        ", ['fornecedor_id' => $fornecedorId, 'de' => $de, 'ate' => $ate]);
    }

    // ----------------------------------------------------------------
    // VALIDAÇÃO
    // ----------------------------------------------------------------

    private function validar(array $dados): void
    {
        if (empty($dados['produto_id'])) {
            throw new \InvalidArgumentException('produto_id é obrigatório.');
        }
        if (empty($dados['tipo']) || !array_key_exists($dados['tipo'], self::TIPO_LABELS)) {
            throw new \InvalidArgumentException('Tipo inválido. Use ENTRADA, SAIDA ou AJUSTE.');
        }
        if (empty($dados['motivo']) || !array_key_exists($dados['motivo'], self::MOTIVO_LABELS)) {
            throw new \InvalidArgumentException('Motivo inválido.');
        }
        if (!isset($dados['quantidade']) || (float) $dados['quantidade'] <= 0) {
            throw new \InvalidArgumentException('Quantidade deve ser maior que zero.');
        }
        // Valida coerência tipo × motivo
        $tipoEsperado = self::MOTIVO_TIPO[$dados['motivo']] ?? null;
        if ($tipoEsperado && $tipoEsperado !== $dados['tipo']) {
            throw new \InvalidArgumentException(
                "Motivo \"{$dados['motivo']}\" não é compatível com o tipo \"{$dados['tipo']}\"."
            );
        }
    }
}
