<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\SuporteIp;
use App\Suporte;
use Illuminate\Support\Facades\Mail;
use App\Mail\InternoSuporteMail;
use PermissoesTableSeeder;
use App\Permissao;

class SuporteTest extends TestCase
{
    use RefreshDatabase;
    private $tg;
    private $td;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
                
        $this->get(route('suporte.log.externo.index'))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.hoje.view', 'interno'))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.busca'))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.view', ['data' => date('Y-m-d'), 'tipo' => 'interno']))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.download', ['data' => date('Y-m-d'), 'tipo' => 'interno']))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.relatorios'))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => 'teste', 'acao' => 'visualizar']))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.relatorios.final'))->assertRedirect(route('login'));
        $this->get(route('suporte.ips.view'))->assertRedirect(route('login'));
        $this->delete(route('suporte.ips.excluir', request()->ip()))->assertRedirect(route('login'));
        $this->get(route('admin.manual'))->assertRedirect(route('login'));
        $this->get(route('admin.manual', 'teste.gif'))->assertRedirect(route('login'));
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
        $this->get(route('suporte.log.externo.relatorios'))->assertForbidden();
        $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => 'teste', 'acao' => 'visualizar']))->assertForbidden();
        $this->get(route('suporte.log.externo.relatorios.final'))->assertForbidden();
        $this->get(route('suporte.ips.view'))->assertForbidden();
        $this->delete(route('suporte.ips.excluir', request()->ip()))->assertForbidden();
    }

    /** 
     * =======================================================================================================
     * TESTES BUSCAS LOG
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
        ->assertSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'interno', 'texto' => 'info']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));
    }

    /** @test */
    public function admin_can_search_logs_by_month_with_lines()
    {
        $data = Carbon::today()->subMonth();
        $ano = $data->format('Y');
        $data = $data->format('Y-m');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'externo', 'texto' => $ano, 'n_linhas' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertDontSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'interno', 'texto' => 'info', 'n_linhas' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertDontSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));
    }

    /** @test */
    public function admin_can_search_logs_by_month_with_distintos()
    {
        $data = Carbon::today()->subMonth();
        $ano = $data->format('Y');
        $data = $data->format('Y-m');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'externo', 'texto' => $ano, 'distintos' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências distintas:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'interno', 'texto' => 'info', 'distintos' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências distintas:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));
    }

    /** @test */
    public function admin_can_search_logs_by_month_with_lines_and_distintos()
    {
        $data = Carbon::today()->subMonth();
        $ano = $data->format('Y');
        $data = $data->format('Y-m');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'externo', 'texto' => $ano, 'distintos' => 'on', 'n_linhas' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertDontSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências distintas:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['mes' => $data, 'tipo' => 'interno', 'texto' => 'info', 'distintos' => 'on', 'n_linhas' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertDontSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências distintas:');

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

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'externo', 'texto' => '[IP:']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'interno', 'texto' => 'info']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));
    }

    /** @test */
    public function admin_can_search_logs_by_year_with_lines()
    {
        $data = Carbon::today()->format('Y');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'externo', 'texto' => '[IP:', 'n_linhas' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertDontSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'interno', 'texto' => 'info', 'n_linhas' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertDontSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));
    }

    /** @test */
    public function admin_can_search_logs_by_year_with_distintos()
    {
        $data = Carbon::today()->format('Y');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'externo', 'texto' => '[IP:', 'distintos' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências distintas:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'interno', 'texto' => 'info', 'distintos' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências distintas:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));
    }

    /** @test */
    public function admin_can_search_logs_by_year_with_lines_and_distintos()
    {
        $data = Carbon::today()->format('Y');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'externo', 'texto' => '[IP:', 'distintos' => 'on', 'n_linhas' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertDontSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências distintas:');

        $this->assertEquals(Cache::get('request_busca_log_'.auth()->id()), request()->except(['page', '_token']));

        $this->get(route('suporte.log.externo.busca', ['ano' => $data, 'tipo' => 'interno', 'texto' => 'info', 'distintos' => 'on', 'n_linhas' => 'on']))
        ->assertSee('<th>Nome do Log</th>')
        ->assertSee('<th>Tamanho em KB</th>')
        ->assertSee('<th>Total de ocorrências</th>')
        ->assertDontSee('<td>-----</td>')
        ->assertSee('<th>Ações</th>')
        ->assertSee('Total de ocorrências distintas:');

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
     * TESTES RELATÓRIOS
     * =======================================================================================================
     */
    
    private function gerar_log_acessos_rc()
    {
        $this->tg = array_combine(array_keys(Suporte::filtros()), array_fill(0, count(Suporte::filtros()), 0));
        ++$this->tg[Suporte::FILTRO_ACESSO];

        $this->td = array_combine(array_keys(Suporte::filtros()), array_fill(0, count(Suporte::filtros()), 0));
        ++$this->td[Suporte::FILTRO_ACESSO];
        ++$this->td[Suporte::FILTRO_ABA_TODAS];

        // exige ao acessar a aba bdo
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO',
        ]);

        $representante = factory('App\Representante')->create();

        for($j=0; $j < 10; $j++)
        {
            $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
            $this->get(route('representante.dados-gerais'))->assertOk();
            ++$this->tg[Suporte::FILTRO_ABA_DADOS];
            ++$this->tg[Suporte::FILTRO_ABA_TODAS];
            $this->td[Suporte::FILTRO_ABA_DADOS] = 1;

            $this->get(route('representante.dashboard'))->assertOk();
            ++$this->tg[Suporte::FILTRO_ABA_HOME];
            ++$this->tg[Suporte::FILTRO_ABA_TODAS];
            $this->td[Suporte::FILTRO_ABA_HOME] = 1;

            $this->get(route('representante.contatos.view'))->assertOk();
            ++$this->tg[Suporte::FILTRO_ABA_CONTATOS];
            ++$this->tg[Suporte::FILTRO_ABA_TODAS];
            $this->td[Suporte::FILTRO_ABA_CONTATOS] = 1;

            $this->get(route('representante.enderecos.view'))->assertOk();
            ++$this->tg[Suporte::FILTRO_ABA_ENDER];
            ++$this->tg[Suporte::FILTRO_ABA_TODAS];
            $this->td[Suporte::FILTRO_ABA_ENDER] = 1;

            if($j > 3){
                $this->get(route('representante.bdo'))->assertOk();
                ++$this->tg[Suporte::FILTRO_ABA_BDO];
                ++$this->tg[Suporte::FILTRO_ABA_TODAS];
                $this->td[Suporte::FILTRO_ABA_BDO] = 1;
            }

            $this->get(route('representante.solicitarCedulaView'))->assertOk();
            ++$this->tg[Suporte::FILTRO_ABA_CEDULA];
            ++$this->tg[Suporte::FILTRO_ABA_TODAS];
            $this->td[Suporte::FILTRO_ABA_CEDULA] = 1;

            $this->get(route('representante.agendar.inserir.view'))->assertOk();
            ++$this->tg[Suporte::FILTRO_ABA_SALAS];
            ++$this->tg[Suporte::FILTRO_ABA_TODAS];
            $this->td[Suporte::FILTRO_ABA_SALAS] = 1;

            if($j > 7){
                $this->get(route('representante.cursos'))->assertOk();
                ++$this->tg[Suporte::FILTRO_ABA_CURSOS];
                ++$this->tg[Suporte::FILTRO_ABA_TODAS];
                $this->td[Suporte::FILTRO_ABA_CURSOS] = 1;
            }
            if($j > 5)
            {
                $this->get(route('representante.lista-cobrancas'))->assertOk();
                ++$this->tg[Suporte::FILTRO_ABA_FINANCA];
                ++$this->tg[Suporte::FILTRO_ABA_TODAS];
                $this->td[Suporte::FILTRO_ABA_FINANCA] = 1;
            }

            $this->post(route('representante.logout'));
        }

        $this->tg['relatorio_final'] = collect($this->tg)->sum();
        $this->td['relatorio_final'] = collect($this->td)->sum();
    }

    private function gerar_log_acessos_admin()
    {
        $this->tg = [Suporte::FILTRO_ACESSO => 1];
        $this->td = [Suporte::FILTRO_ACESSO => 1];

        $this->seed(PermissoesTableSeeder::class);
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);

        for($i=0; $i < 10; $i++)
        {
            $this->post('admin/login', ['login' => $user->username, 'password' => 'TestePorta1@']);
            $this->get(route('regionais.index'))->assertOk();
            $this->post(route('logout'));
        }

        $this->tg['relatorio_final'] = collect($this->tg)->sum();
        $this->td['relatorio_final'] = collect($this->td)->sum();
    }

    /** @test */
    public function admin_can_to_create_report()
    {
        $this->signInAsAdmin();

        $relat = 'relatorio_'.now()->format('Y-m').'-externo-'.Suporte::FILTRO_ACESSO;
        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'externo', 'relat_data' => 'mes', 'relat_mes' => now()->format('Y-m'), 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']));

        $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
        ->assertSee('<title>Core-SP — Relatório</title>')
        ->assertSee('<h2>Relatório do Portal via Log</h2>')
        ->assertSee('<table class="table">')
        ->assertSee('<a class="btn btn-primary" href="'.route('suporte.log.externo.index').'">Voltar</a>')
        ->assertSessionHas($relat);
    }

    /** @test */
    public function admin_can_to_create_report_by_month()
    {
        $this->signInAsAdmin();

        foreach(Suporte::filtros() as $filtro => $f)
        {
            $relat = 'relatorio_'.now()->format('Y-m').'-externo-'.$filtro;
            $this->get(route('suporte.log.externo.relatorios', [
                'relat_tipo' => 'externo', 'relat_data' => 'mes', 'relat_mes' => now()->format('Y-m'), 'relat_opcoes' => $filtro
            ]))
            ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
            ->assertSessionHas($relat);
        }

        $relat = 'relatorio_'.now()->format('Y-m').'-interno-'.Suporte::FILTRO_ACESSO;
        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'interno', 'relat_data' => 'mes', 'relat_mes' => now()->format('Y-m'), 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
        ->assertSessionHas($relat);
    }

    /** @test */
    public function admin_can_to_create_report_by_year()
    {
        $this->signInAsAdmin();

        foreach(Suporte::filtros() as $filtro => $f)
        {
            $relat = 'relatorio_'.now()->format('Y').'-externo-'.$filtro;
            $this->get(route('suporte.log.externo.relatorios', [
                'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => $filtro
            ]))
            ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
            ->assertSessionHas($relat);
        }

        $relat = 'relatorio_'.now()->format('Y').'-interno-'.Suporte::FILTRO_ACESSO;
        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'interno', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
        ->assertSessionHas($relat);
    }

    /** @test */
    public function admin_can_to_create_final_report()
    {
        $this->signInAsAdmin();

        foreach(Suporte::filtros() as $filtro => $f)
        {
            $relat = 'relatorio_'.now()->format('Y').'-externo-'.$filtro;
            $this->get(route('suporte.log.externo.relatorios', [
                'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => $filtro
            ]))
            ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
            ->assertSessionHas($relat);
        }

        $this->get(route('suporte.log.externo.relatorios.final'))
        ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => 'relatorio_final', 'acao' => 'visualizar']))
        ->assertSessionHas('relatorio_final');

        session()->forget('relatorio_final');

        $relat = 'relatorio_'.now()->format('Y').'-interno-'.Suporte::FILTRO_ACESSO;
        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'interno', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
        ->assertSessionHas($relat)
        ->assertSessionMissing('relatorio_final');

        $this->get(route('suporte.log.externo.relatorios.final'))
        ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => 'relatorio_final', 'acao' => 'visualizar']))
        ->assertSessionHas('relatorio_final');
    }

    /** @test */
    public function admin_cannot_to_create_report_without_tipo()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => '', 'relat_data' => 'mes', 'relat_mes' => now()->format('Y-m'), 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertSessionHasErrors('relat_tipo');
    }

    /** @test */
    public function admin_cannot_to_create_report_with_tipo_invalid()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'teste', 'relat_data' => 'mes', 'relat_mes' => now()->format('Y-m'), 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertSessionHasErrors('relat_tipo');
    }

    /** @test */
    public function admin_cannot_to_create_report_without_data()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'externo', 'relat_data' => '', 'relat_mes' => now()->format('Y-m'), 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertSessionHasErrors('relat_data');
    }

    /** @test */
    public function admin_cannot_to_create_report_with_data_invalid()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'externo', 'relat_data' => 'me', 'relat_mes' => now()->format('Y-m'), 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertSessionHasErrors('relat_data');
    }

    /** @test */
    public function admin_cannot_to_create_report_without_mes_if_data_mes()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'externo', 'relat_data' => 'mes', 'relat_mes' => '', 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertSessionHasErrors('relat_mes');
    }

    /** @test */
    public function admin_cannot_to_create_report_with_mes_invalid_if_data_mes()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'externo', 'relat_data' => 'mes', 'relat_mes' => '2024', 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertSessionHasErrors('relat_mes');
    }

    /** @test */
    public function admin_cannot_to_create_report_with_mes_after_now_if_data_mes()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'externo', 'relat_data' => 'mes', 'relat_mes' => now()->addMonth()->format('Y-m'), 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertSessionHasErrors('relat_mes');
    }

    /** @test */
    public function admin_cannot_to_create_report_with_mes_before_2019_if_data_mes()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'externo', 'relat_data' => 'mes', 'relat_mes' => '2018-12', 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertSessionHasErrors('relat_mes');
    }

    /** @test */
    public function admin_cannot_to_create_report_without_ano_if_data_ano()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => '', 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertSessionHasErrors('relat_ano');
    }

    /** @test */
    public function admin_cannot_to_create_report_with_ano_invalid_if_data_ano()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => 'an', 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertSessionHasErrors('relat_ano');
    }

    /** @test */
    public function admin_cannot_to_create_report_with_ano_after_now_if_data_ano()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => '2025', 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertSessionHasErrors('relat_ano');
    }

    /** @test */
    public function admin_cannot_to_create_report_with_ano_before_2019_if_data_ano()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => '2018', 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertSessionHasErrors('relat_ano');
    }

    /** @test */
    public function admin_cannot_to_create_report_without_opcoes()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => ''
        ]))
        ->assertSessionHasErrors('relat_opcoes');
    }

    /** @test */
    public function admin_cannot_to_create_report_with_opcoes_invalid()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => 'teste'
        ]))
        ->assertSessionHasErrors('relat_opcoes');
    }

    /** @test */
    public function admin_can_to_view_report()
    {
        $suporte = new Suporte();
        $this->signInAsAdmin();

        foreach(Suporte::filtros() as $filtro => $f)
        {
            $relat = 'relatorio_'.now()->format('Y').'-externo-'.$filtro;
            $this->get(route('suporte.log.externo.relatorios', [
                'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => $filtro
            ]))
            ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
            ->assertSessionHas($relat, $suporte->getRelatorioPorNome($relat));

            $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
            ->assertSee('<td>Site</td>')
            ->assertSee('<td>'.$f.'</td>')
            ->assertSee('<td>'.now()->format('Y').'</td>')
            ->assertSee('<td><small>'.now()->format('d\/m\/Y, \à\s H:i').'</small></td>')
            ->assertSee('<a class="btn btn-success float-right" href="'.route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'exportar-csv']).'">Exportar .csv</a>');

            $this->get(route('suporte.log.externo.index'))
            ->assertSee('<p><strong>Relatórios salvos temporariamente:</strong></p>')
            ->assertSee('<span class="text-nowrap">')
            ->assertSee($suporte->getTituloPorNome($relat));
        }

        $relat = 'relatorio_'.now()->format('Y').'-interno-'.Suporte::FILTRO_ACESSO;
        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'interno', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
        ->assertSessionHas($relat, $suporte->getRelatorioPorNome($relat));

        $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
        ->assertSee('<td>Admin</td>')
        ->assertSee('<td>'.Suporte::filtros()[Suporte::FILTRO_ACESSO].'</td>')
        ->assertSee('<td>'.now()->format('Y').'</td>')
        ->assertSee('<td><small>'.now()->format('d\/m\/Y, \à\s H:i').'</small></td>')
        ->assertSee('<a class="btn btn-success float-right" href="'.route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'exportar-csv']).'">Exportar .csv</a>');

        $this->get(route('suporte.log.externo.index'))
        ->assertSee('<p><strong>Relatórios salvos temporariamente:</strong></p>')
        ->assertSee('<span class="text-nowrap">')
        ->assertSee($suporte->getTituloPorNome($relat));
    }

    /** @test */
    public function admin_can_to_view_final_report()
    {
        $suporte = new Suporte();
        $this->signInAsAdmin();

        foreach(Suporte::filtros() as $filtro => $f)
        {
            $relat = 'relatorio_'.now()->format('Y').'-externo-'.$filtro;
            $this->get(route('suporte.log.externo.relatorios', [
                'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => $filtro
            ]));
        }

        $this->get(route('suporte.log.externo.relatorios.final'))
        ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => 'relatorio_final', 'acao' => 'visualizar']))
        ->assertSessionHas('relatorio_final', $suporte->getRelatorioPorNome('relatorio_final'));

        $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => 'relatorio_final', 'acao' => 'visualizar']))
        ->assertSee('<td class="border border-left-0 border-right-0 border-bottom-0 text-white">-----</td>')
        ->assertSee('<td class="border border-left-0 border-right-0 border-bottom-0 text-white">-----</td>')
        ->assertSee('<td class="font-weight-bolder">Total Final</td>')
        ->assertSee('<td><small>'.now()->format('d\/m\/Y, \à\s H:i').'</small></td>')
        ->assertSee('<a class="btn btn-success float-right" href="'.route('suporte.log.externo.relatorios.acoes', ['relat' => 'relatorio_final', 'acao' => 'exportar-csv']).'">Exportar .csv</a>');

        $this->get(route('suporte.log.externo.index'))
        ->assertSee('<p><strong>Relatórios salvos temporariamente:</strong></p>')
        ->assertSee('<span class="text-nowrap">')
        ->assertSee($suporte->getTituloPorNome('relatorio_final'));
    }

    /** @test */
    public function admin_cannot_to_view_without_report()
    {
        $this->signInAsAdmin();

        $relat = 'relatorio_'.now()->format('Y').'-interno-'.Suporte::FILTRO_ACESSO;

        $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
        ->assertRedirect(route('suporte.log.externo.index'))
        ->assertSessionHas('message', 'Relatório não existe para visualizar / exportar.csv.');
    }

    /** @test */
    public function admin_can_to_remove_report()
    {
        $suporte = new Suporte();
        $this->signInAsAdmin();

        foreach(Suporte::filtros() as $filtro => $f)
        {
            $relat = 'relatorio_'.now()->format('Y').'-externo-'.$filtro;
            $this->get(route('suporte.log.externo.relatorios', [
                'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => $filtro
            ]))
            ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
            ->assertSessionHas($relat, $suporte->getRelatorioPorNome($relat));
            $temp = $suporte->getTituloPorNome($relat);

            $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'remover']))
            ->assertRedirect(route('suporte.log.externo.index'))
            ->assertSessionHas('relat_removido', $relat)
            ->assertSessionMissing($relat);

            $this->get(route('suporte.log.externo.index'))
            ->assertSee('<div class="toast bg-warning">')
            ->assertSee('<div class="toast-body text-danger text-center">')
            ->assertSee('<strong>Relatório removido!</strong>')
            ->assertDontSee('<p><strong>Relatórios salvos temporariamente:</strong></p>')
            ->assertDontSee('<span class="text-nowrap">')
            ->assertDontSee($temp);
        }

        $relat = 'relatorio_'.now()->format('Y').'-interno-'.Suporte::FILTRO_ACESSO;
        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'interno', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
        ->assertSessionHas($relat, $suporte->getRelatorioPorNome($relat));
        $temp = $suporte->getTituloPorNome($relat);

        $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'remover']))
        ->assertRedirect(route('suporte.log.externo.index'))
        ->assertSessionHas('relat_removido', $relat)
        ->assertSessionMissing($relat);

        $this->get(route('suporte.log.externo.index'))
        ->assertSee('<div class="toast bg-warning">')
        ->assertSee('<div class="toast-body text-danger text-center">')
        ->assertSee('<strong>Relatório removido!</strong>')
        ->assertDontSee('<p><strong>Relatórios salvos temporariamente:</strong></p>')
        ->assertDontSee('<span class="text-nowrap">')
        ->assertDontSee($temp);
    }

    /** @test */
    public function admin_can_to_remove_final_report()
    {
        $suporte = new Suporte();
        $this->signInAsAdmin();

        foreach(Suporte::filtros() as $filtro => $f)
        {
            $relat = 'relatorio_'.now()->format('Y').'-externo-'.$filtro;
            $this->get(route('suporte.log.externo.relatorios', [
                'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => $filtro
            ]));
        }

        $this->get(route('suporte.log.externo.relatorios.final'))
        ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => 'relatorio_final', 'acao' => 'visualizar']))
        ->assertSessionHas('relatorio_final', $suporte->getRelatorioPorNome('relatorio_final'));

        $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => 'relatorio_final', 'acao' => 'remover']))
        ->assertRedirect(route('suporte.log.externo.index'))
        ->assertSessionHas('relat_removido', 'relatorio_final')
        ->assertSessionMissing('relatorio_final');

        $this->get(route('suporte.log.externo.index'))
        ->assertSee('<div class="toast bg-warning">')
        ->assertSee('<div class="toast-body text-danger text-center">')
        ->assertSee('<strong>Relatório removido!</strong>')
        ->assertSee('<p><strong>Relatórios salvos temporariamente:</strong></p>')
        ->assertSee('<span class="text-nowrap">')
        ->assertDontSee('relatorio_final');
    }

    /** @test */
    public function admin_cannot_to_remove_without_report()
    {
        $this->signInAsAdmin();

        $relat = 'relatorio_'.now()->format('Y').'-interno-'.Suporte::FILTRO_ACESSO;
        
        $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'remover']))
        ->assertRedirect(route('suporte.log.externo.index'))
        ->assertSessionHas('message', 'Relatório não existe para remover');
    }

    /** @test */
    public function admin_can_to_export_report()
    {
        $suporte = new Suporte();
        $this->signInAsAdmin();

        foreach(Suporte::filtros() as $filtro => $f)
        {
            $relat = 'relatorio_'.now()->format('Y').'-externo-'.$filtro;
            $this->get(route('suporte.log.externo.relatorios', [
                'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => $filtro
            ]))
            ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
            ->assertSessionHas($relat, $suporte->getRelatorioPorNome($relat));

            $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'exportar-csv']))
            ->assertHeader('Content-Disposition', 'attachment; filename='.$relat.'-'.date('Ymd').'.csv');

            $csv = $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'exportar-csv']))
            ->streamedContent();

            $tg = $suporte->getRelatorioPorNome($relat)['relatorio']['geral'];
            $td = $suporte->getRelatorioPorNome($relat)['relatorio']['distintos'];

            $this->assertStringContainsString('Área;Filtro;Período;"Total geral";"Total distintos";"Gerado em"', $csv);
            $this->assertStringContainsString('Site;"'.$f.'";'.now()->format('Y').';'.$tg.';'.$td.';"'.now()->format('d\/m\/Y, \à\s H:i'), $csv);
        }

        $relat = 'relatorio_'.now()->format('Y').'-interno-'.Suporte::FILTRO_ACESSO;
        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'interno', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]))
        ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']))
        ->assertSessionHas($relat, $suporte->getRelatorioPorNome($relat));

        $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'exportar-csv']))
        ->assertHeader('Content-Disposition', 'attachment; filename='.$relat.'-'.date('Ymd').'.csv');

        $csv = $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'exportar-csv']))
        ->streamedContent();

        $tg = $suporte->getRelatorioPorNome($relat)['relatorio']['geral'];
        $td = $suporte->getRelatorioPorNome($relat)['relatorio']['distintos'];

        $this->assertStringContainsString('Área;Filtro;Período;"Total geral";"Total distintos";"Gerado em"', $csv);
        $this->assertStringContainsString('Admin;"'.Suporte::filtros()[Suporte::FILTRO_ACESSO].'";'.now()->format('Y').';'.$tg.';'.$td.';"'.now()->format('d\/m\/Y, \à\s H:i'), $csv);
    }

    /** @test */
    public function admin_can_to_export_final_report()
    {
        $suporte = new Suporte();
        $this->signInAsAdmin();

        foreach(Suporte::filtros() as $filtro => $f)
        {
            $relat = 'relatorio_'.now()->format('Y').'-externo-'.$filtro;
            $this->get(route('suporte.log.externo.relatorios', [
                'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => $filtro
            ]));
        }

        $this->get(route('suporte.log.externo.relatorios.final'))
        ->assertRedirect(route('suporte.log.externo.relatorios.acoes', ['relat' => 'relatorio_final', 'acao' => 'visualizar']))
        ->assertSessionHas('relatorio_final', $suporte->getRelatorioPorNome('relatorio_final'));

        $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => 'relatorio_final', 'acao' => 'exportar-csv']))
        ->assertHeader('Content-Disposition', 'attachment; filename=relatorio_final-'.date('Ymd').'.csv');

        $csv = $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => 'relatorio_final', 'acao' => 'exportar-csv']))
        ->streamedContent();

        $this->assertStringContainsString('Área;Filtro;Período;"Total geral";"Total distintos";"Gerado em"', $csv);
        $this->assertStringContainsString('-----;-----;"Total Final";', $csv);
    }

    /** @test */
    public function admin_cannot_to_export_without_report()
    {
        $this->signInAsAdmin();

        $relat = 'relatorio_'.now()->format('Y').'-interno-'.Suporte::FILTRO_ACESSO;
        
        $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'exportar-csv']))
        ->assertRedirect(route('suporte.log.externo.index'))
        ->assertSessionHas('message', 'Relatório não existe para visualizar / exportar.csv.');
    }

    /** @test */
    public function error_input_acao_invalid()
    {
        $this->signInAsAdmin();

        $relat = 'relatorio_'.now()->format('Y').'-interno-'.Suporte::FILTRO_ACESSO;
        
        $this->get(route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'exportar']))
        ->assertNotFound();
    }

    /** @test */
    public function remove_reports_after_logout()
    {
        $this->seed(PermissoesTableSeeder::class);
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);

        $this->post('admin/login', ['login' => $user->username, 'password' => 'TestePorta1@']);

        $relats = array();
        foreach(Suporte::filtros() as $filtro => $f)
        {
            $relat = 'relatorio_'.now()->format('Y').'-externo-'.$filtro;
            array_push($relats, $relat);
            $this->get(route('suporte.log.externo.relatorios', [
                'relat_tipo' => 'externo', 'relat_data' => 'ano', 'relat_ano' => now()->format('Y'), 'relat_opcoes' => $filtro
            ]));
        }

        $this->get(route('suporte.log.externo.relatorios.final'));
        array_push($relats, 'relatorio_final');

        $this->get(route('suporte.log.externo.index'))
        ->assertSessionHasAll($relats);

        $this->get(route('usuarios.lista'))
        ->assertSessionHasAll($relats);

        $this->get(route('regionais.index'))
        ->assertSessionHasAll($relats);

        $this->get(route('agendamentos.lista'))
        ->assertSessionHasAll($relats);

        $this->post(route('logout'));

        $this->post('admin/login', ['login' => $user->username, 'password' => 'TestePorta1@']);

        $this->get(route('suporte.log.externo.index'))
        ->assertSessionMissing($relats[0])
        ->assertSessionMissing($relats[2])
        ->assertSessionMissing($relats[4])
        ->assertSessionMissing($relats[6]);
    }

    /** @test */
    public function sum_reports_tipo_externo()
    {
        Storage::disk('log_externo')->delete(Storage::disk('log_externo')->allFiles(date('Y\/m\/')));
        $this->gerar_log_acessos_rc();

        $this->signInAsAdmin();

        foreach(Suporte::filtros() as $filtro => $f)
        {
            $relat = 'relatorio_'.now()->format('Y-m').'-externo-'.$filtro;
            $this->get(route('suporte.log.externo.relatorios', [
                'relat_tipo' => 'externo', 'relat_data' => 'mes', 'relat_mes' => now()->format('Y-m'), 'relat_opcoes' => $filtro
            ]));

            $this->assertEquals(session($relat)['relatorio']['geral'], $this->tg[$filtro]);
            $this->assertEquals(session($relat)['relatorio']['distintos'], $this->td[$filtro]);
        }

        $this->get(route('suporte.log.externo.relatorios.final'));

        $this->assertStringContainsString($this->tg['relatorio_final'], '<td class="font-weight-bolder">'.session('relatorio_final')['tabela'].'</td>');
        $this->assertStringContainsString($this->td['relatorio_final'], '<td class="font-weight-bolder">'.session('relatorio_final')['tabela'].'</td>');
    }

    /** @test */
    public function sum_reports_tipo_interno()
    {
        Storage::disk('log_interno')->delete(Storage::disk('log_interno')->allFiles(date('Y\/m\/')));
        $this->gerar_log_acessos_admin();

        $this->signInAsAdmin()->update(['idperfil' => 1]);

        $relat = 'relatorio_'.now()->format('Y-m').'-interno-'.Suporte::FILTRO_ACESSO;
        $this->get(route('suporte.log.externo.relatorios', [
            'relat_tipo' => 'interno', 'relat_data' => 'mes', 'relat_mes' => now()->format('Y-m'), 'relat_opcoes' => Suporte::FILTRO_ACESSO
        ]));

        $this->assertEquals(session($relat)['relatorio']['geral'], $this->tg[Suporte::FILTRO_ACESSO]);
        $this->assertEquals(session($relat)['relatorio']['distintos'], $this->td[Suporte::FILTRO_ACESSO]);

        $this->get(route('suporte.log.externo.relatorios.final'));

        $this->assertStringContainsString($this->tg['relatorio_final'], '<td class="font-weight-bolder">'.session('relatorio_final')['tabela'].'</td>');
        $this->assertStringContainsString($this->td['relatorio_final'], '<td class="font-weight-bolder">'.session('relatorio_final')['tabela'].'</td>');
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
    public function user_can_see_all_tabs()
    {
        $this->signInAsAdmin();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<button class="btn btn-primary btn-block font-weight-bolder" data-toggle="collapse" data-target="#basico">Funções Básicas</button>')
        ->assertSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_regional">Serviço: Regionais&nbsp;&nbsp;<i class="nav-icon fas fa-globe-americas"></i></button>')
        ->assertSee('<button class="btn btn-success btn-block font-weight-bolder" data-toggle="collapse" data-target="#area_rep">Área do Representante</button>')
        ->assertSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_noticia">Serviço: Notícias&nbsp;&nbsp;<i class="nav-icon far fa-newspaper"></i></button>')
        ->assertSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_post">Serviço: Blog&nbsp;&nbsp;<i class="nav-icon fas fa-rss"></i></button>')
        ->assertSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_agendamento">Serviço: Agendamentos&nbsp;&nbsp;<i class="nav-icon far fa-clock"></i></button>')
        ->assertSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_rep">Serviço: Representantes&nbsp;&nbsp;<i class="nav-icon fa fa-users"></i></button>')
        ->assertSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_licitacao">Serviço: Licitações&nbsp;&nbsp;<i class="nav-icon far fa-file-alt"></i></button>')
        ->assertSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_plantao">Serviço: Plantão Jurídico&nbsp;&nbsp;<i class="nav-icon fas fa-calendar-alt"></i></button>')
        ->assertSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_fiscal">Serviço: Dados de Fiscalização&nbsp;&nbsp;<i class="nav-icon far fa-file-alt"></i></button>');
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
        ->assertSee('<td>Admin - Menus</td>')
        ->assertSee('<td>Admin - Home</td>')
        ->assertSee('<td>Admin - Perfil</td>');
    }

    /** @test */
    public function user_can_see_content_in_servicos_regionais_tab()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<td>Campos do formulário</td>')
        ->assertSee('<td>Editar</td>');
    }

    /** @test */
    public function user_can_see_content_in_area_representante_tab()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<td>Cadastro</td>')
        ->assertSee('<td>Aba - Home</td>')
        ->assertSee('<td>Aba - Dados Gerais</td>');
    }

    /** @test */
    public function user_can_see_content_in_servicos_noticias_tab()
    {
        $user = $this->signIn();
        Permissao::find(7)->update(['perfis' => '1,' . $user->idperfil]);
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<a href="'.route('admin.manual', 'serv_noticia_campos_form.jpg').'"');
    }

    /** @test */
    public function user_cannot_see_servicos_noticias_tab()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_noticia_campos_form.jpg').'"')
        ->assertDontSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_noticia">Serviço: Notícias&nbsp;&nbsp;<i class="nav-icon far fa-newspaper"></i></button>');
    
        $this->get(route('admin.manual', 'serv_noticia_campos_form.jpg'))->assertForbidden();
    }

    /** @test */
    public function user_can_see_content_in_servicos_blog_tab()
    {
        $user = $this->signIn();
        Permissao::find(43)->update(['perfis' => '1,' . $user->idperfil]);
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<a href="'.route('admin.manual', 'serv_post_campos_form.jpg').'"');
    }

    /** @test */
    public function user_cannot_see_servicos_blog_tab()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_post_campos_form.jpg').'"')
        ->assertDontSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_post">Serviço: Blog&nbsp;&nbsp;<i class="nav-icon fas fa-rss"></i></button>');
    
        $this->get(route('admin.manual', 'serv_post_campos_form.jpg'))->assertForbidden();
    }

    /** @test */
    public function user_can_see_content_in_servicos_agendamentos_tab()
    {
        $user = $this->signIn();
        Permissao::find(27)->update(['perfis' => '1,' . $user->idperfil]);
        Permissao::find(29)->update(['perfis' => '1,' . $user->idperfil]);
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<a href="'.route('admin.manual', 'serv_agenda_campos_form.jpg').'"')
        ->assertSee('<a href="'.route('admin.manual', 'serv_agendaSite_campos_form.jpg').'"')
        ->assertSee('<a href="'.route('admin.manual', 'serv_agendaBloqueio_campos_form.jpg').'"')
        ->assertSee('<a href="'.route('admin.manual', 'duvidas_agenda_bloqueado.mp4').'"');
    }

    /** @test */
    public function user_cannot_see_servicos_agendamentos_tab()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_agenda_campos_form.jpg').'"')
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_agendaSite_campos_form.jpg').'"')
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_agendaBloqueio_campos_form.jpg').'"')
        ->assertDontSee('<a href="'.route('admin.manual', 'duvidas_agenda_bloqueado.mp4').'"')
        ->assertDontSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_agendamento">Serviço: Agendamentos&nbsp;&nbsp;<i class="nav-icon far fa-clock"></i></button>');
    
        $this->get(route('admin.manual', 'serv_agenda_campos_form.jpg'))->assertForbidden();
        $this->get(route('admin.manual', 'serv_agendaSite_campos_form.jpg'))->assertForbidden();
        $this->get(route('admin.manual', 'serv_agendaBloqueio_campos_form.jpg'))->assertForbidden();
        $this->get(route('admin.manual', 'duvidas_agenda_bloqueado.mp4'))->assertForbidden();
    }

    /** @test */
    public function user_can_see_servicos_agendamentos_tab_but_cannot_bloqueio()
    {
        $user = $this->signIn();
        Permissao::find(27)->update(['perfis' => '1,' . $user->idperfil]);
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<a href="'.route('admin.manual', 'serv_agendaSite_campos_form.jpg').'"')
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_agendaBloqueio_campos_form.jpg').'"')
        ->assertSee('<a href="'.route('admin.manual', 'duvidas_agenda_bloqueado.mp4').'"')
        ->assertSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_agendamento">Serviço: Agendamentos&nbsp;&nbsp;<i class="nav-icon far fa-clock"></i></button>');
    
        $this->get(route('admin.manual', 'serv_agendaBloqueio_campos_form.jpg'))->assertForbidden();
    }

    /** @test */
    public function user_can_see_servicos_agendamentos_tab_but_cannot_site_and_admin()
    {
        $user = $this->signIn();
        Permissao::find(29)->update(['perfis' => '1,' . $user->idperfil]);
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_agendaSite_campos_form.jpg').'"')
        ->assertSee('<a href="'.route('admin.manual', 'serv_agendaBloqueio_campos_form.jpg').'"')
        ->assertDontSee('<a href="'.route('admin.manual', 'duvidas_agenda_bloqueado.mp4').'"')
        ->assertSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_agendamento">Serviço: Agendamentos&nbsp;&nbsp;<i class="nav-icon far fa-clock"></i></button>');
    
        $this->get(route('admin.manual', 'serv_agenda_campos_form.jpg'))->assertForbidden();
        $this->get(route('admin.manual', 'serv_agendaSite_campos_form.jpg'))->assertForbidden();
        $this->get(route('admin.manual', 'duvidas_agenda_bloqueado.mp4'))->assertForbidden();
    }

    /** @test */
    public function user_can_see_content_in_servicos_representantes_tab()
    {
        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,' . $user->idperfil]);
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<a href="'.route('admin.manual', 'serv_repCedula_aceitar.mp4').'"');
    }

    /** @test */
    public function user_cannot_see_servicos_representantes_tab()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_repCedula_aceitar.mp4').'"')
        ->assertDontSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_rep">Serviço: Representantes&nbsp;&nbsp;<i class="nav-icon fa fa-users"></i></button>');
    
        $this->get(route('admin.manual', 'serv_repCedula_aceitar.mp4'))->assertForbidden();
    }

    /** @test */
    public function user_can_see_content_in_servicos_licitacao_tab()
    {
        $user = $this->signIn();
        Permissao::find(33)->update(['perfis' => '1,' . $user->idperfil]);
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<a href="'.route('admin.manual', 'serv_licitacao_campos_form.jpg').'"');
    }

    /** @test */
    public function user_cannot_see_servicos_licitacao_tab()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_licitacao_campos_form.jpg').'"')
        ->assertDontSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_licitacao">Serviço: Licitações&nbsp;&nbsp;<i class="nav-icon far fa-file-alt"></i></button>');
    
        $this->get(route('admin.manual', 'serv_licitacao_campos_form.jpg'))->assertForbidden();
    }

    /** @test */
    public function user_can_see_content_in_servicos_plantao_juridico_tab()
    {
        $user = $this->signIn();
        Permissao::find(61)->update(['perfis' => '1,' . $user->idperfil]);
        Permissao::find(63)->update(['perfis' => '1,' . $user->idperfil]);
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<a href="'.route('admin.manual', 'serv_plantao_campos_form.jpg').'"')
        ->assertSee('<a href="'.route('admin.manual', 'serv_plantaoBloqueio_campos_form.jpg').'"');
    }

    /** @test */
    public function user_cannot_see_servicos_plantao_juridico_tab()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_plantao_campos_form.jpg').'"')
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_plantaoBloqueio_campos_form.jpg').'"')
        ->assertDontSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_plantao">Serviço: Plantão Jurídico&nbsp;&nbsp;<i class="nav-icon fas fa-calendar-alt"></i></button>');
    
        $this->get(route('admin.manual', 'serv_plantao_campos_form.jpg'))->assertForbidden();
        $this->get(route('admin.manual', 'serv_plantaoBloqueio_campos_form.jpg'))->assertForbidden();
    }

    /** @test */
    public function user_can_see_servicos_plantao_juridicos_tab_but_cannot_bloqueio()
    {
        $user = $this->signIn();
        Permissao::find(61)->update(['perfis' => '1,' . $user->idperfil]);
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<a href="'.route('admin.manual', 'serv_plantao_campos_form.jpg').'"')
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_plantaoBloqueio_campos_form.jpg').'"')
        ->assertSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_plantao">Serviço: Plantão Jurídico&nbsp;&nbsp;<i class="nav-icon fas fa-calendar-alt"></i></button>');
    
        $this->get(route('admin.manual', 'serv_plantaoBloqueio_campos_form.jpg'))->assertForbidden();
    }

    /** @test */
    public function user_can_see_servicos_plantao_juridico_tab_but_cannot_admin()
    {
        $user = $this->signIn();
        Permissao::find(63)->update(['perfis' => '1,' . $user->idperfil]);
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_plantao_campos_form.jpg').'"')
        ->assertSee('<a href="'.route('admin.manual', 'serv_plantaoBloqueio_campos_form.jpg').'"')
        ->assertSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_plantao">Serviço: Plantão Jurídico&nbsp;&nbsp;<i class="nav-icon fas fa-calendar-alt"></i></button>');
    
        $this->get(route('admin.manual', 'serv_plantao_campos_form.jpg'))->assertForbidden();
    }

    /** @test */
    public function user_can_see_content_in_servicos_fiscalizacao_tab()
    {
        $user = $this->signIn();
        Permissao::find(50)->update(['perfis' => '1,' . $user->idperfil]);
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertSee('<a href="'.route('admin.manual', 'serv_fiscal_campos_form_criar.jpg').'"');
    }

    /** @test */
    public function user_cannot_see_servicos_fiscalizacao_tab()
    {
        $this->signIn();
                 
        $this->get(route('admin.manual'))
        ->assertOk()
        ->assertDontSee('<a href="'.route('admin.manual', 'serv_fiscal_campos_form_criar.jpg').'"')
        ->assertDontSee('<button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_fiscal">Serviço: Dados de Fiscalização&nbsp;&nbsp;<i class="nav-icon far fa-file-alt"></i></button>');
    
        $this->get(route('admin.manual', 'serv_fiscal_campos_form_criar.jpg'))->assertForbidden();
    }
}
