<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;
use App\HomeImagem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class HomeImagemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();

        $this->get(route('imagens.banner'))->assertRedirect(route('login'));
        $this->put(route('imagens.banner.put'))->assertRedirect(route('login'));
        $this->get(route('imagens.itens.home'))->assertRedirect(route('login'));
        $this->patch(route('imagens.itens.home.update'))->assertRedirect(route('login'));
        $this->get(route('imagens.itens.home.storage'))->assertRedirect(route('login'));
        $this->post(route('imagens.itens.home.storage.post'))->assertRedirect(route('login'));
        $this->delete(route('imagens.itens.home.storage.delete', 'arquivo.png'))->assertRedirect(route('login'));
        $this->get(route('imagens.itens.home.storage.download', ['folder' => 'itens-home', 'arquivo' => 'teste.jpeg']))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $migrated = true;
        $this->signIn();
        $this->assertAuthenticated('web');

        $this->get(route('imagens.banner'))->assertForbidden();
        $this->put(route('imagens.banner.put'))->assertForbidden();
        $this->get(route('imagens.itens.home'))->assertForbidden();
        $this->patch(route('imagens.itens.home.update'))->assertForbidden();
        $this->get(route('imagens.itens.home.storage'))->assertForbidden();
        $this->post(route('imagens.itens.home.storage.post'))->assertForbidden();
        $this->delete(route('imagens.itens.home.storage.delete', 'arquivo.png'))->assertForbidden();
        $this->get(route('imagens.itens.home.storage.download', ['folder' => 'itens-home', 'arquivo' => 'teste.jpeg']))->assertForbidden();
    }

    /** 
     * =======================================================================================================
     * TESTES CARROSSEL
     * =======================================================================================================
     */

    /** @test */
    public function authorized_users_can_view_form()
    {
        $view = array();
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)->create();

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
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)->create();

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
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)->create();

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
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)->create();

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
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)->create();

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
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)->create();

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
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)->create();

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
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)->create();

        foreach($banners as $key => $banner)
            $key == 0 ? array_push($view, '<li data-target="#carousel" data-slide-to="'.$key.'" class=&quot;active&quot;></li>') : 
            array_push($view, '<li data-target="#carousel" data-slide-to="'.$key.'" ></li>');

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSeeInOrder($view);
    }

    /** 
     * =======================================================================================================
     * TESTES ITENS HOME
     * =======================================================================================================
     */

    /** @test */
    public function authorized_users_can_view_defaults()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();

        $this->signInAsAdmin();
        
        $this->get(route('imagens.itens.home'))
        ->assertOk()
        ->assertSeeInOrder([
            '<small>Logo principal</small>',
            'checked',
            '/> Usar logo principal padrão',
            '<small>Fundo do logo principal</small>',
            'checked',
            '/> Usar fundo do logo principal padrão',
            '<small>Função Neve</small>',
            '/> Sim, inserir neve',
            '<small>Função pop-up vídeo</small>',
            'checked',
            '/> <strong>Não</strong>&nbsp;inserir pop-up de vídeo ',
            '<small>Cards - Espaço do Representante</small>',
            'checked',
            '/> Usar cor padrão do card escuro<i class="fas fa-circle fa-lg ml-1" style="color:' . HomeImagem::padrao()['cards_1_default'].';"></i>',
            'checked',
            '/> Usar cor padrão do card claro<i class="fas fa-circle fa-lg ml-1" style="color:' . HomeImagem::padrao()['cards_2_default'].';"></i>',
            '<small>Calendário</small>',
            'checked',
            '/> Usar calendário padrão',
            '<small>Rodapé</small>',
            'checked',
            '/> Usar cor padrão do rodapé<i class="fas fa-circle fa-lg ml-1" style="color:' . HomeImagem::padrao()['footer_default'].';"></i>'
        ]);
    }

    /** @test */
    public function can_view_defaults_without_itens_home_created()
    {
        factory('App\Regional')->create();
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL)->create();

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSee('<a href="/"><img src="'.asset(HomeImagem::padrao()['header_logo_default']).'" alt="CORE-SP" id="logo-header" /></a>')
        ->assertSee('<header id="header-principal" style="background-image: url(/'.HomeImagem::padrao()['header_fundo_default'].')">')
        ->assertSeeInOrder([
            '<a href="/calendario-oficial-core-sp">',
            '<img class="lazy" data-src="'.asset(HomeImagem::padrao()['calendario_default']).'" alt="Calendário | Core-SP" />',
            '</a>'
        ])
        ->assertSee('<div class="box text-center " style="background-color:'.HomeImagem::padrao()['cards_1_default'].'">')
        ->assertSee('<div class="box text-center " style="background-color:'.HomeImagem::padrao()['cards_2_default'].'">')
        ->assertSee('<footer class="pt-4" id="rodape" style="background-color:'.HomeImagem::padrao()['footer_default'].'">');

        $this->get(route('licitacoes.siteBusca'))
        ->assertOk()
        ->assertSee('<a href="/"><img src="'.asset(HomeImagem::padrao()['header_logo_default']).'" alt="CORE-SP" id="logo-header" /></a>')
        ->assertSee('<header id="header-principal" style="background-image: url(/'.HomeImagem::padrao()['header_fundo_default'].')">')
        ->assertSee('<footer class="pt-4" id="rodape" style="background-color:'.HomeImagem::padrao()['footer_default'].'">');

        $this->get(route('bdosite.index'))
        ->assertOk()
        ->assertSee('<a href="/"><img src="'.asset(HomeImagem::padrao()['header_logo_default']).'" alt="CORE-SP" id="logo-header" /></a>')
        ->assertSee('<header id="header-principal" style="background-image: url(/'.HomeImagem::padrao()['header_fundo_default'].')">')
        ->assertSee('<footer class="pt-4" id="rodape" style="background-color:'.HomeImagem::padrao()['footer_default'].'">');

        $this->get(route('regionais.show', 1))
        ->assertOk()
        ->assertSee('<a href="/"><img src="'.asset(HomeImagem::padrao()['header_logo_default']).'" alt="CORE-SP" id="logo-header" /></a>')
        ->assertSee('<header id="header-principal" style="background-image: url(/'.HomeImagem::padrao()['header_fundo_default'].')">')
        ->assertSee('<footer class="pt-4" id="rodape" style="background-color:'.HomeImagem::padrao()['footer_default'].'">');
    }

    /** @test */
    public function log_is_generated_when_update_itens_home()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();

        $user = $this->signInAsAdmin();
        
        $dados = [
            'calendario' => 'imagens/itens_home/teste.png',
            'header_fundo_cor' => '#000fff',
            'header_logo' => 'imagens/itens_home/fundo.jpg',
            'cards_2' => '#0f0f0f',
            'cards_1' => '#f0f0f0',
            'footer' => '#000000',
            'neve_default' => 'neve_default',
            'popup_video_default' => 'popup_video_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertRedirect(route('imagens.itens.home'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!');

        $log = tailCustom(storage_path($this->pathLogInterno()), HomeImagem::TOTAL_ITENS_HOME);

        foreach($banners as $banner)
        {
            $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
            $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou *item da home: '.$banner->funcao.'* (id: '.$banner->idimagem.')';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function authorized_users_can_update_header_logo()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $banners->where('funcao', 'header_logo')->first()->update(['url' => 'imagens/itens_home/teste.png', 'url_mobile' => 'imagens/itens_home/teste.png']);
        
        $this->get('/teste/teste/teste')
        ->assertNotFound()
        ->assertSee('<a href="/"><img src="'.asset('imagens/itens_home/teste.png').'" alt="CORE-SP" id="logo-header" /></a>');

        $this->assertEquals($this->app->resolved('App\Http\Middleware\ShareData'), true);
        $banners->where('funcao', 'header_logo')->first()->update(['url' => HomeImagem::padrao()['header_logo_default'], 'url_mobile' => HomeImagem::padrao()['header_logo_default']]);

        $dados = [
            'calendario_default' => 'calendario_default',
            'header_fundo_default' => 'header_fundo_default',
            'header_logo' => 'imagens/itens_home/teste.png',
            'cards_2_default' => 'cards_2_default',
            'cards_1_default' => 'cards_1_default',
            'footer_default' => 'footer_default',
        ];

        $user = $this->signInAsAdmin();
        
        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertRedirect(route('imagens.itens.home'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!');

        $this->get(route('imagens.itens.home'))
        ->assertOk()
        ->assertSee('<a href="/imagens/itens_home/teste.png"')
        ->assertSee('<img src="'. asset('imagens/itens_home/teste.png') .'" class="img-thumbnail" alt="Logo principal customizado">');

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSee('<a href="/"><img src="'.asset('imagens/itens_home/teste.png').'" alt="CORE-SP" id="logo-header" /></a>');

        $this->get(route('licitacoes.siteBusca'))
        ->assertOk()
        ->assertSee('<a href="/"><img src="'.asset('imagens/itens_home/teste.png').'" alt="CORE-SP" id="logo-header" /></a>');

        $this->get(route('bdosite.index'))
        ->assertOk()
        ->assertSee('<a href="/"><img src="'.asset('imagens/itens_home/teste.png').'" alt="CORE-SP" id="logo-header" /></a>');
        
        $this->assertEquals($this->app->resolved('App\Http\Middleware\ShareData'), true);

        $this->assertDatabaseHas("home_imagens", [
            'funcao' => 'header_logo',
            "url" => 'imagens/itens_home/teste.png',
            "url_mobile" => 'imagens/itens_home/teste.png',
            'link' => '#',
            'target' => '_self'
        ]);
    }

    /** @test */
    public function authorized_users_can_update_header_fundo_with_image()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $banners->where('funcao', 'header_fundo')->first()->update(['url' => 'imagens/itens_home/teste.png', 'url_mobile' => 'imagens/itens_home/teste.png']);
        
        $this->get('/teste/teste/teste')
        ->assertNotFound()
        ->assertSee('<header id="header-principal" style="background-image: url(/imagens/itens_home/teste.png)">');

        $this->assertEquals($this->app->resolved('App\Http\Middleware\ShareData'), true);
        $banners->where('funcao', 'header_fundo')->first()->update(['url' => HomeImagem::padrao()['header_fundo_default'], 'url_mobile' => HomeImagem::padrao()['header_fundo_default']]);

        $dados = [
            'calendario_default' => 'calendario_default',
            'header_logo_default' => 'header_logo_default',
            'header_fundo' => 'imagens/itens_home/teste.png',
            'cards_2_default' => 'cards_2_default',
            'cards_1_default' => 'cards_1_default',
            'footer_default' => 'footer_default',
        ];
        
        $user = $this->signInAsAdmin();
        
        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertRedirect(route('imagens.itens.home'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!');

        $this->get(route('imagens.itens.home'))
        ->assertOk()
        ->assertSee('<a href="/imagens/itens_home/teste.png"')
        ->assertSee('<img src="'. asset('imagens/itens_home/teste.png') .'" class="img-thumbnail" alt="Fundo do logo principal customizado">');

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSee('<header id="header-principal" style="background-image: url(/imagens/itens_home/teste.png)">');

        $this->get(route('licitacoes.siteBusca'))
        ->assertOk()
        ->assertSee('<header id="header-principal" style="background-image: url(/imagens/itens_home/teste.png)">');

        $this->get(route('bdosite.index'))
        ->assertOk()
        ->assertSee('<header id="header-principal" style="background-image: url(/imagens/itens_home/teste.png)">');

        $this->assertEquals($this->app->resolved('App\Http\Middleware\ShareData'), true);

        $this->assertDatabaseHas("home_imagens", [
            'funcao' => 'header_fundo',
            "url" => 'imagens/itens_home/teste.png',
            "url_mobile" => 'imagens/itens_home/teste.png',
            'link' => '#',
            'target' => '_self'
        ]);
    }

    /** @test */
    public function authorized_users_can_update_header_fundo_with_color()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $banners->where('funcao', 'header_fundo')->first()->update(['url' => '#000fff', 'url_mobile' => '#000fff']);
        
        $this->get('/teste/teste/teste')
        ->assertNotFound()
        ->assertSee('<header id="header-principal" style="background-color: #000fff">');

        $this->assertEquals($this->app->resolved('App\Http\Middleware\ShareData'), true);
        $banners->where('funcao', 'header_fundo')->first()->update(['url' => HomeImagem::padrao()['header_fundo_default'], 'url_mobile' => HomeImagem::padrao()['header_fundo_default']]);
        
        $dados = [
            'calendario_default' => 'calendario_default',
            'header_logo_default' => 'header_logo_default',
            'header_fundo_cor' => '#000fff',
            'cards_2_default' => 'cards_2_default',
            'cards_1_default' => 'cards_1_default',
            'footer_default' => 'footer_default',
        ];
        
        $user = $this->signInAsAdmin();
        
        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertRedirect(route('imagens.itens.home'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!');

        $this->get(route('imagens.itens.home'))
        ->assertOk()
        ->assertSee('value="#000fff"')
        ->assertSee('<small><em>Cor atual na home</em></small>');

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSee('<header id="header-principal" style="background-color: #000fff">');

        $this->get(route('licitacoes.siteBusca'))
        ->assertOk()
        ->assertSee('<header id="header-principal" style="background-color: #000fff">');

        $this->get(route('bdosite.index'))
        ->assertOk()
        ->assertSee('<header id="header-principal" style="background-color: #000fff">');

        $this->assertEquals($this->app->resolved('App\Http\Middleware\ShareData'), true);

        $this->assertDatabaseHas("home_imagens", [
            'funcao' => 'header_fundo',
            "url" => '#000fff',
            "url_mobile" => '#000fff',
            'link' => '#',
            'target' => '_self'
        ]);
    }

    /** @test */
    public function authorized_users_can_update_calendario()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_fundo_default' => 'header_fundo_default',
            'header_logo_default' => 'header_logo_default',
            'calendario' => 'imagens/itens_home/teste.png',
            'cards_2_default' => 'cards_2_default',
            'cards_1_default' => 'cards_1_default',
            'footer_default' => 'footer_default',
        ];
        
        $user = $this->signInAsAdmin();
        
        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertRedirect(route('imagens.itens.home'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!');

        $this->get(route('imagens.itens.home'))
        ->assertOk()
        ->assertSee('<a href="/imagens/itens_home/teste.png"')
        ->assertSee('<img src="'. asset('imagens/itens_home/teste.png') .'" class="img-thumbnail" alt="Calendário customizado">');

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSeeInOrder([
            '<a href="/calendario-oficial-core-sp">',
            '<img class="lazy" data-src="'.asset('imagens/itens_home/teste.png').'" alt="Calendário | Core-SP" />',
            '</a>'
        ]);

        $this->assertDatabaseHas("home_imagens", [
            'funcao' => 'calendario',
            "url" => 'imagens/itens_home/teste.png',
            "url_mobile" => 'imagens/itens_home/teste.png',
            'link' => '#',
            'target' => '_self'
        ]);
    }

    /** @test */
    public function authorized_users_can_update_cards_1()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_fundo_default' => 'header_fundo_default',
            'header_logo_default' => 'header_logo_default',
            'calendario_default' => 'calendario_default',
            'cards_1' => '#fff000',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
        ];
        
        $user = $this->signInAsAdmin();
        
        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertRedirect(route('imagens.itens.home'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!');

        $this->get(route('imagens.itens.home'))
        ->assertOk()
        ->assertSee('value="#fff000"');

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSee('<div class="box text-center " style="background-color:#fff000">');

        $this->assertDatabaseHas("home_imagens", [
            'funcao' => 'cards',
            'ordem' => 1,
            "url" => '#fff000',
            "url_mobile" => '#fff000',
            'link' => '#',
            'target' => '_self'
        ]);
    }

    /** @test */
    public function authorized_users_can_update_cards_2()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_fundo_default' => 'header_fundo_default',
            'header_logo_default' => 'header_logo_default',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2' => '#f0f0f0',
            'footer_default' => 'footer_default',
        ];
        
        $user = $this->signInAsAdmin();
        
        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertRedirect(route('imagens.itens.home'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!');

        $this->get(route('imagens.itens.home'))
        ->assertOk()
        ->assertSee('value="#f0f0f0"');

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSee('<div class="box text-center " style="background-color:#f0f0f0">');

        $this->assertDatabaseHas("home_imagens", [
            'funcao' => 'cards',
            'ordem' => 2,
            "url" => '#f0f0f0',
            "url_mobile" => '#f0f0f0',
            'link' => '#',
            'target' => '_self'
        ]);
    }

    /** @test */
    public function authorized_users_can_update_footer()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $banners->where('funcao', 'footer')->first()->update(['url' => '#ff00f0', 'url_mobile' => '#ff00f0']);
        
        $this->get('/teste/teste/teste')
        ->assertNotFound()
        ->assertSee('<footer class="pt-4" id="rodape" style="background-color:#ff00f0">');

        $this->assertEquals($this->app->resolved('App\Http\Middleware\ShareData'), true);
        $banners->where('funcao', 'header_fundo')->first()->update(['url' => HomeImagem::padrao()['footer_default'], 'url_mobile' => HomeImagem::padrao()['footer_default']]);

        $dados = [
            'header_fundo_default' => 'header_fundo_default',
            'header_logo_default' => 'header_logo_default',
            'calendario_default' => 'calendario_default',
            'cards_2_default' => 'cards_2_default',
            'cards_1_default' => 'cards_1_default',
            'footer' => '#ff00f0',
        ];
        
        $user = $this->signInAsAdmin();
        
        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertRedirect(route('imagens.itens.home'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!');

        $this->get(route('imagens.itens.home'))
        ->assertOk()
        ->assertSee('value="#ff00f0"');

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSee('<footer class="pt-4" id="rodape" style="background-color:#ff00f0">');

        $this->get(route('licitacoes.siteBusca'))
        ->assertOk()
        ->assertSee('<footer class="pt-4" id="rodape" style="background-color:#ff00f0">');

        $this->get(route('bdosite.index'))
        ->assertOk()
        ->assertSee('<footer class="pt-4" id="rodape" style="background-color:#ff00f0">');

        $this->assertEquals($this->app->resolved('App\Http\Middleware\ShareData'), true);

        $this->assertDatabaseHas("home_imagens", [
            'funcao' => 'footer',
            'ordem' => 1,
            "url" => '#ff00f0',
            "url_mobile" => '#ff00f0',
            'link' => '#',
            'target' => '_self'
        ]);
    }

    /** @test */
    public function authorized_users_can_update_neve()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $banners->where('funcao', 'header_fundo')->first()->update(['url' => '#000fff', 'url_mobile' => '#000fff']);
        $banners->where('funcao', 'neve')->first()->update(['url' => HomeImagem::padrao()['neve_default'], 'url_mobile' => HomeImagem::padrao()['neve_default']]);
        
        $this->get('/teste/teste/teste')
        ->assertNotFound()
        ->assertSee('<header id="header-principal" style="background-color: #000fff;background-image: url(/'.HomeImagem::padrao()['neve_default'].');background-repeat: repeat-x">');

        $this->assertEquals($this->app->resolved('App\Http\Middleware\ShareData'), true);
        $banners->where('funcao', 'header_fundo')->first()->update(['url' => HomeImagem::padrao()['header_fundo_default'], 'url_mobile' => HomeImagem::padrao()['header_fundo_default']]);
        $banners->where('funcao', 'neve')->first()->update(['url' => null, 'url_mobile' => null]);

        $dados = [
            'header_fundo_cor' => '#000fff',
            'header_logo_default' => 'header_logo_default',
            'calendario_default' => 'calendario_default',
            'cards_2_default' => 'cards_2_default',
            'cards_1_default' => 'cards_1_default',
            'footer_default' => 'footer_default',
            'neve_default' => 'neve_default',
        ];
        
        $user = $this->signInAsAdmin();
        
        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertRedirect(route('imagens.itens.home'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!');

        $this->get(route('imagens.itens.home'))
        ->assertOk()
        ->assertSeeInOrder([
            'value="neve_default"',
            'checked',
            '/> Sim, inserir neve'
        ]);

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSee('<header id="header-principal" style="background-color: #000fff;background-image: url(/'.HomeImagem::padrao()['neve_default'].');background-repeat: repeat-x">');

        $this->get(route('licitacoes.siteBusca'))
        ->assertOk()
        ->assertSee('<header id="header-principal" style="background-color: #000fff;background-image: url(/'.HomeImagem::padrao()['neve_default'].');background-repeat: repeat-x">');

        $this->get(route('bdosite.index'))
        ->assertOk()
        ->assertSee('<header id="header-principal" style="background-color: #000fff;background-image: url(/'.HomeImagem::padrao()['neve_default'].');background-repeat: repeat-x">');

        $this->assertEquals($this->app->resolved('App\Http\Middleware\ShareData'), true);

        $this->assertDatabaseHas("home_imagens", [
            'funcao' => 'neve',
            'ordem' => 1,
            "url" => HomeImagem::padrao()['neve_default'],
            "url_mobile" => HomeImagem::padrao()['neve_default'],
            'link' => '#',
            'target' => '_self'
        ]);
    }

    /** @test */
    public function authorized_users_can_update_disabled_neve()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $banners->where('funcao', 'header_fundo')->first()->update([
            'url' => '#000fff',
            'url_mobile' => '#000fff'
        ]);
        $banners->where('funcao', 'neve')->first()->update([
            'url' => HomeImagem::padrao()['neve_default'],
            'url_mobile' => HomeImagem::padrao()['neve_default']
        ]);
        $dados = [
            'header_fundo_cor' => '#000fff',
            'header_logo_default' => 'header_logo_default',
            'calendario_default' => 'calendario_default',
            'cards_2_default' => 'cards_2_default',
            'cards_1_default' => 'cards_1_default',
            'footer_default' => 'footer_default',
            'neve_default' => null,
        ];
        
        $this->get('/teste/teste/teste')
        ->assertNotFound()
        ->assertSee('<header id="header-principal" style="background-color: #000fff;background-image: url(/'.HomeImagem::padrao()['neve_default'].');background-repeat: repeat-x">');

        $this->assertEquals($this->app->resolved('App\Http\Middleware\ShareData'), true);

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSee('<header id="header-principal" style="background-color: #000fff;background-image: url(/'.HomeImagem::padrao()['neve_default'].');background-repeat: repeat-x">');

        $this->get(route('licitacoes.siteBusca'))
        ->assertOk()
        ->assertSee('<header id="header-principal" style="background-color: #000fff;background-image: url(/'.HomeImagem::padrao()['neve_default'].');background-repeat: repeat-x">');

        $this->get(route('bdosite.index'))
        ->assertOk()
        ->assertSee('<header id="header-principal" style="background-color: #000fff;background-image: url(/'.HomeImagem::padrao()['neve_default'].');background-repeat: repeat-x">');

        $this->assertEquals($this->app->resolved('App\Http\Middleware\ShareData'), true);

        $user = $this->signInAsAdmin();
        
        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertRedirect(route('imagens.itens.home'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!');

        $this->get('/teste/teste/teste')
        ->assertNotFound()
        ->assertDontSee('<header id="header-principal" style="background-color: #000fff;background-image: url(/'.HomeImagem::padrao()['neve_default'].');background-repeat: repeat-x">');

        $this->get(route('site.home'))
        ->assertOk()
        ->assertDontSee('<header id="header-principal" style="background-color: #000fff;background-image: url(/'.HomeImagem::padrao()['neve_default'].');background-repeat: repeat-x">');

        $this->get(route('licitacoes.siteBusca'))
        ->assertOk()
        ->assertDontSee('<header id="header-principal" style="background-color: #000fff;background-image: url(/'.HomeImagem::padrao()['neve_default'].');background-repeat: repeat-x">');

        $this->get(route('bdosite.index'))
        ->assertOk()
        ->assertDontSee('<header id="header-principal" style="background-color: #000fff;background-image: url(/'.HomeImagem::padrao()['neve_default'].');background-repeat: repeat-x">');

        $this->assertEquals($this->app->resolved('App\Http\Middleware\ShareData'), true);
        
        $this->assertDatabaseHas("home_imagens", [
            'funcao' => 'neve',
            'ordem' => 1,
            "url" => null,
            "url_mobile" => null,
            'link' => '#',
            'target' => '_self'
        ]);
    }

    /** @test */
    public function authorized_users_can_update_popup_video_default()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_fundo_default' => 'header_fundo_default',
            'header_logo_default' => 'header_logo_default',
            'calendario_default' => 'calendario_default',
            'cards_2_default' => 'cards_2_default',
            'cards_1_default' => 'cards_1_default',
            'footer_default' => 'footer_default',
            'popup_video_default' => 'popup_video_default',
        ];
        
        $user = $this->signInAsAdmin();
        
        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertRedirect(route('imagens.itens.home'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!');

        $this->get(route('imagens.itens.home'))
        ->assertOk()
        ->assertSeeInOrder([
            'value="popup_video_default"',
            'checked',
            '/> Sim, inserir pop-up com link padrão'
        ]);

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSee('<div class="modal hide fade" id="popup-campanha">')
        ->assertSee('src="'.HomeImagem::padrao()['popup_video_default'].'"');

        $this->assertDatabaseHas("home_imagens", [
            'funcao' => 'popup_video',
            'ordem' => 1,
            "url" => HomeImagem::padrao()['popup_video_default'],
            "url_mobile" => HomeImagem::padrao()['popup_video_default'],
            'link' => '#',
            'target' => '_self'
        ]);
    }

    /** @test */
    public function authorized_users_can_update_popup_video_custom()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_fundo_default' => 'header_fundo_default',
            'header_logo_default' => 'header_logo_default',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
            'popup_video_default' => 'sem_video',
            'popup_video' => 'https://youtube.com/embed/123YUO',
        ];
        
        $user = $this->signInAsAdmin();
        
        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertRedirect(route('imagens.itens.home'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!');

        $this->get(route('imagens.itens.home'))
        ->assertOk()
        ->assertSeeInOrder([
            'value="popup_video"',
            'checked',
            '/> Sim, inserir pop-up com&nbsp;<strong>novo</strong>&nbsp;link'
        ]);

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSee('<div class="modal hide fade" id="popup-campanha">')
        ->assertSee('src="https://youtube.com/embed/123YUO"');

        $this->assertDatabaseHas("home_imagens", [
            'funcao' => 'popup_video',
            'ordem' => 1,
            "url" => 'https://youtube.com/embed/123YUO',
            "url_mobile" => 'https://youtube.com/embed/123YUO',
            'link' => '#',
            'target' => '_self'
        ]);
    }

    /** @test */
    public function authorized_users_can_update_disabled_popup_video()
    {
        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $banners->where('funcao', 'popup_video')->first()->update([
            'url' => 'https://youtube.com/embed/123YUO',
            'url_mobile' => 'https://youtube.com/embed/123YUO'
        ]);
        $dados = [
            'header_fundo_default' => 'header_fundo_default',
            'header_logo_default' => 'header_logo_default',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
            'popup_video_default' => 'sem_video',
        ];
        
        $user = $this->signInAsAdmin();

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSee('<div class="modal hide fade" id="popup-campanha">')
        ->assertSee('src="https://youtube.com/embed/123YUO"');

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertRedirect(route('imagens.itens.home'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!');

        $this->get(route('site.home'))
        ->assertOk()
        ->assertDontSee('<div class="modal hide fade" id="popup-campanha">')
        ->assertDontSee('src="https://youtube.com/embed/123YUO"');

        $this->assertDatabaseHas("home_imagens", [
            'funcao' => 'popup_video',
            'ordem' => 1,
            "url" => null,
            "url_mobile" => null,
            'link' => '#',
            'target' => '_self'
        ]);
    }

    /** @test */
    public function cannot_be_updated_without_header_logo()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => null,
            'header_logo' => null,
            'header_fundo_default' => 'header_fundo_default',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('header_logo');
    }

    /** @test */
    public function cannot_be_updated_with_header_logo_default_invalid()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_defaul',
            'header_logo' => null,
            'header_fundo_default' => 'header_fundo_default',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('header_logo_default');
    }

    /** @test */
    public function cannot_be_updated_with_header_logo_more_than_191_chars()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => null,
            'header_logo' => $this->faker()->sentence(400),
            'header_fundo_default' => 'header_fundo_default',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('header_logo');
    }

    /** @test */
    public function cannot_be_updated_without_header_fundo()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => null,
            'header_fundo' => null,
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('header_fundo');
    }

    /** @test */
    public function cannot_be_updated_with_header_fundo_default_invalid()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_defaul',
            'header_fundo' => null,
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('header_fundo_default');
    }

    /** @test */
    public function cannot_be_updated_with_header_fundo_more_than_191_chars()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => null,
            'header_fundo' => $this->faker()->sentence(400),
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('header_fundo');
    }

    /** @test */
    public function cannot_be_updated_with_header_fundo_cor_invalid()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => null,
            'header_fundo' => null,
            'header_fundo_cor' => '$123456',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('header_fundo');
    }

    /** @test */
    public function cannot_be_updated_without_cards_1()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'cards_1_default' => null,
            'cards_1' => null,
            'calendario_default' => 'calendario_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('cards_1');
    }

    /** @test */
    public function cannot_be_updated_with_cards_1_default_invalid()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'cards_1_default' => 'cards_1_defaul',
            'cards_1' => null,
            'calendario_default' => 'calendario_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('cards_1_default');
    }

    /** @test */
    public function cannot_be_updated_with_cards_1_invalid()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'cards_1_default' => null,
            'cards_1' => '@123',
            'calendario_default' => 'calendario_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('cards_1');
    }

    /** @test */
    public function cannot_be_updated_without_cards_2()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'cards_2_default' => null,
            'cards_2' => null,
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'footer_default' => 'footer_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('cards_2');
    }

    /** @test */
    public function cannot_be_updated_with_cards_2_default_invalid()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'cards_2_default' => 'cards_2_defaul',
            'cards_2' => null,
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'footer_default' => 'footer_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('cards_2_default');
    }

    /** @test */
    public function cannot_be_updated_with_cards_2_invalid()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'cards_2_default' => null,
            'cards_2' => '@123',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'footer_default' => 'footer_default',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('cards_2');
    }

    /** @test */
    public function cannot_be_updated_without_footer()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'cards_2_default' => 'cards_2_default',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'footer_default' => null,
            'footer' => null,
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('footer');
    }

    /** @test */
    public function cannot_be_updated_with_footer_default_invalid()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'cards_2_default' => 'cards_2_default',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'footer_default' => 'footer_defaul',
            'footer' => null,
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('footer_default');
    }

    /** @test */
    public function cannot_be_updated_with_footer_invalid()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'cards_2_default' => 'cards_2_default',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'footer_default' => null,
            'footer' => '#36jjP',
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('footer');
    }

    /** @test */
    public function cannot_be_updated_without_calendario()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
            'calendario_default' => null,
            'calendario' => null
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('calendario');
    }

    /** @test */
    public function cannot_be_updated_with_calendario_default_invalid()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
            'calendario_default' => 'calendario_def',
            'calendario' => null
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('calendario_default');
    }

    /** @test */
    public function cannot_be_updated_with_calendario_more_than_191_chars()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
            'calendario_default' => null,
            'calendario' => $this->faker()->sentence(400),
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('calendario');
    }

    /** @test */
    public function cannot_be_updated_with_neve_default_invalid()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo' => '#000fff',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
            'neve_default' => 'neve_'
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('neve_default');
    }

    /** @test */
    public function cannot_be_updated_with_header_fundo_image_if_neve_default()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
            'neve_default' => 'neve_default'
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('neve_default');
    }

    /** @test */
    public function cannot_be_updated_with_popup_video_default_invalid()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
            'popup_video_default' => 'popup_video_defaul',
            'popup_video' => null
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('popup_video_default');
    }

    /** @test */
    public function cannot_be_updated_with_popup_video_invalid()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
            'popup_video_default' => null,
            'popup_video' => 'abcdefg'
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('popup_video');
    }

    /** @test */
    public function cannot_be_updated_with_popup_video_more_than_191_chars()
    {
        $user = $this->signInAsAdmin();

        $banners = factory('App\HomeImagem', HomeImagem::TOTAL_ITENS_HOME)->states('itens_home')->create();
        $dados = [
            'header_logo_default' => 'header_logo_default',
            'header_fundo_default' => 'header_fundo_default',
            'calendario_default' => 'calendario_default',
            'cards_1_default' => 'cards_1_default',
            'cards_2_default' => 'cards_2_default',
            'footer_default' => 'footer_default',
            'popup_video_default' => null,
            'popup_video' => $this->faker()->sentence(400)
        ];

        $this->patch(route('imagens.itens.home.update', $dados))
        ->assertSessionHasErrors('popup_video');
    }

    /** 
     * =======================================================================================================
     * TESTES STORAGE
     * =======================================================================================================
     */

    /** @test */
    public function can_view_folder_itens_home()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();

        $this->post(route('imagens.itens.home.storage.post'), ['file_itens_home' => UploadedFile::fake()->image('teste.jpg')]);

        Storage::disk('itens_home')->assertExists('teste.jpg');

        $this->get(route('imagens.itens.home.storage'))
        ->assertOk()
        ->assertJson([
            'path' => ['teste.jpg'],
            'caminho' => HomeImagem::caminhoStorage(),
            'folder' => 'itens-home'
        ]);
    }

    /** @test */
    public function can_view_folder_img()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('imagens.itens.home.storage', 'img'))
        ->assertOk()
        ->assertJsonFragment([
            '001-whatsapp.png',
            'caminho' => 'img/',
            'folder' => 'img'
        ]);
    }

    /** @test */
    public function cannot_view_folder_custom_with_invalid_folder_name()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('imagens.itens.home.storage', 'imge'))
        ->assertNotFound();
    }

    /** @test */
    public function can_upload_file_itens_home()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();

        $files = ['0123.jpg', 'abs123.png', 'abc_123.jpeg', 'asd.ghj.png', '_brasão-.png'];
        foreach($files as $chave => $fileName)
        {
            $temp = strtr(utf8_decode($fileName), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $this->post(route('imagens.itens.home.storage.post'), ['file_itens_home' => UploadedFile::fake()->create($fileName)])
            ->assertJson(['novo_arquivo' => $temp]);
            $files[$chave] = $temp;
            Storage::disk('itens_home')->assertExists($temp);
        }

        $this->get(route('imagens.itens.home.storage'))
        ->assertOk()
        ->assertJsonFragment([
            'path' => $files,
            'caminho' => HomeImagem::caminhoStorage(),
            'folder' => 'itens-home'
        ]);
    }

    /** @test */
    public function can_rename_upload_file_itens_home()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();

        $temp = strtr(utf8_decode('teste.jpeg'), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
        $this->post(route('imagens.itens.home.storage.post'), ['file_itens_home' => UploadedFile::fake()->create($temp)])
        ->assertJson(['novo_arquivo' => $temp]);
        Storage::disk('itens_home')->assertExists($temp);

        $this->get(route('imagens.itens.home.storage'))
        ->assertOk()
        ->assertJsonFragment([
            'path' => [$temp],
            'caminho' => HomeImagem::caminhoStorage(),
            'folder' => 'itens-home'
        ]);

        $temp = strtr(utf8_decode($temp), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
        $copia = 'teste_' .Carbon::now()->timestamp.'.jpeg';
        $this->post(route('imagens.itens.home.storage.post'), ['file_itens_home' => UploadedFile::fake()->create($temp)])
        ->assertJson(['novo_arquivo' => $copia]);
        Storage::disk('itens_home')->assertExists($copia);

        $this->get(route('imagens.itens.home.storage'))
        ->assertOk()
        ->assertJsonFragment([
            'path' => [$temp, $copia],
            'caminho' => HomeImagem::caminhoStorage()
        ]);
    }

    /** @test */
    public function log_is_generated_when_upload_file()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();
        
        $this->post(route('imagens.itens.home.storage.post'), ['file_itens_home' => UploadedFile::fake()->create('teste.jpeg')])
        ->assertJson(['novo_arquivo' => 'teste.jpeg']);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') está armazenando *arquivo de imagem em itens da home com upload do file: '.HomeImagem::pathCompleto().'teste.jpeg* (id: ';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_upload_without_file()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();

        $this->post(route('imagens.itens.home.storage.post'), ['file_itens_home' => null])
        ->assertSessionHasErrors('file_itens_home');
    }

    /** @test */
    public function cannot_upload_with_file_invalid_mimetype()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();

        $files = ['0123.gif', 'abs123.mp3', 'abc_123.exe', 'asd.ghj.pdf', '_brasao-.zip'];
        foreach($files as $fileName)
            $this->post(route('imagens.itens.home.storage.post'), ['file_itens_home' => UploadedFile::fake()->create($fileName)])
            ->assertSessionHasErrors('file_itens_home');
    }

    /** @test */
    public function cannot_upload_with_file_more_than_2mb()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();
  
        $this->post(route('imagens.itens.home.storage.post'), ['file_itens_home' => UploadedFile::fake()->create('teste.png', 2049)])
        ->assertSessionHasErrors('file_itens_home');
    }

    /** @test */
    public function cannot_upload_with_file_invalid_type()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();
  
        $this->post(route('imagens.itens.home.storage.post'), ['file_itens_home' => 'teste sem file'])
        ->assertSessionHasErrors('file_itens_home');
    }

    /** @test */
    public function can_delete_file_itens_home()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();

        $this->post(route('imagens.itens.home.storage.post'), ['file_itens_home' => UploadedFile::fake()->create('teste.jpeg')]);
        Storage::disk('itens_home')->assertExists('teste.jpeg');

        $this->delete(route('imagens.itens.home.storage.delete', 'teste.jpeg'))
        ->assertOk()
        ->assertJson([]);

        Storage::disk('itens_home')->assertMissing('teste.jpeg');
    }

    /** @test */
    public function log_is_generated_when_delete_file()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();

        $this->post(route('imagens.itens.home.storage.post'), ['file_itens_home' => UploadedFile::fake()->create('teste.jpeg')]);
        Storage::disk('itens_home')->assertExists('teste.jpeg');

        $this->delete(route('imagens.itens.home.storage.delete', 'teste.jpeg'))
        ->assertOk()
        ->assertJson([]);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') excluiu *arquivo armazenado como item da home: teste.jpeg* (id: ---)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_delete_file_itens_home_when_not_found()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();

        $this->delete(route('imagens.itens.home.storage.delete', 'teste.jpeg'))
        ->assertNotFound();
    }

    /** @test */
    public function can_download_file()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();

        $this->post(route('imagens.itens.home.storage.post'), ['file_itens_home' => UploadedFile::fake()->create('teste.jpeg')]);
        Storage::disk('itens_home')->assertExists('teste.jpeg');

        $this->get(route('imagens.itens.home.storage.download', ['folder' => 'itens-home', 'arquivo' => 'teste.jpeg']))
        ->assertOk();

        $this->get(route('imagens.itens.home.storage.download', ['folder' => 'img', 'arquivo' => '001-whatsapp.png']))
        ->assertOk();
    }

    /** @test */
    public function cannot_download_file_without_file()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();

        $this->get(route('imagens.itens.home.storage.download', ['folder' => 'itens-home', 'arquivo' => 'teste.jpeg']))
        ->assertNotFound();

        $this->get(route('imagens.itens.home.storage.download', ['folder' => 'img', 'arquivo' => 'teste_teste.png']))
        ->assertNotFound();
    }

    /** @test */
    public function cannot_download_file_with_invalid_folder()
    {
        Storage::fake('itens_home');

        $user = $this->signInAsAdmin();

        $this->post(route('imagens.itens.home.storage.post'), ['file_itens_home' => UploadedFile::fake()->create('teste.jpeg')]);
        Storage::disk('itens_home')->assertExists('teste.jpeg');

        $this->get(route('imagens.itens.home.storage.download', ['folder' => 'itens_home', 'arquivo' => 'teste.jpeg']))
        ->assertNotFound();

        $this->get(route('imagens.itens.home.storage.download', ['folder' => 'imge', 'arquivo' => '001-whatsapp.png']))
        ->assertNotFound();
    }
}