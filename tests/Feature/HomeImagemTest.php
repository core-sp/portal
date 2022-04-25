<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;

class HomeImagemTest extends TestCase
{
    use RefreshDatabase;

    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     Permissao::insert([
    //         [
    //             'controller' => 'HomeImagemController',
    //             'metodo' => 'edit',
    //             'perfis' => '1,'
    //         ]
    //     ]);
    // }

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $this->get('/admin/imagens/bannerprincipal')->assertRedirect(route('login'));
        $this->put('/admin/imagens/bannerprincipal')->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        $this->get('/admin/imagens/bannerprincipal')->assertForbidden();
        $this->put('/admin/imagens/bannerprincipal')->assertForbidden();
    }

    /** @test */
    public function admin_can_access_links()
    {
        $this->signInAsAdmin();
        $dados;
        for($cont = 0; $cont < 7; $cont++)
        {
            $homeImagem = factory('App\HomeImagem')->create([
                'ordem' => $cont + 1,
            ]);
            $dados['img-'.$homeImagem->idimagem] = $homeImagem->url;
            $dados['img-mobile-'.$homeImagem->idimagem] = $homeImagem->url_mobile;
            $dados['link-'.$homeImagem->idimagem] = $homeImagem->link;
            $dados['target-'.$homeImagem->idimagem] = $homeImagem->target;
        }

        $this->get('/admin/imagens/bannerprincipal')->assertOk();
        $this->put('/admin/imagens/bannerprincipal', $dados)->assertStatus(302);
    }
}
