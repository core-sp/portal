<?php

namespace App\Services;

use App\Contracts\SalaReuniaoServiceInterface;
use App\SalaReuniao;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\SalaReuniaoMail;
use Carbon\Carbon;

class SalaReuniaoService implements SalaReuniaoServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'sala de reunião',
            'singulariza' => 'a sala de reunião',
            'plural' => 'salas de reuniões',
            'pluraliza' => 'salas de reuniões',
            'form' => 'sala_reuniao',
        ];
    }

    private function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Id',
            'Regional',
            'Participantes Reunião',
            'Participantes Coworking',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        // $userPodeEditar = auth()->user()->can('updateOther', auth()->user());
        foreach($resultados as $resultado) {
            $acoes = '';
            // if($userPodeEditar)
                $acoes = '<a href="' .route('sala.reuniao.editar.view', $resultado->id). '" class="btn btn-sm btn-primary">Editar</a> ';
            $conteudo = [
                $resultado->id,
                $resultado->regional->regional,
                $resultado->participantes_reuniao,
                $resultado->participantes_coworking,
                $acoes
            ];
            array_push($contents, $conteudo);
        }

        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        $tabela = montaTabela($headers, $contents, $classes);
        
        return $tabela;
    }

    public function getHoras()
    {
        return [
            'manha' => SalaReuniao::horasManha(),
            'tarde' => SalaReuniao::horasTarde()
        ];
    }
    public function getItensByTipo($tipo)
    {
        switch($tipo){
            case 'coworking': 
                return SalaReuniao::itensCoworking();
                break;
            case 'reuniao': 
            default:
                return SalaReuniao::itens();
        }            
    }

    public function listar()
    {
        $salas = SalaReuniao::with('regional')->get();

        return [
            'tabela' => $this->tabelaCompleta($salas),
            'resultados' => $salas,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($id)
    {
        $sala = SalaReuniao::with('regional')->findOrFail($id);
        
        return [
            'resultado' => $sala,
            'variaveis' => (object) $this->variaveis,
        ];
    }

    public function save($dados, $id, $user)
    {
        $sala = SalaReuniao::findOrFail($id);
        $itens_reuniao = $sala->getItens('reuniao');
        $participantes['reuniao'] = $sala->participantes_reuniao;
        $itens_coworking = $sala->getItens('coworking');
        $participantes['coworking'] = $sala->participantes_coworking;

        $sala->update([
            'horarios_reuniao' => json_encode([
                'manha' => isset($dados['manha_horarios_reuniao']) ? $dados['manha_horarios_reuniao'] : array(),
                'tarde' => isset($dados['tarde_horarios_reuniao']) ? $dados['tarde_horarios_reuniao'] : array()
            ], JSON_FORCE_OBJECT),
            'horarios_coworking' => json_encode([
                'manha' => isset($dados['manha_horarios_coworking']) ? $dados['manha_horarios_coworking'] : array(),
                'tarde' => isset($dados['tarde_horarios_coworking']) ? $dados['tarde_horarios_coworking'] : array(),
            ], JSON_FORCE_OBJECT),
            'participantes_reuniao' => $dados['participantes_reuniao'],
            'participantes_coworking' => $dados['participantes_coworking'],
            'itens_reuniao' => json_encode(isset($dados['itens_reuniao']) ? $dados['itens_reuniao'] : array(), JSON_FORCE_OBJECT),
            'itens_coworking' => json_encode(isset($dados['itens_coworking']) ? $dados['itens_coworking'] : array(), JSON_FORCE_OBJECT),
            'idusuario' => $user->idusuario
        ]);

        $final = $sala->verificaAlteracaoItens($itens_reuniao, $itens_coworking, $participantes);
        if(isset($final['gerente']))
        {
            $gerente = $final['gerente'];
            Mail::to($gerente->email)->queue(new SalaReuniaoMail($sala, $final['itens']));
            event(new CrudEvent('para ' . $gerente->nome . ' (gerente da seccional ' . $sala->regional->regional.') após alteração de itens da sala de reunião', 'envio de e-mail', $id));
        }
        
        event(new CrudEvent('sala de reunião', 'editou', $id));
    }

    public function salasAtivas($tipo = null)
    {
        if(!isset($tipo))
            return SalaReuniao::with('regional')
            ->where('participantes_reuniao', '>', 0)
            ->orWhere('participantes_coworking', '>', 0)
            ->get()
            ->sortBy('regional.regional');
        
        if(in_array($tipo, ['reuniao', 'coworking']))
            return SalaReuniao::with('regional')
            ->where('participantes_'.$tipo, '>', 0)
            ->get()
            ->sortBy('regional.regional');
        
        return collect();
    }

    public function getDiasHoras($tipo, $id, $dia = null, $user = null)
    {
        if(in_array($tipo, ['reuniao', 'coworking']))
        {
            $sala = SalaReuniao::where('id', $id)->where('participantes_'.$tipo, '>', 0)->first();
            if(!isset($sala))
                return null;
            
            if(isset($dia))
            {
                $final = array();
                if(!Carbon::hasFormat($dia, 'd/m/Y'))
                    return null;
                $dia = Carbon::createFromFormat('d/m/Y', $dia)->format('Y-m-d');
                $periodos = $sala->removeHorariosSeLotado($tipo, $dia);

                if(isset($user))
                    $periodos = $user->getPeriodoByDia($dia, $periodos);

                if(!empty($periodos))
                {
                    if(in_array('manha', $periodos))
                        $final['manha'] = 'Manhã: '.implode(', ',$sala->getHorariosManha($tipo));
                    if(in_array('tarde', $periodos))
                        $final['tarde'] = 'Tarde: '.implode(', ',$sala->getHorariosTarde($tipo));

                    $final['itens'] = $sala->getItensHtml($tipo);
                    $final['total'] = $sala->getParticipantesAgendar($tipo);
                }
                
                return $final;
            }

            $lotados = $sala->getDiasSeLotado($tipo);

            if(isset($user))
                $lotados = array_merge($lotados, $user->getAgendamentos30Dias($lotados));
            
            return $lotados;
        }

        return null;
    }
    
    public function site()
    {
        return resolve('App\Contracts\SalaReuniaoSiteSubServiceInterface');
    }

    public function agendados()
    {
        return resolve('App\Contracts\AgendamentoSalaSubServiceInterface');
    }
}