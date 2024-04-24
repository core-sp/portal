<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Socio extends Model
{
    use SoftDeletes;

    protected $table = 'socios';
    protected $guarded = [];

    public static function camposPreRegistro()
    {
        return [
            'cpf_cnpj',
            'registro',
            'nome',
            'nome_social',
            'dt_nascimento',
            'identidade',
            'orgao_emissor',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
            'nome_mae',
            'nome_pai',
            'nacionalidade',
            'naturalidade_estado',
        ];
    }

    private function validarUpdateAjax($campo, $valor, $gerenti, $canEdit = null)
    {
        if(($campo == 'cpf_cnpj') || (($campo == 'checkRT_socio') && ($valor == 'off')))
        {
            if(isset($valor) && ((strlen($valor) == 11) || (strlen($valor) == 14))) 
                return self::buscar($valor, $gerenti, $canEdit);
            return 'remover';
        }

        return null;
    }

    private function updateAjax($campo, $valor)
    {
        $dados_pf = ['nome_social', 'dt_nascimento', 'identidade', 'orgao_emissor', 'nacionalidade', 'naturalidade_estado', 'nome_mae', 'nome_pai'];

        if(($campo != 'cpf_cnpj') || (!$this->socioPF() && !in_array($campo, $dados_pf)))
            $this->update([$campo => $valor]);
    }

    private static function confereRTSocio(&$campo, &$valor, $pj)
    {
        if(($campo == 'checkRT_socio') && ($valor == 'on'))
        {
            $campo = 'cpf_cnpj';
            $valor = $pj->responsavelTecnico->cpf;

            return $pj->responsavelTecnico->dadosRTSocio()->toArray();
        }

        return null;
    }

    protected static function criarFinal($campo, $valor, $gerenti, $pr)
    {
        if(!$pr->pessoaJuridica->podeCriarSocio())
            return ['limite' => 'Já alcançou o limite de sócios permitido'];
        
        $dadosRT = self::confereRTSocio($campo, $valor, $pr->pessoaJuridica);

        $valido = $campo == 'cpf_cnpj' ? self::buscar($valor, $gerenti, $pr->pessoaJuridica->getHistoricoCanEdit(self::class)) : null;
        if(isset($valido))
        {
            if($valido == 'notUpdate')
                $valido = ['update' => $pr->pessoaJuridica->getNextUpdateHistorico(self::class)];
            elseif($pr->pessoaJuridica->socios->where('id', $valido->id)->first() !== null)
                return ['existente' => 'O sócio com o CPF / CNPJ <strong>' . formataCpfCnpj($valido->cpf_cnpj) . '</strong> já está relacionado!'];
            else{
                $pr->pessoaJuridica->socios()->attach($valido->id, ['rt' => is_array($dadosRT)]);
                $pr->pessoaJuridica->update(['historico_socio' => $pr->pessoaJuridica->setHistorico(self::class)]);
                $valido = $pr->pessoaJuridica->fresh()->socios->find($valido->id);
            }
        }

        return isset($valido) && (gettype($valido) == "object") && (get_class($valido) == self::class) ? 
        ['tab' => $valido->tabHTML(), 'rt' => is_array($dadosRT)] : $valido;
    }

    public function pessoasJuridicas()
    {
        return $this->belongsToMany('App\PreRegistroCnpj', 'socio_pre_registro_cnpj', 'socio_id', 'pre_registro_cnpj_id')->withPivot('rt')->withTimestamps();
    }

    public function socioPF()
    {
        return strlen($this->cpf_cnpj) == 11;
    }

    public function socioRT()
    {
        return isset($this->pivot) && $this->pivot->rt;
    }

    public function atualizarFinal($campo, $valor, $gerenti, $pj)
    {
        $valido = $this->validarUpdateAjax($campo, $valor, $gerenti, $pj->getHistoricoCanEdit(self::class));
        if(isset($valido))
        {
            if($valido == 'notUpdate')
                $valido = ['update' => $pj->getNextUpdateHistorico(self::class)];
            elseif($valido == 'remover')
                $this->pessoasJuridicas()->detach($pj->id);
        }
        else
        {
            $this->updateAjax($campo, $valor);
            $this->pivot->update(['updated_at' => now()]);
            $pj->preRegistro->touch();
            $valido = ['atualizado' => $this->tabHTML(), 'id' => $this->id];
        }

        return $valido;
    }

    public static function buscar($cpf_cnpj, $gerenti, $canEdit = null)
    {
        if(isset($cpf_cnpj) && ((strlen($cpf_cnpj) == 11) || (strlen($cpf_cnpj) == 14)))
        {   
            if(isset($canEdit) && !$canEdit)
                return 'notUpdate';

            $existe = self::where('cpf_cnpj', $cpf_cnpj)->first();
            if(isset($existe) && isset($gerenti["registro"]) && (!isset($existe->registro) || ($existe->registro != $gerenti["registro"])))
                $existe->update($gerenti);

            if(!isset($existe))
                $existe = isset($gerenti["registro"]) ? self::create($gerenti) : self::create(['cpf_cnpj' => $cpf_cnpj]);

            return $existe;
        }

        return null;
    }

    private function tabHTMLpf($inicio, $final)
    {
        // Nome Social
        $texto = $inicio . '<span class="label_nome_social bold">Nome Social:</span> <span class="nome_social_socio editar_dado">';
        $texto .= !isset($this->nome_social) ? '-----' : $this->nome_social;
        $texto .= '</span>' . $final;

        // Dt Nascimento
        $texto .= $inicio . '<span class="label_dt_nascimento bold">Data de Nascimento:</span> <span class="dt_nascimento_socio editar_dado" style="display: none">' . $this->dt_nascimento .'</span><span>';
        $texto .= !isset($this->dt_nascimento) ? '-----' : onlyDate($this->dt_nascimento);
        $texto .= '</span>' . $final;

        // Identidade
        $texto .= $inicio . '<span class="label_identidade bold">Identidade:</span> <span class="identidade_socio editar_dado">';
        $texto .= !isset($this->identidade) ? '-----' : $this->identidade;
        $texto .= '</span>' . $final;

        // Órgão Emissor
        $texto .= $inicio . '<span class="label_orgao_emissor bold">Órgão Emissor:</span> <span class="orgao_emissor_socio editar_dado">';
        $texto .= !isset($this->orgao_emissor) ? '-----' : $this->orgao_emissor;
        $texto .= '</span>' . $final;

        // Nome Mãe
        $texto .= $inicio . '<span class="label_nome_mae bold">Nome da Mãe:</span> <span class="nome_mae_socio editar_dado">';
        $texto .= !isset($this->nome_mae) ? '-----' : $this->nome_mae;
        $texto .= '</span>' . $final;

        // Nome Pai
        $texto .= $inicio . '<span class="label_nome_pai bold">Nome do Pai:</span> <span class="nome_pai_socio editar_dado">';
        $texto .= !isset($this->nome_pai) ? '-----' : $this->nome_pai;
        $texto .= '</span>' . $final;

        if($this->socioRT())
            $texto = '';

        // Nacionalidade
        $texto .= $inicio . '<span class="label_nacionalidade bold">Nacionalidade:</span> <span class="nacionalidade_socio editar_dado">';
        $texto .= !isset($this->nacionalidade) ? '-----' : $this->nacionalidade;
        $texto .= '</span>' . $final;

        // Naturalidade
        $texto .= $inicio . '<span class="label_naturalidade_estado bold">Naturalidade:</span> <span class="naturalidade_estado_socio editar_dado">';
        $texto .= !isset($this->naturalidade_estado) ? '-----' : $this->naturalidade_estado;
        $texto .= '</span>' . $final;

        return $texto;
    }

    public function tabHTML()
    {
        $inicio = '<span class="p-1 mr-2 mb-2">';
        $final = '</span>';
        $cpf_cnpj_txt = $this->socioPF() ? 'CPF' : 'CNPJ';
        
        $texto = '<div id="socio_' . $this->id .'_box">';
        $texto .= '<button type="button" class="btn btn-primary btn-sm btn-block mt-3" data-toggle="collapse" data-target="#socio_'. $this->id .'">';
        $texto .= '<strong>Sócio <span class="ordem-socio"></span></strong> - ID <strong>' . $this->id . '</strong> - '. $cpf_cnpj_txt . ': <strong>'. formataCpfCnpj($this->cpf_cnpj) .'</strong>';
        if($this->socioRT())
            $texto .= '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>';
        $texto .= '</button>';
        $texto .= '<div id="socio_'.$this->id . '" class="collapse border border-top-0 border-secondary p-2 dados_socio">';

        if($this->socioRT())
            $texto .= '<p class="text-danger mb-2"><strong><i>Dados do Responsável Técnico na aba "Contato / RT", em "Sócios" somente dados complementares.</i></strong></p>';

        // ID
        $texto .= $inicio;
        $texto .= '<span class="label_id bold">ID:</span> <span class="id_socio editar_dado">'.$this->id .'</span>';
        $texto .= $final;

        // CPF / CNPJ
        $texto .= $inicio;
        $texto .= '<span class="label_cpf_cnpj bold">'. $cpf_cnpj_txt .':</span> <span class="cpf_cnpj_socio editar_dado">' . formataCpfCnpj($this->cpf_cnpj) .'</span>';
        $texto .= $final;

        if($this->socioRT())
            $texto .= $this->tabHTMLpf($inicio, $final);
        else{
            // Registro
            $texto .= $inicio . '<span class="label_registro bold">Registro:</span> <span class="registro_socio editar_dado">';
            $texto .= !isset($this->registro) ? '-----' : formataRegistro($this->registro);
            $texto .= '</span>' . $final;

            // Nome
            $texto .= $inicio . '<span class="label_nome bold">Nome:</span> <span class="nome_socio editar_dado">';
            $texto .= !isset($this->nome) ? '-----' : $this->nome;
            $texto .= '</span>' . $final;

            if($this->socioPF())
                $texto .= $this->tabHTMLpf($inicio, $final);

            // Cep
            $texto .= $inicio . '<span class="label_cep bold">Cep:</span> <span class="cep_socio editar_dado">';
            $texto .= !isset($this->cep) ? '-----' : $this->cep;
            $texto .= '</span>' . $final;

            // Logradouro
            $texto .= $inicio . '<span class="label_logradouro bold">Logradouro:</span> <span class="logradouro_socio editar_dado">';
            $texto .= !isset($this->logradouro) ? '-----' : $this->logradouro;
            $texto .= '</span>' . $final;

            // Número
            $texto .= $inicio . '<span class="label_numero bold">Número:</span> <span class="numero_socio editar_dado">';
            $texto .= !isset($this->numero) ? '-----' : $this->numero;
            $texto .= '</span>' . $final;

            // Complemento
            $texto .= $inicio . '<span class="label_complemento bold">Complemento:</span> <span class="complemento_socio editar_dado">';
            $texto .= !isset($this->complemento) ? '-----' : $this->complemento;
            $texto .= '</span>' . $final;

            // Complemento
            $texto .= $inicio . '<span class="label_bairro bold">Bairro:</span> <span class="bairro_socio editar_dado">';
            $texto .= !isset($this->bairro) ? '-----' : $this->bairro;
            $texto .= '</span>' . $final;

            // Município
            $texto .= $inicio . '<span class="label_cidade bold">Município:</span> <span class="cidade_socio editar_dado">';
            $texto .= !isset($this->cidade) ? '-----' : $this->cidade;
            $texto .= '</span>' . $final;

            // Estado
            $texto .= $inicio . '<span class="label_uf bold">Estado:</span> <span class="uf_socio editar_dado">';
            $texto .= !isset($this->uf) ? '-----' : $this->uf;
            $texto .= '</span>' . $final;
        }

        $texto .= '<div class="d-flex justify-content-center acoes_socio mt-3">';
        $texto .= '<button type="button" class="btn btn-warning btn-sm mr-3 editar_socio"><i class="fas fa-edit"></i></button>';
        $texto .= '<button type="button" class="btn btn-danger btn-sm excluir_socio" data-toggle="modal" data-target="#modalExcluir" data-backdrop="static">';
        $texto .= '<i class="fas fa-trash-alt"></i></button></div></div></div>';

        return $texto;
    }

    private function arrayInputs($array)
    {
        return collect(Arr::only($this->attributesToArray(), $array))->keyBy(function ($item, $key) {
            return $key . '_socio_' . $this->id;
        })->toArray();
    }

    public function arrayValidacao()
    {
        $rt = [
            'nacionalidade_socio_' . $this->id => 'required|in:'.implode(',',
                collect(nacionalidades())->map(function ($item, $key) {
                    return mb_strtoupper($item, 'UTF-8');
                })->toArray()),
            'naturalidade_estado_socio_' . $this->id => 'required_if:nacionalidade_socio_'.$this->id.',BRASILEIRA|nullable|in:'.implode(',', array_keys(estados())),
        ];

        if($this->socioRT())
        {
            $rt['checkRT_socio'] = '';
            return $rt;
        }

        $geral = [
            'checkRT_socio' => '',
            'nome_socio_' . $this->id => 'required|min:5|max:191|regex:/^\D*$/',
            'cep_socio_' . $this->id => 'required|size:9|regex:/([0-9]{5})\-([0-9]{3})$/',
            'bairro_socio_' . $this->id => 'required|min:4|max:191',
            'logradouro_socio_' . $this->id => 'required|min:4|max:191',
            'numero_socio_' . $this->id => 'required|min:1|max:10',
            'complemento_socio_' . $this->id => 'nullable|max:50',
            'cidade_socio_' . $this->id => 'required|min:4|max:191',
            'uf_socio_' . $this->id => 'required|in:'.implode(',', array_keys(estados())),
        ];

        return $this->socioPF() ? array_merge([
            'nome_social_socio_' . $this->id => 'nullable|min:5|max:191|regex:/^\D*$/',
            'dt_nascimento_socio_' . $this->id => 'required|date_format:Y-m-d|before_or_equal:'.now()->subYears(18)->format('Y-m-d'),
            'identidade_socio_' . $this->id => 'required|min:4|max:30',
            'orgao_emissor_socio_' . $this->id => 'required|min:3|max:191',
            'nome_mae_socio_' . $this->id => 'required|min:5|max:191|regex:/^\D*$/',
            'nome_pai_socio_' . $this->id => 'required|min:5|max:191|regex:/^\D*$/',
        ], $rt, $geral) : $geral;
    }

    public function arrayValidacaoInputs()
    {
        $rt = $this->arrayInputs(['nacionalidade', 'naturalidade_estado']);

        if($this->socioRT())
            return $rt;

        $geral = $this->arrayInputs(['nome', 'cep', 'bairro', 'logradouro', 'numero', 'complemento', 'cidade', 'uf']);

        return !$this->socioPF() ? $geral : 
        array_merge($this->arrayInputs(['nome_social', 'dt_nascimento', 'identidade', 'orgao_emissor', 'nome_mae', 'nome_pai']), $rt, $geral);
    }

    public function arrayValidacaoMsg()
    {
        $rt = [
            'nacionalidade_socio_' . $this->id => '"Nacionalidade do Sócio com ID ' . $this->id . '"',
            'naturalidade_estado_socio_' . $this->id => '"Naturalidade - Estado do Sócio com ID ' . $this->id . '"',
        ];

        if($this->socioRT())
            return $rt;

        $geral = [
            'nome_socio_' . $this->id => '"Nome do Sócio com ID ' . $this->id . '"',
            'cep_socio_' . $this->id => '"Cep do Sócio com ID ' . $this->id . '"',
            'bairro_socio_' . $this->id => '"Bairro do Sócio com ID ' . $this->id . '"',
            'logradouro_socio_' . $this->id => '"Logradouro do Sócio com ID ' . $this->id . '"',
            'numero_socio_' . $this->id => '"Número do logradouro do Sócio com ID ' . $this->id . '"',
            'complemento_socio_' . $this->id => '"Complemento do logradouro do Sócio com ID ' . $this->id . '"',
            'cidade_socio_' . $this->id => '"Município do Sócio com ID ' . $this->id . '"',
            'uf_socio_' . $this->id => '"Estado do Sócio com ID ' . $this->id . '"',
        ];

        return $this->socioPF() ? array_merge([
            'nome_social_socio_' . $this->id => '"Nome Social do Sócio com ID ' . $this->id . '"',
            'dt_nascimento_socio_' . $this->id => '"Data de Nascimento do Sócio com ID ' . $this->id . '"',
            'identidade_socio_' . $this->id => '"Identidade do Sócio com ID ' . $this->id . '"',
            'orgao_emissor_socio_' . $this->id => '"Órgão Emissor do Sócio com ID ' . $this->id . '"',
            'nome_mae_socio_' . $this->id => '"Nome da Mãe do Sócio com ID ' . $this->id . '"',
            'nome_pai_socio_' . $this->id => '"Nome do Pai do Sócio com ID ' . $this->id . '"',
        ], $rt, $geral) : $geral;
    }
}
