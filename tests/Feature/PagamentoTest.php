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
        
        // $licitacao = factory('App\Licitacao')->create();
        // $licitacao->datarealizacao = Carbon::create($licitacao->datarealizacao)->format('Y-m-d H:i');

        // $this->get(route('licitacoes.index'))->assertRedirect(route('login'));
        // $this->get(route('licitacoes.create'))->assertRedirect(route('login'));
        // $this->get(route('licitacoes.edit', $licitacao->idlicitacao))->assertRedirect(route('login'));
        // $this->post(route('licitacoes.store'))->assertRedirect(route('login'));
        // $this->patch(route('licitacoes.update', $licitacao->idlicitacao))->assertRedirect(route('login'));
        // $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao))->assertRedirect(route('login'));
        // $this->get(route('licitacoes.restore', $licitacao->idlicitacao))->assertRedirect(route('login'));
        // $this->get(route('licitacoes.busca'))->assertRedirect(route('login'));
        // $this->get(route('licitacoes.trashed'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        // $this->signIn();
        // $this->assertAuthenticated('web');
        
        // $licitacao = factory('App\Licitacao')->create();
        // $licitacao->datarealizacao = Carbon::create($licitacao->datarealizacao)->format('Y-m-d H:i');

        // $this->get(route('licitacoes.index'))->assertForbidden();
        // $this->get(route('licitacoes.create'))->assertForbidden();
        // $this->get(route('licitacoes.edit', $licitacao->idlicitacao))->assertForbidden();
        // $this->post(route('licitacoes.store'), $licitacao->toArray())->assertForbidden();
        // $this->patch(route('licitacoes.update', $licitacao->idlicitacao), $licitacao->toArray())->assertForbidden();
        // $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao))->assertForbidden();
        // $this->get(route('licitacoes.restore', $licitacao->idlicitacao))->assertForbidden();
        // $this->get(route('licitacoes.busca'))->assertForbidden();
        // $this->get(route('licitacoes.trashed'))->assertForbidden();
    }
}
