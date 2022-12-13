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

    // /** @test */
    // public function non_authorized_users_cannot_access_links()
    // {
    //     $this->signIn();
    //     $this->assertAuthenticated('web');

    //     $this->get(route('pagamento.admin.index'))->assertForbidden();
    //     $this->get(route('pagamento.admin.busca'))->assertForbidden();
    // }

    /** @test */
    public function user_can_view_list_payments()
    {
        $this->signInAsAdmin();

        $pagamento = factory('App\Pagamento')->create();
        $pagamentoCombinado = factory('App\Pagamento')->states('combinado')->create();
        $pagamentoCancelado = factory('App\Pagamento')->states('cancelado')->create();

        $this->get(route('pagamento.admin.index'))
        ->assertSeeText(substr_replace($pagamento->payment_id, '**********', 9, strlen($pagamento->payment_id)))
        ->assertSeeText(substr_replace($pagamentoCombinado->combined_id, '**********', 9, strlen($pagamentoCombinado->combined_id)))
        ->assertSee($pagamentoCombinado->payment_tag)
        ->assertSee($pagamentoCancelado->getStatusLabel());
    }

    /** 
     * =======================================================================================================
     * TESTES PAGAMENTO USUARIO
     * =======================================================================================================
     */

    /** @test */
    public function user_can_view_form_payment_gerenti()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))
        ->assertSee('Pagamento On-line')
        ->assertSee('Valor total')
        ->assertSee('Forma de pagamento')
        ->assertSee('Parcelas')
        ->assertSee('Crédito');
    }

    /** @test */
    public function user_can_submit_form_payment_gerenti_without_checkoutIframe()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ])
        ->assertSeeText('Dados do  cartão:')
        ->assertSeeText('Número do cartão')
        ->assertSeeText('Nome do titular')
        ->assertSeeText('CVV / CVC')
        ->assertSeeText('Data de Expiração');
    }

    /** @test */
    public function user_cannot_submit_form_payment_gerenti_without_requireds()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => '',
            'tipo_pag' => '',
            'amount' => '',
            'parcelas_1' => '',
        ])->assertSessionHasErrors([
            'cobranca',
            'tipo_pag',
            'valor',
            'parcelas_1',
        ]);
    }

    /** @test */
    public function user_can_submit_form_payment_gerenti_with_checkoutIframe()
    {
        // Colocar checkoutIframe = true no PagamentoController
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $resp = $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
            'checkoutIframe' => true
        ])
        ->assertSeeText('Finalizar')
        ->assertDontSeeText('Dados do  cartão:')
        ->assertDontSeeText('Número do cartão')
        ->assertDontSeeText('Nome do titular')
        ->assertDontSeeText('CVV / CVC')
        ->assertDontSeeText('Data de Expiração');
    }

    /** @test */
    public function user_can_submit_form_payment_getnet_without_checkoutIframe()
    {
        // Acessa o homolog da getnet, então as vezes pode dar erro
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertRedirect(route('representante.dashboard'));

        $this->get(route('representante.dashboard'))->assertSeeText('Pagamento realizado para a cobrança ' . $pagamento->cobranca_id);

        $this->assertDatabaseHas('pagamentos', [
            'cobranca_id' => $pagamento->cobranca_id,
            'bandeira' => 'visa',
        ]);
    }

    /** @test */
    public function user_can_submit_type_combined_form_payment_getnet_without_checkoutIframe()
    {
        // Acessa o homolog da getnet, então as vezes pode dar erro
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'combined',
            'amount' => $pagamento->total,
            'amount_1' => '100',
            'amount_2' => '100',
            'parcelas_1' => $pagamento->parcelas,
            'parcelas_2' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'combined',
            'amount' => $pagamento->total,
            'amount_1' => '100',
            'amount_2' => '100',
            'parcelas_1' => $pagamento->parcelas,
            'parcelas_2' => $pagamento->parcelas,
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
            'cardholder_name_2' => 'TESTE CARTAO DOIS',
            'card_number_2' => '5155901222280001',
            'security_code_2' => '123',
            'expiration_2' => now()->addMonths(2)->addYear()->format('m/Y'),
        ])->assertRedirect(route('representante.dashboard'));

        $this->get(route('representante.dashboard'))->assertSeeText('Pagamento realizado para a cobrança ' . $pagamento->cobranca_id);
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_without_requireds()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => '',
            'amount' => '',
            'parcelas_1' => '',
            'cardholder_name_1' => '',
            'card_number_1' => '',
            'security_code_1' => '',
            'expiration_1' => '',
        ])->assertSessionHasErrors([
            'tipo_pag',
            'amount',
            'parcelas_1',
            'cardholder_name_1',
            'security_code_1',
            'expiration_1',
        ]);
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_amount_with_letters()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => '12a3',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors('amount');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_tipo_pag_wrong()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credito',
            'amount' => '200',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors('tipo_pag');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_parcelas_with_letters()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => '200',
            'parcelas_1' => '1a',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors('parcelas_1');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_parcelas_more_than_2_chars()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => '200',
            'parcelas_1' => '111',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors('parcelas_1');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_expiration_1_invalid_format()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => '200',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m-Y'),
        ])->assertSessionHasErrors('expiration_1');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_expiration_1_before_current_month_and_year()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => '200',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->subMonth()->format('m/Y'),
        ])->assertSessionHasErrors('expiration_1');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_security_code_1_with_letters()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => '200',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '11a',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors('security_code_1');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_security_code_1_more_than_4_chars()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => '200',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '11111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors('security_code_1');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_security_code_1_less_than_3_chars()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => '200',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '11',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors('security_code_1');
    }

    // Continuar testes dos campos.

    // fazer testes do fluxo obrigatório das rotas.
}
