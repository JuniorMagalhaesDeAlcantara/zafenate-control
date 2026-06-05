<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Produto;

class ProdutoController extends Controller
{
    private Produto $produtoModel;

    public function __construct()
    {
        if (!Session::get('usuario_id')) {
            redirect('/login');
        }
        $this->produtoModel = new Produto();
    }

    // ----------------------------------------------------------------
    // GET /produtos
    // ----------------------------------------------------------------
    public function index(Request $request): void
    {
        $status = $request->input('status', '');

        $filtros = [
            'busca'          => $request->input('busca', ''),
            'categoria_id'   => $request->input('categoria_id', ''),
            'tipo'           => $request->input('tipo', ''),   // 'produto' | 'servico' | ''
            'status'         => $status,
            'ativo'          => $status === 'ativos'  ? 1 : '',
            'alerta_estoque' => $status === 'alerta'  ? 1 : 0,
            'zerados'        => $status === 'zerados' ? 1 : 0,
        ];

        $this->view('produtos/index', [
            'title'      => 'Produtos - Zafenate Control',
            'produtos'   => $this->produtoModel->listar($filtros),
            'totais'     => $this->produtoModel->totais(),
            'categorias' => [],
            'filtros'    => $filtros,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /produtos/criar
    // ----------------------------------------------------------------
    public function create(): void
    {
        $this->view('produtos/create', [
            'title'      => 'Novo Produto - Zafenate Control',
            'codigo'     => $this->produtoModel->gerarCodigo(),
            'categorias' => $this->produtoModel->listarCategoriasForm(),
            'unidades'   => $this->produtoModel->listarUnidadesForm(),
        ]);
    }

    // ----------------------------------------------------------------
    // POST /produtos/criar
    // ----------------------------------------------------------------
    public function store(Request $request): void
    {
        try {
            $dados = $this->normalizarDados($request->all());
            $dados['codigo'] = $this->produtoModel->gerarCodigo();

            $this->produtoModel->criar($dados);

            Session::flash('success', 'Produto cadastrado com sucesso!');
            redirect('/produtos');
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            redirect('/produtos/criar');
        } catch (\Exception $e) {
            Session::flash('error', $this->traduzirErroBanco($e->getMessage()));
            redirect('/produtos/criar');
        }
    }

    // ----------------------------------------------------------------
    // GET /produtos/{id}
    // ----------------------------------------------------------------
    public function show(Request $request, int $id): void
    {
        $produto = $this->produtoModel->buscarPorId((int) $id);

        if (!$produto) {
            Session::flash('error', 'Produto não encontrado.');
            redirect('/produtos');
        }

        $this->view('produtos/show', [
            'title'   => $produto['nome'] . ' - Detalhes',
            'produto' => $produto,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /produtos/{id}/editar
    // ----------------------------------------------------------------
    public function edit(Request $request, int $id): void
    {
        $produto = $this->produtoModel->buscarPorId((int) $id);

        if (!$produto) {
            Session::flash('error', 'Produto não encontrado.');
            redirect('/produtos');
        }

        $this->view('produtos/create', [
            'title'      => 'Editar Produto - Zafenate Control',
            'produto'    => $produto,
            'codigo'     => $produto['codigo'],
            'categorias' => $this->produtoModel->listarCategoriasForm(),
            'unidades'   => $this->produtoModel->listarUnidadesForm(),
        ]);
    }

    // ----------------------------------------------------------------
    // POST /produtos/{id}/editar
    // ----------------------------------------------------------------
    public function update(Request $request, mixed $id): void
    {
        $id = (int) $id;

        try {
            $dados = $this->normalizarDados($request->all());

            // Herda o código atual (campo disabled não vem no POST)
            $produtoAtual = $this->produtoModel->buscarPorId($id);
            if ($produtoAtual) {
                $dados['codigo'] = $produtoAtual['codigo'];
            }

            $this->produtoModel->atualizar($id, $dados);

            Session::flash('success', 'Produto atualizado com sucesso!');
            redirect('/produtos');
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            redirect("/produtos/{$id}/editar");
        } catch (\Exception $e) {
            Session::flash('error', $this->traduzirErroBanco($e->getMessage()));
            redirect("/produtos/{$id}/editar");
        }
    }

    // ----------------------------------------------------------------
    // POST /produtos/{id}/status
    // ----------------------------------------------------------------
    public function toggleStatus(Request $request, mixed $id): void
    {
        $id = (int) $id;

        try {
            $this->produtoModel->alternarStatus($id);
            Session::flash('success', 'Status do produto alterado com sucesso!');
        } catch (\Exception $e) {
            Session::flash('error', 'Erro ao alterar o status do produto.');
        }

        redirect('/produtos');
    }

    // ----------------------------------------------------------------
    // POST /produtos/rapido  (AJAX)
    // ----------------------------------------------------------------
    public function storeRapido(Request $request): void
    {
        header('Content-Type: application/json');

        try {
            $dados = [
                'tipo'          => $request->input('tipo', 'produto'),
                'nome'          => trim($request->input('nome', '')),
                'unidade_id'    => (int) $request->input('unidade_id'),
                'categoria_id'  => $request->input('categoria_id') ?: null,
                'codigo_barras' => trim($request->input('codigo_barras', '')) ?: null,
                'preco_custo'   => (float) str_replace(',', '.', $request->input('preco_custo', '0')),
                'preco_venda'   => (float) str_replace(',', '.', $request->input('preco_venda', '0')),
                'estoque_atual'  => 0.000,
                'estoque_minimo' => 0.000,
                'ativo'          => 1,
            ];

            if (empty($dados['nome'])) {
                throw new \InvalidArgumentException('Nome do produto é obrigatório.');
            }
            if (empty($dados['unidade_id'])) {
                throw new \InvalidArgumentException('Unidade é obrigatória.');
            }

            $dados['codigo'] = $this->produtoModel->gerarCodigo();
            $id      = $this->produtoModel->criar($dados);
            $produto = $this->produtoModel->buscarPorId($id);

            echo json_encode([
                'success' => true,
                'produto' => [
                    'id'            => $produto['id'],
                    'nome'          => $produto['nome'],
                    'tipo'          => $produto['tipo'],
                    'codigo'        => $produto['codigo'],
                    'preco_custo'   => $produto['preco_custo'],
                    'unidade_sigla' => $produto['unidade_sigla'] ?? 'UN',
                ],
            ]);
        } catch (\Exception $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

        exit;
    }

    // ----------------------------------------------------------------
    // Helpers privados
    // ----------------------------------------------------------------

    /**
     * Normalização de dados comuns a store() e update().
     */
    private function normalizarDados(array $dados): array
    {
        // Tipo padrão se não vier (segurança)
        $dados['tipo'] = in_array($dados['tipo'] ?? '', ['produto', 'servico'])
            ? $dados['tipo']
            : 'produto';

        // Campos opcionais → null quando vazios
        $dados['categoria_id']  = !empty($dados['categoria_id'])  ? (int) $dados['categoria_id']  : null;
        $dados['codigo_barras'] = !empty($dados['codigo_barras']) ? trim($dados['codigo_barras'])  : null;

        // Numéricos
        $dados['preco_custo']   = !empty($dados['preco_custo'])   ? (float) $dados['preco_custo']  : 0.00;
        $dados['preco_venda']   = !empty($dados['preco_venda'])   ? (float) $dados['preco_venda']  : 0.00;

        // Estoque — serviços: o Model zerará via normalizarServico()
        $dados['estoque_atual']  = isset($dados['estoque_atual'])  && $dados['estoque_atual']  !== '' ? (float) $dados['estoque_atual']  : 0.000;
        $dados['estoque_minimo'] = isset($dados['estoque_minimo']) && $dados['estoque_minimo'] !== '' ? (float) $dados['estoque_minimo'] : 0.000;
        $dados['estoque_maximo'] = !empty($dados['estoque_maximo']) ? (float) $dados['estoque_maximo'] : null;

        return $dados;
    }

    /**
     * Traduz erros de constraint do banco para mensagens amigáveis.
     */
    private function traduzirErroBanco(string $msg): string
    {
        return match (true) {
            str_contains($msg, 'uk_produto_codigo_barras')
            => 'Já existe um produto cadastrado com este código de barras.',
            str_contains($msg, 'uk_produto_codigo')
            => 'Já existe um produto com este código interno.',
            default
            => 'Erro ao salvar produto. Tente novamente.',
        };
    }
}
