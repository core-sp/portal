<?php

namespace Tests\Feature;

use App\Permissao;
use App\Newsletter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $this->get(route('newsletter.download'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $this->get(route('newsletter.download'))->assertForbidden();
    }

    /** @test */
    public function total_newsletter_are_shown_on_admin_panel()
    {
        $this->signIn();

        $news = factory('App\Newsletter', 50)->create();

        $this->get(route('admin'))
            ->assertOk()
            ->assertSee('<span class="info-box-number inherit d-inline">'.$news->count().'</span>');
    }

    /** @test */
    public function button_download_newsletter_are_shown_on_admin_panel()
    {
        $this->signInAsAdmin();

        $news = factory('App\Newsletter', 50)->create();

        $this->get(route('admin'))
            ->assertOk()
            ->assertSee('<a href="'.route('newsletter.download').'" class="inherit">')
            ->assertSee('&nbsp;<span class="linkDownload d-inline">(Baixar CSV)</span>');
    }

    /** @test */
    public function button_download_newsletter_are_shown_on_admin_panel_to_perfil_3()
    {
        $perfil = factory('App\Perfil')->create([
            'idperfil' => 3,
            'nome' => 'Perfil'
        ]);
        $user = factory('App\User')->create([
            'idperfil' => $perfil->idperfil
        ]);
        $this->signIn($user);

        $news = factory('App\Newsletter', 50)->create();

        $this->get(route('admin'))
            ->assertOk()
            ->assertSee('<a href="'.route('newsletter.download').'" class="inherit">')
            ->assertSee('&nbsp;<span class="linkDownload d-inline">(Baixar CSV)</span>');
    }

    /** @test */
    public function button_download_newsletter_are_not_shown_on_admin_panel_to_perfil_3()
    {
        $perfil = factory('App\Perfil')->create([
            'idperfil' => 2,
            'nome' => 'Perfil'
        ]);
        $user = factory('App\User')->create([
            'idperfil' => $perfil->idperfil
        ]);
        $this->signIn($user);

        $news = factory('App\Newsletter', 50)->create();

        $this->get(route('admin'))
            ->assertOk()
            ->assertDontSee('<a href="'.route('newsletter.download').'" class="inherit">')
            ->assertDontSee('&nbsp;<span class="linkDownload d-inline">(Baixar CSV)</span>');
    }

    /** @test */
    public function log_is_generated_when_download_newsletter()
    {
        $user = $this->signInAsAdmin();

        $news = factory('App\Newsletter', 50)->create();
        $this->get(route('newsletter.download'))->assertOk();

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') realizou download *newsletter* (id: ---)';
        $this->assertStringContainsString($txt, $log);
    }

    /** 
     * =======================================================================================================
     * TESTES NEWSLETTER SITE
     * =======================================================================================================
     */

    /** @test */
    public function can_view_inputs_on_portal()
    {
        $this->get(route('site.home'))
            ->assertOk()
            ->assertSee('<form class="mt-3" id="newsletter" method="POST" action="'.route('newsletter.post').'">')
            ->assertSee('name="nome"')
            ->assertSee('name="email"')
            ->assertSee('name="celular"')
            ->assertSee('name="termo"')
            ->assertSee('Li e concordo com o <a href="'.route('termo.consentimento.pdf').'" target="_blank"><u>Termo de Consentimento</u></a> de uso de dados, e aceito receber boletins informativos a respeito de parcerias e serviços do CORE-SP.');
    }

    /** @test */
    public function can_register_newsletter_on_portal()
    {
        $news = factory('App\Newsletter')->states('request')->raw();

        $this->post(route('newsletter.post'), $news)
            ->assertSeeText('Muito obrigado por inscrever-se em nossa newsletter!');
        
        $news['celular'] = apenasNumeros($news['celular']);
        unset($news['termo']);
        
        $this->assertDatabaseHas('newsletters', $news);
    }

    /** @test */
    public function newsletter_cannot_be_created_without_requireds_inputs()
    {
        $this->post(route('newsletter.post'), [
            'nome' => '',
            'celular' => '',
            'email' => '',
            'termo' => ''
        ])->assertSessionHasErrors([
            'nome',
            'celular',
            'email',
            'termo'
        ]);
    }
    
    /** @test */
    public function newsletter_cannot_be_created_with_nome_length_less_than_5()
    {
        $news = factory('App\Newsletter')->states('request')->raw();
        $news['nome'] = 'Test';

        $this->post(route('newsletter.post'), $news)
        ->assertSessionHasErrors([
            'nome'
        ]);
    }

    /** @test */
    public function newsletter_cannot_be_created_with_nome_length_greater_than_191()
    {
        $faker = \Faker\Factory::create();
        $news = factory('App\Newsletter')->states('request')->raw();
        $news['nome'] = $faker->sentence(400);

        $this->post(route('newsletter.post'), $news)
        ->assertSessionHasErrors([
            'nome'
        ]);
    }

    /** @test */
    public function newsletter_cannot_be_created_with_number_in_nome()
    {
        $news = factory('App\Newsletter')->states('request')->raw();
        $news['nome'] = 'T3ste';

        $this->post(route('newsletter.post'), $news)
        ->assertSessionHasErrors([
            'nome'
        ]);
    }

    /** @test */
    public function newsletter_cannot_be_created_with_invalid_email()
    {
        $news = factory('App\Newsletter')->states('request')->raw();
        $news['email'] = 'teste@';

        $this->post(route('newsletter.post'), $news)
        ->assertSessionHasErrors([
            'email'
        ]);
    }

    /** @test */
    public function newsletter_cannot_be_created_with_invalid_celular()
    {
        $news = factory('App\Newsletter')->states('request')->raw();
        $numeros = [
            '(11) A9999-9999', '(1) 99999-9999', '(11) 999999999', '11 99999-9999', '(11) 9999-9999'
        ];

        foreach($numeros as $numero)
        {
            $news['celular'] = $numero;
            $this->post(route('newsletter.post'), $news)
            ->assertSessionHasErrors([
                'celular'
            ]);
        }
    }

    /** @test */
    public function newsletter_cannot_be_created_without_termo()
    {
        $news = factory('App\Newsletter')->states('request')->raw();
        $news['termo'] = 'off';

        $this->post(route('newsletter.post'), $news)
        ->assertSessionHasErrors([
            'termo'
        ]);
    }

    /** @test */
    public function log_is_generated_when_create_on_portal()
    {
        $newsletter = factory('App\Newsletter')->states('request')->raw();
        $this->post(route('newsletter.post'), $newsletter);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . "*".$newsletter['nome']."* (".$newsletter['email'].")";
        $txt .= ' *registrou-se* na newsletter e foi criado um novo registro no termo de consentimento, com a id: 1';
        $this->assertStringContainsString($txt, $log);
    }
}
