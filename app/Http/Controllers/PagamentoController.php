<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\PagamentoGetnetRequest;
use App\Http\Requests\PagamentoGerentiRequest;
use App\Http\Requests\NotificacaoGetnetRequest;
use App\Repositories\GerentiRepositoryInterface;

class PagamentoController extends Controller
{
    private $service;
    private $checkoutIframe;
    private $can_notification;
    private $gerentiRepository;

    public function __construct(MediadorServiceInterface $service, GerentiRepositoryInterface $gerentiRepository) 
    {
        $this->service = $service;
        $this->gerentiRepository = $gerentiRepository;

        $ips = ['201.87.185.248', '201.87.185.249', '201.87.188.248', '201.87.188.249'];
        $this->can_notification = (config('app.env') != 'production') || ((config('app.env') == 'production') && in_array(request()->ip(), $ips));

        // opção para chamar checkout iframe para uma situação específica, ou pode ser geral
        $this->checkoutIframe = false;
        $this->middleware(function ($request, $next) {
            // para testes
            $possui_ids = isset(auth()->user()->id) || isset(auth()->user()->idusuario);
            $pode_acessar = (auth()->guard('representante')->user()->id == 1) || auth()->guard('web')->check();
            if(($possui_ids && $pode_acessar) || $this->can_notification){
                // $this->checkoutIframe = true;
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
            $busca = $request->q;
            $dados = $this->service->getService('Pagamento')->buscar($busca);
            $dados['busca'] = $busca;
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
            $tiposPag = $this->service->getService('Pagamento')->getTiposPagamento();
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

            if($this->checkoutIframe){
                // temporário
                $contatos = $this->gerentiRepository->gerentiContatos($user->ass_id);
                $enderecos = $this->gerentiRepository->gerentiEnderecos($user->ass_id);
                if(isset($enderecos['Logradouro'])){
                    $cobranca_dados['address_street'] = strpos($enderecos['Logradouro'], ',') !== false ? 
                    substr($enderecos['Logradouro'], 0, strpos($enderecos['Logradouro'], ',')) : substr($enderecos['Logradouro'], 0, 60);
                    $cobranca_dados['address_street_number'] = strpos($enderecos['Logradouro'], ',') !== false ? 
                    substr($enderecos['Logradouro'], strpos($enderecos['Logradouro'], ',') + 1) : '';
                }
                $cobranca_dados['address_complementary'] = isset($enderecos['Complemento']) ? substr(str_replace(',', ' ', $enderecos['Complemento']), 0, 60) : '';
                $cobranca_dados['address_neighborhood'] = isset($enderecos['Bairro']) ? substr(str_replace(',', ' ', $enderecos['Bairro']), 0, 40) : '';
                $cobranca_dados['address_city'] = isset($enderecos['Cidade']) ? substr(str_replace(',', ' ', $enderecos['Cidade']), 0, 20) : '';
                $cobranca_dados['address_state'] = isset($enderecos['UF']) ? substr($enderecos['UF'], 0, 20) : '';
                $cobranca_dados['address_zipcode'] = isset($enderecos['CEP']) ? substr(str_replace('-', '', $enderecos['CEP']), 0, 8) : '';
                foreach($contatos as $contato){
                    if(in_array($contato['CXP_TIPO'], ['8', '7', '6', '1', '2', '4']) && (strlen($contato['CXP_VALOR']) > 5)){
                        $cobranca_dados['phone_number'] = apenasNumeros($contato['CXP_VALOR']);
                        break;
                    }
                }
                $pagamento = $this->service->getService('Pagamento')->checkoutIframe($cobranca_dados, $user);
            }

            $tiposPag = $this->service->getService('Pagamento')->getTiposPagamento();
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
            if($this->checkoutIframe)
                throw new \Exception('Página de pagamento não pode ser acessada devido ao uso do Checkout Iframe', 401);

            $user = auth()->user();
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

            // temporário
            $contatos = $this->gerentiRepository->gerentiContatos($user->ass_id);
            $enderecos = $this->gerentiRepository->gerentiEnderecos($user->ass_id);
            if(isset($enderecos['Logradouro'])){
                $cobranca_dados['ba_street'] = strpos($enderecos['Logradouro'], ',') !== false ? 
                substr($enderecos['Logradouro'], 0, strpos($enderecos['Logradouro'], ',')) : substr($enderecos['Logradouro'], 0, 60);
                $cobranca_dados['ba_number'] = strpos($enderecos['Logradouro'], ',') !== false ? 
                substr($enderecos['Logradouro'], strpos($enderecos['Logradouro'], ',') + 1) : '';
            }
            $validate['ba_complement'] = isset($enderecos['Complemento']) ? substr(str_replace(',', ' ', $enderecos['Complemento']), 0, 60) : '';
            $validate['ba_district'] = isset($enderecos['Bairro']) ? substr(str_replace(',', ' ', $enderecos['Bairro']), 0, 40) : '';
            $validate['ba_city'] = isset($enderecos['Cidade']) ? substr(str_replace(',', ' ', $enderecos['Cidade']), 0, 20) : '';
            $validate['ba_state'] = isset($enderecos['UF']) ? substr($enderecos['UF'], 0, 20) : '';
            $validate['ba_postal_code'] = isset($enderecos['CEP']) ? substr(str_replace('-', '', $enderecos['CEP']), 0, 8) : '';
            foreach($contatos as $contato){
                if(in_array($contato['CXP_TIPO'], ['8', '7', '6', '1', '2', '4']) && (strlen($contato['CXP_VALOR']) > 5)){
                    $validate['phone_number'] = apenasNumeros($contato['CXP_VALOR']);
                    break;
                }
            }
            $transacao = $this->service->getService('Pagamento')->checkout($request->ip(), $validate, $user);

            unset($validate);
        }catch(\Exception $e){
            $temp = $this->service->getService('Pagamento')->getException($e->getMessage(), $e->getCode());
            $msg = isset($temp) ? $temp : 'Erro ao processar o pagamento. Código de erro: ' . $e->getCode();

            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            \Log::channel('externo')->info('[IP: '.$request->ip().'] - '.'Usuário '.$user->id.' ('.$user::NAME_AREA_RESTRITA.') recebeu um código de erro *'.$e->getCode().'* ao tentar realizar o pagamento da cobrança *'.$cobranca.'*. Erro registrado no Log de Erros.');
            
            return redirect()->route($user::NAME_ROUTE . '.dashboard')->with([
                'message-cartao' => '<i class="fas fa-times"></i> Não foi possível completar a operação! ' . $msg,
                'class' => 'alert-danger',
            ]);
        }

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
            $msg = isset($temp) ? $temp : 'Erro ao processar o pagamento. Código de erro: ' . $e->getCode();

            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            \Log::channel('externo')->info('[IP: '.$request->ip().'] - '.'Usuário '.$user->id.' ('.$user::NAME_AREA_RESTRITA.') recebeu um código de erro *'.$e->getCode().'* ao tentar realizar o cancelamento do pagamento com a id *'.$id_pagamento.'* da cobrança com a id: *'.$cobranca . '*. Erro registrado no Log de Erros.');
            
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
            if($this->checkoutIframe)
                throw new \Exception('Rota não pode ser acessada devido ao uso do Checkout Iframe', 401);

            if(url()->previous() != route('pagamento.gerenti', $cobranca))
                throw new \Exception('Usuário não prosseguiu com o fluxo correto de pagamento para acessar a rota atual', 500);

            $user = auth()->user();
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
