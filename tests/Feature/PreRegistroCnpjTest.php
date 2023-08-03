<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\PreRegistro;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Anexo;

class PreRegistroCnpjTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_new_pre_registro_pj()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.preregistro.view'))->assertOk();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = $externo->load('preRegistro')->preRegistro;

        $this->assertDatabaseHas('pre_registros', [
            'id' => $preRegistro->id,
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'id' => $preRegistro->pessoaJuridica->id,
        ]);
    }

    /** @test */
    public function cannot_new_pre_registro_pj_without_check()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.preregistro.view'))->assertOk();
        $this->get(route('externo.inserir.preregistro.view'))
        ->assertRedirect(route('externo.preregistro.view'));
    }

    /** @test */
    public function cannot_new_pre_registro_pj_when_access_others_routes()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.preregistro.view'))->assertOk();

        $this->assertEquals(PreRegistro::count(), 0);
        $pr = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => factory('App\PreRegistro')->raw([
                'user_externo_id' => 1
            ])
        ]);

        $this->post(route('externo.inserir.preregistro.ajax', [
            'classe' => 'preRegistro',
            'campo' => 'segmento',
            'valor' => 'Abrasivos'
            ]))->assertStatus(401);
        $this->assertEquals(PreRegistro::count(), 0);

        $this->put(route('externo.verifica.inserir.preregistro', $pr))->assertRedirect(route('externo.preregistro.view'));
        $this->assertEquals(PreRegistro::count(), 0);

        $this->put(route('externo.inserir.preregistro', $pr))->assertRedirect(route('externo.preregistro.view'));
        $this->assertEquals(PreRegistro::count(), 0);
    }

    /** @test */
    public function log_is_generated_when_form_cnpj_is_created()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));     
        $pr = PreRegistro::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário Externo com cnpj: ' . $pr->userExterno->cpf_cnpj.', iniciou o processo de solicitação de registro com a id: ' . $pr->id;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function view_msg_update()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));

        PreRegistro::first()->update(['updated_at' => PreRegistro::first()->updated_at->subHour()]);
        $atual = PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s');

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica',
            'campo' => 'numero',
            'valor' => '223'
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
        $this->assertNotEquals($atual, PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    }

    /** @test */
    public function can_create_new_register_pre_registros_cnpj_after_negado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'status' => 'Negado'
            ]),
        ]);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'pre_registro_id' => 2
        ]);
    }

    /** @test */
    public function cannot_create_new_register_pre_registros_cnpj_after_aprovado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'status' => 'Aprovado'
            ]),
        ]);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));
        
        $this->assertDatabaseMissing('pre_registros_cnpj', [
            'pre_registro_id' => 2
        ]);
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CNPJ VIA AJAX - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj->makeVisible(['pre_registro_id'])->attributesToArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_with_upperCase()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('low')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => in_array($key, $endereco) ? $key.'_empresa' : $key,
                'valor' => $value
            ])->assertStatus(200);
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $preRegistroCnpj[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj->attributesToArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_exists_others_pre_registros()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '67779004000190'
                ])
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '56821972000100'
                ])
            ])
        ]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id,
            'responsavel_tecnico_id' => null,
        ]);
        
        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => in_array($key, $endereco) ? $key.'_empresa' : $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj->makeVisible(['pre_registro_id'])->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_exists_others_pre_registros_with_same_user_and_negado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Negado'
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Negado'
            ])
        ]);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->make([
            'pre_registro_id' => factory('App\PreRegistro')->make([
                'user_externo_id' => 1,
                'id' => 3
            ]),
            'responsavel_tecnico_id' => null,
        ]);
        
        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => in_array($key, $endereco) ? $key.'_empresa' : $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj->makeVisible(['pre_registro_id'])->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_when_exists_others_pre_registros_with_same_user()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Negado'
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Aprovado'
            ])
        ]);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->make([
            'pre_registro_id' => factory('App\PreRegistro')->make([
                'user_externo_id' => 1,
            ]),
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
        ]);
        
        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => in_array($key, $endereco) ? $key.'_empresa' : $key,
                'valor' => $value
            ])->assertStatus(401);
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $preRegistroCnpj->makeVisible(['pre_registro_id'])->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $preRegistroCnpj->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];

        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => in_array($key, $endereco) ? $key.'_empresa' : $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $preRegistroCnpj->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridicaErro',
                'campo' => in_array($key, $endereco) ? $key.'_empresa' : $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $preRegistroCnpj->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $preRegistroCnpj->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_with_input_type_text_more_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = [
            'razao_social' => $faker->text(500),
            'inscricao_municipal' => $faker->text(500),
            'inscricao_estadual' => $faker->text(500),
            'capital_social' => $faker->text(500),
            'logradouro' => $faker->text(500),
            'complemento' => $faker->text(500),
            'bairro' => $faker->text(500),
            'cidade' => $faker->text(500),
        ];

        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        
        foreach($preRegistroCnpj as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => in_array($key, $endereco) ? $key.'_empresa' : $key,
                'valor' => $value
            ])->assertSessionHasErrors('valor');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $preRegistroCnpj);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cnpj_by_ajax_with_dt_inicio_atividade_after_today()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica',
            'campo' => 'dt_inicio_atividade',
            'valor' => Carbon::today()->addDay()->format('Y-m-d')
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'dt_inicio_atividade' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cnpj_by_ajax_without_date_type()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica',
            'campo' => 'dt_inicio_atividade',
            'valor' => 'texto'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'dt_inicio_atividade' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cnpj_by_ajax_with_tipo_empresa_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica',
            'campo' => 'tipo_empresa',
            'valor' => 'texto'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'tipo_empresa' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cnpj_by_ajax_with_uf_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica',
            'campo' => 'uf_empresa',
            'valor' => 'FF'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'uf' => null
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_clean_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
        
        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];

        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => in_array($key, $endereco) ? $key.'_empresa' : $key,
                'valor' => ''
            ])->assertStatus(200);
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $preRegistroCnpj->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_with_blocked_historico_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null,
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => '28819854082'
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1,
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => '47662011089'
        ])->assertOk();

        $this->assertDatabaseMissing('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 2,
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_not_blocked_historico_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null,
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => '28819854082'
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1,
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_empty_cnpj_contabil_and_blocked_historico_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => '28819854082'
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1,
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => ''
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null,
        ]);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                foreach($preRegistroCnpj->attributesToArray() as $key => $value)
                    $this->post(route('externo.inserir.preregistro.ajax'), [
                        'classe' => 'pessoaJuridica',
                        'campo' => $key,
                        'valor' => ''
                    ])->assertStatus(401);
        }
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
        
        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            foreach($preRegistroCnpj->attributesToArray() as $key => $value)
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'pessoaJuridica',
                    'campo' => $key,
                    'valor' => ''
                ])->assertStatus(200);
        }
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CNPJ VIA SUBMIT - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function view_message_errors_when_submit_pf()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $dados = [
            'idregional' => null,'segmento' => '1','cep' => null,'logradouro' => null,'numero' => null,'bairro' => null,
            'cidade' => null,'uf' => null,'telefone' => null,'tipo_telefone' => null,'opcional_celular.*' => ['S'],
            'tipo_telefone_1' => '1','telefone_1' => '(1)','opcional_celular_1.*' => ['S'],'razao_social' => null,
            'tipo_empresa' => null,'dt_inicio_atividade' => null,'inscricao_municipal' => '1','inscricao_estadual' => '1',
            'capital_social' => null,'cep_empresa' => null,'logradouro_empresa' => null,'numero_empresa' => null,'bairro_empresa' => null,
            'cidade_empresa' => null,'uf_empresa' => null,'cpf_rt' => '1','nome_rt' => null,'sexo_rt' => null,'dt_nascimento_rt' => null,
            'cep_rt' => null,'logradouro_rt' => null,'numero_rt' => null,'bairro_rt' => null,'cidade_rt' => null,'uf_rt' => null,
            'nome_mae_rt' => null,'tipo_identidade_rt' => null,'identidade_rt' => null,'orgao_emissor_rt' => null,'dt_expedicao_rt' => null,
            'path' => null,'pergunta' => '1',
        ];

        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);
    }

    /** @test */
    public function view_message_errors_when_submit_when_checkEndEmpresa_on()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = [
            'idregional' => null,'segmento' => '1','cep' => null,'logradouro' => null,'numero' => null,
            'bairro' => null,'cidade' => null,'uf' => null,'telefone' => null,'tipo_telefone' => null,
            'opcional_celular.*' => ['S'],'tipo_telefone_1' => '1','telefone_1' => '(1)','opcional_celular_1.*' => ['S'],
            'razao_social' => null,'tipo_empresa' => null,'dt_inicio_atividade' => null,'inscricao_municipal' => '1',
            'inscricao_estadual' => '1','capital_social' => null,'checkEndEmpresa' => 'on','cep_empresa' => null,
            'logradouro_empresa' => null,'numero_empresa' => null,'bairro_empresa' => null,'cidade_empresa' => null,
            'uf_empresa' => null,'cpf_rt' => '1','nome_rt' => null,'sexo_rt' => null,'dt_nascimento_rt' => null,
            'cep_rt' => null,'logradouro_rt' => null,'numero_rt' => null,'bairro_rt' => null,'cidade_rt' => null,
            'uf_rt' => null,'nome_mae_rt' => null,'tipo_identidade_rt' => null,'identidade_rt' => null,
            'orgao_emissor_rt' => null,'dt_expedicao_rt' => null,'path' => null,'pergunta' => '1',
        ];

        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);
    }

    /** @test */
    public function can_submit_pre_registro_cnpj()
    {
        Mail::fake();
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $pr = factory('App\PreRegistroCnpj')->states('request')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        $pr['numero'] = $dados['numero_empresa'];
        Anexo::first()->delete();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')]
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertOk();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSee('<button type="button" class="btn btn-success" id="submitPreRegistro" value="">Enviar</button>'); 

        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));
        
        Mail::assertQueued(PreRegistroMail::class);

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cnpj', $pr);

        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'random.pdf'
        ]);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
        Storage::disk('local')->assertExists(PreRegistro::find(1)->anexos->first()->path);
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_with_checkEndEmpresa_on()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $pr = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        Anexo::first()->delete();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')]
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertOk();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSee('<button type="button" class="btn btn-success" id="submitPreRegistro" value="">Enviar</button>'); 

        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));
        
        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cnpj', $pr);
        $this->assertDatabaseHas('pre_registros', [
            'cep' => $pr['cep'], 'logradouro' => $pr['logradouro'], 'bairro' => $pr['bairro'], 'cidade' => $pr['cidade'], 
            'uf' => $pr['uf'], 'complemento' => $pr['complemento']
        ]);

        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'random.pdf'
        ]);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
        Storage::disk('local')->assertExists(PreRegistro::find(1)->anexos->first()->path);
    }

    /** @test */
    public function can_submit_pre_registros_cnpj_when_exists_others_pre_registros()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->states('low')->create(),
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '67779004000190'
                ])
            ])
        ])->attributesToArray();

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => 1,
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '56821972000100'
                ])
            ])
        ])->attributesToArray();

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    
        $pr = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cnpj', $pr);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());
    }

    /** @test */
    public function can_submit_pre_registros_cnpj_when_exists_others_pre_registros_with_same_user_and_negado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->states('low')->create(),
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Negado'
            ])
        ])->attributesToArray();

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => 1,
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Negado'
            ])
        ])->attributesToArray();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    
        $pr = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cnpj', $pr);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());
    }

    /** @test */
    public function cannot_submit_pre_registros_cnpj_when_exists_others_pre_registros_with_same_user()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->states('low')->create(),
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Negado'
            ])
        ])->attributesToArray();

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => 1,
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Aprovado'
            ])
        ])->attributesToArray();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));
    
        $pr = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        Anexo::first()->delete();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('path');

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseMissing('pre_registros_cnpj', $pr);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2);
        $this->assertDatabaseMissing('pre_registros_cnpj', ['id' => 3]);
    }

    /** @test */
    public function can_submit_pre_registros_cnpj_when_exists_others_pre_registros_with_same_contabil_and_rt()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->states('low')->create(),
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '67779004000190'
                ])
            ])
        ])->attributesToArray();

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => 1,
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '56821972000100'
                ])
            ])
        ])->attributesToArray();

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $pr = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cnpj', $pr);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_without_optional_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $prCnpj = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make();
        $dados = $prCnpj->final;
        $pr = $prCnpj->preRegistro->attributesToArray();
        $prCnpj = $prCnpj->makeHidden(['final'])->attributesToArray();

        $pr['contabil_id'] = null;
        $pr['segmento'] = null;
        $pr['opcional_celular'] = ';';
        $pr['telefone'] = '(11) 00000-0000;';
        $pr['tipo_telefone'] = 'CELULAR;';
        $prCnpj['nire'] = null;
        $prCnpj['inscricao_municipal'] = null;
        $prCnpj['inscricao_estadual'] = null;

        $dados['contabil_id'] = null;
        $dados['cnpj_contabil'] = null;
        $dados['nome_contabil'] = null;
        $dados['email_contabil'] = null;
        $dados['nome_contato_contabil'] = null;
        $dados['telefone_contabil'] = null;
        $dados['segmento'] = null;
        $dados['opcional_celular'] = [];
        $dados['telefone_1'] = null;
        $dados['tipo_telefone_1'] = null;
        $dados['opcional_celular_1'] = [];
        $dados['nire'] = null;
        $dados['inscricao_municipal'] = null;
        $dados['inscricao_estadual'] = null;
        $dados['nome_social_rt'] = null;
        $dados['complemento_rt'] = null;
        $dados['nome_pai_rt'] = null;
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));
        
        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros', $pr);

        foreach($prCnpj as $key1 => $value1)
            $prCnpj[$key1] = isset($value1) ? mb_strtoupper($value1, 'UTF-8') : $value1;
        $this->assertDatabaseHas('pre_registros_cnpj', $prCnpj);
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_required_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = [
            'path' => '','idregional' => '','cep' => '','bairro' => '','logradouro' => '','numero' => '','cidade' => '',
            'uf' => '','tipo_telefone' => '','telefone' => '','razao_social' => '','capital_social' => '','tipo_empresa' => '',
            'dt_inicio_atividade' => '','cep_empresa' => '','bairro_empresa' => '','logradouro_empresa' => '','numero_empresa' => '',
            'cidade_empresa' => '','uf_empresa' => '','nome_rt' => '','sexo_rt' => '','dt_nascimento_rt' => '','cpf_rt' => '',
            'tipo_identidade_rt' => '','identidade_rt' => '','orgao_emissor_rt' => '','dt_expedicao_rt' => '','cep_rt' => '',
            'bairro_rt' => '','logradouro_rt' => '','numero_rt' => '','cidade_rt' => '','uf_rt' => '','nome_mae_rt' => '',
        ];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors([
            'path','idregional','cep','bairro','logradouro','numero','cidade','uf','tipo_telefone','telefone','razao_social',
            'capital_social','tipo_empresa','dt_inicio_atividade','cep_empresa','bairro_empresa','logradouro_empresa',
            'numero_empresa','cidade_empresa','uf_empresa','nome_rt','sexo_rt','dt_nascimento_rt','cpf_rt','tipo_identidade_rt',
            'identidade_rt','orgao_emissor_rt','dt_expedicao_rt','cep_rt','bairro_rt','logradouro_rt','numero_rt','cidade_rt',
            'uf_rt','nome_mae_rt',
        ]);

        $pr = $externo->load('preRegistro')->preRegistro;

        $this->assertDatabaseHas('pre_registros', $pr->toArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $pr->pessoaJuridica->toArray());
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.pdf'
        ]);
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_razao_social()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['razao_social'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_razao_social_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['razao_social'] = 'Razã';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_razao_social_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['razao_social'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_razao_social_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['razao_social'] = 'Raz4o S0cial';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_capital_social()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['capital_social'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('capital_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_capital_social_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['capital_social'] = '0,0';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('capital_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_capital_social_more_than_16_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['capital_social'] = '1.000.000.000.0,00';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('capital_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_capital_social_wrong_value()
    {
        $capitalSocial = ['0000', '0,00', '01,00', '1,0,00', '1,000', '1000'];
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;        
        foreach($capitalSocial as $val){
            $dados['capital_social'] = $val;
            $this->put(route('externo.verifica.inserir.preregistro'), $dados)
            ->assertSessionHasErrors('capital_social');
        }
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nire_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['nire'] = '1234';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nire');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nire_more_than_20_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['nire'] = '123456789012345678901';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nire');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_tipo_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['tipo_empresa'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_tipo_empresa_value_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['tipo_empresa'] = 'Teste';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_dt_inicio_atividade()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['dt_inicio_atividade'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_inicio_atividade_without_date_type()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['dt_inicio_atividade'] = 'texto';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_inicio_atividade_incorrect_format()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['dt_inicio_atividade'] = '2000/12/25';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_inicio_atividade_after_today()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['dt_inicio_atividade'] = Carbon::today()->addDay()->format('Y-m-d');
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_inscricao_municipal_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['inscricao_municipal'] = '1234';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('inscricao_municipal');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_inscricao_municipal_more_than_30_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['inscricao_municipal'] = '1234567890123456789012345678901';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('inscricao_municipal');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_inscricao_estadual_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['inscricao_estadual'] = '1234';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('inscricao_estadual');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_inscricao_estadual_more_than_30_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['inscricao_estadual'] = '1234567890123456789012345678901';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('inscricao_estadual');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_cep_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cep_empresa'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cep_empresa_more_than_9_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cep_empresa'] = '01234-0123';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cep_empresa_incorrect_format()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cep_empresa'] = '012340123';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_bairro_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['bairro_empresa'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_bairro_empresa_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['bairro_empresa'] = 'São';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_bairro_empresa_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['bairro_empresa'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_logradouro_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['logradouro_empresa'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_logradouro_empresa_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['logradouro_empresa'] = 'Rua';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_logradouro_empresa_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['logradouro_empresa'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_numero_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['numero_empresa'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('numero_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_numero_empresa_more_than_10_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['numero_empresa'] = '12345678901';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('numero_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_complemento_empresa_more_than_50_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['complemento_empresa'] = $faker->text(300);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('complemento_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_cidade_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cidade_empresa'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cidade_empresa_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cidade_empresa'] = 'San';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cidade_empresa_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cidade_empresa'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cidade_empresa_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['cidade_empresa'] = 'S4ntos';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_uf_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['uf_empresa'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('uf_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_wrong_uf_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados['uf_empresa'] = 'PP';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('uf_empresa');
    }

    /** @test */
    public function log_is_generated_when_form_pj_is_submitted()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;

        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        $pr = PreRegistro::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário Externo com cnpj: ' . $pr->userExterno->cpf_cnpj;
        $txt .= ', atualizou o status para ' . $pr::STATUS_ANALISE_INICIAL . ' da solicitação de registro com a id: ' . $pr->id;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_status_different_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->states('low')->create(),
        ])->preRegistro;
        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO]))
                in_array($status, [PreRegistro::STATUS_APROVADO, PreRegistro::STATUS_NEGADO]) ? 
                $this->put(route('externo.inserir.preregistro'), $dados)->assertSessionHasErrors('path') : 
                $this->put(route('externo.inserir.preregistro'), $dados)->assertStatus(401);
        }
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_with_status_aguardando_correcao_or_sendo_elaborado()
    {
        Mail::fake();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->states('low')->create(),
        ])->preRegistro;

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;

        $s = [PreRegistro::STATUS_CRIADO => PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_CORRECAO => PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach([PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO] as $status)
        {
            $preRegistro->update(['status' => $status]);
            if($status == PreRegistro::STATUS_CORRECAO)
                $dados['nire'] = '65439';
            $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));
            Mail::assertQueued(PreRegistroMail::class);
            $this->assertEquals(PreRegistro::first()->status, $s[$status]);
        }
    }

    /** @test */
    public function log_is_generated_when_status_aguardando_correcao()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()->id,
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->states('low')->create(),
        ])->preRegistro;

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário Externo com cnpj: ' . $preRegistro->userExterno->cpf_cnpj;
        $txt .= ', atualizou o status para ' . PreRegistro::STATUS_ANALISE_CORRECAO . ' da solicitação de registro com a id: ' . $preRegistro->id;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function filled_campos_espelho_when_form_pj_is_submitted()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on'])); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['path'] = null;

        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        $pr = PreRegistro::first();
        $arrayFinal = array_diff(array_keys(json_decode($pr->campos_espelho, true)), array_keys($dados));
        $this->assertEquals($arrayFinal, array());
    }

    /** @test */
    public function filled_campos_editados_pre_registros_cnpj_when_form_is_submitted_when_status_aguardando_correcao()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $PreRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()->id,
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->states('low')->create(),
        ])->makeHidden(['pre_registro_id', 'created_at', 'updated_at', 'id']);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;

        $dados['razao_social'] = 'Razão Social';
        $dados['nire'] = '1988963';
        $dados['tipo_empresa'] = tipos_empresa()[2];
        $dados['dt_inicio_atividade'] = '2019-12-10';
        $dados['inscricao_municipal'] = null;
        $dados['inscricao_estadual'] = null;
        $dados['capital_social'] = '5.000,00';

        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        $pr = PreRegistro::first();
        $dados = Arr::except($dados, ['final', 'created_at', 'updated_at', 'deleted_at', 'pergunta']);

        $arrayFinal = array_diff(array_keys($dados), array_keys(json_decode($pr->campos_espelho, true)));
        $this->assertEquals($arrayFinal, array());
        $temp = array_keys(Arr::except($PreRegistroCnpj->attributesToArray(), ['cep','logradouro','numero','complemento','bairro','cidade','uf']));
        $arrayFinal = array_diff($temp, array_keys($pr->getCamposEditados()));
        $this->assertEquals($arrayFinal, array());
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CNPJ VIA AJAX - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function can_update_justificativa()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create([
                'telefone' => '(11) 00000-0000;(12) 00000-111',
                'tipo_telefone' => mb_strtoupper(tipos_contatos()[0].';' . tipos_contatos()[0], 'UTF-8'),
                'opcional_celular' => mb_strtoupper(opcoes_celular()[1] . ';' . opcoes_celular()[2], 'UTF-8'),
            ])
        ]);

        $dados = factory('App\PreRegistroCnpj')->states('request')->make()->final;
        $dados = Arr::except($dados, ['final', 'created_at', 'updated_at', 'deleted_at', 'pergunta']);

        $justificativas = array();
        foreach($dados as $campo => $valor)
        {
            $texto = $faker->text(500);
            $justificativas[$campo] = $texto;
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $texto
            ])->assertStatus(200);   
            
            $this->assertEquals(PreRegistro::first()->getJustificativaArray(), $justificativas);
            $this->assertEquals(PreRegistro::first()->idusuario, $admin->idusuario);
        }

        $this->assertDatabaseHas('pre_registros', [
            'justificativa' => json_encode($justificativas, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function can_update_justificativa_with_status_em_analise_or_analise_da_correcao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create([
                'telefone' => '(11) 00000-0000;(12) 00000-111',
                'tipo_telefone' => mb_strtoupper(tipos_contatos()[0].';' . tipos_contatos()[0], 'UTF-8'),
                'opcional_celular' => mb_strtoupper(opcoes_celular()[1] . ';' . opcoes_celular()[2], 'UTF-8'),
            ])
        ]);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados = Arr::except($dados, ['final', 'created_at', 'updated_at', 'deleted_at', 'pergunta']);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            if(in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                foreach($dados as $campo => $valor)
                    $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                        'acao' => 'justificar',
                        'campo' => $campo,
                        'valor' => $faker->text(500)
                    ])->assertStatus(200);    
        }
    }

    /** @test */
    public function can_edit_justificativas()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = $preRegistroCnpj->preRegistro->getJustificativaArray();
        foreach($dados as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => ''
            ])->assertStatus(200);    

        $this->assertDatabaseHas('pre_registros', [
            'justificativa' => null,
            'idusuario' => $admin->idusuario
        ]);
    }

    /** @test */
    public function cannot_update_justificativa_more_than_500_chars()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = $preRegistroCnpj->preRegistro->getJustificativaArray();
        foreach($dados as $campo => $valor)
        {
            $texto = $faker->text(800);
            $justificativas[$campo] = $texto;
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $texto
            ])->assertSessionHasErrors('valor');
        }

        $this->assertDatabaseMissing('pre_registros', [
            'justificativa' => json_encode($justificativas, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_update_justificativa_with_wrong_inputs()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = $preRegistroCnpj->preRegistro->getJustificativaArray();
        foreach($dados as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo . '_erro',
                'valor' => $faker->text(500)
            ])->assertSessionHasErrors('campo');
    }

    /** @test */
    public function cannot_update_justificativa_with_wrong_input_acao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = $preRegistroCnpj->preRegistro->getJustificativaArray();
        foreach($dados as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar_',
                'campo' => $campo,
                'valor' => $faker->text(500)
            ])->assertSessionHasErrors('acao'); 
    }

    /** @test */
    public function cannot_update_justificativa_with_status_different_em_analise_or_analise_da_correcao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = Arr::only($preRegistroCnpj->preRegistro->getJustificativaArray(), ['segmento', 'path', 'nome_mae', 'nome_pai', 'tipo_identidade']);
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                foreach($dados as $campo => $valor)
                    $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                        'acao' => 'justificar',
                        'campo' => $campo,
                        'valor' => $faker->text(500)
                    ])->assertStatus(401);
                
        }
    }

    /** @test */
    public function log_is_generated_when_update_justificativa()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = $preRegistroCnpj->preRegistro->getJustificativaArray();
        foreach($dados as $campo => $valor)
        {
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $faker->text(500)
            ])->assertOk(); 

            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
            $txt = $inicio . 'Usuário (usuário 1) fez a ação de "justificar" o campo "' . $campo . '", ';
            $txt .= 'inserindo ou removendo valor *pré-registro* (id: '.$preRegistroCnpj->preRegistro->id.')';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function can_save_inputs()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $campos = ['registro' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo,
                'valor' => $valor
            ])->assertStatus(200);    

        $this->assertDatabaseHas('responsaveis_tecnicos', $campos);
    }

    /** @test */
    public function log_is_generated_when_save_inputs()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $campos = ['registro' => '000011234'];

        foreach($campos as $campo => $valor)
        {
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo,
                'valor' => $valor
            ])->assertStatus(200);  

            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
            $txt = $inicio . 'Usuário (usuário 1) fez a ação de "editar" o campo "' . $campo . '", ';
            $txt .= 'inserindo ou removendo valor *pré-registro* (id: '.$preRegistroCnpj->preRegistro->id.')';
            $this->assertStringContainsString($txt, $log);
        }  

        $this->assertDatabaseHas('responsaveis_tecnicos', $campos);
    }

    /** @test */
    public function can_clean_inputs_saved_after_update()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $campos = ['registro' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo,
                'valor' => $valor
            ])->assertStatus(200);    

        $this->assertDatabaseHas('responsaveis_tecnicos', $campos);

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo,
                'valor' => ''
            ])->assertStatus(200);    

        $this->assertDatabaseMissing('responsaveis_tecnicos', $campos);
    }

    /** @test */
    public function cannot_save_input_registro_with_more_than_20_chars()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'editar',
            'campo' => 'registro',
            'valor' => '000011234541235987532'
        ])->assertSessionHasErrors('valor');    
    }

    /** @test */
    public function cannot_save_inputs_with_wrong_action()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $campos = ['registro' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'editar_',
                'campo' => $campo,
                'valor' => $valor
            ])->assertSessionHasErrors('acao');    
    }

    /** @test */
    public function cannot_save_inputs_with_wrong_field()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $campos = ['registro' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo . '-',
                'valor' => $valor
            ])->assertSessionHasErrors('campo');     
    }

    /** @test */
    public function can_check_anexos()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $tipos = Anexo::first()->getOpcoesPreRegistro();

        $arrayAnexos = array();
        foreach($tipos as $tipo)
        {
            $arrayAnexos[$tipo] = "OK";
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'conferir',
                'campo' => 'confere_anexos[]',
                'valor' => $tipo
            ])->assertStatus(200);    

            $this->assertEquals(PreRegistro::first()->getConfereAnexosArray(), $arrayAnexos);
            $this->assertEquals(PreRegistro::first()->idusuario, $admin->idusuario);
        }
            
        $this->assertDatabaseHas('pre_registros', [
            'confere_anexos' => json_encode($arrayAnexos, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function log_is_generated_when_check_anexos()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $tipos = Anexo::first()->getObrigatoriosPreRegistro();

        $arrayAnexos = array();
        foreach($tipos as $tipo)
        {
            $arrayAnexos[$tipo] = "OK";
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'conferir',
                'campo' => 'confere_anexos[]',
                'valor' => $tipo
            ])->assertStatus(200);

            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
            $txt = $inicio . 'Usuário (usuário 1) fez a ação de "conferir" o campo "confere_anexos", ';
            $txt .= 'inserindo ou removendo valor *pré-registro* (id: '.$preRegistroCnpj->preRegistro->id.')';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function cannot_check_anexos_with_wrong_action()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $tipos = Anexo::first()->getOpcoesPreRegistro();

        $arrayAnexos = array();
        foreach($tipos as $tipo)
        {
            $arrayAnexos[$tipo] = "OK";
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'conferir_',
                'campo' => 'confere_anexos[]',
                'valor' => $tipo
            ])->assertSessionHasErrors('acao'); 
        }
    }

    /** @test */
    public function cannot_check_anexos_with_value_wrong()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $tipos = Anexo::first()->getOpcoesPreRegistro();

        $arrayAnexos = array();
        foreach($tipos as $tipo)
        {
            $arrayAnexos[$tipo] = "OK";
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'conferir',
                'campo' => 'confere_anexos[]',
                'valor' => $tipo . '-'
            ])->assertSessionHasErrors('valor'); 
        }
    }

    /** @test */
    public function cannot_check_anexos_with_wrong_field()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $tipos = Anexo::first()->getOpcoesPreRegistro();

        $arrayAnexos = array();
        foreach($tipos as $tipo)
        {
            $arrayAnexos[$tipo] = "OK";
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'conferir',
                'campo' => 'confere_anexos[]_',
                'valor' => $tipo
            ])->assertSessionHasErrors('campo'); 
        }
    }

    /** @test */
    public function cannot_check_anexos_without_anexo()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        Anexo::first()->delete();

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => 'CPF'
        ])->assertSessionHasErrors('valor'); 
    }

    /** @test */
    public function cannot_check_anexos_without_pre_registro()
    {
        $admin = $this->signInAsAdmin();

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => 'CPF'
        ])->assertNotFound();
    }

    /** @test */
    public function cannot_check_anexos_pre_registro_with_status_different_analise_inicial_or_analise_correcao()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => null]);
            if(!in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                    'acao' => 'conferir',
                    'campo' => 'confere_anexos[]',
                    'valor' => 'CPF'
                ])->assertSessionHasErrors('valor');
        }
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CNPJ VIA SUBMIT - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function can_update_status_enviar_para_correcao()
    {
        Mail::fake();
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $arrayAnexos = array();
        foreach(Anexo::first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCnpj->preRegistro->update(['confere_anexos' => $final]);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'corrigir'])
        ->assertRedirect(route('preregistro.index'));

        Mail::assertQueued(PreRegistroMail::class);

        $this->get(route('preregistro.index'))
        ->assertSeeText('Pré-registro com a ID: '.$preRegistroCnpj->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_CORRECAO.'" com sucesso');

        $this->assertDatabaseHas('pre_registros', [
            'status' => PreRegistro::STATUS_CORRECAO,
            'idusuario' => $admin->idusuario
        ]);
    }

    /** @test */
    public function can_update_status_enviar_para_correcao_without_confere_anexos()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'corrigir'])
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('Pré-registro com a ID: '.$preRegistroCnpj->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_CORRECAO.'" com sucesso');

        $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_CORRECAO);
    }

    /** @test */
    public function cannot_update_status_enviar_para_correcao_without_justificativa()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $preRegistroCnpj->preRegistro->update(['justificativa' => null]);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'corrigir'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCnpj->pre_registro_id))
        ->assertSeeText('Não possui justificativa(s)');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_CORRECAO);
    }

    /** @test */
    public function cannot_update_status_enviar_para_correcao_only_key_negado()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $preRegistroCnpj->preRegistro->update(['justificativa' => '{"negado":"teste"}']);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'corrigir'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCnpj->pre_registro_id))
        ->assertSeeText('Não possui justificativa(s)');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_CORRECAO);
    }

    /** @test */
    public function cannot_update_status_enviar_para_correcao_with_status_different_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO, PreRegistro::STATUS_CORRECAO];
        foreach(PreRegistro::getStatus() as $status)
            if(!in_array($status, $canUpdate))
            {
                $preRegistroCnpj->preRegistro->update(['status' => $status]);
                $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'corrigir'])
                ->assertSessionHasErrors('status');

                $this->get(route('preregistro.view', $preRegistroCnpj->pre_registro_id))
                ->assertSeeText('Não possui o status necessário para ser enviado para correção');

                $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_CORRECAO);
            }
    }

    /** @test */
    public function can_update_status_enviar_para_correcao_with_status_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach($canUpdate as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'corrigir'])
            ->assertRedirect(route('preregistro.index'));

            $this->get(route('preregistro.index'))
            ->assertSeeText('Pré-registro com a ID: '.$preRegistroCnpj->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_CORRECAO.'" com sucesso');

            $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_CORRECAO);
        }
    }

    /** @test */
    public function log_is_generated_when_update_status_enviar_para_correcao_with_status_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        
        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach($canUpdate as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'corrigir'])
            ->assertRedirect(route('preregistro.index'));

            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
            $txt = $inicio . 'Usuário (usuário 1) atualizou status para ' . PreRegistro::STATUS_CORRECAO . ' *pré-registro* (id: '.$preRegistroCnpj->preRegistro->id.')';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function can_update_status_negado()
    {
        Mail::fake();
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        foreach($anexo->first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
        ])->assertStatus(200); 

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'negar'])
        ->assertRedirect(route('preregistro.index'));

        Mail::assertQueued(PreRegistroMail::class);

        $this->get(route('preregistro.index'))
        ->assertSeeText('Pré-registro com a ID: '.$preRegistroCnpj->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_NEGADO.'" com sucesso');

        $this->assertDatabaseHas('pre_registros', [
            'status' => PreRegistro::STATUS_NEGADO,
            'idusuario' => $admin->idusuario
        ]);

        $this->assertDatabaseMissing('anexos', [
            'path' => $anexo->path,
            'pre_registro_id' => $anexo->pre_registro_id
        ]);
    }

    /** @test */
    public function log_is_generated_when_update_status_negado()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
        ])->assertStatus(200); 

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'negar'])
        ->assertRedirect(route('preregistro.index'));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário (usuário 1) atualizou status para ' . PreRegistro::STATUS_NEGADO;
        $txt .= ' e seus arquivos foram excluídos pelo sistema *pré-registro* (id: '.$preRegistroCnpj->preRegistro->id.')';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function can_update_status_negado_without_confere_anexos()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
        ])->assertStatus(200); 

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'negar'])
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('Pré-registro com a ID: '.$preRegistroCnpj->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_NEGADO.'" com sucesso');

        $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
    }

    /** @test */
    public function cannot_update_status_negado_without_justificativa_negado()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        $tipos = $anexo->first()->getOpcoesPreRegistro();
        foreach($tipos as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'negar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCnpj->pre_registro_id))
        ->assertSeeText('Não possui justificativa(s)');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
    }

    /** @test */
    public function cannot_update_status_negado_with_others_justificativa_and_without_negado()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'negar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCnpj->pre_registro_id))
        ->assertSeeText('Não possui justificativa(s)');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
    }

    /** @test */
    public function can_update_status_negado_with_others_justificativa_and_negado()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
        ])->assertStatus(200); 

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'negar'])
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('Pré-registro com a ID: '.$preRegistroCnpj->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_NEGADO.'" com sucesso');

        $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
    }

    /** @test */
    public function cannot_update_status_negado_with_status_different_analise_inicial_or_analise_da_correcao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
        ])->assertStatus(200); 

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO, PreRegistro::STATUS_NEGADO];
        foreach(PreRegistro::getStatus() as $status)
            if(!in_array($status, $canUpdate))
            {
                $preRegistroCnpj->preRegistro->update(['status' => $status]);
                $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'negar'])
                ->assertSessionHasErrors('status');

                $this->get(route('preregistro.view', $preRegistroCnpj->pre_registro_id))
                ->assertSeeText('Não possui o status necessário para ser negado');

                $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
            }
    }

    /** @test */
    public function can_update_status_negado_with_status_analise_inicial_or_analise_da_correcao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
        ])->assertStatus(200); 

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach($canUpdate as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'negar'])
            ->assertRedirect(route('preregistro.index'));
            
            $this->get(route('preregistro.index'))
            ->assertSeeText('Pré-registro com a ID: '.$preRegistroCnpj->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_NEGADO.'" com sucesso');

            $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
        }
    }

    /** @test */
    public function can_update_status_aprovado()
    {
        Mail::fake();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        foreach($anexo->first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '00012022']);
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'aprovar'])
        ->assertRedirect(route('preregistro.index'));

        Mail::assertQueued(PreRegistroMail::class);

        $this->get(route('preregistro.index'))
        ->assertSeeText('Pré-registro com a ID: '.$preRegistroCnpj->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_APROVADO.'" com sucesso');

        $this->assertDatabaseHas('pre_registros', [
            'status' => PreRegistro::STATUS_APROVADO,
            'idusuario' => $admin->idusuario
        ]);
    }

    /** @test */
    public function log_is_generated_when_update_status_aprovado()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        foreach($anexo->first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '123452000']);
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'aprovar'])
        ->assertRedirect(route('preregistro.index'));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário (usuário 1) atualizou status para ' . PreRegistro::STATUS_APROVADO;
        $txt .= ' *pré-registro* (id: '.$preRegistroCnpj->preRegistro->id.')';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_update_status_aprovado_without_confere_anexos()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $preRegistroCnpj->responsavelTecnico->update(['registro' => '00012022']);
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->preRegistro->id), ['situacao' => 'aprovar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText('Faltou confirmar a entrega dos anexos');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
    }

    /** @test */
    public function cannot_update_status_aprovado_with_justificativa()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        $tipos = $anexo->first()->getOpcoesPreRegistro();
        foreach($tipos as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '00012022']);
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->preRegistro->id), ['situacao' => 'aprovar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText('Possui justificativa(s)');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
    }

    /** @test */
    public function cannot_update_status_aprovado_without_registro_responsavel_tecnico()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        $tipos = $anexo->first()->getOpcoesPreRegistro();
        foreach($tipos as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->preRegistro->id), ['situacao' => 'aprovar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText('Faltou inserir o registro do Responsável Técnico');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
    }

    /** @test */
    public function cannot_update_status_aprovado_with_status_different_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        $tipos = $anexo->first()->getOpcoesPreRegistro();
        foreach($tipos as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '00012022']);
        $preRegistroCnpj->preRegistro->update(['confere_anexos' => $final]);

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO, PreRegistro::STATUS_APROVADO];
        foreach(PreRegistro::getStatus() as $status)
            if(!in_array($status, $canUpdate))
            {
                $preRegistroCnpj->preRegistro->update(['status' => $status]);
                $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'aprovar'])
                ->assertSessionHasErrors('status');

                $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
                ->assertSeeText('Não possui o status necessário para ser aprovado');

                $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
            }
    }

    /** @test */
    public function can_update_status_aprovado_with_status_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        $tipos = $anexo->first()->getOpcoesPreRegistro();
        foreach($tipos as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '00012022']);
        $preRegistroCnpj->preRegistro->update(['confere_anexos' => $final]);

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach($canUpdate as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'aprovar'])
            ->assertRedirect(route('preregistro.index'));

            $this->get(route('preregistro.index'))
            ->assertSeeText('Pré-registro com a ID: '.$preRegistroCnpj->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_APROVADO.'" com sucesso');

            $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
        }
    }

    /** @test */
    public function cannot_update_status_with_input_situacao_invalid()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        foreach($anexo->first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '00012022']);
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'aprova'])
        ->assertSessionHasErrors('situacao');

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText('Valor do status requisitado inválido');

        $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function cannot_update_status_without_input_situacao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        foreach($anexo->first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '00012022']);
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => null])
        ->assertSessionHasErrors('situacao');

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText('Obrigatório o status requisitado');

        $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CNPJ - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function view_pre_registro_cnpj()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);
        
        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText($preRegistroCnpj->razao_social)
        ->assertSeeText($preRegistroCnpj->nire)
        ->assertSeeText($preRegistroCnpj->tipo_empresa)
        ->assertSeeText(onlyDate($preRegistroCnpj->dt_inicio_atividade))
        ->assertSeeText($preRegistroCnpj->inscricao_municipal)
        ->assertSeeText($preRegistroCnpj->inscricao_estadual)
        ->assertSeeText($preRegistroCnpj->capital_social)
        ->assertSeeText($preRegistroCnpj->cep)
        ->assertSeeText($preRegistroCnpj->logradouro)
        ->assertSeeText($preRegistroCnpj->numero)
        ->assertSeeText($preRegistroCnpj->complemento)
        ->assertSeeText($preRegistroCnpj->bairro)
        ->assertSeeText($preRegistroCnpj->cidade)
        ->assertSeeText($preRegistroCnpj->uf);
    }

    /** @test */
    public function view_pre_registro_cnpj_when_checkEndEmpresa_on()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $preRegistroCnpj->update([
            'cep' => $preRegistroCnpj->preRegistro->cep,
            'logradouro' => $preRegistroCnpj->preRegistro->logradouro,
            'numero' => $preRegistroCnpj->preRegistro->numero,
            'complemento' => $preRegistroCnpj->preRegistro->complemento,
            'bairro' => $preRegistroCnpj->preRegistro->bairro,
            'cidade' => $preRegistroCnpj->preRegistro->cidade,
            'uf' => $preRegistroCnpj->preRegistro->uf,
        ]);
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);
        
        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText('Mesmo endereço da correspondência');
    }

    /** @test */
    public function view_text_justificado_cnpj()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $justificativas = $preRegistroCnpj->preRegistro->getJustificativaArray();

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText($justificativas['razao_social'])
        ->assertSeeText($justificativas['nire'])
        ->assertSeeText($justificativas['tipo_empresa'])
        ->assertSeeText($justificativas['dt_inicio_atividade'])
        ->assertSeeText($justificativas['inscricao_municipal'])
        ->assertSeeText($justificativas['inscricao_estadual'])
        ->assertSeeText($justificativas['capital_social'])
        ->assertSeeText($justificativas['cep'])
        ->assertSeeText($justificativas['logradouro'])
        ->assertSeeText($justificativas['numero'])
        ->assertSeeText($justificativas['complemento'])
        ->assertSeeText($justificativas['bairro'])
        ->assertSeeText($justificativas['cidade'])
        ->assertSeeText($justificativas['uf']);
    }

    /** @test */
    public function view_text_justificado_cnpj_when_checkEndEmpresa_on()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $preRegistroCnpj->update([
            'cep' => $preRegistroCnpj->preRegistro->cep,
            'logradouro' => $preRegistroCnpj->preRegistro->logradouro,
            'numero' => $preRegistroCnpj->preRegistro->numero,
            'complemento' => $preRegistroCnpj->preRegistro->complemento,
            'bairro' => $preRegistroCnpj->preRegistro->bairro,
            'cidade' => $preRegistroCnpj->preRegistro->cidade,
            'uf' => $preRegistroCnpj->preRegistro->uf,
        ]);
        $preRegistroCnpj->preRegistro->update([
            'justificativa' => json_encode(['checkEndEmpresa' => $faker->text(500)], JSON_FORCE_OBJECT)
        ]);

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText($preRegistroCnpj->preRegistro->getJustificativaArray()['checkEndEmpresa']);
    }

    /** @test */
    public function view_label_campo_alterado_pj()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('campos_editados')->create();
        $preRegistroCnpj->preRegistro->update([
            'opcional_celular' => 'SMS;TELEGRAM',
            'telefone' => '(11) 00000-0000;(11) 00000-0000',
            'tipo_telefone' => mb_strtoupper(tipos_contatos()[0] . ';' . tipos_contatos()[0], 'UTF-8'),
        ]);
        $camposEditados = json_decode($preRegistroCnpj->preRegistro->campos_editados, true);

        foreach($camposEditados as $key => $value)
        {
            $preRegistroCnpj->preRegistro->update([
                'campos_editados' => json_encode([$key => null], JSON_FORCE_OBJECT)
            ]);
            $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
            ->assertSee('<span class="badge badge-danger ml-2">Campos alterados</span>')
            ->assertSee('<span class="badge badge-danger ml-2">Campo alterado</span>');
        }
    }

    /** @test */
    public function view_label_campo_alterado_checkEndEmpresa_pj()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('campos_editados')->create();
        $preRegistroCnpj->update([
            'cep' => $preRegistroCnpj->preRegistro->cep,
            'logradouro' => $preRegistroCnpj->preRegistro->logradouro,
            'numero' => $preRegistroCnpj->preRegistro->numero,
            'complemento' => $preRegistroCnpj->preRegistro->complemento,
            'bairro' => $preRegistroCnpj->preRegistro->bairro,
            'cidade' => $preRegistroCnpj->preRegistro->cidade,
            'uf' => $preRegistroCnpj->preRegistro->uf,
        ]);
        $preRegistroCnpj->preRegistro->update([
            'opcional_celular' => 'SMS;TELEGRAM',
            'telefone' => '(11) 00000-0000;(11) 00000-0000',
            'tipo_telefone' => mb_strtoupper(tipos_contatos()[0] . ';' . tipos_contatos()[0], 'UTF-8'),
            'campos_editados' => json_encode(['checkEndEmpresa' => null], JSON_FORCE_OBJECT)
        ]);

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSee('<span class="badge badge-danger ml-2">Campos alterados</span>')
        ->assertSee('<span class="badge badge-danger ml-2">Campo alterado</span>');
    }
}
