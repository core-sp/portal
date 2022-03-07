<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
        $this->get(route('suporte.log.externo.hoje.view'))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.busca'))->assertRedirect(route('login'));
        $this->get(route('suporte.log.externo.view', date('Y-m-d')))->assertRedirect(route('login'));
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
        $this->get(route('suporte.log.externo.hoje.view'))->assertForbidden();
        $this->get(route('suporte.log.externo.busca'))->assertForbidden();
        $this->get(route('suporte.log.externo.view', date('Y-m-d')))->assertForbidden();
        $this->get(route('suporte.erros.index'))->assertForbidden();
        $this->post(route('suporte.erros.file.post'), ['file' => $file])->assertForbidden();
        $this->get(route('suporte.erros.file.get'))->assertForbidden();
    }

    /** @test */
    public function admin_can_view_message_when_without_log_externo_de_hoje()
    {
        Storage::disk('log_externo')->delete(date('Y').'/'.date('m').'/laravel-'.date('Y-m-d').'.log');

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.index'))->assertOk();
        $this->get(route('suporte.log.externo.hoje.view'))->assertRedirect(route('suporte.log.externo.index'));
        $this->get(route('suporte.log.externo.index'))->assertSee('Ainda não há log do dia de hoje: '.date('d/m/Y'));
    }

    /** @test */
    public function admin_can_view_log_externo_de_hoje()
    {
        // Criando o log para teste
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.index'))->assertOk()->assertSeeText('Última atualização');
        $this->get(route('suporte.log.externo.hoje.view'))
        ->assertHeader('content-disposition', 'inline; filename="laravel-'.date('Y-m-d').'.log"')
        ->assertHeader('content-type', 'text/plain; charset=UTF-8')
        ->assertOk();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('conectou-se à Área do Representante.', $log);
    }

    /** @test */
    public function admin_can_view_log_externo_de_other_date()
    {
        $this->signInAsAdmin();

        $data = '2022-01-19';

        $this->get(route('suporte.log.externo.busca', ['data' => $data]))->assertOk()
        ->assertSeeText('Log do dia '.onlyDate($data));

        $this->get(route('suporte.log.externo.view', $data))
        ->assertHeader('content-disposition', 'inline; filename="laravel-'.$data.'.log"')
        ->assertHeader('content-type', 'text/plain; charset=UTF-8')
        ->assertOk();
    }

    /** @test */
    public function non_admin_cannot_view_log_externo_de_hoje()
    {
        // Criando o log para teste
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->signIn();

        $this->get(route('suporte.log.externo.index'))->assertForbidden();
        $this->get(route('suporte.log.externo.hoje.view'))->assertForbidden();
        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('conectou-se à Área do Representante.', $log);
    }

    /** @test */
    public function admin_can_search_log_externo_by_valid_date()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['data' => '2022-01-19']))->assertOk()
        ->assertSeeText('Log do dia 19/01/2022');
    }

    /** @test */
    public function non_admin_cannot_search_log_externo_by_valid_date()
    {
        $this->signIn();

        $this->get(route('suporte.log.externo.busca', ['data' => '2022-01-19']))->assertForbidden();
    }

    /** @test */
    public function search_log_externo_by_date()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['data' => date('Y-m-d')]))
        ->assertSessionHasErrors('data');

        $this->get(route('suporte.log.externo.busca', ['data' => date('Y-m-d', strtotime('tomorrow'))]))
        ->assertSessionHasErrors('data');

        $this->get(route('suporte.log.externo.busca', ['data' => '2021-05-31']))
        ->assertSessionHasErrors('data');

        $this->get(route('suporte.log.externo.busca', ['data' => '2022-01-16']))->assertOk()
        ->assertSeeText('Não foi encontrado log do dia: 16/01/2022');

        $this->get(route('suporte.log.externo.busca', ['data' => '2022-01-19']))->assertOk()
        ->assertSeeText('Log do dia 19/01/2022');
    }

    /** @test */
    public function search_log_externo_by_text()
    {
        $this->signInAsAdmin();

        $this->get(route('suporte.log.externo.busca', ['texto' => '']))
        ->assertSessionHasErrors('texto');

        $this->get(route('suporte.log.externo.busca', ['texto' => '12']))
        ->assertSessionHasErrors('texto');

        Storage::disk('log_externo')->delete(date('Y').'/'.date('m').'/laravel-'.date('Y-m-d', strtotime('yesterday')).'.log');

        $this->get(route('suporte.log.externo.busca', ['texto' => '123']))->assertOk()
        ->assertSeeText('Não há log para o dia '.date('d/m/Y', strtotime('yesterday')))
        ->assertSeeText('Não foi encontrado o texto: ');

        $this->get(route('suporte.log.externo.busca', ['texto' => 'conectou-se à Área do Representante']))->assertOk()
        ->assertSeeText('Não há log para o dia '.date('d/m/Y', strtotime('yesterday')))
        ->assertSeeText('Foram encontradas');

        // Com aspas e barra
        $this->get(route('suporte.log.externo.busca', ['texto' => '"000000/0001"']))->assertOk()
        ->assertSeeText('Não há log para o dia '.date('d/m/Y', strtotime('yesterday')))
        ->assertSeeText('Foram encontradas');
    }

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
