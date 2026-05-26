<?php

namespace App\Models;

use App\Core\Database;
use RuntimeException;

class Venda
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function salvarVenda(array $dados, array $itens, array $pagamentos): int
    {
        return $this->db->transaction(function () use ($dados, $itens, $pagamentos) {

            // 1. Próximo número sequencial
            $res = $this->db->fetchOne("SELECT COALESCE(MAX(numero), 0) + 1 AS proximo FROM vendas");
            $dados['numero'] = (int) $res['proximo'];

            // 2. Cabeçalho da venda
            $this->db->execute("
                INSERT INTO vendas
                    (caixa_id, cliente_id, usuario_id, numero, status,
                     subtotal, desconto_tipo, desconto_valor, desconto_perc, total)
                VALUES
                    (:caixa_id, :cliente_id, :usuario_id, :numero, 'finalizada',
                     :subtotal, :desconto_tipo, :desconto_valor, :desconto_perc, :total)
            ", $dados);

            $vendaId = (int) $this->db->lastInsertId();

            // 3. Itens + estoque
            foreach ($itens as $item) {

                // Lock para evitar race condition
                $snap = $this->db->fetchOne(
                    "SELECT estoque_atual FROM produtos WHERE id = :id FOR UPDATE",
                    ['id' => $item['id']]
                );

                if (!$snap) {
                    throw new RuntimeException("Produto ID {$item['id']} não encontrado.");
                }

                $estoqueAntes  = (float) $snap['estoque_atual'];
                $estoqueDepois = $estoqueAntes - (float) $item['qty'];

                if ($estoqueDepois < 0) {
                    throw new RuntimeException(
                        "Estoque insuficiente para \"{$item['nome']}\". " .
                            "Disponível: {$estoqueAntes}, solicitado: {$item['qty']}."
                    );
                }

                $this->db->execute("
                    INSERT INTO venda_itens
                        (venda_id, produto_id, produto_nome, produto_codigo,
                         unidade_sigla, quantidade, preco_unitario, preco_custo, subtotal)
                    VALUES
                        (:venda_id, :produto_id, :produto_nome, :produto_codigo,
                         :unidade_sigla, :quantidade, :preco_unitario, :preco_custo, :subtotal)
                ", [
                    'venda_id'       => $vendaId,
                    'produto_id'     => $item['id'],
                    'produto_nome'   => $item['nome'],
                    'produto_codigo' => $item['codigo']        ?? 'SEM_COD',
                    'unidade_sigla'  => $item['unidade_sigla'] ?? 'UN',
                    'quantidade'     => $item['qty'],
                    'preco_unitario' => $item['preco'],
                    'preco_custo'    => $item['preco_custo']   ?? 0.00,
                    'subtotal'       => round($item['qty'] * $item['preco'], 2),
                ]);

                $this->db->execute("
                    INSERT INTO movimentacoes_estoque
                        (produto_id, tipo, motivo, quantidade, estoque_antes, estoque_depois, usuario_id)
                    VALUES
                        (:pid, 'SAIDA', 'VENDA', :qty, :antes, :depois, :uid)
                ", [
                    'pid'    => $item['id'],
                    'qty'    => $item['qty'],
                    'antes'  => $estoqueAntes,
                    'depois' => $estoqueDepois,
                    'uid'    => $dados['usuario_id'] ?? null,
                ]);

                $this->db->execute(
                    "UPDATE produtos SET estoque_atual = :est WHERE id = :id",
                    ['est' => $estoqueDepois, 'id' => $item['id']]
                );
            }

            // 4. Pagamentos
            foreach ($pagamentos as $pgto) {
                $this->db->execute("
                    INSERT INTO venda_pagamentos (venda_id, forma, valor, troco)
                    VALUES (:venda_id, :forma, :valor, :troco)
                ", [
                    'venda_id' => $vendaId,
                    'forma'    => $pgto['forma'],
                    'valor'    => $pgto['valor'],
                    'troco'    => $pgto['troco'] ?? 0.00,
                ]);
            }

            // 5. Atualiza totais do caixa
            $dinheiro = array_reduce($pagamentos, function (float $c, array $p): float {
                return $p['forma'] === 'dinheiro'
                    ? $c + ($p['valor'] - ($p['troco'] ?? 0.00))
                    : $c;
            }, 0.00);

            $this->db->execute("
                UPDATE caixas
                SET total_vendas   = total_vendas   + :total,
                    saldo_esperado = saldo_esperado + :dinheiro
                WHERE id = :caixa_id
            ", [
                'total'    => $dados['total'],
                'dinheiro' => $dinheiro,
                'caixa_id' => $dados['caixa_id'],
            ]);

            return $vendaId;
        });
    }

    public function buscarPorId(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT v.*, u.nome AS usuario_nome
             FROM vendas v
             LEFT JOIN usuarios u ON u.id = v.usuario_id
             WHERE v.id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public function itensDaVenda(int $vendaId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM venda_itens WHERE venda_id = :venda_id ORDER BY id",
            ['venda_id' => $vendaId]
        );
    }

    public function pagamentosDaVenda(int $vendaId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM venda_pagamentos WHERE venda_id = :venda_id ORDER BY id",
            ['venda_id' => $vendaId]
        );
    }
}
