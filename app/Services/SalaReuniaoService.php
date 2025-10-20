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
            'singular' => 'sala de reunião / coworking',
            'singulariza' => 'a sala de reunião / coworking',
            'plural' => 'salas de reuniões / coworking',
            'pluraliza' => 'salas de reuniões / coworking',
            'form' => 'sala_reuniao',
        ];
    }

    private function tabelaCompleta($user, $resultados, $service)
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
        $userPodeEditar = $user->can('updateOther', auth()->user());
        foreach($resultados as $resultado) {
            $acoes = '';
            if($userPodeEditar)
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

        $data = $service->getService('TermoConsentimento')->dataAtualizacaoTermoStorage('sala-reuniao');
        $data = isset($data) ? '<strong>Última atualização:</strong> ' . $data : '<strong>Nenhum termo adicionado!</strong>';
        $upload_form = '<p class="text-primary mb-1"><i class="fas fa-info-circle"></i>&nbsp;Para atualizar o arquivo das condições para o aceite do representante ao agendar.</p>';
        $upload_form .= '<div class="d-inline-flex"><form class="form-inline" action="'. route('termo.consentimento.upload', 'sala-reuniao').'" method="POST" enctype="multipart/form-data">';
        $upload_form .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
        $upload_form .= '<label for="enviar-file-sala" class="mr-sm-2"><i class="far fa-file-alt"></i>&nbsp;Atualizar arquivo de aceite</label><input type="file" name="file" ';
        $upload_form .= 'class="form-control pl-0 pb-1 pt-1 mb-2 mr-sm-2" id="enviar-file-sala" accept=".pdf" />';
        $upload_form .= '<button class="btn btn-sm btn-primary mb-2" type="submit">Enviar</button>';
        $upload_form .= '<span class="ml-3 mb-2"><a class="btn btn-sm btn-success" href="'. route('termo.consentimento.pdf', 'sala-reuniao') .'" target="_blank">Abrir</a></span></form>';
        $upload_form .= '<span class="ml-3 d-flex flex-wrap align-content-center"><small><i>'.$data.'</i></small></span></div><hr />';

        $tabela = $userPodeEditar ? $upload_form . montaTabela($headers, $contents, $classes) : montaTabela($headers, $contents, $classes);
        
        return $tabela;
    }

    public function getHorasPeriodo($periodo)
    {
        if($periodo == 'manha')
            return SalaReuniao::periodoManha();
        if($periodo == 'tarde')
            return SalaReuniao::periodoTarde();
        return array();
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

    public function listar($user, $service)
    {
        $salas = SalaReuniao::with('regional')
        ->where('id', '!=', 14)
        ->get()
        ->sortBy('regional.regional');

        return [
            'tabela' => $this->tabelaCompleta($user, $salas, $service),
            'resultados' => $salas,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($id)
    {
        if($id == 14)
            throw new \Exception('Sala com id ' . $id . ' não está disponível para uso.', 404);

        $sala = SalaReuniao::with('regional')->findOrFail($id);
        
        return [
            'resultado' => $sala,
            'variaveis' => (object) $this->variaveis,
        ];
    }

    public function save($dados, $id, $user)
    {
        if($id == 14)
            throw new \Exception('Sala com id ' . $id . ' não está disponível para uso.', 404);
        
        $sala = SalaReuniao::findOrFail($id);

        $dados['itens_reuniao'] = isset($dados['itens_reuniao']) ? $dados['itens_reuniao'] : array();

        // Item "Mesa..." da reunião adicionada é preenchida com o mesmo valor de participantes reunião.
        $indice_mesa = array_keys(array_filter($dados['itens_reuniao'], function($v, $k){
            return strpos($v, 'Mesa com ') !== false;
        }, ARRAY_FILTER_USE_BOTH));
        if(count($indice_mesa) > 0)
            $dados['itens_reuniao'][$indice_mesa[0]] = 'Mesa com ' . $dados['participantes_reuniao'] . ' cadeira(s)';

        $itens_reuniao = $sala->getItens('reuniao');
        $participantes['reuniao'] = $sala->participantes_reuniao;
        $itens_coworking = $sala->getItens('coworking');
        $participantes['coworking'] = $sala->participantes_coworking;
        $periodos = ['final_manha' => $sala->horaAlmoco(), 'final_tarde' => $sala->horaFimExpediente()];

        $sala->update([
            'horarios_reuniao' => isset($dados['horarios_reuniao']) && !empty($dados['horarios_reuniao']) ? implode(',', $dados['horarios_reuniao']) : null,
            'horarios_coworking' => isset($dados['horarios_coworking']) && !empty($dados['horarios_coworking']) ? implode(',', $dados['horarios_coworking']) : null,
            'participantes_reuniao' => $dados['participantes_reuniao'],
            'participantes_coworking' => $dados['participantes_coworking'],
            'itens_reuniao' => json_encode(isset($dados['itens_reuniao']) ? $dados['itens_reuniao'] : array(), JSON_FORCE_OBJECT),
            'itens_coworking' => json_encode(isset($dados['itens_coworking']) ? $dados['itens_coworking'] : array(), JSON_FORCE_OBJECT),
            'hora_limite_final_manha' => $dados['hora_limite_final_manha'],
            'hora_limite_final_tarde' => $dados['hora_limite_final_tarde'],
            'idusuario' => $user->idusuario
        ]);

        $final = $sala->verificaAlteracaoItens($itens_reuniao, $itens_coworking, $participantes, $periodos);
        if(isset($final['gerente']))
        {
            $gerente = $final['gerente'];
            Mail::to($gerente->email)->queue(new SalaReuniaoMail($sala, $final['itens']));
            event(new CrudEvent('para ' . $gerente->nome . ' (gerente da seccional ' . $sala->regional->regional.') após alteração de itens da sala de reunião', 'envio de e-mail', $id));
        }
        
        event(new CrudEvent('sala de reunião / coworking', 'editou', $id));
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
        if(!in_array($tipo, ['reuniao', 'coworking']))
            return null;

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
            {
                // verifica agendamentos que criou
                $periodos = $user->getPeriodoByDia($dia, $periodos);

                // verifica agendamentos como participante
                if($user->tipoPessoa() == 'PF'){
                    foreach($periodos as $chave => $valor){
                        if(!empty($this->site()->participantesVetados($dia, $valor, [apenasNumeros($user->cpf_cnpj)])))
                            unset($periodos[$chave]);
                    }
                }
            }

            if(!empty($periodos))
            {
                $final['horarios'] = $periodos;
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

    public function getTodasHorasById($id)
    {
        return SalaReuniao::findOrFail($id)->getTodasHoras();
    }

    public function getHorarioFormatadoById($id, $arrayHorarios, $final_manha = null, $final_tarde = null)
    {
        $sala = SalaReuniao::findOrFail($id);

        if(isset($final_manha))
            $sala->hora_limite_final_manha = $final_manha;

        if(isset($final_tarde))
            $sala->hora_limite_final_tarde = $final_tarde;

        $horarios = $sala->formatarHorariosAgendamento($arrayHorarios);

        return SalaReuniao::getFormatHorariosHTML($horarios);
    }
    
    public function site()
    {
        return resolve('App\Contracts\SalaReuniaoSiteSubServiceInterface');
    }

    public function agendados()
    {
        return resolve('App\Contracts\AgendamentoSalaSubServiceInterface');
    }

    public function bloqueio()
    {
        return resolve('App\Contracts\SalaReuniaoBloqSubServiceInterface');
    }

    public function suspensaoExcecao()
    {
        return resolve('App\Contracts\SuspensaoExcecaoSubServiceInterface');
    }
}