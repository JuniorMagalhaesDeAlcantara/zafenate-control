<?php

namespace App\Models;

use App\Core\Database;

class Produto
{
    private Database $db;

    // Campos permitidos para insert/update (whitelist)
    private array $fillable = [
        'categoria_id', 'unidade_id', 'codigo', 'codigo_barras',
        'nome', 'descricao', 'imagem', 'preco_custo', 'preco_venda',
        'estoque_minimo', 'estoque_maximo', 'ativo'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ----------------------------------------------------------------
    // LEITURA
    // ----------------------------------------------------------------

    /**
     * Lista todos os produtos com join de categoria e unidade.
     * Suporta busca por nome, código ou código de barras.
     */
    public function listar(array $filtros = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['busca'])) {
            $where[]        = '(p.nome LIKE :busca OR p.codigo LIKE :busca OR p.codigo_barras LIKE :busca)';
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }

        if (isset($filtros['categoria_id']) && $filtros['categoria_id'] !== '') {
            $where[]              = 'p.categoria_id = :categoria_id';
            $params['categoria_id'] = (int) $filtros['categoria_id'];
        }

        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $where[]       = 'p.ativo = :ativo';
            $params['ativo'] = (int) $filtros['ativo'];
        }

        if (!empty($filtros['alerta_estoque'])) {
            $where[] = 'p.estoque_atual <= p.estoque_minimo AND p.estoque_minimo > 0';
        }

        $whereStr = implode(' AND ', $where);
        $order    = $filtros['order'] ?? 'p.nome ASC';

        $sql = "
            SELECT
                p.*,
                c.nome        AS categoria_nome,
                cp.nome       AS subcategoria_pai,
                u.sigla       AS unidade_sigla,
                u.nome        AS unidade_nome,
                CASE WHEN p.estoque_minimo > 0 AND p.estoque_atual <= p.estoque_minimo
                     THEN 1 ELSE 0 END AS alerta_estoque
            FROM produtos p
            LEFT JOIN categorias c  ON c.id = p.categoria_id
            LEFT JOIN categorias cp ON cp.id = c.parent_id
            LEFT JOIN unidades u    ON u.id = p.unidade_id
            WHERE {$whereStr}
            ORDER BY {$order}
        ";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Busca um produto pelo ID com todos os dados relacionados.
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                p.*,
                c.nome   AS categoria_nome,
                u.sigla  AS unidade_sigla,
                u.nome   AS unidade_nome
            FROM produtos p
            LEFT JOIN categorias c ON c.id = p.categoria_id
            LEFT JOIN unidades u   ON u.id = p.unidade_id
            WHERE p.id = :id
            LIMIT 1
        ";

        return $this->db->fetchOne($sql, ['id' => $id]) ?: null;
    }

    /**
     * Busca por código de barras — usado pelo leitor físico ou câmera.
     */
    public function buscarPorCodigoBarras(string $codigo): ?array
    {
        $sql = "
            SELECT p.*, u.sigla AS unidade_sigla
            FROM produtos p
            LEFT JOIN unidades u ON u.id = p.unidade_id
            WHERE p.codigo_barras = :codigo AND p.ativo = 1
            LIMIT 1
        ";

        return $this->db->fetchOne($sql, ['codigo' => $codigo]) ?: null;
    }

    /**
     * Produtos com estoque abaixo do mínimo (painel de alertas).
     */
    public function comEstoqueBaixo(): array
    {
        $sql = "
            SELECT p.*, u.sigla AS unidade_sigla
            FROM produtos p
            LEFT JOIN unidades u ON u.id = p.unidade_id
            WHERE p.ativo = 1
              AND p.estoque_minimo > 0
              AND p.estoque_atual <= p.estoque_minimo
            ORDER BY (p.estoque_atual / p.estoque_minimo) ASC
        ";

        return $this->db->fetchAll($sql);
    }

    /**
     * Contagem total de produtos (ativo/inativo/alertas).
     */
    public function totais(): array
    {
        $sql = "
            SELECT
                COUNT(*)                                                          AS total,
                SUM(ativo = 1)                                                    AS ativos,
                SUM(ativo = 0)                                                    AS inativos,
                SUM(ativo = 1 AND estoque_minimo > 0 AND estoque_atual <= estoque_minimo) AS alerta_estoque
            FROM produtos
        ";

        return $this->db->fetchOne($sql) ?? [];
    }

    // ----------------------------------------------------------------
    // ESCRITA
    // ----------------------------------------------------------------

    /**
     * Cria um novo produto.
     * Retorna o ID inserido ou lança exceção em caso de erro.
     */
    public function criar(array $dados): int
    {
        $dados = $this->filtrarCampos($dados);
        $this->validar($dados);

        $campos    = implode(', ', array_keys($dados));
        $placehold = ':' . implode(', :', array_keys($dados));

        $sql = "INSERT INTO produtos ({$campos}) VALUES ({$placehold})";

        $this->db->execute($sql, $dados);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Atualiza um produto existente.
     */
    public function atualizar(int $id, array $dados): bool
    {
        $dados = $this->filtrarCampos($dados);
        $this->validar($dados, $id);

        $sets = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($dados)));
        $dados['id'] = $id;

        $sql = "UPDATE produtos SET {$sets} WHERE id = :id";

        return $this->db->execute($sql, $dados);
    }

    /**
     * Ativa ou desativa (soft delete — nunca apaga do banco).
     */
    public function alternarStatus(int $id): bool
    {
        $sql = "UPDATE produtos SET ativo = NOT ativo WHERE id = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    /**
     * Atualiza o estoque_atual diretamente (usado pela MovimentacaoEstoque).
     * Nunca chame isso sem registrar a movimentação antes!
     */
    public function atualizarEstoque(int $id, float $novoEstoque): bool
    {
        $sql = "UPDATE produtos SET estoque_atual = :estoque, preco_custo = COALESCE(:preco, preco_custo) WHERE id = :id";
        // Veja MovimentacaoEstoque::registrar() para o fluxo completo
        return $this->db->execute($sql, [
            'estoque' => $novoEstoque,
            'preco'   => null,
            'id'      => $id,
        ]);
    }

    /**
     * Atualiza estoque e preço de custo juntos (chamado na entrada de compra).
     */
    public function atualizarEstoqueECusto(int $id, float $novoEstoque, float $precoCusto): bool
    {
        $sql = "UPDATE produtos SET estoque_atual = :estoque, preco_custo = :preco WHERE id = :id";
        return $this->db->execute($sql, [
            'estoque' => $novoEstoque,
            'preco'   => $precoCusto,
            'id'      => $id,
        ]);
    }

    // ----------------------------------------------------------------
    // GERAÇÃO DE CÓDIGO INTERNO
    // ----------------------------------------------------------------

    /**
     * Gera próximo código sequencial no formato PRD-000001.
     */
    public function gerarCodigo(): string
    {
        $sql    = "SELECT MAX(CAST(SUBSTRING(codigo, 5) AS UNSIGNED)) AS ultimo FROM produtos WHERE codigo REGEXP '^PRD-[0-9]+$'";
        $result = $this->db->fetchOne($sql);
        $proximo = ($result['ultimo'] ?? 0) + 1;
        return 'PRD-' . str_pad($proximo, 6, '0', STR_PAD_LEFT);
    }

    // ----------------------------------------------------------------
    // HELPERS PRIVADOS
    // ----------------------------------------------------------------

    private function filtrarCampos(array $dados): array
    {
        return array_intersect_key($dados, array_flip($this->fillable));
    }

    private function validar(array $dados, ?int $id = null): void
    {
        if (empty($dados['nome'])) {
            throw new \InvalidArgumentException('Nome do produto é obrigatório.');
        }

        if (empty($dados['codigo'])) {
            throw new \InvalidArgumentException('Código do produto é obrigatório.');
        }

        // Verifica duplicidade de código
        $sql    = "SELECT id FROM produtos WHERE codigo = :codigo" . ($id ? " AND id != :id" : "");
        $params = ['codigo' => $dados['codigo']];
        if ($id) $params['id'] = $id;

        if ($this->db->fetchOne($sql, $params)) {
            throw new \InvalidArgumentException("Já existe um produto com o código '{$dados['codigo']}'.");
        }

        // Verifica duplicidade de código de barras (se informado)
        if (!empty($dados['codigo_barras'])) {
            $sql2    = "SELECT id FROM produtos WHERE codigo_barras = :cb" . ($id ? " AND id != :id" : "");
            $params2 = ['cb' => $dados['codigo_barras']];
            if ($id) $params2['id'] = $id;

            if ($this->db->fetchOne($sql2, $params2)) {
                throw new \InvalidArgumentException("Já existe um produto com este código de barras.");
            }
        }
    }
}