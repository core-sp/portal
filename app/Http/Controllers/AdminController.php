<?php

namespace App\Http\Controllers;

use App\User;
use App\Agendamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\CursoRepository;
use App\Repositories\ChamadoRepository;
use App\Repositories\NewsletterRepository;
use App\Repositories\AgendamentoRepository;

class AdminController extends Controller
{
    private $agendamentoRepository;
    private $chamadoRepository;
    private $cursoRepository;
    private $newsletterRepository;
    
    public function __construct(AgendamentoRepository $agendamentoRepository, ChamadoRepository $chamadoRepository, CursoRepository $cursoRepository, NewsletterRepository $newsletterRepository)
    {
        $this->middleware('auth');
        $this->agendamentoRepository = $agendamentoRepository;
        $this->chamadoRepository = $chamadoRepository;
        $this->cursoRepository = $cursoRepository;
        $this->newsletterRepository = $newsletterRepository;
    }

    public function index()
    {
        $alertas = $this->alertas();
        $contagem = $this->contagemAtendimentos();
        $chamados = $this->chamadoRepository->getChamadoByIdUsuario(Auth::user()->idusuario);
        $totalAgendamentos = $this->agendamentoRepository->getCountAllAgendamentos();
        $totalInscritos = $this->cursoRepository->getTotalInscritos();
        $totalNewsletter = $this->newsletterRepository->getCountAllNewsletter();

    	return view("admin.home", compact("alertas", "contagem", "chamados", "totalAgendamentos", "totalInscritos", "totalNewsletter"));
    }

    public function alertas()
    {
        $alertas = [];
        $count = 0;

        // Alerta de atendimentos nulos
        // Contagem de atendimentos pendentes na Sede (perfil de Gestão de Atendimento - Sede)
        if(session('idperfil') === 12) {
            $count = $this->agendamentoRepository->getCountPastAgendamentoPendenteSede();
        } 
        // Contagem de atendimentos pendentes nas Seccionais (perfil de Gestão de Atendimento - Seccionais)
        elseif(session('idperfil') === 13) {
            $count = $this->agendamentoRepository->getCountPastAgendamentoPendenteSeccionais();
        }
        // Contagem de todos os atendimentos pendentes (perfils de Admin e Coordenadoria de Atendimento)
        elseif(session('idperfil') === 6 || session('idperfil') === 1) {
            $count = $this->agendamentoRepository->getCountPastAgendamentoPendente();
        } 
        // Contagem de atendimentos pendentes na regional do usuário (Atendimento)
        elseif(session('idperfil') === 8) {
            $count = $this->agendamentoRepository->getCountPastAgendamentoPendenteByRegional(Auth::user()->idregional);
        }

        if($count < 1) {
            $alertas = [];
        }
        else {
            $alertas['agendamentoCount'] = $count;
        }
        
        return $alertas;
    }

    public function contagemAtendimentos()
    {
        $listaContagem = $this->agendamentoRepository->getAgendamentoConcluidoCountByRegional(1);

        $tabela = '<table class="table table-bordered table-striped">';
        $tabela .= '<thead>';
        $tabela .= '<tr>';
        $tabela .= '<th>Atendente</th>';
        $tabela .= '<th>Atendimentos</th>';
        $tabela .= '</tr>';
        $tabela .= '</thead>';
        $tabela .= '<tbody>';

        foreach($listaContagem as $contagem) {
            // Apenas usuários com perfil de Atendente são listados
            if($contagem->user->idperfil == 8) {
                $tabela .= '<tr>';
                $tabela .= '<td>' . $contagem->user->nome . '</td>';
                $tabela .= '<td>' . $contagem->contagem . '</td>';
                $tabela .= '</tr>';
            }
        }

        $tabela .= '</tbody>';
        $tabela .= '</table>';

        return $tabela;
    }
}