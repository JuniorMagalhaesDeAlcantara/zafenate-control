<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Caixa as CaixaModel;

class CaixaController extends Controller
{
    private CaixaModel $caixaModel;
    private int $usuarioId;

    public function __construct()
    {
        if (!Session::get('usuario_id')) {
            $this->redirect('/login');
            exit;
        }
        $this->caixaModel = new CaixaModel();
        $this->usuarioId  = (int) Session::get('usuario_id');
    }

    /**
     * GET /caixa - Dashboard Principal (Auditoria)
     */
    public function index(Request $request): void
    {
        $caixaAberto = $this->caixaModel->buscarAbertoPorUsuario($this->usuarioId);

        $inicio = $request->input('data_inicio', date('Y-m-01'));
        $fim    = $request->input('data_fim', date('Y-m-t'));

        $caixas = $this->caixaModel->listarRelatorioAuditoria($inicio, $fim);
        $totalDiferenca = array_reduce($caixas, fn($sum, $c) => $sum + (float)$c['diferenca'], 0.0);

        $this->view('caixas/dashboard', [
            'title'           => 'Dashboard de Caixa',
            'caixaAberto'     => $caixaAberto,
            'caixas'          => $caixas,
            'total_diferenca' => $totalDiferenca,
            'filtros'         => ['inicio' => $inicio, 'fim' => $fim]
        ]);
    }

    /**
     * GET /caixa/gestao - Tela para Fechamento
     */
    public function gestao(): void
    {
        $caixa = $this->caixaModel->buscarAbertoPorUsuario($this->usuarioId);

        if (!$caixa) {
            $this->view('caixas/abrir', [
                'title' => 'Abrir Caixa - Zafenate Control',
                'csrf'  => csrf_field(),
            ]);
            return;
        }

        $movimentos = $this->caixaModel->movimentos($caixa['id']);

        $this->view('caixas/gestao', [
            'title'      => 'Gestão do Caixa - Zafenate Control',
            'caixa'      => $caixa,
            'movimentos' => $movimentos,
            'csrf'       => csrf_field(),
        ]);
    }

    /**
     * POST /caixa/abrir
     */
    public function abrir(Request $request): void
    {
        try {
            $saldoAbertura = $request->input('saldo_abertura', '0,00');
            $saldoAbertura = str_replace(['.', ','], ['', '.'], $saldoAbertura);

            $this->caixaModel->abrir($this->usuarioId, $saldoAbertura);

            Session::flash('success', 'Caixa aberto com sucesso!');
            $this->redirect('/caixa');
        } catch (\Exception $e) {
            Session::flash('error', 'Erro ao abrir caixa: ' . $e->getMessage());
            $this->redirect('/caixa');
        }
    }

    /**
     * POST /caixa/fechar
     */
    public function fechar(Request $request): void
    {
        try {
            $caixaId = (int) $request->input('caixa_id');
            $saldoInformado = (float) str_replace(['.', ','], ['', '.'], $request->input('saldo_informado', '0,00'));
            $observacao = $request->input('observacao_fechamento', null);

            if (!$caixaId) {
                throw new \Exception("ID do caixa não fornecido.");
            }

            $this->caixaModel->fechar($caixaId, (string)$saldoInformado, $observacao);

            Session::flash('success', 'Caixa fechado com sucesso!');
            $this->redirect('/caixa');
        } catch (\Exception $e) {
            Session::flash('error', 'Erro ao fechar caixa: ' . $e->getMessage());
            $this->redirect('/caixa/gestao');
        }
    }

    /**
     * GET /caixa/sangria
     */
    public function sangria(): void
    {
        $caixa = $this->caixaModel->buscarAbertoPorUsuario($this->usuarioId);

        if (!$caixa) {
            Session::flash('error', 'Nenhum caixa aberto.');
            $this->redirect('/caixa');
            return;
        }

        $this->view('caixas/sangria', [
            'title'    => 'Realizar Sangria',
            'caixa'    => $caixa,
            'caixa_id' => $caixa['id']
        ]);
    }

    /**
     * POST /caixa/sangria
     */
    public function salvarSangria(Request $request): void
    {
        try {
            $caixaId = (int) $request->input('caixa_id');
            $valorRaw = $request->input('valor', '0,00');
            $valor = (float) str_replace(['.', ','], ['', '.'], $valorRaw);

            if ($valor <= 0) throw new \InvalidArgumentException("Valor inválido.");

            $motivo = trim($request->input('motivo', ''));
            if (strlen($motivo) < 3) throw new \InvalidArgumentException("Motivo obrigatório.");

            $this->caixaModel->registrarSangria($caixaId, $valor, $motivo, $this->usuarioId);

            Session::flash('success', 'Sangria registrada!');
            $this->redirect('/caixa/sangria');
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/caixa/sangria');
        }
    }
}
