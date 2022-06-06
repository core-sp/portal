<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\PreRegistro;
use App\Contabil;

class ContabilTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO CONTABIL VIA AJAX - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function can_update_table_contabeis_by_ajax()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('contabeis', $contabil);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('contabeis', $contabil);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('contabeis', $contabil);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil_erro',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('contabeis', $contabil);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('contabeis', $contabil);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_with_input_type_text_more_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => factory('App\Contabil')->raw()['cnpj']
        ]);

        $contabil = [
            'nome' => $faker->sentence(400),
            'email' => $faker->sentence(400),
            'nome_contato' => $faker->sentence(400),
            'telefone' => $faker->sentence(400),
        ];

        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('contabeis', $contabil);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_with_cnpj_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => factory('App\Contabil')->raw()['cnpj'] . '4'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('contabeis', [
            'cnpj' => factory('App\Contabil')->raw()['cnpj'] . '4'
        ]);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_without_relationship()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
        {
            if($key != 'cnpj')
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'contabil',
                    'campo' => $key.'_contabil',
                    'valor' => $value
                ])->assertOk();
        }
        
        $this->assertDatabaseMissing('contabeis', $contabil);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_when_remove_relationship()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertOk();
        
        $this->assertDatabaseHas('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil->id
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => ''
        ])->assertOk();

        $this->assertDatabaseHas('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => null
        ]);
    }

    /** @test */
    public function get_contabil_by_ajax_when_exists()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $contabil = factory('App\Contabil')->create();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => $contabil->cnpj
        ])->assertJsonFragment($contabil->toArray());
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO CONTABIL VIA SUBMIT - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function can_submit_pre_registro_with_cnpj_contabil_exists()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        
        $contabil = factory('App\Contabil')->create();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);

        foreach($contabil->toArray() as $key => $value)
            $dados[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $dados);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('contabeis', $contabil->toArray());
        $this->assertEquals(Contabil::count(), 1);
    }

    /** @test */
    public function cannot_submit_pre_registro_with_cnpj_contabil_wrong()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $dados = [
            'cnpj_contabil' => '01234567891023',
            'nome_contabil' => 'Teste Contabil',
            'email_contabil' => 'teste@contabil.com',
            'nome_contato_contabil' => 'Dono da Contabil',
            'telefone_contabil' => '(11) 99878-8969'
        ];

        $dados = array_merge($preRegistro, $preRegistroCpf, $dados);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cnpj_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_nome_contabil()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $dados = [
            'cnpj_contabil' => '78087976000130',
            'nome_contabil' => '',
            'email_contabil' => 'teste@contabil.com',
            'nome_contato_contabil' => 'Dono da Contabil',
            'telefone_contabil' => '(11) 99878-8969'
        ];

        $dados = array_merge($preRegistro, $preRegistroCpf, $dados);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contabil_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $dados = [
            'cnpj_contabil' => '78087976000130',
            'nome_contabil' => $faker->sentence(400),
            'email_contabil' => 'teste@contabil.com',
            'nome_contato_contabil' => 'Dono da Contabil',
            'telefone_contabil' => '(11) 99878-8969'
        ];

        $dados = array_merge($preRegistro, $preRegistroCpf, $dados);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_email_contabil()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $dados = [
            'cnpj_contabil' => '78087976000130',
            'nome_contabil' => 'Teste Contabil',
            'email_contabil' => '',
            'nome_contato_contabil' => 'Dono da Contabil',
            'telefone_contabil' => '(11) 99878-8969'
        ];

        $dados = array_merge($preRegistro, $preRegistroCpf, $dados);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('email_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_email_contabil_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $dados = [
            'cnpj_contabil' => '78087976000130',
            'nome_contabil' => 'Teste Contabil',
            'email_contabil' => $faker->sentence(400),
            'nome_contato_contabil' => 'Dono da Contabil',
            'telefone_contabil' => '(11) 99878-8969'
        ];

        $dados = array_merge($preRegistro, $preRegistroCpf, $dados);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('email_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_email_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $dados = [
            'cnpj_contabil' => '78087976000130',
            'nome_contabil' => 'Teste Contabil',
            'email_contabil' => 'qualquercoisa.com',
            'nome_contato_contabil' => 'Dono da Contabil',
            'telefone_contabil' => '(11) 99878-8969'
        ];

        $dados = array_merge($preRegistro, $preRegistroCpf, $dados);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('email_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_nome_contato_contabil()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $dados = [
            'cnpj_contabil' => '78087976000130',
            'nome_contabil' => 'Dono da Contabil',
            'email_contabil' => 'teste@contabil.com',
            'nome_contato_contabil' => '',
            'telefone_contabil' => '(11) 99878-8969'
        ];

        $dados = array_merge($preRegistro, $preRegistroCpf, $dados);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contato_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contato_contabil_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $dados = [
            'cnpj_contabil' => '78087976000130',
            'nome_contabil' => 'Dono da Contabil',
            'email_contabil' => 'teste@contabil.com',
            'nome_contato_contabil' => $faker->sentence(400),
            'telefone_contabil' => '(11) 99878-8969'
        ];

        $dados = array_merge($preRegistro, $preRegistroCpf, $dados);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contato_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contato_contabil_with_numbers()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $dados = [
            'cnpj_contabil' => '78087976000130',
            'nome_contabil' => 'Dono da Contabil',
            'email_contabil' => 'teste@contabil.com',
            'nome_contato_contabil' => 'Teste do n0me com números',
            'telefone_contabil' => '(11) 99878-8969'
        ];

        $dados = array_merge($preRegistro, $preRegistroCpf, $dados);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contato_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_telefone_contabil()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $dados = [
            'cnpj_contabil' => '78087976000130',
            'nome_contabil' => 'Dono da Contabil',
            'email_contabil' => 'teste@contabil.com',
            'nome_contato_contabil' => 'Teste do n0me com números',
            'telefone_contabil' => ''
        ];

        $dados = array_merge($preRegistro, $preRegistroCpf, $dados);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_telefone_contabil_more_than_20_chars_and_value_wrong()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $dados = [
            'cnpj_contabil' => '78087976000130',
            'nome_contabil' => 'Dono da Contabil',
            'email_contabil' => 'teste@contabil.com',
            'nome_contato_contabil' => 'Teste do n0me com números',
            'telefone_contabil' => '(112) 988886-2233'
        ];

        $dados = array_merge($preRegistro, $preRegistroCpf, $dados);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone_contabil');
    }
}
