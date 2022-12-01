<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\PagamentoGetnetRequest;
use App\Http\Requests\PagamentoGerentiRequest;
use App\Http\Requests\NotificacaoGetnetRequest;

class PagamentoController extends Controller
{
    private $service;
    private $checkoutIframe;
    private $can_notification;

    public function __construct(MediadorServiceInterface $service) 
    {
        $this->service = $service;

        // opção para chamar checkout iframe para uma situação específica, ou pode ser geral
        $this->checkoutIframe = false;
        $this->middleware(function ($request, $next) {
            if(isset(auth()->user()->id))
                $this->checkoutIframe = auth()->user()->id != 1;
            return $next($request);
        });

        $ips = ['201.87.185.248', '201.87.185.249', '201.87.188.248', '201.87.188.249'];
        $this->can_notification = (config('app.env') != 'production') || ((config('app.env') == 'production') && in_array(request()->ip(), $ips));
    }

    // Visualizar pagamentos no Admin, apenas como consulta
    public function index()
    {
        // $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('Pagamento')->listar();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os pagamentos on-line.");
        }

        return view('admin.crud.home', $dados);
    }

    // Buscar pagamentos no Admin, apenas como consulta
    public function busca(Request $request)
    {
        // $this->authorize('viewAny', auth()->user());

        try{
            $busca = $request->q;
            $dados = $this->service->getService('Pagamento')->buscar($busca);
            $dados['busca'] = $busca;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em pagamentos.");
        }

        return view('admin.crud.home', $dados);
    }

    public function pagamentoGerentiView($boleto)
    {
        try{
            if(request()->session()->exists('errors'))
                session()->forget(['_old_input']);

            $user = auth()->user();
            // boleto pego do gerenti e deve estar relacionado com o usuário autenticado e não deve estar pago
            $existe = $user->existePagamentoAprovado($boleto);
            if($existe)
                return redirect(route($user::NAME_ROUTE . '.dashboard'))->with([
                    'message-cartao' => '<i class="fas fa-times"></i> Pagamento já realizado pelo portal para este boleto.',
                    'class' => 'alert-danger',
                ]);

            $boleto_dados['valor'] = '1503,03';
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar dados do servidor para verificar pagamento");
        }

        return view('site.' . $user::NAME_VIEW . '.pagamento')->with([
            'boleto' => $boleto, 'boleto_dados' => $boleto_dados, 'checkoutIframe' => $this->checkoutIframe
        ]);
    }

    public function pagamentoGerenti($boleto, PagamentoGerentiRequest $request)
    {
        try{
            $user = auth()->user();
            $boleto_dados = $request->validated();
            // boleto pego do gerenti e deve estar relacionado com o usuário autenticado e não deve estar pago
            $existe = $user->existePagamentoAprovado($boleto);
            if($existe)
                return redirect(route($user::NAME_ROUTE . '.dashboard'))->with([
                    'message-cartao' => '<i class="fas fa-ban"></i> Pagamento já realizado pelo portal para este boleto.',
                    'class' => 'alert-danger',
                ]);

            unset($boleto_dados['amount_soma']);

            $pagamento = true;
            $is_3ds = strpos($boleto_dados['tipo_pag'], '_3ds') !== false;

            if($this->checkoutIframe)
                $pagamento = $this->service->getService('Pagamento')->checkoutIframe($boleto_dados, $user);
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao processar dados do servidor para pagamento online");
        }

        return view('site.' . $user::NAME_VIEW . '.pagamento')->with([
            'pagamento' => $pagamento, 'boleto' => $boleto, 'boleto_dados' => $boleto_dados, 'is_3ds' => $is_3ds, 'checkoutIframe' => $this->checkoutIframe
        ]);
    }

    public function checkoutIframeSucesso($boleto)
    {
        try{
            $user = auth()->user();

            if(!$this->checkoutIframe || (url()->previous() != route('pagamento.gerenti', $boleto)))
                return redirect(route($user::NAME_ROUTE . '.dashboard'));

            // boleto pego do gerenti e deve estar relacionado com o usuário autenticado e não deve estar pago
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar dados do boleto após pagamento online via checkout");
        }

        return redirect(route($user::NAME_ROUTE . '.dashboard'))->with([
            'message-cartao' => '<i class="fas fa-check"></i> Pagamento realizado para o boleto ' . $boleto . '. Detalhes do pagamento enviado para o e-mail: ' . $user->email,
            'class' => 'alert-success',
        ]);
    }

    public function pagamentoCartao($boleto, PagamentoGetnetRequest $request)
    {
        try{
            if($this->checkoutIframe)
                throw new \Exception('Página de pagamento não pode ser acessada devido ao uso do Checkout Iframe', 401);

            $user = auth()->user();
            // boleto pego do gerenti e deve estar relacionado com o usuário autenticado e não deve estar pago
            $existe = $user->existePagamentoAprovado($boleto);
            if($existe)
                return redirect(route($user::NAME_ROUTE . '.dashboard'))->with([
                    'message-cartao' => '<i class="fas fa-ban"></i> Pagamento já realizado pelo portal para este boleto.',
                    'class' => 'alert-danger',
                ]);

            $validate = $request->validated();
            unset($validate['amount_soma']);
            $request->replace([]);
            request()->replace([]);

            $transacao = $this->service->getService('Pagamento')->checkout($request->ip(), $validate, $user);

            unset($validate);
        }catch(\Exception $e){
            $temp = $this->service->getService('Pagamento')->getException($e->getMessage(), $e->getCode());
            $msg = isset($temp) ? $temp : 'Erro ao processar o pagamento. Código de erro: ' . $e->getCode();

            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            \Log::channel('externo')->info('[IP: '.$request->ip().'] - '.'Usuário '.$user->id.' ('.$user::NAME_AREA_RESTRITA.') recebeu um código de erro *'.$e->getCode().'* ao tentar realizar o pagamento do boleto *'.$boleto.'*. Erro registrado no Log de Erros.');
            
            return redirect(route($user::NAME_ROUTE . '.dashboard'))->with([
                'message-cartao' => '<i class="fas fa-times"></i> Não foi possível completar a operação! ' . $msg,
                'class' => 'alert-danger',
            ]);
        }

        return redirect(route($user::NAME_ROUTE . '.dashboard'))->with($transacao);
    }

    public function cancelarPagamentoCartaoView($boleto, $id_pagamento)
    {
        try{
            $user = auth()->user();
            // verifica se o boleto existe no gerenti em relação ao usuario em condições de ser cancelado
            $dados = $user->getPagamento($boleto, $id_pagamento);
            $temp = $dados->first();
            if(!isset($temp) || !$temp->canCancel())
                return redirect(route($user::NAME_ROUTE . '.dashboard'))->with([
                    'message-cartao' => '<i class="fas fa-ban"></i> Pagamento não encontrado / cancelamento não é mais possível para este boleto.',
                    'class' => 'alert-danger',
                ]);
            $cancelamento = true;
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar dados do servidor para verificar o cancelamento do pagamento");
        }

        return view('site.' . $user::NAME_VIEW . '.pagamento')->with([
            'cancelamento' => $cancelamento, 'boleto' => $boleto, 'id_pagamento' => $id_pagamento, 'dados' => $dados, 'checkoutIframe' => $this->checkoutIframe
        ]);
    }

    public function cancelarPagamentoCartao($boleto, $id_pagamento, Request $request)
    {
        try{
            $user = auth()->user();
            // verifica se o boleto existe no gerenti em relação ao usuario em condições de ser cancelado
            $pagamentos = $user->getPagamento($boleto, $id_pagamento);
            $temp = $pagamentos->first();

            if(!isset($temp) || !$temp->canCancel())
                return redirect(route($user::NAME_ROUTE . '.dashboard'))->with([
                    'message-cartao' => '<i class="fas fa-ban"></i> Pagamento não encontrado / cancelamento não é mais possível para este boleto.',
                    'class' => 'alert-danger',
                ]);

            $dados['boleto'] = $boleto;
            $dados['pagamento'] = $pagamentos;
            $transacao = $this->service->getService('Pagamento')->cancelCheckout($dados, $user);
        }catch(\Exception $e){
            $temp = $this->service->getService('Pagamento')->getException($e->getMessage(), $e->getCode());
            $msg = isset($temp) ? $temp : 'Erro ao processar o pagamento. Código de erro: ' . $e->getCode();

            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            \Log::channel('externo')->info('[IP: '.$request->ip().'] - '.'Usuário '.$user->id.' ('.$user::NAME_AREA_RESTRITA.') recebeu um código de erro *'.$e->getCode().'* ao tentar realizar o cancelamento do pagamento com a id *'.$id_pagamento.'* do boleto com a id: *'.$boleto . '*. Erro registrado no Log de Erros.');
            
            return redirect(route($user::NAME_ROUTE . '.dashboard'))->with([
                'message-cartao' => '<i class="fas fa-times"></i> Não foi possível completar a operação! ' . $msg,
                'class' => 'alert-danger',
            ]);
        }

        return redirect(route($user::NAME_ROUTE . '.dashboard'))->with($transacao);
    }

    public function pagamentoView($boleto, $pagamento_id)
    {
        try{
            $user = auth()->user();
            $dados = $user->getPagamento($boleto, $pagamento_id);
            if($dados->isEmpty())
                return redirect(route($user::NAME_ROUTE . '.dashboard'))->with([
                    'message-cartao' => '<i class="fas fa-ban"></i> Não existe pagamento para este boleto e ID.',
                    'class' => 'alert-danger',
                ]);
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar dados do servidor para visualizar pagamento");
        }

        return view('site.' . $user::NAME_VIEW . '.pagamento')->with([
            'boleto' => $boleto, 'dados' => $dados, 'checkoutIframe' => $this->checkoutIframe
        ]);
    }

    public function cardsBrand($boleto, $bin)
    {
        try{
            if($this->checkoutIframe)
                throw new \Exception('Rota não pode ser acessada devido ao uso do Checkout Iframe', 401);

            if(url()->previous() != route('pagamento.gerenti', $boleto))
                throw new \Exception('Usuário não prosseguiu com o fluxo correto de pagamento para acessar a rota atual', 500);

            $user = auth()->user();
            // confere se o boleto existe no gerenti para o usuário autenticado e traz os dados restantes que precisa para pagar
            if($boleto == 5)
                throw new \Exception('Boleto não encontrado!', 404);

            $dados = $this->service->getService('Pagamento')->getDados3DS($bin);
            $token = $dados['token'];
            $token_principal = $dados['token_principal'];

            unset($dados['token']);
            unset($dados['token_principal']);
        } catch (\Exception $e) {
            $temp = $this->service->getService('Pagamento')->getException($e->getMessage(), $e->getCode());
            $msg = isset($temp) ? $temp : $e->getMessage();

            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, $msg);
        }

        return response()->json($dados, 200, ['Authorization_principal' => $token_principal, 'Authorization' => $token]);
    }

    public function generateToken(Request $request)
    {
        if($this->checkoutIframe)
            throw new \Exception('Rota não pode ser acessada devido ao uso do Checkout Iframe', 401);

        try{
            $token = '';
            if($request->hasHeader('authorization'))
                $token = trim(\Str::after($request->header('authorization'), 'Bearer '));
            else
                throw new \Exception('Faltou autenticação da api', 401);
            $dados = $request->all();
            $dados['Authorization'] = $token;
            $dados = $this->service->getService('Pagamento')->autenticacao3DS($dados, \Route::currentRouteName());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            $dados = [
                'status' => $e->getCode(),
                'message' => 'ERROR',
                'data' => [],
                'error' => [$e->getMessage()],
            ];
        }

        return response()->json($dados);
    }

    public function authentications(Request $request)
    {
        if($this->checkoutIframe)
            throw new \Exception('Rota não pode ser acessada devido ao uso do Checkout Iframe', 401);

        try{
            $token = '';
            if($request->hasHeader('authorization'))
                $token = trim(\Str::after($request->header('authorization'), 'Bearer '));
            else
                throw new \Exception('Faltou autenticação da api', 401);
            $dados = $request->all();
            $dados['Authorization'] = $token;
            $dados['ip_address'] = request()->ip();
            $dados = $this->service->getService('Pagamento')->autenticacao3DS($dados, \Route::currentRouteName());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            $dados = [
                'status' => $e->getCode(),
                'message' => 'ERROR',
                'data' => [],
                'error' => [$e->getMessage()],
            ];
        }

        \Log::error(response()->json($dados));
        return response()->json($dados);
    }

    public function authenticationsResults(Request $request)
    {
        if($this->checkoutIframe)
            throw new \Exception('Rota não pode ser acessada devido ao uso do Checkout Iframe', 401);

        try{
            $dados = $this->service->getService('Pagamento')->autenticacao3DS($request, \Route::currentRouteName());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
        }

        return response()->json($dados);
    }

    public function getTransacaoCredito(NotificacaoGetnetRequest $request)
    {
        try{
            if(!$this->can_notification)
                return;

            $dados = $request->validated();
            $dados['checkoutIframe'] = $this->checkoutIframe;
            $dados = $this->service->getService('Pagamento')->rotinaUpdateTransacao($dados);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
        }

        return null;
    }

    public function getTransacaoDebito(NotificacaoGetnetRequest $request)
    {
        try{
            if(!$this->can_notification)
                return;

            $dados = $request->validated();
            $dados['checkoutIframe'] = $this->checkoutIframe;
            $dados = $this->service->getService('Pagamento')->rotinaUpdateTransacao($dados);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
        }

        return null;
    }
}