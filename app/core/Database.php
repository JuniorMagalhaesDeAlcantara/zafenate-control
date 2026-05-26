<?php

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;
use Throwable;

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    // ----------------------------------------------------------------
    // Singleton
    // ----------------------------------------------------------------

    private function __construct()
    {
        $config = require dirname(__DIR__, 2) . '/config/database.php';

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $e) {
            // Nunca exponha credenciais em produção — logue e relance genérico
            error_log('[Database] Falha na conexão: ' . $e->getMessage());
            throw new RuntimeException('Não foi possível conectar ao banco de dados.');
        }
    }

    /**
     * Retorna a instância única (cria na primeira chamada).
     */
    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    // Impede clone e unserialize do singleton
    private function __clone() {}
    public function __wakeup(): void
    {
        throw new RuntimeException('Não é permitido deserializar o Database.');
    }

    // ----------------------------------------------------------------
    // Consultas
    // ----------------------------------------------------------------

    /**
     * Retorna todas as linhas como array de arrays associativos.
     * Retorna [] se não houver resultados.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Retorna uma única linha ou null se não encontrar.
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->prepare($sql, $params);
        $row  = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Executa INSERT, UPDATE, DELETE.
     * Retorna true se ao menos uma linha foi afetada.
     */
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->prepare($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Retorna o ID gerado pelo último INSERT.
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    // ----------------------------------------------------------------
    // Transações
    // ----------------------------------------------------------------

    /**
     * Executa um callable dentro de uma transação.
     * Faz commit se tudo correr bem, rollback em qualquer exceção.
     *
     * Uso:
     *   $id = $this->db->transaction(function () {
     *       // operações...
     *       return $id;
     *   });
     */
    // Métodos avulsos — usados por código legado (prefira transaction(callable))
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }
    public function commit(): bool
    {
        return $this->pdo->commit();
    }
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $callback();
            $this->pdo->commit();
            return $result;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // ----------------------------------------------------------------
    // Helper interno
    // ----------------------------------------------------------------

    /**
     * Prepara e executa um statement com bind automático.
     * Suporta parâmetros nomeados (:param) e posicionais (?).
     */
    private function prepare(string $sql, array $params): \PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);

            foreach ($params as $key => $value) {
                // Chave numérica (posicional) → índice base 1
                // Chave string (nomeada)      → :chave
                $param = is_int($key) ? $key + 1 : ':' . ltrim($key, ':');

                $type = match (true) {
                    is_int($value)  => PDO::PARAM_INT,
                    is_bool($value) => PDO::PARAM_BOOL,
                    is_null($value) => PDO::PARAM_NULL,
                    default         => PDO::PARAM_STR,
                };

                $stmt->bindValue($param, $value, $type);
            }

            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            $msg = '[Database] Query error: ' . $e->getMessage() . ' | SQL: ' . $sql;
            error_log($msg);

            // Em desenvolvimento, relança com mensagem completa para depuração.
            // Em produção, troque APP_DEBUG por false no config.
            if (defined('APP_DEBUG') && APP_DEBUG) {
                throw new RuntimeException($msg, 0, $e);
            }

            throw new RuntimeException('Erro ao executar operação no banco de dados.', 0, $e);
        }
    }

    // ----------------------------------------------------------------
    // Utilitários extras (úteis nos Models futuros)
    // ----------------------------------------------------------------

    /**
     * Retorna uma única coluna da primeira linha.
     * Ex: $total = $db->fetchScalar("SELECT COUNT(*) FROM produtos");
     */
    public function fetchScalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->prepare($sql, $params);
        $row  = $stmt->fetch(PDO::FETCH_NUM);
        return $row !== false ? $row[0] : null;
    }

    /**
     * Retorna uma coluna inteira como array simples.
     * Ex: $ids = $db->fetchColumn("SELECT id FROM produtos WHERE ativo = 1");
     */
    public function fetchColumn(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Expõe o PDO bruto para casos excepcionais (uso cuidadoso).
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
