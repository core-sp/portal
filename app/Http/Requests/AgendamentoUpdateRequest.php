<?php

namespace App\Http\Requests;

use App\Contracts\MediadorServiceInterface;
use App\Rules\Cpf;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AgendamentoUpdateRequest extends FormRequest
{
    private $service;
    private $horariosComBloqueio;
    private $dateFormat;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    private function fillArrayRules($service)
    {
        if(\Route::is('agendamentosite.store'))
        {
            $this->dateFormat = '|date_format:d/m/Y|after:'.date('d\/m\/Y');
            if(request()->filled('idregional') && request()->has('servico') && (request()->servico == 'Plantão Jurídico'))
            {
                $datasPJ = $service->getDiasHorasAjaxSite(['idregional' => request()->idregional], $this->service);
                if(!isset($datasPJ[0]) && !isset($datasPJ[1]))
                    request()->dia = Carbon::today()->subDay()->format('d\/m\/Y');
                else{
                    if(isset($datasPJ[0]))
                        $this->dateFormat = $this->dateFormat.'|after_or_equal:'.onlyDate($datasPJ[0]);
                    if(isset($datasPJ[1]))
                        $this->dateFormat = $this->dateFormat.'|before_or_equal:'.onlyDate($datasPJ[1]);
                }
            }else
                $this->dateFormat = $this->dateFormat.'|before_or_equal:'.Carbon::tomorrow()->addDays(30)->format('d\/m\/Y');

            $this->horariosComBloqueio = request()->filled('idregional') && request()->filled('dia') && request()->filled('servico') ? 
            '|in:'.implode(',', $service->getDiasHorasAjaxSite([
                    'idregional' => request()->idregional, 
                    'dia' => request()->dia,
                    'servico' => request()->servico
                ], $this->service)) : $this->horariosComBloqueio.'|in:';
        }
    }

    protected function prepareForValidation()
    {
        if(\Route::is('agendamentosite.store'))
        {
            if(request()->missing('dia'))
                $this->merge(['dia' => null]);
            if(request()->missing('hora'))
                $this->merge(['hora' => null]);
        }
    }

    public function rules()
    {
        $service = $this->service->getService('Agendamento');
        $completos = $service->getServicosOrStatusOrCompletos('completos');
        $status = $service->getServicosOrStatusOrCompletos('status');
        $servicos = $service->getServicosOrStatusOrCompletos('servicos');
        $this->fillArrayRules($service);
        unset($this->service);
        unset($service);

        return [
            'antigo' => 'sometimes|boolean',
            'idregional' => 'sometimes|exclude_if:antigo,0|exclude_if:antigo,1|required_without_all:antigo|exists:regionais,idregional',
            'nome' => 'sometimes|exclude_if:antigo,1|required|min:5|max:191|string',
            'email' => 'sometimes|exclude_if:antigo,1|required|email|max:191',
            'cpf' => ['sometimes', 'exclude_if:antigo,1', 'required', 'max:14', new Cpf],
            'celular' => 'sometimes|exclude_if:antigo,1|required|max:17',
            'servico' => 'sometimes|required_without_all:tiposervico,antigo,idusuario,status,idagendamento|in:'.implode(',', $servicos),
            'tiposervico' => 'sometimes|required|in:'.implode(',', $completos),
            'pessoa' => 'sometimes|required|in:PF,PJ,PF e PJ',
            'idusuario' => 'sometimes|nullable|exists:users,idusuario|required_if:status,==,'.$status[0],
            'status' => 'sometimes|nullable|in:'.implode(',', $status),
            'dia' => 'sometimes|exclude_if:antigo,0|exclude_if:antigo,1|required_without_all:antigo'.$this->dateFormat,
            'hora' => 'sometimes|exclude_if:antigo,0|exclude_if:antigo,1|required_without_all:antigo'.$this->horariosComBloqueio,
            'termo' => 'sometimes|required|accepted',
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
            'idregional.exists' => 'Regional não existe',
            'hora.in' => 'Essa hora não está disponível',
            'pessoa.in' => 'Esse tipo de pessoa não está disponível',
            'date_format' => 'Formato de data inválido',
            'after' => 'Data fora do período permitido',
            'before_or_equal' => 'Data fora do período permitido',
            'after_or_equal' => 'Data fora do período permitido',
            'required_without_all' => 'Campo obrigatório'
        ];
    }
}
