<?php

namespace App;

use App\Traits\ControleAcesso;
use App\Traits\TabelaAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BdoOportunidade extends Model
{
    use SoftDeletes, ControleAcesso, TabelaAdmin;

	protected $primaryKey = 'idoportunidade';
    protected $table = 'bdo_oportunidades';
    protected $fillable = ['idempresa', 'titulo', 'segmento', 'regiaoatuacao', 'descricao', 'vagasdisponiveis', 'vagaspreenchidas', 'status', 'observacao', 'datainicio', 'idusuario'];

    static $status_sob_analise = 'Sob Análise';
    static $status_recusado = 'Recusado';
    static $status_em_andamento = 'Em andamento';
    static $status_concluido = 'Concluído';
    static $status_expirado = 'Expirado';


    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function empresa()
    {
    	return $this->belongsTo('App\BdoEmpresa', 'idempresa');
    }

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function variaveis() {
        return [
            'singular' => 'oportunidade',
            'singulariza' => 'a oportunidade',
            'plural' => 'oportunidade',
            'pluraliza' => 'oportunidades',
            'titulo_criar' => 'Cadastrar nova oportunidade',
            'form' => 'bdooportunidade',
            'busca' => 'bdo',
            'slug' => 'bdo'
        ];
    }

    public static function status()
    {
    	$status = [
            BdoOportunidade::$status_sob_analise,
            BdoOportunidade::$status_recusado,
            BdoOportunidade::$status_em_andamento,
            BdoOportunidade::$status_concluido,
    		BdoOportunidade::$status_expirado
        ];
        
        sort($status);
        
        return $status;
    }

    protected function statusDestacado($status)
    {
        switch ($status) {
            case BdoOportunidade::$status_sob_analise:
                return '<strong><i>' .  $status . '</i></strong>';
            break;

            case BdoOportunidade::$status_recusado:
                return '<strong class="text-danger">' . $status . '</strong>';
            break;

            case BdoOportunidade::$status_concluido:
                return '<strong class="text-warning">' . $status . '</strong>';
            break;

            case BdoOportunidade::$status_em_andamento:
                return '<strong class="text-success">' . $status . '</strong>';
            break;
            
            default:
                return $status;
            break;
        }
    }

    protected function tabelaHeaders()
    {
        return [
            'Código',
            'Empresa',
            'Segmento',
            'Vagas',
            'Status',
            'Ações'
        ];
    }

    protected function tabelaContents($query)
    {
        return $query->map(function($row){
            if($this->mostra('BdoOportunidadeController', 'edit')) {
                $acoes = '<a href="/admin/bdo/editar/'.$row->idoportunidade.'" class="btn btn-sm btn-primary">Editar</a> ';
            }     
            else {
                $acoes = '';
            }

            if($this->mostra('BdoOportunidadeController', 'destroy')) {
                $acoes .= '<form method="POST" action="/admin/bdo/apagar/'.$row->idoportunidade.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a oportunidade?\')" />';
                $acoes .= '</form>';
            }

            if(empty($acoes)) {
                $acoes = '<i class="fas fa-lock text-muted"></i>';
            }

            if(isset($row->vagaspreenchidas)) {
                $relacaovagas = $row->vagaspreenchidas.' / '.$row->vagasdisponiveis;
            }     
            else {
                $relacaovagas = 'X / '.$row->vagasdisponiveis;
            }
                
            if(isset($row->empresa->razaosocial)) {
                $razaosocial = $row->empresa->razaosocial;
            }     
            else {
                $razaosocial = '';
            }
                        
            return [
                $row->idoportunidade,
                $razaosocial,
                $row->segmento,
                $relacaovagas,
                $this->statusDestacado($row->status),
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
}
