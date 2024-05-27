<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\PreRegistro;
use Carbon\Carbon;
use App\Socio;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Testing\WithFaker;

class SocioTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    const CPF_GERENTI = '86294373085';
    const CNPJ_GERENTI = '11748345000144';

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
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => formataCpfCnpj(factory('App\Socio')->raw()['cpf_cnpj'])
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
        $this->assertNotEquals($atual, PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO SÓCIOS VIA AJAX - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function can_create_10_socios_pf_by_ajax()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        for($i = 1; $i <= 10; $i++)
        {
            $socio = factory('App\Socio')->make()->attributesToArray();

            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => 'cpf_cnpj_socio',
                'valor' => $socio['cpf_cnpj']
            ])->assertStatus(200);
    
            $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj']]);
            $this->assertDatabaseHas('socio_pre_registro_cnpj', [
                'pre_registro_cnpj_id' => 1,
                'socio_id' => $i,
                'rt' => false
            ]);

            $this->assertEquals(Socio::count(), $i);
        }

        $this->assertEquals(Socio::count(), 10);
    }

    /** @test */
    public function can_update_table_socios_pf_by_ajax()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
    }

    /** @test */
    public function can_create_10_socios_pj_by_ajax()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        for($i = 1; $i <= 10; $i++)
        {
            $socio = factory('App\Socio')->states('pj')->make()->attributesToArray();

            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => 'cpf_cnpj_socio',
                'valor' => $socio['cpf_cnpj']
            ])->assertStatus(200);
    
            $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj']]);
            $this->assertDatabaseHas('socio_pre_registro_cnpj', [
                'pre_registro_cnpj_id' => 1,
                'socio_id' => $i,
                'rt' => false
            ]);

            $this->assertEquals(Socio::count(), $i);
        }

        $this->assertEquals(Socio::count(), 10);
    }

    /** @test */
    public function can_update_table_socios_pj_by_ajax()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->states('pj')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pj_by_ajax_with_inputs_pf()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $cpf_cnpj = factory('App\Socio')->states('pj')->raw()['cpf_cnpj'];

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $cpf_cnpj
        ])->assertStatus(200);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
        
        $socio = factory('App\Socio')->make()->makeHidden(['cpf_cnpj', 'registro'])->attributesToArray();

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(in_array($key, ['nome', 'cep', 'logradouro', 'bairro', 'numero', 'complemento', 'cidade', 'uf']) ? 200 : 500);
        
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $cpf_cnpj, 'nome' => $socio['nome'], 'cep' => $socio['cep']]);
        $this->assertDatabaseHas('socios', [
            'registro' => null, 'nome_social' => null, 'dt_nascimento' => null, 'identidade' => null, 'orgao_emissor' => null, 'nacionalidade' => null, 
            'naturalidade_estado' => null, 'nome_mae' => null, 'nome_pai' => null,
        ]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
    }

    /** @test */
    public function can_create_only_1_socio_rt_by_ajax()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->states('rt')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);
    
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj']]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => true
        ]);

        for($i = 1; $i <= 10; $i++)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => 'checkRT_socio',
                'valor' => 'on'
            ])->assertStatus(200);

        $this->assertEquals(Socio::count(), 1);
    }

    /** @test */
    public function can_update_table_socios_rt_by_ajax()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->states('rt')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 1
        ]);
        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);

        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_rt_by_ajax_with_inputs_pf()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $cpf_cnpj = factory('App\Socio')->states('rt')->raw()['cpf_cnpj'];

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 1
        ]);
        
        $socio = factory('App\Socio')->make()->makeHidden(['cpf_cnpj', 'registro'])->attributesToArray();

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(in_array($key, ['nacionalidade', 'naturalidade_estado']) ? 200 : 500);
        
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $cpf_cnpj, 'nacionalidade' => $socio['nacionalidade'], 'naturalidade_estado' => $socio['naturalidade_estado']]);
        $this->assertDatabaseHas('socios', [
            'registro' => null, 'nome' => null, 'nome_social' => null, 'dt_nascimento' => null, 'identidade' => null, 'orgao_emissor' => null, 'nome_mae' => null, 
            'nome_pai' => null,
        ]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => true
        ]);
    }

    /** @test */
    public function can_update_table_socios_pf_by_ajax_with_upperCase()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->states('low')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);
        
        foreach($socio as $key => $value)
            $socio[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;

        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function can_update_table_socios_pj_by_ajax_with_upperCase()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->states('pj', 'low')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);
        
        foreach($socio as $key => $value)
            $socio[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;

        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function can_update_table_socios_rt_by_ajax_with_upperCase()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->states('rt', 'low')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);
        
        foreach($socio as $key => $value)
            $socio[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        
        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 1
        ]);
    }

    /** @test */
    public function can_update_table_socios_pf_by_ajax_when_exists_others_pre_registros()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => Socio::count()
            ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 3,
            'socio_id' => Socio::count(),
            'rt' => 0
        ]);
    }

    /** @test */
    public function can_update_table_socios_pj_by_ajax_when_exists_others_pre_registros()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->states('pj')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => Socio::count()
            ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 3,
            'socio_id' => Socio::count(),
            'rt' => 0
        ]);
    }

    /** @test */
    public function can_update_table_socios_rt_by_ajax_when_exists_others_pre_registros()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->states('rt')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => Socio::count()
            ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 3,
            'socio_id' => Socio::count(),
            'rt' => 1
        ]);
    }

    /** @test */
    public function can_update_table_socios_pf_by_ajax_when_exists_others_pre_registros_with_same_socio()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $socio = $preRegistroCnpj_1->socios->get(0)->attributesToArray();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'naturalidade_estado_socio',
            'valor' => 'RJ',
            'id_socio' => $socio['id']
        ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj'], 'naturalidade_estado' => 'RJ', 'nome' => $socio['nome']]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 2,
            'socio_id' => $socio['id'],
            'rt' => 0
        ]);
    }

    /** @test */
    public function can_update_table_socios_pj_by_ajax_when_exists_others_pre_registros_with_same_socio()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $socio = $preRegistroCnpj_1->socios->get(1)->attributesToArray();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'uf_socio',
            'valor' => 'RJ',
            'id_socio' => $socio['id']
        ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj'], 'uf' => 'RJ', 'nome' => $socio['nome']]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 2,
            'socio_id' => $socio['id'],
            'rt' => 0
        ]);
    }

    /** @test */
    public function can_update_table_socios_rt_by_ajax_when_exists_others_pre_registros_with_same_socio()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->states('rt_socio')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $socio = $preRegistroCnpj_1->socios->where('pivot.rt', 1)->first()->attributesToArray();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $externo->load('preRegistro')->preRegistro->pessoaJuridica->update(['responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'naturalidade_estado_socio',
            'valor' => 'RJ',
            'id_socio' => $socio['id']
        ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj'], 'naturalidade_estado' => 'RJ']);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 2,
            'socio_id' => $socio['id'],
            'rt' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);
        
        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socio',
                'campo' => $key.'_erro',
                'valor' => $value,
                'id_socio' => 1
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);
        
        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);
        
        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socio',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);
        
        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socio',
                'campo' => '',
                'valor' => $value,
                'id_socio' => 1
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_with_input_type_text_more_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $socio = [
            'nome' => $this->faker()->text(500),
            'nome_social' => $this->faker()->text(500),
            'logradouro' => $this->faker()->text(500),
            'complemento' => $this->faker()->text(500),
            'bairro' => $this->faker()->text(500),
            'cidade' => $this->faker()->text(500),
            'nome_mae' => $this->faker()->text(500),
            'nome_pai' => $this->faker()->text(500),
            'identidade' => $this->faker()->text(500),
            'orgao_emissor' => $this->faker()->text(500),
        ];
                
        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertSessionHasErrors('valor');
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_with_cpf_cnpj_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => factory('App\Socio')->raw()['cpf_cnpj'] . '5'
        ])->assertSessionHasErrors('valor');

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => factory('App\Socio')->states('pj')->raw()['cpf_cnpj'] . '5'
        ])->assertSessionHasErrors('valor');

        $this->assertEquals(Socio::count(), 0);

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_with_input_registro()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'registro_socio',
            'valor' => '1234567890',
            'id_socio' => 1
        ])->assertSessionHasErrors('campo.*');

        $this->assertDatabaseMissing('socios', [
            'registro' => '1234567890'
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'registro',
            'valor' => '1234567890',
            'id_socio' => 1
        ])->assertSessionHasErrors('campo');

        $this->assertDatabaseMissing('socios', [
            'registro' => '1234567890'
        ]);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_by_ajax_with_cep_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cep_socio',
            'valor' => '1234567890',
            'id_socio' => 1
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', [
            'cep' => '1234567890'
        ]);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_by_ajax_under_18_years_old()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'dt_nascimento_socio',
            'valor' => Carbon::today()->subYears(17)->format('Y-m-d'),
            'id_socio' => 1
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', [
            'dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')
        ]);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_by_ajax_with_dt_expedicao_after_today()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'dt_expedicao_socio',
            'valor' => Carbon::today()->addDay()->format('Y-m-d'),
            'id_socio' => 1
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', [
            'dt_nascimento' => Carbon::today()->addDay()->format('Y-m-d')
        ]);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_by_ajax_without_date_type()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $datas = [
            'dt_nascimento' => null, 
            // 'dt_expedicao' => null
        ];

        foreach($datas as $key => $value) 
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key . '_socio',
                'valor' => 'texto',
                'id_socio' => 1
            ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('socios', $datas);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_by_ajax_with_uf_socio_value_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'uf_socio',
            'valor' => 'TT',
            'id_socio' => 1
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['uf' => 'TT']);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_rt_by_ajax_with_nacionalidade_socio_value_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        // PF
        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'nacionalidade_socio',
            'valor' => 'Brasileiro',
            'id_socio' => 1
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 1, 'nacionalidade' => 'Brasileiro']);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);

        // RT
        $socio = factory('App\Socio')->states('rt')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'nacionalidade_socio',
            'valor' => 'Brasileirada',
            'id_socio' => 2
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 2, 'nacionalidade' => 'Brasileirada']);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 2,
            'rt' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_rt_by_ajax_with_naturalidade_estado_socio_value_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        // PF
        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'naturalidade_estado_socio',
            'valor' => 'DR',
            'id_socio' => 1
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 1, 'naturalidade_estado' => 'DR']);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);

        // RT
        $socio = factory('App\Socio')->states('rt')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'naturalidade_estado_socio',
            'valor' => 'ER',
            'id_socio' => 2
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 2, 'naturalidade_estado' => 'ER']);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 2,
            'rt' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_when_exists_cnpj_in_contabeis_table()
    {
        $contabil = factory('App\Contabil')->create();

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $contabil['cnpj']
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 1]);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_when_exists_cnpj_deleted_in_contabeis_table()
    {
        $contabil = factory('App\Contabil')->create();
        $cnpj = $contabil->cnpj;
        $contabil->delete();

        $this->assertDatabaseMissing('contabeis', [
            'deleted_at' => null
        ]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $cnpj
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 1]);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_with_cnpj_pre_registro()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $externo->cpf_cnpj
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 1]);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_with_cpf_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->states('rt')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 1]);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_by_ajax_without_relationship()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);

        foreach($socio as $key => $value){
            if($key != 'cpf_cnpj')
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'pessoaJuridica.socios',
                    'campo' => $key . '_socio',
                    'valor' => $value,
                    'id_socio' => 1
                ])->assertStatus(500);
        }
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }
    
    /** @test */
    public function cannot_update_table_socios_pj_by_ajax_without_relationship()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->states('pj')->make()->attributesToArray();
        unset($socio['registro']);

        foreach($socio as $key => $value){
            if($key != 'cpf_cnpj')
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'pessoaJuridica.socios',
                    'campo' => $key . '_socio',
                    'valor' => $value,
                    'id_socio' => 1
                ])->assertStatus(500);
        }
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_when_remove_relationship()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertOk();
        
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj']]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => '',
            'id_socio' => 1
        ])->assertOk();

        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj']]);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);

        foreach($socio as $key => $value){
            if($key != 'cpf_cnpj')
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'pessoaJuridica.socios',
                    'campo' => $key . '_socio',
                    'valor' => $value,
                    'id_socio' => 1
                ])->assertStatus(500);
        }
        
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj']]);
        $this->assertDatabaseMissing('socios', Arr::except($socio, ['cpf_cnpj']));
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function get_socio_pf_by_ajax_when_exists_in_database()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->create();

        $response = $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio->cpf_cnpj
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => $socio->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertStringContainsString('<div id="socio_'. $socio->id .'_box">', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('<span class="label_complemento bold">Complemento:</span> <span class="complemento_socio editar_dado">-----</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('Órgão Emissor:</span> <span class="orgao_emissor_socio editar_dado">' . $socio->orgao_emissor . '</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);
    }

    /** @test */
    public function get_socio_pj_by_ajax_when_exists_in_database()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->states('pj')->create();

        $response = $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio->cpf_cnpj
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => $socio->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertStringContainsString('<div id="socio_'. $socio->id .'_box">', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('<span class="label_complemento bold">Complemento:</span> <span class="complemento_socio editar_dado">-----</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringNotContainsString('Órgão Emissor:</span> <span class="orgao_emissor_socio editar_dado">' . $socio->orgao_emissor . '</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);
    }

    /** @test */
    public function get_socio_rt_by_ajax_when_exists_in_database()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->states('rt')->create();

        $response = $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => true
            ]
        ]);

        $this->assertStringContainsString('<div id="socio_'. $socio->id .'_box">', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('<p class="text-danger mb-2"><strong><i>Dados do Responsável Técnico na aba "Contato / RT", em "Sócios" somente dados complementares.</i></strong></p>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('<span class="label_naturalidade_estado bold">Naturalidade:</span> <span class="naturalidade_estado_socio editar_dado">'. $socio->naturalidade_estado .'</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringNotContainsString('<span class="label_complemento bold">Complemento:</span> <span class="complemento_socio editar_dado">-----</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringNotContainsString('Órgão Emissor:</span> <span class="orgao_emissor_socio editar_dado">' . $socio->orgao_emissor . '</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);
    }

    /** @test */
    public function get_socio_pf_by_ajax_when_exists_in_gerenti()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5 / 2
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = [
            'registro' => '0000000001', 
            'nome' => 'RC TESTE 1', 
            'identidade' => '111111111',
            'orgao_emissor' => 'SSP-SP',
            'nome_pai' => 'PAI 1',
            'nome_mae' => 'MAE 1',
            'dt_nascimento' => '1962-09-30',
        ];

        $response = $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertStringContainsString(' - CPF: <strong>'. formataCpfCnpj(self::CPF_GERENTI) . '</strong>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('<span class="label_registro bold">Registro:</span> <span class="registro_socio editar_dado">'. formataRegistro($socio['registro']) .'</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertDatabaseHas('socios', $socio);
    }

    /** @test */
    public function get_socio_pj_by_ajax_when_exists_in_gerenti()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = [
            'registro' => '0000000002', 
            'nome' => 'RC TESTE 2',
        ];

        $response = $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CNPJ_GERENTI
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertStringContainsString(' - CNPJ: <strong>'. formataCpfCnpj(self::CNPJ_GERENTI) . '</strong>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('<span class="label_registro bold">Registro:</span> <span class="registro_socio editar_dado">'. formataRegistro($socio['registro']) .'</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertDatabaseHas('socios', $socio);
    }

    /** @test */
    public function can_update_table_socios_pf_by_ajax_when_exists_in_gerenti_and_empty_input_registro_in_database()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5 / 2
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->create([
            'cpf_cnpj' => self::CPF_GERENTI
        ]);

        $this->assertDatabaseHas('socios', ['registro' => null, 'cpf_cnpj' => self::CPF_GERENTI]);

        $socio = [
            'registro' => '0000000001', 
            'nome' => 'RC TESTE 1', 
            'identidade' => '111111111',
            'orgao_emissor' => 'SSP-SP',
            'nome_pai' => 'PAI 1',
            'nome_mae' => 'MAE 1',
            'dt_nascimento' => '1962-09-30',
        ];

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertDatabaseHas('socios', $socio);
    }

    /** @test */
    public function can_update_table_socios_pj_by_ajax_when_exists_in_gerenti_and_empty_input_registro_in_database()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->states('pj')->create([
            'cpf_cnpj' => self::CNPJ_GERENTI
        ]);

        $this->assertDatabaseHas('socios', ['registro' => null, 'cpf_cnpj' => self::CNPJ_GERENTI]);

        $socio = [
            'registro' => '0000000002', 
            'nome' => 'RC TESTE 2', 
        ];

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CNPJ_GERENTI
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertDatabaseHas('socios', $socio);
    }

    /** @test */
    public function can_update_table_socios_pf_by_ajax_when_non_exists_in_gerenti_and_fill_input_registro_in_database()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo CANCELADO para T
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = [
            'registro' => '0000000001', 
            'nome' => 'RC TESTE 1', 
            'identidade' => '111111111',
            'orgao_emissor' => 'SSP-SP',
            'nome_pai' => 'PAI 1',
            'nome_mae' => 'MAE 1',
            'dt_nascimento' => '1962-09-30',
        ];

        $socio = factory('App\Socio')->create([
            'cpf_cnpj' => self::CPF_GERENTI,
            'registro' => $socio['registro'],
        ]);

        $this->assertDatabaseHas('socios', ['registro' => $socio['registro'], 'cpf_cnpj' => self::CPF_GERENTI]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertDatabaseHas('socios', ['registro' => null, 'cpf_cnpj' => self::CPF_GERENTI, 'nome' => $socio['nome']]);
    }

    /** @test */
    public function can_update_table_socios_pj_by_ajax_when_non_exists_in_gerenti_and_fill_input_registro_in_database()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa juridica, no campo CANCELADO para T
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = [
            'registro' => '0000000002', 
            'nome' => 'RC TESTE 2', 
        ];

        $socio = factory('App\Socio')->create([
            'cpf_cnpj' => self::CNPJ_GERENTI,
            'registro' => $socio['registro'],
        ]);

        $this->assertDatabaseHas('socios', ['registro' => $socio['registro'], 'cpf_cnpj' => self::CNPJ_GERENTI]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CNPJ_GERENTI
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertDatabaseHas('socios', ['registro' => null, 'cpf_cnpj' => self::CNPJ_GERENTI, 'nome' => $socio['nome']]);
    }

    /** @test */
    public function can_update_table_socios_by_ajax_when_clean_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => '',
                'id_socio' => 1
            ])->assertStatus(200);
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_socios_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado()
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $preRegistro = factory('App\PreRegistroCnpj')->states('rt_socio')->create();

        $rtAjax = array();

        foreach($preRegistro->socios as $socio)
            array_push($rtAjax, $socio->arrayValidacaoInputs());

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
            {
                foreach($rtAjax as $id => $socio)
                {
                    ++$id;
                    foreach($socio as $key => $value)
                        $key == 'cpf_cnpj_socio_' . $id ? null : $this->post(route('externo.inserir.preregistro.ajax'), [
                            'classe' => 'pessoaJuridica.socios',
                            'campo' => str_replace('_' . $id, '', $key),
                            'valor' => '',
                            'id_socio' => $id
                        ])->assertStatus(401);
                }
            }
        }
    }

    /** @test */
    public function can_update_table_socios_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado()
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $preRegistro = factory('App\PreRegistroCnpj')->states('rt_socio')->create();

        $rtAjax = array();

        foreach($preRegistro->socios as $socio)
            array_push($rtAjax, $socio->arrayValidacaoInputs());

        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            foreach($rtAjax as $id => $socio)
            {
                ++$id;
                foreach($socio as $key => $value)
                    $key == 'cpf_cnpj_socio_' . $id ? null : $this->post(route('externo.inserir.preregistro.ajax'), [
                        'classe' => 'pessoaJuridica.socios',
                        'campo' => str_replace('_' . $id, '', $key),
                        'valor' => '',
                        'id_socio' => $id
                    ])->assertStatus(200);
            }
        }
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO SÓCIOS VIA SUBMIT - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function can_submit_socios_if_exists_in_database()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertOk();

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $socio1 = $pr->socios->get(0)->attributesToArray();
        $socio2 = $pr->socios->get(1)->attributesToArray();

        $this->assertDatabaseHas('socios', $socio1);
        $this->assertDatabaseHas('socios', $socio2);
        $this->assertEquals(Socio::count(), 2);
    }

    /** @test */
    public function can_submit_socios_rt_if_exists_in_database()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertOk();

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $socio1 = $pr->socios->get(0)->attributesToArray();
        $socio2 = $pr->socios->get(1)->attributesToArray();
        $sociort = $pr->socios->get(2)->attributesToArray();

        $this->assertDatabaseHas('socios', $socio1);
        $this->assertDatabaseHas('socios', $socio2);
        $this->assertDatabaseHas('socios', $sociort);
        $this->assertDatabaseHas('responsaveis_tecnicos', ['cpf' => $sociort['cpf_cnpj']]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 3,
            'rt' => true
        ]);
        $this->assertEquals(Socio::count(), 3);
    }

    /** @test */
    public function can_submit_socio_pf_if_exists_in_gerenti()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5 / 2
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CPF_GERENTI
        ]);

        Socio::find(3)->update(Arr::except(factory('App\Socio')->raw(), ['cpf_cnpj', 'registro']));

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('socios', [
            'id' => 3,
            'cpf_cnpj' => self::CPF_GERENTI,
            'registro' => '0000000001'
        ]);
        
        $this->assertEquals(Socio::count(), 3);
    }

    /** @test */
    public function can_submit_socio_pj_if_exists_in_gerenti()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5 / 2
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CNPJ_GERENTI
        ]);

        Socio::find(3)->update(Arr::except(factory('App\Socio')->states('pj')->raw(), ['cpf_cnpj', 'registro']));

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('socios', [
            'id' => 3,
            'cpf_cnpj' => self::CNPJ_GERENTI,
            'registro' => '0000000002'
        ]);
        
        $this->assertEquals(Socio::count(), 3);
    }

    /** @test */
    public function can_submit_socio_pf_without_optional_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $socio = factory('App\PreRegistroCnpj')->create();
        Socio::first()->update(['registro' => null, 'nome_social' => null, 'complemento' => null]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));
        
        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('socios', Socio::first()->attributesToArray());

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_socio_pj_without_optional_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $socio = factory('App\PreRegistroCnpj')->create();
        Socio::find(2)->update(['registro' => null, 'complemento' => null]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));
        
        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('socios', Socio::find(2)->attributesToArray());

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function cannot_submit_socio_pf_without_required_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        factory('App\PreRegistroCnpj')->create();
        $socio = Arr::except(Socio::first()->attributesToArray(), ['id', 'cpf_cnpj', 'registro', 'nome_social', 'complemento', 'created_at', 'updated_at', 'deleted_at']);
        Socio::first()->update(array_fill_keys(array_keys($socio), null));
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors([
            'nome_socio_1', 'dt_nascimento_socio_1', 'cep_socio_1', 'logradouro_socio_1', 'numero_socio_1', 'bairro_socio_1', 'cidade_socio_1', 'uf_socio_1', 
            'nome_mae_socio_1', 'identidade_socio_1', 'orgao_emissor_socio_1', 'nome_pai_socio_1', 'nacionalidade_socio_1'
        ]);

        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('socios', Socio::first()->attributesToArray());

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_CRIADO);
    }

    /** @test */
    public function cannot_submit_socio_pj_without_required_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        factory('App\PreRegistroCnpj')->create();
        $socio = Arr::only(Socio::find(2)->attributesToArray(), ['nome', 'cep', 'logradouro', 'numero', 'bairro', 'cidade', 'uf']);
        Socio::find(2)->update(array_fill_keys(array_keys($socio), null));
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors([
            'nome_socio_2', 'cep_socio_2', 'logradouro_socio_2', 'numero_socio_2', 'bairro_socio_2', 'cidade_socio_2', 'uf_socio_2'
        ]);

        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('socios', Socio::find(2)->attributesToArray());

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_CRIADO);
    }

    /** @test */
    public function cannot_submit_socio_rt_without_required_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $socio = Arr::only(Socio::find(3)->attributesToArray(), ['nacionalidade', 'naturalidade_estado']);
        Socio::find(3)->update(array_fill_keys(array_keys($socio), null));
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors([
            'nacionalidade_socio_3'
        ]);

        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('socios', Socio::find(3)->attributesToArray());

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_CRIADO);
    }

    /** @test */
    public function cannot_submit_socio_without_cpf_cnpj()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios()->detach();
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_cnpj_socio_');
    }

    /** @test */
    public function cannot_submit_socio_with_cpf_cnpj_with_wrong_value()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cpf_cnpj' => '12345678901']);
        $pr->socios->get(1)->update(['cpf_cnpj' => '12345678901234']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_cnpj_socio_1', 'cpf_cnpj_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_with_cpf_cnpj_exists_in_contabeis()
    {
        $contabil = factory('App\Contabil')->create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(1)->update(['cpf_cnpj' => $contabil->cnpj]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_cnpj_socio_2');

        $cnpj = $contabil->cnpj;
        $contabil->delete();
        $pr->socios->get(1)->update(['cpf_cnpj' => $cnpj]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_cnpj_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pj_with_cpf_cnpj_equals_user_externo()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(1)->update(['cpf_cnpj' => $externo->cpf_cnpj]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_cnpj_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_with_cpf_cnpj_equals_cpf_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cpf_cnpj' => $pr->responsavelTecnico->cpf]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_cnpj_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_nome_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome' => null]);
        $pr->socios->get(1)->update(['nome' => null]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_socio_1', 'nome_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_nome_socio_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome' => 'Nome']);
        $pr->socios->get(1)->update(['nome' => 'Nome']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_socio_1', 'nome_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_nome_socio_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome' => $this->faker()->text(500)]);
        $pr->socios->get(1)->update(['nome' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_socio_1', 'nome_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_nome_socio_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome' => 'N0me com númer0']);
        $pr->socios->get(1)->update(['nome' => 'N0me com númer0']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_socio_1', 'nome_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_nome_social_socio_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_social' => 'Nome']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_nome_social_socio_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_social' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_nome_social_socio_rt_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_social' => 'Nom3 com numeros']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_without_dt_nascimento_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['dt_nascimento' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_dt_nascimento_socio_incorrect_format()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['dt_nascimento' => '2000/01/01']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_dt_nascimento_socio_without_date_type()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['dt_nascimento' => 'texto']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_dt_nascimento_socio_under_18_years_old()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
       
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_without_identidade_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['identidade' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_identidade_socio_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['identidade' => '12A']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_identidade_socio_more_than_30_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
       
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['identidade' => '123456789012345678901234567890123']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_without_orgao_emissor_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['orgao_emissor' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_orgao_emissor_socio_less_than_3_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['orgao_emissor' => 'sd']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_orgao_emissor_socio_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['orgao_emissor' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_cep_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cep' => '']);
        $pr->socios->get(1)->update(['cep' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_socio_1', 'cep_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_cep_socio_more_than_9_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cep' => '012345698']);
        $pr->socios->get(1)->update(['cep' => '012345698']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_socio_1', 'cep_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_cep_socio_incorrect_format()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cep' => '012-12365']);
        $pr->socios->get(1)->update(['cep' => '012-12365']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_socio_1', 'cep_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_bairro_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['bairro' => '']);
        $pr->socios->get(1)->update(['bairro' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_socio_1', 'bairro_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_bairro_socio_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['bairro' => 'Bai']);
        $pr->socios->get(1)->update(['bairro' => 'Bai']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_socio_1', 'bairro_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_bairro_socio_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['bairro' => $this->faker()->text(500)]);
        $pr->socios->get(1)->update(['bairro' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_socio_1', 'bairro_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_logradouro_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['logradouro' => '']);
        $pr->socios->get(1)->update(['logradouro' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_socio_1', 'logradouro_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_logradouro_socio_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['logradouro' => 'Log']);
        $pr->socios->get(1)->update(['logradouro' => 'Log']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_socio_1', 'logradouro_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_logradouro_socio_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['logradouro' => $this->faker()->text(500)]);
        $pr->socios->get(1)->update(['logradouro' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_socio_1', 'logradouro_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_numero_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['numero' => '']);
        $pr->socios->get(1)->update(['numero' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('numero_socio_1', 'numero_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_numero_socio_more_than_10_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['numero' => '123456789lp']);
        $pr->socios->get(1)->update(['numero' => '123456789lp']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('numero_socio_1', 'numero_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_complemento_socio_more_than_50_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['complemento' => $this->faker()->text(200)]);
        $pr->socios->get(1)->update(['complemento' => $this->faker()->text(200)]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('complemento_socio_1', 'complemento_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_cidade_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cidade' => '']);
        $pr->socios->get(1)->update(['cidade' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cidade_socio_1', 'cidade_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_cidade_socio_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cidade' => $this->faker()->text(500)]);
        $pr->socios->get(1)->update(['cidade' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cidade_socio_1', 'cidade_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_uf_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['uf' => '']);
        $pr->socios->get(1)->update(['uf' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('uf_socio_1', 'uf_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_uf_socio_with_wrong_value()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['uf' => 'UF']);
        $pr->socios->get(1)->update(['uf' => 'UF']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('uf_socio_1', 'uf_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_without_nome_mae_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_mae' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_nome_mae_socio_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_mae' => 'Mãen']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_nome_mae_socio_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_mae' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_nome_mae_socio_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_mae' => 'M4mãe']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_without_nome_pai_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_pai' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_rt_without_nome_pai_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->responsavelTecnico->update(['nome_pai' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_with_nome_pai_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->responsavelTecnico->update(['nome_pai' => 'paiz']);
        $pr->socios->get(0)->update(['nome_pai' => 'paiz']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt', 'nome_pai_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_with_nome_pai_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->responsavelTecnico->update(['nome_pai' => $this->faker()->text(500)]);
        $pr->socios->get(0)->update(['nome_pai' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt', 'nome_pai_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_with_nome_pai_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->responsavelTecnico->update(['nome_pai' => 'pa1 teste']);
        $pr->socios->get(0)->update(['nome_pai' => 'pa1 teste']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt', 'nome_pai_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_without_nacionalidade_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->socios->get(0)->update(['nacionalidade' => '']);
        $pr->socios->get(2)->update(['nacionalidade' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nacionalidade_socio_1', 'nacionalidade_socio_3');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_with_nacionalidade_socio_with_wrong_value()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->socios->get(0)->update(['nacionalidade' => 'BRASILEIRO']);
        $pr->socios->get(2)->update(['nacionalidade' => 'BRASILEIRO']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nacionalidade_socio_1', 'nacionalidade_socio_3');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_without_naturalidade_estado_socio_if_input_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->socios->get(0)->update(['naturalidade_estado' => '']);
        $pr->socios->get(2)->update(['naturalidade_estado' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_estado_socio_1', 'naturalidade_estado_socio_3');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_with_naturalidade_estado_socio_with_wrong_value()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->socios->get(0)->update(['naturalidade_estado' => 'BR']);
        $pr->socios->get(2)->update(['naturalidade_estado' => 'TR']);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_estado_socio_1', 'naturalidade_estado_socio_3');
    }

    /** @test */
    public function filled_campos_editados_socios_when_form_is_submitted_when_status_aguardando_correcao()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $PreRegistroCnpj = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $PreRegistroCnpj->socios()->attach(factory('App\Socio')->create()->id);

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

        // Remove RT e novo sócio
        $PreRegistroCnpj->socios()->detach(3);
        $PreRegistroCnpj->socios()->detach(4);
        
        // PF
        $campos = Arr::except(factory('App\Socio')->raw([
            'identidade' => '2211111135', 'orgao_emissor' => 'SSP - PB', 'cep' => '03021-030', 'logradouro' => 'RUA TESTE DO SÓCIO PF NOVO', 'numero' => '155A',
            'complemento' => 'FINAL', 'bairro' => 'TESTE BAIRRO SÓCIO PF NOVO', 'cidade' => 'OSASCO', 'uf' => 'MG', 'nacionalidade' => 'CHILENA', 
            'naturalidade_estado' => null, 'dt_nascimento' => now()->subYears(33)->format('Y-m-d'),
        ]), ['cpf_cnpj', 'registro']);
        foreach($campos as $key => $value){
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key . '_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);
            $campos[$key] = $key . '_socio_1';
        }

        // PJ
        $temp = Arr::only(factory('App\Socio')->states('pj')->raw([
            'cep' => '03021-030', 'logradouro' => 'RUA TESTE DO SÓCIO PJ NOVO', 'numero' => '155A', 'complemento' => 'FINAL', 'bairro' => 'TESTE BAIRRO SÓCIO PJ NOVO', 
            'cidade' => 'OSASCO', 'uf' => 'MG',
        ]), ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'nome']);
        foreach($temp as $key => $value){
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key . '_socio',
                'valor' => $value,
                'id_socio' => 2
            ])->assertStatus(200);
            $temp[$key] = $key . '_socio_2';
        }

        array_push($campos, 'checkRT_socio');
        $campos = array_merge(array_flip($campos), array_flip($temp), ["removidos_socio" => "3, 4"]);

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $arrayFinal = array_diff(array_keys(PreRegistro::first()->getCamposEditados()), array_keys($campos));
        $this->assertEquals($arrayFinal, array());
        $arrayFinal = array_diff(array_keys($campos), array_keys(PreRegistro::first()->getCamposEditados()));
        $this->assertEquals($arrayFinal, array());
    }

    /** @test */
    public function filled_campos_editados_socios_rt_when_form_is_submitted_when_status_aguardando_correcao()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $PreRegistroCnpj = factory('App\PreRegistroCnpj')->create();

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

        // Remove Sócio PF e PJ
        $PreRegistroCnpj->socios()->detach(1);
        $PreRegistroCnpj->socios()->detach(2);
        
        // RT
        $campos = ['checkRT' => 'on', 'nacionalidade' => 'BRASILEIRA', 'naturalidade_estado' => 'PB'];
        foreach($campos as $key => $value){
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key . '_socio',
                'valor' => $value,
                $key == 'checkRT' ? null : 'id_socio' => 3
            ])->assertStatus(200);
            $campos[$key] = $key == 'checkRT' ? $key . '_socio' : $key . '_socio_3';
        }

        $campos = array_merge(array_flip($campos), ["removidos_socio" => "1, 2"]);

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $arrayFinal = array_diff(array_keys(PreRegistro::first()->getCamposEditados()), array_keys($campos));
        $this->assertEquals($arrayFinal, array());
        $arrayFinal = array_diff(array_keys($campos), array_keys(PreRegistro::first()->getCamposEditados()));
        $this->assertEquals($arrayFinal, array());
    }

    /** @test */
    public function view_justifications_socios()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->socios->get(0)->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at'])->attributesToArray());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo . '_socio',
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('user_externo', $externo);

        foreach($keys as $campo)
            $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
            ->assertSeeInOrder([
                '<a class="nav-link" data-toggle="pill" href="#parte_socios">',
                'Sócios&nbsp',
                '<span class="badge badge-danger">',
                '</a>',
            ])
            ->assertSee('value="'. route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo . '_socio']) .'"');
    }

    /** @test */
    public function view_justifications_text_socios()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->socios->get(0)->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at'])->attributesToArray());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo . '_socio',
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        foreach($keys as $campo)
            $this->get(route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo . '_socio']))
            ->assertJsonFragment(['justificativa' => PreRegistro::first()->getJustificativaPorCampo($campo . '_socio')]);
    }

    /** 
     * ===================================================================================================================================
     * TESTES PRE-REGISTRO SÓCIOS VIA AJAX - LOGIN CONTABILIDADE RESPONSÁVEL PELO GERENCIAMENTO PARA O USUARIO EXTERNO COMUM
     * ===================================================================================================================================
     */

     /** @test */
    public function can_create_10_socios_pf_by_ajax_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        for($i = 1; $i <= 10; $i++)
        {
            $socio = factory('App\Socio')->make()->attributesToArray();

            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => 'cpf_cnpj_socio',
                'valor' => $socio['cpf_cnpj']
            ])->assertStatus(200);
    
            $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj']]);
            $this->assertDatabaseHas('socio_pre_registro_cnpj', [
                'pre_registro_cnpj_id' => 1,
                'socio_id' => $i,
                'rt' => false
            ]);

            $this->assertEquals(Socio::count(), $i);
        }

        $this->assertEquals(Socio::count(), 10);
    }

    /** @test */
    public function can_update_table_socios_pf_by_ajax_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
    }

    /** @test */
    public function can_create_10_socios_pj_by_ajax_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        for($i = 1; $i <= 10; $i++)
        {
            $socio = factory('App\Socio')->states('pj')->make()->attributesToArray();

            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => 'cpf_cnpj_socio',
                'valor' => $socio['cpf_cnpj']
            ])->assertStatus(200);
    
            $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj']]);
            $this->assertDatabaseHas('socio_pre_registro_cnpj', [
                'pre_registro_cnpj_id' => 1,
                'socio_id' => $i,
                'rt' => false
            ]);

            $this->assertEquals(Socio::count(), $i);
        }

        $this->assertEquals(Socio::count(), 10);
    }

    /** @test */
    public function can_update_table_socios_pj_by_ajax_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->states('pj')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pj_by_ajax_with_inputs_pf_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $cpf_cnpj = factory('App\Socio')->states('pj')->raw()['cpf_cnpj'];

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $cpf_cnpj
        ])->assertStatus(200);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
        
        $socio = factory('App\Socio')->make()->makeHidden(['cpf_cnpj', 'registro'])->attributesToArray();

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(in_array($key, ['nome', 'cep', 'logradouro', 'bairro', 'numero', 'complemento', 'cidade', 'uf']) ? 200 : 500);
        
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $cpf_cnpj, 'nome' => $socio['nome'], 'cep' => $socio['cep']]);
        $this->assertDatabaseHas('socios', [
            'registro' => null, 'nome_social' => null, 'dt_nascimento' => null, 'identidade' => null, 'orgao_emissor' => null, 'nacionalidade' => null, 
            'naturalidade_estado' => null, 'nome_mae' => null, 'nome_pai' => null,
        ]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
    }

    /** @test */
    public function can_create_only_1_socio_rt_by_ajax_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->states('rt')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);
    
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj']]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => true
        ]);

        for($i = 1; $i <= 10; $i++)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => 'checkRT_socio',
                'valor' => 'on'
            ])->assertStatus(200);

        $this->assertEquals(Socio::count(), 1);
    }

    /** @test */
    public function can_update_table_socios_rt_by_ajax_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->states('rt')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 1
        ]);
        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);

        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_rt_by_ajax_with_inputs_pf_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $cpf_cnpj = factory('App\Socio')->states('rt')->raw()['cpf_cnpj'];

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 1
        ]);
        
        $socio = factory('App\Socio')->make()->makeHidden(['cpf_cnpj', 'registro'])->attributesToArray();

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(in_array($key, ['nacionalidade', 'naturalidade_estado']) ? 200 : 500);
        
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $cpf_cnpj, 'nacionalidade' => $socio['nacionalidade'], 'naturalidade_estado' => $socio['naturalidade_estado']]);
        $this->assertDatabaseHas('socios', [
            'registro' => null, 'nome' => null, 'nome_social' => null, 'dt_nascimento' => null, 'identidade' => null, 'orgao_emissor' => null, 'nome_mae' => null, 
            'nome_pai' => null,
        ]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => true
        ]);
    }

    /** @test */
    public function can_update_table_socios_pf_by_ajax_with_upperCase_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->states('low')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);
        
        foreach($socio as $key => $value)
            $socio[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;

        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function can_update_table_socios_pj_by_ajax_with_upperCase_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->states('pj', 'low')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);
        
        foreach($socio as $key => $value)
            $socio[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;

        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function can_update_table_socios_rt_by_ajax_with_upperCase_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->states('rt', 'low')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);
        
        foreach($socio as $key => $value)
            $socio[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        
        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 1
        ]);
    }

    /** @test */
    public function can_update_table_socios_pf_by_ajax_when_exists_others_pre_registros_by_contabilidade()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 3]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 3]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => Socio::count()
            ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 3,
            'socio_id' => Socio::count(),
            'rt' => 0
        ]);
    }

    /** @test */
    public function can_update_table_socios_pj_by_ajax_when_exists_others_pre_registros_by_contabilidade()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->states('pj')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 3]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 3]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => Socio::count()
            ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 3,
            'socio_id' => Socio::count(),
            'rt' => 0
        ]);
    }

    /** @test */
    public function can_update_table_socios_rt_by_ajax_when_exists_others_pre_registros_by_contabilidade()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->states('rt')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 3]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 3]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => Socio::count()
            ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 3,
            'socio_id' => Socio::count(),
            'rt' => 1
        ]);
    }

    /** @test */
    public function can_update_table_socios_pf_by_ajax_when_exists_others_pre_registros_with_same_socio_by_contabilidade()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = $preRegistroCnpj_1->socios->get(0)->attributesToArray();
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 2]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 2]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'naturalidade_estado_socio',
            'valor' => 'RJ',
            'id_socio' => $socio['id']
        ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj'], 'naturalidade_estado' => 'RJ', 'nome' => $socio['nome']]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 2,
            'socio_id' => $socio['id'],
            'rt' => 0
        ]);
    }

    /** @test */
    public function can_update_table_socios_pj_by_ajax_when_exists_others_pre_registros_with_same_socio_by_contabilidade()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = $preRegistroCnpj_1->socios->get(1)->attributesToArray();
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 2]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 2]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'uf_socio',
            'valor' => 'RJ',
            'id_socio' => $socio['id']
        ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj'], 'uf' => 'RJ', 'nome' => $socio['nome']]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 2,
            'socio_id' => $socio['id'],
            'rt' => 0
        ]);
    }

    /** @test */
    public function can_update_table_socios_rt_by_ajax_when_exists_others_pre_registros_with_same_socio_by_contabilidade()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->states('rt_socio')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = $preRegistroCnpj_1->socios->where('pivot.rt', 1)->first()->attributesToArray();

        $externo->load('preRegistros')->preRegistros->find(2)->pessoaJuridica->update(['responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id]);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 2]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 2]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'naturalidade_estado_socio',
            'valor' => 'RJ',
            'id_socio' => $socio['id']
        ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj'], 'naturalidade_estado' => 'RJ']);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 2,
            'socio_id' => $socio['id'],
            'rt' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_wrong_input_name_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        
        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);
        
        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socio',
                'campo' => $key.'_erro',
                'valor' => $value,
                'id_socio' => 1
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_without_classe_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);
        
        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => '',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_wrong_classe_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);
        
        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socio',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_without_campo_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);
        
        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socio',
                'campo' => '',
                'valor' => $value,
                'id_socio' => 1
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_with_input_type_text_more_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $socio = [
            'nome' => $this->faker()->text(500),
            'nome_social' => $this->faker()->text(500),
            'logradouro' => $this->faker()->text(500),
            'complemento' => $this->faker()->text(500),
            'bairro' => $this->faker()->text(500),
            'cidade' => $this->faker()->text(500),
            'nome_mae' => $this->faker()->text(500),
            'nome_pai' => $this->faker()->text(500),
            'identidade' => $this->faker()->text(500),
            'orgao_emissor' => $this->faker()->text(500),
        ];
                
        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertSessionHasErrors('valor');
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_with_cpf_cnpj_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => factory('App\Socio')->raw()['cpf_cnpj'] . '5'
        ])->assertSessionHasErrors('valor');

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => factory('App\Socio')->states('pj')->raw()['cpf_cnpj'] . '5'
        ])->assertSessionHasErrors('valor');

        $this->assertEquals(Socio::count(), 0);

        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_with_input_registro_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'registro_socio',
            'valor' => '1234567890',
            'id_socio' => 1
        ])->assertSessionHasErrors('campo.*');

        $this->assertDatabaseMissing('socios', [
            'registro' => '1234567890'
        ]);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'registro',
            'valor' => '1234567890',
            'id_socio' => 1
        ])->assertSessionHasErrors('campo');

        $this->assertDatabaseMissing('socios', [
            'registro' => '1234567890'
        ]);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_by_ajax_with_cep_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cep_socio',
            'valor' => '1234567890',
            'id_socio' => 1
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', [
            'cep' => '1234567890'
        ]);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_by_ajax_under_18_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'dt_nascimento_socio',
            'valor' => Carbon::today()->subYears(17)->format('Y-m-d'),
            'id_socio' => 1
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', [
            'dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')
        ]);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_by_ajax_with_dt_expedicao_after_today_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'dt_expedicao_socio',
            'valor' => Carbon::today()->addDay()->format('Y-m-d'),
            'id_socio' => 1
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', [
            'dt_nascimento' => Carbon::today()->addDay()->format('Y-m-d')
        ]);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_by_ajax_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $datas = [
            'dt_nascimento' => null, 
            // 'dt_expedicao' => null
        ];

        foreach($datas as $key => $value) 
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key . '_socio',
                'valor' => 'texto',
                'id_socio' => 1
            ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('socios', $datas);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_by_ajax_with_uf_socio_value_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'uf_socio',
            'valor' => 'TT',
            'id_socio' => 1
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['uf' => 'TT']);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_rt_by_ajax_with_nacionalidade_socio_value_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        // PF
        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'nacionalidade_socio',
            'valor' => 'Brasileiro',
            'id_socio' => 1
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 1, 'nacionalidade' => 'Brasileiro']);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);

        // RT
        $socio = factory('App\Socio')->states('rt')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'nacionalidade_socio',
            'valor' => 'Brasileirada',
            'id_socio' => 2
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 2, 'nacionalidade' => 'Brasileirada']);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 2,
            'rt' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_rt_by_ajax_with_naturalidade_estado_socio_value_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        // PF
        $socio = factory('App\Socio')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'naturalidade_estado_socio',
            'valor' => 'DR',
            'id_socio' => 1
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 1, 'naturalidade_estado' => 'DR']);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);

        // RT
        $socio = factory('App\Socio')->states('rt')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertStatus(200);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'naturalidade_estado_socio',
            'valor' => 'ER',
            'id_socio' => 2
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 2, 'naturalidade_estado' => 'ER']);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 2,
            'rt' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_when_exists_cnpj_in_contabeis_table_by_contabilidade()
    {
        $contabil = factory('App\Contabil')->create();

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $contabil['cnpj']
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 1]);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_with_cnpj_pre_registro_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $externo->cnpj
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 1]);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_with_cpf_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->states('rt')->make()->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('socios', ['id' => 1]);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pf_by_ajax_without_relationship_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);

        foreach($socio as $key => $value){
            if($key != 'cpf_cnpj')
                $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                    'classe' => 'pessoaJuridica.socios',
                    'campo' => $key . '_socio',
                    'valor' => $value,
                    'id_socio' => 1
                ])->assertStatus(500);
        }
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_pj_by_ajax_without_relationship_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->states('pj')->make()->attributesToArray();
        unset($socio['registro']);

        foreach($socio as $key => $value){
            if($key != 'cpf_cnpj')
                $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                    'classe' => 'pessoaJuridica.socios',
                    'campo' => $key . '_socio',
                    'valor' => $value,
                    'id_socio' => 1
                ])->assertStatus(500);
        }
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function cannot_update_table_socios_by_ajax_when_remove_relationship_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertOk();
        
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj']]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => '',
            'id_socio' => 1
        ])->assertOk();

        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj']]);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);

        foreach($socio as $key => $value){
            if($key != 'cpf_cnpj')
                $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                    'classe' => 'pessoaJuridica.socios',
                    'campo' => $key . '_socio',
                    'valor' => $value,
                    'id_socio' => 1
                ])->assertStatus(500);
        }
        
        $this->assertDatabaseHas('socios', ['cpf_cnpj' => $socio['cpf_cnpj']]);
        $this->assertDatabaseMissing('socios', Arr::except($socio, ['cpf_cnpj']));
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => 0
        ]);
    }

    /** @test */
    public function get_socio_pf_by_ajax_when_exists_in_database_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->create();

        $response = $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio->cpf_cnpj
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => $socio->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertStringContainsString('<div id="socio_'. $socio->id .'_box">', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('<span class="label_complemento bold">Complemento:</span> <span class="complemento_socio editar_dado">-----</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('Órgão Emissor:</span> <span class="orgao_emissor_socio editar_dado">' . $socio->orgao_emissor . '</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);
    }

    /** @test */
    public function get_socio_pj_by_ajax_when_exists_in_database_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->states('pj')->create();

        $response = $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio->cpf_cnpj
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => $socio->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertStringContainsString('<div id="socio_'. $socio->id .'_box">', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('<span class="label_complemento bold">Complemento:</span> <span class="complemento_socio editar_dado">-----</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringNotContainsString('Órgão Emissor:</span> <span class="orgao_emissor_socio editar_dado">' . $socio->orgao_emissor . '</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);
    }

    /** @test */
    public function get_socio_rt_by_ajax_when_exists_in_database_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->states('rt')->create();

        $response = $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'checkRT_socio',
            'valor' => 'on'
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => true
            ]
        ]);

        $this->assertStringContainsString('<div id="socio_'. $socio->id .'_box">', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('<p class="text-danger mb-2"><strong><i>Dados do Responsável Técnico na aba "Contato / RT", em "Sócios" somente dados complementares.</i></strong></p>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('<span class="label_naturalidade_estado bold">Naturalidade:</span> <span class="naturalidade_estado_socio editar_dado">'. $socio->naturalidade_estado .'</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringNotContainsString('<span class="label_complemento bold">Complemento:</span> <span class="complemento_socio editar_dado">-----</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringNotContainsString('Órgão Emissor:</span> <span class="orgao_emissor_socio editar_dado">' . $socio->orgao_emissor . '</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);
    }

    /** @test */
    public function get_socio_pf_by_ajax_when_exists_in_gerenti_by_contabilidade()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5 / 2
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = [
            'registro' => '0000000001', 
            'nome' => 'RC TESTE 1', 
            'identidade' => '111111111',
            'orgao_emissor' => 'SSP-SP',
            'nome_pai' => 'PAI 1',
            'nome_mae' => 'MAE 1',
            'dt_nascimento' => '1962-09-30',
        ];

        $response = $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertStringContainsString(' - CPF: <strong>'. formataCpfCnpj(self::CPF_GERENTI) . '</strong>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('<span class="label_registro bold">Registro:</span> <span class="registro_socio editar_dado">'. formataRegistro($socio['registro']) .'</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertDatabaseHas('socios', $socio);
    }

    /** @test */
    public function get_socio_pj_by_ajax_when_exists_in_gerenti_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = [
            'registro' => '0000000002', 
            'nome' => 'RC TESTE 2',
        ];

        $response = $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CNPJ_GERENTI
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertStringContainsString(' - CNPJ: <strong>'. formataCpfCnpj(self::CNPJ_GERENTI) . '</strong>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertStringContainsString('<span class="label_registro bold">Registro:</span> <span class="registro_socio editar_dado">'. formataRegistro($socio['registro']) .'</span></span>', 
        $response->getOriginalContent()['resultado']['tab']);

        $this->assertDatabaseHas('socios', $socio);
    }

    /** @test */
    public function can_update_table_socios_pf_by_ajax_when_exists_in_gerenti_and_empty_input_registro_in_database_by_contabilidade()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5 / 2
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->create([
            'cpf_cnpj' => self::CPF_GERENTI
        ]);

        $this->assertDatabaseHas('socios', ['registro' => null, 'cpf_cnpj' => self::CPF_GERENTI]);

        $socio = [
            'registro' => '0000000001', 
            'nome' => 'RC TESTE 1', 
            'identidade' => '111111111',
            'orgao_emissor' => 'SSP-SP',
            'nome_pai' => 'PAI 1',
            'nome_mae' => 'MAE 1',
            'dt_nascimento' => '1962-09-30',
        ];

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertDatabaseHas('socios', $socio);
    }

    /** @test */
    public function can_update_table_socios_pj_by_ajax_when_exists_in_gerenti_and_empty_input_registro_in_database_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->states('pj')->create([
            'cpf_cnpj' => self::CNPJ_GERENTI
        ]);

        $this->assertDatabaseHas('socios', ['registro' => null, 'cpf_cnpj' => self::CNPJ_GERENTI]);

        $socio = [
            'registro' => '0000000002', 
            'nome' => 'RC TESTE 2', 
        ];

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CNPJ_GERENTI
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertDatabaseHas('socios', $socio);
    }

    /** @test */
    public function can_update_table_socios_pf_by_ajax_when_non_exists_in_gerenti_and_fill_input_registro_in_database_by_contabilidade()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo CANCELADO para T
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = [
            'registro' => '0000000001', 
            'nome' => 'RC TESTE 1', 
            'identidade' => '111111111',
            'orgao_emissor' => 'SSP-SP',
            'nome_pai' => 'PAI 1',
            'nome_mae' => 'MAE 1',
            'dt_nascimento' => '1962-09-30',
        ];

        $socio = factory('App\Socio')->create([
            'cpf_cnpj' => self::CPF_GERENTI,
            'registro' => $socio['registro'],
        ]);

        $this->assertDatabaseHas('socios', ['registro' => $socio['registro'], 'cpf_cnpj' => self::CPF_GERENTI]);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertDatabaseHas('socios', ['registro' => null, 'cpf_cnpj' => self::CPF_GERENTI, 'nome' => $socio['nome']]);
    }

    /** @test */
    public function can_update_table_socios_pj_by_ajax_when_non_exists_in_gerenti_and_fill_input_registro_in_database_by_contabilidade()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa juridica, no campo CANCELADO para T
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = [
            'registro' => '0000000002', 
            'nome' => 'RC TESTE 2', 
        ];

        $socio = factory('App\Socio')->create([
            'cpf_cnpj' => self::CNPJ_GERENTI,
            'registro' => $socio['registro'],
        ]);

        $this->assertDatabaseHas('socios', ['registro' => $socio['registro'], 'cpf_cnpj' => self::CNPJ_GERENTI]);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CNPJ_GERENTI
        ])->assertJsonFragment([
            'resultado' => [
                'tab' => PreRegistro::first()->pessoaJuridica->socios->first()->tabHTML(),
                'rt' => false
            ]
        ]);

        $this->assertDatabaseHas('socios', ['registro' => null, 'cpf_cnpj' => self::CNPJ_GERENTI, 'nome' => $socio['nome']]);
    }

    /** @test */
    public function can_update_table_socios_by_ajax_when_clean_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $socio = factory('App\Socio')->make()->attributesToArray();
        unset($socio['registro']);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => $socio['cpf_cnpj']
        ])->assertStatus(200);

        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
        unset($socio['cpf_cnpj']);

        foreach($socio as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key.'_socio',
                'valor' => '',
                'id_socio' => 1
            ])->assertStatus(200);
        
        $this->assertDatabaseMissing('socios', $socio);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 1,
            'rt' => false
        ]);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_socios_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $externo = $this->signInAsUserExterno('contabil');

        $preRegistro = factory('App\PreRegistroCnpj')->states('rt_socio')->create();

        $rtAjax = array();

        foreach($preRegistro->socios as $socio)
            array_push($rtAjax, $socio->arrayValidacaoInputs());

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
            {
                foreach($rtAjax as $id => $socio)
                {
                    ++$id;
                    foreach($socio as $key => $value)
                        $key == 'cpf_cnpj_socio_' . $id ? null : $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                            'classe' => 'pessoaJuridica.socios',
                            'campo' => str_replace('_' . $id, '', $key),
                            'valor' => '',
                            'id_socio' => $id
                        ])->assertStatus(401);
                }
            }
        }
    }

    /** @test */
    public function can_update_table_socios_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $externo = $this->signInAsUserExterno('contabil');

        $preRegistro = factory('App\PreRegistroCnpj')->states('rt_socio')->create();

        $rtAjax = array();

        foreach($preRegistro->socios as $socio)
            array_push($rtAjax, $socio->arrayValidacaoInputs());

        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            foreach($rtAjax as $id => $socio)
            {
                ++$id;
                foreach($socio as $key => $value)
                    $key == 'cpf_cnpj_socio_' . $id ? null : $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                        'classe' => 'pessoaJuridica.socios',
                        'campo' => str_replace('_' . $id, '', $key),
                        'valor' => '',
                        'id_socio' => $id
                    ])->assertStatus(200);
            }
        }
    }

    /** 
     * ========================================================================================================================
     * TESTES PRE-REGISTRO SÓCIOS VIA SUBMIT - LOGIN CONTABILIDADE RESPONSÁVEL PELO GERENCIAMENTO PARA O USUARIO EXTERNO COMUM
     * ========================================================================================================================
     */

     /** @test */
    public function can_submit_socios_if_exists_in_database_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $socio1 = $pr->socios->get(0)->attributesToArray();
        $socio2 = $pr->socios->get(1)->attributesToArray();

        $this->assertDatabaseHas('socios', $socio1);
        $this->assertDatabaseHas('socios', $socio2);
        $this->assertEquals(Socio::count(), 2);
    }

    /** @test */
    public function can_submit_socios_rt_if_exists_in_database_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $socio1 = $pr->socios->get(0)->attributesToArray();
        $socio2 = $pr->socios->get(1)->attributesToArray();
        $sociort = $pr->socios->get(2)->attributesToArray();

        $this->assertDatabaseHas('socios', $socio1);
        $this->assertDatabaseHas('socios', $socio2);
        $this->assertDatabaseHas('socios', $sociort);
        $this->assertDatabaseHas('responsaveis_tecnicos', ['cpf' => $sociort['cpf_cnpj']]);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', [
            'pre_registro_cnpj_id' => 1,
            'socio_id' => 3,
            'rt' => true
        ]);
        $this->assertEquals(Socio::count(), 3);
    }

    /** @test */
    public function can_submit_socio_pf_if_exists_in_gerenti_by_contabilidade()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5 / 2
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CPF_GERENTI
        ]);

        Socio::find(3)->update(Arr::except(factory('App\Socio')->raw(), ['cpf_cnpj', 'registro']));

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $this->assertDatabaseHas('socios', [
            'id' => 3,
            'cpf_cnpj' => self::CPF_GERENTI,
            'registro' => '0000000001'
        ]);
        
        $this->assertEquals(Socio::count(), 3);
    }

    /** @test */
    public function can_submit_socio_pj_if_exists_in_gerenti_by_contabilidade()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5 / 2
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.socios',
            'campo' => 'cpf_cnpj_socio',
            'valor' => self::CNPJ_GERENTI
        ]);

        Socio::find(3)->update(Arr::except(factory('App\Socio')->states('pj')->raw(), ['cpf_cnpj', 'registro']));

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $this->assertDatabaseHas('socios', [
            'id' => 3,
            'cpf_cnpj' => self::CNPJ_GERENTI,
            'registro' => '0000000002'
        ]);
        
        $this->assertEquals(Socio::count(), 3);
    }

    /** @test */
    public function can_submit_socio_pf_without_optional_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $socio = factory('App\PreRegistroCnpj')->create();
        Socio::first()->update(['registro' => null, 'nome_social' => null, 'complemento' => null]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));
        
        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('socios', Socio::first()->attributesToArray());

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_socio_pj_without_optional_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $socio = factory('App\PreRegistroCnpj')->create();
        Socio::find(2)->update(['registro' => null, 'complemento' => null]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));
        
        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('socios', Socio::find(2)->attributesToArray());

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function cannot_submit_socio_pf_without_required_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\PreRegistroCnpj')->create();
        $socio = Arr::except(Socio::first()->attributesToArray(), ['id', 'cpf_cnpj', 'registro', 'nome_social', 'complemento', 'created_at', 'updated_at', 'deleted_at']);
        Socio::first()->update(array_fill_keys(array_keys($socio), null));
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors([
            'nome_socio_1', 'dt_nascimento_socio_1', 'cep_socio_1', 'logradouro_socio_1', 'numero_socio_1', 'bairro_socio_1', 'cidade_socio_1', 'uf_socio_1', 
            'nome_mae_socio_1', 'identidade_socio_1', 'orgao_emissor_socio_1', 'nome_pai_socio_1', 'nacionalidade_socio_1'
        ]);

        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('socios', Socio::first()->attributesToArray());

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_CRIADO);
    }

    /** @test */
    public function cannot_submit_socio_pj_without_required_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\PreRegistroCnpj')->create();
        $socio = Arr::only(Socio::find(2)->attributesToArray(), ['nome', 'cep', 'logradouro', 'numero', 'bairro', 'cidade', 'uf']);
        Socio::find(2)->update(array_fill_keys(array_keys($socio), null));
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors([
            'nome_socio_2', 'cep_socio_2', 'logradouro_socio_2', 'numero_socio_2', 'bairro_socio_2', 'cidade_socio_2', 'uf_socio_2'
        ]);

        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('socios', Socio::find(2)->attributesToArray());

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_CRIADO);
    }

    /** @test */
    public function cannot_submit_socio_rt_without_required_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $socio = Arr::only(Socio::find(3)->attributesToArray(), ['nacionalidade', 'naturalidade_estado']);
        Socio::find(3)->update(array_fill_keys(array_keys($socio), null));
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors([
            'nacionalidade_socio_3'
        ]);

        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('socios', Socio::find(3)->attributesToArray());

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_CRIADO);
    }

    /** @test */
    public function cannot_submit_socio_without_cpf_cnpj_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios()->detach();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_cnpj_socio_');
    }

    /** @test */
    public function cannot_submit_socio_with_cpf_cnpj_with_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cpf_cnpj' => '12345678901']);
        $pr->socios->get(1)->update(['cpf_cnpj' => '12345678901234']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_cnpj_socio_1', 'cpf_cnpj_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_with_cpf_cnpj_exists_in_contabeis_by_contabilidade()
    {
        $contabil = factory('App\Contabil')->create();
        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(1)->update(['cpf_cnpj' => $contabil->cnpj]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_cnpj_socio_2');

        $cnpj = $contabil->cnpj;
        $contabil->delete();
        $pr->socios->get(1)->update(['cpf_cnpj' => $cnpj]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_cnpj_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pj_with_cpf_cnpj_equals_user_externo_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(1)->update(['cpf_cnpj' => PreRegistro::first()->userExterno->cpf_cnpj]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_cnpj_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_with_cpf_cnpj_equals_cpf_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cpf_cnpj' => $pr->responsavelTecnico->cpf]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_cnpj_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_nome_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome' => null]);
        $pr->socios->get(1)->update(['nome' => null]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_socio_1', 'nome_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_nome_socio_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome' => 'Nome']);
        $pr->socios->get(1)->update(['nome' => 'Nome']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_socio_1', 'nome_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_nome_socio_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome' => $this->faker()->text(500)]);
        $pr->socios->get(1)->update(['nome' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_socio_1', 'nome_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_nome_socio_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome' => 'N0me com númer0']);
        $pr->socios->get(1)->update(['nome' => 'N0me com númer0']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_socio_1', 'nome_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_nome_social_socio_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_social' => 'Nome']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_nome_social_socio_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_social' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_nome_social_socio_rt_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_social' => 'Nom3 com numeros']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_without_dt_nascimento_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['dt_nascimento' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_dt_nascimento_socio_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['dt_nascimento' => '2000/01/01']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_dt_nascimento_socio_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['dt_nascimento' => 'texto']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_dt_nascimento_socio_under_18_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
       
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_without_identidade_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['identidade' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_identidade_socio_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['identidade' => '12A']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_identidade_socio_more_than_30_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
       
        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['identidade' => '123456789012345678901234567890123']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_without_orgao_emissor_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['orgao_emissor' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_orgao_emissor_socio_less_than_3_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['orgao_emissor' => 'sd']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_orgao_emissor_socio_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['orgao_emissor' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_cep_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cep' => '']);
        $pr->socios->get(1)->update(['cep' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_socio_1', 'cep_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_cep_socio_more_than_9_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cep' => '012345698']);
        $pr->socios->get(1)->update(['cep' => '012345698']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_socio_1', 'cep_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_cep_socio_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cep' => '012-12365']);
        $pr->socios->get(1)->update(['cep' => '012-12365']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_socio_1', 'cep_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_bairro_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['bairro' => '']);
        $pr->socios->get(1)->update(['bairro' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_socio_1', 'bairro_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_bairro_socio_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['bairro' => 'Bai']);
        $pr->socios->get(1)->update(['bairro' => 'Bai']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_socio_1', 'bairro_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_bairro_socio_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['bairro' => $this->faker()->text(500)]);
        $pr->socios->get(1)->update(['bairro' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_socio_1', 'bairro_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_logradouro_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['logradouro' => '']);
        $pr->socios->get(1)->update(['logradouro' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_socio_1', 'logradouro_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_logradouro_socio_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['logradouro' => 'Log']);
        $pr->socios->get(1)->update(['logradouro' => 'Log']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_socio_1', 'logradouro_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_logradouro_socio_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['logradouro' => $this->faker()->text(500)]);
        $pr->socios->get(1)->update(['logradouro' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_socio_1', 'logradouro_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_numero_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['numero' => '']);
        $pr->socios->get(1)->update(['numero' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('numero_socio_1', 'numero_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_numero_socio_more_than_10_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['numero' => '123456789lp']);
        $pr->socios->get(1)->update(['numero' => '123456789lp']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('numero_socio_1', 'numero_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_complemento_socio_more_than_50_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['complemento' => $this->faker()->text(200)]);
        $pr->socios->get(1)->update(['complemento' => $this->faker()->text(200)]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('complemento_socio_1', 'complemento_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_cidade_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cidade' => '']);
        $pr->socios->get(1)->update(['cidade' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cidade_socio_1', 'cidade_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_cidade_socio_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['cidade' => $this->faker()->text(500)]);
        $pr->socios->get(1)->update(['cidade' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cidade_socio_1', 'cidade_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_without_uf_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['uf' => '']);
        $pr->socios->get(1)->update(['uf' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('uf_socio_1', 'uf_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_pj_with_uf_socio_with_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['uf' => 'UF']);
        $pr->socios->get(1)->update(['uf' => 'UF']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('uf_socio_1', 'uf_socio_2');
    }

    /** @test */
    public function cannot_submit_socio_pf_without_nome_mae_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_mae' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_nome_mae_socio_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_mae' => 'Mãen']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_nome_mae_socio_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_mae' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_with_nome_mae_socio_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_mae' => 'M4mãe']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_without_nome_pai_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->create();
        $pr->socios->get(0)->update(['nome_pai' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_rt_without_nome_pai_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->responsavelTecnico->update(['nome_pai' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_with_nome_pai_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->responsavelTecnico->update(['nome_pai' => 'paiz']);
        $pr->socios->get(0)->update(['nome_pai' => 'paiz']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt', 'nome_pai_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_with_nome_pai_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->responsavelTecnico->update(['nome_pai' => $this->faker()->text(500)]);
        $pr->socios->get(0)->update(['nome_pai' => $this->faker()->text(500)]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt', 'nome_pai_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_with_nome_pai_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->responsavelTecnico->update(['nome_pai' => 'pa1 teste']);
        $pr->socios->get(0)->update(['nome_pai' => 'pa1 teste']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt', 'nome_pai_socio_1');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_without_nacionalidade_socio_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->socios->get(0)->update(['nacionalidade' => '']);
        $pr->socios->get(2)->update(['nacionalidade' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nacionalidade_socio_1', 'nacionalidade_socio_3');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_with_nacionalidade_socio_with_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->socios->get(0)->update(['nacionalidade' => 'BRASILEIRO']);
        $pr->socios->get(2)->update(['nacionalidade' => 'BRASILEIRO']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nacionalidade_socio_1', 'nacionalidade_socio_3');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_without_naturalidade_estado_socio_if_input_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->socios->get(0)->update(['naturalidade_estado' => '']);
        $pr->socios->get(2)->update(['naturalidade_estado' => '']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_estado_socio_1', 'naturalidade_estado_socio_3');
    }

    /** @test */
    public function cannot_submit_socio_pf_rt_with_naturalidade_estado_socio_with_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $pr->socios->get(0)->update(['naturalidade_estado' => 'BR']);
        $pr->socios->get(2)->update(['naturalidade_estado' => 'TR']);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_estado_socio_1', 'naturalidade_estado_socio_3');
    }

    /** @test */
    public function filled_campos_editados_socios_when_form_is_submitted_when_status_aguardando_correcao_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $PreRegistroCnpj = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $PreRegistroCnpj->socios()->attach(factory('App\Socio')->create()->id);

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

        // Remove RT e novo sócio
        $PreRegistroCnpj->socios()->detach(3);
        $PreRegistroCnpj->socios()->detach(4);
        
        // PF
        $campos = Arr::except(factory('App\Socio')->raw([
            'identidade' => '2211111135', 'orgao_emissor' => 'SSP - PB', 'cep' => '03021-030', 'logradouro' => 'RUA TESTE DO SÓCIO PF NOVO', 'numero' => '155A',
            'complemento' => 'FINAL', 'bairro' => 'TESTE BAIRRO SÓCIO PF NOVO', 'cidade' => 'OSASCO', 'uf' => 'MG', 'nacionalidade' => 'CHILENA', 
            'naturalidade_estado' => null, 'dt_nascimento' => now()->subYears(33)->format('Y-m-d'),
        ]), ['cpf_cnpj', 'registro']);
        foreach($campos as $key => $value){
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key . '_socio',
                'valor' => $value,
                'id_socio' => 1
            ])->assertStatus(200);
            $campos[$key] = $key . '_socio_1';
        }

        // PJ
        $temp = Arr::only(factory('App\Socio')->states('pj')->raw([
            'cep' => '03021-030', 'logradouro' => 'RUA TESTE DO SÓCIO PJ NOVO', 'numero' => '155A', 'complemento' => 'FINAL', 'bairro' => 'TESTE BAIRRO SÓCIO PJ NOVO', 
            'cidade' => 'OSASCO', 'uf' => 'MG',
        ]), ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'nome']);
        foreach($temp as $key => $value){
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key . '_socio',
                'valor' => $value,
                'id_socio' => 2
            ])->assertStatus(200);
            $temp[$key] = $key . '_socio_2';
        }

        array_push($campos, 'checkRT_socio');
        $campos = array_merge(array_flip($campos), array_flip($temp), ["removidos_socio" => "3, 4"]);

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
    public function filled_campos_editados_socios_rt_when_form_is_submitted_when_status_aguardando_correcao_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $PreRegistroCnpj = factory('App\PreRegistroCnpj')->create();

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

        $this->signInAsUserExterno('user_externo', $externo);

        // Remove Sócio PF e PJ
        $PreRegistroCnpj->socios()->detach(1);
        $PreRegistroCnpj->socios()->detach(2);
        
        // RT
        $campos = ['checkRT' => 'on', 'nacionalidade' => 'BRASILEIRA', 'naturalidade_estado' => 'PB'];
        foreach($campos as $key => $value){
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.socios',
                'campo' => $key . '_socio',
                'valor' => $value,
                $key == 'checkRT' ? null : 'id_socio' => 3
            ])->assertStatus(200);
            $campos[$key] = $key == 'checkRT' ? $key . '_socio' : $key . '_socio_3';
        }

        $campos = array_merge(array_flip($campos), ["removidos_socio" => "1, 2"]);

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
    public function view_justifications_socios_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');
            
        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->socios->get(0)->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at'])->attributesToArray());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo . '_socio',
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('contabil', $externo);

        foreach($keys as $campo)
            $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
            ->assertSeeInOrder([
                '<a class="nav-link" data-toggle="pill" href="#parte_socios">',
                'Sócios&nbsp',
                '<span class="badge badge-danger">',
                '</a>',
            ])
            ->assertSee('value="'. route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo . '_socio']) .'"');
    }

    /** @test */
    public function view_justifications_text_socios_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');
            
        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->socios->get(0)->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at'])->attributesToArray());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo . '_socio',
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        foreach($keys as $campo)
            $this->get(route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo . '_socio']))
            ->assertJsonFragment(['justificativa' => PreRegistro::first()->getJustificativaPorCampo($campo . '_socio')]);
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO SÓCIOS VIA AJAX - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function can_update_justificativa()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(), ['registro_socio' => null]));

        $justificativas = array();
        foreach($dados as $campo)
        {
            $campo = str_replace('_1', '', $campo);
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

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(), ['registro_socio' => null]));

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            if(in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                foreach($dados as $campo)
                    $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                        'acao' => 'justificar',
                        'campo' => str_replace('_1', '', $campo),
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

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(), ['registro_socio' => null]));

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'justificar',
                'campo' => str_replace('_1', '', $campo),
                'valor' => ''
            ])->assertStatus(200);    

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'justificar',
                'campo' => str_replace('_1', '', $campo),
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

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(), ['registro_socio' => null]));

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => str_replace('_1', '', $campo),
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
        {
            $campo = str_replace('_1', '', $campo);
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

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(), ['registro_socio' => null]));

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => str_replace('_1', '', $campo),
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => str_replace('_1', '', $campo) . '_erro',
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

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(), ['registro_socio' => null]));

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => str_replace('_1', '', $campo),
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar_',
                'campo' => str_replace('_1', '', $campo),
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

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(), ['registro_socio' => null]));

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => str_replace('_1', '', $campo),
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
                        'campo' => str_replace('_1', '', $campo),
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

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(), ['registro_socio' => null]));

        foreach($dados as $campo)
        {
            $campo = str_replace('_1', '', $campo);
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

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(), ['registro_socio' => null]));

        foreach($dados as $key => $campo){
            $dados[$key] = str_replace('_1', '', $campo);
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => str_replace('_1', '', $campo),
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);
        }

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

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO SÓCIOS - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function view_pre_registro_socio_pf()
    {
        $admin = $this->signInAsAdmin();

        $socio = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ])->socios->get(0);
        
        $this->get(route('preregistro.view', 1))
        ->assertSeeInOrder([
            '<p id="checkRT_socio">', '<i class="fas fa-times text-danger"></i>', ' - Responsável Técnico pertence ao quadro societário</span>',
            '<p id="cpf_cnpj_socio">', ' - CPF / CNPJ: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', formataCpfCnpj($socio->cpf_cnpj),
            '<p id="registro_socio">', ' - Registro: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '------',
            '<p id="nome_socio">', ' - Nome: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->nome,
            '<p id="nome_social_socio">', ' - Nome Social: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->nome_social,
            '<p id="dt_nascimento_socio">', ' - Data de Nascimento: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', onlyDate($socio->dt_nascimento),
            '<p id="identidade_socio">', ' - Identidade: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->identidade,
            '<p id="orgao_emissor_socio">', ' - Órgão Emissor: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->orgao_emissor,
            '<p id="cep_socio">', ' - CEP: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->cep,
            '<p id="bairro_socio">', ' - Bairro: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->bairro,
            '<p id="logradouro_socio">', ' - Logradouro: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->logradouro,
            '<p id="numero_socio">', ' - Número: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->numero,
            '<p id="complemento_socio">', ' - Complemento: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '-----',
            '<p id="cidade_socio">', ' - Município: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->cidade,
            '<p id="uf_socio">', ' - Estado: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->uf,
            '<p id="nome_mae_socio">', ' - Nome Mãe: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->nome_mae,
            '<p id="nome_pai_socio">', ' - Nome Pai: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->nome_pai,
            '<p id="nacionalidade_socio">', ' - Nacionalidade: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->nacionalidade,
            '<p id="naturalidade_estado_socio">', ' - Naturalidade - Estado: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', $socio->naturalidade_estado,
        ]);
    }

    /** @test */
    public function view_pre_registro_socio_pj()
    {
        $admin = $this->signInAsAdmin();

        $socio = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ])->socios->get(1);
        
        $this->get(route('preregistro.view', 1))
        ->assertSeeInOrder([
            '<p id="checkRT_socio">', '<i class="fas fa-times text-danger"></i>', ' - Responsável Técnico pertence ao quadro societário</span>',
            '<p id="cpf_cnpj_socio">', ' - CPF / CNPJ: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', formataCpfCnpj($socio->cpf_cnpj),
            '<p id="registro_socio">', ' - Registro: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '------',
            '<p id="nome_socio">', ' - Nome: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', $socio->nome,
            '<p id="nome_social_socio">', ' - Nome Social: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Não precisa</i></span>',
            '<p id="dt_nascimento_socio">', ' - Data de Nascimento: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Não precisa</i></span>',
            '<p id="identidade_socio">', ' - Identidade: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Não precisa</i></span>',
            '<p id="orgao_emissor_socio">', ' - Órgão Emissor: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Não precisa</i></span>',
            '<p id="cep_socio">', ' - CEP: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', $socio->cep,
            '<p id="bairro_socio">', ' - Bairro: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', $socio->bairro,
            '<p id="logradouro_socio">', ' - Logradouro: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', $socio->logradouro,
            '<p id="numero_socio">', ' - Número: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', $socio->numero,
            '<p id="complemento_socio">', ' - Complemento: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '-----',
            '<p id="cidade_socio">', ' - Município: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', $socio->cidade,
            '<p id="uf_socio">', ' - Estado: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', $socio->uf,
            '<p id="nome_mae_socio">', ' - Nome Mãe: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Não precisa</i></span>',
            '<p id="nome_pai_socio">', ' - Nome Pai: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Não precisa</i></span>',
            '<p id="nacionalidade_socio">', ' - Nacionalidade: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Não precisa</i></span>',
            '<p id="naturalidade_estado_socio">', ' - Naturalidade - Estado: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Não precisa</i></span>',
        ]);
    }

    /** @test */
    public function view_pre_registro_socio_rt()
    {
        $admin = $this->signInAsAdmin();

        $socio = factory('App\PreRegistroCnpj')->states('rt_socio')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ])->socios->get(2);
        
        $this->get(route('preregistro.view', 1))
        ->assertSeeInOrder([
            '<p id="checkRT_socio">', '<i class="fas fa-check-circle text-success"></i>', ' - Responsável Técnico pertence ao quadro societário</span>',
            '<p id="cpf_cnpj_socio">', ' - CPF / CNPJ: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', formataCpfCnpj($socio->cpf_cnpj),
            '<p id="registro_socio">', ' - Registro: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="nome_socio">', ' - Nome: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="nome_social_socio">', ' - Nome Social: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="dt_nascimento_socio">', ' - Data de Nascimento: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="identidade_socio">', ' - Identidade: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="orgao_emissor_socio">', ' - Órgão Emissor: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="cep_socio">', ' - CEP: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="bairro_socio">', ' - Bairro: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="logradouro_socio">', ' - Logradouro: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="numero_socio">', ' - Número: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="complemento_socio">', ' - Complemento: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="cidade_socio">', ' - Município: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="uf_socio">', ' - Estado: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="nome_mae_socio">', ' - Nome Mãe: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="nome_pai_socio">', ' - Nome Pai: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>',
            '<p id="nacionalidade_socio">', ' - Nacionalidade: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', $socio->nacionalidade,
            '<p id="naturalidade_estado_socio">', ' - Naturalidade - Estado: </span>', '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-', $socio->naturalidade_estado,
        ]);
    }

    /** @test */
    public function view_text_justificado_socio()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $keys = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(), ['registro_socio' => null]));
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => str_replace('_1', '', $campo),
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $justificativas = $preRegistroCnpj->preRegistro->fresh()->getJustificativaArray();

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText($justificativas['cpf_cnpj_socio'])
        ->assertSeeText($justificativas['registro_socio'])
        ->assertSeeText($justificativas['nome_socio'])
        ->assertSeeText($justificativas['nome_social_socio'])
        ->assertSeeText($justificativas['dt_nascimento_socio'])
        ->assertSeeText($justificativas['identidade_socio'])
        ->assertSeeText($justificativas['orgao_emissor_socio'])
        ->assertSeeText($justificativas['cep_socio'])
        ->assertSeeText($justificativas['logradouro_socio'])
        ->assertSeeText($justificativas['numero_socio'])
        ->assertSeeText($justificativas['complemento_socio'])
        ->assertSeeText($justificativas['bairro_socio'])
        ->assertSeeText($justificativas['cidade_socio'])
        ->assertSeeText($justificativas['uf_socio'])
        ->assertSeeText($justificativas['nacionalidade_socio'])
        ->assertSeeText($justificativas['naturalidade_estado_socio'])
        ->assertSeeText($justificativas['nome_mae_socio'])
        ->assertSeeText($justificativas['nome_pai_socio']);
    }

    /** @test */
    public function view_justifications_text_socio_by_url()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['checkPreRegistro' => 'on']), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(), ['registro_socio' => null]));
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => str_replace('_1', '', $campo),
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);
        $data_hora = now()->format('Y-m-d H:i:s');

        foreach($keys as $campo)
            $this->get(route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => str_replace('_1', '', $campo), 'data_hora' => urlencode($data_hora)]))
            ->assertJsonFragment([
                'justificativa' => PreRegistro::first()->getJustificativaPorCampoData(str_replace('_1', '', $campo), $data_hora),
                'data_hora' => formataData($data_hora)
            ]);
    }

    /** @test */
    public function view_historico_justificativas_socio()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['checkPreRegistro' => 'on']), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->socios->get(0)->arrayValidacaoInputs(), ['registro_socio' => null]));
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => str_replace('_1', '', $campo),
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);
        $data_hora = now()->format('Y-m-d H:i:s');

        foreach($keys as $campo)
            $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
            ->assertSee('value="'.route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => str_replace('_1', '', $campo), 'data_hora' => urlencode($data_hora)]).'"');
    }

    /** @test */
    public function view_label_campo_alterado_socio_pf_pj()
    {
        $this->filled_campos_editados_socios_when_form_is_submitted_when_status_aguardando_correcao();
        
        $admin = $this->signIn(PreRegistro::first()->user);

        $camposEditados = json_decode(PreRegistro::first()->campos_editados, true);

        $this->get(route('preregistro.view', 1))
        ->assertSeeInOrder([
            '<a class="card-link" data-toggle="collapse" href="#parte_socios">',
            '<div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">',
            '5. Sócios',
            '<span class="badge badge-danger ml-2">Campos alterados</span>',
        ]);
            
        $this->get(route('preregistro.view', 1))->assertSeeInOrder([
            '<p id="checkRT_socio">', '<i class="fas fa-times text-danger"></i>', '<span class="badge badge-danger ml-2">Campo alterado</span>', '</p>',
            '<p id="cpf_cnpj_socio">', '</p>', '<p id="registro_socio">', '</p>',
            '<p id="nome_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', '</p>',
            '<p id="nome_social_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', 'Não precisa', '</p>',
            '<p id="dt_nascimento_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', 'Não precisa', '</p>',
            '<p id="identidade_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', 'Não precisa', '</p>',
            '<p id="orgao_emissor_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', 'Não precisa', '</p>',
            '<p id="cep_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', '</p>',
            '<p id="bairro_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', '</p>',
            '<p id="logradouro_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', '</p>',
            '<p id="numero_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', '</p>',
            '<p id="complemento_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', '</p>',
            '<p id="cidade_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', '</p>',
            '<p id="uf_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', '</p>',
            '<p id="nome_mae_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', 'Não precisa', '</p>',
            '<p id="nome_pai_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', 'Não precisa', '</p>',
            '<p id="nacionalidade_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', 'Não precisa', '</p>',
            '<p id="naturalidade_estado_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 1</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 2</span>&nbsp;&nbsp;-</span>', 'Não precisa', '</p>',
        ]);
    }

    /** @test */
    public function view_label_campo_alterado_socio_rt()
    {
        $this->filled_campos_editados_socios_rt_when_form_is_submitted_when_status_aguardando_correcao();
        
        $admin = $this->signIn(PreRegistro::first()->user);

        $camposEditados = json_decode(PreRegistro::first()->campos_editados, true);

        $this->get(route('preregistro.view', 1))
        ->assertSeeInOrder([
            '<a class="card-link" data-toggle="collapse" href="#parte_socios">',
            '<div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">',
            '5. Sócios',
            '<span class="badge badge-danger ml-2">Campos alterados</span>',
        ]);
            
        $this->get(route('preregistro.view', 1))->assertSeeInOrder([
            '<p id="checkRT_socio">', '<i class="fas fa-check-circle text-success"></i>', '<span class="badge badge-danger ml-2">Campo alterado</span>', '</p>',
            '<p id="cpf_cnpj_socio">', '</p>', '<p id="registro_socio">', '</p>',
            '<p id="nome_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="nome_social_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="dt_nascimento_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="identidade_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="orgao_emissor_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="cep_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="bairro_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="logradouro_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="numero_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="complemento_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="cidade_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="uf_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="nome_mae_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="nome_pai_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '&nbsp;&nbsp;<span class="text-danger"><i>Aba &quot;Contato / RT&quot;</i></span>', '</p>',
            '<p id="nacionalidade_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', '</p>',
            '<p id="naturalidade_estado_socio">', 
                '<span class="font-weight-bolder">Sócio <span class="text-primary">ID 3</span>&nbsp;&nbsp;-</span>', '<span class="badge badge-danger ml-2">Campo alterado</span>', '</p>',
        ]);
    }

    /** @test */
    public function view_label_justificado_socios()
    {
        $this->view_text_justificado_socio();

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
