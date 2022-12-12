<?php

namespace Tests\Feature;

use App\Pagamento;
use App\Permissao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class PagamentoTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES NO ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $pagamento = factory('App\Pagamento')->create();

        // admin
        $this->get(route('pagamento.admin.index'))->assertRedirect(route('site.home'));
        $this->get(route('pagamento.admin.busca'))->assertRedirect(route('site.home'));

        // site
        $this->get(route('pagamento.view', 1))->assertRedirect(route('representante.login'));
        $this->post(route('pagamento.gerenti', 1))->assertRedirect(route('representante.login'));
        $this->get(route('pagamento.sucesso.checkout', 1))->assertRedirect(route('representante.login'));
        $this->post(route('pagamento.cartao', 1))->assertRedirect(route('representante.login'));
        $this->get(route('pagamento.cancelar.view', ['cobranca' => 1, 'pagamento' => $pagamento->payment_id]))->assertRedirect(route('representante.login'));
        $this->post(route('pagamento.cancelar.view', ['cobranca' => 1, 'pagamento' => $pagamento->payment_id]))->assertRedirect(route('representante.login'));
        $this->get(route('pagamento.visualizar', ['cobranca' => 1, 'pagamento' => $pagamento->payment_id]))->assertRedirect(route('representante.login'));
    }
}
