<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class TermoConsentimentoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_to_insert_email_on_page_termo()
    {
        $this->assertGuest();

        $this->get(route('termo.consentimento.view'))->assertOk();

        $this->post(route('termo.consentimento.post', ['email' => 'teste@teste.com']))
        ->assertRedirect(route('termo.consentimento.view'));

        $this->get(route('termo.consentimento.view'))
        ->assertSee('E-mail cadastrado com sucesso para continuar recebendo nossos informativos.');

        $this->assertDatabaseHas('termos_consentimentos', [
            'id' => 1,
            'ip' => request()->ip(),
            'email' => 'teste@teste.com'
        ]);
    }

    /** @test */
    public function log_is_generated_when_new_email_termo()
    {
        $this->post(route('termo.consentimento.post', ['email' => 'teste@teste.com']));

        $log = tailCustom(storage_path($this->pathLogExterno()));

        $this->assertStringContainsString('foi criado um novo registro no termo de consentimento, com a id: 1', $log);
    }

    /** @test */
    public function cannot_to_insert_email_on_page_termo_if_exist()
    {
        $this->post(route('termo.consentimento.post', ['email' => 'teste@teste.com']));

        $this->post(route('termo.consentimento.post', ['email' => 'teste@teste.com']))
        ->assertRedirect(route('termo.consentimento.view'));

        $this->get(route('termo.consentimento.view'))
        ->assertSee('E-mail já cadastrado para continuar recebendo nossos informativos.');

        $this->assertDatabaseHas('termos_consentimentos', [
            'id' => 1,
            'ip' => request()->ip(),
            'email' => 'teste@teste.com'
        ]);
    }

    /** @test */
    public function show_link_pdf_termo()
    {
        $this->get(route('termo.consentimento.view'))
        ->assertSee(route('termo.consentimento.pdf'));
    }

    // /** @test */
    // public function view_pdf_termo()
    // {
    //     $file = UploadedFile::fake()->create('CORE-SP_Termo_de_consentimento.pdf');

    //     $this->get(route('termo.consentimento.pdf'))
    //     ->assertHeader('content-type', 'application/pdf')
    // }

    /** @test */
    public function redirect_back_if_not_find_pdf_termo()
    {
        $this->get(route('termo.consentimento.pdf'))
        ->assertStatus(302);
    }

    /** @test */
    public function created_new_record_when_new_agendamento()
    {
        $agendamento = factory('App\Agendamento')->raw([
            'idregional' => factory('App\Regional')->create(),
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'termo' => 'on'
        ]);
        $this->post(route('agendamentosite.store'), $agendamento);

        $this->assertDatabaseHas('termos_consentimentos', [
            'id' => 1,
            'ip' => request()->ip(),
            'email' => null,
            'idrepresentante' => null,
            'idnewsletter' => null,
            'idagendamento' => 1,
            'idbdo' => null
        ]);
    }

    /** @test */
    public function id_termo_in_log_when_new_agendamento()
    {
        $agendamento = factory('App\Agendamento')->raw([
            'idregional' => factory('App\Regional')->create(),
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'termo' => 'on'
        ]);
        $this->post(route('agendamentosite.store'), $agendamento);

        $log = tailCustom(storage_path($this->pathLogExterno()));

        $this->assertStringContainsString(' *agendou* atendimento em *', $log);
        $this->assertStringContainsString(' e foi criado um novo registro no termo de consentimento, com a id: 1', $log);
    }

    /** @test */
    public function created_new_record_when_new_newsletter()
    {
        $newsletter = [
            'nomeNl' => 'Testando Termo',
            'emailNl' => 'teste@teste.com',
            'celularNl' => '(11) 99999-9999',
            'termo' => 'on'
        ];
        $this->post('/newsletter', $newsletter);

        $this->assertDatabaseHas('termos_consentimentos', [
            'id' => 1,
            'ip' => request()->ip(),
            'email' => null,
            'idrepresentante' => null,
            'idnewsletter' => 1,
            'idagendamento' => null,
            'idbdo' => null
        ]);
    }

    /** @test */
    public function id_termo_in_log_when_new_newsletter()
    {
        $newsletter = [
            'nomeNl' => 'Testando Termo',
            'emailNl' => 'teste@teste.com',
            'celularNl' => '(11) 99999-9999',
            'termo' => 'on'
        ];
        $this->post('/newsletter', $newsletter);

        $log = tailCustom(storage_path($this->pathLogExterno()));

        $this->assertStringContainsString(' *registrou-se* na newsletter e foi criado um novo registro no termo de consentimento, com a id: 1', $log);
    }

    /** @test */
    public function created_new_record_when_new_bdo()
    {
        $bdoEmpresa = factory('App\BdoEmpresa')->create();
        $bdoOportunidade = factory('App\BdoOportunidade')->raw(['idempresa' => $bdoEmpresa->idempresa]);

        $anunciarVaga = $bdoEmpresa->toArray();
        $anunciarVaga['titulo'] = $bdoOportunidade['titulo'];
        $anunciarVaga['segmentoOportunidade'] = $bdoOportunidade['segmento'];
        $anunciarVaga['regiaoAtuacao'] = explode(',', trim($bdoOportunidade['regiaoatuacao'], ','));
        $anunciarVaga['descricaoOportunidade'] = $bdoOportunidade['descricao'];
        $anunciarVaga['nrVagas'] = $bdoOportunidade['vagasdisponiveis'];
        $anunciarVaga['termo'] = 'on';

        $this->post(route('bdosite.anunciarVaga'), $anunciarVaga);

        $this->assertDatabaseHas('termos_consentimentos', [
            'id' => 1,
            'ip' => request()->ip(),
            'email' => null,
            'idrepresentante' => null,
            'idnewsletter' => null,
            'idagendamento' => null,
            'idbdo' => 1
        ]);
    }

    /** @test */
    public function id_termo_in_log_when_new_bdo()
    {
        $bdoEmpresa = factory('App\BdoEmpresa')->create();
        $bdoOportunidade = factory('App\BdoOportunidade')->raw(['idempresa' => $bdoEmpresa->idempresa]);

        $anunciarVaga = $bdoEmpresa->toArray();
        $anunciarVaga['titulo'] = $bdoOportunidade['titulo'];
        $anunciarVaga['segmentoOportunidade'] = $bdoOportunidade['segmento'];
        $anunciarVaga['regiaoAtuacao'] = explode(',', trim($bdoOportunidade['regiaoatuacao'], ','));
        $anunciarVaga['descricaoOportunidade'] = $bdoOportunidade['descricao'];
        $anunciarVaga['nrVagas'] = $bdoOportunidade['vagasdisponiveis'];
        $anunciarVaga['termo'] = 'on';

        $this->post(route('bdosite.anunciarVaga'), $anunciarVaga);

        $log = tailCustom(storage_path($this->pathLogExterno()));

        $this->assertStringContainsString(' solicitou inclusão de oportunidade no Balcão de Oportunidades e foi criado um novo registro no termo de consentimento, com a id: 1', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_list_emails_termo_download()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        $this->get(route('termo.consentimento.download'))->assertForbidden();
    }

    /** @test */
    public function authorized_users_can_list_emails_termo_download()
    {
        $this->signInAsAdmin();
        
        $this->post(route('termo.consentimento.post', ['email' => 'teste@teste.com']));

        $this->get(route('termo.consentimento.download'))
        ->assertHeader('content-disposition', 'attachment; filename=emails-termo_consentimento-'.date('Ymd').'.csv')
        ->assertHeader('content-type', 'text/csv; charset=UTF-8')
        ->assertOk();
    }

    /** @test */
    public function message_when_without_list_emails_termo()
    {
        $this->signInAsAdmin();
    
        $this->get(route('termo.consentimento.download'))
        ->assertRedirect(route('admin'));

        $this->get(route('admin'))
        ->assertSee('Não há emails cadastrados na tabela de Termo de Consentimento.');
    }
}
