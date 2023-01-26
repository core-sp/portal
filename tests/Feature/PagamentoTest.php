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
     * TESTES PAGAMENTO ADMIN
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
        $pagamentoCombinado = factory('App\Pagamento')->states('combinado_confirmado')->create();
        $pagamentoCancelado = factory('App\Pagamento')->states('cancelado')->create();

        $this->get(route('pagamento.admin.index'))
        ->assertSeeText(substr_replace($pagamento->payment_id, '**********', 9, strlen($pagamento->payment_id)))
        ->assertSeeText(substr_replace($pagamentoCombinado->combined_id, '**********', 9, strlen($pagamentoCombinado->combined_id)))
        ->assertSee($pagamentoCombinado->payment_tag)
        ->assertSee($pagamentoCancelado->getStatusLabel());
    }

    /** @test */
    public function user_can_search_payments()
    {
        $this->signInAsAdmin();

        $pagamento = factory('App\Pagamento')->create();
        $pagamentoCombinado = factory('App\Pagamento')->states('combinado_confirmado')->create();
        $pagamentoCancelado = factory('App\Pagamento')->states('cancelado')->create();

        $this->get(route('pagamento.admin.busca', ['q' => $pagamento->payment_id]))
        ->assertSeeText(substr_replace($pagamento->payment_id, '**********', 9, strlen($pagamento->payment_id)));
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
    public function user_cannot_view_payment_other_user()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamentoUser = factory('App\Pagamento')->create();
        $pagamento = factory('App\Pagamento')->create([
            'idrepresentante' => factory('App\Representante')->create([
                'cpf_cnpj' => '12345678911'
            ]),
        ]);

        $this->get(route('pagamento.visualizar', ['cobranca' => $pagamento->cobranca_id, 'pagamento' => $pagamento->payment_id]))
        ->assertDontSee('Pagamento On-line')
        ->assertDontSee('Valor total')
        ->assertDontSee('Forma de pagamento')
        ->assertDontSee('Parcelas')
        ->assertDontSee('Crédito')
        ->assertRedirect(route('representante.dashboard'));

        $this->get(route('pagamento.visualizar', ['cobranca' => $pagamentoUser->cobranca_id, 'pagamento' => $pagamentoUser->payment_id]))
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
    public function user_cannot_submit_form_payment_getnet_with_cobranca_approved()
    {
        // Acessa o homolog da getnet, então as vezes pode dar erro
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->create();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))
        ->assertRedirect(route('representante.dashboard'));

        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ])->assertRedirect(route('representante.dashboard'));

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

        $this->assertEquals(Pagamento::count(), 1);
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

        $this->assertDatabaseHas('pagamentos', [
            'cobranca_id' => $pagamento->cobranca_id,
            'bandeira' => 'visa',
            'forma' => 'combined',
            'bandeira' => 'mastercard',
        ]);
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

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_cardholder_name_1_less_than_5_chars()
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
            'cardholder_name_1' => 'TEST',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors('cardholder_name_1');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_cardholder_name_1_more_than_26_chars()
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
            'cardholder_name_1' => 'TESTE ASDERTTY PPPOUYHKL POI',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors('cardholder_name_1');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_cardholder_name_1_with_accent()
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
            'cardholder_name_1' => 'TESTE CARTÃO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors('cardholder_name_1');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_card_number_1_less_than_13_chars()
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
            'card_number_1' => '401200103714',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors('card_number_1');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_card_number_1_more_than_19_chars()
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
            'card_number_1' => '40120010371412365489',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors('card_number_1');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_without_requireds_if_combined()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'combined',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'combined',
            'amount' => '200',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors([
            'amount_1',
            'amount_2',
            'parcelas_2',
            'expiration_2',
            'security_code_2',
            'cardholder_name_2',
            'card_number_2'
        ]);
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_amount_1_and_amount_2_different_amount_if_combined()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'combined',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'combined',
            'amount' => '200',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
            'amount_1' => '050',
            'amount_2' => '075',
            'parcelas_2' => '1',
            'expiration_2' => now()->addMonth()->addYear()->format('m/Y'),
            'security_code_2' => '123',
            'cardholder_name_2' => 'TESTE CARTAO DOIS',
            'card_number_2' => '5155901222280001'
        ])->assertSessionHasErrors('amount_soma');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_card_number_2_equal_card_number_1_if_combined()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'combined',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'combined',
            'amount' => '200',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
            'amount_1' => '100',
            'amount_2' => '100',
            'parcelas_2' => '1',
            'expiration_2' => now()->addMonth()->addYear()->format('m/Y'),
            'security_code_2' => '123',
            'cardholder_name_2' => 'TESTE CARTAO DOIS',
            'card_number_2' => '4012001037141112'
        ])->assertSessionHasErrors('card_number_2');
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_url_previous_wrong()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => '200',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors([
            'cobranca',
            'tipo_pag',
            'amount',
            'parcelas_1',
            'cardholder_name_1',
            'security_code_1',
            'expiration_1'
        ]);
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_url_previous_different_cobranca_current()
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
            'cobranca' => $pagamento->cobranca_id . '2',
            'tipo_pag' => 'credit',
            'amount' => '200',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertSessionHasErrors([
            'cobranca',
            'tipo_pag',
            'amount',
            'parcelas_1',
            'cardholder_name_1',
            'security_code_1',
            'expiration_1'
        ]);
    }

    /** @test */
    public function user_cannot_submit_form_payment_getnet_with_checkoutIframe()
    {
        // Deve habilitar no PagamentoController o checkoutIframe
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
            'checkoutIframe' => '1'
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => '200',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertRedirect(route('representante.dashboard'));

        $this->get(route('representante.dashboard'))
        ->assertSeeText('Não foi possível completar a operação! Erro ao processar o pagamento. Código de erro: 401');
    }

    /** @test */
    public function user_cannot_access_route_checkout_iframe_success_without_checkoutIframe()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
            'checkoutIframe' => '0'
        ]);

        $this->get(route('pagamento.sucesso.checkout', $pagamento->cobranca_id))
        ->assertRedirect(route('representante.dashboard'));

        $this->get(route('representante.dashboard'))
        ->assertDontSeeText('Pagamento realizado para a cobrança ' . $pagamento->cobranca_id . '. Detalhes do pagamento enviado para o e-mail: ' . $representante->email);
    }

    /** @test */
    public function user_cannot_access_route_checkout_iframe_success_with_url_previous_different_cobranca_current()
    {
        // Deve habilitar no PagamentoController o checkoutIframe
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
            'checkoutIframe' => '1'
        ]);

        $this->get(route('pagamento.sucesso.checkout', $pagamento->cobranca_id))
        ->assertRedirect(route('representante.dashboard'));

        $this->get(route('representante.dashboard'))
        ->assertDontSeeText('Pagamento realizado para a cobrança ' . $pagamento->cobranca_id . '. Detalhes do pagamento enviado para o e-mail: ' . $representante->email);
    }

    /** @test */
    public function data_card_clean_after_error_submit_form_payment_getnet()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'combined',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'combined',
            'amount' => '200',
            'parcelas_1' => '1',
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertRedirect(route('pagamento.view', $pagamento->cobranca_id));

        // teste limpeza de dados após erro de validação na route('pagamento.view', $pagamento->cobranca_id)
        $this->assertEquals(true, request()->session()->exists('errors'));

        session()->forget(['_old_input']);

        $this->assertEquals(null, session()->get('_old_input'));
    }

    /** @test */
    public function can_create_payment_by_notification()
    {
        // Deve habilitar no PagamentoController o checkoutIframe
        $representante = factory('App\Representante')->create();
        $pagamento = factory('App\Pagamento')->make();
        
        $this->get(route('pagamento.transacao.credito', [
            'payment_type' => $pagamento->forma,
            'customer_id' => $representante->getCustomerId(),
            'order_id' => $pagamento->cobranca_id,
            'payment_id' => $pagamento->payment_id,
            'amount' => '200',
            'status' => $pagamento->status,
            'number_installments' => $pagamento->parcelas,
            'terminal_nsu' => '031575',
            'authorization_code' => '9190383360902371',
            'acquirer_transaction_id' => '000099713751',
            'authorization_timestamp' => now()->toIso8601ZuluString(),
            'brand' => $pagamento->bandeira,
            'description_detail' => '',
            'error_code' => '',
            'tipo_parcelas' => $pagamento->tipo_parcelas,
        ]));

        $this->assertDatabaseHas('pagamentos', [
            'cobranca_id' => $pagamento->cobranca_id,
            'bandeira' => 'visa',
        ]);
    }

    /** @test */
    public function cannot_create_payment_by_notification_without_user()
    {
        // Deve habilitar no PagamentoController o checkoutIframe
        $representante = factory('App\Representante')->create();
        $pagamento = factory('App\Pagamento')->make();
        
        $this->get(route('pagamento.transacao.credito', [
            'payment_type' => $pagamento->forma,
            'customer_id' => '00000000000_rep',
            'order_id' => $pagamento->cobranca_id,
            'payment_id' => $pagamento->payment_id,
            'amount' => '200',
            'status' => $pagamento->status,
            'number_installments' => $pagamento->parcelas,
            'terminal_nsu' => '031575',
            'authorization_code' => '9190383360902371',
            'acquirer_transaction_id' => '000099713751',
            'authorization_timestamp' => now()->toIso8601ZuluString(),
            'brand' => $pagamento->bandeira,
            'description_detail' => '',
            'error_code' => '',
            'tipo_parcelas' => $pagamento->tipo_parcelas,
        ]));

        $this->assertDatabaseMissing('pagamentos', [
            'cobranca_id' => $pagamento->cobranca_id,
            'bandeira' => 'visa',
        ]);
    }

    /** @test */
    public function cannot_create_payment_by_notification_without_checkoutIframe()
    {
        $representante = factory('App\Representante')->create();
        $pagamento = factory('App\Pagamento')->make();
        
        $this->get(route('pagamento.transacao.credito', [
            'payment_type' => $pagamento->forma,
            'customer_id' => $representante->getCustomerId(),
            'order_id' => $pagamento->cobranca_id,
            'payment_id' => $pagamento->payment_id,
            'amount' => '500',
            'status' => 'APPROVED',
            'number_installments' => $pagamento->parcelas,
            'terminal_nsu' => '031575',
            'authorization_code' => '9190383360902371',
            'acquirer_transaction_id' => '000099713751',
            'authorization_timestamp' => now()->toIso8601ZuluString(),
            'brand' => $pagamento->bandeira,
            'description_detail' => '',
            'error_code' => '',
            'tipo_parcelas' => $pagamento->tipo_parcelas,
        ]));

        $this->assertDatabaseMissing('pagamentos', [
            'cobranca_id' => $pagamento->cobranca_id,
            'bandeira' => 'visa',
            'status' => 'APPROVED',
            'total' => '2,00'
        ]);
    }

    /** @test */
    public function cannot_create_payment_by_notification_with_status_different_approved()
    {
        $representante = factory('App\Representante')->create();
        $pagamento = factory('App\Pagamento')->make();
        
        $this->get(route('pagamento.transacao.credito', [
            'payment_type' => $pagamento->forma,
            'customer_id' => $representante->getCustomerId(),
            'order_id' => $pagamento->cobranca_id,
            'payment_id' => $pagamento->payment_id,
            'amount' => '200',
            'status' => 'AUTHORIZED',
            'number_installments' => $pagamento->parcelas,
            'terminal_nsu' => '031575',
            'authorization_code' => '9190383360902371',
            'acquirer_transaction_id' => '000099713751',
            'authorization_timestamp' => now()->toIso8601ZuluString(),
            'brand' => $pagamento->bandeira,
            'description_detail' => '',
            'error_code' => '',
            'tipo_parcelas' => $pagamento->tipo_parcelas,
        ]));

        $this->assertDatabaseMissing('pagamentos', [
            'cobranca_id' => $pagamento->cobranca_id,
            'bandeira' => 'visa',
            'status' => 'AUTHORIZED',
            'total' => '2,00'
        ]);
    }

    /** @test */
    public function cannot_cancel_when_payment_is_canceled()
    {
        // Acessa o homolog da getnet, então as vezes pode dar erro
        $user = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $user['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->states('cancelado')->create();

        $this->post(route('pagamento.cancelar', ['cobranca' => $pagamento->cobranca_id, 'pagamento' => $pagamento->payment_id]))
        ->assertRedirect(route('representante.dashboard'));
    }

    /** @test */
    public function log_is_generated_when_payment_is_created_by_api()
    {
        // Acessa o homolog da getnet, então as vezes pode dar erro
        $user = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $user['cpf_cnpj'], 'password' => 'teste102030']);

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
            'tipo_pag' => $pagamento->forma,
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertRedirect(route('representante.dashboard'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: ' . request()->ip() . '] - ';
        $txt = $inicio . 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') realizou pagamento da cobrança ';
        $txt .= $pagamento->cobranca_id . ' do tipo *' . $pagamento->forma . '* com a payment_id: ';

        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function log_is_generated_when_payment_combined_is_created_by_api()
    {
        // Acessa o homolog da getnet, então as vezes pode dar erro
        $user = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $user['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->states('combinado_autorizado')->make();

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

        $ids = array();
        $tags = array();
        $all = Pagamento::where('status', 'CONFIRMED')->get();
        foreach($all as $key => $pags)
        {
            $ids[$key] = $pags->payment_id;
            $tags[$key] = $pags->payment_tag;
        }

        // retornar os dois últimos registros do log e separados pelo final da linha.
        $log = tailCustom(storage_path($this->pathLogExterno()), 2);
        $pos = strpos($log, PHP_EOL);
        $autorizado = substr($log, 0, $pos);
        $confirmado = substr($log, $pos + 1);

        $inicio = 'testing.INFO: [IP: ' . request()->ip() . '] - ';
        $txt = $inicio . 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') realizou a autorização de pagamento da cobrança ';
        $txt .= $pagamento->cobranca_id . ' do tipo *' . $pagamento->forma . '* com a combined_id: ' . $all->get(0)->combined_id;
        $txt .= ', com as payment_ids: ' . json_encode($ids) . ' e payment_tags: ' . json_encode($tags) . '. Aguardando confirmação do pagamento via Portal.';

        $this->assertStringContainsString($txt, $autorizado);

        $inicio = 'testing.INFO: [IP: ' . request()->ip() . '] - ';
        $txt = $inicio . 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') realizou pagamento da cobrança ';
        $txt .= $pagamento->cobranca_id . ' do tipo *' . $pagamento->forma . '* com a combined_id: ' . $all->get(0)->combined_id;

        $this->assertStringContainsString($txt, $confirmado);
    }

    /** @test */
    public function log_is_generated_when_payment_is_canceled_by_api()
    {
        // Acessa o homolog da getnet, então as vezes pode dar erro
        $user = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $user['cpf_cnpj'], 'password' => 'teste102030']);

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
            'tipo_pag' => $pagamento->forma,
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '4012001037141112',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertRedirect(route('representante.dashboard'));

        $pagamento = Pagamento::first();
        $this->post(route('pagamento.cancelar', ['cobranca' => $pagamento->cobranca_id, 'pagamento' => $pagamento->payment_id]))
        ->assertRedirect(route('representante.dashboard'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: ' . request()->ip() . '] - ';
        $txt = $inicio . 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') realizou o cancelamento do pagamento da cobrança ';
        $txt .= $pagamento->cobranca_id . ' do tipo *' . $pagamento->forma . '* com a payment_id: ';

        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function log_is_generated_when_payment_error_by_api()
    {
        // Acessa o homolog da getnet, então as vezes pode dar erro
        $user = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $user['cpf_cnpj'], 'password' => 'teste102030']);

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
            'tipo_pag' => $pagamento->forma,
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
            'cardholder_name_1' => 'TESTE CARTAO',
            // cartão de teste da getnet com um número a menos
            'card_number_1' => '515590122223000',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertRedirect(route('representante.dashboard'));

        $this->get(route('representante.dashboard'))
        ->assertSeeText('Não foi possível completar a operação!');

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: ' . request()->ip() . '] - ';
        $txt = $inicio . 'Usuário '.$user->id.' ("' . formataCpfCnpj($user->cpf_cnpj) . '", login como: '.$user::NAME_AREA_RESTRITA.') recebeu um código de erro *';

        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function log_is_generated_when_payment_is_created_by_notification()
    {
        // Deve habilitar no PagamentoController o checkoutIframe
        $user = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $user['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.transacao.credito', [
            'payment_type' => $pagamento->forma,
            'customer_id' => $user->getCustomerId(),
            'order_id' => $pagamento->cobranca_id,
            'payment_id' => $pagamento->payment_id,
            'amount' => '200',
            'status' => 'APPROVED',
            'number_installments' => $pagamento->parcelas,
            'terminal_nsu' => '031575',
            'authorization_code' => '9190383360902371',
            'acquirer_transaction_id' => '000099713751',
            'authorization_timestamp' => now()->toIso8601ZuluString(),
            'brand' => $pagamento->bandeira,
            'description_detail' => '',
            'error_code' => '',
            'tipo_parcelas' => $pagamento->tipo_parcelas,
        ]));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: ' . request()->ip() . '] - ';
        $txt = $inicio . '[Rotina Portal - Transação Getnet] - ID: ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') realizou pagamento da cobrança ';
        $txt .= $pagamento->cobranca_id . ' do tipo *' . $pagamento->forma . '*, com a payment_id: ' . Pagamento::first()->payment_id;

        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function log_is_generated_when_get_checkoutIframeSucesso()
    {
        // Deve habilitar no PagamentoController o checkoutIframe
        $user = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $user['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $this->get(route('pagamento.sucesso.checkout', $pagamento->cobranca_id))
        ->assertRedirect(route('representante.dashboard'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: ' . request()->ip() . '] - ';
        $txt = $inicio . 'Usuário '.$user->id.' ("' . formataCpfCnpj($user->cpf_cnpj) . '", login como: '.$user::NAME_AREA_RESTRITA.') ';
        $txt .= 'aviso que realizou o pagamento da cobrança *'.$pagamento->cobranca_id.'* via Checkout Iframe. Aguardando notificação para registro no banco de dados.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function regenerate_session_after_submit_payment_with_error_via_api()
    {
        $user = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $user['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $csrf = csrf_token();
        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => $pagamento->forma,
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
            'cardholder_name_1' => 'TESTE CARTAO',
            // cartão de teste da getnet com um número a menos
            'card_number_1' => '515590122223000',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertRedirect(route('representante.dashboard'));

        $this->assertNotEquals($csrf, csrf_token());
    }

    /** @test */
    public function regenerate_session_after_submit_payment_with_success_via_api()
    {
        $user = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $user['cpf_cnpj'], 'password' => 'teste102030']);

        $pagamento = factory('App\Pagamento')->make();

        $this->get(route('pagamento.view', $pagamento->cobranca_id))->assertOk();
        $this->post(route('pagamento.gerenti', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => 'credit',
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
        ]);

        $csrf = csrf_token();
        $this->post(route('pagamento.cartao', $pagamento->cobranca_id), [
            'cobranca' => $pagamento->cobranca_id,
            'tipo_pag' => $pagamento->forma,
            'amount' => $pagamento->total,
            'parcelas_1' => $pagamento->parcelas,
            'cardholder_name_1' => 'TESTE CARTAO',
            'card_number_1' => '5155901222280001',
            'security_code_1' => '111',
            'expiration_1' => now()->addMonth()->addYear()->format('m/Y'),
        ])->assertRedirect(route('representante.dashboard'));

        $this->assertNotEquals($csrf, csrf_token());
    }
}
