<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class PreRegistroCnpj extends Model
{
    use SoftDeletes;

    protected $table = 'pre_registros_cnpj';
    protected $guarded = [];
    protected $touches = ['preRegistro'];

    const TOTAL_HIST = 1;
    const TOTAL_HIST_SOCIO = 10;
    const TOTAL_HIST_DIAS_UPDATE = 1;
    const TOTAL_HIST_DIAS_UPDATE_SOCIO = 2;

    const RELACAO_RT = 'App\ResponsavelTecnico';
    const RELACAO_SOCIO = 'App\Socio';

    private function horaUpdateHistorico($classe = self::RELACAO_RT)
    {
        $update = $this->getHistoricoArray($classe)['update'];
        $updateCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $update);

        switch ($classe) {
            case self::RELACAO_SOCIO:
                return $updateCarbon->addDays(self::TOTAL_HIST_DIAS_UPDATE_SOCIO);
                break;
            default:
                return $updateCarbon->addDays(self::TOTAL_HIST_DIAS_UPDATE);
        }
    }

    private function validarUpdateAjax($campo, $valor)
    {
        if($campo == 'checkEndEmpresa')
            return $valor == 'on' ? $this->preRegistro->getEndereco() : null;
        return [$campo => $valor];
    }

    public function atualizarFinal($campo, $valor)
    {
        $valido = $this->validarUpdateAjax($campo, $valor);
        if(isset($valido))
            $this->update($valido);

        return null;
    }

    public static function camposPreRegistro()
    {
        return [
            'razao_social',
            'capital_social',
            'nire',
            'tipo_empresa',
            'dt_inicio_atividade',
            'nome_fantasia',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
        ];
    }

    public function preRegistro()
    {
        return $this->belongsTo('App\PreRegistro')->withTrashed();
    }

    public function responsavelTecnico()
    {
        return $this->belongsTo('App\ResponsavelTecnico')->withTrashed();
    }

    public function socios()
    {
        return $this->belongsToMany('App\Socio', 'socio_pre_registro_cnpj', 'pre_registro_cnpj_id', 'socio_id')->withPivot('rt')->withTimestamps();
    }

    public function socioRT()
    {
        return $this->belongsToMany('App\Socio', 'socio_pre_registro_cnpj', 'pre_registro_cnpj_id', 'socio_id')->withPivot('rt')->withTimestamps()->wherePivot('rt', true);
    }

    public function podeCriarSocio()
    {
        return $this->socios->count() < self::TOTAL_HIST_SOCIO;
    }

    public function atendentePodeAprovar()
    {
        return isset($this->responsavelTecnico->registro) && (strlen($this->responsavelTecnico->registro) > 4);
    }

    public function possuiSocio()
    {
        return isset($this->socios) && $this->socios->isNotEmpty();
    }

    public function possuiRT()
    {
        return isset($this->responsavel_tecnico_id);
    }

    public function possuiRTSocio()
    {
        return $this->possuiSocio() && $this->possuiRT() && $this->socios->where('cpf_cnpj', $this->responsavelTecnico->cpf)->where('pivot.rt', true)->isNotEmpty();
    }

    public function possuiSocioPF()
    {
        if(!$this->possuiSocio())
            return false;

        $possuiPF = $this->socios->pluck('cpf_cnpj')->filter(function ($value, $key) {
            return strlen($value) == 11;
        })->isNotEmpty();

        return $this->possuiRTSocio() || $possuiPF;
    }

    public function possuiSocioBrasileiro()
    {
        return $this->possuiSocioPF() && $this->socios->where('nacionalidade', 'BRASILEIRA')->isNotEmpty();
    }

    public function possuiSocioReservista()
    {
        return $this->possuiSocioPF() && $this->socios->whereNotNull('dt_nascimento')->where('dt_nascimento', '>=', now()->subYears(45)->format('Y-m-d'))->isNotEmpty();
    }

    public function removerRT()
    {
        if($this->possuiRTSocio())
            $this->socios()->detach($this->socios->where('pivot.rt', true)->first()->pivot->socio_id);

        return $this->update(['responsavel_tecnico_id' => null]);
    }

    public function relacionarRT($id)
    {
        $this->update(['responsavel_tecnico_id' => $id, 'historico_rt' => $this->setHistorico()]);

        return $this->relacionarRTSocio();
    }

    // Confere se existe como sócio e relaciona
    private function relacionarRTSocio()
    {
        $socio = $this->socios->where('cpf_cnpj', $this->responsavelTecnico->cpf)->first();
        if(isset($socio) && !$socio->pivot->rt)
        {
            $socio->pivot->update(['rt' => true]);
            $this->responsavelTecnico->fill([
                'tab' => $socio->fresh()->tabHTML(),
                'id_socio' => $socio->id,
                'rt' => true,
            ]);
        }

        return $this->responsavelTecnico;
    }

    // Relaciona e verifica se é RT também
    public function relacionarSocio($socio)
    {
        $rt = isset($this->responsavel_tecnico_id) && ($socio->cpf_cnpj == $this->responsavelTecnico->cpf);

        $this->socios()->attach($socio->id, ['rt' => $rt]);
        $this->update(['historico_socio' => $this->setHistorico(get_class($socio))]);

        // Trazer a relação com a tabela pivot
        $socio = $this->fresh()->socios->find($socio->id);
        $socio->fill([
            'tab' => $socio->tabHTML(), 
            'rt' => $rt
        ]);

        return $socio;
    }

    public function socioEstaRelacionado($id)
    {
        return $this->socios->where('id', $id)->first() !== null;
    }

    public function getHistoricoCanEdit($classe = self::RELACAO_RT)
    {
        $array = $this->getHistoricoArray($classe);
        switch ($classe) {
            case self::RELACAO_SOCIO:
                $can = intval($array['tentativas']) < self::TOTAL_HIST_SOCIO;
                break;
            default:
                $can = intval($array['tentativas']) < self::TOTAL_HIST;
        }
        
        $horaUpdate = $this->horaUpdateHistorico($classe);

        return $can || (!$can && ($horaUpdate < now()));
    }

    public function getHistoricoArray($classe = self::RELACAO_RT)
    {
        switch ($classe) {
            case self::RELACAO_SOCIO:
                return isset($this->historico_socio) ? $this->fromJson($this->historico_socio) : array();
                break;
            case self::RELACAO_RT:
                return isset($this->historico_rt) ? $this->fromJson($this->historico_rt) : array();
                break;
        }

        throw new \Exception('Histórico de tentativas de troca no pré-registro deve ser somente as opções: Responsavel Tecnico, Socio', 500);
    }

    public function getNextUpdateHistorico($classe = self::RELACAO_RT)
    {
        return $this->horaUpdateHistorico($classe)->format('d\/m\/Y, \à\s H:i');
    }

    public function setHistorico($classe = self::RELACAO_RT)
    {
        $array = $this->getHistoricoArray($classe);

        switch ($classe) {
            case self::RELACAO_SOCIO:
                $totalTentativas = intval($array['tentativas']) < self::TOTAL_HIST_SOCIO;
                break;
            default:
                $totalTentativas = intval($array['tentativas']) < self::TOTAL_HIST;
        }

        if($totalTentativas)
            $array['tentativas'] = intval($array['tentativas']) + 1;
        $array['update'] = now()->format('Y-m-d H:i:s');

        return $this->asJson($array);
    }

    public function getEndereco()
    {
        return $this->only(['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf']);
    }

    public function mesmoEndereco()
    {
        $naoNulo = isset($this->cep) && isset($this->logradouro) && isset($this->numero) && isset($this->bairro) && isset($this->cidade) && isset($this->uf);

        return $naoNulo && empty(array_diff_assoc($this->getEndereco(), $this->preRegistro->getEndereco()));
    }

    public function arrayValidacaoInputs()
    {
        $all = collect(Arr::except($this->attributesToArray(), ['id', 'historico_rt', 'historico_socio', 'responsavel_tecnico_id', 'pre_registro_id', 'created_at', 
        'updated_at', 'deleted_at']))->keyBy(function ($item, $key) {
            return in_array($key, array_keys($this->getEndereco())) ? $key . '_empresa' : $key;
        })->toArray();

        $all['checkEndEmpresa'] = $this->mesmoEndereco() ? 'on' : 'off';

        return $all;
    }
}
