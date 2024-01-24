<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\DadoFiscalizacao;

class PeriodoFiscalizacao extends Model
{
    protected $table = 'periodos_fiscalizacao';
    protected $guarded = [];

    const STATUS_PUBLICADO = 'Publicado';
    const STATUS_NAO_PUBLICADO = 'Não Publicado';

    private function somenteAcoes()
    {
        return $this->dadoFiscalizacao->makeHidden(['id', 'idregional', 'regional', 'idperiodo', 'created_at', 'updated_at']);
    }

    public function dadoFiscalizacao()
    {
    	return $this->hasMany('App\DadoFiscalizacao', 'idperiodo');
    }

    public static function selectMapa()
    {
        return 'id,idperiodo,idregional,created_at,updated_at,' . DadoFiscalizacao::selectMapa();
    }

    public function somaTotal()
    {
        if(isset($this->dadoFiscalizacao))
            return $this->somenteAcoes()->sum(function ($value) {
                return $value->somaTotal();
            });

        return 0;
    }

    public function somaTotalPorAcao()
    {
        if(!isset($this->dadoFiscalizacao))
            return array();

        $dados = $this->somenteAcoes();
        $temp = collect($dados->get(0)->campos())->except($dados->get(0)->excecoesMapa())->all();
        $acoes = collect(array_combine(array_values($temp), array_keys($temp)));
        unset($temp);

        return $acoes->map(function ($item, $key) use($dados){
            return $dados->sum($item);
        })->toArray();
    }
}
