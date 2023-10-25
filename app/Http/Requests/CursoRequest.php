<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use Carbon\Carbon;

class CursoRequest extends FormRequest
{
    private $service;
    private $hora_final_limite;
    private $hora_inicial_limite;
    private $hora_inicial_real_limite;
    private $hora_final_real_limite;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('Curso');
    }

    protected function prepareForValidation()
    {
        foreach ($this->all() as $key => $value) {
            if(in_array($key, ['datarealizacao', 'datatermino', 'inicio_inscricao', 'termino_inscricao']))
                $this->merge([$key => str_replace('T', ' ', $this->input($key))]);
        }

        if(Carbon::hasFormat($this->datarealizacao, 'Y-m-d H:i'))
            $this->hora_final_limite = Carbon::parse($this->datarealizacao)->subHours(2)->format('Y-m-d H:i');

        $this->hora_inicial_real_limite = Carbon::hasFormat($this->datarealizacao, 'Y-m-d H:i') ? 
        Carbon::parse($this->datarealizacao)->subDay()->format('Y-m-d H:i') : now()->format('Y-m-d 00:00');

        $this->hora_inicial_limite = Carbon::hasFormat($this->inicio_inscricao, 'Y-m-d H:i') ? 
        Carbon::parse($this->inicio_inscricao)->addDay()->format('Y-m-d 00:00') : now()->format('Y-m-d 00:00');

        $this->hora_final_real_limite = Carbon::hasFormat($this->datarealizacao, 'Y-m-d H:i') ? 
        Carbon::parse($this->datarealizacao)->addHour()->format('Y-m-d H:i') : now()->format('Y-m-d 00:00');

        if($this->add_campo == '0')
            $this->merge(['campo_rotulo' => null, 'campo_required' => '0']);
    }

    public function rules()
    {
        return [
            'tipo' => 'required|in:' . implode(',', $this->service->tipos()),
            'tema' => 'required|max:191',
            'idregional' => 'required|exists:regionais',
            'img' => 'nullable|max:191',
            'datarealizacao' => 'required|date_format:Y-m-d H:i|after:'.now()->format('Y-m-d 23:59'),
            'datatermino' => 'required|date_format:Y-m-d H:i|after_or_equal:' . $this->hora_final_real_limite,
            'inicio_inscricao' => 'nullable|date_format:Y-m-d H:i|before_or_equal:' . $this->hora_inicial_real_limite,
            'termino_inscricao' => 'required_with:inicio_inscricao|nullable|date_format:Y-m-d H:i|after:'.$this->hora_inicial_limite.'|before_or_equal:' . $this->hora_final_limite,
            'endereco' => 'required_unless:tipo,Live|max:191',
            'nrvagas' => 'required|numeric',
            'descricao' => 'required',
            'acesso' => 'required|in:' . implode(',', $this->service->acessos()),
            'publicado' => 'required|in:Sim,Não',
            'resumo' => 'required',
            'add_campo' => 'required|boolean',
            'campo_rotulo' => 'required_if:add_campo,1|nullable|in:' . implode(',', array_keys($this->service->rotulos())),
            'campo_required' => 'required|boolean'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'numeric' => 'O :attribute aceita apenas números',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'in' => 'Valor inválido',
            'exists' => 'Regional não existe',
            'date_format' => 'Formato de data inválido',
            'datarealizacao.after' => 'O curso deve iniciar após o dia de hoje',
            'datatermino.after_or_equal' => 'O curso deve terminar pelo menos 1h após a data de realização',
            'inicio_inscricao.before_or_equal' => 'A data inicial das inscrições deve ser 24h antes da data de realização do curso',
            'termino_inscricao.after' => 'A data final das inscrições deve ser de pelo menos no dia seguinte após a data inicial',
            'termino_inscricao.before_or_equal' => 'A data final das inscrições deve ser até 2 horas antes da data de realização',
            'endereco.required_unless' => 'Endereço é obrigatório exceto para "Live"',
            'required_with' => 'A data final das inscrições é obrigatória quando data inicial está preenchida',
            'boolean' => 'Campo com valor inválido',
            'campo_rotulo.required_if' => 'Campo obrigatório se for adicionar campo',
        ];
    }

    public function attributes()
    {
        return [
            'add_campo' => 'adicionar campo',
            'campo_rotulo' => 'tipo do campo adicional',
            'campo_required' => 'campo adicional obrigatório',
        ];
    }
}
