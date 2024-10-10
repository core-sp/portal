<?php

namespace App\Http\Controllers;

use App\Repositories\ChamadoRepository;
use App\Repositories\NewsletterRepository;
use App\Contracts\MediadorServiceInterface;

class AdminController extends Controller
{
    private $chamadoRepository;
    private $newsletterRepository;
    private $service;
    
    public function __construct(ChamadoRepository $chamadoRepository, NewsletterRepository $newsletterRepository, MediadorServiceInterface $service)
    {
        $this->middleware('auth');
        $this->chamadoRepository = $chamadoRepository;
        $this->newsletterRepository = $newsletterRepository;
        $this->service = $service;
    }

    public function index()
    {
        $alertas = $this->alertas();
        $contagem = $this->contagemAtendimentos();
        $chamados = $this->chamadoRepository->getChamadoByIdUsuario(auth()->user()->idusuario);
        $totalAgendamentos = $this->service->getService('Agendamento')->countAll();
        $totalInscritos = $this->service->getService('Curso')->inscritos()->getTotalInscritos();
        $totalNewsletter = $this->newsletterRepository->getCountAllNewsletter();

    	return view("admin.home", compact("alertas", "contagem", "chamados", "totalAgendamentos", "totalInscritos", "totalNewsletter"));
    }

    public function alertas()
    {
        $alertas = [];
        $count = 0;

        if(auth()->user()->perfil->temPermissao('AgendamentoController', 'index'))
        {
            // Alerta de atendimentos sem status
            $count = $this->service->getService('Agendamento')->pendentesByPerfil();

            if($count > 0) 
                $alertas['agendamentoCount'] = $count;
        }
        
        return $alertas;
    }

    public function contagemAtendimentos()
    {
        $listaContagem = auth()->user()
            ->regional
            ->users()
            ->select('nome')
            ->withCount(['agendamentos' => function ($query) {
                $query->where('status', 'Compareceu');
            }])
            ->where('idperfil', 8)
            ->orWhere(function($query) {
                $query->where('idregional', auth()->user()->idregional)
                ->where('idperfil', 18);
            })
            ->orWhere(function($query) {
                $query->where('idregional', auth()->user()->idregional)
                ->where('idperfil', 21);
            })
            ->withoutTrashed()
            ->orderBy('agendamentos_count', 'DESC')
            ->get();

        $tabela = '<table class="table table-bordered table-striped">';
        $tabela .= '<thead>';
        $tabela .= '<tr>';
        $tabela .= '<th>Atendente</th>';
        $tabela .= '<th>Atendimentos</th>';
        $tabela .= '</tr>';
        $tabela .= '</thead>';
        $tabela .= '<tbody>';
    
        foreach($listaContagem as $contagem) {
            $tabela .= '<tr>';
            $tabela .= '<td>' . $contagem->nome . '</td>';
            $tabela .= '<td>' . $contagem->agendamentos_count . '</td>';
            $tabela .= '</tr>';
        }
    
        $tabela .= '</tbody>';
        $tabela .= '</table>';
    
        return $tabela;
    }
}