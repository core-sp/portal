<?php

namespace App\Http\Controllers;

use Redirect;
use App\Regional;
use App\Rules\Cpf;
use App\Agendamento;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\Helper;
use App\Mail\AgendamentoMailGuest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Repositories\AgendamentoRepository;
use App\Http\Requests\AgendamentoSiteRequest;
use App\Http\Requests\AgendamentoUpdateRequest;
// use App\Repositories\AgendamentoBloqueioRepository;
use App\Http\Requests\AgendamentoSiteCancelamentoRequest;
use App\Contracts\MediadorServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class AgendamentoSiteController extends Controller
{
    private $agendamentoRepository;
    // private $agendamentoBloqueioRepository;
    private $service;

    public function __construct(AgendamentoRepository $agendamentoRepository, /*AgendamentoBloqueioRepository $agendamentoBloqueioRepository, */MediadorServiceInterface $service) {
        $this->agendamentoRepository = $agendamentoRepository;
        // $this->agendamentoBloqueioRepository = $agendamentoBloqueioRepository;
        $this->service = $service;
    }

    public function formView()
    {
        try{
            $dados = $this->service->getService('Agendamento')->viewSite($this->service);
            $regionais = $dados['regionais'];
            $pessoas = $dados['pessoas'];
            $servicos = $dados['servicos'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os dados para o agendamento.");
        }

        // $regionais = $this->service->getService('Regional')->getRegionaisAgendamento();
        // $pessoas = Agendamento::TIPOS_PESSOA;
        // $servicos = Agendamento::servicos();

        // if(!$this->service->getService('PlantaoJuridico')->plantaoJuridicoAtivo())
        //     unset($servicos[array_search(Agendamento::SERVICOS_PLANTAO_JURIDICO, $servicos)]);        

        return view('site.agendamento', compact('regionais', 'pessoas', 'servicos'));
    }

    public function consultaView()
    {
        return view('site.agendamento-consulta');
    }

    public function consulta()
    {
        $protocolo = IlluminateRequest::input('protocolo');

        if (!empty($protocolo)) {
            $busca = true;
        } 
        else {
            $busca = false;
        }

        $protocolo = 'AGE-'.$protocolo;
        
        $resultado = $this->agendamentoRepository->getToConsulta($protocolo);

        return view('site.agendamento-consulta', compact('resultado', 'busca'));
    }

    public function store(/*AgendamentoSiteRequest*/AgendamentoUpdateRequest $request)
    {
        // try{
        //     $dados = $this->service->getService('Agendamento')->viewSite($this->service);
        //     $regionais = $dados['regionais'];
        //     $pessoas = $dados['pessoas'];
        //     $servicos = $dados['servicos'];
        // } catch (\Exception $e) {
        //     \Log::error($e->getMessage());
        //     abort(500, "Erro ao carregar os dados para o agendamento.");
        // }

        $request->validated();

        // // Trabalhando com o formato de data Y-m-d por questões de padronização no banco de dados
        // $dia = date('Y-m-d', strtotime(str_replace('/', '-', $request->dia)));
        // $diaAtual = date('Y-m-d');
        
        // // Validação para evitar agendamento no passado
        // if($dia <= $diaAtual) {
        //     abort(500, 'Não é permitido criar agendamento no passado.');
        // }

        // // Limita em até um agendamento por CPF por dia/horário
        // if($this->agendamentoRepository->getCountAgendamentoPendenteByCpfDayHour($dia, $request->hora, $request->cpf) > 0) {
        //     abort(500, 'É permitido apenas 1 agendamentos por CPF por dia/horário!');
        // } 

        // if(stristr($request->toModel()['tiposervico'], Agendamento::SERVICOS_PLANTAO_JURIDICO))
        // {
        //     $plantao = $this->service->getService('PlantaoJuridico')->getPlantaoAtivoComBloqueioPorRegional($request->idregional);

        //     // se existe a regional
        //     abort_if(!isset($plantao), 500, 'A regional escolhida não é válida para o serviço '.Agendamento::SERVICOS_PLANTAO_JURIDICO);
            
        //     // se existe a data
        //     $diaValido = $this->service->getService('PlantaoJuridico')->validacaoAgendarPlantao($plantao, $dia);
        //     abort_if(!$diaValido, 500, 'A data para a regional escolhida não é válida para o serviço '.Agendamento::SERVICOS_PLANTAO_JURIDICO);

        //     // se está lotado
        //     $agendados = $this->agendamentoRepository->getPlantaoJuridicoPorPeriodo($request->idregional, $dia, $dia);
        //     $estaLivre = $this->service->getService('PlantaoJuridico')->validacaoAgendarPlantao($plantao, $dia, $agendados);
        //     abort_if(!$estaLivre, 500, 'A data escolhida para a regional está indiponível atualmente para o serviço '.Agendamento::SERVICOS_PLANTAO_JURIDICO);

        //     // se existe o horário
        //     $agendados = $this->agendamentoRepository->getPlantaoJuridicoByRegionalAndDia($request->idregional, $dia);
        //     $horaValida = $this->service->getService('PlantaoJuridico')->validacaoAgendarPlantao($plantao, $dia, $agendados, $request->hora);
        //     abort_if(!$horaValida, 500, 'A hora para a regional escolhida não é válida para o serviço '.Agendamento::SERVICOS_PLANTAO_JURIDICO);

        //     // se já possui um agendamento no periodo do plantao
        //     $total = $this->agendamentoRepository->countPlantaoJuridicoByCPF($request->cpf, $request->idregional, $plantao);
        //     abort_if($total > 0, 500, 'Durante o período deste plantão jurídico é permitido apenas 1 agendamento por cpf');
        // }else
        // {
        //     // Validação se regional está aceitando agendamentos
        //     if(!$this->permiteAgendamento($dia, $request->hora, $request->idregional)) {
        //         abort(500);
        //     }
            
        //     // Limita em até dois agendamentos por CPF por dia
        //     if($this->limiteCpf($dia, $request->cpf)) {
        //         abort(500, 'É permitido apenas 2 agendamentos por CPF por dia!');
        //     }
            
        //     // Cria bloqueio caso o usuário tenha faltado 3 vezes nos últimos 90 dias
        //     if($this->bloqueioPorFalta($request->cpf)) {
        //         abort(405, 'Agendamento bloqueado por excesso de falta nos últimos 90 dias. Favor entrar em contato com o Core-SP para regularizar o agendamento.');
        //     }
        // }
        
        // // Gera a HASH (protocolo) aleatória
        // $characters = 'ABCDEFGHIJKLMNOPQRSTUVXZ0123456789';
        // do {
        //     $protocoloGerado = substr(str_shuffle($characters), 0, 6);
        //     $protocoloGerado = 'AGE-'.$protocoloGerado;
        //     $countProtocolo = $this->agendamentoRepository->checkProtocol($protocoloGerado);
        // } while($countProtocolo != 0);

        // $request->protocolo = $protocoloGerado;

        // $save = $this->agendamentoRepository->store($request->toModel());
        
        // if(!$save) {
        //     abort(500);
        // }

        // $termo = $save->termos()->create([
        //     'ip' => request()->ip()
        // ]);
            
        // // Gera evento de agendamento
        // $string = $save->nome . " (CPF: " . $save->cpf . ")";
        // $string .= " *agendou* atendimento em *" . $save->regional->regional;
        // $string .= "* no dia " . onlyDate($save->dia) . " e " .$termo->message();
        // event(new ExternoEvent($string));
        
        // // Enviando email de agendamento
        // $email = new AgendamentoMailGuest($save);
        // Mail::to($save->email)->queue($email);

        // // Reaproveita o corpo do email para mostrar na tela de agradecimento
        // $agradece = $email->body;
        // $adendo = '<i>* As informações serão enviadas ao email cadastrado no formulário</i>';

        // Retorna view de agradecimento
        return view('site.agradecimento')/*->with([
            'agradece' => $agradece,
            'adendo' => $adendo
        ])*/;
    }

    public function cancelamento(AgendamentoSiteCancelamentoRequest $request)
    {
        $request->validated();

        $agendamento = $this->agendamentoRepository->getById($request->idagendamento);

        // Checagem se o CPF do Agendamentoé o mesmo fornecido, caso não seja, é retornada uma mensagem de erro
        if($agendamento->cpf != $request->cpf) {
            return redirect('/agendamento-consulta')
                ->with('message', '<i class="icon fa fa-ban"></i>O CPF informado não corresponde ao protocolo. Por favor, pesquise novamente o agendamento')
                ->with('class', 'alert-danger');
        } 
        else {
            $now = date('Y-m-d');

            // Agendamento deve ser cancelado com antecedência, não é permitido cancelar no mesmo dia do Agendamento
            if($now < $agendamento->dia) {
                $update = $this->agendamentoRepository->update($agendamento->idagendamento, ['status' => Agendamento::STATUS_CANCELADO], $agendamento);

                if(!$update) {
                    abort(500);
                }
                    
                // Gera evento de agendamento
                $string = $agendamento->nome . " (CPF: " . $agendamento->cpf . ")";
                $string .= " *cancelou* atendimento em *" . $agendamento->regional->regional;
                $string .= "* no dia " . onlyDate($agendamento->dia);
                event(new ExternoEvent($string));

                // Gera mensagem de agradecimento
                $agradece = "Agendamento cancelado com sucesso!";

                return view('site.agradecimento')->with('agradece', $agradece);
            } 
            // Caso cancelamento seja no mesmo dia, uma mensagem de erro é retornada
            else {
                return redirect('/agendamento-consulta')
                    ->with('message', '<i class="icon fa fa-ban"></i>Não é possível cancelar o agendamento no dia do atendimento')
                    ->with('class', 'alert-danger');
            }
        }
    }

    public function permiteAgendamento($dia, $hora, $idregional)
    {
        // Recupera os agendamentos de acordo com dia/horário/regional
        $agendamentos = $this->agendamentoRepository->getAgendamentoPendeteByDiaHoraRegional($dia, $hora, $idregional);

        // Se contagem for zero, não há nenhum agendamento no dado dia/horário/regional
        if($agendamentos->count() == 0) {

            // Não tendo nenhum agendamento, é necessário realizar uma query para verificar o número de agendamentos por horário da regional
            // Se o número de agendamentos por horário da regional for maior que zero, um agendamento pode ser criado, caso contrário não
            return $this->service->getService('Regional')->getAgeporhorarioById($idregional) > 0;
        }
        else {

            // A query do agendamento traz junto informação de sua região. Com isso verificamos se a contagem de agendamento no dado dia/horário/regional
            // é menor que o número de agendamentos por horário da regional, se sim o agendamento pode ser criado, caso contrário não       
            return $agendamentos->count() < $agendamentos->first()->regional->ageporhorario;
        }
    }

    protected function bloqueioPorFalta($cpf)
    {
        return $this->agendamentoRepository->getCountAgendamentoNaoCompareceuByCpf($cpf) >= 3;
    }

    public function limiteCPF($dia, $cpf)
    {
        return $this->agendamentoRepository->getCountAgendamentoPendenteByCpfDay($dia, $cpf) >= 2;
    }

    // public function checaHorariosMarcados($dia, $idregional)
    // {
    //     $agendamentos = $this->agendamentoRepository->getAgendamentoPendenteByDiaRegional($dia, $idregional);
    //     $horariosMarcados = [];

    //     // Caso exista algum horário já agendado no dia e a regional permita agendamentos, montamos um array com todos os horários marcados
    //     if($agendamentos->count() > 0) {
    //         $agedamentoPorHorario = $agendamentos->first()->regional->ageporhorario;

    //         if($agedamentoPorHorario >= 1) {
    //             foreach($agendamentos as $agendamento) {
    //                 array_push($horariosMarcados,$agendamento->hora);
    //             }
    //         }
    //     }
 
    //     return $horariosMarcados;
    // }

    // public function checaHorarios(Request $request)
    // {
    //     try{
    //         $validate = $request->only('idregional', 'servico', 'dia');
    //         $horarios = $this->service->getService('Agendamento')->getDiasHorasAjaxSite($validate, $this->service);
    //     } catch (\Exception $e) {
    //         \Log::error($e->getMessage());
    //         abort(500, "Erro ao carregar os dados para o agendamento via ajax.");
    //     }

    //     // $idregional = $request->idregional;
    //     // $dia = date('Y-m-d', strtotime(str_replace('/', '-', $request->dia)));
    //     // $servico = $request->servico;
    //     // $horarios = [];

    //     // if($servico == Agendamento::SERVICOS_PLANTAO_JURIDICO)
    //     // {
    //     //     $plantao = $this->service->getService('PlantaoJuridico')->getPlantaoAtivoComBloqueioPorRegional($idregional);
    //     //     if(isset($plantao))
    //     //     {
    //     //         $agendados = $this->agendamentoRepository->getPlantaoJuridicoByRegionalAndDia($idregional, $dia);
    //     //         $horarios = $this->service->getService('PlantaoJuridico')->removeHorariosSeLotado($agendados, $plantao, $dia); 
    //     //     }
    //     // }
    //     // else
    //     // {
    //     //     // Recupera quantos agendamentos podem ser criados por horário de acordo com a regional
    //     //     $agedamentoPorHorario = $this->service->getService('Regional')->getAgeporhorarioById($idregional);

    //     //     // Se podemos criar agendamentos, contamos quantos agendamentos já estão marcados por horário.
    //     //     if($agedamentoPorHorario > 0) {
    //     //         $horarios = $this->service->getService('Regional')->getHorariosAgendamento($idregional, $dia);
    //     //         $horariosMarcados = $this->checaHorariosMarcados($dia,$idregional);
    //     //         $contagemAgendamentosMarcados = array_count_values($horariosMarcados);

    //     //         foreach($contagemAgendamentosMarcados as $hora => $contagem) {
                    
    //     //             // Caso a contagem de agendamentos marcados por horário seja maior ou igual ao agendamento por horário da regional
    //     //             // o horário em questão deve ser removido da lista de horários disponíveis
    //     //             if($contagem >= $agedamentoPorHorario) {
    //     //                 unset($horarios[array_search($hora, $horarios)]);
    //     //             }
    //     //         }
    //     //     }
    //     // }

    //     return response()->json($horarios);
    // }

    /**
     * Função usada para auxiliar o calendário de agendamento no Portal. Verifica quais dias entre d+1 ~ d+m
     * não possuem horários disponíveis. Retorna um array de dias lotados para interface gráfica.
     */
    public function getDiasHorasAjax(Request $request)
    {
        try{
            $validate = $request->only('idregional', 'servico', 'dia');
            $dados = $this->service->getService('Agendamento')->getDiasHorasAjaxSite($validate, $this->service);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os dados para o agendamento via ajax.");
        }

        // $idregional = $request->idregional;
        // $servico = $request->servico;

        // // Variável retornada pela função
        // $diasLotados = [];

        // // if($servico == Agendamento::SERVICOS_PLANTAO_JURIDICO)
        // // {
        // //     $plantao = $this->service->getService('PlantaoJuridico')->getPlantaoAtivoComBloqueioPorRegional($idregional);
        // //     if(isset($plantao))
        // //     {
        // //         $inicial = Carbon::parse($plantao->dataInicial)->lte(Carbon::today()) ? Carbon::tomorrow()->format('Y-m-d') : $plantao->dataInicial;
        // //         $agendados = $this->agendamentoRepository->getPlantaoJuridicoPorPeriodo($idregional, $inicial, $plantao->dataFinal);
        // //         $diasLotados = $this->service->getService('PlantaoJuridico')->getDiasSeLotado($agendados, $plantao); 
        // //     }
        // // }else
        // // {
            
        //     // Recupera o número de agendamentos para cada dia entre d+1 d+m
        //     $contagemAgendamentos = $this->agendamentoRepository->getAgendamentoPendenteByMesRegional($idregional);

        //     // Recupera dados da regional
        //     $regional = $this->service->getService('Regional')->getById($idregional);
        //     $agedamentoPorHorario = $regional->ageporhorario;

        //     // Recupera bloqueios ativos para a regional
        //     $bloqueios = $this->service->getService('Agendamento')->getByRegional($idregional);

        //     $date = date('Y-m-d', strtotime('+1 day'));
        //     $endDate = date('Y-m-d', strtotime('+1 month'));

        //     // Iteramos d+1 ~ d+m, verificando quais dias não possuem horários disponíveis
        //     while(strtotime($date) <= strtotime($endDate)) {  
                
        //         // Recupera os possíveis horários de agendamento da regional
        //         $horarios = $regional->horariosAge();

        //         // Verifica se existe bloqueios para a regional
        //         if($bloqueios->count() != 0 && $horarios) {
        //             foreach($bloqueios as $bloqueio) {
        //                 if($date >= $bloqueio->diainicio && $date <=$bloqueio->diatermino) {
        //                     foreach($horarios as $key => $horario) {
        //                         if($horario >= $bloqueio->horainicio && $horario <= $bloqueio->horatermino) {
        //                             // Caso exista bloqueios válidos, remove-se horários informados no bloqueio
        //                             unset($horarios[$key]);
        //                         }
        //                     }
        //                 }
        //             }
        //         }

        //         // Verificando se existe contagem de agendamento para o dia
        //         if($contagemAgendamentos->contains('dia', '=', $date)) {

        //             // Recupera informações sobre a contagem de agendamento
        //             $contagem = $contagemAgendamentos->filter(function ($contagemAgendamento) use ($date) {
        //                 return $contagemAgendamento->dia == $date;
        //             })->first();


        //             // Caso a contagem de agendamento seja igual ou maior ao [(número de agendamentos por horário) * (horários disponíveis para agendamento)]
        //             // o dia em questão é considerado lotado e inserido no array de retorno dessa função
        //             if($contagem->total >= $agedamentoPorHorario * count($horarios)) {
        //                 $timestamp = strtotime($contagem->dia);
        //                 array_push($diasLotados, array(date('m', $timestamp), date('d', $timestamp), 'lotado'));
        //             }
        //         }

        //         // Se nenhuma contagem de agendamento existe para o dia, verifica se existe disponibilidade de horários nessa regional.
        //         // Verificação necessária caso exista bloqueios que impendem agendamento em todos os horários
        //         else {
        //             if(count($horarios) == 0) {
        //                 $timestamp = strtotime($date);
        //                 array_push($diasLotados, array(date('m', $timestamp), date('d', $timestamp), 'lotado'));
        //             }
        //         }

        //         $date = date('Y-m-d', strtotime("+1 day", strtotime($date)));
        //     }
        // }

        return response()->json($dados);
    }

    public function regionaisPlantaoJuridico()
    {
        try{
            $dados = $this->service->getService('PlantaoJuridico')->getRegionaisAtivas();
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os dados para o agendamento via ajax.");
        }

        return response()->json($dados);
    }

    // public function datasPlantaoJuridico(Request $request)
    // {
    //     try{
    //         $dados = $this->service->getService('PlantaoJuridico')->getRegionaisAtivas();
    //     } catch (\Exception $e) {
    //         \Log::error($e->getMessage());
    //         abort(500, "Erro ao carregar os dados para o agendamento via ajax.");
    //     }
    //     $idregional = $request->idregional;
    //     $plantao = $this->service->getService('PlantaoJuridico')->getPlantaoAtivoComBloqueioPorRegional($idregional);
    //     $datas = array();

    //     if(isset($plantao))
    //     {
    //         $inicial = Carbon::parse($plantao->dataInicial);
    //         $final = Carbon::parse($plantao->dataFinal);
    //         $hoje = Carbon::today();

    //         $datas = [
    //             $inicial->gt($hoje) ? $plantao->dataInicial : null, 
    //             $final->gt($hoje) ? $plantao->dataFinal : null
    //         ];
    //     }

    //     return response()->json($datas);
    // }
}
