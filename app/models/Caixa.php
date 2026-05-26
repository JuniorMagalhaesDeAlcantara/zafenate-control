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
            'saldo_es' => $saldoAbertura
        ]);
    }

    /**
     * Processa o fechamento computando o esperado e atualizando o status
     */
    public function fechar(int $caixaId, string $saldoInformado, ?string $observacao): bool
    {
        // 1. Busca os dados atuais do caixa aberto para fazer a matemática
        $sqlCaixa = "SELECT * FROM caixas WHERE id = :id_caixa AND status = 'aberto' LIMIT 1";
        $caixa = $this->db->fetchOne($sqlCaixa, ['id_caixa' => $caixaId]);

        if (!$caixa) {
            throw new \InvalidArgumentException("Caixa não encontrado ou já encerrado.");
        }

        // 2. Faz a conta matemática usando os valores atuais do banco
        // (Convertendo para float na hora do cálculo para o PHP não se perder)
        $abertura    = (float)$caixa['saldo_abertura'];
        $vendas      = (float)($caixa['total_vendas'] ?? 0);
        $suprimentos = (float)($caixa['total_suprimentos'] ?? 0);
        $sangrias    = (float)($caixa['total_sangrias'] ?? 0);

        $saldoEsperado = ($abertura + $suprimentos + $vendas) - $sangrias;

        // 3. Monta o UPDATE com nomes de parâmetros 100% ÚNICOS
        $sql = "UPDATE caixas SET 
                    status = 'fechado',
                    saldo_esperado = :esperado,
                    saldo_informado = :informado,
                    observacao_fechamento = :obs,
                    fechado_em = NOW()
                WHERE id = :id";

        // Executa passando as chaves certinhas para a sua Database.php
        return $this->db->execute($sql, [
            'esperado'  => (string)$saldoEsperado, // Mandando como string pro formato default do PDO
            'informado' => $saldoInformado,
            'obs'       => $observacao,
            'id'        => $caixaId
        ]);
    }

    
}