<?php

namespace App\Services;

use App\Contracts\AgendamentoSiteSubServiceInterface;
use App\Contracts\MediadorServiceInterface;
use App\Agendamento;
use App\Events\ExternoEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgendamentoMailGuest;
use Exception;

class AgendamentoSiteSubService implements AgendamentoSiteSubServiceInterface {

    private $renameSede;

    public function __construct()
    {
        $this->renameSede = 'São Paulo - Avenida Brigadeiro Luís Antônio';
    }

    private function validarStore($dados, MediadorServiceInterface $service)
    {
        $dia = Carbon::createFromFormat('d/m/Y', $dados['dia'])->format('Y-m-d');
        $agendamentos = Agendamento::where('dia', $dia)
            ->where('cpf', $dados['cpf'])
            ->whereNull('status')
            ->get();

        $total = $agendamentos->where('hora', $dados['hora'])->count();
        if($total == 1)
            return [
                'message' => '<i class="icon fa fa-ban"></i>Este CPF já possui um agendamento neste dia e horário',
                'class' => 'alert-danger'
            ];

        $total = $agendamentos->count();
        if($total >= 2)
            return [
                'message' => '<i class="icon fa fa-ban"></i>É permitido apenas 2 agendamentos por cpf por dia',
                'class' => 'alert-danger'
            ];

        $total = Agendamento::where('cpf', $dados['cpf'])
            ->where('status', Agendamento::STATUS_NAO_COMPARECEU)
            ->whereBetween('dia', [Carbon::today()->subDays(90)->format('Y-m-d'), date('Y-m-d')])
            ->count();
        if($total >= 3)
            return [
                'message' => '<i class="icon fa fa-ban"></i>Agendamento bloqueado por excesso de falta nos últimos 90 dias. 
                Favor entrar em contato com o Core-SP para regularizar o agendamento.',
                'class' => 'alert-danger'
            ];

        if($dados['servico'] == Agendamento::SERVICOS_PLANTAO_JURIDICO)
        {
            $regional = $dados['object_regional'];
            $plantao = $regional->plantaoJuridico;
            $total = $regional->agendamentos()
                ->where('cpf', $dados['cpf'])
                ->where('tiposervico', 'LIKE', Agendamento::SERVICOS_PLANTAO_JURIDICO.'%')
                ->whereNull('status')
                ->whereBetween('dia', [$plantao->dataInicial, $plantao->dataFinal])
                ->count();
            if($total >= 1)
                return [
                    'message' => '<i class="icon fa fa-ban"></i>Durante o período deste plantão jurídico é permitido apenas 1 agendamento por cpf',
                    'class' => 'alert-danger'
                ];
        }

        $dados['dia'] = $dia;
        $dados['nome'] = mb_convert_case(mb_strtolower($dados['nome']), MB_CASE_TITLE);
        $dados['protocolo'] = Agendamento::getProtocolo();
        $dados['tiposervico'] = $dados['servico'].' para '.$dados['pessoa'];
        unset($dados['servico']);
        unset($dados['pessoa']);
        unset($dados['termo']);
        unset($dados['object_regional']);

        return $dados;
    }

    public function view(MediadorServiceInterface $service)
    {
        $regionais = $service->getService('Regional')->all()->whereNotIn('idregional', [14]);
        $regionais->find(1)->regional = $this->renameSede;

        $servicos = $service->getService('Agendamento')->getServicosOrStatusOrCompletos('servicos');

        if(!$service->getService('PlantaoJuridico')->plantaoJuridicoAtivo()) 
            unset($servicos[array_search(Agendamento::SERVICOS_PLANTAO_JURIDICO, $servicos)]);      

        return [
            'regionais' => $regionais->sortBy('regional'),
            'pessoas' => Agendamento::TIPOS_PESSOA,
            'servicos' => $servicos
        ];
    }

    public function consulta($dados)
    {
        $protocolo = 'AGE-'.strtoupper($dados['protocolo']);

        return Agendamento::with('regional')
            ->where('protocolo', $protocolo)
            ->where('dia', '>=', date('Y-m-d'))
            ->first();
    }

    public function cancelamento($dados)
    {
        if(!isset($dados['protocolo']))
            return [
                'message' => '<i class="icon fa fa-ban"></i>Protocolo não recebido. Faça a consulta do agendamento novamente.',
                'class' => 'alert-danger'
            ];

        $protocolo = 'AGE-'.strtoupper($dados['protocolo']);
        $agendamento = Agendamento::where('protocolo', $protocolo)
            ->where('dia', '>=', date('Y-m-d'))
            ->whereNull('status')
            ->first();

        if(!isset($agendamento))
            return [
                'message' => '<i class="icon fa fa-ban"></i>O protocolo não existe para agendamentos de hoje em diante',
                'class' => 'alert-danger'
            ];
        if($agendamento->cpf != $dados['cpf'])
            return [
                'message' => '<i class="icon fa fa-ban"></i>O CPF informado não corresponde ao protocolo. Por favor, pesquise novamente o agendamento',
                'class' => 'alert-danger'
            ];

        if(!$agendamento->isAfter())
            return [
                'message' => '<i class="icon fa fa-ban"></i>Cancelamento do agendamento deve ser antes do dia do atendimento',
                'class' => 'alert-danger'
            ];
            
        $update = $agendamento->update(['status' => Agendamento::STATUS_CANCELADO]);
        if(!$update)
            throw new Exception('Erro ao atualizar o status do agendamento', 500);

        $string = $agendamento->nome.' (CPF: '.$agendamento->cpf.') *cancelou* atendimento em *'.$agendamento->regional->regional;
        $string .= '* no dia '.onlyDate($agendamento->dia);
        event(new ExternoEvent($string));
                
        return 'Agendamento cancelado com sucesso!';            
    }

    public function save($dados, $ip, MediadorServiceInterface $service)
    {
        $valid = $this->validarStore($dados, $service);
        if(isset($valid['message']))
            return $valid;

        $agendamento = Agendamento::create($valid);
        $termo = $agendamento->termos()->create([
            'ip' => $ip
        ]);
        $agendamento->regional = $dados['object_regional'];

        $string = $agendamento->nome.' (CPF: '.$agendamento->cpf.') *agendou* atendimento em *'.$agendamento->regional->regional;
        $string .= '* no dia '.onlyDate($agendamento->dia).' para o serviço '.$agendamento->tiposervico.' e ' .$termo->message();
        event(new ExternoEvent($string));

        $email = new AgendamentoMailGuest($agendamento);
        Mail::to($agendamento->email)->queue($email);

        return [
            'agradece' => $email->body,
            'adendo' => '<i>* As informações serão enviadas ao email cadastrado no formulário</i>'
        ];
    }

    public function getDiasHorasAjax($dados)
    {
        $regional = $dados['regional'];
        if(isset($regional))
        {
            $resultado = !isset($dados['servico']) || ($dados['servico'] == Agendamento::SERVICOS_PLANTAO_JURIDICO) ? 
            $regional->plantaoJuridico()->with('bloqueios')->first() : $regional;
    
            if(!isset($dados['servico']) && !isset($dados['dia']))
            {
                $plantao = $resultado->qtd_advogados > 0 ? $resultado : null;
        
                if(isset($plantao))
                {
                    $inicial = Carbon::parse($plantao->dataInicial);
                    $final = Carbon::parse($plantao->dataFinal);

                    return [
                        $inicial->gt(Carbon::today()) ? $plantao->dataInicial : null,
                        $final->gt(Carbon::today()) ? $plantao->dataFinal : null
                    ];
                }
        
                return [];
            }
        
            if(isset($dados['dia']))
            {
                $dia = Carbon::createFromFormat('d/m/Y', $dados['dia'])->format('Y-m-d');
                return $resultado->removeHorariosSeLotado($dia);
            }
            
            return $resultado->getDiasSeLotado();
        }

        return null;
    }
}