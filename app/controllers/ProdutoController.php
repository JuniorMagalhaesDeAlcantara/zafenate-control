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
            'filtros'    => $filtros,
            'categorias' => $categorias
        ];

        // 🌟 CORREÇÃO: Usando o renderizador oficial do seu Framework (faz o extract() automático)
        $this->view('produtos/index', $dados);
    }

    /**
     * GET /produtos/criar
     * Exibe o formulário de cadastro de um novo produto
     */
    public function create(): void
    {
        // Gera o próximo código automático (Ex: PRD-000001) para já exibir travado no input do form
        $proximoCodigo = $this->produtoModel->gerarCodigo();

        $dados = [
            'title'      => 'Novo Produto - Zafenate Control',
            'codigo'     => $proximoCodigo,
            'unidades'   => [], // Popular com (new Unidade())->listar() futuramente
            'categorias' => []
        ];

        // 🌟 DICA: Quando for criar a tela visual de cadastro, basta descomentar a linha abaixo:
        // $this->view('produtos/create', $dados);

        echo "<pre>➕ TELA DE CADASTRO DE PRODUTO\nCódigo Sugerido: {$proximoCodigo}</pre>";
        die();
    }

    /**
     * POST /produtos/criar
     * Processa a inserção do novo produto no banco
     */
    public function store(Request $request): void
    {
        try {
            $dados = $request->all();

            // Força o código sequencial automático gerado pelo seu Model
            $dados['codigo'] = $this->produtoModel->gerarCodigo();
            $dados['ativo']  = 1;

            $id = $this->produtoModel->criar($dados);

            Session::flash('success', 'Produto criado com sucesso!');
            redirect('/produtos');
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            redirect('/produtos/criar');
        }
    }

    /**
     * GET /produtos/{id}
     * Exibe os detalhes de um produto específico
     */
    public function show(int $id): void
    {
        $produto = $this->produtoModel->buscarPorId($id);

        if (!$produto) {
            Session::flash('error', 'Produto não encontrado.');
            redirect('/produtos');
        }

        $dados = [
            'title'   => $produto['nome'] . ' - Detalhes',
            'produto' => $produto
        ];

        echo "<pre>🔍 VISUALIZAR PRODUTO ID: {$id}\n";
        print_r($produto);
        die();
    }

    /**
     * GET /produtos/{id}/editar
     * Exibe o formulário de edição preenchido
     */
    public function edit(int $id): void
    {
        $produto = $this->produtoModel->buscarPorId($id);

        if (!$produto) {
            Session::flash('error', 'Produto não encontrado.');
            redirect('/produtos');
        }

        $dados = [
            'title'      => 'Editar Produto - ' . $produto['nome'],
            'produto'    => $produto,
            'unidades'   => [],
            'categorias' => []
        ];

        echo "<pre>📝 FORMULÁRIO DE EDIÇÃO DO PRODUTO ID: {$id}\n";
        print_r($produto);
        die();
    }

    /**
     * POST /produtos/{id}/editar
     * Processa a atualização dos dados do produto
     */
    public function update(int $id, Request $request): void
    {
        try {
            $dados = $request->all();

            $this->produtoModel->atualizar($id, $dados);

            Session::flash('success', 'Produto updated com sucesso!');
            redirect('/produtos');
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            // 🌟 CORREÇÃO: Adicionado o $ na variável dentro das aspas duplas
            redirect("/produtos/{$id}/editar");
        }
    }

    /**
     * POST /produtos/{id}/status
     * Ativa/Desativa o produto (Soft Delete)
     */
    public function toggleStatus(int $id): void
    {
        $this->produtoModel->alternarStatus($id);
        Session::flash('success', 'Status alterado com sucesso!');
        redirect('/produtos');
    }
}
