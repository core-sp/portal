<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\PreRegistro;
use App\Anexo;
use Illuminate\Support\Str;

class PreRegistroTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
                
        $this->get(route('externo.preregistro.view'))->assertRedirect(route('externo.login'));
        $this->get(route('externo.inserir.preregistro.view'))->assertRedirect(route('externo.login'));
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.login'));
        $this->put(route('externo.verifica.inserir.preregistro'))->assertRedirect(route('externo.login'));
        $this->post(route('externo.inserir.preregistro.ajax'))->assertRedirect(route('externo.login'));
        $this->get(route('externo.preregistro.anexo.download', 1))->assertRedirect(route('externo.login'));
        $this->delete(route('externo.preregistro.anexo.excluir', 1))->assertRedirect(route('externo.login'));
        $this->get(route('externo.relacao.preregistros'))->assertRedirect(route('externo.login'));
        $this->post(route('externo.contabil.inserir.preregistro'))->assertRedirect(route('externo.login'));

        // ADMIN
        $preRegistro = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $this->get(route('preregistro.index'))->assertRedirect(route('login'));
        $this->get(route('preregistro.view', $preRegistro->id))->assertRedirect(route('login'));
        $this->post(route('preregistro.update.ajax', $preRegistro->id))->assertRedirect(route('login'));
        $this->get(route('preregistro.anexo.download', ['idPreRegistro' => $preRegistro->id, 'id' => $anexo->id]))->assertRedirect(route('login'));
        $this->put(route('preregistro.update.status', $preRegistro->id), ['situacao' => 'aprovar'])->assertRedirect(route('login'));
        $this->get(route('preregistro.busca'))->assertRedirect(route('login'));
        $this->get(route('preregistro.filtro'))->assertRedirect(route('login'));
    }

    /** @test */
    public function users_externo_pf_cannot_access_links_without_pre_registro()
    {
        $externo = $this->signInAsUserExterno();

        factory('App\UserExterno')->create([
            'cpf_cnpj' => '68778785405'
        ]);

        $this->get(route('externo.relacao.preregistros'))->assertRedirect(route('externo.login'));
        $this->post(route('externo.contabil.inserir.preregistro'))->assertRedirect(route('externo.login'));

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'nome_contabil',
            'valor' => 'Teste Teste'
        ])->assertStatus(401);

        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('path');
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('path');
        $this->get(route('externo.preregistro.anexo.download', 1))->assertStatus(401);
        $this->delete(route('externo.preregistro.anexo.excluir', 1))->assertStatus(401);
    }

    /** @test */
    public function users_externo_pj_cannot_access_links_without_pre_registro()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        factory('App\UserExterno')->create([
            'cpf_cnpj' => '68778785405'
        ]);

        $this->get(route('externo.relacao.preregistros'))->assertRedirect(route('externo.login'));
        $this->post(route('externo.contabil.inserir.preregistro'))->assertRedirect(route('externo.login'));

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'nome_contabil',
            'valor' => 'Teste Teste'
        ])->assertStatus(401);

        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('path');
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('path');
        $this->get(route('externo.preregistro.anexo.download', 1))->assertStatus(401);
        $this->delete(route('externo.preregistro.anexo.excluir', 1))->assertStatus(401);
    }

    /** @test */
    public function contabil_cannot_access_links_without_pre_registro()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\UserExterno')->create();

        factory('App\UserExterno')->create([
            'cpf_cnpj' => '68778785405'
        ]);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'segmento',
            'valor' => 'Brindes'
        ])->assertStatus(401);

        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertNotFound();
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertNotFound();
        $this->get(route('externo.preregistro.anexo.download', 1))->assertStatus(401);
        $this->delete(route('externo.preregistro.anexo.excluir', 1))->assertStatus(401);
    }

    /** @test */
    public function registered_users_cannot_create_pre_registro()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->create([
            'cpf_cnpj' => '11748345000144'
        ]));

        $this->get(route('externo.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->create([
            'cpf_cnpj' => '86294373085'
        ]));

        $this->get(route('externo.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));
    }

    /** @test */
    public function log_is_generated_when_registered_users_in_gerenti_before_created()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->create([
            'cpf_cnpj' => '11748345000144'
        ]));

        $this->get(route('externo.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário Externo com cnpj: ' . $externo->cpf_cnpj . ', não pode realizar a solicitação de registro ';
        $txt .= 'devido constar no GERENTI um registro ativo : ' . formataRegistro('0000000002');
        $this->assertStringContainsString($txt, $log);
        
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->create([
            'cpf_cnpj' => '86294373085'
        ]));

        $this->get(route('externo.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário Externo com cpf: ' . $externo->cpf_cnpj . ', não pode realizar a solicitação de registro ';
        $txt .= 'devido constar no GERENTI um registro ativo : ' . formataRegistro('0000000001');
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function error_code_429_externo()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistro')->create();

        for($i = 1; $i <= 100; $i++)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => 'numero',
                'valor' => '222'
            ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'numero',
            'valor' => '222'
        ])->assertStatus(429);
    }

    /** @test */
    public function error_code_429_externo_by_login()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistro')->create();

        for($i = 1; $i <= 100; $i++)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => 'numero',
                'valor' => '222'
            ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'numero',
            'valor' => '222'
        ])->assertStatus(429);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistro = factory('App\PreRegistro')->states('pj')->create([
            'contabil_id' => null
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'segmento',
            'valor' => 'Alimentício'
        ])->assertStatus(200);
    }

    /** @test */
    public function error_code_429_admin()
    {
        $admin = $this->signInAsAdmin();

        for($i = 1; $i <= 100; $i++)
            $this->get(route('preregistro.index'))->assertStatus(200);

        $this->get(route('preregistro.index'))->assertStatus(429);
    }

    /** @test */
    public function error_code_429_admin_by_login()
    {
        $admin = $this->signInAsAdmin();

        for($i = 1; $i <= 100; $i++)
            $this->get(route('preregistro.index'))->assertStatus(200);

        $this->get(route('preregistro.index'))->assertStatus(429);

        $admin = $this->signInAsAdmin('e.admin@teste.com');
        $this->get(route('preregistro.index'))->assertStatus(200);
    }

    /** @test */
    public function view_regionais()
    {
        $regionais = factory('App\Regional', 10)->create();
        $todas = array();
        foreach($regionais->sortBy('regional') as $value)
            array_push($todas, $value->regional);

        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder($todas);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder($todas);
    }

    /** @test */
    public function view_segmentos()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(segmentos());

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(segmentos());
    }

    /** @test */
    public function view_estados()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(estados());

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(estados());
    }

    /** @test */
    public function view_tipos_contatos()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(tipos_contatos());

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(tipos_contatos());
    }

    /** @test */
    public function view_estados_civis()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(estados_civis());

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertDontSeeText(estados_civis()[0]);
    }

    /** @test */
    public function view_nacionalidades()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(nacionalidades());

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertDontSeeText(nacionalidades()[0]);
    }

    /** @test */
    public function view_tipos_empresa()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertDontSeeText(tipos_empresa()[0]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(tipos_empresa());
    }

    /** @test */
    public function view_generos()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(generos());

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(generos());
    }

    /** @test */
    public function view_opcional_celular()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(opcoes_celular());

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(opcoes_celular());
    }

    /** @test */
    public function view_msg_update()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));

        PreRegistro::first()->update(['updated_at' => PreRegistro::first()->updated_at->subHour()]);
        $atual = PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s');

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'numero',
            'valor' => '223'
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
        $this->assertNotEquals($atual, PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    }

    /** @test */
    public function cannot_redirect_form_without_check()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))
        ->assertRedirect(route('externo.preregistro.view'));

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view'))
        ->assertRedirect(route('externo.preregistro.view'));
    }

    // Status do pré-registro

    /** @test */
    public function cannot_view_button_verificar_pendencias_with_status_different_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistroCpf')->create();

        // Status NEGADO permitido, pois irá criar uma nova solicitação
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            if(!in_array($preRegistro->preRegistro->status, [PreRegistro::STATUS_NEGADO, PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
                ->assertDontSeeText('Verificar Pendências');
        }

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => null,
            ])
        ]);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            if(!in_array($preRegistro->preRegistro->status, [PreRegistro::STATUS_NEGADO, PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
                ->assertDontSeeText('Verificar Pendências');
        }
    }

    /** @test */
    public function can_view_button_verificar_pendencias_with_status_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistroCpf')->create();

        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
                $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
                ->assertSeeText($preRegistro->preRegistro->status == PreRegistro::STATUS_CORRECAO ? 'Enviar' : 'Verificar Pendências');
        }

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => null,
            ])
        ]);

        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
                $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
                ->assertSeeText($preRegistro->preRegistro->status == PreRegistro::STATUS_CORRECAO ? 'Enviar' : 'Verificar Pendências');
        }
    }

    /** @test */
    public function can_view_all_status()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistroCpf')->create();

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            $this->get(route('externo.preregistro.view'))
            ->assertSeeText($status);
        }

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => null,
            ])
        ]);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            $this->get(route('externo.preregistro.view'))
            ->assertSeeText($status);
        }
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO VIA AJAX - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function can_update_table_pre_registros_by_ajax()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = factory('App\PreRegistro')->states('campos_ajax')->make();

        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';'. mb_strtoupper($preRegistro['tipo_telefone_1'], 'UTF-8');
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';' . $preRegistro['telefone_1'];
        $preRegistro['opcional_celular'] = $preRegistro['opcional_celular'] . ';' . mb_strtoupper($preRegistro['opcional_celular_1[]'], 'UTF-8');

        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseHas('pre_registros', $preRegistro);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_with_upperCase()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = factory('App\PreRegistro')->states('low', 'campos_ajax')->make();

        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';'. $preRegistro['tipo_telefone_1'];
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';' . $preRegistro['telefone_1'];
        $preRegistro['opcional_celular'] = $preRegistro['opcional_celular'] . ';' . $preRegistro['opcional_celular_1[]'];
    
        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();
        
        foreach($preRegistro as $key => $value)
            if(isset($value))
                $preRegistro[$key] = mb_strtoupper($value, 'UTF-8');

        $this->assertDatabaseHas('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = factory('App\PreRegistro')->states('campos_ajax')->make();
        
        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');

        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = factory('App\PreRegistro')->states('campos_ajax')->make();
        
        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
    
        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = factory('App\PreRegistro')->states('campos_ajax')->make();
        
        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro_erro',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');

        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = factory('App\PreRegistro')->states('campos_ajax')->make();
        
        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');

        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_input_type_text_more_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = [
            'logradouro' => $faker->text(500),
            'complemento' => $faker->text(500),
            'bairro' => $faker->text(500),
            'cidade' => $faker->text(500),
            'telefone' => $faker->text(500),
            'pergunta' => $faker->text(500),
        ];

        foreach($preRegistro as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('valor');

        unset($preRegistro['pergunta']);
        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_idregional_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'idregional',
            'valor' => 55
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'idregional' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_segmento_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'segmento',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'segmento' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_uf_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'uf',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'uf' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_tipo_telefone_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'tipo_telefone',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'tipo_telefone' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_opcional_celular_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_tipo_telefone_1_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'tipo_telefone_1',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'tipo_telefone' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_opcional_celular_1_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular_1',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => null
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_when_insert_tel_optional()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $preRegistro = $externo->load('preRegistro')->preRegistro;

        $telefone = '(11) 98765-4321';

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'tipo_telefone_1',
            'valor' => tipos_contatos()[1]
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'telefone_1',
            'valor' => $telefone
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'tipo_telefone' => $preRegistro->tipo_telefone . ';' . mb_strtoupper(tipos_contatos()[1], 'UTF-8'),
            'telefone' => $preRegistro->telefone . ';' . $telefone,
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_with_tel_principal_after_insert_tel_optional()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $preRegistro = $externo->load('preRegistro')->preRegistro;

        $telefone = '(11) 97777-3216';
        $telefoneOptional = '(11) 98765-4321';

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'tipo_telefone_1',
            'valor' => tipos_contatos()[1]
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'telefone_1',
            'valor' => $telefoneOptional
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'tipo_telefone' => $preRegistro->tipo_telefone . ';' . mb_strtoupper(tipos_contatos()[1], 'UTF-8'),
            'telefone' => $preRegistro->telefone . ';' . $telefoneOptional,
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'tipo_telefone',
            'valor' => tipos_contatos()[0]
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'telefone',
            'valor' => $telefone
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'tipo_telefone' => mb_strtoupper(tipos_contatos()[0], 'UTF-8') . ';' . mb_strtoupper(tipos_contatos()[1], 'UTF-8'),
            'telefone' => $telefone . ';' . $telefoneOptional,
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_when_insert_cel_option()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $opcao_celular = opcoes_celular()[1];

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular',
            'valor' => $opcao_celular
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => mb_strtoupper($opcao_celular, 'UTF-8') . ';',
        ]);

        $opcao_celular = opcoes_celular()[0];

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular',
            'valor' => $opcao_celular
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => mb_strtoupper(opcoes_celular()[1] . ',' . $opcao_celular, 'UTF-8') . ';',
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_when_insert_cel_option_1()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $opcao_celular = opcoes_celular()[1];

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular_1',
            'valor' => $opcao_celular
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => ';' . mb_strtoupper($opcao_celular, 'UTF-8'),
        ]);

        $opcao_celular = opcoes_celular()[0];

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular_1',
            'valor' => $opcao_celular
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => ';' . mb_strtoupper(opcoes_celular()[1] . ',' . $opcao_celular, 'UTF-8'),
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_with_cel_option_principal_after_insert_cel_option_optional()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $preRegistro = $externo->load('preRegistro')->preRegistro;

        $opcao_cel_1 = opcoes_celular()[1];

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular_1',
            'valor' => $opcao_cel_1
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => ';' . mb_strtoupper($opcao_cel_1, 'UTF-8'),
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular',
            'valor' => $opcao_cel_1
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => mb_strtoupper($opcao_cel_1, 'UTF-8') . ';' . mb_strtoupper($opcao_cel_1, 'UTF-8'),
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_when_clean_inputs()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistroPF = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
            ]),
        ]);
        $preRegistro = factory('App\PreRegistro')->states('campos_ajax')->make([
            'tipo_telefone_1' => '',
            'telefone_1' => '',
            'opcional_celular_1[]' => ''
        ]);
        
        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => ''
            ])->assertStatus(200);

        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_blocked_historico_contabil()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => null,
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => '78087976000130'
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => 1,
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => '06985713000138'
        ])->assertOk();

        $this->assertDatabaseMissing('pre_registros', [
            'contabil_id' => 2,
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_when_not_blocked_historico_contabil()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => null,
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => '78087976000130'
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => '1',
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_with_empty_cnpj_contabil_and_blocked_historico_contabil()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => '78087976000130'
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => 1,
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => ''
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => null,
        ]);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistro')->states('sendo_elaborado')->create([
            'contabil_id' => null,
        ]);
        
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                foreach($preRegistro->toArray() as $key => $value)
                    $this->post(route('externo.inserir.preregistro.ajax'), [
                        'classe' => 'preRegistro',
                        'campo' => $key,
                        'valor' => ''
                    ])->assertStatus(401);
        }
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistro')->states('sendo_elaborado')->create([
            'contabil_id' => null,
        ]);

        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->update(['status' => $status]);
            foreach($preRegistro->toArray() as $key => $value)
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'preRegistro',
                    'campo' => $key,
                    'valor' => ''
                ])->assertStatus(200);
        }
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO VIA SUBMIT - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function view_message_errors_when_submit()
    {
        // PF
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));
        $this->put(route('externo.verifica.inserir.preregistro'), ['cnpj_contabil' => '46217816000172'])->assertStatus(302);

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));
        $this->put(route('externo.verifica.inserir.preregistro'), ['cnpj_contabil' => '46217816000172'])->assertStatus(302);

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);
    }

    /** @test */
    public function view_message_errors_when_submit_with_anexos()
    {
        // PF
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), [])->assertStatus(302);
        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), [])->assertStatus(302);
        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);
    }

    /** @test */
    public function cannot_submit_pre_registro_without_anexo()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::first()->anexos()->delete();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();        
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('path');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        PreRegistro::all()->get(1)->anexos()->delete();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();        
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('path');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_wrong_value_segmento()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['segmento'] = 'Qualquer coisa';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('segmento');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['segmento'] = 'Qualquer coisa';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('segmento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_idregional_non_exists()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['idregional'] = 55;
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('idregional');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['idregional'] = 55;
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('idregional');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_cep_more_than_9_chars()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['cep'] = '0123456789';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cep'] = '0123456789';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_cep_with_format_invalid()
    {
        // PF
        $externo = $this->signInAsUserExterno();
        
        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['cep'] = '0454-4555';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cep'] = '0454-4555';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_bairro_less_than_4_chars()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['bairro'] = 'bai';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['bairro'] = 'bai';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_bairro_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();

        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['bairro'] = $faker->text(500);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['bairro'] = $faker->text(500);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_logradouro_less_than_4_chars()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['logradouro'] = 'log';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['logradouro'] = 'log';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_logradouro_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();

        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['logradouro'] = $faker->text(500);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['logradouro'] = $faker->text(500);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_numero_more_than_10_chars()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['numero'] = '01234567890';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('numero');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['numero'] = '01234567890';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('numero');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_complemento_more_than_50_chars()
    {
        $faker = \Faker\Factory::create();

        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['complemento'] = $faker->text(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('complemento');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['complemento'] = $faker->text(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('complemento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_cidade_less_than_4_chars()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['cidade'] = 'cid';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cidade'] = 'cid';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_cidade_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();

        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['cidade'] = $faker->text(500);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cidade'] = $faker->text(500);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_cidade_with_numbers()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['cidade'] = 'Sã0 Paulo';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cidade'] = 'Sã0 Paulo';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_uf_wrong_value()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['uf'] = 'SSP';

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('uf');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['uf'] = 'SSP';

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('uf');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_tipo_telefone_wrong_value()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['tipo_telefone'] = 'SSP';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_telefone');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['tipo_telefone'] = 'SSP';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_telefone');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_telefone_less_than_14_chars()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['telefone'] = '(11) 123456-7';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['telefone'] = '(11) 123456-7';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_telefone_more_than_15_chars_and_value_wrong()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['telefone'] = '(11) 123456-7456';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['telefone'] = '(11) 123456-7456';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_opcional_celular_value_wrong()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['opcional_celular'] = ['KKKKKK', 'SMS'];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('opcional_celular');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = ['KKKKKK', 'SMS'];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('opcional_celular');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_opcional_celular_equals()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['opcional_celular'] = ['SMS', 'SMS'];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('opcional_celular.*');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = ['SMS', 'SMS'];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('opcional_celular.*');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_opcional_celular_not_type_array()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['opcional_celular'] = 'SMS';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('opcional_celular');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = 'SMS';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('opcional_celular');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_empty_telefone_optional_if_tipo_telefone_optional_filled()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['tipo_telefone_1'] = tipos_contatos()[0];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone_1');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['tipo_telefone_1'] = tipos_contatos()[0];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_empty_tipo_telefone_optional_if_telefone_optional_filled()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(11) 99898-8963';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_telefone_1');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(11) 99898-8963';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_telefone_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_tipo_telefone_optional_wrong_value()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(11) 99898-8963';
        $dados['tipo_telefone_1'] = 'KKKKKK';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_telefone_1');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(11) 99898-8963';
        $dados['tipo_telefone_1'] = 'KKKKKK';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_telefone_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_opcional_celular_1_wrong_value()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['opcional_celular_1'] = ['KKKKKK', 'SMS'];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();   
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('opcional_celular_1');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular_1'] = ['KKKKKK', 'SMS'];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();   
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('opcional_celular_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_opcional_celular_1_equals()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['opcional_celular_1'] = ['SMS', 'SMS'];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('opcional_celular_1.*');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['opcional_celular_1'] = ['SMS', 'SMS'];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('opcional_celular_1.*');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_opcional_celular_1_not_type_array()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['opcional_celular_1'] = 'SMS';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('opcional_celular_1');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['opcional_celular_1'] = 'SMS';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('opcional_celular_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_telefone_optional_less_than_14_chars()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(11) 9888-322';
        $dados['tipo_telefone_1'] = tipos_contatos()[0];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone_1');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(11) 9888-322';
        $dados['tipo_telefone_1'] = tipos_contatos()[0];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_telefone_optional_more_than_15_chars_and_wrong_value()
    {
        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(112) 988886-2233';
        $dados['tipo_telefone_1'] = tipos_contatos()[0];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone_1');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(112) 988886-2233';
        $dados['tipo_telefone_1'] = tipos_contatos()[0];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_pergunta_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();

        // PF
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['pergunta'] = $faker->text(500);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('pergunta');

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['pergunta'] = $faker->text(500);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('pergunta');
    }

    /** @test */
    public function cannot_submit_pre_registro_pf_with_status_aguardando_correcao_without_update_input()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados);
        $this->put(route('externo.inserir.preregistro'), $dados);
        $externo->preRegistro->update(['status' => PreRegistro::STATUS_CORRECAO]);

        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));

        $this->get(route('externo.preregistro.view'))
        ->assertSee('<i class="fas fa-times"></i> Formulário não foi enviado para análise da correção, pois precisa editar dados(s) conforme justificativa(s).');
    }

    /** @test */
    public function cannot_submit_pre_registro_pj_with_status_aguardando_correcao_without_update_input()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados);
        $this->put(route('externo.inserir.preregistro'), $dados);
        $externo->preRegistro->update(['status' => PreRegistro::STATUS_CORRECAO]);

        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));

        $this->get(route('externo.preregistro.view'))
        ->assertSee('<i class="fas fa-times"></i> Formulário não foi enviado para análise da correção, pois precisa editar dados(s) conforme justificativa(s).');
    }

    /** @test */
    public function filled_campos_editados_pre_registro_pf_when_form_is_submitted_when_status_aguardando_correcao()
    {
        $externo = $this->signInAsUserExterno();
        factory('App\Regional')->create();

        $dados = ['idregional' => 3, 'segmento' => segmentos()[7], 'cep' => '01234-050', 'logradouro' => 'Rua outro teste', 'numero' => '659',
            'complemento' => 'fundos', 'bairro' => 'Bairro teste', 'cidade' => 'Osasco', 'uf' => 'SC', 'telefone' => '(12) 00000-0000',
            'tipo_telefone' => tipos_contatos()[1], 'opcional_celular' => opcoes_celular()[1], 'telefone_1' => '(11) 11111-0000', 
            'tipo_telefone_1' => tipos_contatos()[0], 'opcional_celular_1' => opcoes_celular()[1]];

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $dadosTotais = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dadosTotais["opcional_celular_1"] = ["SMS"];
      
        foreach($dados as $key => $val)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => $val
            ])->assertStatus(200);

        $dados["opcional_celular"] = [opcoes_celular()[1]];
        $dados["opcional_celular_1"] = [opcoes_celular()[1]];

        $this->put(route('externo.inserir.preregistro'), array_merge($dadosTotais, $dados))
        ->assertRedirect(route('externo.preregistro.view'));

        $pr = PreRegistro::first();
        $camposEditados = $preRegistroCpf->fresh()->preRegistro->getCamposEditados();

        $arrayFinal = array_diff(array_keys($dados), array_keys(json_decode($pr->campos_espelho, true)));
        $this->assertEquals($arrayFinal, array());
        $arrayFinal = array_diff(array_keys($camposEditados), array_keys($pr->getCamposEditados()));
        $this->assertEquals($arrayFinal, array());
    }

    /** @test */
    public function filled_campos_editados_pre_registro_pj_when_form_is_submitted_when_status_aguardando_correcao()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        factory('App\Regional')->create();

        $dados = ['idregional' => 3, 'segmento' => segmentos()[7], 'cep' => '01234-050', 'logradouro' => 'Rua outro teste', 'numero' => '659',
            'complemento' => 'fundos', 'bairro' => 'Bairro teste', 'cidade' => 'Osasco', 'uf' => 'SC', 'telefone' => '(12) 00000-0000',
            'tipo_telefone' => tipos_contatos()[1], 'opcional_celular' => opcoes_celular()[1], 'telefone_1' => '(11) 11111-0000', 
            'tipo_telefone_1' => tipos_contatos()[0], 'opcional_celular_1' => opcoes_celular()[1]];

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->states('low')
        ]);
        $dadosTotais = factory('App\PreRegistroCnpj')->states('request')->make()->final;
      
        foreach($dados as $key => $val)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => $val
            ])->assertStatus(200);

        $dados["opcional_celular"] = [opcoes_celular()[1]];
        $dados["opcional_celular_1"] = [opcoes_celular()[1]];

        $this->put(route('externo.inserir.preregistro'), array_merge($dadosTotais, $dados))
        ->assertRedirect(route('externo.preregistro.view'));

        $pr = PreRegistro::first();
        $camposEditados = $preRegistroCnpj->fresh()->preRegistro->getCamposEditados();

        $arrayFinal = array_diff(array_keys($dados), array_keys(json_decode($pr->campos_espelho, true)));
        $this->assertEquals($arrayFinal, array());
        $arrayFinal = array_diff(array_keys($camposEditados), array_keys($pr->getCamposEditados()));
        $this->assertEquals($arrayFinal, array());
    }

    /** 
     * ===============================================================================================================
     * TESTES PRE-REGISTRO - LOGIN CONTABILIDADE RESPONSÁVEL PELO GERENCIAMENTO PARA O USUARIO EXTERNO COMUM
     * ===============================================================================================================
     */

    /** @test */
    public function contabilidade_cannot_access_links_without_pre_registro()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'segmento',
            'valor' => 'Brindes'
        ])->assertNotFound();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        Anexo::first()->delete();
        
        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('path');
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('path');
        $this->get(route('externo.preregistro.anexo.download', ['id' => 1, 'preRegistro' => 1]))->assertStatus(401);
        $this->delete(route('externo.preregistro.anexo.excluir', ['id' => 1, 'preRegistro' => 1]))->assertStatus(401);

        // PJ
        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        Anexo::first()->delete();
        
        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('path');
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('path');
        $this->get(route('externo.preregistro.anexo.download', ['id' => 1, 'preRegistro' => 1]))->assertStatus(401);
        $this->delete(route('externo.preregistro.anexo.excluir', ['id' => 1, 'preRegistro' => 1]))->assertStatus(401);
    }

    /** @test */
    public function error_code_429_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistro = factory('App\PreRegistro')->create();

        for($i = 1; $i <= 100; $i++)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'preRegistro',
                'campo' => 'numero',
                'valor' => '222'
            ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'numero',
            'valor' => '222'
        ])->assertStatus(429);
    }

    /** @test */
    public function view_regionais_by_contabilidade()
    {
        $regionais = factory('App\Regional', 10)->create();
        $todas = array();
        foreach($regionais->sortBy('regional') as $value)
            array_push($todas, $value->regional);

        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeTextInOrder($todas);

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))
        ->assertSeeTextInOrder($todas);
    }

    /** @test */
    public function view_segmentos_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeTextInOrder(segmentos());

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))
        ->assertSeeTextInOrder(segmentos());
    }

    /** @test */
    public function view_estados_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeTextInOrder(estados());

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))
        ->assertSeeTextInOrder(estados());
    }

    /** @test */
    public function view_tipos_contatos_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeTextInOrder(tipos_contatos());

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))
        ->assertSeeTextInOrder(tipos_contatos());
    }

    /** @test */
    public function view_estados_civis_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeTextInOrder(estados_civis());

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))
        ->assertDontSeeText(estados_civis()[0]);
    }

    /** @test */
    public function view_nacionalidades_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeTextInOrder(nacionalidades());

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))
        ->assertDontSeeText(nacionalidades()[0]);
    }

    /** @test */
    public function view_tipos_empresa_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertDontSeeText(tipos_empresa()[0]);

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))
        ->assertSeeTextInOrder(tipos_empresa());
    }

    /** @test */
    public function view_generos_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeTextInOrder(generos());

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))
        ->assertSeeTextInOrder(generos());
    }

    /** @test */
    public function view_opcional_celular_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeTextInOrder(opcoes_celular());

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))
        ->assertSeeTextInOrder(opcoes_celular());
    }

    /** @test */
    public function view_msg_update_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        
        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));

        PreRegistro::first()->update(['updated_at' => PreRegistro::first()->updated_at->subHour()]);
        $atual = PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s');

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'numero',
            'valor' => '223'
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
        $this->assertNotEquals($atual, PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    }

    // Status do pré-registro

    /** @test */
    public function cannot_view_button_verificar_pendencias_with_status_different_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $preRegistro = factory('App\PreRegistroCpf')->create();

        // Status NEGADO permitido, pois irá criar uma nova solicitação
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            if(!in_array($preRegistro->preRegistro->status, [PreRegistro::STATUS_NEGADO, PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
                ->assertDontSeeText('Verificar Pendências');
        }

        // PJ
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create()->id
        ]);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            if(!in_array($preRegistro->preRegistro->status, [PreRegistro::STATUS_NEGADO, PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))
                ->assertDontSeeText('Verificar Pendências');
        }
    }

    /** @test */
    public function can_view_button_verificar_pendencias_with_status_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $preRegistro = factory('App\PreRegistroCpf')->create();

        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
                $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
                ->assertSeeText($preRegistro->preRegistro->status == PreRegistro::STATUS_CORRECAO ? 'Enviar' : 'Verificar Pendências');
        }

        // PJ
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create()->id
        ]);

        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
                $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))
                ->assertSeeText($preRegistro->preRegistro->status == PreRegistro::STATUS_CORRECAO ? 'Enviar' : 'Verificar Pendências');
        }
    }

    /** @test */
    public function can_view_all_status_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $preRegistro = factory('App\PreRegistroCpf')->create();

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            $this->get(route('externo.preregistro.view', ['preRegistro' => 1]))
            ->assertSeeText($status);
        }

        // PJ
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create()->id
        ]);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            $this->get(route('externo.preregistro.view', ['preRegistro' => 2]))
            ->assertSeeText($status);
        }
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistro = factory('App\PreRegistro')->states('campos_ajax')->make();

        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';'. mb_strtoupper($preRegistro['tipo_telefone_1'], 'UTF-8');
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';' . $preRegistro['telefone_1'];
        $preRegistro['opcional_celular'] = $preRegistro['opcional_celular'] . ';' . mb_strtoupper($preRegistro['opcional_celular_1[]'], 'UTF-8');

        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseHas('pre_registros', $preRegistro);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_with_upperCase_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistro = factory('App\PreRegistro')->states('low', 'campos_ajax')->make();

        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';'. $preRegistro['tipo_telefone_1'];
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';' . $preRegistro['telefone_1'];
        $preRegistro['opcional_celular'] = $preRegistro['opcional_celular'] . ';' . $preRegistro['opcional_celular_1[]'];
    
        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();
        
        foreach($preRegistro as $key => $value)
            if(isset($value))
                $preRegistro[$key] = mb_strtoupper($value, 'UTF-8');

        $this->assertDatabaseHas('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_wrong_input_name_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistro = factory('App\PreRegistro')->states('campos_ajax')->make();
        
        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'preRegistro',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');

        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_without_classe_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistro = factory('App\PreRegistro')->states('campos_ajax')->make();
        
        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => '',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
    
        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_wrong_classe_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistro = factory('App\PreRegistro')->states('campos_ajax')->make();
        
        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'preRegistro_erro',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');

        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_without_campo_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistro = factory('App\PreRegistro')->states('campos_ajax')->make();
        
        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'preRegistro',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');

        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_input_type_text_more_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistro = [
            'logradouro' => $faker->text(500),
            'complemento' => $faker->text(500),
            'bairro' => $faker->text(500),
            'cidade' => $faker->text(500),
            'telefone' => $faker->text(500),
            'pergunta' => $faker->text(500),
        ];

        foreach($preRegistro as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('valor');

        unset($preRegistro['pergunta']);
        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_idregional_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'idregional',
            'valor' => 55
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'idregional' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_segmento_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'segmento',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'segmento' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_uf_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'uf',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'uf' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_tipo_telefone_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'tipo_telefone',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'tipo_telefone' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_opcional_celular_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_tipo_telefone_1_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'tipo_telefone_1',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'tipo_telefone' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_opcional_celular_1_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular_1',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => null
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_when_insert_tel_optional_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $preRegistro = $externo->preRegistros->first();

        $telefone = '(11) 98765-4321';

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'tipo_telefone_1',
            'valor' => tipos_contatos()[1]
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'telefone_1',
            'valor' => $telefone
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'tipo_telefone' => $preRegistro->tipo_telefone . ';' . mb_strtoupper(tipos_contatos()[1], 'UTF-8'),
            'telefone' => $preRegistro->telefone . ';' . $telefone,
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_with_tel_principal_after_insert_tel_optional_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $preRegistro = $externo->preRegistros->first();

        $telefone = '(11) 97777-3216';
        $telefoneOptional = '(11) 98765-4321';

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'tipo_telefone_1',
            'valor' => tipos_contatos()[1]
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'telefone_1',
            'valor' => $telefoneOptional
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'tipo_telefone' => $preRegistro->tipo_telefone . ';' . mb_strtoupper(tipos_contatos()[1], 'UTF-8'),
            'telefone' => $preRegistro->telefone . ';' . $telefoneOptional,
        ]);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'tipo_telefone',
            'valor' => tipos_contatos()[0]
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'telefone',
            'valor' => $telefone
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'tipo_telefone' => mb_strtoupper(tipos_contatos()[0], 'UTF-8') . ';' . mb_strtoupper(tipos_contatos()[1], 'UTF-8'),
            'telefone' => $telefone . ';' . $telefoneOptional,
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_when_insert_cel_option_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $opcao_celular = opcoes_celular()[1];

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular',
            'valor' => $opcao_celular
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => mb_strtoupper($opcao_celular, 'UTF-8') . ';',
        ]);

        $opcao_celular = opcoes_celular()[0];

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular',
            'valor' => $opcao_celular
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => mb_strtoupper(opcoes_celular()[1] . ',' . $opcao_celular, 'UTF-8') . ';',
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_when_insert_cel_option_1_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $opcao_celular = opcoes_celular()[1];

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular_1',
            'valor' => $opcao_celular
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => ';' . mb_strtoupper($opcao_celular, 'UTF-8'),
        ]);

        $opcao_celular = opcoes_celular()[0];

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular_1',
            'valor' => $opcao_celular
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => ';' . mb_strtoupper(opcoes_celular()[1] . ',' . $opcao_celular, 'UTF-8'),
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_with_cel_option_principal_after_insert_cel_option_optional_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $preRegistro = $externo->preRegistros->first();

        $opcao_cel_1 = opcoes_celular()[1];

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular_1',
            'valor' => $opcao_cel_1
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => ';' . mb_strtoupper($opcao_cel_1, 'UTF-8'),
        ]);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'preRegistro',
            'campo' => 'opcional_celular',
            'valor' => $opcao_cel_1
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'opcional_celular' => mb_strtoupper($opcao_cel_1, 'UTF-8') . ';' . mb_strtoupper($opcao_cel_1, 'UTF-8'),
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_when_clean_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroPF = factory('App\PreRegistroCpf')->create();
        $preRegistro = factory('App\PreRegistro')->states('campos_ajax')->make([
            'tipo_telefone_1' => '',
            'telefone_1' => '',
            'opcional_celular_1[]' => ''
        ]);
        
        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => ''
            ])->assertStatus(200);

        $preRegistro = $preRegistro->makeHidden([
            'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistro = factory('App\PreRegistro')->states('sendo_elaborado')->create();
        
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                foreach($preRegistro->toArray() as $key => $value)
                    $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                        'classe' => 'preRegistro',
                        'campo' => $key,
                        'valor' => ''
                    ])->assertStatus(401);
        }
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistro = factory('App\PreRegistro')->states('sendo_elaborado')->create();

        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->update(['status' => $status]);
            foreach($preRegistro->toArray() as $key => $value)
                $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                    'classe' => 'preRegistro',
                    'campo' => $key,
                    'valor' => ''
                ])->assertStatus(200);
        }
    }

    /** @test */
    public function view_message_errors_when_submit_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $dados['segmento'] = 'BláBlá';

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))->assertOk();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertRedirect(route('externo.inserir.preregistro.view', ['preRegistro' => 1]));

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $dados['segmento'] = 'BláBlá';

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))->assertOk();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 2]), $dados)
        ->assertRedirect(route('externo.inserir.preregistro.view', ['preRegistro' => 2]));

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);
    }

    /** @test */
    public function view_message_errors_when_submit_with_anexos_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))->assertOk();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), [])
        ->assertRedirect(route('externo.inserir.preregistro.view', ['preRegistro' => 1]));

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))->assertOk();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 2]), [])
        ->assertRedirect(route('externo.inserir.preregistro.view', ['preRegistro' => 2]));

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 2]))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);
    }

    /** @test */
    public function cannot_submit_pre_registro_without_anexo_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::first()->anexos()->delete();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('path');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        PreRegistro::find(3)->anexos()->delete();
        
        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 3]))->assertOk();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('path');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_wrong_value_segmento_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['segmento'] = 'Qualquer coisa';
                
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('segmento');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['segmento'] = 'Qualquer coisa';
                
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('segmento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_idregional_non_exists_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['idregional'] = 55;
                
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('idregional');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['idregional'] = 55;
                
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('idregional');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_cep_more_than_9_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['cep'] = '0123456789';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('cep');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cep'] = '0123456789';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('cep');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_cep_with_format_invalid_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        
        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['cep'] = '0454-4555';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('cep');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cep'] = '0454-4555';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('cep');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_bairro_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['bairro'] = 'bai';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('bairro');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['bairro'] = 'bai';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('bairro');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_bairro_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['bairro'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('bairro');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['bairro'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('bairro');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_logradouro_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['logradouro'] = 'log';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('logradouro');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['logradouro'] = 'log';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('logradouro');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_logradouro_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['logradouro'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('logradouro');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['logradouro'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('logradouro');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_numero_more_than_10_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['numero'] = '01234567890';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('numero');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['numero'] = '01234567890';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('numero');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_complemento_more_than_50_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['complemento'] = $faker->text(200);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('complemento');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['complemento'] = $faker->text(200);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('complemento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_cidade_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['cidade'] = 'cid';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('cidade');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cidade'] = 'cid';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_cidade_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['cidade'] = $faker->text(500);

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('cidade');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cidade'] = $faker->text(500);

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_cidade_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['cidade'] = 'Sã0 Paulo';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('cidade');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cidade'] = 'Sã0 Paulo';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_uf_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['uf'] = 'SSP';

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('uf');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['uf'] = 'SSP';

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('uf');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_tipo_telefone_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['tipo_telefone'] = 'SSP';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('tipo_telefone');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['tipo_telefone'] = 'SSP';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('tipo_telefone');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_telefone_less_than_14_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['telefone'] = '(11) 123456-7';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('telefone');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['telefone'] = '(11) 123456-7';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('telefone');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_telefone_more_than_15_chars_and_value_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['telefone'] = '(11) 123456-7456';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('telefone');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['telefone'] = '(11) 123456-7456';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('telefone');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_opcional_celular_value_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['opcional_celular'] = ['KKKKKK', 'SMS'];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('opcional_celular');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = ['KKKKKK', 'SMS'];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('opcional_celular');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_opcional_celular_equals_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['opcional_celular'] = ['SMS', 'SMS'];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('opcional_celular.*');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = ['SMS', 'SMS'];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('opcional_celular.*');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_opcional_celular_not_type_array_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['opcional_celular'] = 'SMS';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('opcional_celular');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = 'SMS';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('opcional_celular');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_empty_telefone_optional_if_tipo_telefone_optional_filled_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['opcional_celular'] = null;
        $dados['tipo_telefone_1'] = tipos_contatos()[0];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('telefone_1');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['tipo_telefone_1'] = tipos_contatos()[0];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('telefone_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_empty_tipo_telefone_optional_if_telefone_optional_filled_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(11) 99898-8963';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('tipo_telefone_1');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(11) 99898-8963';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('tipo_telefone_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_tipo_telefone_optional_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(11) 99898-8963';
        $dados['tipo_telefone_1'] = 'KKKKKK';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('tipo_telefone_1');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(11) 99898-8963';
        $dados['tipo_telefone_1'] = 'KKKKKK';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('tipo_telefone_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_opcional_celular_1_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['opcional_celular_1'] = ['KKKKKK', 'SMS'];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('opcional_celular_1');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular_1'] = ['KKKKKK', 'SMS'];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('opcional_celular_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_opcional_celular_1_equals_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['opcional_celular'] = null;
        $dados['opcional_celular_1'] = ['SMS', 'SMS'];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('opcional_celular_1.*');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['opcional_celular_1'] = ['SMS', 'SMS'];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('opcional_celular_1.*');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_opcional_celular_1_not_type_array_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['opcional_celular'] = null;
        $dados['opcional_celular_1'] = 'SMS';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('opcional_celular_1');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['opcional_celular_1'] = 'SMS';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('opcional_celular_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_telefone_optional_less_than_14_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(11) 9888-322';
        $dados['tipo_telefone_1'] = tipos_contatos()[0];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('telefone_1');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(11) 9888-322';
        $dados['tipo_telefone_1'] = tipos_contatos()[0];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('telefone_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_telefone_optional_more_than_15_chars_and_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(112) 988886-2233';
        $dados['tipo_telefone_1'] = tipos_contatos()[0];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('telefone_1');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['opcional_celular'] = null;
        $dados['telefone_1'] = '(112) 988886-2233';
        $dados['tipo_telefone_1'] = tipos_contatos()[0];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('telefone_1');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_pergunta_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');

        // PF
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        $dados['pergunta'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('pergunta');

        // PJ
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['pergunta'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('pergunta');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_status_aguardando_correcao_without_update_input_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        PreRegistro::find(2)->update(['contabil_id' => null]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados);
        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]), $dados);
        PreRegistro::find(1)->update(['status' => PreRegistro::STATUS_CORRECAO]);

        $this->get(route('externo.inserir.preregistro', ['preRegistro' => 1]))->assertOk();

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $this->get(route('externo.preregistro.view', ['preRegistro' => 1]))
        ->assertSee('<i class="fas fa-times"></i> Formulário não foi enviado para análise da correção, pois precisa editar dados(s) conforme justificativa(s).');
    }

    /** @test */
    public function filled_campos_editados_pre_registro_pf_when_form_is_submitted_when_status_aguardando_correcao_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        factory('App\Regional')->create();

        $dados = ['idregional' => 3, 'segmento' => segmentos()[7], 'cep' => '01234-050', 'logradouro' => 'Rua outro teste', 'numero' => '659',
            'complemento' => 'fundos', 'bairro' => 'Bairro teste', 'cidade' => 'Osasco', 'uf' => 'SC', 'telefone' => '(12) 00000-0000',
            'tipo_telefone' => tipos_contatos()[1], 'opcional_celular' => opcoes_celular()[1], 'telefone_1' => '(11) 11111-0000', 
            'tipo_telefone_1' => tipos_contatos()[0], 'opcional_celular_1' => opcoes_celular()[1]];

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $dadosTotais = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dadosTotais["opcional_celular_1"] = ["SMS"];
      
        foreach($dados as $key => $val)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => $val
            ])->assertStatus(200);

        $dados["opcional_celular"] = [opcoes_celular()[1]];
        $dados["opcional_celular_1"] = [opcoes_celular()[1]];

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]), array_merge($dadosTotais, $dados))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $pr = PreRegistro::first();
        $camposEditados = $preRegistroCpf->fresh()->preRegistro->getCamposEditados();

        $arrayFinal = array_diff(array_keys($dados), array_keys(json_decode($pr->campos_espelho, true)));
        $this->assertEquals($arrayFinal, array());
        $arrayFinal = array_diff(array_keys($camposEditados), array_keys($pr->getCamposEditados()));
        $this->assertEquals($arrayFinal, array());
    }

    /** @test */
    public function filled_campos_editados_pre_registro_pj_when_form_is_submitted_when_status_aguardando_correcao_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        factory('App\Regional')->create();

        $dados = ['idregional' => 3, 'segmento' => segmentos()[7], 'cep' => '01234-050', 'logradouro' => 'Rua outro teste', 'numero' => '659',
            'complemento' => 'fundos', 'bairro' => 'Bairro teste', 'cidade' => 'Osasco', 'uf' => 'SC', 'telefone' => '(12) 00000-0000',
            'tipo_telefone' => tipos_contatos()[1], 'opcional_celular' => opcoes_celular()[1], 'telefone_1' => '(11) 11111-0000', 
            'tipo_telefone_1' => tipos_contatos()[0], 'opcional_celular_1' => opcoes_celular()[1]];

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->states('low')
        ]);
        $dadosTotais = factory('App\PreRegistroCnpj')->states('request')->make()->final;
      
        foreach($dados as $key => $val)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => $val
            ])->assertStatus(200);

        $dados["opcional_celular"] = [opcoes_celular()[1]];
        $dados["opcional_celular_1"] = [opcoes_celular()[1]];

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]), array_merge($dadosTotais, $dados))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $pr = PreRegistro::first();
        $camposEditados = $preRegistroCnpj->fresh()->preRegistro->getCamposEditados();

        $arrayFinal = array_diff(array_keys($dados), array_keys(json_decode($pr->campos_espelho, true)));
        $this->assertEquals($arrayFinal, array());
        $arrayFinal = array_diff(array_keys($camposEditados), array_keys($pr->getCamposEditados()));
        $this->assertEquals($arrayFinal, array());
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function view_list_pre_registros()
    {
        $admin = $this->signInAsAdmin();

        $preRegistro1 = factory('App\PreRegistro')->states('analise_inicial')->create([
            'idregional' => $admin->idregional
        ]);
        $preRegistro2 = factory('App\PreRegistro')->states('analise_inicial')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '47662011089'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        $preRegistro3 = factory('App\PreRegistro')->states('analise_inicial')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '06985713000138'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        
        $this->get(route('preregistro.index'))
        ->assertSeeText(formataCpfCnpj($preRegistro1->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj))
        ->assertSeeText($preRegistro1->userExterno->nome)
        ->assertSeeText($preRegistro2->userExterno->nome)
        ->assertSeeText($preRegistro3->userExterno->nome);
    }

    /** @test */
    public function view_status_description_in_list_pre_registros()
    {
        $admin = $this->signInAsAdmin();

        $preRegistro1 = factory('App\PreRegistro')->states('analise_inicial')->create([
            'idregional' => $admin->idregional
        ]);
        $preRegistro2 = factory('App\PreRegistro')->states('analise_inicial')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '47662011089'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        $preRegistro3 = factory('App\PreRegistro')->states('analise_inicial')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '06985713000138'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        
        $this->get(route('preregistro.index'))
        ->assertSeeText('Legenda (click)')
        ->assertSee('data-content="<strong>Solicitante está em processo de preenchimento do formulário</strong>')
        ->assertSee('data-content="<strong>Solicitante está aguardando o atendente analisar os dados</strong>')
        ->assertSee('data-content="<strong>Atendente está aguardando o solicitante corrigir os dados</strong>')
        ->assertSee('data-content="<strong>Solicitante está aguardando o atendente analisar os dados após correção</strong>')
        ->assertSee('data-content="<strong>Atendente aprovou a solicitação</strong>')
        ->assertSee('data-content="<strong>Atendente negou a solicitação</strong>');
    }

    /** @test */
    public function view_list_pre_registros_order_by_status()
    {
        $admin = $this->signInAsAdmin();

        $preRegistro0 = factory('App\PreRegistro')->create([
            'idregional' => $admin->idregional
        ]);
        $preRegistro1 = factory('App\PreRegistro')->states('analise_correcao')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '85528135052'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        $preRegistro2 = factory('App\PreRegistro')->states('enviado_correcao')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '47662011089'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        $preRegistro3 = factory('App\PreRegistro')->states('analise_inicial')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '06985713000138'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        $preRegistro4 = factory('App\PreRegistro')->states('analise_inicial')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '86294373085'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        $preRegistro5 = factory('App\PreRegistro')->states('aprovado')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '11748345000144'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);

        $this->get(route('preregistro.index'))
        ->assertSeeTextInOrder([
            formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj),
            formataCpfCnpj($preRegistro4->userExterno->cpf_cnpj),
            formataCpfCnpj($preRegistro1->userExterno->cpf_cnpj),
            formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj),
            formataCpfCnpj($preRegistro0->userExterno->cpf_cnpj),
            formataCpfCnpj($preRegistro5->userExterno->cpf_cnpj),
        ]);
    }

    /** @test */
    public function view_list_pre_registros_with_idregional_1_when_user_idregional_14()
    {
        $admin = $this->signInAsAdmin();
        factory('App\Regional')->create([
            'idregional' => 14
        ]);
        $regionalAntiga = $admin->regional->regional;
        $admin->update(['idregional' => 14]);

        $preRegistro1 = factory('App\PreRegistro')->states('analise_inicial')->create([
            'idregional' => 1
        ]);
        
        $this->get(route('preregistro.index'))
        ->assertSeeText($preRegistro1->userExterno->nome);
    }

    /** @test */
    public function view_button_editar_with_status_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistro = factory('App\PreRegistro')->states('analise_inicial')->create([
            'idregional' => $admin->idregional
        ]);
        
        $this->get(route('preregistro.index'))
        ->assertSee('class="btn btn-sm btn-primary">Editar</a> ');
    }

    /** @test */
    public function view_button_visualizar_with_status_different_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistro = factory('App\PreRegistro')->create([
            'idregional' => $admin->idregional
        ]);
        
        foreach(PreRegistro::getStatus() as $status)
            if(!in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
            {
                $preRegistro->update(['idusuario' => $admin->idusuario, 'status' => $status]);
                $this->get(route('preregistro.index'))
                ->assertSee('class="btn btn-sm btn-info">Visualizar</a> ');
            }
    }

    /** @test */
    public function view_msg_atualizado_por()
    {
        $admin = $this->signInAsAdmin();

        $preRegistro = factory('App\PreRegistro')->create([
            'idregional' => $admin->idregional
        ]);
        $preRegistro->update(['idusuario' => $admin->idusuario]);
        
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->update(['status' => $status]);
            $this->get(route('preregistro.index'))
            ->assertSee('<small class="d-block">Atualizado por: <strong>'.$admin->nome.'</strong></small>');
        }
    }

    /** @test */
    public function can_filter_by_regional()
    {
        $admin = $this->signInAsAdmin();

        $preRegistro = factory('App\PreRegistro')->states('analise_inicial')->create([
            'idregional' => $admin->idregional
        ]);
        $preRegistro2 = factory('App\PreRegistro')->states('analise_inicial')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '47662011089'
            ]),
            'contabil_id' => null,
        ]);
        $preRegistro3 = factory('App\PreRegistro')->states('analise_inicial')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '06985713000138'
            ]),
            'contabil_id' => null,
        ]);
        
        $this->get(route('preregistro.filtro', ['regional' => $admin->idregional]))
        ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['regional' => $preRegistro2->idregional]))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['regional' => $preRegistro3->idregional]))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));
    }

    /** @test */
    public function can_filter_by_status()
    {
        $admin = $this->signInAsAdmin();

        $preRegistro = factory('App\PreRegistro')->states('analise_inicial')->create([
            'idregional' => $admin->idregional
        ]);
        $preRegistro1 = factory('App\PreRegistro')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '85528135052'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        $preRegistro2 = factory('App\PreRegistro')->states('enviado_correcao')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '47662011089'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        $preRegistro3 = factory('App\PreRegistro')->states('analise_correcao')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '06985713000138'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        $preRegistro4 = factory('App\PreRegistro')->states('aprovado')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '86294373085'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        $preRegistro5 = factory('App\PreRegistro')->states('negado')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '11748345000144'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        
        $this->get(route('preregistro.filtro', ['status' => $preRegistro->status]))
        ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro1->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro4->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro5->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['status' => $preRegistro1->status]))
        ->assertSeeText(formataCpfCnpj($preRegistro1->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro4->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro5->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['status' => $preRegistro2->status]))
        ->assertDontSeeText(formataCpfCnpj($preRegistro1->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro4->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro5->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['status' => $preRegistro3->status]))
        ->assertDontSeeText(formataCpfCnpj($preRegistro1->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro4->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro5->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['status' => $preRegistro4->status]))
        ->assertDontSeeText(formataCpfCnpj($preRegistro1->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro4->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro5->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['status' => $preRegistro5->status]))
        ->assertDontSeeText(formataCpfCnpj($preRegistro1->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro4->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro5->userExterno->cpf_cnpj));
    }

    /** @test */
    public function can_filter_by_atendente()
    {
        $admin = $this->signInAsAdmin();
        $admin2 = factory('App\User')->create([
            'idperfil' => factory('App\Perfil')->create([
                'idperfil' => 8
            ])
        ]);

        $preRegistro = factory('App\PreRegistro')->states('analise_inicial')->create([
            'idregional' => $admin->idregional,
            'idusuario' => $admin->idusuario,
        ]);
        $preRegistro1 = factory('App\PreRegistro')->states('analise_correcao')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '06985713000138'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional,
            'idusuario' => $admin2->idusuario,
        ]);
        
        $this->get(route('preregistro.filtro', ['atendente' => $admin->idusuario]))
        ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro1->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['atendente' => $admin2->idusuario]))
        ->assertSeeText(formataCpfCnpj($preRegistro1->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj));
    }

    /** @test */
    public function can_filter_by_regional_and_status()
    {
        $admin = $this->signInAsAdmin();

        $preRegistro = factory('App\PreRegistro')->states('analise_inicial')->create([
            'idregional' => $admin->idregional
        ]);
        $preRegistro2 = factory('App\PreRegistro')->states('enviado_correcao')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '47662011089'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        $preRegistro3 = factory('App\PreRegistro')->states('analise_correcao')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '06985713000138'
            ]),
            'contabil_id' => null,
        ]);
        
        $this->get(route('preregistro.filtro', ['regional' => $admin->idregional, 'status' => $preRegistro->status]))
        ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['regional' => $admin->idregional, 'status' => $preRegistro2->status]))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['regional' => $preRegistro3->idregional, 'status' => $preRegistro3->status]))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));
    }

    /** @test */
    public function can_filter_by_regional_and_atendente()
    {
        $admin = $this->signInAsAdmin();
        $admin2 = factory('App\User')->create([
            'idperfil' => factory('App\Perfil')->create([
                'idperfil' => 8
            ])
        ]);
        $preRegistro = factory('App\PreRegistro')->states('analise_inicial')->create([
            'idregional' => $admin->idregional,
            'idusuario' => $admin->idusuario,
        ]);
        $preRegistro2 = factory('App\PreRegistro')->states('enviado_correcao')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '47662011089'
            ]),
            'contabil_id' => null,
            'idregional' => $admin2->idregional,
            'idusuario' => $admin2->idusuario,
        ]);
        
        $this->get(route('preregistro.filtro', ['regional' => $admin->idregional, 'atendente' => $preRegistro->idusuario]))
        ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['regional' => $admin2->idregional, 'atendente' => $preRegistro2->idusuario]))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['regional' => 'Todas', 'atendente' => 'Todos']))
        ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj));
    }

    /** @test */
    public function can_filter_by_status_and_atendente()
    {
        $admin = $this->signInAsAdmin();
        $admin2 = factory('App\User')->create([
            'idperfil' => factory('App\Perfil')->create([
                'idperfil' => 8
            ])
        ]);
        $preRegistro = factory('App\PreRegistro')->states('analise_inicial')->create([
            'idregional' => $admin->idregional,
            'idusuario' => $admin->idusuario,
        ]);
        $preRegistro2 = factory('App\PreRegistro')->states('analise_inicial')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '47662011089'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional,
            'idusuario' => $admin2->idusuario,
        ]);
        
        $this->get(route('preregistro.filtro', ['status' => $preRegistro->status, 'atendente' => $preRegistro->idusuario]))
        ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['status' => $preRegistro2->status, 'atendente' => $preRegistro2->idusuario]))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['regional' => 'Todas', 'atendente' => 'Todos']))
        ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj));
    }

    /** @test */
    public function can_filter_by_status_and_atendente_and_regional()
    {
        $admin = $this->signInAsAdmin();
        $admin2 = factory('App\User')->create([
            'idperfil' => factory('App\Perfil')->create([
                'idperfil' => 8
            ])
        ]);
        $preRegistro = factory('App\PreRegistro')->states('analise_inicial')->create([
            'idusuario' => $admin->idusuario,
        ]);
        $preRegistro2 = factory('App\PreRegistro')->states('analise_inicial')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '47662011089'
            ]),
            'contabil_id' => null,
            'idusuario' => $admin2->idusuario,
        ]);
        
        $this->get(route('preregistro.filtro', [
            'status' => $preRegistro->status, 'atendente' => $preRegistro->idusuario, 'regional' => $preRegistro->idregional
        ]))
        ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', [
            'status' => $preRegistro2->status, 'atendente' => $preRegistro2->idusuario, 'regional' => $preRegistro2->idregional
        ]))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj));

        $this->get(route('preregistro.filtro', ['status' => 'Qualquer', 'atendente' => 'Todos', 'regional' => 'Todas']))
        ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj));
    }

    /** @test */
    public function search()
    {
        $admin = $this->signInAsAdmin();

        $preRegistro = factory('App\PreRegistro')->states('analise_inicial')->create([
            'idregional' => $admin->idregional
        ]);
        $preRegistro2 = factory('App\PreRegistro')->states('enviado_correcao')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '47662011089'
            ]),
            'contabil_id' => null,
            'idregional' => $admin->idregional
        ]);
        $preRegistro3 = factory('App\PreRegistro')->states('analise_correcao')->create([
            'user_externo_id' => factory('App\UserExterno')->create([
                'cpf_cnpj' => '06985713000138'
            ]),
            'contabil_id' => null,
        ]);
        
        $this->get(route('preregistro.busca', ['q' => 1]))
        ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

        $this->get(route('preregistro.busca', ['q' => $preRegistro3->userExterno->nome]))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

        $this->get(route('preregistro.busca', ['q' => $preRegistro2->userExterno->cpf_cnpj]))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

        $this->get(route('preregistro.busca', ['q' => '198.']))
        ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

        $this->get(route('preregistro.busca', ['q' => '????']))
        ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
        ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));
    }

    /** @test */
    public function view_button_update_status_with_status_analise_inicial_or_analise_correcao()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSee('<i class="fas fa-check"></i> Aprovar')
        ->assertSee('<i class="fas fa-times"></i> Enviar para correção')
        ->assertSee('<i class="fas fa-ban"></i> Negar');

        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSee('<i class="fas fa-check"></i> Aprovar')
        ->assertSee('<i class="fas fa-times"></i> Enviar para correção')
        ->assertSee('<i class="fas fa-ban"></i> Negar');
    }

    /** @test */
    public function cannot_view_button_update_status_with_status_different_analise_inicial_or_analise_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();

        foreach(PreRegistro::getStatus() as $status)
            if(!in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
            {
                $preRegistroCpf->preRegistro->update(['status' => $status]);
                $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
                ->assertDontSee('<i class="fas fa-check"></i> Aprovar')
                ->assertDontSee('<i class="fas fa-times"></i> Enviar para correção')
                ->assertDontSee('<i class="fas fa-ban"></i> Negar');
            }
    }

    /** @test */
    public function view_justificativa_with_status_negado()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();

        $preRegistroCpf->preRegistro->update([
            'status' => PreRegistro::STATUS_NEGADO, 
            'justificativa' => json_encode(['negado' => 'teste verificando'])
        ]);
        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSee($preRegistroCpf->preRegistro->getJustificativaNegado());
    }

    /** @test */
    public function view_anexos()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexos = factory('App\Anexo', 3)->states('pre_registro')->create();
        
        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeText($anexos->get(0)->nome_original)
        ->assertSeeText($anexos->get(1)->nome_original)
        ->assertSeeText($anexos->get(2)->nome_original);
    }

    /** @test */
    public function view_historico_status()
    {
        $admin = $this->signInAsAdmin();

        $preRegistro = factory('App\PreRegistro')->states('aprovado')->create();

        $this->get(route('preregistro.view', $preRegistro->id))
        ->assertSee(PreRegistro::STATUS_CRIADO.'</span>')
        ->assertSee(PreRegistro::STATUS_ANALISE_INICIAL.'</span>')
        ->assertSee(PreRegistro::STATUS_CORRECAO.'</span>')
        ->assertSee(PreRegistro::STATUS_ANALISE_CORRECAO.'</span>')
        ->assertSee(PreRegistro::STATUS_APROVADO.'</span>');
    }

    /** @test */
    public function view_pre_registro()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $preRegistro = $preRegistroCpf->preRegistro;
        
        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
        ->assertSeeText($preRegistro->userExterno->nome)
        ->assertSeeText($preRegistro->regional->regional)
        ->assertSeeText($preRegistro->segmento)
        ->assertSeeText($preRegistro->registro_secundario)
        ->assertSeeText($preRegistro->cep)
        ->assertSeeText($preRegistro->logradouro)
        ->assertSeeText($preRegistro->numero)
        ->assertSeeText($preRegistro->complemento)
        ->assertSeeText($preRegistro->bairro)
        ->assertSeeText($preRegistro->cidade)
        ->assertSeeText($preRegistro->uf)
        ->assertSeeText(explode(';', $preRegistro->telefone)[0])
        ->assertSeeText(explode(';', $preRegistro->tipo_telefone)[0])
        ->assertSeeText(implode(', ', $preRegistro->getOpcionalCelular()[0]));
    }

    /** @test */
    public function view_text_justificado()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $justificativas = $preRegistroCpf->preRegistro->getJustificativaArray();

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeText($justificativas['idregional'])
        ->assertSeeText($justificativas['segmento'])
        ->assertSeeText($justificativas['cep'])
        ->assertSeeText($justificativas['logradouro'])
        ->assertSeeText($justificativas['numero'])
        ->assertSeeText($justificativas['complemento'])
        ->assertSeeText($justificativas['bairro'])
        ->assertSeeText($justificativas['cidade'])
        ->assertSeeText($justificativas['uf'])
        ->assertSeeText($justificativas['telefone'])
        ->assertSeeText($justificativas['tipo_telefone'])
        ->assertSeeText($justificativas['opcional_celular'])
        ->assertSeeText($justificativas['path']);
    }

    /** @test */
    public function view_historico_justificativas()
    {
        $admin = $this->signInAsAdmin();

        $preRegistro = factory('App\PreRegistro')->states('aprovado_varias_justificativas')->create();
        $hist_justificativas = $preRegistro->getHistoricoJustificativas();
        $just_1 = $hist_justificativas[0];
        $just_2 = $hist_justificativas[1];

        $this->get(route('preregistro.view', $preRegistro->id))
        ->assertSee('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;')
        ->assertSeeInOrder($just_1)
        ->assertSeeInOrder($just_2);
    }
}
