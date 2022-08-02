<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\PreRegistro;

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

        // ADMIN
        $preRegistro = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->create([
            'path' => \App\Anexo::PATH_PRE_REGISTRO . '/' . $preRegistro->id . '/' . (string) \Str::uuid() . '.jpg',
            'pre_registro_id' => $preRegistro->id
        ]);

        $this->get(route('preregistro.index'))->assertRedirect(route('login'));
        $this->get(route('preregistro.view', $preRegistro->id))->assertRedirect(route('login'));
        $this->post(route('preregistro.update.ajax', $preRegistro->id))->assertRedirect(route('login'));
        $this->get(route('preregistro.anexo.download', ['idPreRegistro' => $preRegistro->id, 'id' => $anexo->id]))->assertRedirect(route('login'));
        $this->put(route('preregistro.update.enviar.correcao', $preRegistro->id))->assertRedirect(route('login'));
        $this->put(route('preregistro.update.aprovado', $preRegistro->id))->assertRedirect(route('login'));
        $this->put(route('preregistro.update.negado', $preRegistro->id))->assertRedirect(route('login'));
        $this->get(route('preregistro.busca'))->assertRedirect(route('login'));
        $this->get(route('preregistro.filtro'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
        ]);
        $anexo = factory('App\Anexo')->raw([
            'path' => \App\Anexo::PATH_PRE_REGISTRO . '/' . $preRegistro['id'] . '/' . (string) \Str::uuid() . '.jpg',
            'pre_registro_id' => $preRegistro['id'],
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf, $anexo);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'nome_contabil',
            'valor' => 'Teste Teste'
        ])->assertStatus(401);

        $this->put(route('externo.inserir.preregistro'), $dados)->assertStatus(302);
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertStatus(302);
        $this->get(route('externo.preregistro.anexo.download', 1))->assertStatus(401);
        $this->delete(route('externo.preregistro.anexo.excluir', 1))->assertStatus(401);
    }

    /** @test */
    public function registered_users_cannot_create_pre_registro()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '11748345000144'
        ]));

        $this->get(route('externo.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '86294373085'
        ]));

        $this->get(route('externo.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');
    }

    /** @test */
    public function log_is_generated_when_registered_users_in_gerenti_before_created()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '11748345000144'
        ]));

        $this->get(route('externo.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('Usuário Externo com cnpj: ' . $externo->cpf_cnpj . ', não pode realizar a solicitação de registro ', $log);
        $this->assertStringContainsString('devido constar no GERENTI um registro ativo : ' . formataRegistro('0000000002'), $log);
        
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '86294373085'
        ]));

        $this->get(route('externo.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('Usuário Externo com cpf: ' . $externo->cpf_cnpj . ', não pode realizar a solicitação de registro ', $log);
        $this->assertStringContainsString('devido constar no GERENTI um registro ativo : ' . formataRegistro('0000000001'), $log);
    }

    /** @test */
    public function error_code_429_externo()
    {
        // Considerado o valor local que está no UserExternoSiteController
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistro')->create([
                'user_externo_id' => $externo->id,
        ]);

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
    public function error_code_429_admin()
    {
        // Considerado o valor local que está no PreRegistroController
        $admin = $this->signInAsAdmin();

        for($i = 1; $i <= 150; $i++)
            $this->get(route('preregistro.index'))->assertStatus(200);

        $this->get(route('preregistro.index'))->assertStatus(429);
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

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder($todas);
    }

    /** @test */
    public function view_segmentos()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(segmentos());

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(segmentos());
    }

    /** @test */
    public function view_estados()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(estados());

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(estados());
    }

    /** @test */
    public function view_tipos_contatos()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(tipos_contatos());

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(tipos_contatos());
    }

    /** @test */
    public function view_estados_civis()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(estados_civis());

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertDontSeeText(estados_civis()[0]);
    }

    /** @test */
    public function view_nacionalidades()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(nacionalidades());

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertDontSeeText(nacionalidades()[0]);
    }

    /** @test */
    public function view_tipos_empresa()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertDontSeeText(tipos_empresa()[0]);

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(tipos_empresa());
    }

    /** @test */
    public function view_generos()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(generos());

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(generos());
    }

    /** @test */
    public function view_opcional_celular()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeTextInOrder(opcoes_celular());

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());

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

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view'))
        ->assertRedirect(route('externo.preregistro.view'));
    }

    // Status do pré-registro

    /** @test */
    public function cannot_view_button_verificar_pendencias_with_status_different_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => $externo->id,
            ])
        ]);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            if(!in_array($preRegistro->preRegistro->status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
                ->assertDontSeeText('Verificar Pendências');
        }

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => $externo->id,
                'contabil_id' => null,
            ])
        ]);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            if(!in_array($preRegistro->preRegistro->status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
                ->assertDontSeeText('Verificar Pendências');
        }
    }

    /** @test */
    public function can_view_button_verificar_pendencias_with_status_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => $externo->id,
            ])
        ]);

        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
                $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
                ->assertSeeText('Verificar Pendências');
        }

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => $externo->id,
                'contabil_id' => null,
            ])
        ]);

        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
                $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
                ->assertSeeText('Verificar Pendências');
        }
    }

    /** @test */
    public function can_view_all_status()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => $externo->id,
            ])
        ]);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            $this->get(route('externo.preregistro.view'))
            ->assertSeeText('Status: ' . $status);
        }

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => $externo->id,
                'contabil_id' => null,
            ])
        ]);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            $this->get(route('externo.preregistro.view'))
            ->assertSeeText('Status: ' . $status);
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

        $preRegistro = factory('App\PreRegistro')->make([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'tipo_telefone_1' => mb_strtoupper(tipos_contatos()[0], 'UTF-8'),
            'telefone_1' => '(11) 99999-8888',
            'opcional_celular_1[]' => mb_strtoupper(opcoes_celular()[1], 'UTF-8'),
        ]);

        $preRegistro = $preRegistro->makeHidden([
            'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
            'historico_status', 'campos_espelho', 'campos_editados'
        ]);

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
            'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
            'historico_status', 'campos_espelho', 'campos_editados', 'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseHas('pre_registros', $preRegistro);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_with_upperCase()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = factory('App\PreRegistro')->state('low')->make([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'tipo_telefone_1' => mb_strtoupper(tipos_contatos()[0], 'UTF-8'),
            'telefone_1' => '(11) 99999-8888',
            'opcional_celular_1[]' => mb_strtoupper(opcoes_celular()[1], 'UTF-8'),
        ]);

        $preRegistro = $preRegistro->makeHidden([
            'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
            'historico_status', 'campos_espelho', 'campos_editados'
        ]);
        
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
            'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
            'historico_status', 'campos_espelho', 'campos_editados', 'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
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

        $preRegistro = factory('App\PreRegistro')->make([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'tipo_telefone_1' => mb_strtoupper(tipos_contatos()[0], 'UTF-8'),
            'telefone_1' => '(11) 99999-8888',
            'opcional_celular_1[]' => mb_strtoupper(opcoes_celular()[1], 'UTF-8'),
        ]);

        $preRegistro = $preRegistro->makeHidden([
            'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
            'historico_status', 'campos_espelho', 'campos_editados'
        ]);
        
        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');

        $preRegistro = $preRegistro->makeHidden([
            'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
            'historico_status', 'campos_espelho', 'campos_editados', 'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = factory('App\PreRegistro')->make([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'tipo_telefone_1' => mb_strtoupper(tipos_contatos()[0], 'UTF-8'),
            'telefone_1' => '(11) 99999-8888',
            'opcional_celular_1[]' => mb_strtoupper(opcoes_celular()[1], 'UTF-8'),
        ]);

        $preRegistro = $preRegistro->makeHidden([
            'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
            'historico_status', 'campos_espelho', 'campos_editados'
        ]);
        
        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
    
        $preRegistro = $preRegistro->makeHidden([
            'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
            'historico_status', 'campos_espelho', 'campos_editados', 'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = factory('App\PreRegistro')->make([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'tipo_telefone_1' => mb_strtoupper(tipos_contatos()[0], 'UTF-8'),
            'telefone_1' => '(11) 99999-8888',
            'opcional_celular_1[]' => mb_strtoupper(opcoes_celular()[1], 'UTF-8'),
        ]);

        $preRegistro = $preRegistro->makeHidden([
            'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
            'historico_status', 'campos_espelho', 'campos_editados'
        ]);
        
        foreach($preRegistro->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro_erro',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');

        $preRegistro = $preRegistro->makeHidden([
            'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
            'historico_status', 'campos_espelho', 'campos_editados', 'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
        ])->toArray();

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = factory('App\PreRegistro')->make([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'tipo_telefone_1' => mb_strtoupper(tipos_contatos()[0], 'UTF-8'),
            'telefone_1' => '(11) 99999-8888',
            'opcional_celular_1[]' => mb_strtoupper(opcoes_celular()[1], 'UTF-8'),
        ]);

        $preRegistro = $preRegistro->makeHidden([
            'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
            'historico_status', 'campos_espelho', 'campos_editados'
        ]);
        
        foreach($preRegistro as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');

        $preRegistro = $preRegistro->makeHidden([
            'registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'confere_anexos', 'historico_contabil',
            'historico_status', 'campos_espelho', 'campos_editados', 'tipo_telefone_1', 'telefone_1', 'opcional_celular_1[]'
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
            'segmento' => $faker->sentence(400),
            'logradouro' => $faker->sentence(400),
            'complemento' => $faker->sentence(400),
            'bairro' => $faker->sentence(400),
            'cidade' => $faker->sentence(400),
            'telefone' => $faker->sentence(400),
            'tipo_telefone' => $faker->sentence(400),
            'opcional_celular' => $faker->sentence(400),
            'opcional_celular_1' => $faker->sentence(400),
            'pergunta' => $faker->sentence(400),
        ];

        foreach($preRegistro as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('valor');

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

    // /** @test */
    // public function cannot_update_table_anexos_by_ajax_with_more_than_5_files()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     for($count = 1; $count <= 5; $count++)
    //     {
    //         $this->post(route('externo.inserir.preregistro.ajax'), [
    //             'classe' => 'anexos',
    //             'campo' => 'path',
    //             'valor' => UploadedFile::fake()->create('random' . $count . '.pdf')
    //         ])->assertOk();

    //         $this->assertDatabaseHas('anexos', [
    //             'pre_registro_id' => 1,
    //             'nome_original' => 'random' . $count. '.pdf',
    //         ]);
    //     }

    //     $anexos = $externo->load('preRegistro')->preRegistro->anexos;
    //     foreach($anexos as $anexo)
    //         Storage::disk('local')->assertExists($anexo->path);

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random6.pdf')
    //     ])->assertOk();

    //     $this->assertDatabaseMissing('anexos', [
    //         'pre_registro_id' => 1,
    //         'nome_original' => 'random6.pdf'
    //     ]);

    //     Storage::disk('local')->assertMissing('userExterno/pre_registros/random6.pdf');
    // }

    // /** @test */
    // public function owner_can_delete_file()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();

    //     $anexo = $externo->load('preRegistro')->preRegistro->anexos->first();
    //     Storage::disk('local')->assertExists($anexo->path);

    //     $this->delete(route('externo.preregistro.anexo.excluir', $anexo->id))->assertOk();

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
    //     ->assertDontSee('random.pdf');

    //     $this->assertDatabaseMissing('anexos', [
    //         'pre_registro_id' => 1,
    //         'nome_original' => 'random.pdf'
    //     ]);

    //     Storage::disk('local')->assertMissing($anexo->path);
    // }

    // /** @test */
    // public function owner_can_download_file()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();

    //     $anexo = $externo->load('preRegistro')->preRegistro->anexos->first();
    //     Storage::disk('local')->assertExists($anexo->path);

    //     $this->get(route('externo.preregistro.anexo.download', $anexo->id))->assertOk();
    // }

    // /** @test */
    // public function not_owner_cannot_delete_file()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();

    //     $pr = $externo->load('preRegistro')->preRegistro;
    //     $anexo = $pr->anexos->first();
    //     Storage::disk('local')->assertExists($anexo->path);

    //     $externo = $this->signInAsUserExterno(factory('App\UserExterno')->state('pj')->create());
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->delete(route('externo.preregistro.anexo.excluir', $anexo->id))->assertStatus(401);

    //     $this->assertDatabaseHas('anexos', [
    //         'path' => $anexo->path,
    //         'nome_original' => $anexo->nome_original,
    //         'pre_registro_id' => $pr->id
    //     ]);

    //     Storage::disk('local')->assertExists($anexo->path);
    // }

    // /** @test */
    // public function not_owner_cannot_download_file()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();

    //     $anexo = $externo->load('preRegistro')->preRegistro->anexos->first();
    //     Storage::disk('local')->assertExists($anexo->path);

    //     $externo = $this->signInAsUserExterno(factory('App\UserExterno')->state('pj')->create());
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->get(route('externo.preregistro.anexo.download', $anexo->id))->assertStatus(401);
    // }

    // /** @test */
    // public function owner_cannot_delete_without_file()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->delete(route('externo.preregistro.anexo.excluir', 1))->assertStatus(401);
    // }

    // /** @test */
    // public function owner_cannot_download_without_file()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->get(route('externo.preregistro.anexo.download', 1))->assertStatus(401);
    // }

    // /** @test */
    // public function can_update_table_pre_registros_by_ajax_when_insert_tel_optional()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $preRegistro = $externo->load('preRegistro')->preRegistro;

    //     $telefone = '(11) 98765-4321';

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'preRegistro',
    //         'campo' => 'tipo_telefone_1',
    //         'valor' => tipos_contatos()[1]
    //     ])->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'preRegistro',
    //         'campo' => 'telefone_1',
    //         'valor' => $telefone
    //     ])->assertOk();

    //     $this->assertDatabaseHas('pre_registros', [
    //         'tipo_telefone' => $preRegistro->tipo_telefone . ';' . mb_strtoupper(tipos_contatos()[1], 'UTF-8'),
    //         'telefone' => $preRegistro->telefone . ';' . $telefone,
    //     ]);
    // }

    // /** @test */
    // public function can_update_table_pre_registros_by_ajax_with_tel_principal_after_insert_tel_optional()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $preRegistro = $externo->load('preRegistro')->preRegistro;

    //     $telefone = '(11) 97777-3216';
    //     $telefoneOptional = '(11) 98765-4321';

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'preRegistro',
    //         'campo' => 'tipo_telefone_1',
    //         'valor' => tipos_contatos()[1]
    //     ])->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'preRegistro',
    //         'campo' => 'telefone_1',
    //         'valor' => $telefoneOptional
    //     ])->assertOk();

    //     $this->assertDatabaseHas('pre_registros', [
    //         'tipo_telefone' => $preRegistro->tipo_telefone . ';' . mb_strtoupper(tipos_contatos()[1], 'UTF-8'),
    //         'telefone' => $preRegistro->telefone . ';' . $telefoneOptional,
    //     ]);

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'preRegistro',
    //         'campo' => 'tipo_telefone',
    //         'valor' => tipos_contatos()[0]
    //     ])->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'preRegistro',
    //         'campo' => 'telefone',
    //         'valor' => $telefone
    //     ])->assertOk();

    //     $this->assertDatabaseHas('pre_registros', [
    //         'tipo_telefone' => mb_strtoupper(tipos_contatos()[0], 'UTF-8') . ';' . mb_strtoupper(tipos_contatos()[1], 'UTF-8'),
    //         'telefone' => $telefone . ';' . $telefoneOptional,
    //     ]);
    // }

    // /** @test */
    // public function can_update_table_pre_registros_by_ajax_when_insert_cel_option()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $opcao_celular = opcoes_celular()[1];

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'preRegistro',
    //         'campo' => 'opcional_celular',
    //         'valor' => $opcao_celular
    //     ])->assertOk();

    //     $this->assertDatabaseHas('pre_registros', [
    //         'opcional_celular' => mb_strtoupper($opcao_celular, 'UTF-8') . ';',
    //     ]);

    //     $opcao_celular = opcoes_celular()[0];

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'preRegistro',
    //         'campo' => 'opcional_celular',
    //         'valor' => $opcao_celular
    //     ])->assertOk();

    //     $this->assertDatabaseHas('pre_registros', [
    //         'opcional_celular' => mb_strtoupper(opcoes_celular()[1] . ',' . $opcao_celular, 'UTF-8') . ';',
    //     ]);
    // }

    // /** @test */
    // public function can_update_table_pre_registros_by_ajax_when_insert_cel_option_1()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $opcao_celular = opcoes_celular()[1];

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'preRegistro',
    //         'campo' => 'opcional_celular_1',
    //         'valor' => $opcao_celular
    //     ])->assertOk();

    //     $this->assertDatabaseHas('pre_registros', [
    //         'opcional_celular' => ';' . mb_strtoupper($opcao_celular, 'UTF-8'),
    //     ]);

    //     $opcao_celular = opcoes_celular()[0];

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'preRegistro',
    //         'campo' => 'opcional_celular_1',
    //         'valor' => $opcao_celular
    //     ])->assertOk();

    //     $this->assertDatabaseHas('pre_registros', [
    //         'opcional_celular' => ';' . mb_strtoupper(opcoes_celular()[1] . ',' . $opcao_celular, 'UTF-8'),
    //     ]);
    // }

    // /** @test */
    // public function can_update_table_pre_registros_by_ajax_with_cel_option_principal_after_insert_cel_option_optional()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $preRegistro = $externo->load('preRegistro')->preRegistro;

    //     $opcao_cel_1 = opcoes_celular()[1];

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'preRegistro',
    //         'campo' => 'opcional_celular_1',
    //         'valor' => $opcao_cel_1
    //     ])->assertOk();

    //     $this->assertDatabaseHas('pre_registros', [
    //         'opcional_celular' => ';' . mb_strtoupper($opcao_cel_1, 'UTF-8'),
    //     ]);

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'preRegistro',
    //         'campo' => 'opcional_celular',
    //         'valor' => $opcao_cel_1
    //     ])->assertOk();

    //     $this->assertDatabaseHas('pre_registros', [
    //         'opcional_celular' => mb_strtoupper($opcao_cel_1, 'UTF-8') . ';' . mb_strtoupper($opcao_cel_1, 'UTF-8'),
    //     ]);
    // }

    // /** @test */
    // public function can_update_table_pre_registros_by_ajax_when_clean_inputs()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->create([
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //     ]);
    //     $preRegistroPF = factory('App\PreRegistroCpf')->create([
    //         'pre_registro_id' => $preRegistro->id,
    //     ]);

    //     $preRegistro = $preRegistro->toArray();
    //     $preRegistro['tipo_telefone_1'] = '';
    //     $preRegistro['telefone_1'] = '';

    //     $pular = ['registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'updated_at', 'created_at', 'id', 'confere_anexos', 'historico_contabil'];
        
    //     foreach($preRegistro as $key => $value)
    //     {
    //         if(!in_array($key, $pular))
    //             $this->post(route('externo.inserir.preregistro.ajax'), [
    //                 'classe' => 'preRegistro',
    //                 'campo' => $key,
    //                 'valor' => ''
    //             ])->assertStatus(200);
    //     }
        
    //     unset($preRegistro['tipo_telefone_1']);
    //     unset($preRegistro['telefone_1']);

    //     $this->assertDatabaseMissing('pre_registros', $preRegistro);
    // }

    // /** @test */
    // public function cannot_update_table_pre_registros_by_ajax_with_pergunta_filled()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'preRegistro',
    //         'campo' => 'pergunta',
    //         'valor' => 'resposta da pergunta'
    //     ])->assertOk();

    //     $this->assertDatabaseMissing('pre_registros', [
    //         'pergunta' => 'resposta da pergunta',
    //     ]);
    // }

    // /** @test */
    // public function cannot_update_table_pre_registros_by_ajax_with_blocked_historico_contabil()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'contabil',
    //         'campo' => 'cnpj_contabil',
    //         'valor' => '78087976000130'
    //     ])->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'contabil',
    //         'campo' => 'cnpj_contabil',
    //         'valor' => '06985713000138'
    //     ])->assertOk();

    //     $this->assertDatabaseMissing('pre_registros', [
    //         'contabil_id' => '2',
    //     ]);
    // }

    // /** @test */
    // public function can_update_table_pre_registros_by_ajax_when_not_blocked_historico_contabil()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'contabil',
    //         'campo' => 'cnpj_contabil',
    //         'valor' => '78087976000130'
    //     ])->assertOk();

    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => '1',
    //     ]);
    // }

    // /** @test */
    // public function can_update_table_pre_registros_by_ajax_when_empty_cnpj_contabil_and_blocked_historico_contabil()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'contabil',
    //         'campo' => 'cnpj_contabil',
    //         'valor' => '78087976000130'
    //     ])->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'contabil',
    //         'campo' => 'cnpj_contabil',
    //         'valor' => ''
    //     ])->assertOk();

    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => null,
    //     ]);
    // }

    // // Status do pré-registro

    // /** @test */
    // public function cannot_update_table_pre_registros_by_ajax_with_status_different_aguardando_correcao_or_null()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $preRegistro = factory('App\PreRegistro')->create([
    //             'user_externo_id' => $externo->id,
    //     ]);

    //     $preRegistroAjax = $preRegistro->toArray();
    //     $pular = ['registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'updated_at', 'created_at', 'id', 'confere_anexos', 'historico_contabil'];
        
    //     foreach(PreRegistro::getStatus() as $status)
    //     {
    //         $preRegistro->update(['status' => $status]);
    //         if($status != PreRegistro::STATUS_CORRECAO)
    //             foreach($preRegistroAjax as $key => $value)
    //             {
    //                 if(!in_array($key, $pular))
    //                     $this->post(route('externo.inserir.preregistro.ajax'), [
    //                         'classe' => 'preRegistro',
    //                         'campo' => $key,
    //                         'valor' => ''
    //                     ])->assertStatus(401);
    //             }
    //     }
    // }

    // /** @test */
    // public function can_update_table_pre_registros_by_ajax_with_status_aguardando_correcao_or_null()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $preRegistro = factory('App\PreRegistro')->create([
    //             'user_externo_id' => $externo->id,
    //     ]);

    //     $preRegistroAjax = $preRegistro->toArray();
    //     $pular = ['registro_secundario', 'user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'updated_at', 'created_at', 'id', 'confere_anexos', 'historico_contabil'];
        
    //     foreach([PreRegistro::STATUS_CORRECAO, null] as $status)
    //     {
    //         $preRegistro->update(['status' => $status]);
    //         foreach($preRegistroAjax as $key => $value)
    //         {
    //             if(!in_array($key, $pular))
    //                 $this->post(route('externo.inserir.preregistro.ajax'), [
    //                     'classe' => 'preRegistro',
    //                     'campo' => $key,
    //                     'valor' => ''
    //                 ])->assertStatus(200);
    //         }
    //     }
    // }

    // /** 
    //  * =======================================================================================================
    //  * TESTES PRE-REGISTRO VIA SUBMIT - CLIENT
    //  * =======================================================================================================
    //  */

    // /** @test */
    // public function view_message_errors_when_submit()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));
    //     $this->put(route('externo.verifica.inserir.preregistro'), ['cnpj_contabil' => '46217816000172'])->assertStatus(302);

    //     $errors = session('errors');
    //     $keys = array();
    //     foreach($errors->messages() as $key => $value)
    //         array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
    //     ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
    //     ->assertSeeInOrder($keys);

    //     $externo = $this->signInAsUserExterno(factory('App\UserExterno')->state('pj')->create());
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));
    //     $this->put(route('externo.verifica.inserir.preregistro'), ['cnpj_contabil' => '46217816000172'])->assertStatus(302);

    //     $errors = session('errors');
    //     $keys = array();
    //     foreach($errors->messages() as $key => $value)
    //         array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
    //     ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
    //     ->assertSeeInOrder($keys);
    // }

    // /** @test */
    // public function view_message_errors_when_submit_with_anexos()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random2.pdf')
    //     ])->assertStatus(200);

    //     $this->put(route('externo.verifica.inserir.preregistro'), [])->assertStatus(302);
    //     $errors = session('errors');
    //     $keys = array();
    //     foreach($errors->messages() as $key => $value)
    //         array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
    //     ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
    //     ->assertSeeInOrder($keys);

    //     $externo = $this->signInAsUserExterno(factory('App\UserExterno')->state('pj')->create());
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random2.pdf')
    //     ])->assertStatus(200);

    //     $this->put(route('externo.verifica.inserir.preregistro'), [])->assertStatus(302);
    //     $errors = session('errors');
    //     $keys = array();
    //     foreach($errors->messages() as $key => $value)
    //         array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
    //     ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
    //     ->assertSeeInOrder($keys);
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_without_anexo()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();        
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('path');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_without_segmento()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'segmento' => ''
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('segmento');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_wrong_value_segmento()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'segmento' => 'Qualquer coisa'
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('segmento');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_idregional_non_exists()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'idregional' => 55
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('idregional');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_cep_more_than_9_chars()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'cep' => '0123456789'
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('cep');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_bairro_more_than_191_chars()
    // {
    //     $faker = \Faker\Factory::create();
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'bairro' => $faker->sentence(400)
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('bairro');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_logradouro_more_than_191_chars()
    // {
    //     $faker = \Faker\Factory::create();
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'logradouro' => $faker->sentence(400)
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('logradouro');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_complemento_more_than_191_chars()
    // {
    //     $faker = \Faker\Factory::create();
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'complemento' => $faker->sentence(400)
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('complemento');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_cidade_more_than_191_chars()
    // {
    //     $faker = \Faker\Factory::create();
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'cidade' => $faker->sentence(400)
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('cidade');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_numero_more_than_10_chars()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'numero' => '012345678910'
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('numero');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_cidade_with_numbers()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'cidade' => 'Teste 9ove'
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('cidade');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_uf_more_than_2_chars_and_value_wrong()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'uf' => 'SSP'
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('uf');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_tipo_telefone_value_wrong()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'tipo_telefone' => 'KKKKKK'
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('tipo_telefone');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_opcional_celular_value_wrong()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => ['KKKKKK', 'SMS']
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('opcional_celular');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_telefone_more_than_20_chars_and_value_wrong()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'telefone' => '(112) 988886-2233'
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('telefone');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_empty_telefone_optional_if_tipo_telefone_optional_full()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'tipo_telefone_1' => tipos_contatos()[0]
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('telefone_1');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_empty_tipo_telefone_optional_if_telefone_optional_full()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'telefone_1' => '(11) 99898-8963'
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('tipo_telefone_1');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_tipo_telefone_optional_value_wrong()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'telefone_1' => '(11) 99898-8963',
    //         'tipo_telefone_1' => 'KKKKKK'
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('tipo_telefone_1');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_opcional_celular_1_value_wrong()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular_1' => ['KKKKKK', 'SMS']
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('opcional_celular_1');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_telefone_optional_more_than_20_chars_and_value_wrong()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'pergunta' => 'teste',
    //         'opcional_celular' => null,
    //         'telefone_1' => '(112) 988886-2233',
    //         'tipo_telefone_1' => tipos_contatos()[0]
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('telefone_1');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_without_pergunta()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'opcional_celular' => null,
    //         'pergunta' => ''
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('pergunta');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_pergunta_more_than_191_chars()
    // {
    //     $faker = \Faker\Factory::create();
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->state('low')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //         'opcional_celular' => null,
    //         'pergunta' => $faker->sentence(400)
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('pergunta');
    // }

    // /** 
    //  * =======================================================================================================
    //  * TESTES PRE-REGISTRO - ADMIN
    //  * =======================================================================================================
    //  */

    // /** @test */
    // public function view_list_pre_registros()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistro1 = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro2 = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '47662011089'
    //         ]),
    //         'contabil_id' => null,
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro3 = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '06985713000138'
    //         ]),
    //         'contabil_id' => null,
    //         'idregional' => $admin->idregional
    //     ]);
        
    //     $this->get(route('preregistro.index'))
    //     ->assertSeeText(formataCpfCnpj($preRegistro1->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj))
    //     ->assertSeeText($preRegistro1->userExterno->nome)
    //     ->assertSeeText($preRegistro2->userExterno->nome)
    //     ->assertSeeText($preRegistro3->userExterno->nome);
    // }

    // /** @test */
    // public function view_list_pre_registros_order_by_status()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistro1 = factory('App\PreRegistro')->state('analise_correcao')->create([
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro2 = factory('App\PreRegistro')->state('enviado_correcao')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '47662011089'
    //         ]),
    //         'contabil_id' => null,
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro3 = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '06985713000138'
    //         ]),
    //         'contabil_id' => null,
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro4 = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '86294373085'
    //         ]),
    //         'contabil_id' => null,
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro5 = factory('App\PreRegistro')->state('aprovado')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '11748345000144'
    //         ]),
    //         'contabil_id' => null,
    //         'idregional' => $admin->idregional
    //     ]);

    //     $this->get(route('preregistro.index'))
    //     ->assertSeeTextInOrder([
    //         formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj),
    //         formataCpfCnpj($preRegistro4->userExterno->cpf_cnpj),
    //         formataCpfCnpj($preRegistro1->userExterno->cpf_cnpj),
    //         formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj),
    //         formataCpfCnpj($preRegistro5->userExterno->cpf_cnpj)
    //     ]);
    // }

    // /** @test */
    // public function view_list_pre_registros_with_idregional_1_when_user_idregional_14()
    // {
    //     factory('App\Regional')->create([
    //         'idregional' => 14
    //     ]);
    //     $admin = $this->signInAsAdmin();
    //     $regionalAntiga = $admin->regional->regional;
    //     $admin->update(['idregional' => 14]);

    //     $preRegistro1 = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'idregional' => $admin->idregional
    //     ]);
        
    //     $this->get(route('preregistro.index'))
    //     ->assertSeeText($regionalAntiga);
    // }

    // /** @test */
    // public function view_button_editar_with_status_analise_inicial_or_analise_da_correcao()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistro = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'idregional' => $admin->idregional
    //     ]);
        
    //     $this->get(route('preregistro.index'))
    //     ->assertSee('class="btn btn-sm btn-primary">Editar</a> ');
    // }

    // /** @test */
    // public function view_button_visualizar_with_status_different_analise_inicial_or_analise_da_correcao()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistro = factory('App\PreRegistro')->create([
    //         'idregional' => $admin->idregional
    //     ]);
        
    //     foreach(PreRegistro::getStatus() as $status)
    //         if(!in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
    //         {
    //             $preRegistro->update(['idusuario' => $admin->idusuario, 'status' => $status]);
    //             $this->get(route('preregistro.index'))
    //             ->assertSee('class="btn btn-sm btn-info">Visualizar</a> ');
    //         }
    // }

    // /** @test */
    // public function view_msg_atualizado_por()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistro = factory('App\PreRegistro')->create([
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro->update(['idusuario' => $admin->idusuario]);
        
    //     foreach(PreRegistro::getStatus() as $status)
    //     {
    //         $preRegistro->update(['status' => $status]);
    //         $this->get(route('preregistro.index'))
    //         ->assertSee('<small class="d-block">Atualizado por: <strong>'.$admin->nome.'</strong></small>');
    //     }
    // }

    // /** @test */
    // public function can_filtro_by_regional()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistro = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro2 = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '47662011089'
    //         ]),
    //         'contabil_id' => null,
    //     ]);
    //     $preRegistro3 = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '06985713000138'
    //         ]),
    //         'contabil_id' => null,
    //     ]);
        
    //     $this->get(route('preregistro.filtro', ['regional' => $admin->idregional]))
    //     ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

    //     $this->get(route('preregistro.filtro', ['regional' => $preRegistro2->idregional]))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

    //     $this->get(route('preregistro.filtro', ['regional' => $preRegistro3->idregional]))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));
    // }

    // /** @test */
    // public function can_filtro_by_status()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistro = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro2 = factory('App\PreRegistro')->state('enviado_correcao')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '47662011089'
    //         ]),
    //         'contabil_id' => null,
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro3 = factory('App\PreRegistro')->state('analise_correcao')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '06985713000138'
    //         ]),
    //         'contabil_id' => null,
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro4 = factory('App\PreRegistro')->state('aprovado')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '86294373085'
    //         ]),
    //         'contabil_id' => null,
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro5 = factory('App\PreRegistro')->state('negado')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '11748345000144'
    //         ]),
    //         'contabil_id' => null,
    //         'idregional' => $admin->idregional
    //     ]);
        
    //     $this->get(route('preregistro.filtro', ['status' => $preRegistro->status]))
    //     ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro4->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro5->userExterno->cpf_cnpj));

    //     $this->get(route('preregistro.filtro', ['status' => $preRegistro2->status]))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro4->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro5->userExterno->cpf_cnpj));

    //     $this->get(route('preregistro.filtro', ['status' => $preRegistro3->status]))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro4->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro5->userExterno->cpf_cnpj));

    //     $this->get(route('preregistro.filtro', ['status' => $preRegistro4->status]))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro4->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro5->userExterno->cpf_cnpj));

    //     $this->get(route('preregistro.filtro', ['status' => $preRegistro5->status]))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro4->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro5->userExterno->cpf_cnpj));
    // }

    // /** @test */
    // public function can_filtro_by_regional_and_status()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistro = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro2 = factory('App\PreRegistro')->state('enviado_correcao')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '47662011089'
    //         ]),
    //         'contabil_id' => null,
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro3 = factory('App\PreRegistro')->state('analise_correcao')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '06985713000138'
    //         ]),
    //         'contabil_id' => null,
    //     ]);
        
    //     $this->get(route('preregistro.filtro', ['regional' => $admin->idregional, 'status' => $preRegistro->status]))
    //     ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

    //     $this->get(route('preregistro.filtro', ['regional' => $admin->idregional, 'status' => $preRegistro2->status]))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

    //     $this->get(route('preregistro.filtro', ['regional' => $preRegistro3->idregional, 'status' => $preRegistro3->status]))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));
    // }

    // /** @test */
    // public function can_filtro_by_regional_and_status_but_not_find()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistro = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro2 = factory('App\PreRegistro')->state('enviado_correcao')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '47662011089'
    //         ]),
    //         'contabil_id' => null,
    //     ]);
        
    //     $this->get(route('preregistro.filtro', ['regional' => $preRegistro2->idregional, 'status' => $preRegistro->status]))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertSeeText('Nenhum pré-registro encontrado');
    // }

    // /** @test */
    // public function search()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistro = factory('App\PreRegistro')->state('analise_inicial')->create([
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro2 = factory('App\PreRegistro')->state('enviado_correcao')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '47662011089'
    //         ]),
    //         'contabil_id' => null,
    //         'idregional' => $admin->idregional
    //     ]);
    //     $preRegistro3 = factory('App\PreRegistro')->state('analise_correcao')->create([
    //         'user_externo_id' => factory('App\UserExterno')->create([
    //             'cpf_cnpj' => '06985713000138'
    //         ]),
    //         'contabil_id' => null,
    //     ]);
        
    //     $this->get(route('preregistro.busca', ['q' => 1]))
    //     ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

    //     $this->get(route('preregistro.busca', ['q' => $preRegistro3->userExterno->nome]))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

    //     $this->get(route('preregistro.busca', ['q' => $preRegistro2->userExterno->cpf_cnpj]))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

    //     $this->get(route('preregistro.busca', ['q' => '198.']))
    //     ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));

    //     $this->get(route('preregistro.busca', ['q' => '????']))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro2->userExterno->cpf_cnpj))
    //     ->assertDontSeeText(formataCpfCnpj($preRegistro3->userExterno->cpf_cnpj));
    // }

    // /** @test */
    // public function view_button_update_status_with_status_analise_inicial_or_analise_correcao()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();

    //     $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
    //     ->assertSee('<i class="fas fa-check"></i> Aprovar')
    //     ->assertSee('<i class="fas fa-times"></i> Enviar para correção')
    //     ->assertSee('<i class="fas fa-ban"></i> Negar');

    //     $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
    //     $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
    //     ->assertSee('<i class="fas fa-check"></i> Aprovar')
    //     ->assertSee('<i class="fas fa-times"></i> Enviar para correção')
    //     ->assertSee('<i class="fas fa-ban"></i> Negar');
    // }

    // /** @test */
    // public function cannot_view_button_update_status_with_status_different_analise_inicial_or_analise_correcao()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->create();

    //     foreach(PreRegistro::getStatus() as $status)
    //         if(!in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
    //         {
    //             $preRegistroCpf->preRegistro->update(['status' => $status]);
    //             $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
    //             ->assertDontSee('<i class="fas fa-check"></i> Aprovar')
    //             ->assertDontSee('<i class="fas fa-times"></i> Enviar para correção')
    //             ->assertDontSee('<i class="fas fa-ban"></i> Negar');
    //         }
    // }

    // /** @test */
    // public function view_justificativa_with_status_negado()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();

    //     $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_NEGADO, 'justificativa' => json_encode(['negado' => 'teste verificando'])]);
    //     $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
    //     ->assertSee($preRegistroCpf->preRegistro->getJustificativaNegado());
    // }

    // /** @test */
    // public function view_anexos()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->create();
    //     $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);
    //     $preRegistro = $preRegistroCpf->preRegistro;
    //     $anexo = factory('App\Anexo')->state('pre_registro')->create([
    //         'pre_registro_id' => $preRegistroCpf->pre_registro_id
    //     ]);
        
    //     $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
    //     ->assertSeeText($anexo->nome_original);
    // }

    // /** @test */
    // public function view_pre_registro()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->create();
    //     $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);
    //     $preRegistro = $preRegistroCpf->preRegistro;
        
    //     $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
    //     ->assertSeeText(formataCpfCnpj($preRegistro->userExterno->cpf_cnpj))
    //     ->assertSeeText($preRegistro->userExterno->nome)
    //     ->assertSeeText($preRegistro->regional->regional)
    //     ->assertSeeText($preRegistro->segmento)
    //     ->assertSeeText($preRegistro->registro_secundario)
    //     ->assertSeeText($preRegistro->cep)
    //     ->assertSeeText($preRegistro->logradouro)
    //     ->assertSeeText($preRegistro->numero)
    //     ->assertSeeText($preRegistro->complemento)
    //     ->assertSeeText($preRegistro->bairro)
    //     ->assertSeeText($preRegistro->cidade)
    //     ->assertSeeText($preRegistro->uf)
    //     ->assertSeeText(explode(';', $preRegistro->telefone)[0])
    //     ->assertSeeText(explode(';', $preRegistro->tipo_telefone)[0])
    //     ->assertSeeText(implode(', ', $preRegistro->getOpcionalCelular()[0]));
    // }

    // /** @test */
    // public function view_text_justificado()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
    //     $justificativas = $preRegistroCpf->preRegistro->getJustificativaArray();
    //     $anexo = factory('App\Anexo')->state('pre_registro')->create([
    //         'pre_registro_id' => $preRegistroCpf->pre_registro_id
    //     ]);

    //     $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
    //     ->assertSeeText($justificativas['idregional'])
    //     ->assertSeeText($justificativas['segmento'])
    //     ->assertSeeText($justificativas['cep'])
    //     ->assertSeeText($justificativas['logradouro'])
    //     ->assertSeeText($justificativas['numero'])
    //     ->assertSeeText($justificativas['complemento'])
    //     ->assertSeeText($justificativas['bairro'])
    //     ->assertSeeText($justificativas['cidade'])
    //     ->assertSeeText($justificativas['uf'])
    //     ->assertSeeText($justificativas['telefone'])
    //     ->assertSeeText($justificativas['tipo_telefone'])
    //     ->assertSeeText($justificativas['opcional_celular'])
    //     ->assertSeeText($justificativas['path']);
    // }
}
