<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\PreRegistro;
use App\Anexo;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class AnexoTest extends TestCase
{
    use RefreshDatabase;

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
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        $anexos = $externo->load('preRegistro')->preRegistro->anexos;
        Storage::disk('local')->assertExists($anexos->get(0)->path);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_after_aprovado()
    {
        $externo = $this->signInAsUserExterno();
        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'status' => 'Aprovado'
            ]),
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
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertStatus(200);
        
        $this->assertDatabaseHas('anexos', [
            'nome_original' => Anexo::find(1)->nome_original,
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
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
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertSessionHasErrors(['valor']);
        
        $this->assertDatabaseMissing('anexos', [
            'id' => 1,
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
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

        for($cont = 1; $cont <= 10; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => $anexos
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id
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
                'pre_registro_id' => $id
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
                'pre_registro_id' => $id
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

        for($cont = 1; $cont <= 15; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => $anexos
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id
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
            UploadedFile::fake()->image('random15.jpg')->size(10),
        ];

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertSessionHasErrors(['valor']);

        $this->assertDatabaseMissing('anexos', [
            'id' => 1,
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
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
                'pre_registro_id' => $id
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
                'pre_registro_id' => $id
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
            'pre_registro_id' => $id
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
            'pre_registro_id' => $id
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
            'pre_registro_id' => $id
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
            'pre_registro_id' => $id
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
            'pre_registro_id' => $id
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
                'nome_original' => 'random.' . $extensao
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
            'pre_registro_id' => $id
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
    public function owner_can_download_boleto()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300)
        ]);

        $externo = $this->signInAsUserExterno('user_externo', $preRegistroCpf->preRegistro->userExterno);
        $this->get(route('externo.preregistro.anexo.download', Anexo::find(1)->id))->assertOk();
    }

    /** @test */
    public function owner_cannot_delete_boleto()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300)
        ]);

        $externo = $this->signInAsUserExterno('user_externo', $preRegistroCpf->preRegistro->userExterno);
        $this->delete(route('externo.preregistro.anexo.excluir', 1))->assertStatus(401);
    }

    /** @test */
    public function log_is_generated_when_download_boleto()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300)
        ]);

        $externo = $this->signInAsUserExterno('user_externo', $preRegistroCpf->preRegistro->userExterno);
        $this->get(route('externo.preregistro.anexo.download', Anexo::find(1)->id))->assertOk();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Foi realizado o download do boleto com ID 1 do pré-registro com ID 1.';
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
        factory('App\Anexo')->states('pre_registro')->create();

        $this->get(route('externo.preregistro.anexo.download', 1))->assertStatus(401);
    }

    /** @test */
    public function filled_campos_editados_anexos_when_form_is_submitted_when_status_aguardando_correcao()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()->id
        ])->makeHidden(['pre_registro_id', 'created_at', 'updated_at', 'id']);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        Anexo::find(1)->delete();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $pr = PreRegistro::first();
        $dados = Arr::except($dados, ['final', 'created_at', 'updated_at', 'deleted_at', 'pergunta']);

        $arrayFinal = array_diff(array_keys($dados), array_keys(json_decode($pr->campos_espelho, true)));
        $this->assertEquals($arrayFinal, array());
        $this->assertEquals(json_decode($pr->campos_editados, true)['path'], 2);
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
            'pre_registro_id' => 1
        ]);

        $anexos = $externo->load('preRegistros')->preRegistros->first()->anexos;
        Storage::disk('local')->assertExists($anexos->get(0)->path);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_after_aprovado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'status' => 'Aprovado'
            ]),
        ]);
        $this->post(route('externo.contabil.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')]
        ])->assertStatus(401);
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'pre_registro_id' => 2
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
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertStatus(200);
        
        $this->assertDatabaseHas('anexos', [
            'nome_original' => Anexo::find(1)->nome_original,
            'pre_registro_id' => $externo->preRegistros->first()->id
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
        
        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertSessionHasErrors(['valor']);
        
        $this->assertDatabaseMissing('anexos', [
            'id' => 1,
            'pre_registro_id' => $externo->preRegistros->first()->id
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

        for($cont = 1; $cont <= 10; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => $anexos
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id
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
                'pre_registro_id' => $id
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

        for($cont = 1; $cont <= 15; $cont++)
        {
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => $anexos
            ])->assertStatus(200);

            $this->assertDatabaseHas('anexos', [
                'nome_original' => Anexo::find($cont)->nome_original,
                'pre_registro_id' => $id
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

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertSessionHasErrors(['valor']);

        $this->assertDatabaseMissing('anexos', [
            'id' => 1,
            'pre_registro_id' => $id
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
                'pre_registro_id' => $id
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
                'pre_registro_id' => $id
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
            'pre_registro_id' => $id
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
            'pre_registro_id' => $id
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
            'pre_registro_id' => $id
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
            'pre_registro_id' => $id
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
            'pre_registro_id' => $id
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
                'nome_original' => 'random.' . $extensao
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
            'pre_registro_id' => $id
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
            'nome_original' => 'random.pdf'
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
    public function owner_can_download_boleto_by_contabilidade()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300)
        ]);

        $externo = $this->signInAsUserExterno('contabil', $preRegistroCpf->preRegistro->contabil);
        $this->get(route('externo.preregistro.anexo.download', ['id' => 1, 'preRegistro' => 1]))->assertOk();
    }

    /** @test */
    public function owner_cannot_delete_boleto_by_contabilidade()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300)
        ]);

        $externo = $this->signInAsUserExterno('contabil', $preRegistroCpf->preRegistro->contabil);
        $this->delete(route('externo.preregistro.anexo.excluir', ['id' => 1, 'preRegistro' => 1]))->assertStatus(401);
    }

    /** @test */
    public function log_is_generated_when_download_boleto_by_contabilidade()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300)
        ]);

        $externo = $this->signInAsUserExterno('contabil', $preRegistroCpf->preRegistro->contabil);
        $this->get(route('externo.preregistro.anexo.download', ['id' => 1, 'preRegistro' => 1]))->assertOk();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Foi realizado o download do boleto com ID 1 do pré-registro com ID 1.';
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
            'pre_registro_id' => $pr->id
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
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()->id
        ])->makeHidden(['pre_registro_id', 'created_at', 'updated_at', 'id']);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        Anexo::find(1)->delete();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $pr = PreRegistro::first();
        $dados = Arr::except($dados, ['final', 'created_at', 'updated_at', 'deleted_at', 'pergunta']);

        $arrayFinal = array_diff(array_keys($dados), array_keys(json_decode($pr->campos_espelho, true)));
        $this->assertEquals($arrayFinal, array());
        $this->assertEquals(json_decode($pr->campos_editados, true)['path'], 2);
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function view_label_novo_anexo_when_user_change_files()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        factory('App\Anexo', 2)->states('pre_registro')->create();

        $preRegistroCpf->preRegistro->update([
            'status' => PreRegistro::STATUS_ANALISE_CORRECAO,
            'campos_editados' => json_encode(['path' => '2'], JSON_FORCE_OBJECT)
        ]);
        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeText('Novos anexos')
        ->assertSeeText('Novo anexo');
    }

    /** @test */
    public function can_upload_file_after_approved()
    {
        Storage::fake();

        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(100)
        ])
        ->assertRedirect(route('preregistro.view', $preRegistroCpf->preRegistro->id));

        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'boleto_aprovado_' . $preRegistroCpf->preRegistro->id,
            'pre_registro_id' => $preRegistroCpf->preRegistro->id
        ]);

        $anexos = $preRegistroCpf->preRegistro->anexos;
        Storage::disk('local')->assertExists($anexos->get(0)->path);
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
            'file' => null
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
            'file' => UploadedFile::fake()->create('random2.pdf')->size(2049)
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
            'file' => UploadedFile::fake()->create('random2.png')->size(300)
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
            'file' => 'random2.pdf'
        ])
        ->assertSessionHasErrors([
            'file'
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
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300)
        ])
        ->assertSessionHas([
            'message' => '<i class="icon fas fa-times"></i> O pré-registro precisa estar aprovado para anexar documento.',
            'class' => 'alert-danger'
        ]);
    }

    /** @test */
    public function log_is_generated_when_anexo_doc_created()
    {
        Storage::fake('local');
        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300)
        ]);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') anexou o documento "random2.pdf" do tipo boleto *pré-registro* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function view_label_anexo_doc()
    {
        Storage::fake('local');
        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300)
        ]);

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSee('<span class="font-weight-bolder">Boleto:</span>')
        ->assertSee('<u>1 - boleto_aprovado_1</u>');
    }

    /** @test */
    public function log_is_not_generated_when_download_boleto()
    {
        Storage::fake('local');

        $user = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()->id
        ]);

        $this->post(route('preregistro.upload.doc', $preRegistroCpf->preRegistro->id), [
            'file' => UploadedFile::fake()->create('random2.pdf')->size(300)
        ]);

        $this->get(route('preregistro.anexo.download', ['idPreRegistro' => 1, 'id' => 1]))->assertOk();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Foi realizado o download do boleto com ID 1 do pré-registro com ID 1.';
        $this->assertStringNotContainsString($txt, $log);
    }
}
