<?php

namespace App\Http\Controllers;

use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
use App\Traits\ControleAcesso;
use App\Http\Requests\CompromissoRequest;
use App\Repositories\CompromissoRepository;

class CompromissoController extends Controller
{
    use ControleAcesso, TabelaAdmin;

    private $compromissoRepository;

    public function __construct(CompromissoRepository $compromissoRepository)
    {
        $this->middleware('auth');
        $this->compromissoRepository = $compromissoRepository;
        $this->variaveis = [
            'singular' => 'compromisso',
            'singulariza' => 'o compromisso',
            'plural' => 'compromissos',
            'pluraliza' => 'compromisso',
            'titulo_criar' => 'Registrar compromisso'
        ];
    }

    public function index()
    {
        //$this->autoriza($this->class, "index");

        $resultados = $this->compromissoRepository->getAll();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        //$this->autoriza($this->class, __FUNCTION__);

        $variaveis = (object) $this->variaveis;

        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(CompromissoRequest $request)
    {
        //$this->autoriza($this->class, __FUNCTION__);

        $compromisso = $this->compromissoRepository->store($request);

        if(!$compromisso) {
            abort(500, 'Erro ao salvar o compromisso');
        }

        event(new CrudEvent('compromisso', 'criou', $compromisso->id));

        return redirect(route('compromisso.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Compromisso criado com sucesso!')
            ->with('class', 'alert-success');

    }

    public function edit($id)
    {
        //$this->autoriza($this->class, __FUNCTION__);

        $resultado = $this->compromissoRepository->getById($id);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(CompromissoRequest $request, $id)
    {
        //$this->autoriza($this->class, __FUNCTION__);

        $compromisso = $this->compromissoRepository->update($id, $request);

        if(!$compromisso) {
            abort(500, 'Erro ao editar o compromisso');
        }

        event(new CrudEvent('compromisso', 'editou', $id));

        return redirect(route('compromisso.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Compromisso editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy($id)
    {
        //$this->autoriza($this->class, __FUNCTION__);
        
        $delete = $this->compromissoRepository->deleteBy($id);

        if(!$delete) {
            abort(500, 'Erro ao apagar o compromisso');
        }
            
        event(new CrudEvent('compromisso', 'apagou', $id));

        return redirect(route('compromisso.index'))
            ->with('message', '<i class="icon fa fa-ban"></i>Compromisso apagado com sucesso!')
            ->with('class', 'alert-danger');
    }

    protected function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'ID',
            'Data',
            'Início',
            'Término',
            'Local', 
            'Título',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];

        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/compromissos/edit/'.$resultado->id.'" class="btn btn-sm btn-default">Editar</a> ';
            
            $acoes .= '<form method="POST" action="' . route('compromisso.destroy', $resultado->id) . '" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir o compromisso?\')" />';
            $acoes .= '</form>';
            
            
            $conteudo = [
                $resultado->id,
                onlyDate($resultado->data),
                onlyHour($resultado->horarioinicio),
                onlyHour($resultado->horariotermino),
                $resultado->local,
                $resultado->titulo,
                $acoes
            ];
            array_push($contents, $conteudo);
        }

        // Classes da tabela
        $classes = [
            'table',
            'table-bordered',
            'table-striped'
        ];
        $tabela = $this->montaTabela($headers, $contents, $classes);
        
        return $tabela;
    }
}
