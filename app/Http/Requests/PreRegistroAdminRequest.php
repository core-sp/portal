<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class PreRegistroAdminRequest extends FormRequest
{
    private $service;
    private $msg;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    protected function prepareForValidation()
    {
        $this->msg = '';
        $preRegistro = $this->service->getService('PreRegistro')->view($this->preRegistro)['resultado'];
        $arrayStatus = [
            'aprovar' => $preRegistro::STATUS_APROVADO,
            'negar' => $preRegistro::STATUS_NEGADO,
            'corrigir' => $preRegistro::STATUS_CORRECAO,
        ];
        if(isset($arrayStatus[$this->situacao]))
            $status = $arrayStatus[$this->situacao];
        else
            return;

        $texto = $status != $preRegistro::STATUS_APROVADO ? 'Não possui' : 'Possui';
        $temp = $status == $preRegistro::STATUS_CORRECAO ? 'enviado para correção' : strtolower($status);
        $anexosOk = true;

        // Validação do anexo
        if($status == $preRegistro::STATUS_APROVADO)
        {
            $tipos = $preRegistro->anexos->first()->getObrigatoriosPreRegistro();
            $anexos = $preRegistro->getConfereAnexosArray();
            
            if(count($anexos) > 0)
                foreach($anexos as $key => $value)
                    if(in_array($key, $tipos))
                        unset($tipos[array_search($key, $tipos)]);

            $anexosOk = count($tipos) == 0;
            if(!$anexosOk)
            {
                $this->merge([
                    'status' => ''
                ]);  
                $this->msg = 'Faltou confirmar a entrega dos anexos';
                return;
            }
        }

        // Validação do registro RT e justificativa
        $verificaJustificativa = false;
        $verificaRegistro = false;
        if($status == $preRegistro::STATUS_APROVADO)
        {
            $verificaJustificativa = !isset($preRegistro->justificativa);
            $verificaRegistro = $preRegistro->userExterno->isPessoaFisica() || (!$preRegistro->userExterno->isPessoaFisica() && $preRegistro->pessoaJuridica->canUpdateStatus());
            if(!$verificaRegistro)
            {
                $this->merge([
                    'status' => ''
                ]);  
                $this->msg = 'Faltou inserir o registro do Responsável Técnico';
                return;
            }
        }
        else
            $verificaJustificativa = $status == $preRegistro::STATUS_NEGADO ? isset($preRegistro->getJustificativaArray()['negado']) : 
                (isset($preRegistro->justificativa) && !isset($preRegistro->getJustificativaArray()['negado']));

        if(!$verificaJustificativa)
        {
            $this->merge([
                'status' => ''
            ]);  
            $this->msg = $texto . ' justificativa(s)';
            return;
        }

        // Validação do status se pode atualizar
        $statusOK = in_array($preRegistro->status, [$preRegistro::STATUS_ANALISE_INICIAL, $preRegistro::STATUS_ANALISE_CORRECAO]);
        if(!$statusOK)
        {
            $this->merge([
                'status' => ''
            ]);  
            $this->msg = 'Não possui o status necessário para ser ' . $temp;
            return;
        }

        // Se não houver nenhum erro, preenche o status com o status requisitado
        $this->merge([
            'status' => $arrayStatus[$this->situacao]
        ]);
    }

    public function rules()
    {
        return [
            'situacao' => 'required|in:aprovar,negar,corrigir',
            'status' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'situacao.required' => 'Obrigatório o status requisitado',
            'situacao.in' => 'Valor do status requisitado inválido',
            'status.required' => $this->msg,
        ];
    }
}
