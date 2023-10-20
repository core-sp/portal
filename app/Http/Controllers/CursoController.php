<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CursoRequest;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CursoController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth', ['except' => ['show', 'cursosView', 'cursosAnterioresView']]);
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('Curso')->listar(auth()->user());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os cursos.");
        }

        return view('admin.crud.home', $dados);
    }

    public function create()
    {
        $this->authorize('create', auth()->user());
        
        try{
            $dados = $this->service->getService('Curso')->view();
            $dados['regionais'] = $this->service->getService('Regional')->getRegionais(); 
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página para criar o curso.");
        }

        return view('admin.crud.criar', $dados);
    }

    public function store(CursoRequest $request)
    {
        $this->authorize('create', auth()->user());

        try{
            $validated = $request->validated();
            $this->service->getService('Curso')->save($validated, auth()->user());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao criar o curso.");
        }

        return redirect()->route('cursos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Curso criado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());
        
        try{
            $dados = $this->service->getService('Curso')->view($id);
            $dados['regionais'] = $this->service->getService('Regional')->getRegionais(); 
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página para editar o curso.");
        }

        return view('admin.crud.editar', $dados);
    }

    public function update(CursoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $this->service->getService('Curso')->save($validated, auth()->user(), $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar o curso.");
        }

        return redirect()->route('cursos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Curso com ID '.$id.' foi atualizado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy($id)
    {
        $this->authorize('delete', auth()->user());
        
        try{
            $this->service->getService('Curso')->destroy($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao cancelar o curso.");
        }

        return redirect()->route('cursos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Curso com ID '.$id.' foi cancelado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function lixeira()
    {
        $this->authorize('onlyAdmin', auth()->user());
        
        try{
            $dados = $this->service->getService('Curso')->lixeira();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os cursos cancelados.");
        }

        return view('admin.crud.lixeira', $dados);
    }

    public function restore($id)
    {
        $this->authorize('onlyAdmin', auth()->user());
        
        try{
            $this->service->getService('Curso')->restore($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao restaurar o curso.");
        }

        return redirect()->route('cursos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Curso com ID '.$id.' foi restaurado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());
        
        try{
            $busca = $request->q;
            $dados = $this->service->getService('Curso')->buscar($busca, auth()->user());
            $dados['busca'] = $busca;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em cursos.");
        }

        return view('admin.crud.home', $dados);
    }

    public function show($id)
    {
        try{
            $curso = $this->service->getService('Curso')->show($id);
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, "Curso não encontrado.");
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página do curso no portal.");
        }

        return response()
            ->view('site.curso', compact('curso'))
            ->header('Cache-Control','no-cache');
    }

    public function cursosView()
    {
        try{
            $cursos = $this->service->getService('Curso')->siteGrid();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os cursos no portal.");
        }

        return response()
            ->view('site.cursos', compact('cursos'))
            ->header('Cache-Control','no-cache');
    }

    public function cursosAnterioresView()
    {        
        try{
            $cursos = $this->service->getService('Curso')->cursosAnteriores();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os cursos anteriores no portal.");
        }

        return response()
            ->view('site.cursos-anteriores', compact('cursos'))
            ->header('Cache-Control','no-cache');
    }
}
