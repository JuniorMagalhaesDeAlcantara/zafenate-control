<?php

namespace App\Models;

use App\Core\Database;

class Usuario
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Procura um usuário pelo e-mail
     * Retorna o array do usuário ou null se não encontrar
     */
    public function buscarPorEmail(string $email): ?array
    {
        $sql = "
            SELECT * FROM usuarios 
            WHERE email = :email 
              AND ativo = 1 
            LIMIT 1
        ";
        
        return $this->db->fetchOne($sql, ['email' => $email]) ?: null;
    }
}