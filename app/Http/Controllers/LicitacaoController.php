<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Licitacao;

class LicitacaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['show', 'buscaAvancada']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $licitacoes = Licitacao::paginate(10);
        return view('admin.licitacoes.home', compact('licitacoes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        return view('admin.licitacoes.criar');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $regras = [
            'modalidade' => 'required',
            'nrlicitacao' => 'required',
            'nrprocesso' => 'required',
            'situacao' => 'required',
            'objeto' => 'required',
            'datarealizacao' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'nrlicitacao.required' => 'O nº da licitação é obrigatório',
            'nrprocesso.required' => 'O nº do processo é obrigatório',
            'datarealizacao.required' => 'Informe a data de realização da Licitação',
        ];
        $erros = $request->validate($regras, $mensagens);

        $licitacao = new Licitacao();
        $licitacao->modalidade = $request->input('modalidade');
        $licitacao->nrlicitacao = $request->input('nrlicitacao');
        $licitacao->nrprocesso = $request->input('nrprocesso');
        $licitacao->situacao = $request->input('situacao');
        $licitacao->datarealizacao = $request->input('datarealizacao');
        $licitacao->objeto = $request->input('objeto');
        $licitacao->idusuario = $request->input('idusuario');
        $licitacao->save();
        return redirect()->route('licitacoes.lista');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $licitacao = Licitacao::find($id);
        return view('admin.licitacoes.editar', compact('licitacao'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $regras = [
            'modalidade' => 'required',
            'nrlicitacao' => 'required',
            'nrprocesso' => 'required',
            'situacao' => 'required',
            'objeto' => 'required',
            'datarealizacao' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'nrlicitacao.required' => 'O nº da licitação é obrigatório',
            'nrprocesso.required' => 'O nº do processo é obrigatório',
            'datarealizacao.required' => 'Informe a data de realização da Licitação',
        ];
        $erros = $request->validate($regras, $mensagens);

        $licitacao = Licitacao::find($id);
        $licitacao->modalidade = $request->input('modalidade');
        $licitacao->nrlicitacao = $request->input('nrlicitacao');
        $licitacao->nrprocesso = $request->input('nrprocesso');
        $licitacao->situacao = $request->input('situacao');
        $licitacao->datarealizacao = $request->input('datarealizacao');
        $licitacao->objeto = $request->input('objeto');
        $licitacao->idusuario = $request->input('idusuario');
        $licitacao->update();
        return redirect()->route('licitacoes.lista');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $licitacao = Licitacao::find($id);
        $licitacao->delete();
        return redirect()->route('licitacoes.lista');
    }

    /**
     * Mostra a lixeira de licitações
     *
     * @return \Illuminate\Http\Response
     */
    public function lixeira(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $licitacoes = Licitacao::onlyTrashed()->paginate(10);
        return view('admin.licitacoes.lixeira', compact('licitacoes'));
    }

    /**
     * Restaura licitação deletada
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $licitacao = Licitacao::onlyTrashed()->find($id);
        $licitacao->restore();
        return redirect()->route('licitacoes.lista');
    }

    public function busca()
    {
        $busca = Input::get('q');
        $licitacoes = Licitacao::where('modalidade','LIKE','%'.$busca.'%')
            ->orWhere('nrlicitacao','LIKE','%'.$busca.'%')
            ->orWhere('nrprocesso','LIKE','%'.$busca.'%')
            ->orWhere('situacao','LIKE','%'.$busca.'%')
            ->orWhere('objeto','LIKE','%'.$busca.'%')
            ->paginate(10);
        if (count($licitacoes) > 0) 
            return view('admin.licitacoes.home', compact('licitacoes', 'busca'));
        else
            return view('admin.licitacoes.home')->withMessage('Nenhuma licitação encontrada');
    }
}
