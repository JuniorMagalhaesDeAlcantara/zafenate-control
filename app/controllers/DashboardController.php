<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Produto;
use App\Models\Fornecedor;

class DashboardController extends Controller
{
    private Produto $produtoModel;
    private Fornecedor $fornecedorModel;

    public function __construct()
    {
        // Se não tiver usuário logado, chuta de volta para o login
        if (!Session::get('usuario_id')) {
            Session::flash('error', 'Sessão expirada. Faça login novamente.');
            redirect('/login');
        }

        $this->produtoModel = new Produto();
        $this->fornecedorModel = new Fornecedor();
    }

    /**
     * GET /dashboard
     */
    public function index(): void
    {
        // Pegando os totais reais dos seus Models para preencher os cards
        $totaisProdutos = $this->produtoModel->totais();

        // Contagem simplificada de fornecedores
        $fornecedores = $this->fornecedorModel->listar();
        $totalFornecedores = count($fornecedores);

        // Simulando a configuração que você usou no login
        $config = [
            'empresa_nome' => Session::get('empresa_nome') ?? 'Zafenate Control',
            'empresa_cor'  => '#1A1A1A' // Cor padrão, ou puxe do banco/config se tiver
        ];

        $dados = [
            'title'             => 'Dashboard — ' . $config['empresa_nome'],
            'usuario_nome'      => Session::get('usuario_nome') ?? 'Administrador',
            'total_produtos'    => $totaisProdutos['total'] ?? 0,
            'alerta_estoque'    => $totaisProdutos['alerta_estoque'] ?? 0,
            'total_fornecedores' => $totalFornecedores,
            'config'            => $config
        ];

        // Carrega a view usando o require direto (ou a sua função $this->view se preferir)
        // Se usar $this->view, lembre-se que ela precisa herdar a classe Base Controller
       $this->view('dashboard/index', $dados);
    }
}
