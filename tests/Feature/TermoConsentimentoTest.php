<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\TermoConsentimento;
use Illuminate\Support\Facades\Mail;
use App\Mail\BeneficiosMail;

class TermoConsentimentoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
                
        $this->post(route('termo.consentimento.upload', 'sala-reuniao'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        factory('App\User')->create();
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $this->post(route('termo.consentimento.upload', 'sala-reuniao'), [
            'file' => UploadedFile::fake()->create('teste.pdf')
        ])->assertForbidden();
    }

    /** @test */
    public function not_found_tipo_servico_post()
    {
        $this->signInAsAdmin();
                
        $this->post(route('termo.consentimento.upload', 'salas-reuniao'))->assertNotFound();
        $this->post(route('termo.consentimento.upload', 'salareuniao'))->assertNotFound();
        $this->post(route('termo.consentimento.upload', 'Sala-Reuniao'))->assertNotFound();
        $this->post(route('termo.consentimento.upload', 'sala_reuniao'))->assertNotFound();
    }

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
    public function show_link_pdf_termo_by_tipo_servico()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        factory('App\SalaReuniao')->create();

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee(route('termo.consentimento.pdf', 'sala-reuniao'));
    }

    /** @test */
    public function can_upload_termo_sala_reuniao()
    {
        Storage::fake('public');

        $this->signInAsAdmin();
        factory('App\SalaReuniao')->create();

        $this->get(route('sala.reuniao.index'))
        ->assertSee(route('termo.consentimento.upload', 'sala-reuniao'))
        ->assertSee('<p class="text-primary mb-1"><i class="fas fa-info-circle"></i>&nbsp;Para atualizar o arquivo das condições para o aceite do representante ao agendar.</p>')
        ->assertSee('<label for="enviar-file-sala" class="mr-sm-2"><i class="far fa-file-alt"></i>&nbsp;Atualizar arquivo de aceite</label><input type="file" name="file" ');

        $this->post(route('termo.consentimento.upload', 'sala-reuniao'), ['file' => UploadedFile::fake()->create('teste.pdf')])
        ->assertRedirect(route('sala.reuniao.index'));

        $this->get(route('sala.reuniao.index'))
        ->assertSee('<i class="icon fa fa-check"></i> Termo foi atualizado com sucesso.');

        Storage::disk('public')->assertExists('termos/sala_reuniao_condicoes.pdf');
    }

    /** @test */
    public function log_is_generated_when_upload_termo_sala_reuniao()
    {
        Storage::fake('public');

        $user = $this->signInAsAdmin();

        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->post(route('termo.consentimento.upload', 'sala-reuniao'), ['file' => UploadedFile::fake()->create('teste.pdf')])
        ->assertRedirect(route('sala.reuniao.index'));

        $log = explode(PHP_EOL, tailCustom(storage_path($this->pathLogInterno()), 2));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') está atualizando *arquivo de termo de consentimento com upload do file: teste.pdf* (id: ---)';
        $this->assertStringContainsString($txt, $log[0]);

        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') atualizou *arquivo de termo de consentimento sala_reuniao_condicoes.pdf* (id: ---)';
        $this->assertStringContainsString($txt, $log[1]);
    }

    /** @test */
    public function cannot_upload_termo_sala_reuniao_without_file()
    {
        Storage::fake('public');

        $this->signInAsAdmin();
        factory('App\SalaReuniao')->create();

        $this->post(route('termo.consentimento.upload', 'sala-reuniao'), ['file' => ''])
        ->assertSessionHasErrors([
            'file'
        ]);

        $this->get(route('sala.reuniao.index'))
        ->assertSee('<i class="fas fa-times"></i> <b>Erro de upload do arquivo:</b> O campo file é obrigatório');

        Storage::disk('public')->assertMissing('termos/sala_reuniao_condicoes.pdf');
    }

    /** @test */
    public function cannot_upload_termo_sala_reuniao_with_file_mime_type_invalid()
    {
        Storage::fake('public');

        $this->signInAsAdmin();
        factory('App\SalaReuniao')->create();

        $this->post(route('termo.consentimento.upload', 'sala-reuniao'), ['file' => UploadedFile::fake()->create('teste.png')])
        ->assertSessionHasErrors([
            'file'
        ]);

        $this->get(route('sala.reuniao.index'))
        ->assertSee('<i class="fas fa-times"></i> <b>Erro de upload do arquivo:</b> Tipo de arquivo não suportado');

        Storage::disk('public')->assertMissing('termos/sala_reuniao_condicoes.pdf');
    }

    /** @test */
    public function cannot_upload_termo_sala_reuniao_with_file_more_than_2mb()
    {
        Storage::fake('public');

        $this->signInAsAdmin();
        factory('App\SalaReuniao')->create();

        $this->post(route('termo.consentimento.upload', 'sala-reuniao'), ['file' => UploadedFile::fake()->create('teste.pdf', 2050)])
        ->assertSessionHasErrors([
            'file'
        ]);

        $this->get(route('sala.reuniao.index'))
        ->assertSee('<i class="fas fa-times"></i> <b>Erro de upload do arquivo:</b> Limite de até 2MB o tamanho do arquivo');

        Storage::disk('public')->assertMissing('termos/sala_reuniao_condicoes.pdf');
    }

    /** @test */
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

        $this->get(route('agendamentosite.formview'))
        ->assertSee(route('termo.consentimento.pdf'));

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

        $this->get(route('site.home'))
        ->assertSee(route('termo.consentimento.pdf'));

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

        $this->get(route('bdosite.anunciarVagaView'))
        ->assertSee(route('termo.consentimento.pdf'));

        $this->post(route('bdosite.anunciarVaga'), $anunciarVaga);

        $log = tailCustom(storage_path($this->pathLogExterno()));

        $this->assertStringContainsString('*'.$bdoEmpresa->razaosocial.'* ('.$bdoEmpresa->email.') solicitou inclusão de oportunidade no Balcão de Oportunidades e foi criado um novo registro no termo de consentimento, com a id: 1', $log);
    }

    /** @test */
    public function created_new_record_when_new_course_registration_portal()
    {
        $curso = factory('App\Curso')->states('publico')->create();

        $cursoInscrito = [
            'cpf' => '862.943.730-85',
            'nome' => 'Testando Termo',
            'telefone' => '(11) 99999-9999',
            'email' => 'teste@teste.com',
            'registrocore' => '',
            'termo' => 'on'
        ];

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertSee(route('termo.consentimento.pdf'));

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
        $curso = factory('App\Curso')->states('publico')->create();

        $cursoInscrito = [
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
    public function created_new_record_when_new_sala_agendamento_portal()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee(route('termo.consentimento.pdf', 'sala-reuniao'));

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'], 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ]);

        $this->assertDatabaseHas('termos_consentimentos', [
            'id' => 1,
            'ip' => request()->ip(),
            'email' => null,
            'idrepresentante' => null,
            'idnewsletter' => null,
            'idagendamento' => null,
            'idbdo' => null,
            'idcursoinscrito' => null,
            'agendamento_sala_id' => 1
        ]);
    }

    /** @test */
    public function id_termo_in_log_when_new_sala_agendamento_registration_portal()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'], 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ]);

        $agenda = \App\AgendamentoSala::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $string = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $string .= $representante->nome.' (CPF / CNPJ: '.$representante->cpf_cnpj.') *agendou* reserva da sala em *'.$agenda->sala->regional->regional;
        $string .= '* no dia '.onlyDate($agenda->dia).' para '.$agenda->tipo_sala.', no período ' .$agenda->periodo . ' e foi criado um novo registro no termo de consentimento, com a id: 1';
        $this->assertStringContainsString($string, $log);
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

    /** 
     * =======================================================================================================
     * TESTES BENEFÍCIOS - ÁREA RESTRITA RC
     * =======================================================================================================
     */

    /** @test */
    public function can_view_options_in_route_beneficios()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->get(route('representante.beneficios'))
        ->assertSeeInOrder([
            'type="checkbox" ',
            'id="bene-0"',
            'value="Todos" ',
            'type="checkbox" ',
            'id="bene-1"',
            'value="Allya" ',
            '<button type="submit" class="btn btn-primary loadingPagina">Salvar</button>'
        ]);
    }

    /** @test */
    public function can_view_options_checked_in_route_beneficios()
    {
        $representante = factory('App\Representante')->create();
        $beneficio = factory('App\TermoConsentimento')->states('beneficio')->create([
            'idrepresentante' => $representante->id
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->get(route('representante.beneficios'))
        ->assertSeeInOrder([
            'type="checkbox" ',
            'id="bene-0"',
            'value="Todos" ',
            'checked',
            'type="checkbox" ',
            'id="bene-1"',
            'value="Allya" ',
            'checked',
            '<button type="submit" class="btn btn-primary loadingPagina">Salvar</button>'
        ]);
    }

    /** @test */
    public function can_checked_in_route_beneficios()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->post(route('representante.beneficios.acao'), ['inscricoes' => ['Allya']])
        ->assertSessionHas('message', 'Ação realizada com sucesso e encaminhada à Comunicação!')
        ->assertSessionHas('class', 'alert-success')
        ->assertRedirect(route('representante.beneficios'));

        Mail::assertQueued(BeneficiosMail::class, function ($mail) {
            return $mail->acao == 'inclusão';
        });

        $this->assertDatabaseHas('termos_consentimentos', [
            'ip' => '127.0.0.1',
            'beneficio' => 'Allya',
            'idrepresentante' => 1
        ]);
    }

    /** @test */
    public function can_checked_todos_in_route_beneficios()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $beneficio = factory('App\TermoConsentimento')->states('beneficio')->create([
            'idrepresentante' => $representante->id,
            'created_at' => now()->subDay()->format('Y-m-d H:i:s'),
            'updated_at' => now()->subDay()->format('Y-m-d H:i:s')
        ]);
        $representante->termos()->delete();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->post(route('representante.beneficios.acao'), ['inscricoes' => ['Todos']])
        ->assertSessionHas('message', 'Ação realizada com sucesso e encaminhada à Comunicação!')
        ->assertSessionHas('class', 'alert-success')
        ->assertRedirect(route('representante.beneficios'));

        Mail::assertQueued(BeneficiosMail::class, function ($mail) {
            return $mail->acao == 'novamente a inclusão';
        });

        $this->assertDatabaseHas('termos_consentimentos', [
            'ip' => '127.0.0.1',
            'beneficio' => 'Allya',
            'idrepresentante' => 1
        ]);

        $this->assertDatabaseMissing('termos_consentimentos', [
            'ip' => '127.0.0.1',
            'beneficio' => 'Todos',
            'idrepresentante' => 1
        ]);
    }

    /** @test */
    public function cannot_checked_in_route_beneficios_without_array()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->post(route('representante.beneficios.acao'), ['inscricoes' => 'Allya'])
        ->assertSessionHasErrors(['inscricoes']);
    }

    /** @test */
    public function cannot_checked_in_route_beneficios_with_invalid_type()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->post(route('representante.beneficios.acao'), ['inscricoes' => ['Allyas']])
        ->assertSessionHasErrors(['inscricoes']);
    }

    /** @test */
    public function cannot_checked_in_route_beneficios_with_equals()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->post(route('representante.beneficios.acao'), ['inscricoes' => ['Allya', 'Allya']])
        ->assertSessionHasErrors(['inscricoes.*']);
    }

    /** @test */
    public function can_remove_checked_in_route_beneficios()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $beneficio = factory('App\TermoConsentimento')->states('beneficio')->create([
            'idrepresentante' => $representante->id,
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->post(route('representante.beneficios.acao'), ['inscricoes' => []])
        ->assertSessionHas('message', 'Ação realizada com sucesso e encaminhada à Comunicação!')
        ->assertSessionHas('class', 'alert-success')
        ->assertRedirect(route('representante.beneficios'));

        Mail::assertQueued(BeneficiosMail::class, function ($mail) {
            return $mail->acao == 'remoção';
        });

        $this->assertSoftDeleted('termos_consentimentos', [
            'ip' => '127.0.0.1',
            'beneficio' => 'Allya',
            'idrepresentante' => 1
        ]);
    }

    /** @test */
    public function no_action_in_route_beneficios()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $beneficio = factory('App\TermoConsentimento')->states('beneficio')->create([
            'idrepresentante' => $representante->id
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->post(route('representante.beneficios.acao'), ['inscricoes' => ['Allya']])
        ->assertSessionHas('message', 'Não há ação a ser realizada!')
        ->assertSessionHas('class', 'alert-warning')
        ->assertRedirect(route('representante.beneficios'));

        $this->assertEquals(TermoConsentimento::count(), 1);

        Mail::assertNotQueued(BeneficiosMail::class);

        $representante->termos()->delete();

        $this->assertEquals(TermoConsentimento::count(), 0);

        $this->post(route('representante.beneficios.acao'), ['inscricoes' => []])
        ->assertSessionHas('message', 'Não há ação a ser realizada!')
        ->assertSessionHas('class', 'alert-warning')
        ->assertRedirect(route('representante.beneficios'));

        $this->assertEquals(TermoConsentimento::count(), 0);

        Mail::assertNotQueued(BeneficiosMail::class);
    }

    /** @test */
    public function id_termo_in_log_when_created_beneficio()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->post(route('representante.beneficios.acao'), ['inscricoes' => ['Allya']])
        ->assertSessionHas('message', 'Ação realizada com sucesso e encaminhada à Comunicação!')
        ->assertSessionHas('class', 'alert-success')
        ->assertRedirect(route('representante.beneficios'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $texto = $representante->nome . ' (CPF / CNPJ: ' . $representante->cpf_cnpj . ') solicitou a inclusão da inscrição no Programa de Benefícios ';
        $texto .= 'para o benefício Allya e está registrado com a ID 1 o termo de consentimento.';

        $this->assertStringContainsString($texto, $log);
    }

    /** @test */
    public function id_termo_in_log_when_deleted_beneficio()
    {
        $representante = factory('App\Representante')->create();
        $beneficio = factory('App\TermoConsentimento')->states('beneficio')->create([
            'idrepresentante' => $representante->id
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->post(route('representante.beneficios.acao'), ['inscricoes' => []])
        ->assertSessionHas('message', 'Ação realizada com sucesso e encaminhada à Comunicação!')
        ->assertSessionHas('class', 'alert-success')
        ->assertRedirect(route('representante.beneficios'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $texto = $representante->nome . ' (CPF / CNPJ: ' . $representante->cpf_cnpj . ') solicitou a remoção da inscrição no Programa de Benefícios ';
        $texto .= 'para o benefício Allya e está registrado com a ID 1 o termo de consentimento.';

        $this->assertStringContainsString($texto, $log);
    }

    /** @test */
    public function id_termo_in_log_when_restored_beneficio()
    {
        $representante = factory('App\Representante')->create();
        $beneficio = factory('App\TermoConsentimento')->states('beneficio')->create([
            'idrepresentante' => $representante->id
        ]);
        $beneficio->delete();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->post(route('representante.beneficios.acao'), ['inscricoes' => ['Allya']])
        ->assertSessionHas('message', 'Ação realizada com sucesso e encaminhada à Comunicação!')
        ->assertSessionHas('class', 'alert-success')
        ->assertRedirect(route('representante.beneficios'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $texto = $representante->nome . ' (CPF / CNPJ: ' . $representante->cpf_cnpj . ') solicitou novamente a inclusão da inscrição no Programa de Benefícios ';
        $texto .= 'para o benefício Allya e está registrado com a ID 1 o termo de consentimento.';

        $this->assertStringContainsString($texto, $log);
    }
}
