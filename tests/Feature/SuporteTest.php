<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\SuporteIp;
use Illuminate\Support\Facades\Mail;
use App\Mail\InternoSuporteMail;

class SuporteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
                
        $this->get(route('suporte.log.externo.index'))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.hoje.view', 'interno'))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.busca'))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.view', ['data' => date('Y-m-d'), 'tipo' => 'interno']))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.download', ['data' => date('Y-m-d'), 'tipo' => 'interno']))->assertRedirect(route('login'));
        $this->get(route('suporte.ips.view'))->assertRedirect(route('login'));
        $this->delete(route('suporte.ips.excluir', request()->ip()))->assertRedirect(route('login'));
        $this->get(route('admin.manual'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        $this->get(route('suporte.log.externo.index'))->assertForbidden();
        $this->get(route('suporte.log.externo.hoje.view', 'interno'))->assertForbidden();
        $this->get(route('suporte.log.externo.busca', ['data' => Carbon::today()->subDay()->format('Y-m-d'), 'tipo' => 'interno']))->assertForbidden();
        $this->get(route('suporte.log.externo.view', ['data' => date('Y-m-d'), 'tipo' => 'interno']))->assertForbidden();
        $this->get(route('suporte.log.externo.download', ['data' => date('Y-m-d'), 'tipo' => 'interno']))->assertForbidden();
        $this->get(route('suporte.ips.view'))->assertForbidden();
        $this->delete(route('suporte.ips.excluir', request()->ip()))->assertForbidden();
    }

    /** 
     * =======================================================================================================
     * TESTES LOGS
     * =======================================================================================================
    */

    /** @test */
    public function admin_can_search_logs_by_day_before_today()
    {
        $data = '2022-09-25';

        $conteudo = '[2022-09-30 11:34:04] testing.INFO: [IP: 127.0.0.1] - Usuário 1 ("000000/0001") conectou-se à Área do Representante.';
        Storage::disk('log_externo')->put('2022/09/laravel-'.$data.'.log', $conteudo);
        $conteudo = '[2022-09-30 11:34:04] testing.INFO: [IP: 127.0.0.1] - Usuário (usuário 1) editou *plantão juridico* (id: 1)';
        Storage::disk('log_interno')->put('2022/09/laravel-'.$data.'.log', $conteudo);
        $conteudo = '[2022-09-30 11:34:04] testing.ERROR: [Erro: No query results for model [App\Noticia]. para o slug: teste], [Controller: App\Http\Controllers\NoticiaController@show], [Código: 0], [Arquivo: /home/vagrant/Workspace/portal/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php], [Linha: 470]';
        Storage::disk('log_erros')->put('laravel-'.$data.'.log', $conteudo);

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'externo']))->assertOk()
        ->assertSee('<i class="fas fa-file-alt"></i> - Log <strong>do Site</strong> do dia '.onlyDate($data));

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'interno']))->assertOk()
        ->assertSee('<i class="fas fa-file-alt"></i> - Log <strong>do Admin</strong> do dia '.onlyDate($data));

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'erros']))->assertOk()
        ->assertSee('<i class="fas fa-file-alt"></i> - Log <strong>de Erros</strong> do dia '.onlyDate($data));

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));
    }

    /** @test */
    public function admin_can_view_message_when_without_logs_de_hoje()
    {
        Storage::disk('log_externo')->delete(date('Y').'/'.date('m').'/laravel-'.date('Y-m-d').'.log');
        Storage::disk('log_interno')->delete(date('Y').'/'.date('m').'/laravel-'.date('Y-m-d').'.log');
        Storage::disk('log_erros')->delete('laravel-'.date('Y-m-d').'.log');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.index'))->assertOk();
        $this->get(route('suporte.log.externo.hoje.view', 'externo'))->assertRedirect(route('suporte.log.externo.index'));
        $this->get(route('suporte.log.externo.index'))->assertSeeText('Ainda não há log do Site do dia de hoje: '.date('d/m/Y'));

        $this->get(route('suporte.log.externo.hoje.view', 'interno'))->assertRedirect(route('suporte.log.externo.index'));
        $this->get(route('suporte.log.externo.index'))->assertSeeText('Ainda não há log do Admin do dia de hoje: '.date('d/m/Y'));

        $this->get(route('suporte.log.externo.hoje.view', 'erros'))->assertRedirect(route('suporte.log.externo.index'));
        $this->get(route('suporte.log.externo.index'))->assertSeeText('Ainda não há log de Erros do dia de hoje: '.date('d/m/Y'));
    }

    /** @test */
    public function admin_can_view_logs_today()
    {
        // Criando o log para teste do externo
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->signInAsAdmin();

        // Criando o log para teste do interno
        $plantao = factory('App\PlantaoJuridico')->create();
        $dados = $plantao->toArray();
        $dados['qtd_advogados'] = 1;
        $dados['dataInicial'] = date('Y-m-d', strtotime('+2 month'));
        $dados['dataFinal'] = date('Y-m-d', strtotime('+2 month'));
        $dados['horarios'] = ['10:00', '11:00', '12:00'];
        $this->put(route('plantao.juridico.editar', $plantao->id), $dados);

        // Criando o log para teste de erros
        $this->get('/noticias/teste')->assertStatus(404);

        $this->get(route('suporte.log.externo.index'))
        ->assertOk()
        ->assertSee('Última atualização:')
        ->assertSee('Log do Site hoje')
        ->assertSee('Log do Admin hoje')
        ->assertSee('Log de Erros hoje');

        $this->get(route('suporte.log.externo.hoje.view', 'externo'))
        ->assertHeader('content-disposition', 'inline; filename="laravel-'.date('Y-m-d').'.log"')
        ->assertHeader('content-type', 'text/plain; charset=UTF-8')
        ->assertOk();
        
        $this->get(route('suporte.log.externo.hoje.view', 'interno'))
        ->assertHeader('content-disposition', 'inline; filename="laravel-'.date('Y-m-d').'.log"')
        ->assertHeader('content-type', 'text/plain; charset=UTF-8')
        ->assertOk();

        $this->get(route('suporte.log.externo.hoje.view', 'erros'))
        ->assertHeader('content-disposition', 'inline; filename="laravel-'.date('Y-m-d').'.log"')
        ->assertHeader('content-type', 'text/plain; charset=UTF-8')
        ->assertOk();
    }

    /** @test */
    public function admin_cannot_search_logs_by_day_today_or_after()
    {
        $data = Carbon::today()->format('Y-m-d');
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'externo']))
        ->assertSessionHasErrors('data');

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'interno']))
        ->assertSessionHasErrors('data');

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'erros']))
        ->assertSessionHasErrors('data');

        $data = Carbon::today()->addDay()->format('Y-m-d');

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'externo']))
        ->assertSessionHasErrors('data');

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'interno']))
        ->assertSessionHasErrors('data');

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'erros']))
        ->assertSessionHasErrors('data');
    }

    /** @test */
    public function admin_cannot_search_logs_by_day_with_date_before_2019()
    {
        $data = '2018-12-31';
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'externo']))
        ->assertSessionHasErrors('data');

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'interno']))
        ->assertSessionHasErrors('data');

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'erros']))
        ->assertSessionHasErrors('data');
    }

    /** @test */
    public function admin_cannot_search_logs_by_day_with_tipo_wrong_by_day()
    {
        $data = '2022-09-25';
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'nome_errado']))
        ->assertSessionHasErrors('tipo');
    }

    /** @test */
    public function admin_cannot_search_logs_by_day_with_data_invalid_format()
    {
        $data = '2022/09';
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['data' => $data, 'tipo' => 'externo']))
        ->assertSessionHasErrors('data');
    }

    /** @test */
    public function admin_cannot_search_logs_by_day_without_date()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['tipo' => 'externo']))
        ->assertSessionHasErrors('data');
    }

    /** @test */
    public function admin_can_search_logs_by_month()
    {
        $data = Carbon::today()->subMonth()->format('Y-m');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'externo', 'texto' => 'info']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<th>Ações</th>');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'interno', 'texto' => 'info']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<th>Ações</th>');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));
    }

    /** @test */
    public function admin_can_search_logs_by_month_with_lines()
    {
        $data = Carbon::today()->subMonth()->format('Y-m');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'externo', 'texto' => date('Y'), 'n_linhas' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'interno', 'texto' => 'info', 'n_linhas' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));
    }

    /** @test */
    public function admin_cannot_search_logs_by_month_with_tipo_wrong()
    {
        $data = Carbon::today()->subMonth()->format('Y-m');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'erros', 'texto' => 'info']))
        ->assertSessionHasErrors('tipo');
    }

    /** @test */
    public function admin_cannot_search_logs_by_month_with_month_wrong()
    {
        $data = Carbon::today()->subMonth()->format('Y/m');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'externo', 'texto' => 'info']))
        ->assertSessionHasErrors('mes');
    }

    /** @test */
    public function admin_cannot_search_logs_by_month_with_next_month()
    {
        $data = Carbon::today()->addMonth()->format('Y-m');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'externo', 'texto' => 'info']))
        ->assertSessionHasErrors('mes');
    }

    /** @test */
    public function admin_cannot_search_logs_by_month_with_date_before_2019()
    {
        $data = '2018-12';

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'externo', 'texto' => 'info']))
        ->assertSessionHasErrors('mes');

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'interno', 'texto' => 'info']))
        ->assertSessionHasErrors('mes');
    }

    /** @test */
    public function admin_cannot_search_logs_by_month_with_text_less_than_3_chars()
    {
        $data = Carbon::today()->subMonth()->format('Y-m');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'externo', 'texto' => 'in']))
        ->assertSessionHasErrors('texto');
    }

    /** @test */
    public function admin_cannot_search_logs_by_month_with_text_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $data = Carbon::today()->subMonth()->format('Y-m');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'externo', 'texto' => $faker->sentence(400)]))
        ->assertSessionHasErrors('texto');
    }

    /** @test */
    public function admin_cannot_search_logs_by_month_without_date()
    {
        $data = Carbon::today()->subMonth()->format('Y-m');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['tipo' => 'externo', 'texto' => 'info']))
        ->assertSessionHasErrors('mes');
    }

    /** @test */
    public function admin_cannot_search_logs_by_month_without_tipo()
    {
        $data = Carbon::today()->subMonth()->format('Y-m');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'texto' => 'info']))
        ->assertSessionHasErrors('tipo');
    }

    /** @test */
    public function admin_cannot_search_logs_by_month_without_texto()
    {
        $data = Carbon::today()->subMonth()->format('Y-m');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'externo']))
        ->assertSessionHasErrors('texto');
    }

    /** @test */
    public function admin_can_search_logs_by_year()
    {
        $data = Carbon::today()->format('Y');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'externo', 'texto' => 'teste']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<th>Ações</th>');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'interno', 'texto' => 'info']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<th>Ações</th>');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));
    }

    /** @test */
    public function admin_can_search_logs_by_year_with_lines()
    {
        $data = Carbon::today()->format('Y');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'externo', 'texto' => 'teste', 'n_linhas' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'interno', 'texto' => 'info', 'n_linhas' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));
    }

    /** @test */
    public function admin_cannot_search_logs_by_year_with_tipo_wrong()
    {
        $data = Carbon::today()->format('Y');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'erros', 'texto' => 'info']))
        ->assertSessionHasErrors('tipo');
    }

    /** @test */
    public function admin_cannot_search_logs_by_year_with_year_before_2019()
    {
        $data = '2018';

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'externo', 'texto' => 'teste']))
        ->assertSessionHasErrors('ano');

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'interno', 'texto' => 'teste']))
        ->assertSessionHasErrors('ano');
    }

    /** @test */
    public function admin_cannot_search_logs_by_year_with_date_after_today()
    {
        $data = Carbon::today()->addYear()->format('Y');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'externo', 'texto' => 'teste']))
        ->assertSessionHasErrors('ano');
    }

    /** @test */
    public function admin_cannot_search_logs_by_year_with_text_less_than_3_chars()
    {
        $data = Carbon::today()->format('Y');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'externo', 'texto' => 'te']))
        ->assertSessionHasErrors('texto');
    }

    /** @test */
    public function admin_cannot_search_logs_by_year_with_text_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $data = Carbon::today()->format('Y');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'externo', 'texto' => $faker->sentence(400)]))
        ->assertSessionHasErrors('texto');
    }

    /** @test */
    public function admin_cannot_search_logs_by_year_without_date()
    {
        $data = Carbon::today()->format('Y');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['tipo' => 'externo', 'texto' => 'teste']))
        ->assertSessionHasErrors('ano');
    }

    /** @test */
    public function admin_cannot_search_logs_by_year_without_tipo()
    {
        $data = Carbon::today()->format('Y');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'texto' => 'teste']))
        ->assertSessionHasErrors('tipo');
    }

    /** @test */
    public function admin_cannot_search_logs_by_year_without_texto()
    {
        $data = Carbon::today()->format('Y');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'externo']))
        ->assertSessionHasErrors('texto');
    }

    /** 
     * =======================================================================================================
     * TESTES GERENCIAR IP
     * =======================================================================================================
     */

    /** @test */
    public function admin_can_view_blocked_ips()
    {
        $block_ips = factory('App\SuporteIp', 3)->states('bloqueado')->create();
        $this->signInAsAdmin();

        $this->get(route('suporte.ips.view'))
        ->assertSee('<td>'.$block_ips->get(0)->ip.'</td>')
        ->assertSee('<td>'.$block_ips->get(1)->ip.'</td>')
        ->assertSee('<td>'.$block_ips->get(2)->ip.'</td>')
        ->assertSee('<td class="text-danger">'.$block_ips->get(0)->status.'</td>')
        ->assertSee('<td class="text-danger">'.$block_ips->get(1)->status.'</td>')
        ->assertSee('<td class="text-danger">'.$block_ips->get(2)->status.'</td>')
        ->assertSee('<form method="POST" action="' . route('suporte.ips.excluir', $block_ips->get(0)->ip).'" class="d-inline">')
        ->assertSee('<form method="POST" action="' . route('suporte.ips.excluir', $block_ips->get(1)->ip).'" class="d-inline">')
        ->assertSee('<form method="POST" action="' . route('suporte.ips.excluir', $block_ips->get(2)->ip).'" class="d-inline">');
    }

    /** @test */
    public function admin_can_view_free_ips()
    {
        $ips = factory('App\SuporteIp', 3)->states('liberado')->create();
        $this->signInAsAdmin();

        $this->get(route('suporte.ips.view'))
        ->assertSee('<td>'.$ips->get(0)->ip.'</td>')
        ->assertSee('<td>'.$ips->get(1)->ip.'</td>')
        ->assertSee('<td>'.$ips->get(2)->ip.'</td>')
        ->assertSee('<td class="text-success">'.$ips->get(0)->status.'</td>')
        ->assertSee('<td class="text-success">'.$ips->get(1)->status.'</td>')
        ->assertSee('<td class="text-success">'.$ips->get(2)->status.'</td>')
        ->assertSeeText('Exclusão somente via SSH');
    }

    /** @test */
    public function admin_cannot_view_unblocked_ips()
    {
        $ips = factory('App\SuporteIp', 3)->create();
        $this->signInAsAdmin();

        $this->get(route('suporte.ips.view'))
        ->assertDontSee('<td>'.$ips->get(0)->ip.'</td>')
        ->assertDontSee('<td>'.$ips->get(1)->ip.'</td>')
        ->assertDontSee('<td>'.$ips->get(2)->ip.'</td>')
        ->assertDontSee('<td class="text-danger">'.$ips->get(0)->status.'</td>')
        ->assertDontSee('<td class="text-danger">'.$ips->get(1)->status.'</td>')
        ->assertDontSee('<td class="text-danger">'.$ips->get(2)->status.'</td>')
        ->assertDontSee('<form method="POST" action="' . route('suporte.ips.excluir', $ips->get(0)->ip).'" class="d-inline">')
        ->assertDontSee('<form method="POST" action="' . route('suporte.ips.excluir', $ips->get(1)->ip).'" class="d-inline">')
        ->assertDontSee('<form method="POST" action="' . route('suporte.ips.excluir', $ips->get(2)->ip).'" class="d-inline">');
    }

    /** @test */
    public function admin_cannot_delete_free_ips()
    {
        $ip = factory('App\SuporteIp')->states('liberado')->create();
        $this->signInAsAdmin();

        $this->get(route('suporte.ips.view'))
        ->assertSee('<td>'.$ip->ip.'</td>');

        $this->delete(route('suporte.ips.excluir', $ip->ip))
        ->assertStatus(302);

        $this->get(route('suporte.ips.view'))
        ->assertSee('<td>'.$ip->ip.'</td>');

        $this->assertEquals(1, SuporteIp::count());
        $this->assertDatabaseHas('suporte_ips', [
            'ip' => $ip->ip,
        ]);
    }

    /** @test */
    public function admin_cannot_delete_unblocked_ips()
    {
        $ip = factory('App\SuporteIp')->create();
        $this->signInAsAdmin();

        $this->get(route('suporte.ips.view'))
        ->assertDontSee('<td>'.$ip->ip.'</td>');

        $this->delete(route('suporte.ips.excluir', $ip->ip))
        ->assertStatus(302);

        $this->get(route('suporte.ips.view'))
        ->assertDontSee('<td>'.$ip->ip.'</td>');

        $this->assertEquals(1, SuporteIp::count());
        $this->assertDatabaseHas('suporte_ips', [
            'ip' => $ip->ip,
        ]);
    }

    /** @test */
    public function admin_can_delete_blocked_ips()
    {
        Mail::fake();

        $users[2] = $this->signInAsAdmin();

        $ip = factory('App\SuporteIp')->states('bloqueado')->create();

        $users[0] = factory('App\User')->create([
            'nome' => 'Teste dois',
            'idperfil' => 1,
        ]);
        $users[1] = factory('App\User')->create([
            'nome' => 'Teste tres',
            'idperfil' => 1
        ]);

        $this->get(route('suporte.ips.view'))
        ->assertSee('<td>'.$ip->ip.'</td>');

        $this->delete(route('suporte.ips.excluir', $ip->ip))
        ->assertStatus(302);

        Mail::assertQueued(InternoSuporteMail::class, count($users), function ($mail) use ($users) {
            return strpos($mail->body, $users[2]->nome) !== false;
        });

        $this->get(route('suporte.ips.view'))
        ->assertDontSee('<td>'.$ip->ip.'</td>');

        $this->assertNotEquals(1, SuporteIp::count());
        $this->assertDatabaseMissing('suporte_ips', [
            'ip' => $ip->ip,
        ]);
    }

    /** @test */
    public function log_is_generated_when_admin_delete_blocked_ip()
    {
        $ip = factory('App\SuporteIp')->states('bloqueado')->create();
        $user = $this->signInAsAdmin();

        $this->delete(route('suporte.ips.excluir', $ip->ip));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.$ip->ip.'] - ';
        $texto .= "IP DESBLOQUEADO por " . $user->nome . " (administrador do Portal) após análise.";
        $this->assertStringContainsString($texto, $log);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($texto, $log);
    }

    /** @test */
    public function blocked_ip_after_6_submits()
    {
        Mail::fake();

        factory('App\User')->create();

        for($i = 1; $i <= 7; $i++)
        {
            $this->post('admin/login', ['login' => 'teste', 'username' => 'teste', 'password' => 'TestePorta1']);
            session()->regenerateToken();
        }

        Mail::assertQueued(InternoSuporteMail::class);

        $this->get(route('site.home'))->assertStatus(423);
        $this->get(route('admin'))->assertStatus(423);
        $this->get(route('representante.login'))->assertStatus(423);
        $this->get(route('representante.dashboard'))->assertStatus(423);
        $this->get(route('login'))->assertStatus(423);
        $this->get(route('agendamentosite.formview'))->assertStatus(423);

        $this->assertEquals(1, SuporteIp::count());
        $this->assertDatabaseHas('suporte_ips', [
            'ip' => request()->ip(),
            'status' => 'BLOQUEADO'
        ]);
    }

    /** @test */
    public function log_is_generated_when_blocked_ip()
    {
        for($i = 1; $i <= 7; $i++)
        {
            $this->post('admin/login', ['login' => 'teste', 'username' => 'teste', 'password' => 'TestePorta1']);
            session()->regenerateToken();
        }

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - [Rotina Portal - Bloqueio de IP] - ';
        $texto .= "IP BLOQUEADO por segurança devido a alcançar o limite de " . SuporteIp::TOTAL_TENTATIVAS . " tentativas de login.";
        $this->assertStringContainsString($texto, $log);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($texto, $log);
    }

    /** @test */
    public function cannot_to_block_free_ip_after_6_submits()
    {
        factory('App\SuporteIp')->states('liberado')->create([
            'ip' => '127.0.0.1'
        ]);

        for($i = 1; $i <= 7; $i++)
        {
            $this->post('admin/login', ['login' => 'teste', 'username' => 'teste', 'password' => 'TestePorta1']);
            session()->regenerateToken();
        }

        $this->get(route('site.home'))->assertStatus(200);
        $this->get(route('admin'))->assertStatus(302);
        $this->get(route('representante.login'))->assertStatus(200);
        $this->get(route('representante.dashboard'))->assertStatus(302);
        $this->get(route('login'))->assertStatus(200);

        $this->assertEquals(1, SuporteIp::count());
        $this->assertDatabaseHas('suporte_ips', [
            'ip' => request()->ip(),
            'status' => 'LIBERADO',
            'tentativas' => 0
        ]);
    }

    /** @test */
    public function delete_unblocked_ip_after_login_user()
    {
        for($i = 1; $i <= 5; $i++)
        {
            $this->post('admin/login', ['login' => 'teste', 'username' => 'teste', 'password' => 'TestePorta1']);
            session()->regenerateToken();
        }

        $this->assertEquals(1, SuporteIp::count());
        $this->assertDatabaseHas('suporte_ips', [
            'ip' => request()->ip(),
            'status' => 'DESBLOQUEADO'
        ]);

        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);

        $this->post('admin/login', ['login' => $user->email, 'password' => 'TestePorta1@'])
        ->assertRedirect(route('admin'));

        $this->assertEquals(0, SuporteIp::count());
        $this->assertDatabaseMissing('suporte_ips', [
            'ip' => request()->ip(),
            'status' => 'DESBLOQUEADO'
        ]);
    }

    /** @test */
    public function delete_unblocked_ip_after_login_representante()
    {
        for($i = 1; $i <= 5; $i++)
        {
            $this->post(route('representante.login.submit'), ['cpf_cnpj' => '11748345000144', 'password' => 'teste102030']);
            session()->regenerateToken();
        }

        $this->assertEquals(1, SuporteIp::count());
        $this->assertDatabaseHas('suporte_ips', [
            'ip' => request()->ip(),
            'status' => 'DESBLOQUEADO'
        ]);

        $representante = factory('App\Representante')->create();

        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030'])
        ->assertRedirect(route('representante.dashboard'));

        $this->assertEquals(0, SuporteIp::count());
        $this->assertDatabaseMissing('suporte_ips', [
            'ip' => request()->ip(),
            'status' => 'DESBLOQUEADO'
        ]);
    }

    /** @test */
    public function recount_unblocked_ip_when_submit_day_after()
    {
        for($i = 1; $i <= 5; $i++)
        {
            $this->post('admin/login', ['login' => 'teste', 'username' => 'teste', 'password' => 'TestePorta1']);
            session()->regenerateToken();
        }

        $this->assertDatabaseHas('suporte_ips', [
            'ip' => request()->ip(),
            'status' => 'DESBLOQUEADO',
            'tentativas' => 5
        ]);

        SuporteIp::first()->update(['updated_at' => now()->subDay()->format('Y-m-d H:i:s')]);

        $this->post('admin/login', ['login' => 'teste', 'username' => 'teste', 'password' => 'TestePorta1']);

        $this->assertDatabaseHas('suporte_ips', [
            'ip' => request()->ip(),
            'status' => 'DESBLOQUEADO',
            'tentativas' => 1
        ]);
    }

    /** @test */
    public function free_ip_can_get_routes()
    {
        factory('App\Regional')->create();
        $ip = factory('App\SuporteIp')->states('liberado')->create();

        $this->get(route('site.home'))->assertStatus(200);
        $this->get(route('admin'))->assertStatus(302);
        $this->get(route('representante.login'))->assertStatus(200);
        $this->get(route('representante.dashboard'))->assertStatus(302);
        $this->get(route('login'))->assertStatus(200);
        $this->get(route('agendamentosite.formview'))->assertStatus(200);
    }

    /** @test */
    public function unblocked_ip_can_get_routes()
    {
        factory('App\Regional')->create();
        $ip = factory('App\SuporteIp')->create([
            'tentativas' => 3
        ]);

        $this->get(route('site.home'))->assertStatus(200);
        $this->get(route('admin'))->assertStatus(302);
        $this->get(route('representante.login'))->assertStatus(200);
        $this->get(route('representante.dashboard'))->assertStatus(302);
        $this->get(route('login'))->assertStatus(200);
        $this->get(route('agendamentosite.formview'))->assertStatus(200);
    }

    /** @test */
    public function blocked_ips_array()
    {
        $ips = factory('App\SuporteIp', 5)->states('bloqueado')->create();
        $service = resolve('App\Contracts\MediadorServiceInterface');
        $ips_array = $service->getService('Suporte')->ipsBloqueados()->pluck('ip')->all();

        $this->assertEquals([
            $ips->get(0)->ip,
            $ips->get(1)->ip,
            $ips->get(2)->ip,
            $ips->get(3)->ip,
            $ips->get(4)->ip,
        ], $ips_array);
    }

    /** @test */
    public function error_500_when_without_suporte_ips_table()
    {
        \Illuminate\Support\Facades\DB::statement('drop table suporte_ips');
        $this->get(route('site.home'))->assertStatus(500)->assertSeeText('Erro interno! Tente novamente mais tarde.');
        $this->get(route('admin'))->assertStatus(500)->assertSeeText('Erro interno! Tente novamente mais tarde.');
        $this->get(route('representante.login'))->assertStatus(500)->assertSeeText('Erro interno! Tente novamente mais tarde.');
        $this->get(route('representante.dashboard'))->assertStatus(500)->assertSeeText('Erro interno! Tente novamente mais tarde.');
        $this->get(route('login'))->assertStatus(500)->assertSeeText('Erro interno! Tente novamente mais tarde.');
    }

    /** 
     * =======================================================================================================
     * TESTES MANUAL
     * =======================================================================================================
    */

    /** @test */
    public function user_can_access_manual()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))->assertOk();
    }

    /** @test */
    public function user_can_see_tabs()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<button class="btn btn-primary btn-block" data-toggle="collapse" data-target="#basico">Funções Básicas <small>(Admin, Representante)</small></button>')
        ->assertSee('<button class="btn btn-primary btn-block" data-toggle="collapse" data-target="#area_rep">Área do Representante</button>')
        ->assertSee('<button class="btn btn-primary btn-block" data-toggle="collapse" data-target="#duvidas_frequentes">Dúvidas Frequentes</button>');
    }

    /** @test */
    public function user_can_see_text_update()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<strong>Última atualização:</strong>');
    }

    /** @test */
    public function user_can_see_content_in_funcoes_basicas_tab()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<p class="font-weight-bolder">Admin - Menus</p>')
        ->assertSee('<p class="font-weight-bolder">Admin - Home</p>')
        ->assertSee('<p class="font-weight-bolder">Admin - Perfil</p>')
        ->assertSee('<p class="font-weight-bolder">Admin - Abrir Chamados</p>')
        ->assertSee('<p class="font-weight-bolder">Admin - Perfil pelo menu vertical</p>')
        ->assertSee('<p class="font-weight-bolder">Admin - Alterar senha</p>')
        ->assertSee('<p class="font-weight-bolder">Admin - Desconectar</p>')
        ->assertSee('<p class="font-weight-bolder">Representante - Cadastro</p>')
        ->assertSee('<p class="font-weight-bolder">Representante - Alterar senha</p>')
        ->assertSee('<p class="font-weight-bolder">Representante - Alterar e-mail</p>')
        ->assertSee('<p class="font-weight-bolder">Representante - Desconectar</p>');
    }

    /** @test */
    public function user_can_see_content_in_area_representante_tab()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<p class="font-weight-bolder">Aba - Home</p>')
        ->assertSee('<p class="font-weight-bolder">Aba - Dados Gerais</p>')
        ->assertSee('<p class="font-weight-bolder">Aba - Contatos</p>')
        ->assertSee('<p class="font-weight-bolder">Aba - Contatos > Inserir Contato</p>')
        ->assertSee('<p class="font-weight-bolder">Aba - End. de Correspondência</p>')
        ->assertSee('<p class="font-weight-bolder">Aba - End. de Correspondência > Inserir Endereço</p>')
        ->assertSee('<p class="font-weight-bolder">Aba - Situação Financeira</p>')
        ->assertSee('<p class="font-weight-bolder">Aba - Emitir Certidão</p>')
        ->assertSee('<p class="font-weight-bolder">Aba - Oportunidades</p>')
        ->assertSee('<p class="font-weight-bolder">Aba - Solicitação de Cédula</p>')
        ->assertSee('<p class="font-weight-bolder">Aba - Solicitação de Cédula > Solicitar Cédula</p>');
    }

    /** @test */
    public function user_can_see_content_in_duvidas_frequentes_tab()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<p class="font-weight-bolder">Representante com agendamento bloqueado</p>')
        ->assertSee('<p class="font-weight-bolder">Representante não consegue fazer login - Caso 1</p>')
        ->assertSee('<p class="font-weight-bolder">Representante não consegue fazer login - Caso 2</p>')
        ->assertSee('<p class="font-weight-bolder">Representante não consegue fazer login - Caso 3</p>')
        ->assertSee('<p class="font-weight-bolder">Representante não consegue alterar a senha</p>')
        ->assertSee('<p class="font-weight-bolder">Representante não consegue alterar o e-mail - Caso 1</p>')
        ->assertSee('<p class="font-weight-bolder">Representante não consegue alterar o e-mail - Caso 2</p>');
    }
}
