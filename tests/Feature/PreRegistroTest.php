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
        $this->post(route('externo.inserir.preregistro.ajax'))->assertRedirect(route('externo.login'));
        $this->get(route('externo.preregistro.anexo.download', 1))->assertRedirect(route('externo.login'));
        $this->delete(route('externo.preregistro.anexo.excluir', 1))->assertRedirect(route('externo.login'));
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
            'pre_registro_id' => $preRegistro['id'],
            'path' => '/fake/qwertyuiop.jpg'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf, $anexo);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'nome_contabil',
            'valor' => 'Teste Teste'
        ])->assertStatus(401);

        $this->put(route('externo.inserir.preregistro'), $dados)->assertStatus(302);
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

        $this->get(route('externo.inserir.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '86294373085'
        ]));

        $this->get(route('externo.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $this->get(route('externo.inserir.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistro = factory('App\PreRegistro')->raw([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);

        $preRegistro['tipo_telefone_1'] = tipos_contatos()[1];
        $preRegistro['telefone_1'] = '(11) 99999-8888';

        $pular = ['user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa'];
        
        foreach($preRegistro as $key => $value)
        {
            if(!in_array($key, $pular))
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'preRegistro',
                    'campo' => $key,
                    'valor' => $value
                ])->assertStatus(200);
        }
        
        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';'. $preRegistro['tipo_telefone_1'];
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';' . $preRegistro['telefone_1'];
        unset($preRegistro['tipo_telefone_1']);
        unset($preRegistro['telefone_1']);

        $this->assertDatabaseHas('pre_registros', $preRegistro);
    }

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
    public function can_create_anexos_by_ajax()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $anexos = [
            UploadedFile::fake()->image('random.jpg'),
            UploadedFile::fake()->image('random1.png'),
            UploadedFile::fake()->create('random2.pdf'),
        ];
        
        foreach($anexos as $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'random.jpg',
            'nome_original' => 'random1.png',
            'nome_original' => 'random2.pdf',
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistro = factory('App\PreRegistro')->raw([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);

        $preRegistro['tipo_telefone_1'] = tipos_contatos()[1];
        $preRegistro['telefone_1'] = '(11) 99999-8888';

        $pular = ['user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa'];
        
        foreach($preRegistro as $key => $value)
        {
            if(!in_array($key, $pular))
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'preRegistro',
                    'campo' => $key.'_erro',
                    'valor' => $value
                ])->assertSessionHasErrors('campo');
        }

        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';'. $preRegistro['tipo_telefone_1'];
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';' . $preRegistro['telefone_1'];
        unset($preRegistro['tipo_telefone_1']);
        unset($preRegistro['telefone_1']);

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
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
    public function cannot_create_anexos_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $anexos = [
            UploadedFile::fake()->image('random.jpg'),
            UploadedFile::fake()->image('random1.png'),
            UploadedFile::fake()->image('random2.jpeg'),
        ];
        
        foreach($anexos as $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'nome_original' => 'random1.png',
            'nome_original' => 'random2.jpeg',
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistro = factory('App\PreRegistro')->raw([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);

        $preRegistro['tipo_telefone_1'] = tipos_contatos()[1];
        $preRegistro['telefone_1'] = '(11) 99999-8888';

        $pular = ['user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa'];
        
        foreach($preRegistro as $key => $value)
        {
            if(!in_array($key, $pular))
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => '',
                    'campo' => $key,
                    'valor' => $value
                ])->assertSessionHasErrors('classe');
        }
        
        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';'. $preRegistro['tipo_telefone_1'];
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';' . $preRegistro['telefone_1'];
        unset($preRegistro['tipo_telefone_1']);
        unset($preRegistro['telefone_1']);

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
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
    public function cannot_create_anexos_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $anexos = [
            UploadedFile::fake()->image('random.jpg'),
            UploadedFile::fake()->image('random1.png'),
            UploadedFile::fake()->image('random2.jpeg'),
        ];
        
        foreach($anexos as $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => 'path',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'nome_original' => 'random1.png',
            'nome_original' => 'random2.jpeg',
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistro = factory('App\PreRegistro')->raw([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);

        $preRegistro['tipo_telefone_1'] = tipos_contatos()[1];
        $preRegistro['telefone_1'] = '(11) 99999-8888';

        $pular = ['user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa'];
        
        foreach($preRegistro as $key => $value)
        {
            if(!in_array($key, $pular))
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'preRegistro_erro',
                    'campo' => $key,
                    'valor' => $value
                ])->assertSessionHasErrors('classe');
        }

        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';'. $preRegistro['tipo_telefone_1'];
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';' . $preRegistro['telefone_1'];
        unset($preRegistro['tipo_telefone_1']);
        unset($preRegistro['telefone_1']);

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
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
    public function cannot_create_anexos_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $anexos = [
            UploadedFile::fake()->image('random.jpg'),
            UploadedFile::fake()->image('random1.png'),
            UploadedFile::fake()->image('random2.jpeg'),
        ];
        
        foreach($anexos as $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos_erro',
                'campo' => 'path',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'nome_original' => 'random1.png',
            'nome_original' => 'random2.jpeg',
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistro = factory('App\PreRegistro')->raw([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);

        $preRegistro['tipo_telefone_1'] = tipos_contatos()[1];
        $preRegistro['telefone_1'] = '(11) 99999-8888';

        $pular = ['user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa'];
        
        foreach($preRegistro as $key => $value)
        {
            if(!in_array($key, $pular))
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'preRegistro',
                    'campo' => '',
                    'valor' => $value
                ])->assertSessionHasErrors('campo');
        }

        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';'. $preRegistro['tipo_telefone_1'];
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';' . $preRegistro['telefone_1'];
        unset($preRegistro['tipo_telefone_1']);
        unset($preRegistro['telefone_1']);

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
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
    public function cannot_create_anexos_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $anexos = [
            UploadedFile::fake()->image('random.jpg'),
            UploadedFile::fake()->image('random1.png'),
            UploadedFile::fake()->image('random2.jpeg'),
        ];
        
        foreach($anexos as $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'nome_original' => 'random1.png',
            'nome_original' => 'random2.jpeg',
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_input_type_text_more_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistro = [
            'ramo_atividade' => $faker->sentence(400),
            'segmento' => $faker->sentence(400),
            'registro_secundario' => $faker->sentence(400),
            'logradouro' => $faker->sentence(400),
            'complemento' => $faker->sentence(400),
            'bairro' => $faker->sentence(400),
            'cidade' => $faker->sentence(400),
            'telefone' => $faker->sentence(400),
            'tipo_telefone' => $faker->sentence(400),
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
    public function cannot_update_table_pre_registros_by_ajax_with_idregional_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
    public function cannot_update_table_anexos_by_ajax_without_type_file()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => 'C://arquivo.jpeg'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('anexos', [
            'pre_registro_id' => 1,
            'nome_original' => 'arquivo.jpeg'
        ]);
    }

    /** @test */
    public function cannot_update_table_anexos_by_ajax_with_wrong_extension_file()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $extensoes = ['gif', 'txt', 'doc', 'docx', 'ppt', 'pptx', 'exe', 'php', 'xlsx', 'sql'];

        foreach($extensoes as $extensao)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => UploadedFile::fake()->create('random.' . $extensao)
            ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('anexos', [
            'pre_registro_id' => 1,
            'nome_original' => 'random.gif'
        ]);
    }

    /** @test */
    public function cannot_update_table_anexos_by_ajax_with_size_more_than_5120_kb()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf', 5121)
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('anexos', [
            'pre_registro_id' => 1,
            'nome_original' => 'random.pdf'
        ]);
    }

    /** @test */
    public function cannot_update_table_anexos_by_ajax_with_more_than_5_files()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        for($count = 1; $count <= 5; $count++)
        {
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => UploadedFile::fake()->create('random' . $count . '.pdf')
            ])->assertOk();

            $this->assertDatabaseHas('anexos', [
                'pre_registro_id' => 1,
                'nome_original' => 'random' . $count. '.pdf',
            ]);
        }

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random6.pdf')
        ])->assertOk();

        $this->assertDatabaseMissing('anexos', [
            'pre_registro_id' => 1,
            'nome_original' => 'random6.pdf'
        ]);
    }

    /** @test */
    public function owner_can_delete_file()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();

        $id = $externo->load('preRegistro')->preRegistro->anexos->first()->id;

        $this->delete(route('externo.preregistro.anexo.excluir', $id))->assertOk();

        $this->assertDatabaseMissing('anexos', [
            'pre_registro_id' => 1,
            'nome_original' => 'random.pdf'
        ]);
    }

    /** @test */
    public function owner_can_download_file()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();

        $id = $externo->load('preRegistro')->preRegistro->anexos->first()->id;

        $this->get(route('externo.preregistro.anexo.download', $id))->assertOk();
    }

    /** @test */
    public function not_owner_cannot_delete_file()
    {
        $faker = \Faker\Factory::create();
        $pr = factory('App\PreRegistro')->create();
        $anexo = factory('App\Anexo')->create([
            'path' => 'userExterno/pre_registro/' . $faker->uuid,
            'nome_original' => 'not_owner.jpg',
            'pre_registro_id' => $pr->id
        ]);

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->delete(route('externo.preregistro.anexo.excluir', $anexo->id))->assertStatus(401);

        $this->assertDatabaseHas('anexos', [
            'path' => $anexo->path,
            'nome_original' => $anexo->nome_original,
            'pre_registro_id' => $pr->id
        ]);
    }

    /** @test */
    public function not_owner_cannot_download_file()
    {
        $faker = \Faker\Factory::create();
        $pr = factory('App\PreRegistro')->create();
        $anexo = factory('App\Anexo')->create([
            'path' => 'userExterno/pre_registro/' . $faker->uuid,
            'nome_original' => 'not_owner.jpg',
            'pre_registro_id' => $pr->id
        ]);

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->get(route('externo.preregistro.anexo.download', $anexo->id))->assertStatus(401);
    }

    /** @test */
    public function owner_cannot_delete_without_file()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->delete(route('externo.preregistro.anexo.excluir', 1))->assertStatus(401);
    }

    /** @test */
    public function owner_cannot_download_without_file()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->get(route('externo.preregistro.anexo.download', 1))->assertStatus(401);
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax_when_clean_inputs()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->create([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroPF = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => $preRegistro->id,
        ]);

        $preRegistro = $preRegistro->toArray();
        $preRegistro['tipo_telefone_1'] = '';
        $preRegistro['telefone_1'] = '';

        $pular = ['user_externo_id', 'contabil_id', 'idusuario', 'status', 'justificativa', 'updated_at', 'created_at', 'id'];
        
        foreach($preRegistro as $key => $value)
        {
            if(!in_array($key, $pular))
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'preRegistro',
                    'campo' => $key,
                    'valor' => ''
                ])->assertStatus(200);
        }
        
        unset($preRegistro['tipo_telefone_1']);
        unset($preRegistro['telefone_1']);

        $this->assertDatabaseMissing('pre_registros', $preRegistro);
    }
}
