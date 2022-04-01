<?php

namespace App\Http\Requests;

use App\Contracts\MediadorServiceInterface;
use App\Rules\Cpf;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AgendamentoUpdateRequest extends FormRequest
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    public function rules()
    {
        $service = $this->service->getService('Agendamento');
        $completos = $service->getServicosOrStatusOrCompletos('completos');
        $status = $service->getServicosOrStatusOrCompletos('status');
        $servicos = $service->getServicosOrStatusOrCompletos('servicos');
        $horariosComBloqueio = '';
        $dateFormat = '';
        if(\Route::is('agendamentosite.store'))
        {
            $horariosComBloqueio = '|in:'.implode(',', $service->getDiasHorasAjaxSite([
                'idregional' => request()->idregional, 
                'dia' => request()->dia,
                'servico' => request()->servico
            ], $this->service));

            $dateFormat = '|date_format:d/m/Y|after:'.date('d\/m\/Y').'|before_or_equal:'.Carbon::tomorrow()->addDays(30)->format('d\/m\/Y');
        }

        return [
            'antigo' => 'sometimes|boolean',
            'idregional' => 'sometimes|exclude_if:antigo,0|exclude_if:antigo,1|exists:regionais,idregional',
            'nome' => 'sometimes|exclude_if:antigo,1|required|min:5|max:191|string',
            'email' => 'sometimes|exclude_if:antigo,1|required|email|max:191',
            'cpf' => ['sometimes', 'exclude_if:antigo,1', 'required', 'max:14', new Cpf],
            'celular' => 'sometimes|exclude_if:antigo,1|required|max:17',
            'servico' => 'sometimes|required|in:'.implode(',', $servicos),
            'tiposervico' => 'sometimes|required|in:'.implode(',', $completos),
            'pessoa' => 'sometimes|required|in:PF,PJ,PF e PJ',
            'idusuario' => 'sometimes|nullable|exists:users,idusuario|required_if:status,==,'.$status[0],
            'status' => 'sometimes|nullable|in:'.implode(',', $status),
            'dia' => 'sometimes|exclude_if:antigo,0|exclude_if:antigo,1|required_without_all:antigo'.$dateFormat,
            'hora' => 'sometimes|exclude_if:antigo,0|exclude_if:antigo,1|required_without_all:antigo'.$horariosComBloqueio,
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
            'after' => 'Deve ser uma data após o dia de hoje',
            'before_or_equal' => 'Deve ser uma data anterior ou igual a '.Carbon::tomorrow()->addDays(30)->format('d\/m\/Y')
        ];
    }
}
