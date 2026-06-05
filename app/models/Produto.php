<?php

namespace App\Models;

use App\Core\Database;

class Produto
{
    private Database $db;

    private array $fillable = [
        'categoria_id',
        'unidade_id',
        'codigo',
        'codigo_barras',
        'tipo',            // 'produto' | 'servico'
        'nome',
        'descricao',
        'imagem',
        'preco_custo',
        'preco_venda',
        'estoque_atual',   // ignorado no update; para serviços sempre NULL
        'estoque_minimo',
        'estoque_maximo',
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
            $where[]              = '(p.nome LIKE :busca_nome OR p.codigo LIKE :busca_codigo OR p.codigo_barras LIKE :busca_barras)';
            $termo                = '%' . $filtros['busca'] . '%';
            $params['busca_nome']   = $termo;
            $params['busca_codigo'] = $termo;
            $params['busca_barras'] = $termo;
        }

        if (isset($filtros['categoria_id']) && $filtros['categoria_id'] !== '') {
            $where[]                = 'p.categoria_id = :categoria_id';
            $params['categoria_id'] = (int) $filtros['categoria_id'];
        }

        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $where[]       = 'p.ativo = :ativo';
            $params['ativo'] = (int) $filtros['ativo'];
        }

        if (!empty($filtros['tipo'])) {
            $where[]       = 'p.tipo = :tipo';
            $params['tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['alerta_estoque'])) {
            // Serviços nunca entram em alerta de estoque
            $where[] = "p.tipo = 'produto' AND p.estoque_minimo > 0 AND p.estoque_atual <= p.estoque_minimo";
        }

        if (!empty($filtros['zerados'])) {
            $where[] = "p.tipo = 'produto' AND p.estoque_atual <= 0";
        }

        $whereStr = implode(' AND ', $where);
        $order    = $filtros['order'] ?? 'p.nome ASC';

        $sql = "
            SELECT
                p.*,
                c.nome   AS categoria_nome,
                cp.nome  AS subcategoria_pai,
                u.sigla  AS unidade_sigla,
                u.nome   AS unidade_nome,
                CASE
                    WHEN p.tipo = 'servico' THEN 0
                    WHEN p.estoque_minimo > 0 AND p.estoque_atual <= p.estoque_minimo THEN 1
                    ELSE 0
                END AS alerta_estoque
            FROM produtos p
            LEFT JOIN categorias c  ON c.id = p.categoria_id
            LEFT JOIN categorias cp ON cp.id = c.parent_id
            LEFT JOIN unidades u    ON u.id = p.unidade_id
            WHERE {$whereStr}
            ORDER BY {$order}
        ";

        return $this->db->fetchAll($sql, $params);
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                p.*,
                c.nome  AS categoria_nome,
                u.sigla AS unidade_sigla,
                u.nome  AS unidade_nome
            FROM produtos p
            LEFT JOIN categorias c ON c.id = p.categoria_id
            LEFT JOIN unidades u   ON u.id = p.unidade_id
            WHERE p.id = :id
            LIMIT 1
        ";

        return $this->db->fetchOne($sql, ['id' => $id]) ?: null;
    }

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

    public function comEstoqueBaixo(): array
    {
        $sql = "
            SELECT p.*, u.sigla AS unidade_sigla
            FROM produtos p
            LEFT JOIN unidades u ON u.id = p.unidade_id
            WHERE p.tipo = 'produto'
              AND p.ativo = 1
              AND p.estoque_minimo > 0
              AND p.estoque_atual <= p.estoque_minimo
            ORDER BY (p.estoque_atual / p.estoque_minimo) ASC
        ";

        return $this->db->fetchAll($sql);
    }

    public function totais(): array
    {
        $sql = "
            SELECT
                COUNT(*)                                                                                      AS total,
                SUM(ativo = 1)                                                                                AS ativos,
                SUM(ativo = 0)                                                                                AS inativos,
                SUM(tipo = 'produto')                                                                         AS total_produtos,
                SUM(tipo = 'servico')                                                                         AS total_servicos,
                SUM(tipo = 'produto' AND ativo = 1 AND estoque_minimo > 0 AND estoque_atual <= estoque_minimo) AS alerta_estoque,
                SUM(tipo = 'produto' AND ativo = 1 AND estoque_atual <= 0)                                    AS zerados
            FROM produtos
        ";

        return $this->db->fetchOne($sql) ?? [];
    }

    // ----------------------------------------------------------------
    // ESCRITA
    // ----------------------------------------------------------------

    public function criar(array $dados): int
    {
        $dados = $this->filtrarCampos($dados);
        $this->normalizarServico($dados);   // ← regra de negócio: serviço sem estoque
        $this->validar($dados);

        $campos    = implode(', ', array_keys($dados));
        $placehold = ':' . implode(', :', array_keys($dados));

        $this->db->execute(
            "INSERT INTO produtos ({$campos}) VALUES ({$placehold})",
            $dados
        );

        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $dados = $this->filtrarCampos($dados);

        // Estoque não pode ser editado pelo form — só por MovimentacaoEstoque
        unset($dados['estoque_atual']);

        $this->normalizarServico($dados);   // ← garante a regra mesmo no update
        $this->validar($dados, $id);

        $sets      = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($dados)));
        $dados['id'] = $id;

        return $this->db->execute(
            "UPDATE produtos SET {$sets} WHERE id = :id",
            $dados
        );
    }

    public function alternarStatus(int $id): bool
    {
        return $this->db->execute(
            "UPDATE produtos SET ativo = NOT ativo WHERE id = :id",
            ['id' => $id]
        );
    }

    public function atualizarEstoque(int $id, float $novoEstoque): bool
    {
        return $this->db->execute(
            "UPDATE produtos SET estoque_atual = :estoque WHERE id = :id",
            ['estoque' => $novoEstoque, 'id' => $id]
        );
    }

    public function atualizarEstoqueECusto(int $id, float $novoEstoque, float $precoCusto): bool
    {
        return $this->db->execute(
            "UPDATE produtos SET estoque_atual = :estoque, preco_custo = :preco WHERE id = :id",
            ['estoque' => $novoEstoque, 'preco' => $precoCusto, 'id' => $id]
        );
    }

    // ----------------------------------------------------------------
    // GERAÇÃO DE CÓDIGO
    // ----------------------------------------------------------------

    public function gerarCodigo(): string
    {
        $sql    = "SELECT MAX(CAST(SUBSTRING(codigo, 5) AS UNSIGNED)) AS ultimo FROM produtos WHERE codigo REGEXP '^PRD-[0-9]+$'";
        $result = $this->db->fetchOne($sql);
        $proximo = ($result['ultimo'] ?? 0) + 1;
        return 'PRD-' . str_pad($proximo, 6, '0', STR_PAD_LEFT);
    }

    // ----------------------------------------------------------------
    // HELPERS PARA O FORM
    // ----------------------------------------------------------------

    public function listarCategoriasForm(): array
    {
        return $this->db->fetchAll(
            "SELECT id, nome, parent_id FROM categorias ORDER BY nome ASC"
        ) ?? [];
    }

    public function listarUnidadesForm(): array
    {
        return $this->db->fetchAll(
            "SELECT id, nome, sigla FROM unidades ORDER BY nome ASC"
        ) ?? [];
    }

    // ----------------------------------------------------------------
    // HELPERS PRIVADOS
    // ----------------------------------------------------------------

    /**
     * REGRA DE NEGÓCIO CENTRAL:
     * Serviços não têm estoque — os campos são zerados/nulificados
     * independentemente do que vier do formulário.
     */
    private function normalizarServico(array &$dados): void
    {
        if (($dados['tipo'] ?? 'produto') === 'servico') {
            $dados['estoque_atual']  = null;
            $dados['estoque_minimo'] = null;
            $dados['estoque_maximo'] = null;
        }
    }

    private function filtrarCampos(array $dados): array
    {
        $dados = array_intersect_key($dados, array_flip($this->fillable));

        // Campos opcionais que devem ser NULL quando vazios
        foreach (['codigo_barras', 'descricao', 'imagem', 'categoria_id', 'estoque_maximo'] as $campo) {
            if (isset($dados[$campo]) && $dados[$campo] === '') {
                $dados[$campo] = null;
            }
        }

        return $dados;
    }

    private function validar(array $dados, ?int $id = null): void
    {
        if (empty($dados['nome'])) {
            throw new \InvalidArgumentException('Nome do produto é obrigatório.');
        }

        if (empty($dados['codigo'])) {
            throw new \InvalidArgumentException('Código interno é obrigatório.');
        }

        $sql    = "SELECT id FROM produtos WHERE codigo = :codigo" . ($id ? " AND id != :id" : "");
        $params = ['codigo' => $dados['codigo']];
        if ($id) $params['id'] = $id;

        if ($this->db->fetchOne($sql, $params)) {
            throw new \InvalidArgumentException("Já existe um produto com o código '{$dados['codigo']}'.");
        }

        if (!empty($dados['codigo_barras'])) {
            $sql2    = "SELECT id FROM produtos WHERE codigo_barras = :cb" . ($id ? " AND id != :id" : "");
            $params2 = ['cb' => $dados['codigo_barras']];
            if ($id) $params2['id'] = $id;

            if ($this->db->fetchOne($sql2, $params2)) {
                throw new \InvalidArgumentException('Já existe um produto com este código de barras.');
            }
        }
    }
}
