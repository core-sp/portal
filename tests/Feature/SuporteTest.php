<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SuporteTest extends TestCase
{
    use RefreshDatabase;

    private $nomeArquivo = 'suporte-tabela-erros.txt';

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        $file = UploadedFile::fake()->create('teste.txt', 500);
                
        $this->get(route('suporte.log.externo.index'))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.hoje.view', 'interno'))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.busca'))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.view', ['data' => date('Y-m-d'), 'tipo' => 'interno']))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.download', ['data' => date('Y-m-d'), 'tipo' => 'interno']))->assertRedirect(route('login'));
        $this->get(route('suporte.erros.index'))->assertRedirect(route('login'));
        $this->post(route('suporte.erros.file.post'), ['file' => $file])->assertRedirect(route('login'));
        $this->get(route('suporte.erros.file.get'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        $file = UploadedFile::fake()->create('teste.txt', 500);

        $this->get(route('suporte.log.externo.index'))->assertForbidden();
        $this->get(route('suporte.log.externo.hoje.view', 'interno'))->assertForbidden();
        $this->get(route('suporte.log.externo.busca', ['data' => Carbon::today()->subDay()->format('Y-m-d'), 'tipo' => 'interno']))->assertForbidden();
        $this->get(route('suporte.log.externo.view', ['data' => date('Y-m-d'), 'tipo' => 'interno']))->assertForbidden();
        $this->get(route('suporte.log.externo.download', ['data' => date('Y-m-d'), 'tipo' => 'interno']))->assertForbidden();
        $this->get(route('suporte.erros.index'))->assertForbidden();
        $this->post(route('suporte.erros.file.post'), ['file' => $file])->assertForbidden();
        $this->get(route('suporte.erros.file.get'))->assertForbidden();
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
        $this->get('/noticias/teste')->assertStatus(500);

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

    // MENU ERROS

    /** @test */
    public function admin_can_view_message_when_without_table_errors()
    {
        Storage::disk('local')->delete($this->nomeArquivo);
        $this->signInAsAdmin();

        $this->get(route('suporte.erros.index'))->assertOk()
        ->assertSeeText('Os erros ainda não foram tabelados');
    }

    /** @test */
    public function admin_and_email_equal_desenvolvimento_can_do_upload_file()
    {
        $this->signInAsAdmin('desenvolvimento@core-sp.org.br');

        $file = UploadedFile::fake()->createWithContent($this->nomeArquivo, 'texto erro*texto local*texto situação*texto sugestão');

        $this->get(route('suporte.erros.index'))->assertOk();
        $this->post(route('suporte.erros.file.post'), ['file' => $file])->assertRedirect(route('suporte.erros.index'));
        $this->get(route('suporte.erros.index'))->assertSee('Arquivo atualizado com sucesso!')
        ->assertSee('texto erro')
        ->assertSee('texto local')
        ->assertSee('texto situação')
        ->assertSee('texto sugestão');
    }

    /** @test */
    public function admin_and_email_different_desenvolvimento_cannot_do_upload_file()
    {
        Storage::disk('local')->delete($this->nomeArquivo);
        $this->signInAsAdmin();

        $file = UploadedFile::fake()->createWithContent($this->nomeArquivo, 'texto erro*texto local*texto situação*texto sugestão');

        $this->get(route('suporte.erros.index'))->assertOk();
        $this->post(route('suporte.erros.file.post'), ['file' => $file])->assertForbidden();
        Storage::disk('local')->assertMissing($this->nomeArquivo);
    }

    /** @test */
    public function admin_and_email_equal_desenvolvimento_can_do_download_file()
    {
        $this->signInAsAdmin('desenvolvimento@core-sp.org.br');

        $file = UploadedFile::fake()->createWithContent($this->nomeArquivo, 'texto erro*texto local*texto situação*texto sugestão');

        $this->get(route('suporte.erros.index'))->assertOk();
        $this->post(route('suporte.erros.file.post'), ['file' => $file])->assertRedirect(route('suporte.erros.index'));
        $this->get(route('suporte.erros.file.get'))->assertOk();
    }

    /** @test */
    public function admin_and_email_different_desenvolvimento_cannot_do_download_file()
    {
        $this->signInAsAdmin('desenvolvimento@core-sp.org.br');

        $file = UploadedFile::fake()->createWithContent($this->nomeArquivo, 'texto erro*texto local*texto situação*texto sugestão');

        $this->post(route('suporte.erros.file.post'), ['file' => $file]);

        $this->signInAsAdmin();

        $this->get(route('suporte.erros.file.get'))->assertForbidden();
    }

    /** @test */
    public function admin_and_email_equal_desenvolvimento_can_update_file()
    {
        $this->signInAsAdmin('desenvolvimento@core-sp.org.br');

        $file = UploadedFile::fake()->createWithContent($this->nomeArquivo, 'texto erro*texto local*texto situação*texto sugestão');
        
        $this->post(route('suporte.erros.file.post'), ['file' => $file]);
        $this->get(route('suporte.erros.index'))->assertSee('Arquivo atualizado com sucesso!')
        ->assertSee('texto erro')
        ->assertSee('texto local')
        ->assertSee('texto situação')
        ->assertSee('texto sugestão');

        $file = UploadedFile::fake()->createWithContent($this->nomeArquivo, 'novo texto erro*texto local*novo texto situação*texto sugestão');
        
        $this->post(route('suporte.erros.file.post'), ['file' => $file]);
        $this->get(route('suporte.erros.index'))->assertSee('Arquivo atualizado com sucesso!')
        ->assertSee('novo texto erro')
        ->assertSee('texto local')
        ->assertSee('novo texto situação')
        ->assertSee('texto sugestão');
    }
}
