<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Noticia;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function homepage_is_shown_correctly()
    {
        $this->get('/')->assertOk();
    }

    /** @test */
    public function noticia_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create([
            'idregional' => null
        ]);

        $this->get('/')->assertSee($noticia->titulo);
    }

    /** @test */
    public function noticias_is_shown_on_homepage()
    {
        $noticias = factory('App\Noticia', 7)->create([
            'idregional' => null
        ]);

        $this->get('/')
        ->assertSee($noticias->get(0)->titulo)
        ->assertSee($noticias->get(1)->titulo)
        ->assertSee($noticias->get(2)->titulo)
        ->assertSee($noticias->get(3)->titulo)
        ->assertSee($noticias->get(4)->titulo)
        ->assertSee($noticias->get(5)->titulo)
        ->assertDontSee($noticias->get(6)->titulo);
    }

    /** @test */
    public function link_to_noticia_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create([
            'idregional' => null
        ]);

        $this->get('/')->assertSee(route('noticias.show', $noticia->slug));
    }

    /** @test */
    public function cotidiano_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create([
            'categoria' => 'Cotidiano'
        ]);

        $this->get('/')->assertSee($noticia->titulo)
        ->assertSee(route('noticias.show', $noticia->slug));
    }

    /** @test */
    public function cotidianos_is_shown_on_homepage()
    {
        $noticias = factory('App\Noticia', 5)->create([
            'categoria' => 'Cotidiano'
        ]);

        $this->get('/')
        ->assertSee($noticias->get(0)->titulo)
        ->assertSee($noticias->get(1)->titulo)
        ->assertSee($noticias->get(2)->titulo)
        ->assertSee($noticias->get(3)->titulo)
        ->assertDontSee($noticias->get(4)->titulo);
    }

    /** @test */
    public function post_is_shown_on_homepage()
    {
        $post = factory('App\Post')->create();

        $this->get('/')->assertSee($post->titulo);
    }

    /** @test */
    public function posts_is_shown_on_homepage()
    {
        $posts = factory('App\Post', 4)->create();

        $this->get('/')
        ->assertSee($posts->get(0)->titulo)
        ->assertSee($posts->get(1)->titulo)
        ->assertSee($posts->get(2)->titulo)
        ->assertDontSee($posts->get(3)->titulo);
    }

    /** @test */
    public function feiras_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create([
            'categoria' => 'Feiras'
        ]);

        $this->get(route('site.feiras'))->assertSee($noticia->titulo)
        ->assertSee(route('noticias.show', $noticia->slug));
    }

    /** @test */
    public function acoes_fiscalizacao_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create([
            'categoria' => 'Fiscalização'
        ]);

        $this->get(route('fiscalizacao.acoesfiscalizacao'))->assertSee($noticia->titulo)
        ->assertSee(route('noticias.show', $noticia->slug));
    }

    /** @test */
    public function espaco_contador_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create([
            'categoria' => 'Espaço do Contador'
        ]);

        $this->get(route('fiscalizacao.espacoContador'))->assertSee($noticia->titulo)
        ->assertSee(route('noticias.show', $noticia->slug));
    }

    /** @test */
    public function same_csrf_token_when_lockout_cannot_try_any_login_on_portal()
    {
        $this->get('/')->assertOk();
        $csrf = csrf_token();

        for($i = 0; $i < 4; $i++)
        {
            $this->get('admin/login')->assertOk();
            $this->assertEquals($csrf, request()->session()->get('_token'));
            $this->post('admin/login', ['login' => 'Teste', 'password' => 'TestePorta1']);
            $this->assertEquals($csrf, request()->session()->get('_token'));
        }

        $representante = factory('App\Representante')->create();

        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste1020']);
        $this->assertEquals($csrf, request()->session()->get('_token'));
        $this->get(route('representante.login'))
        ->assertSee('Login inválido devido à quantidade de tentativas.');
        
        $this->assertEquals($csrf, request()->session()->get('_token'));

        $this->post('admin/login', ['login' => 'Teste', 'password' => 'TestePorta1']);
        $this->assertEquals($csrf, request()->session()->get('_token'));
        $this->get('admin/login')
        ->assertSee('Login inválido devido à quantidade de tentativas.');
    }

    /** @test */
    public function search_on_portal()
    {
        $post = factory('App\Post')->create([
            'titulo' => 'Teste título post na busca da home'
        ]);

        $noticia = factory('App\Noticia')->create([
            'titulo' => 'Teste título notícia na busca da home'
        ]);

        $pagina = factory('App\Pagina')->create([
            'titulo' => 'Teste título página na busca da home'
        ]);

        $this->get('/')->assertOk();

        $this->get(route('site.busca', ['busca' => 'Teste home']))
        ->assertSee('<h5 class="normal"><i>Notícia -</i> <strong>'.$noticia->titulo.'</strong></h5>')
        ->assertSee('<h5 class="normal"><i>Post -</i> <strong>'.$post->titulo.'</strong></h5>')
        ->assertSee('<h5 class="normal mb-2"><i>Página -</i> <strong>'.$pagina->titulo.'</strong></h5>')
        ->assertSee($post->titulo)
        ->assertSee($noticia->titulo)
        ->assertSee($pagina->titulo);
    }

    /** @test */
    public function consulta_situacao_on_portal()
    {
        $this->get(route('consultaSituacao'))
        ->assertSee('<h2 class="stronger">Representante já pode consultar, com mais facilidade, sua situação junto ao Conselho!</h2>')
        ->assertSee('<p class="mb-2 light">A consulta de situação, novo serviço oferecido pelo Core-SP, é uma solução informatizada que permite verificar a situação do Representante Comercial junto ao Conselho.</p>')
        ->assertSee('<label for="cpfCnpj">Insira o CPF/CNPJ abaixo:</label>')
        ->assertSee('<p class="mt-1 mb-1">O teor desta consulta é meramente informativo, não valendo como certidão.</p>');

        $this->post(route('consultaSituacao.post'), ['cpfCnpj' => '86294373085'])
        ->assertSee('<p class="light"><i>Resultados para a busca do CPF/CNPJ:</i> <strong>86294373085</strong></p>')
        ->assertSee('<h5>RC Teste 1</h5>')
        ->assertSee('<p><strong>Registro:</strong> 000000/0001</p>')
        ->assertSee('<p><strong>CPF:</strong> 86294373085</p>')
        ->assertSee('<p class="mt-2"><strong>Situação:</strong> <span class="badge badge-success">Ativo</span></p>');

        $this->post(route('consultaSituacao.post'), ['cpfCnpj' => '79096445000177'])
        ->assertSee('<p><strong>Nenhum Representante Comercial encontrado com o CPF/CNPJ fornecido!</strong></p>');
    }

    /** @test */
    public function anuidade_vigente_on_portal()
    {
        $this->get(route('anuidade-ano-vigente'))
        ->assertSee('<h2 class="stronger">Baixe o boleto de anuidade do ano vigente</h2>')
        ->assertSee('<label for="cpfCnpj">CPF ou CNPJ *</label>')
        ->assertSee('<p>Informe o CPF ou CNPJ abaixo para verificar a disponibilidade do boleto de anuidade do ano vigente, e então baixe-o clicando no link.</p>');

        $this->post(route('anuidade-ano-vigente.post'), ['cpfCnpj' => '86294373085'])
        ->assertSee('<p class="pb-0"><strong>Anuidade encontrada!</strong> Baixe o boleto clicando no link abaixo:</p>')
        ->assertSee('<i class="fas fa-download"></i>&nbsp;&nbsp;BAIXAR BOLETO');

        $this->post(route('anuidade-ano-vigente.post'), ['cpfCnpj' => '79096445000177'])
        ->assertSee('<strong>Nenhum boleto encontrado para o CPF/CNPJ informado.</strong>');
    }
}
