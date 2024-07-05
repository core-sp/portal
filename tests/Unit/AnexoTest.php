<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Anexo;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AnexoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** 
     * =======================================================================================================
     * TESTES MODEL
     * =======================================================================================================
     */

    /** @test */
    public function pre_registro()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ]);

        $this->assertEquals(1, Anexo::with('preRegistro')->count());
    }

    /** @test */
    public function campos_pre_registro()
    {
        $this->assertEquals([
            'path',
        ], Anexo::camposPreRegistro());
    }

    /** @test */
    public function tipos_docs_atendente_pre_registro()
    {
        $this->assertEquals([
            'boleto',
        ], Anexo::tiposDocsAtendentePreRegistro());
    }

    /** @test */
    public function criar_final()
    {
        Storage::fake('local');

        $pr = factory('App\PreRegistroCpf')->create();
        Anexo::first()->delete();

        // um arquivo
        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(300),
        ];

        $this->assertEquals(Anexo::class, get_class(Anexo::criarFinal('path', $anexos, $pr->preRegistro)));
        $this->assertDatabaseHas('anexos', ['extensao' => 'jpeg']);

        // mais de um arquivo
        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(300),
            UploadedFile::fake()->image('random1.png')->size(400),
            UploadedFile::fake()->create('random2.pdf')->size(100),
        ];

        $this->assertEquals(Anexo::class, get_class(Anexo::criarFinal('path', $anexos, $pr->preRegistro)));
        $this->assertDatabaseHas('anexos', ['extensao' => 'zip']);

        // alcançou limite de anexos
        $total = Anexo::TOTAL_PF_PRE_REGISTRO - $pr->preRegistro->anexos->count();
        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(300),
        ];
        for ($i=1; $i <= $total; $i++)
            $this->assertEquals(Anexo::class, get_class(Anexo::criarFinal('path', $anexos, $pr->preRegistro)));
        
        $this->assertEquals(null, Anexo::criarFinal('path', $anexos, $pr->preRegistro));
        $this->assertEquals(Anexo::TOTAL_PF_PRE_REGISTRO, Anexo::count());
    }

    /** @test */
    public function armazenar()
    {
        Storage::fake('local');

        $pr_pf = factory('App\PreRegistroCpf')->create();
        Anexo::first()->delete();

        // um arquivo PF
        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(300),
        ];

        $this->assertEquals('array', gettype(Anexo::armazenar(1, $anexos, $pr_pf->preRegistro->id, $pr_pf->preRegistro->userExterno->isPessoaFisica())));

        $pr_pj = factory('App\PreRegistroCnpj')->create();
        Anexo::first()->delete();

        // um arquivo PJ
        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(300),
        ];

        $this->assertEquals('array', gettype(Anexo::armazenar(1, $anexos, $pr_pj->preRegistro->id, $pr_pj->preRegistro->userExterno->isPessoaFisica())));

        // limite alcançado arquivo PF
        $total = Anexo::TOTAL_PF_PRE_REGISTRO - 1;
        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(300),
        ];
        for ($i=1; $i <= $total; $i++)    
            $this->assertEquals('array', 
            gettype(Anexo::armazenar(count(Storage::disk('local')->allFiles('userExterno/pre_registros/1')), $anexos, $pr_pf->preRegistro->id, $pr_pf->preRegistro->userExterno->isPessoaFisica())));

        $this->assertEquals(null, 
        Anexo::armazenar(count(Storage::disk('local')->allFiles('userExterno/pre_registros/1')), $anexos, $pr_pf->preRegistro->id, $pr_pf->preRegistro->userExterno->isPessoaFisica()));

        $this->assertEquals(Anexo::TOTAL_PF_PRE_REGISTRO, count(Storage::disk('local')->allFiles('userExterno/pre_registros/1')));

        // limite alcançado arquivo PJ
        $total = Anexo::TOTAL_PJ_PRE_REGISTRO - 1;
        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(300),
        ];
        for ($i=1; $i <= $total; $i++)    
            $this->assertEquals('array', 
            gettype(Anexo::armazenar(count(Storage::disk('local')->allFiles('userExterno/pre_registros/2')), $anexos, $pr_pj->preRegistro->id, $pr_pj->preRegistro->userExterno->isPessoaFisica())));

        $this->assertEquals(null, 
        Anexo::armazenar(count(Storage::disk('local')->allFiles('userExterno/pre_registros/2')), $anexos, $pr_pj->preRegistro->id, $pr_pj->preRegistro->userExterno->isPessoaFisica()));

        $this->assertEquals(Anexo::TOTAL_PJ_PRE_REGISTRO, count(Storage::disk('local')->allFiles('userExterno/pre_registros/2')));
    }

    /** @test */
    public function armazenar_doc()
    {
        Storage::fake('local');

        $pr_pf = factory('App\PreRegistroCpf')->create();
        Anexo::first()->delete();

        // um arquivo novo
        $anexo = UploadedFile::fake()->image('random.jpg')->size(300);

        $doc_um = Anexo::armazenarDoc($pr_pf->preRegistro->id, $anexo, 'boleto');
        $this->assertEquals('boleto', $doc_um['tipo']);
        $doc_um['pre_registro_id'] = $pr_pf->preRegistro->id;
        Anexo::create($doc_um);

        // um arquivo substituido
        $anexo = UploadedFile::fake()->image('random.jpg')->size(300);

        $doc = Anexo::armazenarDoc($pr_pf->preRegistro->id, $anexo, 'boleto');
        $this->assertEquals('boleto', $doc['tipo']);
        $doc['pre_registro_id'] = $pr_pf->preRegistro->id;
        Anexo::create($doc);
        Storage::disk('local')->assertMissing($doc_um['path']);
    }

    /** @test */
    public function get_obrigatorios_pre_registro()
    {
        $pr_pf = factory('App\PreRegistroCpf')->create();

        $this->assertEquals([
            "Comprovante de identidade",
            "CPF",
            "Comprovante de Residência",
            "Certidão de quitação eleitoral",
            "Cerificado de reservista ou dispensa",
        ], Anexo::first()->getObrigatoriosPreRegistro());

        $pr_pj = factory('App\PreRegistroCnpj')->create();
        
        // método acima dá erro em pj
        foreach([
            'Comprovante de identidade',
            'CPF',
            'Comprovante de Residência',
            'Certidão de quitação eleitoral',
            // 'Cerificado de reservista ou dispensa',
            'Comprovante de inscrição CNPJ',
            'Contrato Social',
            'Declaração Termo de indicação RT ou Procuração'
        ] as $tipo)
        $this->assertEquals(true, in_array($tipo, Anexo::find(2)->getObrigatoriosPreRegistro()));
    }

    /** @test */
    public function get_opcoes_pre_registro()
    {
        $pr_pf = factory('App\PreRegistroCpf')->create();
        $pr_pf->update(['nacionalidade' => 'CHILENA', 'sexo' => 'F']);

        $this->assertEquals([
            "Comprovante de identidade",
            "CPF",
            "Comprovante de Residência",
            // "Certidão de quitação eleitoral",
            // "Cerificado de reservista ou dispensa",
        ], Anexo::first()->getObrigatoriosPreRegistro());

        $pr_pj = factory('App\PreRegistroCnpj')->create();
        $pr_pj->socios()->detach(1);
        
        // método acima dá erro em pj
        foreach([
            // 'Comprovante de identidade',
            // 'CPF',
            // 'Comprovante de Residência',
            // 'Certidão de quitação eleitoral',
            // 'Cerificado de reservista ou dispensa',
            'Comprovante de inscrição CNPJ',
            'Contrato Social',
            'Declaração Termo de indicação RT ou Procuração'
        ] as $tipo)
        $this->assertEquals(true, in_array($tipo, Anexo::find(2)->getObrigatoriosPreRegistro()));
    }

    /** @test */
    public function anexado_pelo_atendente()
    {
        $pr_pf = factory('App\PreRegistroCpf')->create();

        $this->assertEquals(false, Anexo::first()->anexadoPeloAtendente());

        Anexo::first()->update(['tipo' => 'boleto']);

        $this->assertEquals(true, Anexo::first()->anexadoPeloAtendente());
    }

    /** @test */
    public function sem_soft_delete()
    {
        $user = factory('App\Anexo')->create([
            'path' => 'teste/teste/img.pdf'
        ]);

        $this->assertEquals(1, Anexo::count());
        $this->assertDatabaseHas('anexos', ['id' => 1]);
        $this->assertDatabaseMissing('anexos', ['deleted_at' => null]);

        $user->delete();

        $this->assertEquals(0, Anexo::count());
        $this->assertDatabaseMissing('anexos', ['id' => 1]);
        $this->assertDatabaseMissing('anexos', ['deleted_at' => null]);

        $this->expectException(\Exception::class);
        Anexo::withTrashed()->first();
    }
}
