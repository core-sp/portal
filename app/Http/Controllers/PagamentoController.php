<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;

class PagamentoController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service) 
    {
        $this->middleware('auth:representante');
        $this->service = $service;
    }

    // teste
    public function cardsBrand($boleto, $bin)
    {
        try{
            if(auth()->guard('representante')->check() && (url()->previous() != route('representante.pagamento.gerenti', $boleto)))
                throw new \Exception('Usuário não prosseguiu com o fluxo correto de pagamento para acessar a rota atual', 500);

            $user = auth()->guard('representante')->user();
            // confere se o boleto existe no gerenti para o usuário autenticado e traz os dados restantes que precisa para pagar
            if($boleto == 5)
                throw new \Exception('Boleto não encontrado!', 404);
            $dados = $this->service->getService('Pagamento')->bin3DS($bin);
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
        $dados = $request;
        // try{
        //     $dados = $this->service->getService('Pagamento')->etapa2_3DS($request);
        // } catch (\Exception $e) {
        //     \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
        // }

        return response()->json($dados);
    }

    public function authentications(Request $request)
    {
        $dados = $request;
        // try{
        //     $dados = $this->service->getService('Pagamento')->etapa2_3DS($request);
        // } catch (\Exception $e) {
        //     \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
        // }

        return response()->json($dados);
    }

    public function authenticationsResults(Request $request)
    {
        $dados = $request;
        // try{
        //     $dados = $this->service->getService('Pagamento')->etapa2_3DS($request);
        // } catch (\Exception $e) {
        //     \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
        // }

        return response()->json($dados);
    }
}
