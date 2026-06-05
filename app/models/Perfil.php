<?php

namespace App\Models;

use App\Core\Database;

class Perfil
{
    private Database $db;

    // Módulos do sistema com label amigável
    public const MODULOS = [
        'dashboard'     => 'Dashboard',
        'vendas'        => 'Vendas / PDV',
        'compras'       => 'Compras',
        'financeiro'    => 'Financeiro',
        'estoque'       => 'Estoque',
        'clientes'      => 'Clientes',
        'fornecedores'  => 'Fornecedores',
        'relatorios'    => 'Relatórios',
        'configuracoes' => 'Configurações',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function listar(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM perfis ORDER BY id ASC"
        );
    }

    public function buscarPorId(int $id): ?array
    {
        $perfil = $this->db->fetchOne(
            "SELECT * FROM perfis WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
        if ($perfil) {
            $perfil['permissoes'] = json_decode($perfil['permissoes'] ?? '{}', true) ?: [];
        }
        return $perfil ?: null;
    }

    public function buscarPorSlug(string $slug): ?array
    {
        $perfil = $this->db->fetchOne(
            "SELECT * FROM perfis WHERE slug = :slug LIMIT 1",
            ['slug' => $slug]
        );
        if ($perfil) {
            $perfil['permissoes'] = json_decode($perfil['permissoes'] ?? '{}', true) ?: [];
        }
        return $perfil ?: null;
    }

    public function salvar(int $id, string $nome, array $permissoes): void
    {
        $this->db->execute(
            "UPDATE perfis SET nome = :nome, permissoes = :perms WHERE id = :id",
            [
                'nome'  => $nome,
                'perms' => json_encode($permissoes),
                'id'    => $id,
            ]
        );
    }

    public function criar(string $nome, string $slug, array $permissoes): int
    {
        $this->db->execute(
            "INSERT INTO perfis (nome, slug, permissoes) VALUES (:nome, :slug, :perms)",
            [
                'nome'  => $nome,
                'slug'  => $slug,
                'perms' => json_encode($permissoes),
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function toggleAtivo(int $id): void
    {
        $this->db->execute(
            "UPDATE perfis SET ativo = IF(ativo = 1, 0, 1) WHERE id = :id",
            ['id' => $id]
        );
    }
}
