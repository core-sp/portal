<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\GerentiRepositoryInterface;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\CursoInscricaoRequest;

class CursoInscritoController extends Controller
{
    private $gerentiRepository;
    private $service;
    
    public function __construct(GerentiRepositoryInterface $gerentiRepository, MediadorServiceInterface $service)
    {
        $this->middleware('auth', ['except' => ['inscricao', 'inscricaoView']]);
        $this->gerentiRepository = $gerentiRepository;
        $this->service = $service;
    }

    public function index($idcurso)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $curso = $this->service->getService('Curso')->show($idcurso);
            $dados = $this->service->getService('Curso')->inscritos()->listar($curso, auth()->user());
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, "Curso não encontrado.");
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as inscrições do curso com ID ".$idcurso.".");
        }

        return view('admin.crud.home', $dados);
    }

    public function create($idcurso)
    {
        $this->authorize('create', auth()->user());
        
        try{
            $curso = $this->service->getService('Curso')->show($idcurso);
            $dados = $this->service->getService('Curso')->inscritos()->view($curso);
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, "Curso não encontrado.");
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao carregar a página para adicionar um inscrito no curso com ID ".$idcurso.".");
        }

        return view('admin.crud.criar', $dados);
    }

    // inscrição via área admin
    public function store(CursoInscricaoRequest $request, $idcurso)
    {
        $this->authorize('create', auth()->user());
        
        try{
            $validated = $request->validated();
            $curso = $this->service->getService('Curso')->show($idcurso);
            $validated['conta_no_portal'] = $this->service->getService('Representante')->getRepresentanteByCpfCnpj(apenasNumeros($validated['cpf']));
            $dados = $this->service->getService('Curso')->inscritos()->save($validated, auth()->user(), $curso);
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, "Curso não encontrado.");
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao adicionar um inscrito no curso com ID ".$idcurso.".");
        }

        return redirect()->route('inscritos.index', $idcurso)
            ->with('message', '<i class="icon fa fa-check"></i>Participante inscrito com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());
        
        try{
            $dados = $this->service->getService('Curso')->inscritos()->view(null, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página para editar um inscrito com ID ".$id.".");
        }

        return view('admin.crud.editar', $dados);
    }

    // atualizar inscrição via área admin
    public function update(CursoInscricaoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());
        
        try{
            $validated = $request->validated();
            $dados = $this->service->getService('Curso')->inscritos()->save($validated, auth()->user(), null, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao editar um inscrito com ID ".$id.".");
        }

        return redirect()->route('inscritos.index', $dados['idcurso'])
            ->with('message', '<i class="icon fa fa-check"></i>Participante com ID '.$id.' foi editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function inscricaoView($idcurso)
    {
        try{
            $curso = $this->service->getService('Curso')->show($idcurso, true);
            $rep = auth()->guard('representante')->check();
            $dados = array();

            if($rep)
                $dados = $this->service->getService('Representante')->getDadosInscricaoCurso(auth()->guard('representante')->user(), $this->gerentiRepository);
            $situacao = isset($dados['situacao']) ? $dados['situacao'] : '';

            $verifica = $this->service->getService('Curso')->inscritos()->liberarInscricao($curso, auth()->guard('representante')->user(), $situacao);
            if(!empty($verifica))
                return redirect()->route($verifica['rota'])->with($verifica);

            $dados['curso'] = $curso;
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, "Curso não encontrado.");
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar página de inscrição no curso com ID ".$idcurso.".");
        }

        return view('site.curso-inscricao', $dados);
    }

    // inscrição via área aberta
    public function inscricao(CursoInscricaoRequest $request, $idcurso)
    {
        try{
            $validated = $request->validated();
            $curso = $this->service->getService('Curso')->show($idcurso, true);

            $situacao = isset($validated['situacao']) ? $validated['situacao'] : '';
            unset($validated['situacao']);

            $verifica = $this->service->getService('Curso')->inscritos()->liberarInscricao($curso, auth()->guard('representante')->user(), $situacao);
            if(!empty($verifica))
                return redirect()->route($verifica['rota'])->with($verifica);

            $validated['conta_no_portal'] = $this->service->getService('Representante')->getRepresentanteByCpfCnpj(apenasNumeros($validated['cpf']));
            $dados = $this->service->getService('Curso')->inscritos()->inscricaoExterna($curso, $validated);
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, "Curso não encontrado.");
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao salvar inscrição no curso com ID ".$idcurso.".");
        }
        
        return view('site.agradecimento')->with($dados);
    }

    public function destroy($id)
    {
        $this->authorize('delete', auth()->user());
        
        try{
            $dados = $this->service->getService('Curso')->inscritos()->destroy($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao cancelar a inscrição com ID ".$id.".");
        }

        return redirect()->route('inscritos.index', $dados['idcurso'])
            ->with('message', '<i class="icon fa fa-check"></i>Inscrição com ID '.$id.' foi cancelada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca(Request $request, $id)
    {
        $this->authorize('viewAny', auth()->user());
        
        try{
            $busca = $request->q;
            $curso = $this->service->getService('Curso')->show($id);
            $dados = $this->service->getService('Curso')->inscritos()->buscar($curso, $busca, auth()->user());
            $dados['busca'] = $busca;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em inscritos no curso com ID ".$id.".");
        }

        return view('admin.crud.home', $dados);
    }

    public function download($id)
    {
        $this->authorize('viewAny', auth()->user());
        
        try{
            $callback = $this->service->getService('Curso')->downloadInscricoes($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao realizar download dos inscritos no curso com ID ".$id.".");
        }

        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=inscritos-'.$id.'.csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ];

        return response()->stream($callback, 200, $headers);
    }

    public function updatePresenca(CursoInscricaoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());
        
        try{
            $validated = $request->validated();
            $this->service->getService('Curso')->inscritos()->updatePresenca($id, $validated);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [400]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao atualizar a presença da inscrição com ID ".$id.".");
        }

        return redirect()->back()
            ->with('message', '<i class="icon fa fa-check"></i>Inscrição com ID '.$id.' teve a presença atualizada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function reenviarCodCertificado(Request $request, $id)
    {
        $this->authorize('updateOther', auth()->user());
        
        try{
            $msg = $this->service->getService('Curso')->inscritos()->reenviarCodigo($id, $this->service);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [400]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao reenviar código do certificado da inscrição com ID ".$id.".");
        }

        return isset($msg['message']) ? redirect()->back()->with($msg) : redirect()->back()
            ->with('message', '<i class="icon fa fa-check"></i>Inscrição com ID '.$id.' teve o código do certificado reenviado com sucesso!')
            ->with('class', 'alert-success');
    }
}
