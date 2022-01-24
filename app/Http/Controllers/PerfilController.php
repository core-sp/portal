<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Perfil;
use App\User;
use App\Permissao;
use App\Repositories\PermissaoRepository;
use App\Repositories\PerfilRepository;
use App\Events\CrudEvent;

class PerfilController extends Controller
{
    private $permissao;
    private $permissaoRepository;
    private $permissaoVariaveis;
    private $perfil;
    private $perfilRepository;
    private $variaveis;

    public function __construct(Permissao $permissao, PermissaoRepository $permissaoRepository, Perfil $perfil, PerfilRepository $perfilRepository)
    {
        $this->middleware('auth');
        $this->permissao = $permissao;
        $this->permissaoRepository = $permissaoRepository;
        $this->permissaoVariaveis = $permissao->variaveis();
        $this->perfil = $perfil;
        $this->perfilRepository = $perfilRepository;
        $this->variaveis = $perfil->variaveis(); 
    }

    public function index()
    {
        $this->authorize('onlyAdmin', auth()->user());

        $resultados = $this->perfilRepository->getToTable();
        $tabela = $this->perfil->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->authorize('onlyAdmin', auth()->user());

        $variaveis = (object) $this->variaveis;

        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(Request $request)
    {
        $this->authorize('onlyAdmin', auth()->user());

        $regras = [
            'nome' => 'required|max:191',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $erros = $request->validate($regras, $mensagens);

        $save = $this->perfilRepository->store($request);
        
        if(!$save)
            abort(500);
        
        event(new CrudEvent('perfil de usuário', 'criou', $save->idperfil));
        
        return redirect('/admin/usuarios/perfis')
            ->with('message', '<i class="icon fa fa-check"></i>Perfil cadastrado com sucesso!')
            ->with('class', 'alert-success');
    }

    /** Edição de perfil envolve recuperar todas as permissões. Neste método é construído um array contendo informações 
     * sobre as configuração das permissões para mostrar na tela de edição.*/ 
    public function edit($id)
    {
        $this->authorize('onlyAdmin', auth()->user());

        $perfil = $this->perfilRepository->findOrFail($id);
        $idperfil = $perfil->idperfil; 
        $permissoesGroup = $this->permissaoRepository->getAll()->groupBy('controller');
        $permissoesArray = array();

        /** Array contendo ações de CRUD padrão (store e update são considerados create e edit respectivamente). */ 
        $metodoArray = ['index', 'create', 'edit', 'destroy', 'show'];
        $i = 0;

        foreach($permissoesGroup as $group) {

            $nomeController = $group->first()->controller;

            array_push($permissoesArray, ['display' => $this->permissaoVariaveis[$nomeController], 'controller' => $nomeController, 'permissoes' => array()]);

            foreach($metodoArray as $m) {
                if($group->contains('metodo',$m)) {
                    $permissao = $group->where('metodo', '=', $m)->first();
                    $possuiPermissao = in_array($id, explode(',',$permissao->perfis));
                    array_push($permissoesArray[$i]['permissoes'], ['metodo' => $m, 'editavel'=> true, 'autorizado' => $possuiPermissao]);
                }
                else {
                    array_push($permissoesArray[$i]['permissoes'], ['metodo' => $m, 'editavel'=> false, 'autorizado' => false]);
                }
            }
            $i++;
        }

        // Ordenando o nome da funcionalidade mostrado na tela em ordem alfabética.
        array_multisort(array_column($permissoesArray, 'display'), SORT_ASC, $permissoesArray);
        $variaveis = (object) $this->variaveis;
        
        return view('admin.crud.editar', compact('variaveis', 'permissoesArray', 'idperfil'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('onlyAdmin', auth()->user());

        $permissoes = $this->permissaoRepository->getAll();

        foreach($permissoes as $permissao) {
            $idPermissao = $permissao->idpermissao;
            $cm = $permissao->controller.'_'.$permissao->metodo;

            if(in_array($id, explode(',',$permissao->perfis))) {
                if($request->input($cm) !== 'on') {
                    $this->permissaoRepository->removePerfisById($idPermissao, $id);
                }
            } elseif ($request->input($cm) === 'on') {
                $this->permissaoRepository->addPerfisById($idPermissao, $id);              
            }
        }

        return redirect()->route('perfis.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Permissões atualizadas com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('onlyAdmin', auth()->user());

        $delete = $this->perfilRepository->destroy($id);
        
        if(!$delete)
            abort(500);

        event(new CrudEvent('perfil de usuário', 'apagou', $id));
        
        return redirect()->route('perfis.lista')
            ->with('message', '<i class="icon fa fa-ban"></i>Perfil deletado com sucesso!')
            ->with('class', 'alert-danger');
    }
}