<?php

namespace App;

use App\Repositories\CursoRepository;
use App\Traits\TabelaAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Curso extends Model
{
    use SoftDeletes, TabelaAdmin;

    protected $primaryKey = 'idcurso';
    protected $table = 'cursos';
    protected $fillable = ['tipo', 'tema', 'img', 'datarealizacao', 'datatermino',
    'endereco', 'nrvagas', 'descricao', 'resumo', 'publicado', 'idregional', 'idusuario', 'acesso'];

    const ACESSO_PRI = 'Privado';
    const ACESSO_PUB = 'Público';

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function cursoinscrito()
    {
    	return $this->hasMany('App\CursoInscrito', 'idcursoinscrito');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function noticia()
    {
        return $this->hasMany('App\Noticia', 'idcurso');
    }

    public function variaveis()
    {
        return [
            'singular' => 'curso',
            'singulariza' => 'o curso',
            'plural' => 'cursos',
            'pluraliza' => 'cursos',
            'titulo_criar' => 'Cadastrar curso',
            'btn_criar' => '<a href="'.route('cursos.create').'" class="btn btn-primary mr-1">Novo Curso</a>',
            'btn_lixeira' => '<a href="'.route('cursos.lixeira').'" class="btn btn-warning">Cursos Cancelados</a>',
            'btn_lista' => '<a href="'.route('cursos.index').'" class="btn btn-primary mr-1">Lista de Cursos</a>',
            'titulo' => 'Cursos cancelados',
        ];
    }

    private function tabelaHeaders()
    {
        return ['Turma', 'Tipo / Tema', 'Onde / Quando', 'Vagas', 'Regional', 'Acesso', 'Ações'];
    }

    private function tabelaContents($query)
    {
        return $query->map(function($row){
            $acoes = '<a href="'.route('cursos.show', $row->idcurso).'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if(perfisPermitidos('CursoInscritoController', 'index'))
                $acoes .= '<a href="'.route('inscritos.index', $row->idcurso).'" class="btn btn-sm btn-secondary">Inscritos</a> ';
            if(perfisPermitidos('CursoController', 'edit'))
                $acoes .= '<a href="'.route('cursos.edit', $row->idcurso).'" class="btn btn-sm btn-primary">Editar</a> ';
            if(perfisPermitidos('CursoController', 'destroy')) {
                $acoes .= '<form method="POST" action="'.route('cursos.destroy', $row->idcurso).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Cancelar" onclick="return confirm(\'Tem certeza que deseja cancelar o curso?\')" />';
                $acoes .= '</form>';
            }
            if($row->publicado == 'Sim')
                $publicado = 'Publicado';
            else
                $publicado = 'Rascunho';
            isset($row->endereco) ? $endereco = $row->endereco : $endereco = 'Evento online';
            return [
                $row->idcurso,
                $row->tipo.'<br>'.$row->tema.'<br /><small><em>'.$publicado.'</em></small>',
                $endereco.'<br />'.formataData($row->datarealizacao),
                (new CursoRepository())->getCursoContagem($row->idcurso).' / '.$row->nrvagas,
                $row->regional->regional,
                $row->acesso,
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

    public function tabelaTrashed($query)
    {
        $headers = ['Turma', 'Tipo / Tema', 'Onde / Quando', 'Regional', 'Cancelado em:', 'Ações'];
        $contents = $query->map(function($row){
            $acoes = '<a href="'.route('cursos.restore', $row->idcurso).'" class="btn btn-sm btn-primary">Restaurar</a> ';
            isset($row->endereco) ? $endereco = $row->endereco : $endereco = 'Evento online';
            return [
                $row->idcurso,
                $row->tipo.'<br>'.$row->tema,
                $endereco.'<br />'.formataData($row->datarealizacao),
                $row->regional->regional,
                formataData($row->deleted_at),
                $acoes
            ];
        })->toArray();

        return $this->montaTabela(
            $headers, 
            $contents,
            [ 'table', 'table-hover' ]
        );
    }

    public function liberarAcesso($rep = false)
    {
        return ($this->acesso == self::ACESSO_PUB) || (($this->acesso == self::ACESSO_PRI) && $rep);
    }

    public function textoAcesso()
    {
        if($this->acesso == self::ACESSO_PUB)
            return 'Aberta ao público';
        if($this->acesso == self::ACESSO_PRI)
            return 'Restrita para representantes';
    }
}
