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
        // Proteção de Sessão
        if (!Session::get('usuario_id')) {
            redirect('/login');
        }
        $this->produtoModel = new Produto();
    }

    /**
     * GET /produtos
     * Lista todos os produtos com paginação/filtros
     */
    public function index(Request $request): void
    {
        $filtros = [
            'busca'          => $request->input('busca', ''),
            'categoria_id'   => $request->input('categoria_id', ''),
            'ativo'          => $request->input('ativo', ''),
            'alerta_estoque' => $request->input('alerta_estoque', 0)
        ];

        $produtos = $this->produtoModel->listar($filtros);
        $totais   = $this->produtoModel->totais();

        // Mocando categorias temporariamente (Substitua pelo seu Categoria Model depois)
        $categorias = [];

        $dados = [
            'title'      => 'Produtos - Zafenate Control',
            'produtos'   => $produtos,
            'totais'     => $totais,
            'categorias' => $categorias,
            'filtros'    => $filtros
        ];

        $this->view('produtos/index', $dados);
    }

    /**
     * GET /produtos/criar
     * Exibe o formulário de cadastro de novo produto
     */
    public function create(): void
    {
        $codigoObtido = $this->produtoModel->gerarCodigo();

        // Substituir pelos Models reais depois
        $categorias = $this->produtoModel->listarCategoriasForm() ?? [];
        $unidades   = $this->produtoModel->listarUnidadesForm() ?? [];

        $dados = [
            'title'      => 'Novo Produto - Zafenate Control',
            'codigo'     => $codigoObtido,
            'categorias' => $categorias,
            'unidades'   => $unidades
        ];

        $this->view('produtos/create', $dados);
    }

    /**
     * POST /produtos/criar
     * Processa o salvamento do novo produto no banco
     */
    public function store(Request $request): void
    {
        try {
            $dados = $request->all();

            // 🔥 TRATAMENTO 1: Evita quebra se categoria vier vazia
            if (empty($dados['categoria_id'])) {
                $dados['categoria_id'] = null;
            }

            // 🔥 TRATAMENTO 2: Limpa strings vazias de campos numéricos opcionais para não quebrar o MySQL
            if (empty($dados['preco_custo'])) $dados['preco_custo'] = 0.00;
            if (empty($dados['estoque_atual'])) $dados['estoque_atual'] = 0.000;
            if (empty($dados['estoque_minimo'])) $dados['estoque_minimo'] = 0.000;
            if (empty($dados['estoque_maximo'])) $dados['estoque_maximo'] = null;

            // Gera código interno sequencial (PRD-000001, PRD-000002…)
            $dados['codigo'] = $this->produtoModel->gerarCodigo();

            $this->produtoModel->criar($dados);

            Session::flash('success', 'Produto cadastrado com sucesso!');
            redirect('/produtos');
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            redirect('/produtos/criar');
        } catch (\Exception $e) {
            Session::flash('error', 'Erro interno ao salvar produto: ' . $e->getMessage());
            redirect('/produtos/criar');
        }
    }

    /**
     * GET /produtos/{id}/editar
     * Exibe o formulário populado com os dados do produto para edição
     */
    /**
     * GET /produtos/{id}/editar
     */
    public function edit(Request $request, int $id): void
    {
        // Força o ID a ser um inteiro, já que ele vem como string da URL
        $id = (int)$id;

        $produto = $this->produtoModel->buscarPorId($id);

        if (!$produto) {
            Session::flash('error', 'Produto não encontrado.');
            redirect('/produtos');
        }

        $categorias = $this->produtoModel->listarCategoriasForm() ?? [];
        $unidades   = $this->produtoModel->listarUnidadesForm() ?? [];

        $dados = [
            'title'      => 'Editar Produto - Zafenate Control',
            'produto'    => $produto,
            'codigo'     => $produto['codigo'],
            'categorias' => $categorias,
            'unidades'   => $unidades
        ];

        $this->view('produtos/create', $dados);
    }
    /**
     * POST /produtos/{id}/editar
     * Processa a atualização dos dados do produto
     */
    /**
     * POST /produtos/{id}/editar
     * Processa a atualização dos dados do produto
     */
    public function update(Request $request, mixed $id): void
    {
        // Força o ID a virar um número inteiro puro
        $id = (int)$id;

        try {
            $dados = $request->all();

            // TRATAMENTO DE CAMPOS: Evita quebras no MySQL se vierem vazios
            if (empty($dados['categoria_id'])) {
                $dados['categoria_id'] = null;
            }

            if (empty($dados['preco_custo'])) $dados['preco_custo'] = 0.00;
            if (empty($dados['estoque_atual'])) $dados['estoque_atual'] = 0.000;
            if (empty($dados['estoque_minimo'])) $dados['estoque_minimo'] = 0.000;
            if (empty($dados['estoque_maximo'])) $dados['estoque_maximo'] = null;

            // Estoque só muda via MovimentacaoEstoque — nunca pelo form de edição
            unset($dados['estoque_atual']);

            // Como o input do código fica "disabled" no formulário HTML, o navegador não o envia no POST.
            // Buscamos o registro atual no banco para herdar o código original e passar na validação.
            $produtoAtual = $this->produtoModel->buscarPorId($id);
            if ($produtoAtual) {
                $dados['codigo'] = $produtoAtual['codigo'];
            }

            // Executa a query de UPDATE na model
            $this->produtoModel->atualizar($id, $dados);

            Session::flash('success', 'Produto atualizado com sucesso!');
            redirect('/produtos');
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            redirect("/produtos/{$id}/editar");
        } catch (\Exception $e) {
            Session::flash('error', 'Erro ao atualizar produto: ' . $e->getMessage());
            redirect("/produtos/{$id}/editar");
        }
    }
    /**
     * GET /produtos/{id}
     * Exibe os detalhes de um produto específico
     */
    /**
     * GET /produtos/{id}
     */
    public function show(Request $request, int $id): void
    {
        $id = (int)$id;

        $produto = $this->produtoModel->buscarPorId($id);

        if (!$produto) {
            Session::flash('error', 'Produto não encontrado.');
            redirect('/produtos');
        }

        $dados = [
            'title'   => $produto['nome'] . ' - Detalhes',
            'produto' => $produto
        ];

        $this->view('produtos/show', $dados);
    }

    /**
     * POST /produtos/{id}/status
     * Ativa/Desativa o produto (Soft Delete)
     */
    public function toggleStatus(Request $request, mixed $id): void
    {
        // Força o ID vindo da URL a ser um inteiro puro
        $id = (int)$id;

        try {
            // Chama o método exato da sua Model Produto.php
            $this->produtoModel->alternarStatus($id);

            Session::flash('success', 'Status do produto alterado com sucesso!');
        } catch (\Exception $e) {
            Session::flash('error', 'Erro ao alterar o status do produto: ' . $e->getMessage());
        }

        // Redireciona de volta para a listagem principal
        redirect('/produtos');
    }

    /**
     * POST /produtos/rapido
     * Cadastro rápido via AJAX (modal na tela de compra)
     * Retorna JSON com o produto criado
     */
    public function storeRapido(Request $request): void
    {
        header('Content-Type: application/json');

        try {
            $dados = [
                'nome'          => trim($request->input('nome', '')),
                'unidade_id'    => (int)$request->input('unidade_id'),
                'categoria_id'  => $request->input('categoria_id') ?: null,
                'codigo_barras' => trim($request->input('codigo_barras', '')) ?: null,
                'preco_custo'   => (float)str_replace(',', '.', $request->input('preco_custo', '0')),
                'preco_venda'   => (float)str_replace(',', '.', $request->input('preco_venda', '0')),
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

            $id = $this->produtoModel->criar($dados);

            // Busca o produto completo pra devolver pro JS
            $produto = $this->produtoModel->buscarPorId($id);

            echo json_encode([
                'success' => true,
                'produto' => [
                    'id'           => $produto['id'],
                    'nome'         => $produto['nome'],
                    'codigo'       => $produto['codigo'],
                    'preco_custo'  => $produto['preco_custo'],
                    'unidade_sigla' => $produto['unidade_sigla'] ?? 'UN',
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

        exit;
    }
}
