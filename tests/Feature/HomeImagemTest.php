<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;
use App\HomeImagem;

class HomeImagemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $this->get(route('imagens.banner'))->assertRedirect(route('login'));
        $this->put(route('imagens.banner.put'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        $this->get(route('imagens.banner'))->assertForbidden();
        $this->put(route('imagens.banner.put'))->assertForbidden();
    }

    /** @test */
    public function authorized_users_can_view_form()
    {
        $view = array();
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)
        ->create()
        ->each(function ($banner) {
            $banner->update(['ordem' => $banner->idimagem]);
        });

        foreach($banners as $banner)
            array_push($view, 
            '<a id="lfm-'.$banner->idimagem.'" data-input="img-'.$banner->idimagem.'" data-preview="holder" class="btn btn-default">',
            '<a id="lfm-m-'.$banner->idimagem.'" data-input="img-m-'.$banner->idimagem.'" data-preview="holder" class="btn btn-default">',
            'name="link-'.$banner->idimagem.'"',
            '<select name="target-'.$banner->idimagem.'" class="form-control form-control-sm" id="selectTarget">');

        $this->signInAsAdmin();
        
        $this->get(route('imagens.banner'))
        ->assertOk()
        ->assertSeeInOrder($view);
    }

    /** @test */
    public function authorized_users_can_update_banner()
    {
        $campos = ['img-' => 'url', 'img-mobile-' => 'url_mobile', 'link-' => 'link', 'target-' => 'target'];
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)
        ->create()
        ->each(function ($banner) {
            $banner->update(['ordem' => $banner->idimagem]);
        });

        $user = $this->signInAsAdmin();
        
        for($cont = 1, $index = 0; $cont <= HomeImagem::TOTAL; $cont++, $index++)
        {
            $banner = $banners->get($index)->toArray();
            foreach($campos as $key => $val)
                $dados[$key . $cont] = $banner[$val];
        }

        $dados['img-mobile-1'] = "";
        $dados['img-mobile-3'] = "";
        $dados['target-1'] = '_self';
        $dados['target-5'] = '_self';

        $this->put(route('imagens.banner.put', $dados))
        ->assertRedirect(route('admin'));

        $this->assertDatabaseHas("home_imagens", [
            "url_mobile" => null,
            'target' => '_self'
        ]);
        $this->assertEquals(HomeImagem::whereNull('url_mobile')->count(), 2);
        $this->assertEquals(HomeImagem::where('target', '_self')->count(), 2);
        $this->assertEquals(HomeImagem::whereNotNull('url_mobile')->count(), 5);
        $this->assertEquals(HomeImagem::where('target', '_blank')->count(), 5);
    }

    /** @test */
    public function log_is_generated_when_update_banner()
    {
        $campos = ['img-' => 'url', 'img-mobile-' => 'url_mobile', 'link-' => 'link', 'target-' => 'target'];
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)
        ->create()
        ->each(function ($banner) {
            $banner->update(['ordem' => $banner->idimagem]);
        });

        $user = $this->signInAsAdmin();
        
        for($cont = 1, $index = 0; $cont <= HomeImagem::TOTAL; $cont++, $index++)
        {
            $banner = $banners->get($index)->toArray();
            foreach($campos as $key => $val)
                $dados[$key . $cont] = $banner[$val];
        }

        $this->put(route('imagens.banner.put', $dados))
        ->assertRedirect(route('admin'));

        $log = tailCustom(storage_path($this->pathLogInterno()), HomeImagem::TOTAL);
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou *banner principal* (id: ';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_update_banner_with_input_name_wrong()
    {
        $campos = ['img-' => 'url', 'img-mobile-' => 'url_mobile', 'link-' => 'link', 'target-' => 'target'];
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)
        ->create()
        ->each(function ($banner) {
            $banner->update(['ordem' => $banner->idimagem]);
        });

        $this->signInAsAdmin();
        
        for($cont = 1, $index = 0; $cont <= HomeImagem::TOTAL; $cont++, $index++)
        {
            $banner = $banners->get($index)->toArray();
            foreach($campos as $key => $val)
                $dados[$key . $cont] = $banner[$val];
        }

        $dados['img-mobil-1'] = $dados['img-mobile-1'];
        unset($dados['img-mobile-1']);

        $this->put(route('imagens.banner.put', $dados))
        ->assertRedirect(route('imagens.banner'));

        $this->get(route('imagens.banner'))
        ->assertSeeText('Campo (img-mobil-1) não é válido ao atualizar o carrossel devido não ser compatível com: img-1 ou img-mobile-1 ou link-1 ou target-1.');
    }

    /** @test */
    public function cannot_update_banner_with_input_number_wrong()
    {
        $campos = ['img-' => 'url', 'img-mobile-' => 'url_mobile', 'link-' => 'link', 'target-' => 'target'];
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)
        ->create()
        ->each(function ($banner) {
            $banner->update(['ordem' => $banner->idimagem]);
        });

        $this->signInAsAdmin();
        
        for($cont = 1, $index = 0; $cont <= HomeImagem::TOTAL; $cont++, $index++)
        {
            $banner = $banners->get($index)->toArray();
            foreach($campos as $key => $val)
                $dados[$key . $cont] = $banner[$val];
        }

        $dados['img-mobile-8'] = $dados['img-mobile-1'];
        unset($dados['img-mobile-1']);

        $this->put(route('imagens.banner.put', $dados))
        ->assertRedirect(route('imagens.banner'));

        $this->get(route('imagens.banner'))
        ->assertSeeText('Campo (img-mobile-8) não é válido ao atualizar o carrossel devido não ser compatível com: img-1 ou img-mobile-1 ou link-1 ou target-1.');
    }

    /** @test */
    public function cannot_update_banner_with_total_inputs_wrong()
    {
        $campos = ['img-' => 'url', 'img-mobile-' => 'url_mobile', 'link-' => 'link', 'target-' => 'target'];
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)
        ->create()
        ->each(function ($banner) {
            $banner->update(['ordem' => $banner->idimagem]);
        });

        $this->signInAsAdmin();
        
        for($cont = 1, $index = 0; $cont <= HomeImagem::TOTAL; $cont++, $index++)
        {
            $banner = $banners->get($index)->toArray();
            foreach($campos as $key => $val)
                $dados[$key . $cont] = $banner[$val];
        }

        unset($dados['img-mobile-1']);

        $this->put(route('imagens.banner.put', $dados))
        ->assertRedirect(route('imagens.banner'));

        $this->get(route('imagens.banner'))
        ->assertSeeText('Possui total de campos (27) diferente do permitido (28), então não é válido ao atualizar o carrossel.');
    }

    /** @test */
    public function cannot_update_banner_with_input_target_wrong()
    {
        $campos = ['img-' => 'url', 'img-mobile-' => 'url_mobile', 'link-' => 'link', 'target-' => 'target'];
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)
        ->create()
        ->each(function ($banner) {
            $banner->update(['ordem' => $banner->idimagem]);
        });

        $this->signInAsAdmin();
        
        for($cont = 1, $index = 0; $cont <= HomeImagem::TOTAL; $cont++, $index++)
        {
            $banner = $banners->get($index)->toArray();
            foreach($campos as $key => $val)
                $dados[$key . $cont] = $banner[$val];
        }

        $dados['target-1'] = '_selfe';

        $this->put(route('imagens.banner.put', $dados))
        ->assertRedirect(route('imagens.banner'));

        $this->get(route('imagens.banner'))
        ->assertSeeText('Campo (target-1) não é válido ao atualizar o carrossel devido seu valor (_selfe) não ser aceito: _blank, _self.');
    }

    /** @test */
    public function can_view_carrossel()
    {
        $view = array();
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)
        ->create()
        ->each(function ($banner) {
            $banner->update(['ordem' => $banner->idimagem]);
        });

        foreach($banners as $key => $banner)
            $key == 0 ? array_push($view, '<li data-target="#carousel" data-slide-to="'.$key.'" class=&quot;active&quot;></li>') : 
            array_push($view, '<li data-target="#carousel" data-slide-to="'.$key.'" ></li>');

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSeeInOrder($view);
    }
}
