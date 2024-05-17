<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\PreRegistro;
use App\Anexo;
use Illuminate\Foundation\Testing\WithFaker;

class AnexoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO
     * =======================================================================================================
     */

    /** @test */
    public function view_files()
    {
        Storage::fake('local');

        // PF
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(300),
            UploadedFile::fake()->image('random1.png')->size(400),
            UploadedFile::fake()->create('random2.pdf')->size(100),
        ];

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSee(Anexo::find(1)->nome_original);

        Storage::disk('local')->assertExists('userExterno/pre_registros/1/'.Anexo::find(1)->nome_original);

        // PJ
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSee(Anexo::find(2)->nome_original);

        Storage::disk('local')->assertExists('userExterno/pre_registros/2/'.Anexo::find(2)->nome_original);
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
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
        $this->assertNotEquals($atual, PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    }

    /** @test */
    public function can_create_anexos_by_ajax()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertStatus(200);
        
        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id,
            'tipo' => null,
        ]);

        $anexos = $externo->load('preRegistro')->preRegistro->anexos;
        Storage::disk('local')->assertExists($anexos->get(0)->path);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_with_status_aprovado()
    {
        $externo = $this->signInAsUserExterno();
        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create(),
        ]);
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertStatus(401);
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => 2
        ]);

        Storage::disk('local')->assertMissing('userExterno/pre_registros/1/');
    }

    /** @test */
    public function log_is_generated_when_anexo_created()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertStatus(200);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário Externo com cpf ' . $externo->cpf_cnpj . ', anexou o arquivo "' . Anexo::first()->nome_original . '"';
        $txt .= ', que possui a ID: ' . Anexo::first()->id . ' na solicitação de registro com a id: ' . $externo->preRegistro->id;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function can_upload_anexo_up_to_15_files_by_ajax()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
        ];

        $this->assertEquals(count($anexos), 15);
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertStatus(200);
        
        $this->assertDatabaseHas('anexos', [
            'nome_original' => Anexo::find(1)->nome_original,
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id,
            'extensao' => 'zip',
            'tipo' => null,
        ]);

        Storage::disk('local')->assertExists(Anexo::find(1)->path);
    }

    /** @test */
    public function cannot_upload_more_than_15_files_by_ajax()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
        ];
        
        $this->assertEquals(count($anexos) > 15, true);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertSessionHasErrors(['valor']);
        
        $this->assertDatabaseMissing('anexos', [
            'id' => 1,
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id,
            'extensao' => 'zip',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function can_upload_up_to_10_anexos_with_15_files_if_pf_by_ajax()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $id = $externo->load('preRegistro')->preRegistro->id;

        $anexos = [
            UploadedFile::fake()->image('random1.jpg')->size(10),
            UploadedFile::fake()->image('random2.jpg')->size(10),
            UploadedFile::fake()->image('random3.jpg')->size(10),
            UploadedFile::fake()->image('random4.jpg')->size(10),
            UploadedFile::fake()->image('random5.jpg')->size(10),
            UploadedFile::fake()->image('random6.jpg')->size(10),
            UploadedFile::fake()->image('random7.jpg')->size(10),
            UploadedFile::fake()->image('random8.jpg')->size(10),
            UploadedFile::fake()->image('random9.jpg')->size(10),
            UploadedFile::fake()->image('random10.jpg')->size(10),
            UploadedFile::fake()->image('random11.jpg')->size(10),
            UploadedFile::fake()->image('random12.jpg')->size(10),
            UploadedFile::fake()->image('random13.jpg')->size(10),
            UploadedFile::fake()->image('random14.jpg')->size(10),
            UploadedFile::fake()->image('random15.jpg')->size(10),
        ];

        $this->assertEquals(count($anexos), 15);

        for($cont = 1; $cont <= 10; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => $anexos
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id,
                'extensao' => 'zip',
                'tipo' => null,
            ]);
    
            Storage::disk('local')->assertExists(Anexo::find($cont)->path);
        }

        $this->assertEquals(Anexo::count(), 10);
    }

    /** @test */
    public function cannot_create_if_pf_and_more_than_10_anexos_by_ajax()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $id = $externo->load('preRegistro')->preRegistro->id;

        for($cont = 1; $cont <= 10; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => [UploadedFile::fake()->image('random_' . $cont . '.jpg')->size(10)]
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id,
                'extensao' => 'jpeg',
                'tipo' => null,
            ]);
    
            Storage::disk('local')->assertExists(Anexo::find($cont)->path);
        }

        $this->assertEquals(Anexo::count(), 10);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random_11.jpg')->size(10)]
        ])->assertStatus(200);

        $this->assertEquals(Anexo::count(), 10);
    }

    /** @test */
    public function can_total_upload_to_15_anexos_if_pj_by_ajax()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $id = $externo->load('preRegistro')->preRegistro->id;

        for($cont = 1; $cont <= 15; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => [UploadedFile::fake()->image('random_' . $cont . '.jpg')->size(10)]
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id,
                'extensao' => 'jpeg',
                'tipo' => null,
            ]);
    
            Storage::disk('local')->assertExists(Anexo::find($cont)->path);
        }

        $this->assertEquals(Anexo::count(), 15);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random_16.jpg')->size(10)]
        ])->assertStatus(200);

        $this->assertEquals(Anexo::count(), 15);
    }

    /** @test */
    public function can_upload_up_to_15_anexos_with_15_files_if_pj_by_ajax()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $id = $externo->load('preRegistro')->preRegistro->id;

        $anexos = [
            UploadedFile::fake()->image('random1.jpg')->size(10),
            UploadedFile::fake()->image('random2.jpg')->size(10),
            UploadedFile::fake()->image('random3.jpg')->size(10),
            UploadedFile::fake()->image('random4.jpg')->size(10),
            UploadedFile::fake()->image('random5.jpg')->size(10),
            UploadedFile::fake()->image('random6.jpg')->size(10),
            UploadedFile::fake()->image('random7.jpg')->size(10),
            UploadedFile::fake()->image('random8.jpg')->size(10),
            UploadedFile::fake()->image('random9.jpg')->size(10),
            UploadedFile::fake()->image('random10.jpg')->size(10),
            UploadedFile::fake()->image('random11.jpg')->size(10),
            UploadedFile::fake()->image('random12.jpg')->size(10),
            UploadedFile::fake()->image('random13.jpg')->size(10),
            UploadedFile::fake()->image('random14.jpg')->size(10),
            UploadedFile::fake()->image('random15.jpg')->size(10),
        ];

        $this->assertEquals(count($anexos), 15);

        for($cont = 1; $cont <= 15; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => $anexos
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id,
                'extensao' => 'zip',
                'tipo' => null,
            ]);
    
            Storage::disk('local')->assertExists(Anexo::find($cont)->path);
        }

        $this->assertEquals(Anexo::count(), 15);
    }

    /** @test */
    public function cannot_upload_more_than_15_files_if_pj_by_ajax()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $anexos = [
            UploadedFile::fake()->image('random1.jpg')->size(10),
            UploadedFile::fake()->image('random2.jpg')->size(10),
            UploadedFile::fake()->image('random3.jpg')->size(10),
            UploadedFile::fake()->image('random4.jpg')->size(10),
            UploadedFile::fake()->image('random5.jpg')->size(10),
            UploadedFile::fake()->image('random6.jpg')->size(10),
            UploadedFile::fake()->image('random7.jpg')->size(10),
            UploadedFile::fake()->image('random8.jpg')->size(10),
            UploadedFile::fake()->image('random9.jpg')->size(10),
            UploadedFile::fake()->image('random10.jpg')->size(10),
            UploadedFile::fake()->image('random11.jpg')->size(10),
            UploadedFile::fake()->image('random12.jpg')->size(10),
            UploadedFile::fake()->image('random13.jpg')->size(10),
            UploadedFile::fake()->image('random14.jpg')->size(10),
            UploadedFile::fake()->image('random15.jpg')->size(10),
            UploadedFile::fake()->image('random15.jpg')->size(10),
        ];

        $this->assertEquals(count($anexos) > 15, true);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertSessionHasErrors(['valor']);

        $this->assertDatabaseMissing('anexos', [
            'id' => 1,
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id,
            'extensao' => 'zip',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_create_if_pj_and_more_than_15_anexos_by_ajax()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $id = $externo->load('preRegistro')->preRegistro->id;

        for($cont = 1; $cont <= 15; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => [UploadedFile::fake()->image('random_' . $cont . '.jpg')->size(10)]
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id,
                'extensao' => 'jpeg',
                'tipo' => null,
            ]);
    
            Storage::disk('local')->assertExists(Anexo::find($cont)->path);
        }

        for($cont = 1; $cont <= 15; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => [UploadedFile::fake()->image('random_' . $cont . '.jpg')->size(10)]
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id,
                'extensao' => 'jpeg',
                'tipo' => null,
            ]);
    
            Storage::disk('local')->assertExists(Anexo::find($cont)->path);
        }

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random_16.jpg')->size(10)]
        ])->assertStatus(200);

        $this->assertEquals(Anexo::count(), 15);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_wrong_input_name()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $id = $externo->load('preRegistro')->preRegistro->id;
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path_erro',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => $id,
            'extensao' => 'jpeg',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_without_classe()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $id = $externo->load('preRegistro')->preRegistro->id;
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => '',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => $id,
            'extensao' => 'jpeg',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_wrong_classe()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $id = $externo->load('preRegistro')->preRegistro->id;
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos_erro',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => $id,
            'extensao' => 'jpeg',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_without_campo()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $id = $externo->load('preRegistro')->preRegistro->id;
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => '',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => $id,
            'extensao' => 'jpeg',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_without_type_file()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $id = $externo->load('preRegistro')->preRegistro->id;
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => ['C://teste.jpg']
        ])->assertSessionHasErrors('valor.0');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => $id,
            'extensao' => 'jpeg',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_update_table_anexos_by_ajax_with_wrong_extension_file()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $extensoes = ['gif', 'txt', 'doc', 'docx', 'ppt', 'pptx', 'exe', 'php', 'xlsx', 'sql'];

        foreach($extensoes as $extensao)
        {
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => [UploadedFile::fake()->create('random.' . $extensao)]
            ])->assertSessionHasErrors('valor.0');

            $this->assertDatabaseMissing('anexos', [
                'pre_registro_id' => 1,
                'nome_original' => 'random.' . $extensao,
                'extensao' => $extensao,
                'tipo' => null,
            ]);
        }

        $this->assertEquals(Anexo::count(), 0);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_with_size_more_than_5120_kb()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $id = $externo->load('preRegistro')->preRegistro->id;
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(5121)]
        ])->assertSessionHasErrors('total');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.pdf',
            'pre_registro_id' => $id,
            'extensao' => 'pdf',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_with_size_more_than_5120_kb_if_zip()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $id = $externo->load('preRegistro')->preRegistro->id;
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [
                UploadedFile::fake()->create('random.pdf')->size(2500),
                UploadedFile::fake()->create('random1.pdf')->size(2500),
                UploadedFile::fake()->create('random2.pdf')->size(150),
            ]
        ])->assertSessionHasErrors('total');

        $this->assertDatabaseMissing('anexos', [
            'pre_registro_id' => $id,
            'extensao' => 'zip',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_without_type_array()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $id = $externo->load('preRegistro')->preRegistro->id;
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')->size(2500)->path()
        ])->assertSessionHasErrors('valor');
    }

    /** @test */
    public function owner_can_delete_file()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        Storage::disk('local')->assertExists(Anexo::find(1)->path);
        $caminho = Anexo::find(1)->path;

        $this->delete(route('externo.preregistro.anexo.excluir', Anexo::find(1)->id))->assertOk();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertDontSee('random.pdf');

        $this->assertDatabaseMissing('anexos', [
            'pre_registro_id' => 1,
            'nome_original' => 'random.pdf'
        ]);

        Storage::disk('local')->assertMissing($caminho);
    }

    /** @test */
    public function log_is_generated_when_anexo_deleted()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        Storage::disk('local')->assertExists(Anexo::find(1)->path);
        $caminho = Anexo::find(1)->path;

        $this->delete(route('externo.preregistro.anexo.excluir', Anexo::find(1)->id))->assertOk();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário Externo com cpf: '.$externo->cpf_cnpj.', excluiu o arquivo com a ID: 1';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function owner_can_download_file()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        Storage::disk('local')->assertExists(Anexo::find(1)->path);

        $this->get(route('externo.preregistro.anexo.download', Anexo::find(1)->id))->assertOk();
    }

    /** @test */
    public function owner_cannot_view_doc_atendimento_without_file_when_approved()
    {
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $externo = $this->signInAsUserExterno('user_externo', $preRegistroCpf->preRegistro->userExterno);

        $this->get(route('externo.preregistro.view'))
        ->assertSee('<p><i class="fas fa-exclamation-circle text-primary"></i>&nbsp;Documentos do atendimento ainda não estão disponíveis.</p>');
    }

    /** @test */
    public function owner_can_download_doc_atendimento()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ])
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i> Boleto anexado com sucesso!');

        $id = Anexo::where('tipo', Anexo::tiposDocsAtendentePreRegistro()[0])->where('pre_registro_id', 1)->first()->id;

        $externo = $this->signInAsUserExterno('user_externo', $preRegistroCpf->preRegistro->userExterno);

        $this->get(route('externo.preregistro.view'))
        ->assertSeeInOrder([
            '<a ',
            'class="btn btn-success text-white mt-3" ',
            'href="'. route('externo.preregistro.anexo.download', Anexo::find(2)->id) .'"',
            'download',
            'Baixar '. Anexo::find(2)->tipo,
            '</a>',
        ]);

        $this->get(route('externo.preregistro.anexo.download', $id))->assertOk();
    }

    /** @test */
    public function owner_cannot_delete_doc_atendimento()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ])
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i> Boleto anexado com sucesso!');

        $id = Anexo::where('tipo', Anexo::tiposDocsAtendentePreRegistro()[0])->where('pre_registro_id', 1)->first()->id;

        $externo = $this->signInAsUserExterno('user_externo', $preRegistroCpf->preRegistro->userExterno);
        $this->delete(route('externo.preregistro.anexo.excluir', $id))->assertStatus(401);
    }

    /** @test */
    public function log_is_generated_when_download_doc_atendimento()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ])
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i> Boleto anexado com sucesso!');

        $id = Anexo::where('tipo', Anexo::tiposDocsAtendentePreRegistro()[0])->where('pre_registro_id', 1)->first()->id;

        $externo = $this->signInAsUserExterno('user_externo', $preRegistroCpf->preRegistro->userExterno);
        $this->get(route('externo.preregistro.anexo.download', $id))->assertOk();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Foi realizado o download do '.Anexo::tiposDocsAtendentePreRegistro()[0].' com ID ' . $id . ' do pré-registro com ID 1.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function not_owner_cannot_delete_file()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        $pr = $externo->load('preRegistro')->preRegistro;
        $anexo = $pr->anexos->first();
        Storage::disk('local')->assertExists($anexo->path);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->delete(route('externo.preregistro.anexo.excluir', $anexo->id))->assertStatus(401);

        $this->assertDatabaseHas('anexos', [
            'path' => $anexo->path,
            'nome_original' => $anexo->nome_original,
            'pre_registro_id' => $pr->id
        ]);

        Storage::disk('local')->assertExists($anexo->path);
    }

    /** @test */
    public function not_owner_cannot_download_file()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        $anexo = $externo->load('preRegistro')->preRegistro->anexos->first();
        Storage::disk('local')->assertExists($anexo->path);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->get(route('externo.preregistro.anexo.download', $anexo->id))->assertStatus(401);
    }

    /** @test */
    public function owner_cannot_delete_without_file()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->delete(route('externo.preregistro.anexo.excluir', 1))->assertStatus(401);
    }

    /** @test */
    public function owner_cannot_download_without_file()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->get(route('externo.preregistro.anexo.download', 1))->assertStatus(401);
    }

    /** @test */
    public function owner_cannot_download_file_with_status_approved()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->get(route('externo.preregistro.anexo.download', 1))->assertStatus(401);
    }

    /** @test */
    public function filled_campos_editados_anexos_when_form_is_submitted_when_status_aguardando_correcao()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistroCpf')->create()->preRegistro;
           
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        PreRegistro::first()->update(['status' => PreRegistro::STATUS_CORRECAO]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random_2.pdf')->size(100)]
        ])->assertOk();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $this->assertEquals(json_decode(PreRegistro::first()->campos_editados, true)['path'], '2');
    }

    /** @test */
    public function view_justifications_anexos()
    {
        $externo = $this->signInAsUserExterno();

        factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'justificar',
            'campo' => 'path',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('user_externo', $externo);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeInOrder([
            '<a class="nav-link" data-toggle="pill" href="#parte_anexos">',
            'Anexos&nbsp',
            '<span class="badge badge-danger">',
            '</a>',
        ])
        ->assertSee('value="'. route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => 'path']) .'"');
    }

    /** @test */
    public function view_justifications_text_anexos()
    {
        $externo = $this->signInAsUserExterno();

        factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'justificar',
            'campo' => 'path',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->get(route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => 'path']))
        ->assertJsonFragment(['justificativa' => PreRegistro::first()->getJustificativaPorCampo('path')]);
    }

    /** 
     * ==============================================================================================================
     * TESTES PRE-REGISTRO ANEXO - LOGIN CONTABILIDADE RESPONSÁVEL PELO GERENCIAMENTO PARA O USUARIO EXTERNO COMUM
     * ===============================================================================================================
     */

    /** @test */
    public function view_files_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(300),
            UploadedFile::fake()->image('random1.png')->size(400),
            UploadedFile::fake()->create('random2.pdf')->size(100),
        ];

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSee(Anexo::find(1)->nome_original);

        Storage::disk('local')->assertExists('userExterno/pre_registros/1/'.Anexo::find(1)->nome_original);
    }

    /** @test */
    public function view_msg_update_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
        
        PreRegistro::first()->update(['updated_at' => PreRegistro::first()->updated_at->subHour()]);
        $atual = PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s');

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
        $this->assertNotEquals($atual, PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    }

    /** @test */
    public function can_create_anexos_by_ajax_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertStatus(200);
        
        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => 1,
            'tipo' => null,
        ]);

        $anexos = $externo->load('preRegistros')->preRegistros->first()->anexos;
        Storage::disk('local')->assertExists($anexos->get(0)->path);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_with_status_aprovado_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();

        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create([
                'user_externo_id' => factory('App\UserExterno')->create($dados)
            ]),
        ]);

        $this->post(route('externo.contabil.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'))
        ->assertSessionHas('message', "Este CPF / CNPJ já possui uma solicitação aprovada.");
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertStatus(401);
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => 2,
            'tipo' => null,
        ]);
    }

    /** @test */
    public function log_is_generated_when_anexo_created_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertStatus(200);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Contabilidade com cnpj ' . $externo->cnpj . ' realizou a operação para o Usuário Externo com cpf '.$externo->preRegistros->first()->userExterno->cpf_cnpj;
        $txt .= ', anexou o arquivo "'.Anexo::first()->nome_original.'"';
        $txt .= ', que possui a ID: ' . Anexo::first()->id . ' na solicitação de registro com a id: ' . $externo->preRegistros->first()->id;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function can_upload_anexo_up_to_15_files_by_ajax_by_contabilidade()
    {
        Storage::fake('local');
        
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
        ];

        $this->assertEquals(count($anexos), 15);
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertStatus(200);
        
        $this->assertDatabaseHas('anexos', [
            'nome_original' => Anexo::find(1)->nome_original,
            'pre_registro_id' => $externo->preRegistros->first()->id,
            'tipo' => null,
        ]);

        Storage::disk('local')->assertExists(Anexo::find(1)->path);
    }

    /** @test */
    public function cannot_upload_more_than_15_files_by_ajax_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
            UploadedFile::fake()->image('random.jpg')->size(10),
        ];

        $this->assertEquals(count($anexos) > 15, true);
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertSessionHasErrors(['valor']);
        
        $this->assertDatabaseMissing('anexos', [
            'id' => 1,
            'pre_registro_id' => $externo->preRegistros->first()->id,
            'tipo' => null,
        ]);
    }

    /** @test */
    public function can_upload_up_to_10_anexos_with_15_files_if_pf_by_ajax_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;

        $anexos = [
            UploadedFile::fake()->image('random1.jpg')->size(10),
            UploadedFile::fake()->image('random2.jpg')->size(10),
            UploadedFile::fake()->image('random3.jpg')->size(10),
            UploadedFile::fake()->image('random4.jpg')->size(10),
            UploadedFile::fake()->image('random5.jpg')->size(10),
            UploadedFile::fake()->image('random6.jpg')->size(10),
            UploadedFile::fake()->image('random7.jpg')->size(10),
            UploadedFile::fake()->image('random8.jpg')->size(10),
            UploadedFile::fake()->image('random9.jpg')->size(10),
            UploadedFile::fake()->image('random10.jpg')->size(10),
            UploadedFile::fake()->image('random11.jpg')->size(10),
            UploadedFile::fake()->image('random12.jpg')->size(10),
            UploadedFile::fake()->image('random13.jpg')->size(10),
            UploadedFile::fake()->image('random14.jpg')->size(10),
            UploadedFile::fake()->image('random15.jpg')->size(10),
        ];

        $this->assertEquals(count($anexos), 15);

        for($cont = 1; $cont <= 10; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => $anexos
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id,
                'tipo' => null,
            ]);

            Storage::disk('local')->assertExists(Anexo::find($cont)->path);
        }

        $this->assertEquals(Anexo::count(), 10);
    }

    /** @test */
    public function cannot_create_if_pf_and_more_than_10_anexos_by_ajax_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;

        for($cont = 1; $cont <= 10; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => [UploadedFile::fake()->image('random_' . $cont . '.jpg')->size(10)]
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id,
                'extensao' => 'jpeg',
                'tipo' => null,
            ]);

            Storage::disk('local')->assertExists(Anexo::find($cont)->path);
        }

        $this->assertEquals(Anexo::count(), 10);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random_11.jpg')->size(10)]
        ])->assertStatus(200);

        $this->assertEquals(Anexo::count(), 10);
    }

    /** @test */
    public function can_total_upload_to_15_anexos_if_pj_by_ajax_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;

        for($cont = 1; $cont <= 15; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => [UploadedFile::fake()->image('random_' . $cont . '.jpg')->size(10)]
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id
            ]);

            Storage::disk('local')->assertExists(Anexo::find($cont)->path);
        }

        $this->assertEquals(Anexo::count(), 15);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random_16.jpg')->size(10)]
        ])->assertStatus(200);

        $this->assertEquals(Anexo::count(), 15);
    }

    /** @test */
    public function can_upload_up_to_15_anexos_with_15_files_if_pj_by_ajax_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;

        $anexos = [
            UploadedFile::fake()->image('random1.jpg')->size(10),
            UploadedFile::fake()->image('random2.jpg')->size(10),
            UploadedFile::fake()->image('random3.jpg')->size(10),
            UploadedFile::fake()->image('random4.jpg')->size(10),
            UploadedFile::fake()->image('random5.jpg')->size(10),
            UploadedFile::fake()->image('random6.jpg')->size(10),
            UploadedFile::fake()->image('random7.jpg')->size(10),
            UploadedFile::fake()->image('random8.jpg')->size(10),
            UploadedFile::fake()->image('random9.jpg')->size(10),
            UploadedFile::fake()->image('random10.jpg')->size(10),
            UploadedFile::fake()->image('random11.jpg')->size(10),
            UploadedFile::fake()->image('random12.jpg')->size(10),
            UploadedFile::fake()->image('random13.jpg')->size(10),
            UploadedFile::fake()->image('random14.jpg')->size(10),
            UploadedFile::fake()->image('random15.jpg')->size(10),
        ];

        $this->assertEquals(count($anexos), 15);

        for($cont = 1; $cont <= 15; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => $anexos
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id,
                'extensao' => 'zip',
                'tipo' => null,
            ]);

            Storage::disk('local')->assertExists(Anexo::find($cont)->path);
        }

        $this->assertEquals(Anexo::count(), 15);
    }

    /** @test */
    public function cannot_upload_more_than_15_files_if_pj_by_ajax_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;

        $anexos = [
            UploadedFile::fake()->image('random1.jpg')->size(10),
            UploadedFile::fake()->image('random2.jpg')->size(10),
            UploadedFile::fake()->image('random3.jpg')->size(10),
            UploadedFile::fake()->image('random4.jpg')->size(10),
            UploadedFile::fake()->image('random5.jpg')->size(10),
            UploadedFile::fake()->image('random6.jpg')->size(10),
            UploadedFile::fake()->image('random7.jpg')->size(10),
            UploadedFile::fake()->image('random8.jpg')->size(10),
            UploadedFile::fake()->image('random9.jpg')->size(10),
            UploadedFile::fake()->image('random10.jpg')->size(10),
            UploadedFile::fake()->image('random11.jpg')->size(10),
            UploadedFile::fake()->image('random12.jpg')->size(10),
            UploadedFile::fake()->image('random13.jpg')->size(10),
            UploadedFile::fake()->image('random14.jpg')->size(10),
            UploadedFile::fake()->image('random15.jpg')->size(10),
            UploadedFile::fake()->image('random15.jpg')->size(10),
        ];

        $this->assertEquals(count($anexos) > 15, true);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertSessionHasErrors(['valor']);

        $this->assertDatabaseMissing('anexos', [
            'id' => 1,
            'pre_registro_id' => $id,
            'extensao' => 'zip',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_create_if_pj_and_more_than_15_anexos_by_ajax_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;

        for($cont = 1; $cont <= 15; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => [UploadedFile::fake()->image('random_' . $cont . '.jpg')->size(10)]
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id,
                'extensao' => 'jpeg',
                'tipo' => null,
            ]);

            Storage::disk('local')->assertExists(Anexo::find($cont)->path);
        }

        for($cont = 1; $cont <= 15; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => [UploadedFile::fake()->image('random_' . $cont . '.jpg')->size(10)]
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id,
                'extensao' => 'jpeg',
                'tipo' => null,
            ]);

            Storage::disk('local')->assertExists(Anexo::find($cont)->path);
        }

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random_16.jpg')->size(10)]
        ])->assertStatus(200);

        $this->assertEquals(Anexo::count(), 15);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_wrong_input_name_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path_erro',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => $id,
            'extensao' => 'jpeg',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_without_classe_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => '',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => $id,
            'extensao' => 'jpeg',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_wrong_classe_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos_erro',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => $id,
            'extensao' => 'jpeg',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_without_campo_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => '',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => $id,
            'extensao' => 'jpeg',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_without_type_file_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => ['C://teste.jpg']
        ])->assertSessionHasErrors('valor.0');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => $id,
            'extensao' => 'jpeg',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_update_table_anexos_by_ajax_with_wrong_extension_file_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $extensoes = ['gif', 'txt', 'doc', 'docx', 'ppt', 'pptx', 'exe', 'php', 'xlsx', 'sql'];

        foreach($extensoes as $extensao)
        {
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => [UploadedFile::fake()->create('random.' . $extensao)]
            ])->assertSessionHasErrors('valor.0');

            $this->assertDatabaseMissing('anexos', [
                'pre_registro_id' => 1,
                'nome_original' => 'random.' . $extensao,
                'extensao' => 'jpeg',
                'tipo' => null,
            ]);
        }

        $this->assertEquals(Anexo::count(), 0);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_with_size_more_than_5120_kb_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(5121)]
        ])->assertSessionHasErrors('total');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.pdf',
            'pre_registro_id' => $id,
            'extensao' => 'jpeg',
            'tipo' => null,
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_with_size_more_than_5120_kb_if_zip_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [
                UploadedFile::fake()->create('random.pdf')->size(2500),
                UploadedFile::fake()->create('random1.pdf')->size(2500),
                UploadedFile::fake()->create('random2.pdf')->size(150),
            ]
        ])->assertSessionHasErrors('total');
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_without_type_array_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')->size(2500)->path()
        ])->assertSessionHasErrors('valor');
    }

    /** @test */
    public function owner_can_delete_file_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        Storage::disk('local')->assertExists(Anexo::find(1)->path);
        $caminho = Anexo::find(1)->path;

        $this->delete(route('externo.preregistro.anexo.excluir', ['id' => Anexo::find(1)->id, 'preRegistro' => 1]))->assertOk();

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertDontSee('random.pdf');

        $this->assertDatabaseMissing('anexos', [
            'pre_registro_id' => 1,
            'nome_original' => 'random.pdf',
            'extensao' => 'pdf',
            'tipo' => null,
        ]);

        Storage::disk('local')->assertMissing($caminho);
    }

    /** @test */
    public function log_is_generated_when_anexo_deleted_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        Storage::disk('local')->assertExists(Anexo::find(1)->path);
        $caminho = Anexo::find(1)->path;

        $this->delete(route('externo.preregistro.anexo.excluir', ['id' => Anexo::find(1)->id, 'preRegistro' => 1]))->assertOk();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Contabilidade com cnpj '.$externo->cnpj.' realizou a operação para o Usuário Externo com cpf: ';
        $txt .= $externo->preRegistros->first()->userExterno->cpf_cnpj.', excluiu o arquivo com a ID: 1 na solicitação de registro com a id: 1';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function owner_can_download_file_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        Storage::disk('local')->assertExists(Anexo::find(1)->path);

        $this->get(route('externo.preregistro.anexo.download', ['id' => Anexo::find(1)->id, 'preRegistro' => 1]))->assertOk();
    }

    /** @test */
    public function owner_cannot_view_doc_atendimento_without_file_when_approved_by_contabilidade()
    {        
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $externo = $this->signInAsUserExterno('contabil', $preRegistroCpf->preRegistro->contabil);
        
        $this->get(route('externo.relacao.preregistros'))
        ->assertSee('<span><i>Documentos do atendimento ainda não estão disponíveis.</i></span>');
    }

    /** @test */
    public function owner_can_download_doc_atendimento_by_contabilidade()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ])
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i> Boleto anexado com sucesso!');

        $id = Anexo::where('tipo', Anexo::tiposDocsAtendentePreRegistro()[0])->where('pre_registro_id', 1)->first()->id;

        $externo = $this->signInAsUserExterno('contabil', $preRegistroCpf->preRegistro->contabil);

        $this->get(route('externo.relacao.preregistros'))
        ->assertSeeInOrder([
            '&nbsp; | &nbsp;',
            '<a ',
            'class="btn btn-success btn-sm text-white" ',
            'href="'. route('externo.preregistro.anexo.download', ['id' => 2, 'preRegistro' => 1]) .'"',
            'download',
            'Baixar '. Anexo::find(2)->tipo,
            '</a>',
        ]);

        $this->get(route('externo.preregistro.anexo.download', ['id' => $id, 'preRegistro' => 1]))->assertOk();
    }

    /** @test */
    public function owner_cannot_delete_doc_atendimento_by_contabilidade()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ])
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i> Boleto anexado com sucesso!');

        $id = Anexo::where('tipo', Anexo::tiposDocsAtendentePreRegistro()[0])->where('pre_registro_id', 1)->first()->id;

        $externo = $this->signInAsUserExterno('contabil', $preRegistroCpf->preRegistro->contabil);
        $this->delete(route('externo.preregistro.anexo.excluir', ['id' => $id, 'preRegistro' => 1]))->assertStatus(401);
    }

    /** @test */
    public function log_is_generated_when_download_doc_atendimento_by_contabilidade()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ])
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i> Boleto anexado com sucesso!');

        $id = Anexo::where('tipo', Anexo::tiposDocsAtendentePreRegistro()[0])->where('pre_registro_id', 1)->first()->id;

        $externo = $this->signInAsUserExterno('contabil', $preRegistroCpf->preRegistro->contabil);
        $this->get(route('externo.preregistro.anexo.download', ['id' => $id, 'preRegistro' => 1]))->assertOk();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Foi realizado o download do '.Anexo::tiposDocsAtendentePreRegistro()[0].' com ID ' . $id . ' do pré-registro com ID 1.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function not_owner_cannot_delete_file_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        $pr = $externo->preRegistros->first();
        $anexo = $pr->anexos->first();
        Storage::disk('local')->assertExists($anexo->path);

        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->delete(route('externo.preregistro.anexo.excluir', ['id' => $anexo->id, 'preRegistro' => 2]))->assertStatus(401);

        $this->assertDatabaseHas('anexos', [
            'path' => $anexo->path,
            'nome_original' => $anexo->nome_original,
            'pre_registro_id' => $pr->id,
            'extensao' => 'pdf',
            'tipo' => null,
        ]);

        Storage::disk('local')->assertExists($anexo->path);
    }

    /** @test */
    public function not_owner_cannot_download_file_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        $anexo = $externo->preRegistros->first()->anexos->first();
        Storage::disk('local')->assertExists($anexo->path);

        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.preregistro.anexo.download', ['id' => $anexo->id, 'preRegistro' => 2]))->assertStatus(401);
    }

    /** @test */
    public function owner_cannot_delete_without_file_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->delete(route('externo.preregistro.anexo.excluir', ['id' => 1, 'preRegistro' => 1]))->assertStatus(401);
    }

    /** @test */
    public function owner_cannot_download_without_file_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.preregistro.anexo.download', ['id' => 1, 'preRegistro' => 1]))->assertStatus(401);
    }

    /** @test */
    public function owner_cannot_download_file_with_status_approved_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);
        factory('App\Anexo')->states('pre_registro')->create();

        $this->get(route('externo.preregistro.anexo.download', ['id' => 1, 'preRegistro' => 1]))->assertStatus(401);
    }

    /** @test */
    public function filled_campos_editados_anexos_when_form_is_submitted_when_status_aguardando_correcao_by_contabilidade()
    {
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        
        $preRegistro = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        PreRegistro::first()->update(['status' => PreRegistro::STATUS_CORRECAO]);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $this->assertEquals(json_decode(PreRegistro::first()->campos_editados, true)['path'], '2');
    }

    /** @test */
    public function view_justifications_anexos_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $admin = $this->signIn(PreRegistro::first()->user);

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'justificar',
            'campo' => 'path',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('contabil', $externo);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeInOrder([
            '<a class="nav-link" data-toggle="pill" href="#parte_anexos">',
            'Anexos&nbsp',
            '<span class="badge badge-danger">',
            '</a>',
        ])
        ->assertSee('value="'. route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => 'path']) .'"');
    }

    /** @test */
    public function view_justifications_text_anexos_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $admin = $this->signIn(PreRegistro::first()->user);

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'justificar',
            'campo' => 'path',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->get(route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => 'path']))
        ->assertJsonFragment(['justificativa' => PreRegistro::first()->getJustificativaPorCampo('path')]);
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function view_pre_registro_anexos()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);
        
        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeInOrder([
            '<span class="font-weight-bolder">ID: '. Anexo::first()->id .' </span>', 
            '<p class="mb-0">', 
            '<i class="fas fa-paperclip"></i>',
            '<a href="'. route('preregistro.anexo.download', ['idPreRegistro' => 1, 'id' => 1]) .'"',
            'class="ml-2" ',
            'target="_blank" ',
            '<u>'. Anexo::first()->nome_original .'</u>',
            '</a>',
            '<a href="'. route('preregistro.anexo.download', ['idPreRegistro' => 1, 'id' => 1]) .'"',
            'class="btn btn-sm btn-primary ml-2"',
            'download',
            '<i class="fas fa-download"></i>',
            '</a>'
        ]);
    }

    /** @test */
    public function view_pre_registro_anexos_with_extension_zip()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);
        factory('App\Anexo')->states('pre_registro', 'zip')->create();
        
        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeInOrder([
            '<span class="font-weight-bolder">ID: '. Anexo::find(2)->id .' </span>', 
            '<p class="mb-0">', 
            '<i class="fas fa-paperclip"></i> ' . Anexo::find(2)->nome_original,
            '<a href="'. route('preregistro.anexo.download', ['idPreRegistro' => 1, 'id' => 2]) .'"',
            'class="btn btn-sm btn-primary ml-2"',
            'download',
            '<i class="fas fa-download"></i>',
            '</a>'
        ]);
    }

    /** @test */
    public function view_text_justificado_anexo()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'justificar',
            'campo' => 'path',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200);

        $justificativas = $preRegistroCpf->preRegistro->fresh()->getJustificativaArray();

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeText($justificativas['path']);
    }

    /** @test */
    public function view_justifications_text_anexos_by_url()
    {
        $externo = $this->signInAsUserExterno();

        factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['checkPreRegistro' => 'on']), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'justificar',
            'campo' => 'path',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);
        $data_hora = now()->format('Y-m-d H:i:s');

        $this->get(route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => 'path', 'data_hora' => urlencode($data_hora)]))
        ->assertJsonFragment([
            'justificativa' => PreRegistro::first()->getJustificativaPorCampoData('path', $data_hora),
            'data_hora' => formataData($data_hora)
        ]);
    }

    /** @test */
    public function view_historico_justificativas_anexos()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['checkPreRegistro' => 'on']), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'justificar',
            'campo' => 'path',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);
        $data_hora = now()->format('Y-m-d H:i:s');

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSee('value="'.route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => 'path', 'data_hora' => urlencode($data_hora)]).'"');
    }

    /** @test */
    public function view_label_novo_anexo()
    {
        $this->filled_campos_editados_anexos_when_form_is_submitted_when_status_aguardando_correcao();

        $admin = $this->signIn(PreRegistro::first()->user);

        $camposEditados = json_decode(PreRegistro::first()->campos_editados, true);

        $this->get(route('preregistro.view', 1))
        ->assertSeeInOrder([
            '<a class="card-link" data-toggle="collapse" href="#parte_anexos">',
            '<div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">',
            '7. Anexos',
            '<span class="badge badge-success ml-2">Novos anexos</span>',
        ]);
            
        $this->get(route('preregistro.view', 1))
        ->assertSee('<span class="badge badge-success ml-2">Novo anexo</span>');
    }

    /** @test */
    public function view_label_justificado_anexos()
    {
        $this->view_text_justificado_anexo();

        $admin = $this->signIn(PreRegistro::first()->user);
            
        $this->get(route('preregistro.view', 1))->assertSeeInOrder([
            '<p id="path" class="mb-4">',
            'type="button" ',
            'value="path"',
            '<i class="fas fa-edit"></i>',
            '<span class="badge badge-warning just ml-2">Justificado</span>',
            '</p>',
        ]);
    }

    /** @test */
    public function can_upload_doc_atendimento_after_approved()
    {
        Storage::fake();

        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ])
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i> Boleto anexado com sucesso!');

        $id = Anexo::where('tipo', Anexo::tiposDocsAtendentePreRegistro()[0])->where('pre_registro_id', 1)->first()->id;

        $this->assertDatabaseHas('anexos', [
            'pre_registro_id' => $preRegistroCpf->preRegistro->id,
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ]);

        $anexos = $preRegistroCpf->preRegistro->anexos;
        Storage::disk('local')->assertExists(Anexo::find($id)->path);
    }

    /** @test */
    public function cannot_upload_without_file_after_approved()
    {
        Storage::fake();

        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => null,
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ])
        ->assertSessionHasErrors([
            'file'
        ]);
    }

    /** @test */
    public function cannot_upload_file_more_than_2MB_after_approved()
    {
        Storage::fake();

        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(2049),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ])
        ->assertSessionHasErrors([
            'file'
        ]);
    }

    /** @test */
    public function cannot_upload_file_invalid_extension_after_approved()
    {
        Storage::fake();

        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.png')->size(300),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ])
        ->assertSessionHasErrors([
            'file'
        ]);
    }

    /** @test */
    public function cannot_upload_file_invalid_type_after_approved()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => 'random2.pdf',
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ])
        ->assertSessionHasErrors([
            'file'
        ]);
    }

    /** @test */
    public function cannot_upload_file_without_tipo_after_approved()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300),
            'tipo' => '',
        ])
        ->assertSessionHasErrors([
            'tipo'
        ]);
    }

    /** @test */
    public function cannot_upload_file_wrong_tipo_after_approved()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0] . '_',
        ])
        ->assertSessionHasErrors([
            'tipo'
        ]);
    }

    /** @test */
    public function cannot_upload_file_with_status_not_approved()
    {
        Storage::fake();

        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ])
        ->assertSessionHas([
            'message' => '<i class="icon fas fa-times"></i> O pré-registro precisa estar aprovado para anexar documento.',
            'class' => 'alert-danger'
        ]);
    }

    /** @test */
    public function log_is_generated_when_doc_atendimento_created()
    {
        Storage::fake('local');
        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ]);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') anexou o documento "random2.pdf" do tipo boleto *pré-registro* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function view_label_doc_atendimento()
    {
        Storage::fake('local');
        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ]);

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeInOrder([
            '<legend class="w-auto pr-2">Documentos Anexados</legend>',
            '<i class="fas fa-paperclip"></i>',
            '<span class="font-weight-bolder ml-1">'. ucfirst(Anexo::find(2)->tipo) .': </span>',
            '<u>'. Anexo::find(2)->id .' - '. Anexo::find(2)->nome_original .'</u>',
            '<i class="fas fa-download"></i>',
            '<span class="ml-2"><small><i>Última atualização:</i> '. formataData(Anexo::find(2)->updated_at) .'</small></span>',
        ]);
    }

    /** @test */
    public function log_is_not_generated_when_download_doc_atendimento()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300),
            'tipo' => Anexo::tiposDocsAtendentePreRegistro()[0],
        ]);

        $this->get(route('preregistro.anexo.download', ['idPreRegistro' => 1, 'id' => 2]))->assertOk();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Foi realizado o download do boleto com ID 2 do pré-registro com ID 1.';
        $this->assertStringNotContainsString($txt, $log);
    }

    /** @test */
    public function view_form_doc_atendimento_with_status_approved()
    {
        Storage::fake('local');
        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeInOrder([
            '<p class="font-weight-bolder">Documentos gerenciados pelo atendimento após aprovação:</p>',
            '<legend class="w-auto pr-2">Anexar Documentos</legend>',
            '<form class="ml-1" action="'. route('preregistro.upload.doc', 1) .'" method="POST" enctype="multipart/form-data">',
            '<label class="mr-2 mb-0">Tipo de documento a ser anexado:</label>',
            '<input type="radio" class="form-check-input" name="tipo" value="'. Anexo::tiposDocsAtendentePreRegistro()[0] .'">'. ucfirst(Anexo::tiposDocsAtendentePreRegistro()[0]),
            '<label>Anexar novo documento <i>(irá substituir caso já exista)</i>:</label>',
            '<label class="custom-file-label" for="doc_pre_registro">Selecionar arquivo...</label>',
            '<button type="submit" class="btn btn-sm btn-primary">Enviar</button>'
        ]);
    }

    /** @test */
    public function view_message_doc_atendimento_without_status_approved()
    {
        Storage::fake('local');
        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('sendo_elaborado')->create()->id
        ]);

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSee('<p><i>Pré-registro não está aprovado.</i></p>');
    }

    /** @test */
    public function view_message_doc_atendimento_with_status_approved_without_file()
    {
        Storage::fake('local');
        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSee('<p>Sem documento anexado.</p>');
    }
}
