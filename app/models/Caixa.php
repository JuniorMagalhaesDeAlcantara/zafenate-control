<?php

namespace App\Models;

use App\Core\Database;

class Caixa
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Busca o caixa aberto atual de um usuário
     */
    public function buscarAbertoPorUsuario(int $usuarioId): ?array
    {
        $sql = "SELECT * FROM caixas WHERE usuario_id = :uid AND status = 'aberto' LIMIT 1";
        return $this->db->fetchOne($sql, ['uid' => $usuarioId]);
    }

    /**
     * Registra a abertura de um novo caixa
     */
    public function abrir(int $usuarioId, string $saldoAbertura): bool
    {
        $sql = "INSERT INTO caixas (usuario_id, status, saldo_abertura, saldo_esperado)
                VALUES (:uid, 'aberto', :saldo_ab, :saldo_es)";

        return $this->db->execute($sql, [
            'uid'      => $usuarioId,
            'saldo_ab' => $saldoAbertura,
            'saldo_es' => $saldoAbertura,
        ]);
    }

    /**
     * Processa o fechamento computando o esperado e atualizando o status
     */
    public function fechar(int $caixaId, string $saldoInformado, ?string $observacao): bool
    {
        $sqlCaixa = "SELECT * FROM caixas WHERE id = :id_caixa AND status = 'aberto' LIMIT 1";
        $caixa    = $this->db->fetchOne($sqlCaixa, ['id_caixa' => $caixaId]);

        if (!$caixa) {
            throw new \InvalidArgumentException("Caixa não encontrado ou já encerrado.");
        }

        $abertura      = (float) $caixa['saldo_abertura'];
        $vendas        = (float) ($caixa['total_vendas']      ?? 0);
        $suprimentos   = (float) ($caixa['total_suprimentos'] ?? 0);
        $sangrias      = (float) ($caixa['total_sangrias']    ?? 0);

        $saldoEsperado = ($abertura + $suprimentos + $vendas) - $sangrias;

        $sql = "UPDATE caixas SET
                    status                = 'fechado',
                    saldo_esperado        = :esperado,
                    saldo_informado       = :informado,
                    observacao_fechamento = :obs,
                    fechado_em            = NOW()
                WHERE id = :id";

        return $this->db->execute($sql, [
            'esperado'  => (string) $saldoEsperado,
            'informado' => $saldoInformado,
            'obs'       => $observacao,
            'id'        => $caixaId,
        ]);
    }

    /**
     * Registra uma sangria no caixa aberto.
     *
     * - Insere em caixa_movimentos (tipo = 'sangria')
     * - Decrementa total_sangrias no caixa
     * - Decrementa saldo_esperado (dinheiro físico sai da gaveta)
     *
     * @throws \InvalidArgumentException  Caixa não encontrado ou fechado
     * @throws \InvalidArgumentException  Valor inválido
     * @throws \RuntimeException          Saldo esperado ficaria negativo
     */
    public function registrarSangria(int $caixaId, float $valor, string $motivo, int $usuarioId): int
    {
        if ($valor <= 0) {
            throw new \InvalidArgumentException("O valor da sangria deve ser maior que zero.");
        }

        return $this->db->transaction(function () use ($caixaId, $valor, $motivo, $usuarioId) {

            // 1. Trava o registro do caixa para evitar race condition
            $caixa = $this->db->fetchOne(
                "SELECT id, status, saldo_esperado, total_sangrias
                 FROM caixas
                 WHERE id = :id AND status = 'aberto'
                 LIMIT 1 FOR UPDATE",
                ['id' => $caixaId]
            );

            if (!$caixa) {
                throw new \InvalidArgumentException("Caixa não encontrado ou já encerrado.");
            }

            $saldoAtual = (float) $caixa['saldo_esperado'];

            if ($valor > $saldoAtual) {
                throw new \RuntimeException(sprintf(
                    "Valor da sangria (R$ %.2f) superior ao saldo disponível na gaveta (R$ %.2f).",
                    $valor,
                    $saldoAtual
                ));
            }

            // 2. Insere o movimento
            $this->db->execute(
                "INSERT INTO caixa_movimentos (caixa_id, usuario_id, tipo, valor, observacao)
                 VALUES (:caixa_id, :usuario_id, 'sangria', :valor, :obs)",
                [
                    'caixa_id'   => $caixaId,
                    'usuario_id' => $usuarioId,
                    'valor'      => $valor,
                    'obs'        => $motivo,
                ]
            );

            $movimentoId = (int) $this->db->lastInsertId();

            // 3. Atualiza totais do caixa
            $this->db->execute(
                "UPDATE caixas
                 SET total_sangrias  = total_sangrias  + :valor,
                     saldo_esperado  = saldo_esperado  - :valor2
                 WHERE id = :id",
                [
                    'valor'  => $valor,
                    'valor2' => $valor,
                    'id'     => $caixaId,
                ]
            );

            return $movimentoId;
        });
    }

    /**
     * Lista todos os movimentos de um caixa, mais recentes primeiro
     */
    public function movimentos(int $caixaId): array
    {
        return $this->db->fetchAll(
            "SELECT cm.*, u.nome AS usuario_nome
             FROM caixa_movimentos cm
             LEFT JOIN usuarios u ON u.id = cm.usuario_id
             WHERE cm.caixa_id = :cid
             ORDER BY cm.criado_em DESC",
            ['cid' => $caixaId]
        );
    }
}
