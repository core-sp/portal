<?php

namespace App;

use App\Repositories\AgendamentoBloqueioRepository;
use App\Traits\ControleAcesso;
use App\Traits\TabelaAdmin;
use Illuminate\Database\Eloquent\Model;

class Regional extends Model
{
    use ControleAcesso, TabelaAdmin;

    protected $table = 'regionais';
    protected $primaryKey = 'idregional';
    protected $fillable = ['prefixo', 'regional', 'endereco', 'bairro',
    'numero', 'complemento', 'cep', 'telefone', 'fax', 'email',
    'funcionamento', 'ageporhorario', 'horariosage', 'responsavel', 'descricao'];
    public $timestamps = false;

    public function user()
    {
        return $this->hasMany('App\User', 'idusuario');
    }

    public function variaveis()
    {
        return [
            'singular' => 'regional',
            'singulariza' => 'a regional',
            'plural' => 'regionais',
            'pluraliza' => 'regionais'
        ];
    }

    protected function tabelaHeaders()
    {
        return ['Código', 'Regional', 'Telefone', 'Email', 'Ações'];
    }

    protected function tabelaContents($query)
    {
        return $query->map(function($row){
            $acoes = '<a href="'.route('regionais.show', $row->idregional).'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if($this->mostra('RegionalController', 'edit'))
                $acoes .= '<a href="'.route('regionais.edit', $row->idregional).'" class="btn btn-sm btn-primary">Editar</a>';
            return [
                $row->idregional,
                $row->prefixo.' - '.$row->regional,
                $row->telefone,
                $row->email,
                $acoes
            ];
        })->toArray();
    }

    public function tabelaCompleta($query)
    {
        return $this->montaTabela(
            $this->tabelaHeaders(), 
            $this->tabelaContents($query),
            [ 'table', 'table-hover' ]
        );
    }

    public function horariosAge()
    {
        if($this->horariosage)
            return explode(',', $this->horariosage);

        return null;
    }

    public function horariosDisponiveis($dia)
    {
        $horas = $this->horariosAge();
        $bloqueios = (new AgendamentoBloqueioRepository)->getByRegionalAndDay($this->idregional, $dia);
        if($bloqueios) {
            foreach($bloqueios as $bloqueio) {
                foreach($horas as $key => $hora) {
                    if($hora >= $bloqueio->horainicio && $hora <= $bloqueio->horatermino) {
                        unset($horas[$key]);
                    }
                }
            }
        }
        return $horas;
    }
}
