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

        $ips = explode(';', env('GETNET_IPS'));
        $this->can_notification = (config('app.env') != 'production') || ((config('app.env') == 'production') && in_array(request()->ip(), $ips));

        // para teste
        $hora = (int) now()->format('H');
        $this->checkoutIframe = ($hora % 2) == 0;
        $this->middleware(function ($request, $next) {
            // para testes
            $pode_acessar = (auth()->guard('representante')->check() && auth()->guard('representante')->user()->id == 1) || auth()->guard('web')->check();
            if($pode_acessar || \Route::is('pagamento.transacao.*')){
                if(\Route::is('pagamento.admin.*') && !auth()->guard('web')->check())
                    return redirect()->route('site.home');
                return $next($request);
            }
            return redirect()->route('site.home');
        });
    }

    public function index()
    {
        try{
            $dados = $this->service->getService('Pagamento')->listar();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os pagamentos on-line.");
        }

        return view('admin.crud.home', $dados);
    }

    public function busca(Request $request)
    {
        try{
            $dados = $this->service->getService('Pagamento')->buscar($request->q);
            $dados['busca'] = $request->q;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em pagamentos.");
        }

        return view('admin.crud.home', $dados);
    }

    public function pagamentoGerentiView($cobranca)
    {
        try{
            if(request()->session()->exists('errors'))
                session()->forget(['_old_input']);

            $user = auth()->user();
            // cobranca pego do gerenti e deve estar relacionado com o usuário autenticado e não deve estar pago
            $existe = $user->existePagamentoAprovado($cobranca);
            if($existe)
                return redirect()->route($user::NAME_ROUTE . '.dashboard')->with([
                    'message-cartao' => '<i class="fas fa-times"></i> Pagamento já realizado pelo portal para esta cobrança.',
                    'class' => 'alert-danger',
                ]);
            
            $cobranca_dados['valor'] = '2,00';
            $tiposPag = $this->checkoutIframe ? $this->service->getService('Pagamento')->getTiposPagamentoCheckout() : $this->service->getService('Pagamento')->getTiposPagamento();
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar dados do servidor para verificar pagamento");
        }

        return view('site.' . $user::NAME_VIEW . '.pagamento', [
            'cobranca' => $cobranca, 'cobranca_dados' => $cobranca_dados, 'checkoutIframe' => $this->checkoutIframe, 'tiposPag' => $tiposPag
        ]);
    }

    public function pagamentoGerenti($cobranca, PagamentoGerentiRequest $request)
    {
        try{
            $user = auth()->user();
            $cobranca_dados = $request->validated();
            // cobranca pego do gerenti e deve estar relacionado com o usuário autenticado e não deve estar pago
            $existe = $user->existePagamentoAprovado($cobranca);
            if($existe)
                return redirect()->route($user::NAME_ROUTE . '.dashboard')->with([
                    'message-cartao' => '<i class="fas fa-ban"></i> Pagamento já realizado pelo portal para esta cobrança.',
                    'class' => 'alert-danger',
                ]);

            unset($cobranca_dados['amount_soma']);

            $pagamento = true;
            $is_3ds = strpos($cobranca_dados['tipo_pag'], '_3ds') !== false;

            if($this->checkoutIframe)
                $pagamento = $this->service->getService('Pagamento')->checkoutIframe($cobranca_dados, $user);

            $tiposPag = $this->checkoutIframe ? $this->service->getService('Pagamento')->getTiposPagamentoCheckout() : $this->service->getService('Pagamento')->getTiposPagamento();
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao processar dados do servidor para pagamento online");
        }

        return view('site.' . $user::NAME_VIEW . '.pagamento', [
            'pagamento' => $pagamento, 'cobranca' => $cobranca, 'cobranca_dados' => $cobranca_dados, 'is_3ds' => $is_3ds, 'checkoutIframe' => $this->checkoutIframe, 
            'tiposPag' => $tiposPag
        ]);
    }

    public function checkoutIframeSucesso($cobranca)
    {
        try{
            $user = auth()->user();

            if(!$this->checkoutIframe || (url()->previous() != route('pagamento.gerenti', $cobranca)))
                return redirect()->route($user::NAME_ROUTE . '.dashboard');

            // cobranca pego do gerenti e deve estar relacionado com o usuário autenticado e não deve estar pago
            \Log::channel('externo')->info('[IP: '.request()->ip().'] - '.'Usuário '.$user->id.' ("' . formataCpfCnpj($user->cpf_cnpj) . '", login como: '.$user::NAME_AREA_RESTRITA.') aviso que realizou o pagamento da cobrança *'.$cobranca.'* via Checkout Iframe. Aguardando notificação para registro no banco de dados.');
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar dados da cobrança após pagamento online via checkout");
        }

        return redirect()->route($user::NAME_ROUTE . '.dashboard')->with([
            'message-cartao' => '<i class="fas fa-check"></i> Pagamento realizado para a cobrança ' . $cobranca . '. Detalhes do pagamento enviado para o e-mail: ' . $user->email,
            'class' => 'alert-success',
        ]);
    }

    public function pagamentoCartao($cobranca, PagamentoGetnetRequest $request)
    {
        try{
            $user = auth()->user();
            if($this->checkoutIframe)
                throw new \Exception('Página de pagamento não pode ser acessada devido ao uso do Checkout Iframe', 401);

            // cobranca pego do gerenti e deve estar relacionado com o usuário autenticado e não deve estar pago
            $existe = $user->existePagamentoAprovado($cobranca);
            if($existe)
                return redirect()->route($user::NAME_ROUTE . '.dashboard')->with([
                    'message-cartao' => '<i class="fas fa-ban"></i> Pagamento já realizado pelo portal para esta cobrança.',
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
            \Log::channel('externo')->info('[IP: '.$request->ip().'] - '.'Usuário '.$user->id.' ("' . formataCpfCnpj($user->cpf_cnpj) . '", login como: '.$user::NAME_AREA_RESTRITA.') recebeu um código de erro *'.$e->getCode().'* ao tentar realizar o pagamento da cobrança *'.$cobranca.'*. Erro registrado no Log de Erros.');
            
            session()->regenerate();

            return redirect()->route($user::NAME_ROUTE . '.dashboard')->with([
                'message-cartao' => '<i class="fas fa-times"></i> Não foi possível completar a operação! ' . $msg,
                'class' => 'alert-danger',
            ]);
        }

        session()->regenerate();

        return redirect()->route($user::NAME_ROUTE . '.dashboard')->with($transacao);
    }

    public function cancelarPagamentoCartaoView($cobranca, $id_pagamento)
    {
        try{
            $user = auth()->user();
            // verifica se o cobranca existe no gerenti em relação ao usuario em condições de ser cancelado
            $dados = $user->getPagamento($cobranca, $id_pagamento);
            $temp = $dados->first();
            if(!isset($temp) || !$temp->canCancel())
                return redirect()->route($user::NAME_ROUTE . '.dashboard')->with([
                    'message-cartao' => '<i class="fas fa-ban"></i> Pagamento não encontrado / cancelamento não é mais possível para esta cobrança.',
                    'class' => 'alert-danger',
                ]);
            $cancelamento = true;
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar dados do servidor para verificar o cancelamento do pagamento");
        }

        return view('site.' . $user::NAME_VIEW . '.pagamento', [
            'cancelamento' => $cancelamento, 'cobranca' => $cobranca, 'id_pagamento' => $id_pagamento, 'dados' => $dados, 'checkoutIframe' => $this->checkoutIframe
        ]);
    }

    public function cancelarPagamentoCartao($cobranca, $id_pagamento, Request $request)
    {
        try{
            $user = auth()->user();
            // verifica se o cobranca existe no gerenti em relação ao usuario em condições de ser cancelado
            $pagamentos = $user->getPagamento($cobranca, $id_pagamento);
            $temp = $pagamentos->first();

            if(!isset($temp) || !$temp->canCancel())
                return redirect()->route($user::NAME_ROUTE . '.dashboard')->with([
                    'message-cartao' => '<i class="fas fa-ban"></i> Pagamento não encontrado / cancelamento não é mais possível para esta cobrança.',
                    'class' => 'alert-danger',
                ]);

            $dados['cobranca'] = $cobranca;
            $dados['pagamento'] = $pagamentos;
            $transacao = $this->service->getService('Pagamento')->cancelCheckout($dados, $user);
        }catch(\Exception $e){
            $temp = $this->service->getService('Pagamento')->getException($e->getMessage(), $e->getCode());
            $msg = isset($temp) ? $temp : 'Erro ao processar o cancelamento do pagamento. Código de erro: ' . $e->getCode();

            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            \Log::channel('externo')->info('[IP: '.$request->ip().'] - '.'Usuário '.$user->id.' ("' . formataCpfCnpj($user->cpf_cnpj) . '", login como: '.$user::NAME_AREA_RESTRITA.') recebeu um código de erro *'.$e->getCode().'* ao tentar realizar o cancelamento do pagamento com a id *'.$id_pagamento.'* da cobrança com a id: *'.$cobranca . '*. Erro registrado no Log de Erros.');
            
            return redirect()->route($user::NAME_ROUTE . '.dashboard')->with([
                'message-cartao' => '<i class="fas fa-times"></i> Não foi possível completar a operação! ' . $msg,
                'class' => 'alert-danger',
            ]);
        }

        return redirect()->route($user::NAME_ROUTE . '.dashboard')->with($transacao);
    }

    public function pagamentoView($cobranca, $pagamento_id)
    {
        try{
            $user = auth()->user();
            $dados = $user->getPagamento($cobranca, $pagamento_id);
            if($dados->isEmpty())
                return redirect()->route($user::NAME_ROUTE . '.dashboard')->with([
                    'message-cartao' => '<i class="fas fa-ban"></i> Não existe pagamento para esta cobrança e ID.',
                    'class' => 'alert-danger',
                ]);
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar dados do servidor para visualizar pagamento");
        }

        return view('site.' . $user::NAME_VIEW . '.pagamento', [
            'cobranca' => $cobranca, 'dados' => $dados, 'checkoutIframe' => $this->checkoutIframe
        ]);
    }

    public function cardsBrand($cobranca, $bin)
    {
        try{
            $user = auth()->user();

            if($this->checkoutIframe)
                throw new \Exception('Rota não pode ser acessada devido ao uso do Checkout Iframe', 401);

            if(url()->previous() != route('pagamento.gerenti', $cobranca))
                throw new \Exception('Usuário não prosseguiu com o fluxo correto de pagamento para acessar a rota atual', 500);

            // confere se o cobranca existe no gerenti para o usuário autenticado e traz os dados restantes que precisa para pagar
            if($cobranca == 5)
                throw new \Exception('Cobrança não encontrada!', 404);

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

        return response()->json($dados);
    }

    public function authenticationResults(Request $request)
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

    public function getTransacaoCredito(NotificacaoGetnetRequest $request)
    {
        try{
            if(!$this->can_notification)
            {
                \Log::error('[Transação Getnet] - IP: ' . $request->ip() . ' enviou uma notificação de transação com a payment_id: ' . $request->input('payment_id') . ' e customer_id: ' . $request->input('customer_id') . ', mas o ip não é permitido. Notificação não foi aceita.');
                return;
            }

            $dados = $request->validated();
            $request->replace([]);
            request()->replace([]);
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
            {
                \Log::error('[Transação Getnet] - IP: ' . $request->ip() . ' enviou uma notificação de transação com a payment_id: ' . $request->input('payment_id') . ' e customer_id: ' . $request->input('customer_id') . ', mas o ip não é permitido. Notificação não foi aceita.');
                return;
            }

            $dados = $request->validated();
            $request->replace([]);
            request()->replace([]);
            $dados['checkoutIframe'] = $this->checkoutIframe;
            $dados = $this->service->getService('Pagamento')->rotinaUpdateTransacao($dados);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
        }

        return null;
    }
}
