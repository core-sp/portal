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
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . 'Novo email e foi criado um novo registro no termo de consentimento, com a id: 1';
        $this->assertStringContainsString($txt, $log);
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

    /** @test */
    public function view_pdf_termo()
    {
        $this->get(route('termo.consentimento.pdf'))
        ->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function created_new_record_when_new_agendamento()
    {
        $pegarDia = factory('App\Agendamento')->raw();
        $agendamento = factory('App\Agendamento')->raw([
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => 'Outros',
            'pessoa' => 'PF',
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
            'idbdo' => null,
            'idcursoinscrito' => null,
        ]);
    }

    /** @test */
    public function id_termo_in_log_when_new_agendamento()
    {
        $regional = factory('App\Regional')->create();
        $pegarDia = factory('App\Agendamento')->raw();

        $agendamento = factory('App\Agendamento')->raw([
            'idregional' => $regional->idregional,
            'dia' => onlyDate($pegarDia['dia']),
            'hora' => '10:00',
            'servico' => 'Outros',
            'pessoa' => 'PJ',
            'termo' => 'on'
        ]);
        $this->post(route('agendamentosite.store'), $agendamento);

        $log = tailCustom(storage_path($this->pathLogExterno()));

        $this->assertStringContainsString(' *agendou* atendimento em *', $log);
        $this->assertStringContainsString($agendamento['nome'].' (CPF: '.$agendamento['cpf'].') *agendou* atendimento em *'
        .$regional->regional.'* no dia '.$agendamento['dia'].' para o serviço '.$agendamento['servico'].' para '.$agendamento['pessoa'].' e foi criado um novo registro no termo de consentimento, com a id: 1', $log);
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
            'idbdo' => null,
            'idcursoinscrito' => null,
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

        $this->assertStringContainsString('*'.$newsletter['nomeNl'].'* ('.$newsletter['emailNl'].') *registrou-se* na newsletter e foi criado um novo registro no termo de consentimento, com a id: 1', $log);
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
            'idbdo' => 1,
            'idcursoinscrito' => null,
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

        $this->assertStringContainsString('*'.$bdoEmpresa->razaosocial.'* ('.$bdoEmpresa->email.') solicitou inclusão de oportunidade no Balcão de Oportunidades e foi criado um novo registro no termo de consentimento, com a id: 1', $log);
    }

    /** @test */
    public function created_new_record_when_new_course_registration_portal()
    {
        $curso = factory('App\Curso')->create();

        $cursoInscrito = [
            'idcurso' => $curso->idcurso,
            'cpf' => '862.943.730-85',
            'nome' => 'Testando Termo',
            'telefone' => '(11) 99999-9999',
            'email' => 'teste@teste.com',
            'registrocore' => '',
            'termo' => 'on'
        ];

        $this->post(route('cursos.inscricao', $curso->idcurso), $cursoInscrito);

        $this->assertDatabaseHas('termos_consentimentos', [
            'id' => 1,
            'ip' => request()->ip(),
            'email' => null,
            'idrepresentante' => null,
            'idnewsletter' => null,
            'idagendamento' => null,
            'idbdo' => null,
            'idcursoinscrito' => 1,
        ]);
    }

    /** @test */
    public function id_termo_in_log_when_new_course_registration_portal()
    {
        $curso = factory('App\Curso')->create();

        $cursoInscrito = [
            'idcurso' => $curso->idcurso,
            'cpf' => '862.943.730-85',
            'nome' => 'Testando Termo',
            'telefone' => '(11) 99999-9999',
            'email' => 'teste@teste.com',
            'registrocore' => '',
            'termo' => 'on'
        ];
        $this->post(route('cursos.inscricao', $curso->idcurso), $cursoInscrito);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $texto = $cursoInscrito['nome']." (CPF: ".$cursoInscrito['cpf'].") *inscreveu-se* no curso *".$curso->tipo." - ".$curso->tema;
        $texto .= '*, turma *'.$curso->idcurso.'* e foi criado um novo registro no termo de consentimento, com a id: 1';

        $this->assertStringContainsString($texto, $log);
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
