<?php

namespace App\Models;

use App\Core\Database;

class Categoria
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ----------------------------------------------------------------
    // LEITURA
    // ----------------------------------------------------------------

    public function listar(array $filtros = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filtros['busca'])) {
            $where[] = 'c.nome LIKE :busca';
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }

        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $where[] = 'c.ativo = :ativo';
            $params['ativo'] = (int) $filtros['ativo'];
        }

        $sql = "
SELECT
c.*,
p.nome AS parent_nome,
(SELECT COUNT(*) FROM categorias f WHERE f.parent_id = c.id) AS qtd_filhas,
(SELECT COUNT(*) FROM produtos pr WHERE pr.categoria_id = c.id AND pr.ativo = 1) AS qtd_produtos
FROM categorias c
LEFT JOIN categorias p ON p.id = c.parent_id
WHERE " . implode(' AND ', $where) . "
ORDER BY COALESCE(p.nome, c.nome) ASC, c.nome ASC
";

        return $this->db->fetchAll($sql, $params);
    }

    public function buscarPorId(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT c.*, p.nome AS parent_nome
FROM categorias c
LEFT JOIN categorias p ON p.id = c.parent_id
WHERE c.id = :id LIMIT 1",
            ['id' => $id]
        ) ?: null;
    }

    /**
     * Retorna apenas categorias raiz (sem parent) — para o select de "Categoria Pai"
     */
    public function listarRaiz(): array
    {
        return $this->db->fetchAll(
            "SELECT id, nome FROM categorias WHERE parent_id IS NULL AND ativo = 1 ORDER BY nome ASC"
        );
    }

    /**
     * Todas as categorias ativas — para selects em formulários de produto
     */
    public function listarAtivas(): array
    {
        return $this->db->fetchAll(
            "SELECT c.id, c.nome, p.nome AS parent_nome
FROM categorias c
LEFT JOIN categorias p ON p.id = c.parent_id
WHERE c.ativo = 1
ORDER BY COALESCE(p.nome, c.nome) ASC, c.nome ASC"
        );
    }

    public function totais(): array
    {
        return $this->db->fetchOne("
SELECT
COUNT(*) AS total,
SUM(ativo = 1) AS ativas,
SUM(ativo = 0) AS inativas,
SUM(parent_id IS NULL AND ativo = 1) AS raiz,
SUM(parent_id IS NOT NULL AND ativo = 1) AS subcategorias
FROM categorias
") ?? [];
    }

    // ----------------------------------------------------------------
    // ESCRITA
    // ----------------------------------------------------------------

    public function criar(array $dados): int
    {
        $dados = $this->filtrar($dados);
        $this->validar($dados);

        $cols = implode(', ', array_keys($dados));
        $holds = ':' . implode(', :', array_keys($dados));

        $this->db->execute("INSERT INTO categorias ({$cols}) VALUES ({$holds})", $dados);
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $dados = $this->filtrar($dados);
        $this->validar($dados, $id);

        // Impede categoria ser pai dela mesma
        if (!empty($dados['parent_id']) && (int)$dados['parent_id'] === $id) {
            throw new \InvalidArgumentException('Uma categoria não pode ser pai de si mesma.');
        }

        $sets = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($dados)));
        $dados['id'] = $id;

        return $this->db->execute("UPDATE categorias SET {$sets} WHERE id = :id", $dados);
    }

    public function alternarStatus(int $id): bool
    {
        return $this->db->execute(
            "UPDATE categorias SET ativo = NOT ativo WHERE id = :id",
            ['id' => $id]
        );
    }

    // ----------------------------------------------------------------
    // HELPERS PRIVADOS
    // ----------------------------------------------------------------

    private function filtrar(array $dados): array
    {
        $allowed = ['parent_id', 'nome', 'descricao', 'ativo'];
        return array_intersect_key($dados, array_flip($allowed));
    }

    private function validar(array $dados, ?int $id = null): void
    {
        if (empty($dados['nome'])) {
            throw new \InvalidArgumentException('Nome da categoria é obrigatório.');
        }

        $sql = "SELECT id FROM categorias WHERE nome = :nome" . ($id ? " AND id != :id" : "");
        $params = ['nome' => $dados['nome']];
        if ($id) $params['id'] = $id;

        if ($this->db->fetchOne($sql, $params)) {
            throw new \InvalidArgumentException("Já existe uma categoria com o nome '{$dados['nome']}'.");
        }
    }
}
