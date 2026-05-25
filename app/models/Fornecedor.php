<?php

namespace App\Models;

use App\Core\Database;

class Fornecedor
{
    private Database $db;

    private array $fillable = [
        'razao_social', 'nome_fantasia', 'cnpj_cpf', 'ie',
        'email', 'telefone', 'celular', 'contato',
        'cep', 'logradouro', 'numero', 'complemento',
        'bairro', 'cidade', 'uf',
        'prazo_pagamento', 'observacoes', 'ativo'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function listar(array $filtros = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['busca'])) {
            $where[]         = '(razao_social LIKE :busca OR nome_fantasia LIKE :busca OR cnpj_cpf LIKE :busca)';
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }

        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $where[]       = 'ativo = :ativo';
            $params['ativo'] = (int) $filtros['ativo'];
        }

        $whereStr = implode(' AND ', $where);

        $sql = "SELECT * FROM fornecedores WHERE {$whereStr} ORDER BY razao_social ASC";

        return $this->db->fetchAll($sql, $params);
    }

    public function buscarPorId(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM fornecedores WHERE id = :id LIMIT 1",
            ['id' => $id]
        ) ?: null;
    }

    public function criar(array $dados): int
    {
        $dados = $this->filtrarCampos($dados);
        $this->validar($dados);

        $campos    = implode(', ', array_keys($dados));
        $placehold = ':' . implode(', :', array_keys($dados));

        $this->db->execute("INSERT INTO fornecedores ({$campos}) VALUES ({$placehold})", $dados);
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $dados = $this->filtrarCampos($dados);
        $this->validar($dados, $id);

        $sets      = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($dados)));
        $dados['id'] = $id;

        return $this->db->execute("UPDATE fornecedores SET {$sets} WHERE id = :id", $dados);
    }

    public function alternarStatus(int $id): bool
    {
        return $this->db->execute(
            "UPDATE fornecedores SET ativo = NOT ativo WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Lista simplificada para selects/dropdowns.
     */
    public function paraSelect(): array
    {
        return $this->db->fetchAll(
            "SELECT id, razao_social, nome_fantasia FROM fornecedores WHERE ativo = 1 ORDER BY razao_social"
        );
    }

    private function filtrarCampos(array $dados): array
    {
        return array_intersect_key($dados, array_flip($this->fillable));
    }

    private function validar(array $dados, ?int $id = null): void
    {
        if (empty($dados['razao_social'])) {
            throw new \InvalidArgumentException('Razão Social é obrigatória.');
        }

        // CNPJ/CPF único (se informado)
        if (!empty($dados['cnpj_cpf'])) {
            $sql    = "SELECT id FROM fornecedores WHERE cnpj_cpf = :cnpj" . ($id ? " AND id != :id" : "");
            $params = ['cnpj' => $dados['cnpj_cpf']];
            if ($id) $params['id'] = $id;

            if ($this->db->fetchOne($sql, $params)) {
                throw new \InvalidArgumentException("Já existe um fornecedor com este CNPJ/CPF.");
            }
        }
    }
}