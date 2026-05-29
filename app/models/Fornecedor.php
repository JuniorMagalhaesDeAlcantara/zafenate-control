<?php

namespace App\Models;

use App\Core\Database;

class Fornecedor
{
    private Database $db;

    private array $fillable = [
        'tipo_pessoa',
        'razao_social',
        'nome_fantasia',
        'cnpj_cpf',
        'ie',
        'email',
        'telefone',
        'celular',
        'whatsapp',
        'site',
        'contato',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'prazo_pagamento',
        'forma_pagamento',
        'limite_credito',
        'banco',
        'agencia',
        'conta',
        'chave_pix',
        'obs_financeiras',
        'prazo_entrega',
        'obs_internas',
        'avaliacao_prazo',
        'avaliacao_qualidade',
        'avaliacao_atendimento',
        'observacoes',
        'ativo',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ----------------------------------------------------------------
    // LEITURA
    // ----------------------------------------------------------------

    public function listar(array $filtros = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['busca'])) {
            $where[]          = '(f.razao_social LIKE :busca OR f.nome_fantasia LIKE :busca OR f.cnpj_cpf LIKE :busca OR f.telefone LIKE :busca OR f.celular LIKE :busca)';
            $params['busca']  = '%' . $filtros['busca'] . '%';
        }

        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $where[]         = 'f.ativo = :ativo';
            $params['ativo'] = (int) $filtros['ativo'];
        }

        if (!empty($filtros['tipo_pessoa'])) {
            $where[]              = 'f.tipo_pessoa = :tipo';
            $params['tipo']       = $filtros['tipo_pessoa'];
        }

        $whereStr = implode(' AND ', $where);

        $sql = "
            SELECT
                f.*,
                -- Histórico de compras via movimentações de estoque
                COUNT(DISTINCT me.id)          AS total_compras,
                MAX(me.criado_em)               AS ultima_compra,
                COALESCE(SUM(me.quantidade * me.preco_custo_unitario), 0) AS valor_total_compras
            FROM fornecedores f
            LEFT JOIN movimentacoes_estoque me
                ON me.fornecedor_id = f.id AND me.motivo = 'COMPRA'
            WHERE {$whereStr}
            GROUP BY f.id
            ORDER BY f.razao_social ASC
        ";

        return $this->db->fetchAll($sql, $params);
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                f.*,
                COUNT(DISTINCT me.id)          AS total_compras,
                MAX(me.criado_em)               AS ultima_compra,
                COALESCE(SUM(me.quantidade * me.preco_custo_unitario), 0) AS valor_total_compras
            FROM fornecedores f
            LEFT JOIN movimentacoes_estoque me
                ON me.fornecedor_id = f.id AND me.motivo = 'COMPRA'
            WHERE f.id = :id
            GROUP BY f.id
            LIMIT 1
        ";

        return $this->db->fetchOne($sql, ['id' => $id]) ?: null;
    }

    public function totais(): array
    {
        $sql = "
            SELECT
                COUNT(*)           AS total,
                SUM(ativo = 1)     AS ativos,
                SUM(ativo = 0)     AS inativos,
                SUM(tipo_pessoa = 'juridica') AS pj,
                SUM(tipo_pessoa = 'fisica')   AS pf
            FROM fornecedores
        ";
        return $this->db->fetchOne($sql) ?? [];
    }

    /** Histórico de compras do fornecedor (últimas movimentações) */
    public function historicoCompras(int $id, int $limite = 20): array
    {
        $sql = "
            SELECT
                me.*,
                p.nome AS produto_nome,
                p.codigo AS produto_codigo
            FROM movimentacoes_estoque me
            JOIN produtos p ON p.id = me.produto_id
            WHERE me.fornecedor_id = :id AND me.motivo = 'COMPRA'
            ORDER BY me.criado_em DESC
            LIMIT :limite
        ";
        return $this->db->fetchAll($sql, ['id' => $id, 'limite' => $limite]);
    }

    /** Para selects de formulário (PDV, movimentações) */
    public function listarSelect(): array
    {
        return $this->db->fetchAll(
            "SELECT id, razao_social, nome_fantasia FROM fornecedores WHERE ativo = 1 ORDER BY razao_social ASC"
        );
    }

    // ----------------------------------------------------------------
    // ESCRITA
    // ----------------------------------------------------------------

    public function criar(array $dados): int
    {
        $dados = $this->filtrarCampos($dados);
        $this->validar($dados);

        $cols  = implode(', ', array_keys($dados));
        $binds = ':' . implode(', :', array_keys($dados));

        $this->db->execute("INSERT INTO fornecedores ({$cols}) VALUES ({$binds})", $dados);
        return $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $dados = $this->filtrarCampos($dados);
        $this->validar($dados, $id);

        $sets        = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($dados)));
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

    // ----------------------------------------------------------------
    // HELPERS PRIVADOS
    // ----------------------------------------------------------------

    private function filtrarCampos(array $dados): array
    {
        // Normaliza avaliações (1-5 ou null)
        foreach (['avaliacao_prazo', 'avaliacao_qualidade', 'avaliacao_atendimento'] as $campo) {
            if (isset($dados[$campo])) {
                $v = (int) $dados[$campo];
                $dados[$campo] = ($v >= 1 && $v <= 5) ? $v : null;
            }
        }

        // Campos numéricos opcionais
        foreach (['limite_credito', 'prazo_pagamento', 'prazo_entrega'] as $campo) {
            if (isset($dados[$campo]) && $dados[$campo] === '') {
                $dados[$campo] = null;
            }
        }

        return array_intersect_key($dados, array_flip($this->fillable));
    }

    private function validar(array $dados, ?int $id = null): void
    {
        if (empty($dados['razao_social'])) {
            throw new \InvalidArgumentException('Razão Social é obrigatória.');
        }

        if (!empty($dados['cnpj_cpf'])) {
            $sql    = "SELECT id FROM fornecedores WHERE cnpj_cpf = :doc" . ($id ? " AND id != :id" : "");
            $params = ['doc' => $dados['cnpj_cpf']];
            if ($id) $params['id'] = $id;

            if ($this->db->fetchOne($sql, $params)) {
                throw new \InvalidArgumentException('Já existe um fornecedor com este CPF/CNPJ.');
            }
        }
    }
}
