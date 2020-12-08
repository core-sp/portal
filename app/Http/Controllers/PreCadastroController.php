<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ControleAcesso;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\PreCadastroRequest;
use App\Repositories\PreCadastroRepository;

class PreCadastroController extends Controller
{
    use ControleAcesso;
    
    private $preCadastroRepository;
    private $variaveis;

    public function __construct(PreCadastroRepository $preCadastroRepository)
    {
        $this->middleware('auth', ['except' => ['create', 'store']]);

        $this->preCadastroRepository = $preCadastroRepository;

        $this->variaveis = [
            'singular' => 'solicitação de pré-cadastro',
            'singulariza' => 'a solicitação de inclusão de pré-cadastro',
            'plural' => 'solicitações de pré-cadastro',
            'pluraliza' => 'solicitações de pré-cadastro',
            'mostra' => 'pre-cadastro'
        ];
    }

    public function index()
    {
        return view('site.pre-cadastro');
    }

    public function show($id)
    {
        //$this->autoriza($this->class, __FUNCTION__);

        $resultado = $this->preCadastroRepository->getById($id);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.mostra', compact('resultado', 'variaveis'));
    }

    public function create()
    {
        return view('site.pre-cadastro');
    }

    public function store(PreCadastroRequest $request)
    {
        $nomeAnexo = 'anexo.' . $request->file('anexo')->getClientOriginalExtension();

        $preCadastro = $this->preCadastroRepository->store($request, $nomeAnexo);

        $request->file('anexo')->storeAs('pre-cadastro/' . $preCadastro->id , $nomeAnexo);
    }

    public function edit()
    {
        
    }

    public function update()
    {
        
    }

    public function visualizarAnexo(Request $request, $id) 
    {
        if(Storage::exists('pre-cadastro/' . $id . '/' . $request->nomeArquivo)) {
            return response()->file(Storage::path('pre-cadastro/' . $id . '/' . $request->nomeArquivo), ["Cache-Control" => "no-cache"]);
        }
        else {
            abort(404);
        }
    }

    public function baixarAnexo(Request $request, $id) 
    {
        if(Storage::exists('pre-cadastro/' . $id . '/' . $request->nomeArquivo)) {
            return Storage::download('pre-cadastro/' . $id . '/' . $request->nomeArquivo, $request->nomeArquivo);
        }
        else {
            abort(404);
        }
    }


}
