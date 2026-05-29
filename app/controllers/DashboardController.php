<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Dashboard as DashboardModel;

class DashboardController extends Controller
{
    private DashboardModel $dashboard;

    public function __construct()
    {
        if (!Session::get('usuario_id')) {
            Session::flash('error', 'Sessão expirada. Faça login novamente.');
            redirect('/login');
        }

        $this->dashboard = new DashboardModel();
    }

    public function index(): void
    {
        $vendasHoje  = $this->dashboard->vendasHoje();
        $resumoMes   = $this->dashboard->resumoMes();
        $estoque     = $this->dashboard->resumoEstoque();
        $comprasMes  = $this->dashboard->resumoComprasMes();
        $caixaAberto = $this->dashboard->caixaAberto();
        $fat7dias    = $this->dashboard->faturamento7Dias();
        $topProdutos = $this->dashboard->topProdutos();
        $alertas     = $this->dashboard->produtosAlerta();
        $ultVendas   = $this->dashboard->ultimasVendas();

        $this->view('dashboard/index', [
            'pageTitle'          => 'Dashboard',
            'usuario_nome'       => Session::get('usuario_nome') ?? 'Usuário',
            // Hoje
            'faturamento_hoje'   => $vendasHoje['faturamento_hoje'],
            'qtd_vendas_hoje'    => $vendasHoje['qtd_vendas_hoje'],
            'lucro_bruto_hoje'   => $vendasHoje['lucro_bruto_hoje'],
            'ticket_medio'       => $vendasHoje['ticket_medio'],
            'margem_hoje'        => $vendasHoje['margem_hoje'],
            // Mês
            'faturamento_mes'    => $resumoMes['faturamento_mes'],
            'lucro_bruto_mes'    => $resumoMes['lucro_bruto_mes'],
            'margem_mes'         => $resumoMes['margem_mes'],
            'produtos_sem_custo' => $resumoMes['produtos_sem_custo'],
            'qtd_vendas_mes'     => $resumoMes['qtd_vendas_mes'],
            'variacao_mes'       => $resumoMes['variacao_mes'],
            // Estoque
            'total_produtos'            => $estoque['total_produtos']           ?? 0,
            'alerta_estoque'            => $estoque['alerta_estoque']           ?? 0,
            'sem_estoque'               => $estoque['sem_estoque']              ?? 0,
            'valor_em_estoque'          => $estoque['valor_em_estoque']         ?? 0,
            'lucro_presumido_estoque'   => $estoque['lucro_presumido_estoque']  ?? 0,
            'sem_preco_completo'        => $estoque['sem_preco_completo']       ?? 0,
            // Compras
            'compras_mes_qtd'    => $comprasMes['qtd_compras']   ?? 0,
            'compras_mes_valor'  => $comprasMes['valor_compras'] ?? 0,
            // Caixa
            'caixa_aberto'       => $caixaAberto,
            // Listas
            'fat_7dias'          => $fat7dias,
            'top_produtos'       => $topProdutos,
            'produtos_alerta'    => $alertas,
            'ultimas_vendas'     => $ultVendas,
        ]);
    }
}
