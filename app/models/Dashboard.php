<?php

namespace App\Models;

use App\Core\Database;

class Dashboard
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ----------------------------------------------------------------
    // CARDS — VENDAS DO DIA
    // ----------------------------------------------------------------

    public function vendasHoje(): array
    {
        $vendas = $this->db->fetchOne("
            SELECT
                COALESCE(SUM(total), 0)   AS faturamento_hoje,
                COUNT(*)                  AS qtd_vendas_hoje
            FROM vendas
            WHERE DATE(criado_em) = CURDATE()
              AND status = 'finalizada'
        ") ?? [];

        $lucro = $this->db->fetchOne("
            SELECT COALESCE(SUM(
                (vi.preco_unitario - vi.preco_custo) * vi.quantidade
            ), 0) AS lucro_bruto_hoje
            FROM venda_itens vi
            INNER JOIN vendas v ON v.id = vi.venda_id
            WHERE DATE(v.criado_em) = CURDATE()
              AND v.status = 'finalizada'
        ") ?? [];

        $fat   = (float)($vendas['faturamento_hoje'] ?? 0);
        $qtd   = (int)  ($vendas['qtd_vendas_hoje']  ?? 0);
        $lucroVal = (float)($lucro['lucro_bruto_hoje'] ?? 0);

        return [
            'faturamento_hoje' => $fat,
            'qtd_vendas_hoje'  => $qtd,
            'lucro_bruto_hoje' => $lucroVal,
            'ticket_medio'     => $qtd > 0 ? round($fat / $qtd, 2) : 0.0,
            'margem_hoje'      => $fat > 0  ? round(($lucroVal / $fat) * 100, 1) : 0.0,
        ];
    }

    // ----------------------------------------------------------------
    // CARDS — FATURAMENTO E LUCRO DO MÊS
    // ----------------------------------------------------------------

    public function resumoMes(): array
    {
        $mes = $this->db->fetchOne("
            SELECT
                COALESCE(SUM(v.total), 0)  AS faturamento_mes,
                COUNT(*)                   AS qtd_vendas_mes
            FROM vendas v
            WHERE MONTH(v.criado_em) = MONTH(CURDATE())
              AND YEAR(v.criado_em)  = YEAR(CURDATE())
              AND v.status = 'finalizada'
        ") ?? [];

        $lucroMes = $this->db->fetchOne("
            SELECT COALESCE(SUM(
                (vi.preco_unitario - vi.preco_custo) * vi.quantidade
            ), 0) AS lucro_bruto_mes
            FROM venda_itens vi
            INNER JOIN vendas v ON v.id = vi.venda_id
            WHERE MONTH(v.criado_em) = MONTH(CURDATE())
              AND YEAR(v.criado_em)  = YEAR(CURDATE())
              AND v.status = 'finalizada'
        ") ?? [];

        $mesAnterior = $this->db->fetchOne("
            SELECT COALESCE(SUM(total), 0) AS faturamento_mes_anterior
            FROM vendas
            WHERE status = 'finalizada'
              AND MONTH(criado_em) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
              AND YEAR(criado_em)  = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
        ") ?? [];

        // Conta produtos vendidos no mês que tinham custo zerado no snapshot
        // (preco_custo = 0 no venda_itens significa que o custo não foi preenchido)
        $semCusto = $this->db->fetchOne("
            SELECT COUNT(DISTINCT vi.produto_id) AS qtd_sem_custo
            FROM venda_itens vi
            INNER JOIN vendas v ON v.id = vi.venda_id
            WHERE v.status = 'finalizada'
              AND MONTH(v.criado_em) = MONTH(CURDATE())
              AND YEAR(v.criado_em)  = YEAR(CURDATE())
              AND vi.preco_custo = 0
        ") ?? [];

        $fat    = (float)($mes['faturamento_mes']                  ?? 0);
        $fatAnt = (float)($mesAnterior['faturamento_mes_anterior'] ?? 0);
        $lucro  = (float)($lucroMes['lucro_bruto_mes']             ?? 0);

        $variacao = 0.0;
        if ($fatAnt > 0) {
            $variacao = round((($fat - $fatAnt) / $fatAnt) * 100, 1);
        }

        return [
            'faturamento_mes'          => $fat,
            'lucro_bruto_mes'          => $lucro,
            'margem_mes'               => $fat > 0 ? round(($lucro / $fat) * 100, 1) : 0.0,
            'qtd_vendas_mes'           => (int)($mes['qtd_vendas_mes']    ?? 0),
            'faturamento_mes_anterior' => $fatAnt,
            'variacao_mes'             => $variacao,
            'produtos_sem_custo'       => (int)($semCusto['qtd_sem_custo'] ?? 0),
        ];
    }

    // ----------------------------------------------------------------
    // CARDS — ESTOQUE
    // ----------------------------------------------------------------

    public function resumoEstoque(): array
    {
        return $this->db->fetchOne("
        SELECT
            COUNT(*)                                                              AS total_produtos,
            SUM(ativo = 1)                                                        AS produtos_ativos,
            SUM(ativo = 1 AND estoque_minimo > 0
                AND estoque_atual <= estoque_minimo)                              AS alerta_estoque,
            SUM(ativo = 1 AND estoque_atual = 0)                                  AS sem_estoque,
            COALESCE(SUM(estoque_atual * preco_custo), 0)                         AS valor_em_estoque,
            -- lucro presumido: o que sobraria se vender tudo pelo preço de venda
            COALESCE(SUM(
                CASE
                    WHEN ativo = 1 AND estoque_atual > 0 AND preco_custo > 0
                    THEN estoque_atual * (preco_venda - preco_custo)
                    ELSE 0
                END
            ), 0)                                                                 AS lucro_presumido_estoque,
            -- qtd de produtos ativos com estoque > 0 mas sem preco_venda ou preco_custo zerado
            SUM(ativo = 1 AND estoque_atual > 0 AND (preco_custo = 0 OR preco_venda = 0)) AS sem_preco_completo
        FROM produtos
    ") ?? [];
    }

    // ----------------------------------------------------------------
    // CARDS — COMPRAS DO MÊS
    // ----------------------------------------------------------------

    public function resumoComprasMes(): array
    {
        return $this->db->fetchOne("
            SELECT
                COUNT(*)                AS qtd_compras,
                COALESCE(SUM(total), 0) AS valor_compras
            FROM compras
            WHERE MONTH(data_emissao) = MONTH(CURDATE())
              AND YEAR(data_emissao)  = YEAR(CURDATE())
              AND status = 'confirmada'
        ") ?? [];
    }

    // ----------------------------------------------------------------
    // CAIXA ABERTO (se houver)
    // ----------------------------------------------------------------

    public function caixaAberto(): ?array
    {
        return $this->db->fetchOne("
            SELECT c.*, u.nome AS operador
            FROM caixas c
            INNER JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.status = 'aberto'
            ORDER BY c.aberto_em DESC
            LIMIT 1
        ") ?: null;
    }

    // ----------------------------------------------------------------
    // GRÁFICO — FATURAMENTO E LUCRO DOS ÚLTIMOS 7 DIAS
    // ----------------------------------------------------------------

    public function faturamento7Dias(): array
    {
        // Faturamento e qtd vêm de vendas (sem JOIN para não inflar o SUM)
        // Lucro vem de venda_itens via subquery correlacionada por dia
        $rows = $this->db->fetchAll("
            SELECT
                fat.dia,
                fat.faturamento,
                fat.qtd_vendas,
                COALESCE(lucro.lucro_bruto, 0) AS lucro_bruto
            FROM (
                SELECT
                    DATE(criado_em)          AS dia,
                    COALESCE(SUM(total), 0)  AS faturamento,
                    COUNT(*)                 AS qtd_vendas
                FROM vendas
                WHERE status = 'finalizada'
                  AND criado_em >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(criado_em)
            ) AS fat
            LEFT JOIN (
                SELECT
                    DATE(v.criado_em) AS dia,
                    COALESCE(SUM(
                        (vi.preco_unitario - vi.preco_custo) * vi.quantidade
                    ), 0)             AS lucro_bruto
                FROM venda_itens vi
                INNER JOIN vendas v ON v.id = vi.venda_id
                WHERE v.status = 'finalizada'
                  AND v.criado_em >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(v.criado_em)
            ) AS lucro ON lucro.dia = fat.dia
            ORDER BY fat.dia ASC
        ");

        // Indexa por data para lookup O(1)
        $byData = [];
        foreach ($rows as $r) {
            $byData[$r['dia']] = $r;
        }

        // Garante os 7 dias completos sem gaps
        $resultado = [];
        for ($i = 6; $i >= 0; $i--) {
            $data  = date('Y-m-d', strtotime("-{$i} days"));
            $label = date('d/m',   strtotime("-{$i} days"));
            $r     = $byData[$data] ?? null;

            $resultado[] = [
                'dia'          => $label,
                'faturamento'  => $r ? (float)$r['faturamento']  : 0.0,
                'lucro_bruto'  => $r ? (float)$r['lucro_bruto']  : 0.0,
                'qtd_vendas'   => $r ? (int)  $r['qtd_vendas']   : 0,
            ];
        }

        return $resultado;
    }

    // ----------------------------------------------------------------
    // TOP 5 PRODUTOS MAIS VENDIDOS (30 dias)
    // ----------------------------------------------------------------

    public function topProdutos(int $limite = 5, int $dias = 30): array
    {
        return $this->db->fetchAll("
            SELECT
                vi.produto_id,
                vi.produto_nome,
                SUM(vi.quantidade)                                      AS total_qty,
                SUM(vi.subtotal)                                        AS total_receita,
                SUM((vi.preco_unitario - vi.preco_custo) * vi.quantidade) AS total_lucro
            FROM venda_itens vi
            INNER JOIN vendas v ON v.id = vi.venda_id
            WHERE v.status = 'finalizada'
              AND v.criado_em >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
            GROUP BY vi.produto_id, vi.produto_nome
            ORDER BY total_qty DESC
            LIMIT :limite
        ", ['dias' => $dias, 'limite' => $limite]);
    }

    // ----------------------------------------------------------------
    // PRODUTOS COM ESTOQUE ABAIXO DO MÍNIMO
    // ----------------------------------------------------------------

    public function produtosAlerta(int $limite = 6): array
    {
        return $this->db->fetchAll("
            SELECT
                p.id, p.nome, p.codigo,
                p.estoque_atual, p.estoque_minimo,
                COALESCE(u.sigla, 'UN') AS unidade_sigla
            FROM produtos p
            LEFT JOIN unidades u ON u.id = p.unidade_id
            WHERE p.ativo = 1
              AND p.estoque_minimo > 0
              AND p.estoque_atual <= p.estoque_minimo
            ORDER BY (p.estoque_atual / p.estoque_minimo) ASC
            LIMIT :limite
        ", ['limite' => $limite]);
    }

    // ----------------------------------------------------------------
    // ÚLTIMAS VENDAS FINALIZADAS
    // ----------------------------------------------------------------

    public function ultimasVendas(int $limite = 5): array
    {
        return $this->db->fetchAll("
            SELECT
                v.id, v.numero, v.total, v.criado_em,
                c.nome AS cliente_nome,
                u.nome AS operador_nome,
                (SELECT forma FROM venda_pagamentos
                 WHERE venda_id = v.id LIMIT 1) AS forma_pgto
            FROM vendas v
            LEFT JOIN clientes c  ON c.id = v.cliente_id
            LEFT JOIN usuarios u  ON u.id = v.usuario_id
            WHERE v.status = 'finalizada'
            ORDER BY v.criado_em DESC
            LIMIT :limite
        ", ['limite' => $limite]);
    }
}
