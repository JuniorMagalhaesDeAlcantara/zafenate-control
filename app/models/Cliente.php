<?php

namespace App\Models;

use App\Core\Database;

class Cliente
{
    private Database $db;

    private array $fillable = [
        'tipo_pessoa',
        'nome',
        'nome_fantasia',
        'cpf_cnpj',
        'ie',
        'data_nascimento',
        'email',
        'telefone',
        'celular',
        'contato',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'limite_credito',
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

    /**
     * Lista paginada com totais de compra (JOIN vendas).
     * Retorna ['dados', 'total', 'pagina', 'paginas']
     */
    public function listar(array $filtros = [], int $porPagina = 20): array
    {
        $where  = ['c.id != 1']; // exclui "Consumidor Final" da listagem
        $params = [];

        // Busca geral
        if (!empty($filtros['q'])) {

            $where[] = '
            (
                c.nome LIKE :nome
                OR c.cpf_cnpj LIKE :cpf_cnpj
                OR c.celular LIKE :celular
                OR c.telefone LIKE :telefone
                OR c.email LIKE :email
            )
        ';

            $like = '%' . $filtros['q'] . '%';

            $params['nome']      = $like;
            $params['cpf_cnpj']  = $like;
            $params['celular']   = $like;
            $params['telefone']  = $like;
            $params['email']     = $like;
        }

        // Tipo de pessoa
        if (!empty($filtros['tipo_pessoa'])) {
            $where[]               = 'c.tipo_pessoa = :tipo_pessoa';
            $params['tipo_pessoa'] = $filtros['tipo_pessoa'];
        }

        // Status ativo/inativo
        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $where[]         = 'c.ativo = :ativo';
            $params['ativo'] = (int) $filtros['ativo'];
        }

        $whereStr = implode(' AND ', $where);

        // Total para paginação
        $total = (int) $this->db->fetchScalar(
            "SELECT COUNT(*) FROM clientes c WHERE {$whereStr}",
            $params
        );

        $pagina  = max(1, (int)($filtros['pagina'] ?? 1));
        $offset  = ($pagina - 1) * $porPagina;
        $paginas = (int) ceil($total / $porPagina);

        if ($paginas < 1) {
            $paginas = 1;
        }

        $sql = "
        SELECT
            c.*,
            COUNT(v.id) AS total_compras,
            COALESCE(SUM(v.total), 0) AS total_gasto,
            MAX(v.criado_em) AS ultima_compra
        FROM clientes c
        LEFT JOIN vendas v
            ON v.cliente_id = c.id
            AND v.status = 'finalizada'
        WHERE {$whereStr}
        GROUP BY c.id
        ORDER BY c.nome ASC
        LIMIT :limite OFFSET :offset
    ";

        $params['limite'] = (int) $porPagina;
        $params['offset'] = (int) $offset;

        return [
            'dados'   => $this->db->fetchAll($sql, $params),
            'total'   => $total,
            'pagina'  => $pagina,
            'paginas' => $paginas,
        ];
    }

    /**
     * Busca por ID com totais de compra.
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                c.*,
                COUNT(v.id)               AS total_compras,
                COALESCE(SUM(v.total), 0) AS total_gasto,
                MAX(v.criado_em)          AS ultima_compra,
                COALESCE(AVG(v.total), 0) AS ticket_medio
            FROM clientes c
            LEFT JOIN vendas v ON v.cliente_id = c.id AND v.status = 'finalizada'
            WHERE c.id = :id
            GROUP BY c.id
            LIMIT 1
        ";

        return $this->db->fetchOne($sql, ['id' => $id]) ?: null;
    }

    /**
     * Últimas N vendas do cliente — para o perfil.
     */
    public function ultimasVendas(int $clienteId, int $limite = 10): array
    {
        // 1. Busca as vendas básicas
        $sqlVendas = "
        SELECT
            v.id, v.numero, v.total, v.status, v.criado_em,
            u.nome AS operador
        FROM vendas v
        LEFT JOIN usuarios u ON u.id = v.usuario_id
        WHERE v.cliente_id = :id
        ORDER BY v.criado_em DESC
        LIMIT " . (int)$limite; // LIMIT fixo para evitar erro de bind

        $vendas = $this->db->fetchAll($sqlVendas, ['id' => $clienteId]);

        if (empty($vendas)) {
            return [];
        }

        // 2. Coleta os IDs das vendas para buscar os produtos de uma vez só
        $vendaIds = array_column($vendas, 'id');
        $idsPlaceholders = implode(',', array_fill(0, count($vendaIds), '?'));

        $sqlItens = "
        SELECT 
            vi.venda_id, p.nome AS produto_nome, vi.quantidade, vi.preco_unitario
        FROM venda_itens vi
        JOIN produtos p ON p.id = vi.produto_id
        WHERE vi.venda_id IN ($idsPlaceholders)
    ";

        $itens = $this->db->fetchAll($sqlItens, $vendaIds);

        // 3. Agrupa os produtos dentro de cada venda
        $itensPorVenda = [];
        foreach ($itens as $item) {
            $itensPorVenda[$item['venda_id']][] = $item;
        }

        foreach ($vendas as &$venda) {
            $venda['itens'] = $itensPorVenda[$venda['id']] ?? [];
        }

        return $vendas;
    }

    /**
     * Busca rápida para o modal do PDV.
     * Retorna id, nome, cpf_cnpj, celular.
     */
    public function buscarRapido(string $termo, int $limite = 8): array
    {
        // 1. Sanitizar o limite para garantir que seja um inteiro (evita injeção e erro de sintaxe)
        $limite = (int) $limite;

        // 2. Usar parâmetros únicos para cada coluna no LIKE
        // O LIMIT é inserido diretamente via interpolação de variável, já que foi castado para int
        $sql = "
            SELECT id, nome, cpf_cnpj, celular, tipo_pessoa, limite_credito
            FROM clientes
            WHERE ativo = 1
              AND id != 1
              AND (nome LIKE :nome OR cpf_cnpj LIKE :cpf OR celular LIKE :cel)
            ORDER BY nome ASC
            LIMIT {$limite}
        ";

        // 3. Preparar o array de parâmetros com valores distintos para cada marcador
        $likeTerm = '%' . $termo . '%';

        return $this->db->fetchAll($sql, [
            'nome' => $likeTerm,
            'cpf'  => $likeTerm,
            'cel'  => $likeTerm
        ]);
    }

    // ----------------------------------------------------------------
    // ESCRITA
    // ----------------------------------------------------------------

    public function criar(array $dados): int
    {
        $dados = $this->filtrarCampos($dados);
        $this->validar($dados);
        $this->normalizar($dados);

        $campos    = implode(', ', array_keys($dados));
        $placehold = ':' . implode(', :', array_keys($dados));

        $this->db->execute(
            "INSERT INTO clientes ({$campos}) VALUES ({$placehold})",
            $dados
        );

        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $dados = $this->filtrarCampos($dados);
        $this->validar($dados, $id);
        $this->normalizar($dados);

        $sets      = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($dados)));
        $dados['id'] = $id;

        return $this->db->execute(
            "UPDATE clientes SET {$sets} WHERE id = :id",
            $dados
        );
    }

    public function alternarStatus(int $id): bool
    {
        if ($id === 1) return false; // Consumidor Final imutável

        return $this->db->execute(
            "UPDATE clientes SET ativo = NOT ativo WHERE id = :id",
            ['id' => $id]
        );
    }

    // ----------------------------------------------------------------
    // HELPERS
    // ----------------------------------------------------------------

    private function filtrarCampos(array $dados): array
    {
        return array_intersect_key($dados, array_flip($this->fillable));
    }

    private function validar(array $dados, ?int $id = null): void
    {
        if (empty($dados['nome'])) {
            throw new \InvalidArgumentException('Nome é obrigatório.');
        }

        // CPF/CNPJ único (se informado)
        if (!empty($dados['cpf_cnpj'])) {
            $sql    = "SELECT id FROM clientes WHERE cpf_cnpj = :cpf" . ($id ? " AND id != :id" : "");
            $params = ['cpf' => $dados['cpf_cnpj']];
            if ($id) $params['id'] = $id;

            if ($this->db->fetchOne($sql, $params)) {
                throw new \InvalidArgumentException('Já existe um cliente com este CPF/CNPJ.');
            }
        }
    }

    /**
     * Limpa campos opcionais vazios para evitar salvar strings vazias.
     */
    private function normalizar(array &$dados): void
    {
        $nullable = [
            'cpf_cnpj',
            'ie',
            'email',
            'telefone',
            'celular',
            'contato',
            'cep',
            'logradouro',
            'numero',
            'complemento',
            'bairro',
            'cidade',
            'uf',
            'nome_fantasia',
            'data_nascimento',
            'observacoes'
        ];

        foreach ($nullable as $campo) {
            if (isset($dados[$campo]) && $dados[$campo] === '') {
                $dados[$campo] = null;
            }
        }

        // Remove máscara do CPF/CNPJ antes de salvar
        if (!empty($dados['cpf_cnpj'])) {
            $dados['cpf_cnpj'] = preg_replace('/\D/', '', $dados['cpf_cnpj']);
        }

        // Limite de crédito padrão
        if (!isset($dados['limite_credito']) || $dados['limite_credito'] === '') {
            $dados['limite_credito'] = 0.00;
        }
    }
}
