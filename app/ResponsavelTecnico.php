<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class ResponsavelTecnico extends Model
{
    use SoftDeletes;

    protected $table = 'responsaveis_tecnicos';
    protected $guarded = [];

    private function validarUpdateAjax($campo)
    {
        // Não atualiza CPF de RT já criado
        if($campo == 'cpf')
            return 'remover';

        return null;
    }

    private function updateAjax($campo, $valor)
    {
        if($campo != 'cpf')
            $this->update([$campo => $valor]);
    }

    protected static function criarFinal($campo, $valor, $gerenti, $pr)
    {
        if($campo != 'cpf')
            throw new \Exception('Não pode relacionar responsável técnico sem CPF no pré-registro de ID ' . $pr->id . '.', 400);

        $valido = self::buscar($valor, $gerenti, $pr->pessoaJuridica->getHistoricoCanEdit());

        if($valido == 'notUpdate')
            $valido = ['update' => $pr->pessoaJuridica->getNextUpdateHistorico()];
        else{
            $pr->pessoaJuridica->relacionarRT($valido->id);
            $valido->fill($pr->pessoaJuridica->relacionarRTSocio($valor));
        }

        return $valido;
    }

    public function atualizarFinal($campo, $valor, $pj)
    {
        $valido = $this->validarUpdateAjax($campo);
        if(isset($valido) && ($valido == 'remover'))
        {
            $pj->removerRTSocio();
            $pj->update(['responsavel_tecnico_id' => null]);
        }
        else
        {
            $this->updateAjax($campo, $valor);
            $pj->preRegistro->touch();
        }

        return $valido;
    }

    public static function camposPreRegistro()
    {
        return [
            'cpf',
            'registro',
            'nome',
            'nome_social',
            'dt_nascimento',
            'sexo',
            'tipo_identidade',
            'identidade',
            'orgao_emissor',
            'dt_expedicao',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
            'nome_mae',
            'nome_pai',
            'titulo_eleitor',
            'zona',
            'secao',
            'ra_reservista',
        ];
    }

    public function pessoasJuridicas()
    {
        return $this->hasMany('App\PreRegistroCnpj')->withTrashed();
    }

    public function dadosRTSocio()
    {
        return $this->makeHidden([
            'cpf', 'sexo', 'tipo_identidade', 'dt_expedicao', 'titulo_eleitor', 'zona', 'secao', 'ra_reservista', 'id', 'created_at', 'updated_at', 'deleted_at'
        ]);
    }

    public static function buscar($cpf, $gerenti, $canEdit = null)
    {
        if(isset($cpf) && (strlen($cpf) == 11))
        {   
            if(isset($canEdit) && !$canEdit)
                return 'notUpdate';

            $existe = self::where('cpf', $cpf)->first();
            if(isset($existe) && isset($gerenti["registro"]) && (!isset($existe->registro) || ($existe->registro != $gerenti["registro"])))
                $existe->update($gerenti);
            if(isset($existe) && !isset($gerenti["registro"]) && isset($existe->registro))
                $existe->update(['registro' => null]);

            if(!isset($existe))
                $existe = isset($gerenti["registro"]) ? self::create($gerenti) : self::create(['cpf' => $cpf]);

            return $existe;
        }

        throw new \Exception('Não pode buscar responsável técnico sem CPF.', 400);
    }

    public function arrayValidacaoInputs()
    {
        return collect(Arr::except($this->attributesToArray(), ['id', 'registro', 'created_at', 'updated_at', 'deleted_at']))->keyBy(function ($item, $key) {
            return $key . '_rt';
        })->toArray();
    }

    // public function finalArray($arrayCampos, $pj)
    // {
    //     $resultado = 'remover';

    //     if(isset($arrayCampos['cpf']) && (strlen($arrayCampos['cpf']) == 11))
    //     {
    //         unset($arrayCampos['cpf']);
    //         $resultado = $this->update($arrayCampos);
    //     }

    //     if(($resultado === 'remover') && $pj->possuiRTSocio())
    //         $pj->socios()->detach($pj->socios->where('pivot.rt', true)->first()->pivot->socio_id);
    //     $resultado = $pj->update(['responsavel_tecnico_id' => $resultado === 'remover' ? null : $this->id]);
    //     $pj->preRegistro->touch();

    //     return $resultado;
    // }
}
