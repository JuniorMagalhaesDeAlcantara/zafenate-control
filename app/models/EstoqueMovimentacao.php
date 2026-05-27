<?php

namespace App\Models;

use App\Core\Database;
use RuntimeException;

class EstoqueMovimentacao
{
    private Database $db;

    // Mapeamento tipo → motivos válidos
    public const MOTIVOS_POR_TIPO = [
        'ENTRADA' => ['COMPRA', 'DEVOLUCAO'],
        'SAIDA'   => ['VENDA', 'PERDA', 'USO_INTERNO', 'TRANSFERENCIA'],
        'AJUSTE'  => ['AJUSTE_MANUAL'],
    ];

    public const LABELS_TIPO = [
        'ENTRADA' => 'Entrada',
        'SAIDA'   => 'Saída',
        'AJUSTE'  => 'Ajuste',
    ];

    public const LABELS_MOTIVO = [
        'COMPRA'        => 'Compra / Reposição',
        'DEVOLUCAO'     => 'Devolução de Cliente',
        'VENDA'         => 'Venda (PDV)',
        'PERDA'         => 'Perda / Avaria / Vencimento',
        'USO_INTERNO'   => 'Uso Interno',
        'AJUSTE_MANUAL' => 'Ajuste de Inventário',
        'TRANSFERENCIA' => 'Transferência',
    ];

    public const ORIGENS = [
        'COMPRA'        => 'Entrada de Mercadoria',
        'DEVOLUCAO'     => 'Cancelamento de Venda',
        'VENDA'         => 'PDV',
        'PERDA'         => 'Ajuste Manual',
        'USO_INTERNO'   => 'Ajuste Manual',
        'AJUSTE_MANUAL' => 'Ajuste Manual',
        'TRANSFERENCIA' => 'Transferência',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ----------------------------------------------------------------
    // LISTAGEM COM FILTROS + PAGINAÇÃO
    // ----------------------------------------------------------------

    public function listar(array $filtros = [], int $pagina = 1, int $porPagina = 30): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['produto_id'])) {
            $where[]               = 'm.produto_id = :produto_id';
            $params['produto_id']  = (int) $filtros['produto_id'];
        }

        if (!empty($filtros['busca_produto'])) {
            $where[]                    = '(p.nome LIKE :bp1 OR p.codigo LIKE :bp2)';
            $termo                      = '%' . $filtros['busca_produto'] . '%';
            $params['bp1']              = $termo;
            $params['bp2']              = $termo;
        }

        if (!empty($filtros['tipo'])) {
            $where[]        = 'm.tipo = :tipo';
            $params['tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['motivo'])) {
            $where[]          = 'm.motivo = :motivo';
            $params['motivo'] = $filtros['motivo'];
        }

        if (!empty($filtros['usuario_id'])) {
            $where[]               = 'm.usuario_id = :usuario_id';
            $params['usuario_id']  = (int) $filtros['usuario_id'];
        }

        if (!empty($filtros['data_de'])) {
            $where[]           = 'DATE(m.criado_em) >= :data_de';
            $params['data_de'] = $filtros['data_de'];
        }

        if (!empty($filtros['data_ate'])) {
            $where[]            = 'DATE(m.criado_em) <= :data_ate';
            $params['data_ate'] = $filtros['data_ate'];
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($pagina - 1) * $porPagina;

        $total = (int) $this->db->fetchScalar(
            "SELECT COUNT(*) FROM movimentacoes_estoque m
             INNER JOIN produtos p ON p.id = m.produto_id
             WHERE {$whereStr}",
            $params
        );

        $params['limite'] = $porPagina;
        $params['offset'] = $offset;

        $rows = $this->db->fetchAll("
            SELECT
                m.*,
                p.nome          AS produto_nome,
                p.codigo        AS produto_codigo,
                u.sigla         AS unidade_sigla,
                usr.nome        AS usuario_nome,
                f.razao_social  AS fornecedor_nome
            FROM movimentacoes_estoque m
            INNER JOIN produtos p          ON p.id   = m.produto_id
            LEFT JOIN  unidades u          ON u.id   = p.unidade_id
            LEFT JOIN  usuarios usr        ON usr.id = m.usuario_id
            LEFT JOIN  fornecedores f      ON f.id   = m.fornecedor_id
            WHERE {$whereStr}
            ORDER BY m.criado_em DESC
            LIMIT :limite OFFSET :offset
        ", $params);

        return [
            'dados'         => $rows,
            'total'         => $total,
            'pagina'        => $pagina,
            'por_pagina'    => $porPagina,
            'total_paginas' => (int) ceil($total / $porPagina),
        ];
    }

    // ----------------------------------------------------------------
    // HISTÓRICO DE UM PRODUTO ESPECÍFICO
    // ----------------------------------------------------------------

    public function historicoProduto(int $produtoId, int $limite = 50): array
    {
        return $this->db->fetchAll("
            SELECT
                m.*,
                usr.nome AS usuario_nome
            FROM movimentacoes_estoque m
            LEFT JOIN usuarios usr ON usr.id = m.usuario_id
            WHERE m.produto_id = :pid
            ORDER BY m.criado_em DESC
            LIMIT :limite
        ", ['pid' => $produtoId, 'limite' => $limite]);
    }

    // ----------------------------------------------------------------
    // REGISTRAR MOVIMENTAÇÃO MANUAL (ENTRADA, SAÍDA, AJUSTE)
    // ----------------------------------------------------------------

    public function registrar(array $dados): int
    {
        $this->validar($dados);

        return $this->db->transaction(function () use ($dados) {

            // Lock no produto para evitar race condition
            $produto = $this->db->fetchOne(
                "SELECT id, nome, estoque_atual FROM produtos WHERE id = :id AND ativo = 1 FOR UPDATE",
                ['id' => $dados['produto_id']]
            );

            if (!$produto) {
                throw new RuntimeException("Produto não encontrado ou inativo.");
            }

            $estoqueAntes = (float) $produto['estoque_atual'];
            $qtd          = (float) $dados['quantidade'];

            $estoqueDepois = match ($dados['tipo']) {
                'ENTRADA' => $estoqueAntes + $qtd,
                'SAIDA'   => $estoqueAntes - $qtd,
                'AJUSTE'  => $qtd,                   // valor absoluto
            };

            if ($dados['tipo'] === 'SAIDA' && $estoqueDepois < 0) {
                throw new RuntimeException(
                    "Estoque insuficiente. Disponível: " .
                        number_format($estoqueAntes, 3, ',', '.') .
                        ", solicitado: " . number_format($qtd, 3, ',', '.')
                );
            }

            // Insere movimentação
            $this->db->execute("
                INSERT INTO movimentacoes_estoque
                    (produto_id, fornecedor_id, usuario_id, tipo, motivo,
                     quantidade, estoque_antes, estoque_depois,
                     preco_custo_unitario, numero_nf, observacao)
                VALUES
                    (:produto_id, :fornecedor_id, :usuario_id, :tipo, :motivo,
                     :quantidade, :estoque_antes, :estoque_depois,
                     :preco_custo_unitario, :numero_nf, :observacao)
            ", [
                'produto_id'           => $dados['produto_id'],
                'fornecedor_id'        => $dados['fornecedor_id']        ?? null,
                'usuario_id'           => $dados['usuario_id'],
                'tipo'                 => $dados['tipo'],
                'motivo'               => $dados['motivo'],
                'quantidade'           => $qtd,
                'estoque_antes'        => $estoqueAntes,
                'estoque_depois'       => $estoqueDepois,
                'preco_custo_unitario' => !empty($dados['preco_custo_unitario'])
                    ? (float) str_replace(',', '.', $dados['preco_custo_unitario'])
                    : null,
                'numero_nf'            => $dados['numero_nf']  ?? null,
                'observacao'           => $dados['observacao'] ?? null,
            ]);

            $movId = (int) $this->db->lastInsertId();

            // Atualiza estoque do produto
            if ($dados['tipo'] === 'ENTRADA' && !empty($dados['preco_custo_unitario'])) {
                $this->db->execute(
                    "UPDATE produtos SET estoque_atual = :est, preco_custo = :custo WHERE id = :id",
                    [
                        'est'   => $estoqueDepois,
                        'custo' => (float) str_replace(',', '.', $dados['preco_custo_unitario']),
                        'id'    => $dados['produto_id'],
                    ]
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
    // TOTALIZADORES
    // ----------------------------------------------------------------

    public function totaisPorPeriodo(string $de, string $ate): array
    {
        return $this->db->fetchOne("
            SELECT
                COUNT(*)                                           AS total_movs,
                SUM(tipo = 'ENTRADA')                             AS total_entradas,
                SUM(tipo = 'SAIDA')                               AS total_saidas,
                SUM(tipo = 'AJUSTE')                              AS total_ajustes,
                SUM(CASE WHEN tipo = 'ENTRADA' THEN quantidade END) AS qtd_entrou,
                SUM(CASE WHEN tipo = 'SAIDA'   THEN quantidade END) AS qtd_saiu
            FROM movimentacoes_estoque
            WHERE DATE(criado_em) BETWEEN :de AND :ate
        ", ['de' => $de, 'ate' => $ate]) ?? [];
    }

    // ----------------------------------------------------------------
    // HELPERS
    // ----------------------------------------------------------------

    public function listarProdutos(): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.nome, p.codigo, p.estoque_atual, u.sigla AS unidade_sigla
             FROM produtos p
             LEFT JOIN unidades u ON u.id = p.unidade_id
             WHERE p.ativo = 1
             ORDER BY p.nome ASC"
        );
    }

    public function listarFornecedores(): array
    {
        return $this->db->fetchAll(
            "SELECT id, razao_social FROM fornecedores WHERE ativo = 1 ORDER BY razao_social"
        );
    }

    public function listarOperadores(): array
    {
        return $this->db->fetchAll(
            "SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome"
        );
    }

    public function buscarProduto(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT p.*, u.sigla AS unidade_sigla
             FROM produtos p
             LEFT JOIN unidades u ON u.id = p.unidade_id
             WHERE p.id = :id AND p.ativo = 1 LIMIT 1",
            ['id' => $id]
        );
    }

    private function validar(array $dados): void
    {
        if (empty($dados['produto_id'])) {
            throw new \InvalidArgumentException('Selecione um produto.');
        }
        if (empty($dados['tipo']) || !array_key_exists($dados['tipo'], self::LABELS_TIPO)) {
            throw new \InvalidArgumentException('Tipo de movimentação inválido.');
        }
        if (empty($dados['motivo']) || !array_key_exists($dados['motivo'], self::LABELS_MOTIVO)) {
            throw new \InvalidArgumentException('Motivo inválido.');
        }
        $motivosValidos = self::MOTIVOS_POR_TIPO[$dados['tipo']] ?? [];
        if (!in_array($dados['motivo'], $motivosValidos, true)) {
            throw new \InvalidArgumentException(
                "Motivo \"{$dados['motivo']}\" não é válido para o tipo \"{$dados['tipo']}\"."
            );
        }
        if (empty($dados['quantidade']) || (float)$dados['quantidade'] <= 0) {
            throw new \InvalidArgumentException('Quantidade deve ser maior que zero.');
        }
    }
}
