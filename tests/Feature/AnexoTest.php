<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\PreRegistro;
use App\Anexo;

class AnexoTest extends TestCase
{
    use RefreshDatabase;

    // /** 
    //  * =======================================================================================================
    //  * TESTES PRE-REGISTRO
    //  * =======================================================================================================
    //  */

    /** @test */
    public function view_files()
    {
        Storage::fake('local');
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

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $anexos
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSee(Anexo::find(2)->nome_original);
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
    public function can_upload_up_to_10_anexos_if_pf_by_ajax()
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

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random_11.jpg')->size(10)]
        ])->assertStatus(200);

        $this->assertEquals(Anexo::count(), 10);
    }

    /** @test */
    public function can_upload_up_to_15_anexos_if_pj_by_ajax()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());
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
    }

    /** @test */
    public function can_upload_up_to_15_anexos_with_15_files_if_pj_by_ajax()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());
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
    public function cannot_create_if_pj_and_more_than_15_anexos_by_ajax()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());
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

        $this->assertSoftDeleted('anexos', [
            'pre_registro_id' => 1,
            'nome_original' => 'random.pdf'
        ]);

        Storage::disk('local')->assertMissing($caminho);
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

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());
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

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->states('pj')->create());
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
}
