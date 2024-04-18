<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResponsavelTecnico extends Model
{
    use SoftDeletes;

    protected $table = 'responsaveis_tecnicos';
    protected $guarded = [];

    private function validarUpdateAjax($campo, $valor, $gerenti, $canEdit = null)
    {
        if($campo == 'cpf')
        {
            if(isset($valor) && (strlen($valor) == 11)) 
                return self::buscar($valor, $gerenti, $canEdit);
            return 'remover';
        }

        return null;
    }

    private function updateAjax($campo, $valor)
    {
        if($campo != 'cpf')
            $this->update([$campo => $valor]);
    }

    protected static function criarFinal($campo, $valor, $gerenti, $pr)
    {
        $valido = $campo == 'cpf' ? self::buscar($valor, $gerenti, $pr->pessoaJuridica->getHistoricoCanEdit()) : null;
        if(isset($valido))
        {
            if($valido == 'notUpdate')
                $valido = ['update' => $pr->pessoaJuridica->getNextUpdateHistorico()];
            else{
                $pr->pessoaJuridica->update(['responsavel_tecnico_id' => $valido->id, 'historico_rt' => $pr->pessoaJuridica->setHistorico()]);
                $socio = $pr->pessoaJuridica->socios->where('cpf_cnpj', $valor)->first();
                if(isset($socio) && !$socio->pivot->rt){
                    $socio->pivot->update(['rt' => true]);
                    $valido['tab'] = $pr->pessoaJuridica->socioRT->first()->tabHTML($pr->pessoaJuridica->socios->count());
                    $valido['id_socio'] = $socio->id;
                    $valido['rt'] = true;
                }
            }
        }

        return $valido;
    }

    public function atualizarFinal($campo, $valor, $gerenti, $pj)
    {
        $valido = $this->validarUpdateAjax($campo, $valor, $gerenti, $pj->getHistoricoCanEdit());
        if(isset($valido))
        {
            if($valido == 'notUpdate')
                $valido = ['update' => $pj->getNextUpdateHistorico()];
            else
                if($valido == 'remover'){
                    $pj->possuiRTSocio() ? $pj->socios()->detach($pj->socios->where('pivot.rt', true)->first()->pivot->socio_id) : null;
                    $pj->update(['responsavel_tecnico_id' => null]);
                }else
                    $pj->update(['responsavel_tecnico_id' => $valido->id, 'historico_rt' => $pj->setHistorico()]);
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

            if(!isset($existe))
                $existe = isset($gerenti["registro"]) ? self::create($gerenti) : self::create(['cpf' => $cpf]);

            return $existe;
        }

        return null;
    }

    public function finalArray($arrayCampos, $pj)
    {
        $resultado = 'remover';

        if(isset($arrayCampos['cpf']) && (strlen($arrayCampos['cpf']) == 11))
        {
            unset($arrayCampos['cpf']);
            $resultado = $this->update($arrayCampos);
        }

        if(($resultado === 'remover') && $pj->possuiRTSocio())
            $pj->socios()->detach($pj->socios->where('pivot.rt', true)->first()->pivot->socio_id);
        $resultado = $pj->update(['responsavel_tecnico_id' => $resultado === 'remover' ? null : $this->id]);
        $pj->preRegistro->touch();

        return $resultado;
    }
}
