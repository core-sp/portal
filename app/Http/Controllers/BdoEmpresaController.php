<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\BdoEmpresa;
use App\BdoOportunidade;
use App\Http\Controllers\ControleController;
use App\Events\CrudEvent;
use App\Rules\Cnpj;

class BdoEmpresaController extends Controller
{
    // Nome da Classe
    private $class = 'BdoEmpresaController';
    // Variáveis
    public $variaveis = [
        'singular' => 'empresa',
        'singulariza' => 'a empresa',
        'plural' => 'empresas',
        'pluraliza' => 'empresas',
        'titulo_criar' => 'Cadastrar nova empresa',
        'form' => 'bdoempresa',
        'btn_criar' => '<a href="/admin/bdo/empresas/criar" class="btn btn-primary mr-1">Nova Empresa</a>',
        'busca' => 'bdo/empresas',
        'slug' => 'bdo/empresas'
    ];

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['show', 'apiGetEmpresa']]);
    }

    public function resultados()
    {
        $resultados = BdoEmpresa::orderBy('idempresa', 'DESC')->paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Segmento',
            'Razão Social',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            if(ControleController::mostra('BdoOportunidadeController', 'create'))
                $acoes = '<a href="/admin/bdo/criar?empresa='.$resultado->idempresa.'" class="btn btn-sm btn-secondary">Nova Oportunidade</a> ';
            else
                $acoes = '';
            if(ControleController::mostra($this->class, 'edit'))
                $acoes .= '<a href="/admin/bdo/empresas/editar/'.$resultado->idempresa.'" class="btn btn-sm btn-primary">Editar</a> ';
            if(ControleController::mostra($this->class, 'destroy')) {
                $acoes .= '<form method="POST" action="/admin/bdo/empresas/apagar/'.$resultado->idempresa.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a empresa?\')" />';
                $acoes .= '</form>';
            }
            if(empty($acoes))
                $acoes = '<i class="fas fa-lock text-muted"></i>';
            $conteudo = [
                $resultado->idempresa,
                $resultado->segmento,
                $resultado->razaosocial,
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        // Monta e retorna tabela        
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    protected function regras()
    {
        return [
            'segmento' => 'max:191',
            'cnpj' => ['required', 'max:191', new Cnpj],
            'razaosocial' => 'required|max:191',
            'capitalsocial' => 'max:191',
            'endereco' => 'required|max:191',
            'descricao' => 'required',
            'email' => 'required|email|max:191',
            'telefone' => 'required|max:191',
            'site' => 'max:191',
            'contatonome' => 'max:191',
            'contatotelefone' => 'max:191',
            'contatoemail' => 'email|max:191',
        ];
    }

    protected function mensagens()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'cnpj.unique' => 'Já existe uma empresa cadastrada com este CNPJ',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'email' => 'Email inválido'
        ];
    }

    public function index()
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        if(!ControleController::mostra($this->class, 'create'))
            unset($this->variaveis['btn_criar']);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(Request $request)
    {
        ControleController::autoriza($this->class, 'create');
        $regras = $this->regras();
        array_push($regras['cnpj'], 'unique:bdo_empresas');
        $erros = $request->validate($regras, $this->mensagens());

        $save = BdoEmpresa::create(request(['segmento', 'cnpj', 'razaosocial', 'fantasia', 'descricao', 'capitalsocial',
        'endereco', 'site', 'email', 'telefone', 'contatonome', 'contatotelefone', 'contatoemail', 'idusuario']));

        if(!$save)
            abort(500);
        event(new CrudEvent('empresa (Balcão de Oportunidades)', 'criou', $save->idempresa));
        return redirect()->route('bdoempresas.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Empresa cadastrada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = BdoEmpresa::findOrFail($id);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(Request $request, $id)
    {
        ControleController::autoriza($this->class, 'edit');
        $erros = $request->validate($this->regras(), $this->mensagens());

        $update = BdoEmpresa::findOrFail($id)->update(request(['segmento', 'cnpj', 'razaosocial', 'fantasia', 'descricao', 'capitalsocial',
        'endereco', 'site', 'email', 'telefone', 'contatonome', 'contatotelefone', 'contatoemail', 'idusuario']));
        
        if(!$update)
            abort(500);
        event(new CrudEvent('empresa (Balcão de Oportunidades)', 'editou', $id));
        return redirect()->route('bdoempresas.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Empresa editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $empresa = BdoEmpresa::findOrFail($id);
        $count = BdoOportunidade::where('idempresa',$id)->count();
        if($count >= 1) {
            return redirect()->route('bdoempresas.lista')
                ->with('message', '<i class="icon fa fa-ban"></i>Não é possível deletar empresas com oportunidades abertas!')
                ->with('class', 'alert-danger');
        } else {
            $delete = $empresa->delete();
            if(!$delete)
                abort(500);
            event(new CrudEvent('empresa (Balcão de Oportunidades)', 'apagou', $empresa->idempresa));
            return redirect()->route('bdoempresas.lista')
                ->with('message', '<i class="icon fa fa-ban"></i>Empresa deletada com sucesso!')
                ->with('class', 'alert-danger');
        }
    }

    public function busca()
    {
        ControleController::autoriza($this->class, 'index');
        $busca = Input::get('q');
        $variaveis = (object) $this->variaveis;
        $resultados = BdoEmpresa::where('segmento','LIKE','%'.$busca.'%')
            ->orWhere('razaosocial','LIKE','%'.$busca.'%')
            ->orWhere('cnpj','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function apiGetEmpresa($cnpj)
    {
        $cnpj = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj);

       $empresa = BdoEmpresa::select('idempresa', 'cnpj', 'razaosocial', 'fantasia', 'telefone', 'segmento', 'endereco', 'site', 'email')
            ->where('cnpj', '=', $cnpj)
            ->first();

        if(isset($empresa)) {
            return $empresa->toJson();
        } else {
            abort(500);
        }
    }
}
