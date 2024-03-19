<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\GerarTextoRequest;
use Illuminate\Support\Str;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GerarTextoController extends Controller
{
    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth', ['except' => ['show', 'buscar']]);
        $this->service = $service;
    }
    
    public function create($tipo_doc)
    {
        $this->authorize('gerarTextoUpdate', auth()->user());

        try{
            $texto = $this->service->getService('GerarTexto')->criar($tipo_doc);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao criar o texto do documento ".$tipo_doc.".");
        }

        return redirect()->route('textos.view', $tipo_doc)
            ->with('message', '<i class="icon fa fa-check"></i>Novo texto com o título: "'.$texto->texto_tipo.'" foi criado com sucesso e inserido no final do sumário!')
            ->with('class', 'alert-success')
            ->with('novo_texto', $texto->id);
    }

    public function updateCampos($tipo_doc, $id, GerarTextoRequest $request)
    {
        $this->authorize('gerarTextoUpdate', auth()->user());

        try{
            $dados = $request->validated();
            $ok = $this->service->getService('GerarTexto')->update($tipo_doc, $dados, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar os campos do texto do documento ".$tipo_doc.".");
        }

        return response()->json($ok);
    }

    public function delete(GerarTextoRequest $request, $tipo_doc)
    {
        $this->authorize('gerarTextoUpdate', auth()->user());

        try{
            $dados = $request->validated();
            $ok = $this->service->getService('GerarTexto')->excluir($tipo_doc, $dados['excluir_ids']);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [400]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao excluir o texto do documento ".$tipo_doc.".");
        }

        return response()->json($ok);
    }

    public function view($tipo_doc, $id = null)
    {
        $user = auth()->user();
        $this->authorize('gerarTextoView', $user);

        try{
            $dados = $this->service->getService('GerarTexto')->view($tipo_doc, $id);
            $dados['orientacao_sumario'] = session()->exists('orientacao_sumario') ? session('orientacao_sumario') : $dados['orientacao_sumario'];
            $dados['tipo_doc'] = $tipo_doc;
            $dados['can_update'] = $user->can('gerarTextoUpdate', $user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os textos de ".$tipo_doc.".");
        }

        return isset($id) ? response()->json($dados['resultado']) : view('admin.crud.editar', $dados);
    }

    public function orientacaoSumario($tipo_doc, $orientacao)
    {
        try{
            session()->put('orientacao_sumario', $orientacao);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao mudar orientação do sumário em ".$tipo_doc.".");
        }

        return redirect()->route('textos.view', ['tipo_doc' => $tipo_doc, 'id' => null]);
    }

    public function update($tipo_doc, Request $request)
    {
        $this->authorize('gerarTextoUpdate', auth()->user());

        try{
            $dados = $request->except(['_token', '_method']);
            $this->service->getService('GerarTexto')->update($tipo_doc, $dados);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar a índice do documento ".$tipo_doc.".");
        }
        
        return redirect()->route('textos.view', $tipo_doc)
            ->with('message', '<i class="icon fa fa-check"></i>Índice atualizada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function publicar($tipo_doc, GerarTextoRequest $request)
    {
        $this->authorize('gerarTextoUpdate', auth()->user());

        try{
            $publicar = $request->validated()['publicar'];
            $this->service->getService('GerarTexto')->publicar($tipo_doc, $publicar);
            $texto = !$publicar ? 'Foi revertida a publicação no site' : 'Foi publicada no site';
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar o status de publicação do documento ".$tipo_doc.".");
        }

        return redirect()->route('textos.view', $tipo_doc)
            ->with('message', '<i class="icon fa fa-check"></i>'.$texto.' com sucesso!')
            ->with('class', 'alert-success');
    }

    public function show($id = null)
    {
        try{
            $user = auth()->user();
            $tipo_doc = \Route::currentRouteName();
            $dados = $this->service->getService('GerarTexto')->show($tipo_doc, $id, $user);
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().', para o documento '.$tipo_doc.'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, "Texto não encontrado.");
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os textos do documento ".$tipo_doc.".");
        }
        
        return response()
            ->view('site.'.$tipo_doc, $dados)
            ->header('Cache-Control','no-cache');
    }

    public function buscar(GerarTextoRequest $request)
    {
        try{
            $user = auth()->user();
            $busca = $request->validated()['buscaTexto'];
            $tipo_doc = Str::beforeLast(\Route::currentRouteName(), '-buscar');
            $dados = $this->service->getService('GerarTexto')->buscar($tipo_doc, $busca, $user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar textos no documento ".$tipo_doc.".");
        }

        return response()
            ->view('site.'.$tipo_doc, $dados)
            ->header('Cache-Control','no-cache');
    }
}
