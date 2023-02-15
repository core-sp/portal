<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Events\CrudEvent;
use App\Http\Requests\AvisoRequest;
// use App\Repositories\AvisoRepository;
// use App\Traits\TabelaAdmin;
// use Illuminate\Support\Facades\Request as IlluminateRequest;
use App\Contracts\MediadorServiceInterface;

class AvisoController extends Controller
{
    // use TabelaAdmin;

    // // Nome da classe
    // private $class = 'AvisoController';
    // private $avisoRepository;
    // private $variaveis = [
    //     'singular' => 'aviso',
    //     'singulariza' => 'o aviso',
    //     'plural' => 'avisos',
    //     'pluraliza' => 'avisos',
    //     'form' => 'aviso'
    // ];
    private $service;

    public function __construct(/*AvisoRepository $avisoRepository, */MediadorServiceInterface $service)
    {
        $this->middleware('auth');
        // $this->avisoRepository = $avisoRepository;
        $this->service = $service;
    }  

    public function index()
    {
        $this->authorize('viewAny', auth()->user());
        // $resultados = $this->avisoRepository->getAll();
        // $variaveis = (object) $this->variaveis;
        // $tabela = $this->tabelaCompleta($resultados);

        try{
            $dados = $this->service->getService('Aviso')->listar();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os avisos.");
        }

        return view('admin.crud.home', $dados);
    }

    public function show($id)
    {
        $this->authorize('viewAny', auth()->user());
        // $resultado = $this->avisoRepository->getById($id);
        // $this->variaveis['singulariza'] = 'o aviso da área do ' .$resultado->area;
        // $variaveis = (object) $this->variaveis;

        try{
            $dados = $this->service->getService('Aviso')->show($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar o formulário de exemplo do aviso.");
        }

        return view('admin.crud.mostra', $dados);
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());
        // $resultado = $this->avisoRepository->getById($id);
        // $variaveis = (object) $this->variaveis;
        // $cores = $this->avisoRepository->cores();

        try{
            $dados = $this->service->getService('Aviso')->edit($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar o formulário de edição do aviso.");
        }

        return view('admin.crud.editar', $dados);
    }

    public function update(AvisoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());
        // $request->validated();
        // if(auth()->user() == null)
        //     abort(500, 'Não foi encontrado o usuário');
        // try{
        //     $update = $this->avisoRepository->update($request, $id, auth()->user());
        // }catch(\Exception $e){
        //     abort(500, 'Erro ao atualizar o aviso');
        // }
    
        // event(new CrudEvent('aviso', 'editou', $id));

        try{
            $user = auth()->user();
            $validated = $request->validated();
            $dados = $this->service->getService('Aviso')->save($validated, $id, $user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar o aviso.");
        }

        return redirect()->route('avisos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Aviso editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function updateStatus($id)
    {
        $this->authorize('updateOther', auth()->user());
        // if(auth()->user() == null)
        //     abort(500, 'Não foi encontrado o usuário');
        // try{
        //     $update = $this->avisoRepository->updateCampoStatus($id, auth()->user());
        // }catch(\Exception $e){
        //     abort(500, 'Erro ao atualizar o aviso');
        // }
        
        // $aviso = $this->avisoRepository->getById($id);
        // event(new CrudEvent('aviso', 'editou o status para ' . $aviso->status, $id));

        try{
            $user = auth()->user();
            $status = $this->service->getService('Aviso')->updateStatus($id, $user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar o status do aviso.");
        }

        return redirect()->route('avisos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Aviso foi ' .$status. ' com sucesso!')
            ->with('class', 'alert-success');
    }

    // private function tabelaCompleta($resultados)
    // {
    //     // Opções de cabeçalho da tabela
    //     $headers = [
    //         'Id',
    //         'Área',
    //         'Título',
    //         'Última Atualização',
    //         'Ações'
    //     ];
    //     // Opções de conteúdo da tabela
    //     $contents = [];
    //     foreach($resultados as $resultado) {
    //         $statusDesejado = $resultado->isAtivado() ? 'Desativar' : 'Ativar';
    //         $botao = $resultado->isAtivado() ? 'btn btn-sm btn-danger' : 'btn btn-sm btn-success';

    //         $acoes = ' <a href="' .route('avisos.show', $resultado->id). '" class="btn btn-sm btn-default">Ver</a> ';
    //         $acoes .= '<a href="' .route('avisos.editar.view', $resultado->id). '" class="btn btn-sm btn-primary">Editar</a> ';
    //         $acoes .= '<form method="POST" action="' .route('avisos.editar.status', $resultado->id). '" class="d-inline">';
    //         $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
    //         $acoes .= '<input type="hidden" name="_method" value="put" />';
    //         $acoes .= '<input type="submit" class="' .$botao. '" value="' .$statusDesejado. '" 
    //         onclick="return confirm(\'Tem certeza que deseja ' .$statusDesejado. ' o aviso?\')" />';
    //         $acoes .= '</form>';

    //         $user = isset($resultado->user) ? $resultado->user->nome : '------------';
    //         $conteudo = [
    //             $resultado->id,
    //             $resultado->area,
    //             $resultado->titulo,
    //             formataData($resultado->updated_at). '<br><small>Por: ' .$user. '</small>',
    //             $acoes
    //         ];
    //         array_push($contents, $conteudo);
    //     }

    //     // Classes da tabela
    //     $classes = [
    //         'table',
    //         'table-hover'
    //     ];
    //     $tabela = $this->montaTabela($headers, $contents, $classes);
        
    //     return $tabela;
    // }
}
