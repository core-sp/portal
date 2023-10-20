<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use App\Rules\Cpf;
use App\Repositories\GerentiRepositoryInterface;

class CursoInscricaoRequest extends FormRequest
{
    private $service;
    private $gerentiRepository;

    public function __construct(MediadorServiceInterface $service, GerentiRepositoryInterface $gerentiRepository)
    {
        $this->service = $service;
        $this->gerentiRepository = $gerentiRepository;
    }

    protected function prepareForValidation()
    {
        if(\Route::is('cursos.inscricao') && auth()->guard('representante')->check())
        {
            $user_rep = auth()->guard('representante')->user();
            $dados = $this->service->getService('Representante')->getDadosInscricaoCurso($user_rep, $this->gerentiRepository);
            $this->merge([
                'cpf' => $user_rep->cpf_cnpj,
                'nome' => $user_rep->nome,
                'email' => $user_rep->email,
                'telefone' => $dados['user_rep']->telefone,
                'registrocore' => $dados['user_rep']->registro_core,
                'situacao' => $dados['situacao'],
            ]);
        }

        if(\Route::is('cursos.inscricao'))
            $this->merge(['ip' => request()->ip(), 'tipo_inscrito' => 'Site']);
    }

    public function rules()
    {
        $unique = 'unique:curso_inscritos,cpf,NULL,idcurso,idcurso,'.$this->idcurso.',deleted_at,NULL';
        $regras = [
            'cpf' => [
                'required', 
                'max:191', 
                $unique, 
                new Cpf
            ],
            'nome' => 'required|min:5|max:191|regex:/^[\sa-zA-ZáéíóúýÁÉÍÓÚÝâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÿÄËÏÖÜŸçÇ]*$/',
            'telefone' => 'required|max:18|regex:/(\([0-9]{2}\))\s([0-9]{4,5})\-([0-9]{4,5})/',
            'email' => 'required|email|max:191',
            'registrocore' => 'nullable|max:191',
            'tipo_inscrito' => 'required|in:' . implode(',', $this->service->getService('Curso')->inscritos()->tiposInscricao()),
        ];

        if(\Route::is('inscritos.update.presenca'))
            return [
                'presenca' => 'required|in:Sim,Não',
            ];
            
        if(\Route::is('cursos.inscricao') && auth()->guard('representante')->check())
            return [
                'nome' => '', 'telefone' => '', 'email'=> '', 'registrocore' => '', 'ip' => '',
                'cpf' => 'required|' . $unique,
                'termo' => 'required|accepted',
                'situacao' => '',
            ];

        if(\Route::is('cursos.inscricao'))
            return array_merge($regras, ['termo' => 'required|accepted', 'ip' => '']);

        if(isset($this->idcurso))
            return $regras;

        // Não pode editar CPF
        unset($regras['cpf']);
        return $regras;
        
    }

    public function messages()
    {
        return [
            'cpf.unique' => 'Este CPF já está cadastrado para o curso',
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'telefone.regex' => 'Telefone no formato inválido',
            'email' => 'Digite um email válido',
            'in' => 'Valor inválido',
            'accepted' => 'Você deve concordar com o Termo de Consentimento',
            'nome.regex' => 'Nome inválido',
            'min' => 'O :attribute tem menos que o mínimo de :min caracteres',
        ];
    }
}
