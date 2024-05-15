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
use Illuminate\Foundation\Testing\WithFaker;

class PreRegistroCnpjTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private function remove_empresa($prCnpj)
    {
        if(!is_array($prCnpj))
            $prCnpj = $prCnpj->attributesToArray();

        return collect($prCnpj)->keyBy(function ($item, $key) {
            return $key != 'tipo_empresa' ? str_replace('_empresa', '', $key) : $key;
        });
    }

    private function adiciona_empresa(array $prCnpj)
    {
        return collect($prCnpj)->keyBy(function ($item, $key) {
            return in_array($key, ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf']) ? $key . '_empresa' : $key;
        })->toArray();
    }

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

        $this->post(route('externo.inserir.preregistro.ajax', [
            'classe' => 'preRegistro',
            'campo' => 'segmento',
            'valor' => 'Abrasivos'
            ]))->assertStatus(401);
        $this->assertEquals(PreRegistro::count(), 0);

        $this->put(route('externo.verifica.inserir.preregistro'))->assertNotFound();
        $this->assertEquals(PreRegistro::count(), 0);

        $this->put(route('externo.inserir.preregistro'))->assertUnauthorized();
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
            'campo' => 'numero_empresa',
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
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create(),
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
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'aprovado')->create(),
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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_with_upperCase()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco', 'low')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
                
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);

        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            if(isset($value))
                $preRegistroCnpj[$key] = mb_strtoupper($value, 'UTF-8');

        $this->assertDatabaseHas('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_exists_others_pre_registros()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->states('pj')->create()
            ])
        ]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id,
            'responsavel_tecnico_id' => null,
        ]);
                
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_exists_others_pre_registros_with_same_user_and_negado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create([
                'contabil_id' => null,
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create([
                'contabil_id' => null,
            ])
        ]);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->make(),
            'responsavel_tecnico_id' => null,
        ]);

        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_when_exists_others_pre_registros_with_same_user()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create([
                'contabil_id' => null,
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'aprovado')->create([
                'contabil_id' => null,
            ])
        ]);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->make(),
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
        ]);
                
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(401);
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');

        $this->assertDatabaseMissing('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridicaErro',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
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
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = [
            'razao_social' => $this->faker()->text(500),
            'nome_fantasia' => $this->faker()->text(500),
            'capital_social' => $this->faker()->text(500),
            'logradouro_empresa' => $this->faker()->text(500),
            'complemento_empresa' => $this->faker()->text(500),
            'bairro_empresa' => $this->faker()->text(500),
            'cidade_empresa' => $this->faker()->text(500),
        ];
        
        foreach($preRegistroCnpj as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('valor');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => ''
            ])->assertStatus(200);
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_with_blocked_historico_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 1);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->assertDatabaseMissing('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 2,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 1);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_not_blocked_historico_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 1);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_empty_cnpj_contabil_and_blocked_historico_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 1);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => ''
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 1);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_with_blocked_historico_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);

        for($i = 1; $i < 11; $i++)
        {
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => 'cpf_cnpj_socio',
                'valor' => factory('App\Socio')->raw()['cpf_cnpj']
            ])->assertOk();

            $this->assertDatabaseHas('socio_pre_registro_cnpj', [
                'pre_registro_cnpj_id' => 1,
                'socio_id' => $i,
            ]);
            $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], $i);
        }

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => factory('App\Socio')->states('pj')->raw()['cpf_cnpj']
        ])->assertOk();

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'socio_id' => 11,
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_not_blocked_historico_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => factory('App\Socio')->states('pj')->raw()['cpf_cnpj']
        ])->assertOk();

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 1);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_empty_cnpj_contabil_and_blocked_historico_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => factory('App\Socio')->states('pj')->raw()['cpf_cnpj']
        ])->assertOk();

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 1);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => ''
        ])->assertSessionHasErrors('campo');

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 1);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_when_exists_cnpj_in_contabeis_table_in_historico_socio()
    {
        $pj = factory('App\Contabil')->create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $pj->cnpj
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_when_exists_cnpj_deleted_in_contabeis_table_in_historico_socio()
    {
        $pj = factory('App\Contabil')->create([
            'deleted_at' => now()
        ]);
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $pj->cnpj
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_when_cpf_cnpj_equal_user_externo_id_in_historico_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $externo->cpf_cnpj
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);
    }

    // RT como Sócio somente via checkbox na aba Sócios (checkRT_socio)
    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_when_cpf_equal_responsavel_tecnico_id_in_historico_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1,
        ]);

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => PreRegistro::first()->pessoaJuridica->responsavelTecnico->cpf
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create()->makeHidden(['id', 'pre_registro_id', 'updated_at', 'created_at']);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                foreach($this->adiciona_empresa($preRegistroCnpj->attributesToArray()) as $key => $value)
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
        
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create()->makeHidden(['id', 'pre_registro_id', 'updated_at', 'created_at']);

        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            foreach($this->adiciona_empresa($preRegistroCnpj->attributesToArray()) as $key => $value)
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
    public function can_submit_pre_registro_cnpj()
    {
        Mail::fake();

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertOk();

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));
        
        Mail::assertQueued(PreRegistroMail::class);

        $this->assertDatabaseHas('pre_registros_cnpj', ['razao_social' => $pr->razao_social, 'nome_fantasia' => $pr->nome_fantasia]);

        $this->assertDatabaseHas('anexos', [
            'pre_registro_id' => 1
        ]);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_with_checkEndEmpresa_on()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->update([
            'cep' => $pr->preRegistro->cep,
            'logradouro' => $pr->preRegistro->logradouro,
            'numero' => $pr->preRegistro->numero,
            'complemento' => $pr->preRegistro->complemento,
            'bairro' => $pr->preRegistro->bairro,
            'cidade' => $pr->preRegistro->cidade,
            'uf' => $pr->preRegistro->uf,
        ]);

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertOk();

        $this->assertEquals('on', session('final_pr')['checkEndEmpresa']);

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));
        
        $this->assertDatabaseHas('pre_registros_cnpj', ['razao_social' => $pr->razao_social, 'nome_fantasia' => $pr->nome_fantasia]);

        $this->assertDatabaseHas('anexos', [
            'pre_registro_id' => 1
        ]);

        $this->assertEquals(PreRegistro::find(1)->pessoaJuridica->mesmoEndereco(), true);
        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registros_cnpj_when_exists_others_pre_registros()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ])->attributesToArray();

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => 1,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->states('pj')->create()
            ])
        ])->attributesToArray();

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
    
        $pr = factory('App\PreRegistroCnpj')->create()->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('pre_registros_cnpj', $pr);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());

        $this->assertEquals(PreRegistro::find(3)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registros_cnpj_when_exists_others_pre_registros_with_same_user_and_negado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => 1,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();

        $pr = factory('App\PreRegistroCnpj')->create()->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('pre_registros_cnpj', $pr);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());

        $this->assertEquals(PreRegistro::find(3)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function cannot_submit_pre_registros_cnpj_when_exists_others_pre_registros_with_same_user()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => 1,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'aprovado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));
    
        $pr = factory('App\PreRegistroCnpj')->raw();
        Anexo::find(3)->delete();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertStatus(500);

        $this->put(route('externo.inserir.preregistro'))
        ->assertUnauthorized();

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
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'user_externo_id' => factory('App\UserExterno')->states('pj')->create()
            ])
        ])->attributesToArray();

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => 1,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->states('pj')->create()
            ])
        ])->attributesToArray();

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create()->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('pre_registros_cnpj', $pr);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());

        $this->assertEquals(PreRegistro::find(3)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registros_cnpj_when_exists_others_pre_registros_with_same_socio()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'user_externo_id' => factory('App\UserExterno')->states('pj')->create()
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'user_externo_id' => factory('App\UserExterno')->states('pj')->create()
            ])
        ]);
        $preRegistroCnpj_2->socios()->detach();
        $preRegistroCnpj_2->socios()->attach($preRegistroCnpj_1->socios->get(0)->id, ['rt' => false]);
        $preRegistroCnpj_2->socios()->attach($preRegistroCnpj_1->socios->get(1)->id, ['rt' => false]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios()->detach();
        $pr->socios()->attach($preRegistroCnpj_1->socios->get(0)->id, ['rt' => false]);
        $pr->socios()->attach($preRegistroCnpj_1->socios->get(1)->id, ['rt' => false]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('pre_registros_cnpj', $pr->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());

        $this->assertEquals(PreRegistro::find(3)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_without_optional_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $prCnpj = factory('App\PreRegistroCnpj')->create([
            'nire' => null,
            'complemento' => null,
        ])->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));
        
        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $prCnpj);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_required_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $prCnpj = factory('App\PreRegistroCnpj')->create([
            'razao_social' => null,
            'tipo_empresa' => null,
            'dt_inicio_atividade' => null,
            'nome_fantasia' => null,
            'capital_social' => null,
            'cep' => null,
            'logradouro' => null,
            'numero' => null,
            'bairro' => null,
            'cidade' => null,
            'uf' => null,
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors([
            'razao_social', 'tipo_empresa', 'dt_inicio_atividade', 'nome_fantasia', 'capital_social', 'cep_empresa', 'logradouro_empresa', 'numero_empresa',
            'bairro_empresa', 'cidade_empresa', 'uf_empresa',
        ]);

        $this->assertDatabaseHas('pre_registros', $prCnpj->preRegistro->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $prCnpj->attributesToArray());

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_CRIADO);
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_razao_social()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'razao_social' => '',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_razao_social_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'razao_social' => 'Razã',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_razao_social_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'razao_social' => $this->faker()->text(500),
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_razao_social_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'razao_social' => 'Raz4o S0cial',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_capital_social()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'capital_social' => '',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('capital_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_capital_social_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'capital_social' => '0,0',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('capital_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_capital_social_more_than_16_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'capital_social' => '1.000.000.000.0,00',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('capital_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_capital_social_wrong_value()
    {
        $capitalSocial = ['0000', '0,00', '01,00', '1,0,00', '1,000', '1000'];
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'capital_social' => '',
        ]);

        foreach($capitalSocial as $val){
            $dados->update(['capital_social' => $val]);
            $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
            ->assertSessionHasErrors('capital_social');
        }
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nire_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'nire' => '1234',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nire');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nire_more_than_20_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'nire' => '123456789012345678901',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nire');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_tipo_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'tipo_empresa' => '',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('tipo_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_tipo_empresa_value_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'tipo_empresa' => 'Teste',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('tipo_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_dt_inicio_atividade()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_inicio_atividade_without_date_type()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => 'texto',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_inicio_atividade_incorrect_format()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000/12/25',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_inicio_atividade_after_today()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => Carbon::today()->addDay()->format('Y-m-d'),
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_nome_fantasia()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'nome_fantasia' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_fantasia');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_fantasia_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'nome_fantasia' => 'Fant'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_fantasia');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_fantasia_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'nome_fantasia' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_fantasia');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_cep_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'cep' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cep_empresa_more_than_9_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'cep' => '01234-0123'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cep_empresa_incorrect_format()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'cep' => '012340123'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_bairro_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'bairro' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_bairro_empresa_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'bairro' => 'São'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_bairro_empresa_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'bairro' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_logradouro_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'logradouro' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_logradouro_empresa_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'logradouro' => 'Rua'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_logradouro_empresa_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'logradouro' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_numero_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'numero' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('numero_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_numero_empresa_more_than_10_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'numero' => '12345678901'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('numero_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_complemento_empresa_more_than_50_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'complemento' => $this->faker()->text(300)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('complemento_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_cidade_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'cidade' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cidade_empresa_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'cidade' => 'San'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cidade_empresa_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'cidade' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cidade_empresa_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'cidade' => 'S4ntos'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_uf_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'uf' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('uf_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_wrong_uf_empresa()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create([
            'uf' => 'PP'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('uf_empresa');
    }

    /** @test */
    public function log_is_generated_when_form_pj_is_submitted()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $dados = factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

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

        $preRegistro = factory('App\PreRegistroCnpj')->create()->preRegistro;

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO]))
                in_array($status, [PreRegistro::STATUS_APROVADO, PreRegistro::STATUS_NEGADO]) ? 
                $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertNotFound() : 
                $this->put(route('externo.inserir.preregistro'))->assertUnauthorized();
        }
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_with_status_aguardando_correcao_or_sendo_elaborado()
    {
        Mail::fake();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $preRegistro = factory('App\PreRegistroCnpj')->create()->preRegistro;

        $s = [PreRegistro::STATUS_CRIADO => PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_CORRECAO => PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach([PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO] as $status)
        {
            $preRegistro->update(['status' => $status]);
            if($status == PreRegistro::STATUS_CORRECAO)
                $preRegistro->pessoaJuridica->update(['nire' => '65439']);
            $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
            $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));
            Mail::assertQueued(PreRegistroMail::class);
            $this->assertEquals(PreRegistro::first()->status, $s[$status]);
        }
    }

    /** @test */
    public function log_is_generated_when_status_aguardando_correcao()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'enviado_correcao')->create()
        ])->preRegistro;
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

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

        $preRegistro = factory('App\PreRegistroCnpj')->create()->preRegistro;

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $t = json_decode($preRegistro->fresh()->campos_espelho, true);
        $t2 = array_merge($preRegistro->arrayValidacaoInputs(), $preRegistro->contabil->arrayValidacaoInputs(), $preRegistro->pessoaJuridica->arrayValidacaoInputs(), 
        $preRegistro->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), $preRegistro->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(),
        $preRegistro->pessoaJuridica->socios->get(1)->arrayValidacaoInputs(), ['path' => $preRegistro->anexos->count(), "opcional_celular" => $preRegistro->opcional_celular, 
        "opcional_celular_1" => '', 'checkRT_socio' => 'off']);

        $this->assertEquals($t, $t2);
    }

    /** @test */
    public function filled_campos_editados_pre_registros_cnpj_when_form_is_submitted_when_status_aguardando_correcao()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $PreRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'complemento' => 'FUNDOS',
            'cidade' => 'BELO HORIZONTE',
            'uf' => 'MG',
        ]);

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'justificar',
            'campo' => 'razao_social',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('user_externo', $externo);

        $campos = [
            'razao_social' => 'Razão Social',
            'nire' => null,
            'tipo_empresa' => tipos_empresa()[2],
            'dt_inicio_atividade' => '2019-12-10',
            'capital_social' => '5.000,00',
            'cep_empresa' => null,
            'logradouro_empresa' => null,
            'numero_empresa' => null,
            'complemento_empresa' => null,
            'bairro_empresa' => null,
            'cidade_empresa' => null,
            'uf_empresa' => null,
            'checkEndEmpresa' => 'on',
        ];

        foreach($campos as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $arrayFinal = array_diff(array_keys(PreRegistro::first()->getCamposEditados()), array_keys($campos));
        $this->assertEquals($arrayFinal, array());
        $arrayFinal = array_diff(array_keys($campos), array_keys(PreRegistro::first()->getCamposEditados()));
        $this->assertEquals($arrayFinal, array());
    }

    /** @test */
    public function view_justifications_pj()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('user_externo', $externo);

        foreach($keys as $campo)
            $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
            ->assertSeeInOrder([
                '<a class="nav-link" data-toggle="pill" href="#parte_dados_gerais">',
                'Dados Gerais&nbsp',
                '<span class="badge badge-danger">',
                '</a>',
                '<a class="nav-link" data-toggle="pill" href="#parte_endereco">',
                'Endereço&nbsp',
                '<span class="badge badge-danger">',
                '</a>',
            ])
            ->assertSee('value="'. route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo]) .'"');
    }

    /** @test */
    public function view_justifications_text_pj()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        foreach($keys as $campo)
            $this->get(route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo]))
            ->assertJsonFragment(['justificativa' => PreRegistro::first()->getJustificativaPorCampo($campo)]);
    }

    /** 
     * ===============================================================================================================
     * TESTES PRE-REGISTRO-CNPJ - LOGIN CONTABILIDADE RESPONSÁVEL PELO GERENCIAMENTO PARA O USUARIO EXTERNO COMUM
     * ===============================================================================================================
     */

    /** @test */
    public function can_new_pre_registro_pj_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $this->get(route('externo.preregistro.view'))->assertOk();
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistro = $externo->preRegistros->first();

        $this->assertDatabaseHas('pre_registros', [
            'id' => $preRegistro->id,
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'id' => $preRegistro->pessoaJuridica->id,
        ]);
    }

    /** @test */
    public function log_is_generated_when_form_cnpj_is_created_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $pr = PreRegistro::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Contabilidade com cnpj '.$externo->cnpj.', criou a solicitação de registro com a id: 1 junto com a conta do Usuário Externo com o cnpj '.$pr->userExterno->cpf_cnpj;
        $txt .= ' que foi notificado pelo e-mail ' . $pr->userExterno->email;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function view_msg_update_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        
        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));

        PreRegistro::first()->update(['updated_at' => PreRegistro::first()->updated_at->subHour()]);
        $atual = PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s');

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica',
            'campo' => 'numero_empresa',
            'valor' => '223'
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
        $this->assertNotEquals($atual, PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    }

    /** @test */
    public function can_create_new_register_pre_registros_cnpj_after_negado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create(),
        ]);

        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'pre_registro_id' => 2
        ]);
    }

    /** @test */
    public function cannot_create_new_register_pre_registros_cnpj_after_aprovado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'aprovado')->create(),
        ]);        

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make([
            'cpf_cnpj' => PreRegistro::first()->userExterno->cpf_cnpj
        ])->toArray();

        $this->post(route('externo.contabil.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));
        
        $this->assertDatabaseMissing('pre_registros_cnpj', [
            'pre_registro_id' => 2
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => $externo->preRegistros->first()->id
        ]);

        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj->makeVisible(['pre_registro_id'])->attributesToArray())->toArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_with_upperCase_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco', 'low')->make([
            'pre_registro_id' => $externo->preRegistros->first()->id
        ]);
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);

        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $preRegistroCnpj[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;

        $this->assertDatabaseHas('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj->attributesToArray())->toArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_exists_others_pre_registros_by_contabilidade()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->states('pj')->create()
            ])
        ]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => $externo->preRegistros->first()->id,
            'responsavel_tecnico_id' => null,
        ]);
                
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => $externo->preRegistros->first()->id]), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj->makeVisible(['pre_registro_id']))->toArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_exists_others_pre_registros_with_same_user_and_negado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create([
                'contabil_id' => null,
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create([
                'contabil_id' => null,
            ])
        ]);

        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->make([
                'responsavel_tecnico_id' => null,
            ]),
        ]);
                
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 3]), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_when_exists_others_pre_registros_with_same_user_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create([
                'contabil_id' => null,
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'aprovado')->create([
                'contabil_id' => null,
            ])
        ]);

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make([
            'cpf_cnpj' => $preRegistroCnpj_1->preRegistro->userExterno->cpf_cnpj
        ])->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->make(),
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
        ]);
                
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 3]), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(500);
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj->makeVisible(['pre_registro_id']))->toArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_wrong_input_name_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => 1
        ]);
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_without_classe_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => 1
        ]);

        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => '',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_wrong_classe_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => 1
        ]);
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridicaErro',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_without_campo_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => 1
        ]);

        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_with_input_type_text_more_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCnpj = [
            'razao_social' => $this->faker()->text(500),
            'nome_fantasia' => $this->faker()->text(500),
            'capital_social' => $this->faker()->text(500),
            'logradouro_empresa' => $this->faker()->text(500),
            'complemento_empresa' => $this->faker()->text(500),
            'bairro_empresa' => $this->faker()->text(500),
            'cidade_empresa' => $this->faker()->text(500),
        ];
        
        foreach($preRegistroCnpj as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('valor');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registro_cnpj_by_ajax_with_dt_inicio_atividade_after_today_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica',
            'campo' => 'dt_inicio_atividade',
            'valor' => Carbon::today()->addDay()->format('Y-m-d')
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'dt_inicio_atividade' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cnpj_by_ajax_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica',
            'campo' => 'dt_inicio_atividade',
            'valor' => 'texto'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'dt_inicio_atividade' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cnpj_by_ajax_with_tipo_empresa_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica',
            'campo' => 'tipo_empresa',
            'valor' => 'texto'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'tipo_empresa' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cnpj_by_ajax_with_uf_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica',
            'campo' => 'uf_empresa',
            'valor' => 'FF'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'uf' => null
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_clean_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('make_endereco')->make([
            'pre_registro_id' => $externo->preRegistros->first()->id
        ]);
        
        foreach($preRegistroCnpj->attributesToArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => ''
            ])->assertStatus(200);
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $this->remove_empresa($preRegistroCnpj)->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_with_blocked_historico_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 1);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->assertDatabaseMissing('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 2,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 1);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_not_blocked_historico_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 1);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_empty_cnpj_contabil_and_blocked_historico_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 1);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => ''
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_rt, true)['tentativas'], 1);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_with_blocked_historico_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);

        for($i = 1; $i < 11; $i++)
        {
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => 'cpf_cnpj_socio',
                'valor' => factory('App\Socio')->raw()['cpf_cnpj']
            ])->assertOk();

            $this->assertDatabaseHas('socio_pre_registro_cnpj', [
                'pre_registro_cnpj_id' => 1,
                'socio_id' => $i,
            ]);
            $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], $i);
        }

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => factory('App\Socio')->states('pj')->raw()['cpf_cnpj']
        ])->assertOk();

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'socio_id' => 11,
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_not_blocked_historico_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => factory('App\Socio')->states('pj')->raw()['cpf_cnpj']
        ])->assertOk();

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 1);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_empty_cnpj_contabil_and_blocked_historico_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => factory('App\Socio')->states('pj')->raw()['cpf_cnpj']
        ])->assertOk();

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 1);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => ''
        ])->assertSessionHasErrors('campo');

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 1);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_when_exists_cnpj_in_contabeis_table_in_historico_socio_by_contabilidade()
    {
        $pj = factory('App\Contabil')->create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $pj->cnpj
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_when_exists_cnpj_deleted_in_contabeis_table_in_historico_socio_by_contabilidade()
    {
        $pj = factory('App\Contabil')->create([
            'deleted_at' => now()
        ]);
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $pj->cnpj
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_when_cpf_cnpj_equal_user_externo_id_in_historico_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => PreRegistro::first()->userExterno->cpf_cnpj
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);
    }

    // RT como Sócio somente via checkbox na aba Sócios (checkRT_socio)
    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_when_cpf_equal_responsavel_tecnico_id_in_historico_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1,
        ]);

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => PreRegistro::first()->pessoaJuridica->responsavelTecnico->cpf
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
        ]);
        $this->assertEquals(json_decode(PreRegistro::first()->pessoaJuridica->historico_socio, true)['tentativas'], 0);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => 1
        ])->makeHidden(['id', 'pre_registro_id', 'updated_at', 'created_at']);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                foreach($this->adiciona_empresa($preRegistroCnpj->attributesToArray()) as $key => $value)
                    $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                        'classe' => 'pessoaJuridica',
                        'campo' => $key,
                        'valor' => ''
                    ])->assertStatus(401);
        }
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => 1
        ])->makeHidden(['id', 'pre_registro_id', 'updated_at', 'created_at']);
        
        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            foreach($this->adiciona_empresa($preRegistroCnpj->attributesToArray()) as $key => $value)
                $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                    'classe' => 'pessoaJuridica',
                    'campo' => $key,
                    'valor' => ''
                ])->assertStatus(200);
        }
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_by_contabilidade()
    {
        Mail::fake();

        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))->assertOk();
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])->assertOk();

        $this->assertEquals('off', session('final_pr')['checkEndEmpresa']);

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        Mail::assertQueued(PreRegistroMail::class);

        $this->assertDatabaseHas('pre_registros_cnpj', $pr->attributesToArray());

        $this->assertDatabaseHas('anexos', [
            'pre_registro_id' => 1
        ]);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_with_checkEndEmpresa_on_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->update([
            'cep' => $pr->preRegistro->cep,
            'logradouro' => $pr->preRegistro->logradouro,
            'numero' => $pr->preRegistro->numero,
            'complemento' => $pr->preRegistro->complemento,
            'bairro' => $pr->preRegistro->bairro,
            'cidade' => $pr->preRegistro->cidade,
            'uf' => $pr->preRegistro->uf,
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->assertEquals('on', session('final_pr')['checkEndEmpresa']);

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $this->assertDatabaseHas('pre_registros_cnpj', $pr->attributesToArray());
    }

    /** @test */
    public function can_submit_pre_registros_cnpj_when_exists_others_pre_registros_by_contabilidade()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ])->attributesToArray();

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => 1,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->states('pj')->create()
            ])
        ])->attributesToArray();

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'user_externo_id' => factory('App\UserExterno')->states('pj')->create()
            ])
        ])->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 3]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 3]));

        $this->assertDatabaseHas('pre_registros_cnpj', $pr);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2);
        $this->assertDatabaseHas('pre_registros', PreRegistro::find(3)->toArray());

        $this->assertEquals(PreRegistro::find(3)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registros_cnpj_when_exists_others_pre_registros_with_same_user_and_negado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => 1,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();

        $pr = factory('App\PreRegistroCnpj')->create()->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 3]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 3]));

        $this->assertDatabaseHas('pre_registros_cnpj', $pr);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2);
        $this->assertDatabaseHas('pre_registros', PreRegistro::find(3)->toArray());
    }

    /** @test */
    public function cannot_submit_pre_registros_cnpj_when_exists_others_pre_registros_with_same_user_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'negado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => 1,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'aprovado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();

        $pr = factory('App\PreRegistroCnpj')->raw();
        Anexo::find(3)->delete();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), ['pergunta' => "25 meses"])
        ->assertStatus(500);

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 3]))
        ->assertUnauthorized();

        $this->assertDatabaseMissing('pre_registros_cnpj', $pr);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2);
        $this->assertDatabaseMissing('pre_registros_cnpj', ['id' => 3]);
    }

    /** @test */
    public function can_submit_pre_registros_cnpj_when_exists_others_pre_registros_with_same_contabil_and_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ])->attributesToArray();

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => 1,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->states('pj')->create()
            ])
        ])->attributesToArray();

        $pr = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'user_externo_id' => factory('App\UserExterno')->states('pj')->create()
            ])
        ])->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 3]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 3]));

        $this->assertDatabaseHas('pre_registros_cnpj', $pr);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2);
        $this->assertDatabaseHas('pre_registros', PreRegistro::find(3)->toArray());

        $this->assertEquals(PreRegistro::find(3)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registros_cnpj_when_exists_others_pre_registros_with_same_socio_by_contabilidade()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'user_externo_id' => factory('App\UserExterno')->states('pj')->create()
            ])
        ]);
        $preRegistroCnpj_2->socios()->detach();
        $preRegistroCnpj_2->socios()->attach($preRegistroCnpj_1->socios->get(0)->id, ['rt' => false]);
        $preRegistroCnpj_2->socios()->attach($preRegistroCnpj_1->socios->get(1)->id, ['rt' => false]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());

        $pr = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'user_externo_id' => factory('App\UserExterno')->states('pj')->create()
            ])
        ]);
        $pr->socios()->detach();
        $pr->socios()->attach($preRegistroCnpj_1->socios->get(0)->id, ['rt' => false]);
        $pr->socios()->attach($preRegistroCnpj_1->socios->get(1)->id, ['rt' => false]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 3]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 3]));

        $this->assertDatabaseHas('pre_registros_cnpj', $pr->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('pre_registros', PreRegistro::find(3)->toArray());

        $this->assertEquals(PreRegistro::find(3)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_without_optional_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $prCnpj = factory('App\PreRegistroCnpj')->create([
            'nire' => null,
            'complemento' => null,
        ])->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));
        
        $this->assertDatabaseHas('pre_registros_cnpj', $prCnpj);
        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_required_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $prCnpj = factory('App\PreRegistroCnpj')->create([
            'razao_social' => null,
            'tipo_empresa' => null,
            'dt_inicio_atividade' => null,
            'nome_fantasia' => null,
            'capital_social' => null,
            'cep' => null,
            'logradouro' => null,
            'numero' => null,
            'bairro' => null,
            'cidade' => null,
            'uf' => null,
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors([
            'razao_social', 'tipo_empresa', 'dt_inicio_atividade', 'nome_fantasia', 'capital_social', 'cep_empresa', 'logradouro_empresa', 'numero_empresa',
            'bairro_empresa', 'cidade_empresa', 'uf_empresa',
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', $prCnpj->attributesToArray());
        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_CRIADO);
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_razao_social_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'razao_social' => '',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_razao_social_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'razao_social' => 'Razã',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_razao_social_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'razao_social' => $this->faker()->text(500),
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_razao_social_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'razao_social' => 'Raz4o S0cial',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_capital_social_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'capital_social' => '',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('capital_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_capital_social_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'capital_social' => '0,0',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('capital_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_capital_social_more_than_16_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'capital_social' => '1.000.000.000.0,00',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('capital_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_capital_social_wrong_value_by_contabilidade()
    {
        $capitalSocial = ['0000', '0,00', '01,00', '1,0,00', '1,000', '1000'];
        $externo = $this->signInAsUserExterno('contabil');

        $dados = factory('App\PreRegistroCnpj')->create([
            'capital_social' => '',
        ]);

        foreach($capitalSocial as $val){
            $dados->update(['capital_social' => $val]);
            $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
            ->assertSessionHasErrors('capital_social');
        }
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nire_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'nire' => '1234',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('nire');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nire_more_than_20_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'nire' => '123456789012345678901',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('nire');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_tipo_empresa_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'tipo_empresa' => '',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('tipo_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_tipo_empresa_value_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'tipo_empresa' => 'Teste',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('tipo_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_dt_inicio_atividade_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_inicio_atividade_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => 'texto',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_inicio_atividade_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000/12/25',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_inicio_atividade_after_today_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => Carbon::today()->addDay()->format('Y-m-d'),
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_nome_fantasia_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'nome_fantasia' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('nome_fantasia');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_fantasia_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'nome_fantasia' => 'Fant'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('nome_fantasia');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_fantasia_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'nome_fantasia' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('nome_fantasia');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_cep_empresa_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'cep' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('cep_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cep_empresa_more_than_9_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'cep' => '01234-0123'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('cep_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cep_empresa_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'cep' => '012340123'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('cep_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_bairro_empresa_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'bairro' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('bairro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_bairro_empresa_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'bairro' => 'São'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('bairro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_bairro_empresa_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'bairro' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('bairro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_logradouro_empresa_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'logradouro' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('logradouro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_logradouro_empresa_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'logradouro' => 'Rua'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('logradouro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_logradouro_empresa_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'logradouro' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('logradouro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_numero_empresa_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'numero' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('numero_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_numero_empresa_more_than_10_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'numero' => '12345678901'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('numero_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_complemento_empresa_more_than_50_chars_by_contabilidade()
    {
        
        $externo = $this->signInAsUserExterno('contabil');

        $dados = factory('App\PreRegistroCnpj')->create([
            'complemento' => $this->faker()->text(300)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('complemento_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_cidade_empresa_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'cidade' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cidade_empresa_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'cidade' => 'San'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cidade_empresa_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'cidade' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cidade_empresa_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'cidade' => 'S4ntos'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_uf_empresa_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'uf' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('uf_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_wrong_uf_empresa_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCnpj')->create([
            'uf' => 'PP'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors('uf_empresa');
    }

    /** @test */
    public function log_is_generated_when_form_pj_is_submitted_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $dados = factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $pr = PreRegistro::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Contabilidade com cnpj '.$externo->cnpj.' realizou a operação para o Usuário Externo com cnpj: ' . $pr->userExterno->cpf_cnpj;
        $txt .= ', atualizou o status para ' . $pr::STATUS_ANALISE_INICIAL . ' da solicitação de registro com a id: ' . $pr->id;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_status_different_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $preRegistro = factory('App\PreRegistroCnpj')->create()->preRegistro;

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO]))
                in_array($status, [PreRegistro::STATUS_APROVADO, PreRegistro::STATUS_NEGADO]) ? 
                $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])->assertNotFound() : 
                $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))->assertUnauthorized();
        }
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_with_status_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        Mail::fake();
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistro = factory('App\PreRegistroCnpj')->create()->preRegistro;

        $s = [PreRegistro::STATUS_CRIADO => PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_CORRECAO => PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach([PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO] as $status)
        {
            $preRegistro->update(['status' => $status]);
            if($status == PreRegistro::STATUS_CORRECAO)
                $preRegistro->pessoaJuridica->update(['nire' => '65439']);

            $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
            ->assertViewIs('site.userExterno.inserir-pre-registro');

            $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
            ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));
            Mail::assertQueued(PreRegistroMail::class);
            $this->assertEquals(PreRegistro::first()->status, $s[$status]);
        }
    }

    /** @test */
    public function log_is_generated_when_status_aguardando_correcao_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'enviado_correcao')->create()
        ])->preRegistro;

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Contabilidade com cnpj '.$externo->cnpj.' realizou a operação para o Usuário Externo com cnpj: ' . $preRegistro->userExterno->cpf_cnpj;
        $txt .= ', atualizou o status para ' . PreRegistro::STATUS_ANALISE_CORRECAO . ' da solicitação de registro com a id: ' . $preRegistro->id;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function filled_campos_espelho_when_form_pj_is_submitted_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $preRegistro = factory('App\PreRegistroCnpj')->create()->preRegistro;
           
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $t = json_decode($preRegistro->fresh()->campos_espelho, true);
        $t2 = array_merge($preRegistro->arrayValidacaoInputs(), $preRegistro->contabil->arrayValidacaoInputs(), $preRegistro->pessoaJuridica->arrayValidacaoInputs(), 
        $preRegistro->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), $preRegistro->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(),
        $preRegistro->pessoaJuridica->socios->get(1)->arrayValidacaoInputs(), ['path' => $preRegistro->anexos->count(), "opcional_celular" => $preRegistro->opcional_celular, 
        "opcional_celular_1" => '', 'checkRT_socio' => 'off']);

        $this->assertEquals($t, $t2);
    }

    /** @test */
    public function filled_campos_editados_pre_registros_cnpj_when_form_is_submitted_when_status_aguardando_correcao_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $PreRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'complemento' => 'FUNDOS',
            'cidade' => 'BELO HORIZONTE',
            'uf' => 'MG',
        ]);

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $admin = $this->signIn(PreRegistro::first()->user);

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'justificar',
            'campo' => 'razao_social',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('contabil', $externo);

        $campos = [
            'razao_social' => 'Razão Social',
            'nire' => null,
            'tipo_empresa' => tipos_empresa()[2],
            'dt_inicio_atividade' => '2019-12-10',
            'capital_social' => '5.000,00',
            'cep_empresa' => null,
            'logradouro_empresa' => null,
            'numero_empresa' => null,
            'complemento_empresa' => null,
            'bairro_empresa' => null,
            'cidade_empresa' => null,
            'uf_empresa' => null,
            'checkEndEmpresa' => 'on',
        ];

        foreach($campos as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');
        
        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $arrayFinal = array_diff(array_keys(PreRegistro::first()->getCamposEditados()), array_keys($campos));
        $this->assertEquals($arrayFinal, array());
        $arrayFinal = array_diff(array_keys($campos), array_keys(PreRegistro::first()->getCamposEditados()));
        $this->assertEquals($arrayFinal, array());
    }

    /** @test */
    public function view_justifications_pj_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');
        
        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('contabil', $externo);

        foreach($keys as $campo)
            $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
            ->assertSeeInOrder([
                '<a class="nav-link" data-toggle="pill" href="#parte_dados_gerais">',
                'Dados Gerais&nbsp',
                '<span class="badge badge-danger">',
                '</a>',
                '<a class="nav-link" data-toggle="pill" href="#parte_endereco">',
                'Endereço&nbsp',
                '<span class="badge badge-danger">',
                '</a>',
            ])
            ->assertSee('value="'. route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo]) .'"');
    }

    /** @test */
    public function view_justifications_text_pj_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');
        
        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        foreach($keys as $campo)
            $this->get(route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo]))
            ->assertJsonFragment(['justificativa' => PreRegistro::first()->getJustificativaPorCampo($campo)]);
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CNPJ VIA AJAX - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function can_update_justificativa()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());

        $justificativas = array();
        foreach($dados as $campo)
        {
            $texto = $this->faker()->text(500);
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
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            if(in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                foreach($dados as $campo)
                    $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                        'acao' => 'justificar',
                        'campo' => $campo,
                        'valor' => $this->faker()->text(500)
                    ])->assertStatus(200);    
        }
    }

    /** @test */
    public function can_edit_justificativas()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => ''
            ])->assertStatus(200);    

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
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
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
        {
            $texto = $this->faker()->text(900);
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
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo . '_erro',
                'valor' => $this->faker()->text(500)
            ])->assertSessionHasErrors('campo');
    }

    /** @test */
    public function cannot_update_justificativa_with_wrong_input_acao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar_',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertSessionHasErrors('acao'); 
    }

    /** @test */
    public function cannot_update_justificativa_with_status_different_em_analise_or_analise_da_correcao()
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                foreach($dados as $campo)
                    $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                        'acao' => 'justificar',
                        'campo' => $campo,
                        'valor' => $this->faker()->text(500)
                    ])->assertStatus(401);
                
        }
    }

    /** @test */
    public function log_is_generated_when_update_justificativa()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());

        foreach($dados as $campo)
        {
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertOk(); 

            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
            $txt = $inicio . 'Usuário (usuário 1) fez a ação de "justificar" o campo "' . $campo . '", ';
            $txt .= 'inserindo ou removendo valor *pré-registro* (id: '.$preRegistroCnpj->preRegistro->id.')';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function can_remove_all_justificativas()
    {
        $admin = $this->signInAsAdmin();
        
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);   

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'exclusao_massa',
            'campo' => 'exclusao_massa',
            'valor' => $dados
        ])->assertStatus(200);    

        $this->assertDatabaseHas('pre_registros', [
            'justificativa' => null,
            'idusuario' => $admin->idusuario
        ]);
    }

    /** @test */
    public function can_check_anexos()
    {
        $admin = $this->signInAsAdmin();
        
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
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
    public function cannot_view_check_cpf_if_socio_not_pf()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->socios()->detach(1);

        $tipo = 'CPF';
        $arrayAnexos[$tipo] = "OK";

        $this->get(route('preregistro.view', 1))
        ->assertDontSee('<label for="comprovante_cpf" class="form-check-label">');

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => $tipo
        ])->assertOk(); 

        $this->assertDatabaseHas('pre_registros', [
            'confere_anexos' => json_encode($arrayAnexos, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_view_check_residencia_if_socio_not_pf()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->socios()->detach(1);

        $tipo = 'Comprovante de Residência';
        $arrayAnexos[$tipo] = "OK";

        $this->get(route('preregistro.view', 1))
        ->assertDontSee('<label for="comprovante_residencia" class="form-check-label">');

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => $tipo
        ])->assertOk(); 

        $this->assertDatabaseHas('pre_registros', [
            'confere_anexos' => json_encode($arrayAnexos, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_view_check_eleitoral_if_socio_not_pf()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->socios()->detach(1);

        $tipo = 'Certidão de quitação eleitoral';
        $arrayAnexos[$tipo] = "OK";

        $this->get(route('preregistro.view', 1))
        ->assertDontSee('<label for="cert_quitacao_eleitoral" class="form-check-label">');

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => $tipo
        ])->assertOk(); 

        $this->assertDatabaseHas('pre_registros', [
            'confere_anexos' => json_encode($arrayAnexos, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_view_check_eleitoral_if_socio_pf_foreign()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->socios->get(0)->update(['nacionalidade' => 'CHILENA']);

        $tipo = 'Certidão de quitação eleitoral';
        $arrayAnexos[$tipo] = "OK";

        $this->get(route('preregistro.view', 1))
        ->assertDontSee('<label for="cert_quitacao_eleitoral" class="form-check-label">');

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => $tipo
        ])->assertOk(); 

        $this->assertDatabaseHas('pre_registros', [
            'confere_anexos' => json_encode($arrayAnexos, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_view_check_identidade_if_socio_not_pf()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->socios()->detach(1);

        $tipo = 'Comprovante de identidade';
        $arrayAnexos[$tipo] = "OK";

        $this->get(route('preregistro.view', 1))
        ->assertDontSee('<label for="comprovante_identidade" class="form-check-label">');

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => $tipo
        ])->assertOk(); 

        $this->assertDatabaseHas('pre_registros', [
            'confere_anexos' => json_encode($arrayAnexos, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_view_check_reservista_if_socio_not_pf()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->socios()->detach(1);

        $tipo = 'Cerificado de reservista ou dispensa';
        $arrayAnexos[$tipo] = "OK";

        $this->get(route('preregistro.view', 1))
        ->assertDontSee('<label for="cert_reservista_dispensa" class="form-check-label">');

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => $tipo
        ])->assertOk(); 

        $this->assertDatabaseHas('pre_registros', [
            'confere_anexos' => json_encode($arrayAnexos, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_view_check_reservista_if_socio_pf_and_over_45_years_old()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->socios->get(0)->update(['dt_nascimento' => now()->subYears(45)->subDay()->format('Y-m-d')]);

        $tipo = 'Cerificado de reservista ou dispensa';
        $arrayAnexos[$tipo] = "OK";

        $this->get(route('preregistro.view', 1))
        ->assertDontSee('<label for="cert_reservista_dispensa" class="form-check-label">');

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => $tipo
        ])->assertOk(); 

        $this->assertDatabaseHas('pre_registros', [
            'confere_anexos' => json_encode($arrayAnexos, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_check_anexos_with_wrong_action()
    {
        $admin = $this->signInAsAdmin();
        
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
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
        
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
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
        
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
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
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
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
        
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
            'acao' => 'justificar',
            'campo' => 'dt_nascimento_socio',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'corrigir'])
        ->assertRedirect(route('preregistro.index'));

        Mail::assertQueued(PreRegistroMail::class);

        $this->get(route('preregistro.index'))
        ->assertSeeText('Pré-registro com a ID: '.$preRegistroCnpj->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_CORRECAO.'" com sucesso');

        $this->assertDatabaseHas('pre_registros', [
            'status' => PreRegistro::STATUS_CORRECAO,
            'idusuario' => $admin->idusuario,
            'historico_justificativas' => $preRegistroCnpj->fresh()->preRegistro->historico_justificativas
        ]);
    }

    /** @test */
    public function can_update_status_enviar_para_correcao_without_confere_anexos()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
            'acao' => 'justificar',
            'campo' => 'dt_nascimento_socio',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200);

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
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

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
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->preRegistro->update(['justificativa' => json_encode(['negado' => 'teste negação'])]);

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
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
            'acao' => 'justificar',
            'campo' => 'dt_nascimento_socio',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200);

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
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
            'acao' => 'justificar',
            'campo' => 'dt_nascimento_socio',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200);

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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
            'acao' => 'justificar',
            'campo' => 'dt_nascimento_socio',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200);
        
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
        
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);
        $anexo = Anexo::first();

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $this->faker()->text(500)
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
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $this->faker()->text(500)
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
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $this->faker()->text(500)
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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

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
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'razao_social',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->pre_registro_id), ['situacao' => 'negar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCnpj->pre_registro_id))
        ->assertSeeText('Não possui justificativa(s)');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
    }

    /** @test */
    public function can_update_status_negado_with_others_justificativa_and_negado()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);

        foreach(['razao_social', 'negado'] as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
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
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $this->faker()->text(500)
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
        
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $this->faker()->text(500)
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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '012345/2020']);

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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '012345/2020']);

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
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '012345/2020']);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->preRegistro->id), ['situacao' => 'aprovar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText('Faltou confirmar a entrega dos anexos');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
    }

    /** @test */
    public function cannot_update_status_aprovado_with_confere_anexos_without_cpf_when_pf()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '012345/2020']);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => 'CPF'
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->preRegistro->id), ['situacao' => 'aprovar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText('Faltou confirmar a entrega dos anexos');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
    }

    /** @test */
    public function cannot_update_status_aprovado_with_confere_anexos_without_identidade_when_pf()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '012345/2020']);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => 'Comprovante de identidade'
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->preRegistro->id), ['situacao' => 'aprovar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText('Faltou confirmar a entrega dos anexos');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
    }

    /** @test */
    public function cannot_update_status_aprovado_with_confere_anexos_without_residencia_when_pf()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '012345/2020']);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => 'Comprovante de Residência'
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->preRegistro->id), ['situacao' => 'aprovar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText('Faltou confirmar a entrega dos anexos');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
    }

    /** @test */
    public function cannot_update_status_aprovado_with_confere_anexos_without_eleitoral_when_pf()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '012345/2020']);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => 'Certidão de quitação eleitoral'
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', $preRegistroCnpj->preRegistro->id), ['situacao' => 'aprovar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText('Faltou confirmar a entrega dos anexos');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
    }

    /** @test */
    public function cannot_update_status_aprovado_with_confere_anexos_without_inscricao_contrato_declaracao()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '012345/2020']);

        foreach(['Comprovante de inscrição CNPJ', 'Contrato Social', 'Declaração Termo de indicação RT ou Procuração'] as $tipo)
        {
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'conferir',
                'campo' => 'confere_anexos[]',
                'valor' => $tipo
            ])->assertStatus(200);
    
            $this->put(route('preregistro.update.status', $preRegistroCnpj->preRegistro->id), ['situacao' => 'aprovar'])
            ->assertSessionHasErrors('status');
    
            $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
            ->assertSeeText('Faltou confirmar a entrega dos anexos');
    
            $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
        }
    }

    /** @test */
    public function cannot_update_status_aprovado_with_justificativa()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '012345/2020']);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'razao_social',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200); 

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
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);

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
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '012345/2020']);

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
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '012345/2020']);

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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '012345/2020']);

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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'anexos_ok_pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->responsavelTecnico->update(['registro' => '012345/2020']);

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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        
        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeInOrder(['<p id="razao_social">', ' - Razão Social: </span>', $preRegistroCnpj->razao_social])
        ->assertSeeInOrder(['<p id="nire">', ' - NIRE: </span>', $preRegistroCnpj->nire])
        ->assertSeeInOrder(['<p id="tipo_empresa">', ' - Tipo da Empresa: </span>', $preRegistroCnpj->tipo_empresa])
        ->assertSeeInOrder(['<p id="dt_inicio_atividade">', ' - Data início da atividade: </span>', onlyDate($preRegistroCnpj->dt_inicio_atividade)])
        ->assertSeeInOrder(['<p id="capital_social">', ' - Capital Social: R$ </span>', $preRegistroCnpj->capital_social])
        ->assertSeeInOrder(['<p id="cep_empresa">', ' - CEP: </span>', $preRegistroCnpj->cep])
        ->assertSeeInOrder(['<p id="logradouro_empresa">', ' - Logradouro: </span>', $preRegistroCnpj->logradouro])
        ->assertSeeInOrder(['<p id="numero_empresa">', ' - Número: </span>', $preRegistroCnpj->numero])
        ->assertSeeInOrder(['<p id="complemento_empresa">', ' - Complemento: </span>', '------'])
        ->assertSeeInOrder(['<p id="bairro_empresa">', ' - Bairro: </span>', $preRegistroCnpj->bairro])
        ->assertSeeInOrder(['<p id="cidade_empresa">', ' - Município: </span>', $preRegistroCnpj->cidade])
        ->assertSeeInOrder(['<p id="uf_empresa">', ' - Estado: </span>', $preRegistroCnpj->uf])
        ->assertSeeInOrder(['<p id="checkEndEmpresa">', '<i class="fas fa-times text-danger"></i>', ' - Mesmo endereço da correspondência </span>']);
    }

    /** @test */
    public function view_pre_registro_cnpj_when_checkEndEmpresa_on()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $preRegistroCnpj->update([
            'cep' => $preRegistroCnpj->preRegistro->cep,
            'logradouro' => $preRegistroCnpj->preRegistro->logradouro,
            'numero' => $preRegistroCnpj->preRegistro->numero,
            'complemento' => $preRegistroCnpj->preRegistro->complemento,
            'bairro' => $preRegistroCnpj->preRegistro->bairro,
            'cidade' => $preRegistroCnpj->preRegistro->cidade,
            'uf' => $preRegistroCnpj->preRegistro->uf,
        ]);
        
        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeInOrder(['<p id="checkEndEmpresa">', '<i class="fas fa-check-circle text-success"></i>', ' - Mesmo endereço da correspondência </span>']);
    }

    /** @test */
    public function view_text_justificado_cnpj()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $justificativas = $preRegistroCnpj->preRegistro->fresh()->getJustificativaArray();

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText($justificativas['razao_social'])
        ->assertSeeText($justificativas['nire'])
        ->assertSeeText($justificativas['tipo_empresa'])
        ->assertSeeText($justificativas['dt_inicio_atividade'])
        ->assertSeeText($justificativas['nome_fantasia'])
        ->assertSeeText($justificativas['capital_social'])
        ->assertSeeText($justificativas['cep_empresa'])
        ->assertSeeText($justificativas['logradouro_empresa'])
        ->assertSeeText($justificativas['numero_empresa'])
        ->assertSeeText($justificativas['complemento_empresa'])
        ->assertSeeText($justificativas['bairro_empresa'])
        ->assertSeeText($justificativas['cidade_empresa'])
        ->assertSeeText($justificativas['uf_empresa'])
        ->assertSeeText($justificativas['checkEndEmpresa']);
    }

    /** @test */
    public function view_justifications_text_cnpj_by_url()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['checkPreRegistro' => 'on']), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);
        $data_hora = now()->format('Y-m-d H:i:s');

        foreach($keys as $campo)
            $this->get(route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo, 'data_hora' => urlencode($data_hora)]))
            ->assertJsonFragment([
                'justificativa' => PreRegistro::first()->getJustificativaPorCampoData($campo, $data_hora),
                'data_hora' => formataData($data_hora)
            ]);
    }

    /** @test */
    public function view_historico_justificativas_cnpj()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['checkPreRegistro' => 'on']), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);
        $data_hora = now()->format('Y-m-d H:i:s');

        foreach($keys as $campo)
            $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
            ->assertSee('value="'.route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo, 'data_hora' => urlencode($data_hora)]).'"');
    }

    /** @test */
    public function view_label_campo_alterado_pj()
    {
        $this->filled_campos_editados_pre_registros_cnpj_when_form_is_submitted_when_status_aguardando_correcao();
        
        $admin = $this->signIn(PreRegistro::first()->user);

        $camposEditados = json_decode(PreRegistro::first()->campos_editados, true);

        $this->get(route('preregistro.view', 1))
        ->assertSeeInOrder([
            '<a class="card-link" data-toggle="collapse" href="#parte_dados_gerais">',
            '<div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">',
            '2. Dados Gerais',
            '<span class="badge badge-danger ml-2">Campos alterados</span>',
            '<a class="card-link" data-toggle="collapse" href="#parte_endereco">',
            '<div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">',
            '3. Endereço',
            '<span class="badge badge-danger ml-2">Campos alterados</span>',
        ]);
            
        foreach($camposEditados as $key => $value)
            $this->get(route('preregistro.view', 1))->assertSeeInOrder([
                '<p id="'.$key.'">',
                '<span class="badge badge-danger ml-2">Campo alterado</span>',
                '</p>',
            ]);
    }

    /** @test */
    public function view_label_justificado_cnpj()
    {
        $this->view_text_justificado_cnpj();

        $admin = $this->signIn(PreRegistro::first()->user);

        $justificados = PreRegistro::first()->getJustificativaArray();
            
        foreach($justificados as $key => $value)
            $this->get(route('preregistro.view', 1))->assertSeeInOrder([
                '<p id="'.$key.'">',
                'type="button" ',
                'value="'.$key.'"',
                '<i class="fas fa-edit"></i>',
                '<span class="badge badge-warning just ml-2">Justificado</span>',
                '</p>',
            ]);
    }
}
