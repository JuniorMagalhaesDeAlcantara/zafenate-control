<?php

namespace App\Models;

use App\Core\Database;
use RuntimeException;

/**
 * Model do Módulo de Vendas
 * Separado do Venda.php (que cuida da persistência do PDV)
 * para não misturar responsabilidades.
 */
class VendaModulo
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ----------------------------------------------------------------
    // LISTAGEM COM FILTROS
    // ----------------------------------------------------------------

    public function listar(array $filtros = [], int $pagina = 1, int $porPagina = 20): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['numero'])) {
            $where[]            = 'v.numero = :numero';
            $params['numero']   = (int) $filtros['numero'];
        }

        if (!empty($filtros['status'])) {
            $where[]           = 'v.status = :status';
            $params['status']  = $filtros['status'];
        }

        if (!empty($filtros['data_de'])) {
            $where[]            = 'DATE(v.criado_em) >= :data_de';
            $params['data_de']  = $filtros['data_de'];
        }

        if (!empty($filtros['data_ate'])) {
            $where[]             = 'DATE(v.criado_em) <= :data_ate';
            $params['data_ate']  = $filtros['data_ate'];
        }

        if (!empty($filtros['usuario_id'])) {
            $where[]               = 'v.usuario_id = :usuario_id';
            $params['usuario_id']  = (int) $filtros['usuario_id'];
        }

        if (!empty($filtros['cliente_id'])) {
            $where[]               = 'v.cliente_id = :cliente_id';
            $params['cliente_id']  = (int) $filtros['cliente_id'];
        }

        if (!empty($filtros['forma_pagamento'])) {
            $where[]  = 'EXISTS (SELECT 1 FROM venda_pagamentos vp WHERE vp.venda_id = v.id AND vp.forma = :forma)';
            $params['forma'] = $filtros['forma_pagamento'];
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($pagina - 1) * $porPagina;

        $total = (int) $this->db->fetchScalar(
            "SELECT COUNT(*) FROM vendas v WHERE {$whereStr}",
            $params
        );

        $params['limite'] = $porPagina;
        $params['offset'] = $offset;

        $rows = $this->db->fetchAll("
            SELECT
                v.id, v.numero, v.status,
                v.subtotal, v.desconto_valor, v.total,
                v.criado_em, v.cancelado_em,
                cl.nome          AS cliente_nome,
                u.nome           AS operador_nome,
                cx.id            AS caixa_id,
                GROUP_CONCAT(
                    DISTINCT vp.forma ORDER BY vp.forma SEPARATOR ','
                )                AS formas_pagamento,
                COUNT(vi.id)     AS qtd_itens
            FROM vendas v
            LEFT JOIN clientes cl          ON cl.id = v.cliente_id
            LEFT JOIN usuarios u           ON u.id  = v.usuario_id
            LEFT JOIN caixas   cx          ON cx.id = v.caixa_id
            LEFT JOIN venda_pagamentos vp  ON vp.venda_id = v.id
            LEFT JOIN venda_itens vi       ON vi.venda_id = v.id
            WHERE {$whereStr}
            GROUP BY v.id
            ORDER BY v.criado_em DESC
            LIMIT :limite OFFSET :offset
        ", $params);

        return [
            'dados'        => $rows,
            'total'        => $total,
            'pagina'       => $pagina,
            'por_pagina'   => $porPagina,
            'total_paginas' => (int) ceil($total / $porPagina),
        ];
    }

    // ----------------------------------------------------------------
    // DETALHE COMPLETO
    // ----------------------------------------------------------------

    public function buscarPorId(int $id): ?array
    {
        $venda = $this->db->fetchOne("
            SELECT
                v.*,
                cl.nome          AS cliente_nome,
                cl.cpf_cnpj      AS cliente_cpf,
                cl.telefone      AS cliente_telefone,
                u.nome           AS operador_nome,
                uc.nome          AS cancelado_por_nome,
                cx.saldo_abertura AS caixa_abertura
            FROM vendas v
            LEFT JOIN clientes cl  ON cl.id = v.cliente_id
            LEFT JOIN usuarios u   ON u.id  = v.usuario_id
            LEFT JOIN usuarios uc  ON uc.id = v.cancelado_por
            LEFT JOIN caixas   cx  ON cx.id = v.caixa_id
            WHERE v.id = :id
            LIMIT 1
        ", ['id' => $id]);

        if (!$venda) return null;

        $venda['itens']      = $this->itens($id);
        $venda['pagamentos'] = $this->pagamentos($id);

        return $venda;
    }

    public function buscarPorNumero(int $numero): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT id FROM vendas WHERE numero = :n LIMIT 1",
            ['n' => $numero]
        );
        return $row ? $this->buscarPorId((int) $row['id']) : null;
    }

    public function itens(int $vendaId): array
    {
        return $this->db->fetchAll(
            "SELECT vi.*, p.imagem AS produto_imagem
             FROM venda_itens vi
             LEFT JOIN produtos p ON p.id = vi.produto_id
             WHERE vi.venda_id = :id
             ORDER BY vi.id",
            ['id' => $vendaId]
        );
    }

    public function pagamentos(int $vendaId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM venda_pagamentos WHERE venda_id = :id ORDER BY id",
            ['id' => $vendaId]
        );
    }

    // ----------------------------------------------------------------
    // CANCELAMENTO
    // ----------------------------------------------------------------

    /**
     * Cancela uma venda finalizada:
     * - Muda status para 'cancelada'
     * - Estorna estoque de cada item
     * - Registra movimentação de estoque (DEVOLUCAO)
     * - Atualiza totais do caixa
     */
    public function cancelar(int $vendaId, int $usuarioId, string $motivo): void
    {
        $this->db->transaction(function () use ($vendaId, $usuarioId, $motivo) {

            $venda = $this->db->fetchOne(
                "SELECT * FROM vendas WHERE id = :id FOR UPDATE",
                ['id' => $vendaId]
            );

            if (!$venda) {
                throw new RuntimeException("Venda não encontrada.");
            }
            if ($venda['status'] !== 'finalizada') {
                throw new RuntimeException("Apenas vendas finalizadas podem ser canceladas.");
            }

            // 1. Cancela a venda
            $this->db->execute("
                UPDATE vendas SET
                    status               = 'cancelada',
                    cancelado_por        = :uid,
                    motivo_cancelamento  = :motivo,
                    cancelado_em         = NOW()
                WHERE id = :id
            ", ['uid' => $usuarioId, 'motivo' => $motivo, 'id' => $vendaId]);

            // 2. Estorna estoque item a item
            $itens = $this->itens($vendaId);
            foreach ($itens as $item) {

                $snap = $this->db->fetchOne(
                    "SELECT estoque_atual FROM produtos WHERE id = :id FOR UPDATE",
                    ['id' => $item['produto_id']]
                );

                $estoqueAntes  = (float) ($snap['estoque_atual'] ?? 0);
                $estoqueDepois = $estoqueAntes + (float) $item['quantidade'];

                $this->db->execute(
                    "UPDATE produtos SET estoque_atual = :est WHERE id = :id",
                    ['est' => $estoqueDepois, 'id' => $item['produto_id']]
                );

                $this->db->execute("
                    INSERT INTO movimentacoes_estoque
                        (produto_id, tipo, motivo, quantidade,
                         estoque_antes, estoque_depois, usuario_id, observacao)
                    VALUES
                        (:pid, 'ENTRADA', 'DEVOLUCAO', :qty,
                         :antes, :depois, :uid, :obs)
                ", [
                    'pid'    => $item['produto_id'],
                    'qty'    => $item['quantidade'],
                    'antes'  => $estoqueAntes,
                    'depois' => $estoqueDepois,
                    'uid'    => $usuarioId,
                    'obs'    => "Estorno — cancelamento venda #{$venda['numero']}",
                ]);
            }

            // 3. Reverte totais do caixa
            $dinheiroPago = (float) $this->db->fetchScalar("
                SELECT COALESCE(SUM(valor - troco), 0)
                FROM venda_pagamentos
                WHERE venda_id = :id AND forma = 'dinheiro'
            ", ['id' => $vendaId]);

            $this->db->execute("
                UPDATE caixas SET
                    total_vendas   = total_vendas   - :total,
                    saldo_esperado = saldo_esperado - :dinheiro
                WHERE id = :caixa_id
            ", [
                'total'    => $venda['total'],
                'dinheiro' => $dinheiroPago,
                'caixa_id' => $venda['caixa_id'],
            ]);
        });
    }

    // ----------------------------------------------------------------
    // TOTALIZADORES (relatório / dashboard)
    // ----------------------------------------------------------------

    public function totaisPorPeriodo(string $de, string $ate): array
    {
        return $this->db->fetchOne("
            SELECT
                COUNT(*)                                     AS total_vendas,
                COUNT(CASE WHEN status='cancelada' THEN 1 END) AS canceladas,
                COALESCE(SUM(CASE WHEN status='finalizada' THEN total END), 0) AS faturamento,
                COALESCE(SUM(CASE WHEN status='finalizada' THEN desconto_valor END), 0) AS total_descontos,
                COALESCE(AVG(CASE WHEN status='finalizada' THEN total END), 0) AS ticket_medio
            FROM vendas
            WHERE DATE(criado_em) BETWEEN :de AND :ate
        ", ['de' => $de, 'ate' => $ate]) ?? [];
    }

    public function faturamentoPorDia(string $de, string $ate): array
    {
        return $this->db->fetchAll("
            SELECT
                DATE(criado_em)     AS dia,
                COUNT(*)            AS qtd_vendas,
                SUM(total)          AS faturamento
            FROM vendas
            WHERE status = 'finalizada'
              AND DATE(criado_em) BETWEEN :de AND :ate
            GROUP BY DATE(criado_em)
            ORDER BY dia ASC
        ", ['de' => $de, 'ate' => $ate]);
    }

    public function topProdutos(string $de, string $ate, int $limite = 10): array
    {
        return $this->db->fetchAll("
            SELECT
                vi.produto_nome,
                vi.produto_codigo,
                SUM(vi.quantidade)   AS qtd_vendida,
                SUM(vi.subtotal)     AS faturamento
            FROM venda_itens vi
            INNER JOIN vendas v ON v.id = vi.venda_id
            WHERE v.status = 'finalizada'
              AND DATE(v.criado_em) BETWEEN :de AND :ate
            GROUP BY vi.produto_id, vi.produto_nome, vi.produto_codigo
            ORDER BY faturamento DESC
            LIMIT :limite
        ", ['de' => $de, 'ate' => $ate, 'limite' => $limite]);
    }

    public function vendasPorForma(string $de, string $ate): array
    {
        return $this->db->fetchAll("
            SELECT
                vp.forma,
                COUNT(DISTINCT vp.venda_id) AS qtd_vendas,
                SUM(vp.valor)               AS total_recebido
            FROM venda_pagamentos vp
            INNER JOIN vendas v ON v.id = vp.venda_id
            WHERE v.status = 'finalizada'
              AND DATE(v.criado_em) BETWEEN :de AND :ate
            GROUP BY vp.forma
            ORDER BY total_recebido DESC
        ", ['de' => $de, 'ate' => $ate]);
    }

    // ----------------------------------------------------------------
    // HELPERS
    // ----------------------------------------------------------------

    public function listarOperadores(): array
    {
        return $this->db->fetchAll(
            "SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome"
        );
    }

    public function listarClientes(): array
    {
        return $this->db->fetchAll(
            "SELECT id, nome FROM clientes WHERE ativo = 1 ORDER BY nome LIMIT 200"
        );
    }
}
