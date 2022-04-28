<?php

namespace App\Http\Requests;

use App\Contracts\MediadorServiceInterface;
use App\Rules\Cpf;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AgendamentoRequest extends FormRequest
{
    private $service;
    private $horariosComBloqueio;
    private $dateFormat;
    private $completos;
    private $chaveStatus;
    private $servicos;
    private $chaveProtocolo;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    protected function prepareForValidation()
    {
        $service = $this->service->getService('Agendamento');
        $this->completos = $service->getServicosOrStatusOrCompletos('completos');
        $this->chaveStatus = $service->getServicosOrStatusOrCompletos('status');
        $this->servicos = $service->getServicosOrStatusOrCompletos('servicos');

        if(\Route::is('agendamentosite.consulta'))
        {
            $this->chaveProtocolo = '|size:6|not_regex:/[^A-Za-z0-9]/';
            if(request()->missing('protocolo') || !request()->filled('protocolo'))
                $this->merge(['protocolo' => null]);
        }
            
        if(\Route::is('agendamentosite.store'))
        {
            $this->dateFormat = '|date_format:d/m/Y';
            if(request()->filled('dia') && substr_count(request()->dia, "/") != 2)
            {
                $this->merge(['dia' => Carbon::tomorrow()->format('Y-m-d')]);
                return;
            }

            if(request()->missing('dia'))
                $this->merge(['dia' => null]);
            if(request()->missing('hora'))
                $this->merge(['hora' => null]);

            if(request()->filled('idregional') && request()->filled('dia') && request()->filled('servico'))
            {
                if(request()->servico == 'Plantão Jurídico')
                {
                    $datasPJ = $service->getDiasHorasAjaxSite(['idregional' => request()->idregional], $this->service);
                    if(empty($datasPJ) || (!isset($datasPJ[0]) && !isset($datasPJ[1])))
                        $this->merge(['idregional' => 0]);
                    if(isset($datasPJ[0]))
                        $this->dateFormat = $this->dateFormat.'|after_or_equal:'.onlyDate($datasPJ[0]);
                    if(isset($datasPJ[1]))
                        $this->dateFormat = $this->dateFormat.'|before_or_equal:'.onlyDate($datasPJ[1]);
                }else
                    $this->dateFormat = $this->dateFormat.'|after:'.date('d\/m\/Y').'|before_or_equal:'.Carbon::today()->addDays(30)->format('d\/m\/Y');

                $horarios = $service->getDiasHorasAjaxSite([
                    'idregional' => request()->idregional, 
                    'dia' => request()->dia,
                    'servico' => request()->servico
                ], $this->service);
                $this->horariosComBloqueio = isset($horarios) ? '|in:'.implode(',', $horarios) : '|in:';
            }
        }
    }

    public function rules()
    {        
        return [
            'antigo' => 'sometimes|boolean',
            'idregional' => 'sometimes|exclude_if:antigo,0|exclude_if:antigo,1|required_without_all:antigo|exists:regionais,idregional',
            'nome' => 'sometimes|exclude_if:antigo,1|required|min:5|max:191|string|regex:/^\D*$/',
            'email' => 'sometimes|exclude_if:antigo,1|required|email|max:191',
            'cpf' => ['sometimes', 'exclude_if:antigo,1', 'required', 'max:14', new Cpf],
            'celular' => 'sometimes|exclude_if:antigo,1|required|max:17|regex:/(\([0-9]{2}\))\s([0-9]{5})\-([0-9]{4})/',
            'servico' => 'sometimes|required_without_all:tiposervico,antigo,idusuario,status,idagendamento|in:'.implode(',', $this->servicos),
            'tiposervico' => 'sometimes|required|in:'.implode(',', $this->completos),
            'pessoa' => 'sometimes|required|in:PF,PJ,PF e PJ',
            'idusuario' => 'sometimes|nullable|exists:users,idusuario|required_if:status,==,'.$this->chaveStatus[0],
            'status' => 'sometimes|nullable|in:'.implode(',', $this->chaveStatus),
            'dia' => 'sometimes|exclude_if:antigo,0|exclude_if:antigo,1|required_without_all:antigo'.$this->dateFormat,
            'hora' => 'sometimes|exclude_if:antigo,0|exclude_if:antigo,1|required_without_all:antigo'.$this->horariosComBloqueio,
            'termo' => 'sometimes|required|accepted',
            'protocolo' => 'sometimes|exclude_if:antigo,0|exclude_if:antigo,1|required_without_all:antigo,idregional,nome,email,cpf,celular,servico,tiposervico,pessoa,idusuario,status,dia,hora,termo,idagendamento'.$this->chaveProtocolo,
            'idagendamento' => 'sometimes|required_without_all:nome,email,cpf,celular,servico,tiposervico,idusuario,antigo,dia,hora,pessoa,termo'
        ];
    }

    public function messages() 
    {
        return [
            'min' => 'O campo possui menos que :min caracteres',
            'max' => 'O campo excedeu o limite de :max caracteres',
            'required' => 'O campo é obrigatório',
            'email' => 'Email inválido',
            'idusuario.required_if' => 'Informe o atendente que realizou o atendimento',
            'status.in' => 'Opção inválida de status',
            'tiposervico.in' => 'Opção inválida de tipo de serviço',
            'servico.in' => 'Opção inválida de tipo de serviço',
            'idusuario.exists' => 'Usuário não existe',
            'string' => 'Deve ser um texto sem números',
            'accepted' => 'Você deve concordar com o Termo de Consentimento',
            'idregional.exists' => 'Regional não existe ou não está disponível',
            'hora.in' => 'Essa hora não está disponível',
            'pessoa.in' => 'Esse tipo de pessoa não está disponível',
            'date_format' => 'Formato de data inválido',
            'after' => 'Data fora do período permitido',
            'before_or_equal' => 'Data fora do período permitido',
            'after_or_equal' => 'Data fora do período permitido',
            'required_without_all' => 'Campo obrigatório',
            'protocolo.not_regex' => 'Formato inválido',
            'size' => 'Deve conter :size caracteres',
            'nome.regex' => 'Não é permitido números',
            'celular.regex' => 'Somente neste formato (00) 00000-0000',
        ];
    }
}
