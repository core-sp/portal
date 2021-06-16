<?php

namespace App\Http\Requests;

use App\BdoOportunidade;
use App\Rules\Cnpj;
use Illuminate\Foundation\Http\FormRequest;

class AnunciarVagaRequest extends FormRequest
{
    public function rules()
    {
        return [
            'idempresa' => 'required',
            'razaosocial' => 'required_if:idempresa,!=,0|max:191',
            'fantasia' => 'required_if:idempresa,!=,0|max:191',
            'cnpj' => ['required', 'max:191', new Cnpj],
            'segmento' => 'required_if:idempresa,!=,0',
            'endereco' => 'required_if:idempresa,!=,0|max:191',
            'telefone' => 'required_if:idempresa,!=,0|max:191',
            'site' => 'required_if:idempresa,!=,0|max:191',
            'email' => 'required_if:idempresa,!=,0|max:191',
            'titulo' => 'required|max:191',
            'segmentoOportunidade' => 'required',
            'nrVagas' => 'required|max:3|not_in:0',
            'regiaoAtuacao' => 'required|array|min:1|max:15',
            'descricaoOportunidade' => 'required|max:500',
            'contatonome' => 'required|max:191',
            'contatotelefone' => 'required|max:191',
            'contatoemail' => 'required|email|max:191'
        ];
    }

    public function messages() 
    {
        return [
            'cnpj.required' => 'Por favor, informe o CNPJ',
            'razaosocial.required_if' => 'Por favor, informe a Razão Social',
            'fantasia.required_if' => 'Por favor, informe o Nome Fantasia',
            'endereco.required_if' => 'Por favor, informe o endereço',
            'nrVagas.required' => 'Por favor, informe a quantidade de vagas da oportunidade',
            'nrVagas.not_in' => 'Valor inválido',
            'regiaoAtuacao.required' => 'Por favor, selecione ao menos uma região de atuação',
            'descricaoOportunidade.required' => 'Por favor, insira a descrição da oportunidade',
            'contatonome.required' => 'Por favor, informe o nome do contato',
            'contatotelefone.required' => 'Por favor, informe o telefone do contato',
            'contatoemail.required' => 'Por favor, informe o email do contato',
            'segmentoOportunidade.required' => 'Por favor, informe o segmento da oportunidade',
            'required' => 'Por favor, informe o :attribute',
            'required_if' => 'Por favor, informe o :attribute',
            'email' => 'Email inválido',
            'max' => 'Excedido número máximo de caracteres'
        ];
    }

    public function toEmpresaModel() 
    {
        return [
            'segmento' => $this->segmento, 
            'cnpj' => $this->cnpj, 
            'razaosocial' => $this->razaosocial, 
            'fantasia' => $this->fantasia, 
            'capitalsocial' => $this->capitalsocial,
            'endereco' => $this->endereco, 
            'site' => $this->site, 
            'email' => $this->email, 
            'telefone' => $this->telefone,
            'descricao' => $this->descricao,
            'contatonome' => $this->contatonome, 
            'contatotelefone' => $this->contatotelefone, 
            'contatoemail' => $this->contatoemail, 
            'idusuario' => $this->idusuario
        ];
    }

    public function toOportunidadeModel() 
    {
        return [
            'idempresa' => $this->idempresa,
            'titulo' => $this->titulo,
            'segmento' => $this->segmentoOportunidade,
            'regiaoatuacao' => ','.implode(',',$this->regiaoAtuacao).',',
            'descricao' => $this->descricaoOportunidade,
            'vagasdisponiveis' => $this->nrVagas,
            'status' => BdoOportunidade::STATUS_SOB_ANALISE
        ];
    }
}