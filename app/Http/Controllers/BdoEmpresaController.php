<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\BdoEmpresa;

class BdoEmpresaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'show']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['admin']);
        $empresas = BdoEmpresa::paginate(10);
        return view('admin.bdo.empresas.home', compact('empresas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        return view('admin.bdo.empresas.criar');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->user()->autorizarPerfis(['admin']);
        $regras = [
            'cnpj' => 'required',
            'razaosocial' => 'required',
            'endereco' => 'required',
            'descricao' => 'required',
            'email' => 'required',
            'telefone' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
        ];
        $erros = $request->validate($regras, $mensagens);

        $empresa = New BdoEmpresa();
        $empresa->segmento = $request->input('segmento');
        $empresa->cnpj = $request->input('cnpj');
        $empresa->razaosocial = $request->input('razaosocial');
        $empresa->descricao = $request->input('descricao');
        $empresa->capitalsocial = $request->input('capitalsocial');
        $empresa->endereco = $request->input('endereco');
        $empresa->site = $request->input('site');
        $empresa->email = $request->input('email');
        $empresa->telefone = $request->input('telefone');
        $empresa->contatonome = $request->input('contatonome');
        $empresa->contatotelefone = $request->input('contatotelefone');
        $empresa->contatoemail = $request->input('contatoemail');
        $empresa->idusuario = $request->input('idusuario');
        $empresa->save();
        return redirect()->route('bdoempresas.lista');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin']);
        $empresa = BdoEmpresa::find($id);
        return view('admin.bdo.empresas.editar', compact('empresa'));
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
        $request->user()->autorizarPerfis(['admin']);
        $regras = [
            'cnpj' => 'required',
            'razaosocial' => 'required',
            'endereco' => 'required',
            'descricao' => 'required',
            'email' => 'required',
            'telefone' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
        ];
        $erros = $request->validate($regras, $mensagens);

        $empresa = BdoEmpresa::find($id);
        $empresa->segmento = $request->input('segmento');
        $empresa->cnpj = $request->input('cnpj');
        $empresa->razaosocial = $request->input('razaosocial');
        $empresa->descricao = $request->input('descricao');
        $empresa->capitalsocial = $request->input('capitalsocial');
        $empresa->endereco = $request->input('endereco');
        $empresa->site = $request->input('site');
        $empresa->email = $request->input('email');
        $empresa->telefone = $request->input('telefone');
        $empresa->contatonome = $request->input('contatonome');
        $empresa->contatotelefone = $request->input('contatotelefone');
        $empresa->contatoemail = $request->input('contatoemail');
        $empresa->idusuario = $request->input('idusuario');
        $empresa->update();
        return redirect()->route('bdoempresas.lista');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin']);
        $empresa = BdoEmpresa::find($id);
        $empresa->delete();
        return redirect()->route('bdoempresas.lista');
    }

    public function busca()
    {
        $busca = Input::get('q');
        $empresas = BdoEmpresa::where('segmento','LIKE','%'.$busca.'%')
            ->orWhere('razaosocial','LIKE','%'.$busca.'%')
            ->orWhere('cnpj','LIKE','%'.$busca.'%')
            ->paginate(10);
        if (count($empresas) > 0) 
            return view('admin.bdo.empresas.home', compact('empresas', 'busca'));
        else
            return view('admin.bdo.empresas.home')->withMessage('Nenhuma empresa encontrada');
    }
}
