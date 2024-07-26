<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\PreRegistro;
use App\Anexo;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Repositories\GerentiRepositoryMock;
use App\Services\PreRegistroService;
use App\Services\PreRegistroAdminSubService;
use App\Services\MediadorService;

class PreRegistroTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    const CPF_GERENTI = '86294373085';
    const CNPJ_GERENTI = '11748345000144';

    /** 
     * =======================================================================================================
     * TESTES MODEL
     * =======================================================================================================
     */

    /** @test */
    public function campos_pre_registro()
    {
        $this->assertEquals([
            'segmento',
            'idregional',
            'tipo_telefone',
            'telefone',
            'opcional_celular',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
        ], PreRegistro::camposPreRegistro());
    }

    /** @test */
    public function user_externo()
    {
        $dados = factory('App\PreRegistroCpf')->create();
        factory('App\PreRegistroCpf')->create();

        $this->assertEquals(1, PreRegistro::find(1)->userExterno()->count());
        $this->assertEquals(1, PreRegistro::find(2)->userExterno()->count());

        PreRegistro::find(1)->userExterno()->delete();
        $this->assertNotEquals(null, PreRegistro::find(1)->userExterno()->first()->deleted_at);
        $this->assertEquals(1, PreRegistro::find(1)->userExterno()->count());
    }

    /** @test */
    public function regional()
    {
        $dados = factory('App\PreRegistroCpf')->create();
        factory('App\PreRegistroCpf')->create();

        $this->assertEquals(1, PreRegistro::find(1)->regional()->count());
        $this->assertEquals(1, PreRegistro::find(2)->regional()->count());
    }

    /** @test */
    public function contabil()
    {
        $dados = factory('App\PreRegistroCpf')->create();
        factory('App\PreRegistroCpf')->create();

        $this->assertEquals(1, PreRegistro::find(1)->contabil()->count());
        $this->assertEquals(1, PreRegistro::find(2)->contabil()->count());

        PreRegistro::find(1)->contabil()->delete();
        $this->assertNotEquals(null, PreRegistro::find(1)->contabil()->first()->deleted_at);
        $this->assertEquals(1, PreRegistro::find(1)->contabil()->count());
    }

    /** @test */
    public function user()
    {
        $dados = factory('App\PreRegistroCpf')->create();
        factory('App\PreRegistroCpf')->create();

        $this->assertEquals(1, PreRegistro::find(1)->user()->count());
        $this->assertEquals(1, PreRegistro::find(2)->user()->count());

        PreRegistro::find(1)->user()->delete();
        $this->assertNotEquals(null, PreRegistro::find(1)->user()->first()->deleted_at);
        $this->assertEquals(1, PreRegistro::find(1)->user()->count());
    }

    /** @test */
    public function pessoa_fisica()
    {
        $dados = factory('App\PreRegistroCpf')->create();
        factory('App\PreRegistroCpf')->create();

        $this->assertEquals(1, PreRegistro::find(1)->pessoaFisica()->get()->count());
        $this->assertEquals(1, PreRegistro::find(2)->pessoaFisica()->get()->count());

        PreRegistro::find(1)->pessoaFisica()->delete();
        $this->assertNotEquals(null, PreRegistro::find(1)->pessoaFisica()->first()->deleted_at);
        $this->assertEquals(1, PreRegistro::find(1)->pessoaFisica()->first()->count());
    }

    /** @test */
    public function pessoa_juridica()
    {
        $dados = factory('App\PreRegistroCnpj')->create();
        factory('App\PreRegistroCnpj')->create();

        $this->assertEquals(1, PreRegistro::find(1)->pessoaJuridica()->get()->count());
        $this->assertEquals(1, PreRegistro::find(2)->pessoaJuridica()->get()->count());

        PreRegistro::find(1)->pessoaJuridica()->delete();
        $this->assertNotEquals(null, PreRegistro::find(1)->pessoaJuridica()->first()->deleted_at);
        $this->assertEquals(1, PreRegistro::find(1)->pessoaJuridica()->first()->count());
    }

    /** @test */
    public function anexos()
    {
        $dados = factory('App\PreRegistroCpf')->create();
        factory('App\PreRegistroCpf')->create();

        $this->assertEquals(1, PreRegistro::find(1)->anexos()->get()->count());
        $this->assertEquals(1, PreRegistro::find(2)->anexos()->get()->count());
    }

    /** @test */
    public function get_status()
    {
        $this->assertEquals([
            PreRegistro::STATUS_CORRECAO,
            PreRegistro::STATUS_APROVADO,
            PreRegistro::STATUS_ANALISE_CORRECAO,
            PreRegistro::STATUS_ANALISE_INICIAL,
            PreRegistro::STATUS_NEGADO,
            PreRegistro::STATUS_CRIADO,
        ], PreRegistro::getStatus());
    }

    /** @test */
    public function get_legenda_status()
    {
        $txt = PreRegistro::getLegendaStatus();

        $this->assertStringContainsString(PreRegistro::STATUS_CORRECAO, $txt);
        $this->assertStringContainsString(PreRegistro::STATUS_APROVADO, $txt);
        $this->assertStringContainsString(PreRegistro::STATUS_ANALISE_CORRECAO, $txt);
        $this->assertStringContainsString(PreRegistro::STATUS_ANALISE_INICIAL, $txt);
        $this->assertStringContainsString(PreRegistro::STATUS_NEGADO, $txt);
        $this->assertStringContainsString(PreRegistro::STATUS_CRIADO, $txt);
    }

    /** @test */
    public function possui_contabil()
    {
        $dados = factory('App\PreRegistroCpf')->create();
        $dados_2 = factory('App\PreRegistroCpf')->create();
        $dados_2->preRegistro->update(['contabil_id' => null]);

        $this->assertEquals(true, $dados->preRegistro->possuiContabil());
        $this->assertEquals(false, $dados_2->preRegistro->possuiContabil());
    }

    /** @test */
    public function gerenciado_por_contabil()
    {
        $cont = factory('App\Contabil')->create();
        $dados = factory('App\PreRegistroCpf')->create();

        $this->assertEquals(true, $dados->preRegistro->gerenciadoPorContabil());

        $dados->preRegistro->update(['contabil_id' => null]);
        $this->assertEquals(false, $dados->preRegistro->gerenciadoPorContabil());

        $dados->preRegistro->update(['contabil_id' => 1]);
        $dados->preRegistro->contabil->update(['ativo' => 0]);
        $this->assertEquals(false, $dados->preRegistro->gerenciadoPorContabil());
    }

    /** @test */
    public function excluir_anexos()
    {
        Storage::fake('local');

        $pr_pf = factory('App\PreRegistroCpf')->create();
        factory('App\PreRegistroCpf')->create();
        Anexo::first()->delete();

        // um arquivo PF
        $anexos = [
            UploadedFile::fake()->image('random.jpg')->size(300),
        ];

        $resp = Anexo::criarFinal('path', $anexos, $pr_pf->preRegistro);
        $this->assertEquals(1, $pr_pf->preRegistro->anexos()->count());
        Storage::disk('local')->assertExists($resp['path']);

        $pr_pf->preRegistro->excluirAnexos();
        $this->assertEquals(0, $pr_pf->preRegistro->anexos()->count());
        $this->assertEquals(1, PreRegistro::find(2)->anexos()->count());
        Storage::disk('local')->assertMissing($resp['path']);
    }

    /** @test */
    public function relacionar_contabil()
    {
        $dados = factory('App\PreRegistroCpf')->create();
        $contabil = $dados->preRegistro->contabil;
        $contabil2 = factory('App\Contabil')->create();

        $this->assertEquals(true, $dados->preRegistro->relacionarContabil($contabil2->id));
        $this->assertEquals(2, $dados->preRegistro->contabil_id);
    }

    /** @test */
    public function get_historico_array()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        $contabil = factory('App\Contabil')->create();

        $this->assertEquals('0', $dados->getHistoricoArray()['tentativas']);
        $dados->relacionarContabil($contabil->id);
        $this->assertEquals('1', $dados->getHistoricoArray()['tentativas']);
        $dados->update(['historico_contabil' => null]);
        $this->assertEquals([], $dados->getHistoricoArray());
    }

    /** @test */
    public function set_historico()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals('0', $dados->getHistoricoArray()['tentativas']);
        $this->assertEquals(json_encode([
            'tentativas' => 1,
            'update' => now()->format('Y-m-d H:i:s')
        ]), $dados->setHistorico());

        $dados->update(['historico_contabil' => $dados->setHistorico()]);
        $this->assertEquals(json_encode([
            'tentativas' => 1,
            'update' => now()->format('Y-m-d H:i:s')
        ]), $dados->setHistorico());
    }

    /** @test */
    public function get_historico_can_edit()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals('0', $dados->getHistoricoArray()['tentativas']);
        $this->assertEquals(true, $dados->getHistoricoCanEdit());

        // alcançou o limite de tentativas
        $dados->update(['historico_contabil' => $dados->setHistorico()]);
        $this->assertEquals(false, $dados->getHistoricoCanEdit());

        // alcançou o limite de tentativas, mas já passou do prazo de espera
        $temp = json_decode($dados->setHistorico(), true);
        $temp['update'] = now()->subDays(2)->format('Y-m-d H:i:s');
        $dados->update(['historico_contabil' => json_encode($temp)]);

        $this->assertEquals(true, $dados->getHistoricoCanEdit());
    }

    /** @test */
    public function get_next_update_historico()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        $data = Carbon::createFromFormat('Y-m-d H:i:s', json_decode($dados->historico_contabil, true)['update']);
        $data = formataData($data->addDays(PreRegistro::TOTAL_HIST_DIAS_UPDATE));

        $this->assertEquals($data, $dados->getNextUpdateHistorico());
    }

    /** @test */
    public function get_endereco()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals([
            'cep' => $dados->cep, 
            'logradouro' => $dados->logradouro, 
            'numero' => $dados->numero, 
            'complemento' => $dados->complemento, 
            'bairro' => $dados->bairro, 
            'cidade' => $dados->cidade, 
            'uf' => $dados->uf, 
        ], $dados->getEndereco());
    }

    /** @test */
    public function get_label_status()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        $this->assertEquals('-info', $dados->getLabelStatus());

        $dados->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);
        $this->assertEquals('-primary', $dados->getLabelStatus());

        $dados->update(['status' => PreRegistro::STATUS_CORRECAO]);
        $this->assertEquals('-secondary', $dados->getLabelStatus());

        $dados->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $this->assertEquals('-warning', $dados->getLabelStatus());

        $dados->update(['status' => PreRegistro::STATUS_APROVADO]);
        $this->assertEquals('-success', $dados->getLabelStatus());

        $dados->update(['status' => PreRegistro::STATUS_NEGADO]);
        $this->assertEquals('-danger', $dados->getLabelStatus());

        foreach([
            PreRegistro::STATUS_CRIADO => '-info',
            PreRegistro::STATUS_ANALISE_INICIAL => '-primary',
            PreRegistro::STATUS_CORRECAO => '-secondary',
            PreRegistro::STATUS_ANALISE_CORRECAO => '-warning',
            PreRegistro::STATUS_APROVADO => '-success',
            PreRegistro::STATUS_NEGADO => '-danger',
        ] as $key => $status)
            $this->assertEquals($status, $dados->getLabelStatus($key));
    }

    /** @test */
    public function get_label_status_user()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        $this->assertStringContainsString('<span class="badge badge-secondary">' . $dados->status . '</span>', $dados->getLabelStatusUser(true));
        $this->assertStringContainsString('está sendo elaborado', $dados->getLabelStatusUser());

        $dados->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);
        $this->assertStringContainsString('<span class="badge badge-primary">' . $dados->status . '</span>', $dados->getLabelStatusUser(true));
        $this->assertStringContainsString('aguardando a análise', $dados->getLabelStatusUser());

        $dados->update(['status' => PreRegistro::STATUS_CORRECAO]);
        $this->assertStringContainsString('<span class="badge badge-warning">' . $dados->status . '</span>', $dados->getLabelStatusUser(true));
        $this->assertStringContainsString('possui correções', $dados->getLabelStatusUser());

        $dados->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $this->assertStringContainsString('<span class="badge badge-info">' . $dados->status . '</span>', $dados->getLabelStatusUser(true));
        $this->assertStringContainsString('correção pelo atendente', $dados->getLabelStatusUser());

        $dados->update(['status' => PreRegistro::STATUS_APROVADO]);
        $this->assertStringContainsString('<span class="badge badge-success">' . $dados->status . '</span>', $dados->getLabelStatusUser(true));
        $this->assertStringContainsString('aprovado pelo atendente', $dados->getLabelStatusUser());

        $dados->update(['status' => PreRegistro::STATUS_NEGADO]);
        $this->assertStringContainsString('<span class="badge badge-danger">' . $dados->status . '</span>', $dados->getLabelStatusUser(true));
        $this->assertStringContainsString('foi negado pelo atendente', $dados->getLabelStatusUser());
    }

    /** @test */
    public function get_docs_atendimento()
    {
        Storage::fake('local');

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ])->preRegistro;

        $anexo = UploadedFile::fake()->image('random.pdf')->size(300);
        $doc_um = Anexo::armazenarDoc(1, $anexo, 'boleto');
        $dados->anexos()->create($doc_um);

        $this->assertEquals([
            'path' => $doc_um['path'],
            'nome_original' => $doc_um['nome_original'],
            'extensao' => $doc_um['extensao'],
            'tipo' => $doc_um['tipo'],
        ], $dados->getDocsAtendimento()->first()->only(['path', 'nome_original', 'extensao', 'tipo']));

        // Status Negado não recupera docs
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $anexo = UploadedFile::fake()->image('random2.pdf')->size(300);
        $doc_um = Anexo::armazenarDoc(2, $anexo, 'boleto');
        $dados->anexos()->create($doc_um);

        $this->assertEquals(null, $dados->getDocsAtendimento()->first());
    }

    /** @test */
    public function get_tipo_telefone()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals(['CELULAR'], $dados->getTipoTelefone());
        $this->assertEquals(['tipo_telefone' => 'CELULAR', 'tipo_telefone_1' => null], $dados->getTipoTelefone(true));

        $dados->update(['tipo_telefone' => 'CELULAR;FIXO - COMERCIAL']);
        $this->assertEquals(['CELULAR', 'FIXO - COMERCIAL'], $dados->getTipoTelefone());
        $this->assertEquals(['tipo_telefone' => 'CELULAR', 'tipo_telefone_1' => 'FIXO - COMERCIAL'], $dados->getTipoTelefone(true));

        $dados->update(['tipo_telefone' => ';']);
        $this->assertEquals([], $dados->getTipoTelefone());
        $this->assertEquals(['tipo_telefone' => null, 'tipo_telefone_1' => null], $dados->getTipoTelefone(true));

        $dados->update(['tipo_telefone' => null]);
        $this->assertEquals(['tipo_telefone' => null, 'tipo_telefone_1' => null], $dados->getTipoTelefone(true));
    }

    /** @test */
    public function tipo_telefone_celular()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals(true, $dados->tipoTelefoneCelular());

        $dados->update(['tipo_telefone' => 'CELULAR;FIXO - COMERCIAL']);
        $this->assertEquals(true, $dados->tipoTelefoneCelular());

        $dados->update(['tipo_telefone' => ';CELULAR']);
        $this->assertEquals(false, $dados->tipoTelefoneCelular());

        $dados->update(['tipo_telefone' => ';']);
        $this->assertEquals(false, $dados->tipoTelefoneCelular());

        $dados->update(['tipo_telefone' => null]);
        $this->assertEquals(false, $dados->tipoTelefoneCelular());
    }

    /** @test */
    public function tipo_telefone_opcional_celular()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals(false, $dados->tipoTelefoneOpcionalCelular());

        $dados->update(['tipo_telefone' => 'CELULAR;FIXO - COMERCIAL']);
        $this->assertEquals(false, $dados->tipoTelefoneOpcionalCelular());

        $dados->update(['tipo_telefone' => ';CELULAR']);
        $this->assertEquals(true, $dados->tipoTelefoneOpcionalCelular());

        $dados->update(['tipo_telefone' => ';']);
        $this->assertEquals(false, $dados->tipoTelefoneOpcionalCelular());

        $dados->update(['tipo_telefone' => null]);
        $this->assertEquals(false, $dados->tipoTelefoneOpcionalCelular());
    }

    /** @test */
    public function get_telefone()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals(['(11) 00000-0000'], $dados->getTelefone());
        $this->assertEquals(['telefone' => '(11) 00000-0000', 'telefone_1' => null], $dados->getTelefone(true));

        $dados->update(['telefone' => ';(11) 00000-0000']);
        $this->assertEquals([1 => '(11) 00000-0000'], $dados->getTelefone());
        $this->assertEquals(['telefone' => null, 'telefone_1' => '(11) 00000-0000'], $dados->getTelefone(true));

        $dados->update(['telefone' => '(11) 00000-1234;(11) 00000-0000']);
        $this->assertEquals(['(11) 00000-1234', '(11) 00000-0000'], $dados->getTelefone());
        $this->assertEquals(['telefone' => '(11) 00000-1234', 'telefone_1' => '(11) 00000-0000'], $dados->getTelefone(true));

        $dados->update(['telefone' => ';']);
        $this->assertEquals([], $dados->getTelefone());
        $this->assertEquals(['telefone' => null, 'telefone_1' => null], $dados->getTelefone(true));

        $dados->update(['telefone' => null]);
        $this->assertEquals([], $dados->getTelefone());
        $this->assertEquals(['telefone' => null, 'telefone_1' => null], $dados->getTelefone(true));
    }

    /** @test */
    public function get_opcional_celular()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        $this->assertEquals([
            0 => ['TELEGRAM']
        ], $dados->getOpcionalCelular());
        $this->assertEquals([
            'opcional_celular' => ['TELEGRAM'],
            'opcional_celular_1' => []
        ], $dados->getOpcionalCelular(true));
        $this->assertEquals([
            'opcional_celular' => 'TELEGRAM',
            'opcional_celular_1' => null
        ], $dados->getOpcionalCelular(true, null));
        $this->assertEquals([
            0 => 'TELEGRAM'
        ], $dados->getOpcionalCelular(false, null));


        $dados->update(['opcional_celular' => ';SMS,WHATSAPP,TELEGRAM']);
        $this->assertEquals([
            1 => ['SMS', 'WHATSAPP', 'TELEGRAM']
        ], $dados->getOpcionalCelular());
        $this->assertEquals([
            'opcional_celular' => [],
            'opcional_celular_1' => ['SMS', 'WHATSAPP', 'TELEGRAM']
        ], $dados->getOpcionalCelular(true));
        $this->assertEquals([
            'opcional_celular' => null,
            'opcional_celular_1' => 'SMS,WHATSAPP,TELEGRAM'
        ], $dados->getOpcionalCelular(true, null));
        $this->assertEquals([
            1 => 'SMS,WHATSAPP,TELEGRAM'
        ], $dados->getOpcionalCelular(false, null));


        $dados->update(['opcional_celular' => 'TELEGRAM,SMS;SMS,WHATSAPP,TELEGRAM']);
        $this->assertEquals([
            0 => ['TELEGRAM', 'SMS'],
            1 => ['SMS', 'WHATSAPP', 'TELEGRAM']
        ], $dados->getOpcionalCelular());
        $this->assertEquals([
            'opcional_celular' => ['TELEGRAM', 'SMS'],
            'opcional_celular_1' => ['SMS', 'WHATSAPP', 'TELEGRAM']
        ], $dados->getOpcionalCelular(true));
        $this->assertEquals([
            'opcional_celular' => 'TELEGRAM,SMS',
            'opcional_celular_1' => 'SMS,WHATSAPP,TELEGRAM'
        ], $dados->getOpcionalCelular(true, null));
        $this->assertEquals([
            0 => 'TELEGRAM,SMS',
            1 => 'SMS,WHATSAPP,TELEGRAM'
        ], $dados->getOpcionalCelular(false, null));


        $dados->update(['opcional_celular' => ';']);
        $this->assertEquals([], $dados->getOpcionalCelular());
        $this->assertEquals([
            'opcional_celular' => [],
            'opcional_celular_1' => []
        ], $dados->getOpcionalCelular(true));
        $this->assertEquals([
            'opcional_celular' => null,
            'opcional_celular_1' => null
        ], $dados->getOpcionalCelular(true, null));
        $this->assertEquals([], $dados->getOpcionalCelular(false, null));


        $dados->update(['opcional_celular' => null]);
        $this->assertEquals([], $dados->getOpcionalCelular());
        $this->assertEquals([
            'opcional_celular' => [],
            'opcional_celular_1' => []
        ], $dados->getOpcionalCelular(true));
        $this->assertEquals([
            'opcional_celular' => null,
            'opcional_celular_1' => null
        ], $dados->getOpcionalCelular(true, null));
        $this->assertEquals([], $dados->getOpcionalCelular(false, null));
    }

    /** @test */
    public function get_justificativa_array()
    {
        $dados = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create()
        ])->preRegistro;
        
        $array = array_merge(array_keys($dados->pessoaFisica->arrayValidacaoInputs()), array_keys($dados->arrayValidacaoInputs()));
        $this->assertEquals($array, array_keys($dados->getJustificativaArray()));

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;
        
        $this->assertEquals(['negado'], array_keys($dados->getJustificativaArray()));

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        $this->assertEquals([], $dados->getJustificativaArray());
    }

    /** @test */
    public function get_justificativa_por_campo()
    {
        $dados = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create()
        ])->preRegistro;
        
        $array = array_merge(array_keys($dados->pessoaFisica->arrayValidacaoInputs()), array_keys($dados->arrayValidacaoInputs()));
        foreach($array as $campo)
            $this->assertNotEquals(null, $dados->getJustificativaPorCampo($campo));

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;
        
        $this->assertNotEquals(null, $dados->getJustificativaPorCampo('negado'));
        $this->assertNotEquals('', $dados->getJustificativaPorCampo('negado'));
        foreach($array as $campo)
            $this->assertEquals(null, $dados->getJustificativaPorCampo($campo));

        $this->assertEquals(null, $dados->getJustificativaPorCampo('teste_campo_nao_existe'));
    }

    /** @test */
    public function get_justificativa_por_campo_data()
    {
        $temp = now()->subDay();

        $dados = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create()
        ])->preRegistro;
        
        foreach(['segmento', 'idregional', 'cep'] as $campo)
            $this->assertNotEquals(null, $dados->getJustificativaPorCampoData($campo, $temp->format('Y-m-d H:i:s')));

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        foreach(['segmento', 'idregional', 'cep'] as $campo)
            $this->assertEquals(null, $dados->getJustificativaPorCampoData($campo, $temp->format('Y-m-d H:i:s')));

        $this->expectException(\Exception::class);
        $this->assertEquals(null, $dados->getJustificativaPorCampoData('cep', $temp->format('Y-m-d')));
    }

    /** @test */
    public function get_confere_anexos_array()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao', 'anexos_ok_pf')->create()
        ])->preRegistro;
        
        $this->assertEquals([
            'Comprovante de identidade',
            'CPF',
            'Comprovante de Residência',
            'Certidão de quitação eleitoral',
            'Cerificado de reservista ou dispensa',
        ], array_keys($dados->getConfereAnexosArray()));

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals([], $dados->getConfereAnexosArray());
    }

    /** @test */
    public function get_justificativa_negado()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;
        
        $this->assertNotEquals(null, $dados->getJustificativaNegado());
        $this->assertNotEquals('', $dados->getJustificativaNegado());
        $this->assertNotEquals([], $dados->getJustificativaNegado());

        $dados = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create()
        ])->preRegistro;
        
        $this->assertEquals(null, $dados->getJustificativaNegado());
    }

    /** @test */
    public function criado()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        $this->assertEquals(true, $dados->criado());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $this->assertEquals(false, $dados->criado());
    }

    /** @test */
    public function is_finalizado()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $this->assertEquals(true, $dados->isFinalizado());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ])->preRegistro;

        $this->assertEquals(true, $dados->isFinalizado());

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        $this->assertEquals(false, $dados->isFinalizado());
    }

    /** @test */
    public function negado()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $this->assertEquals(true, $dados->negado());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ])->preRegistro;

        $this->assertEquals(false, $dados->negado());

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        $this->assertEquals(false, $dados->negado());
    }

    /** @test */
    public function is_aprovado()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $this->assertEquals(false, $dados->isAprovado());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ])->preRegistro;

        $this->assertEquals(true, $dados->isAprovado());

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        $this->assertEquals(false, $dados->isAprovado());
    }

    /** @test */
    public function correcao_enviada()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $this->assertEquals(false, $dados->correcaoEnviada());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $this->assertEquals(true, $dados->correcaoEnviada());

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        $this->assertEquals(false, $dados->correcaoEnviada());
    }

    /** @test */
    public function correcao_em_analise()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $this->assertEquals(false, $dados->correcaoEmAnalise());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create()
        ])->preRegistro;

        $this->assertEquals(true, $dados->correcaoEmAnalise());

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        $this->assertEquals(false, $dados->correcaoEmAnalise());
    }

    /** @test */
    public function user_pode_corrigir()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $this->assertEquals(false, $dados->userPodeCorrigir());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $this->assertEquals(true, $dados->userPodeCorrigir());

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        $this->assertEquals(false, $dados->userPodeCorrigir());
    }

    /** @test */
    public function user_pode_editar()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $this->assertEquals(false, $dados->userPodeEditar());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $this->assertEquals(true, $dados->userPodeEditar());

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        $this->assertEquals(true, $dados->userPodeEditar());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create()
        ])->preRegistro;

        $this->assertEquals(false, $dados->userPodeEditar());
    }

    /** @test */
    public function atendente_pode_editar()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $this->assertEquals(false, $dados->atendentePodeEditar());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $this->assertEquals(false, $dados->atendentePodeEditar());

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        $this->assertEquals(false, $dados->atendentePodeEditar());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create()
        ])->preRegistro;

        $this->assertEquals(true, $dados->atendentePodeEditar());
    }

    /** @test */
    public function get_codigos_justificados_by_aba()
    {
        $dados = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $this->assertEquals(null, $dados->getCodigosJustificadosByAba($dados->getCodigosCampos()[1]));

        $dados = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $this->assertEquals([
            '3.1' => 'cep',
            '3.2' => 'bairro',
            '3.3' => 'logradouro',
            '3.4' => 'numero',
            '3.5' => 'complemento',
            '3.6' => 'cidade',
            '3.7' => 'uf'
        ], $dados->getCodigosJustificadosByAba($dados->getCodigosCampos()[2]));

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        $this->assertEquals(null, $dados->getCodigosJustificadosByAba($dados->getCodigosCampos()[2]));
    }

    /** @test */
    public function set_historico_justificativas()
    {
        $dados = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $antigo = $dados->historico_justificativas;
        $antigoStatus = $dados->historico_status;
        $this->assertEquals(true, $dados->setHistoricoJustificativas());
        $this->assertNotEquals($antigo, $dados->historico_justificativas);
        $this->assertNotEquals($antigoStatus, $dados->historico_status);

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $antigo = $dados->historico_justificativas;
        $antigoStatus = $dados->historico_status;
        $this->assertEquals(null, $dados->setHistoricoJustificativas());
        $this->assertEquals($antigo, $dados->historico_justificativas);
        $this->assertNotEquals($antigoStatus, $dados->historico_status);

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        $antigoStatus = $dados->historico_status;
        $this->assertEquals(null, $dados->setHistoricoJustificativas());
        $this->assertEquals(null, $dados->historico_justificativas);
        $this->assertNotEquals($antigoStatus, $dados->historico_status);
    }

    /** @test */
    public function get_historico_status()
    {
        $dados = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $this->assertEquals(true, Carbon::hasFormat(array_keys($dados->getHistoricoStatus())[0], 'Y-m-d H:i:s'));
        $this->assertEquals([
            PreRegistro::STATUS_CRIADO,
            PreRegistro::STATUS_ANALISE_INICIAL,
            PreRegistro::STATUS_CORRECAO
        ], array_values($dados->getHistoricoStatus()));

        $dados->setHistoricoJustificativas();
        $this->assertEquals(true, Carbon::hasFormat(array_keys($dados->getHistoricoStatus())[3], 'Y-m-d H:i:s'));
        $this->assertEquals([
            PreRegistro::STATUS_CRIADO,
            PreRegistro::STATUS_ANALISE_INICIAL,
            PreRegistro::STATUS_CORRECAO,
            PreRegistro::STATUS_CORRECAO
        ], array_values($dados->getHistoricoStatus()));

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        $this->assertEquals(true, Carbon::hasFormat(array_keys($dados->getHistoricoStatus())[0], 'Y-m-d H:i:s'));
        $this->assertEquals([
            PreRegistro::STATUS_CRIADO,
        ], array_values($dados->getHistoricoStatus()));
    }

    /** @test */
    public function get_historico_justificativas()
    {
        $dados = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $this->assertEquals(true, Carbon::hasFormat(array_keys($dados->getHistoricoJustificativas())[0], 'Y-m-d H:i:s'));
        $this->assertEquals([
            'segmento',
            'idregional',
            'cep'
        ], array_values(array_values($dados->getHistoricoJustificativas())[0]));

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals([], $dados->getHistoricoJustificativas());
    }

    /** @test */
    public function possui_campos_editados()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals(false, $dados->possuiCamposEditados());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao', 'campos_editados')->create()
        ])->preRegistro;

        $this->assertEquals(true, $dados->possuiCamposEditados());
    }

    /** @test */
    public function get_campos_editados()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals([], $dados->getCamposEditados());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao', 'campos_editados')->create()
        ])->preRegistro;

        $this->assertEquals([], $dados->getCamposEditados());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create()
        ])->preRegistro;

        $this->assertEquals([], $dados->getCamposEditados());

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao', 'campos_editados')->create()
        ])->preRegistro;

        $this->assertEquals([
            'idregional',
            'bairro',
            'numero'
        ], array_keys($dados->getCamposEditados()));
    }

    /** @test */
    public function confere_justificados_submit_pf()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ])->preRegistro;

        $request = array_merge($dados->arrayValidacaoInputs(), $dados->pessoaFisica->arrayValidacaoInputs(), ['path' => $dados->anexos->count()]);

        $this->assertEquals(true, $dados->confereJustificadosSubmit($request));
        $this->assertEquals(null, $dados->campos_editados);
        $this->assertEquals(null, $dados->campos_espelho);

        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $request = array_merge($dados->arrayValidacaoInputs(), $dados->pessoaFisica->arrayValidacaoInputs(), ['path' => $dados->anexos->count()]);

        $this->assertEquals(true, $dados->confereJustificadosSubmit($request));
        $this->assertEquals(null, $dados->campos_editados);
        $this->assertNotEquals(null, $dados->campos_espelho);

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $request = array_merge($dados->arrayValidacaoInputs(), $dados->pessoaFisica->arrayValidacaoInputs(), ['path' => $dados->anexos->count()]);

        $this->assertEquals(true, $dados->confereJustificadosSubmit($request));
        $this->assertNotEquals(null, $dados->campos_editados);
        $this->assertNotEquals(null, $dados->campos_espelho);

        $antigoCEs = $dados->campos_espelho;
        $antigoCEd = $dados->campos_editados;
        $this->assertEquals(false, $dados->confereJustificadosSubmit($request));
        $this->assertNotEquals($antigoCEd, $dados->campos_editados);
        $this->assertEquals($antigoCEs, $dados->campos_espelho);

        $antigoAnexos = json_decode($dados->campos_espelho, true)['path'];
        factory('App\Anexo')->states('pre_registro')->create();
        $request = array_merge($dados->arrayValidacaoInputs(), $dados->pessoaFisica->arrayValidacaoInputs(), ['path' => $dados->fresh()->anexos->count()]);

        $dados->fresh()->confereJustificadosSubmit($request);
        $this->assertNotEquals($antigoAnexos, json_decode($dados->fresh()->campos_espelho, true)['path']);
        $this->assertNotEquals('1,2', json_decode($dados->fresh()->campos_editados, true)['path']);
        $this->assertNotEquals('3', json_decode($dados->fresh()->campos_editados, true)['path']);
        $this->assertEquals('4', json_decode($dados->fresh()->campos_editados, true)['path']);
        $this->assertEquals('3,4', json_decode($dados->fresh()->campos_espelho, true)['path']);
    }

    /** @test */
    public function confere_justificados_submit_pj()
    {
        // PJ - Sócios
        $dados = factory('App\PreRegistroCnpj')->states('rt_socio')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'enviado_correcao')->create()
        ])->preRegistro;

        $request = array_merge($dados->arrayValidacaoInputs(), $dados->pessoaJuridica->arrayValidacaoInputs(), 
        $dados->pessoaJuridica->socios->find(1)->arrayValidacaoInputs(), $dados->pessoaJuridica->socios->find(2)->arrayValidacaoInputs(), 
        $dados->pessoaJuridica->socios->find(3)->arrayValidacaoInputs(), ['path' => $dados->anexos->count(), 'checkRT_socio' => 'on']);

        $this->assertEquals(true, $dados->confereJustificadosSubmit($request));
        $this->assertNotEquals(null, $dados->campos_editados);
        $this->assertEquals(false, isset(json_decode($dados->campos_editados, true)['removidos_socio']));
        $this->assertEquals(true, isset(json_decode($dados->campos_espelho, true)['cpf_cnpj_socio_1']));
        $this->assertEquals(true, isset(json_decode($dados->campos_espelho, true)['cpf_cnpj_socio_2']));
        $this->assertEquals(true, isset(json_decode($dados->campos_espelho, true)['checkRT_socio']));

        $dados->pessoaJuridica->socios()->detach([1, 2]);
        $socio = factory('App\Socio')->create();
        $dados->pessoaJuridica->relacionarSocio($socio);
        $dados->refresh();

        $request = array_merge($dados->arrayValidacaoInputs(), $dados->pessoaJuridica->arrayValidacaoInputs(), 
        $dados->pessoaJuridica->socios->find(3)->arrayValidacaoInputs(), $dados->pessoaJuridica->socios->find(4)->arrayValidacaoInputs(), 
        ['path' => $dados->anexos->count(), 'checkRT_socio' => 'on']);

        $this->assertEquals(true, $dados->confereJustificadosSubmit($request));
        $this->assertEquals('1, 2', json_decode($dados->campos_editados, true)['removidos_socio']);
        $this->assertEquals(false, isset(json_decode($dados->campos_espelho, true)['cpf_cnpj_socio_1']));
        $this->assertEquals(false, isset(json_decode($dados->campos_espelho, true)['cpf_cnpj_socio_2']));
        $this->assertEquals(true, isset(json_decode($dados->campos_espelho, true)['checkRT_socio']));
        $this->assertEquals(true, isset(json_decode($dados->campos_espelho, true)['cpf_cnpj_socio_4']));
    }

    /** @test */
    public function verifica_atendente_pode_atualizar_status_sem_status_permitido()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals('Não possui o status necessário para ser ' . strtolower(PreRegistro::STATUS_APROVADO), 
        $dados->verificaAtendentePodeAtualizarStatus('aprovar'));
        $this->assertEquals('Não possui o status necessário para ser ' . strtolower(PreRegistro::STATUS_NEGADO), 
        $dados->verificaAtendentePodeAtualizarStatus('negar'));
        $this->assertEquals('Não possui o status necessário para ser enviado para correção', 
        $dados->verificaAtendentePodeAtualizarStatus('corrigir'));

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $this->assertEquals('Não possui o status necessário para ser ' . strtolower(PreRegistro::STATUS_APROVADO), 
        $dados->verificaAtendentePodeAtualizarStatus('aprovar'));
        $this->assertEquals('Não possui o status necessário para ser ' . strtolower(PreRegistro::STATUS_NEGADO), 
        $dados->verificaAtendentePodeAtualizarStatus('negar'));
        $this->assertEquals('Não possui o status necessário para ser enviado para correção', 
        $dados->verificaAtendentePodeAtualizarStatus('corrigir'));
    }

    /** @test */
    public function verifica_atendente_pode_atualizar_status_para_aprovar_pf()
    {
        // PF
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial', 'anexos_ok_pf')->create()
        ])->preRegistro;

        $this->assertEquals(['status' => PreRegistro::STATUS_APROVADO], $dados->verificaAtendentePodeAtualizarStatus('aprovar'));

        // Sem confirmação de anexos PF
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create()
        ])->preRegistro;

        $this->assertEquals('Faltou confirmar a entrega dos anexos', $dados->verificaAtendentePodeAtualizarStatus('aprovar'));

        // Com justificativa
        $dados = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao', 'anexos_ok_pf')->create()
        ])->preRegistro;

        $this->assertEquals('Possui justificativa(s)', $dados->verificaAtendentePodeAtualizarStatus('aprovar'));
    }

    /** @test */
    public function verifica_atendente_pode_atualizar_status_para_aprovar_pj()
    {
        // PJ
        $dados = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial', 'anexos_ok_pj')->create()
        ])->preRegistro;
        $dados->pessoaJuridica->responsavelTecnico->update(['registro' => '00000001']);

        $this->assertEquals(['status' => PreRegistro::STATUS_APROVADO], $dados->verificaAtendentePodeAtualizarStatus('aprovar'));

        // Sem anexos PJ
        $dados = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_correcao')->create()
        ])->preRegistro;

        $this->assertEquals('Faltou confirmar a entrega dos anexos', $dados->verificaAtendentePodeAtualizarStatus('aprovar'));

        // Com justificativa
        $dados = factory('App\PreRegistroCnpj')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_correcao', 'anexos_ok_pj')->create()
        ])->preRegistro;

        $this->assertEquals('Possui justificativa(s)', $dados->verificaAtendentePodeAtualizarStatus('aprovar'));

        // Sem registro do RT
        $dados = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_correcao', 'anexos_ok_pj')->create()
        ])->preRegistro;

        $dados->pessoaJuridica->responsavelTecnico->update(['registro' => null]);
        $this->assertEquals('Faltou inserir o registro do Responsável Técnico', $dados->verificaAtendentePodeAtualizarStatus('aprovar'));
    }

    /** @test */
    public function verifica_atendente_pode_atualizar_status_para_negar()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial', 'anexos_ok_pf')->create()
        ])->preRegistro;
        $dados->update(['justificativa' => json_encode(['negado' => $this->faker()->text(100)])]);

        $this->assertEquals(['status' => PreRegistro::STATUS_NEGADO], $dados->fresh()->verificaAtendentePodeAtualizarStatus('negar'));

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial', 'anexos_ok_pf')->create()
        ])->preRegistro;

        $this->assertEquals('Não possui justificativa(s)', $dados->verificaAtendentePodeAtualizarStatus('negar'));
    }

    /** @test */
    public function verifica_atendente_pode_atualizar_status_para_corrigir()
    {
        $dados = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial', 'anexos_ok_pf')->create()
        ])->preRegistro;

        $this->assertEquals(['status' => PreRegistro::STATUS_CORRECAO], $dados->verificaAtendentePodeAtualizarStatus('corrigir'));

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create()
        ])->preRegistro;
        $dados->update(['justificativa' => json_encode(['negado' => $this->faker()->text(100)])]);

        $this->assertEquals('Existe justificativa de negação, informe CTI', $dados->fresh()->verificaAtendentePodeAtualizarStatus('corrigir'));

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao', 'anexos_ok_pf')->create()
        ])->preRegistro;

        $this->assertEquals('Não possui justificativa(s)', $dados->verificaAtendentePodeAtualizarStatus('corrigir'));
    }

    /** @test */
    public function salvar_ajax_criar()
    {
        Storage::fake('local');

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
            ]),
        ])->preRegistro;

        $request = [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [
                UploadedFile::fake()->image('random.jpg')->size(300),
            ],
        ];

        $this->assertEquals(Anexo::class, get_class($dados->salvarAjax($request)));
        $this->assertDatabaseHas('anexos', ['extensao' => 'jpeg']);

        $cnpj = factory('App\Contabil')->raw()['cnpj'];
        $request = [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => $cnpj,
        ];

        $this->assertEquals('App\Contabil', get_class($dados->salvarAjax($request)));
        $this->assertDatabaseHas('contabeis', ['cnpj' => $request['valor']]);

        $dados = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => null,
        ])->preRegistro;

        $cpf = factory('App\ResponsavelTecnico')->raw()['cpf'];
        $request = [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => $cpf,
        ];

        $this->assertEquals('App\ResponsavelTecnico', get_class($dados->salvarAjax($request)));
        $this->assertDatabaseHas('responsaveis_tecnicos', ['cpf' => $request['valor']]);

        $cpf_cnpj = factory('App\Socio')->raw()['cpf_cnpj'];
        $request = [
            'classe' => 'pessoaJuridica.socios',
            'campo' => [0, 'cpf_cnpj_socio'],
            'valor' => $cpf_cnpj,
        ];

        $this->assertEquals(['tab', 'rt'], array_keys($dados->salvarAjax($request)));
        $this->assertDatabaseHas('socios', ['id' => 3, 'cpf_cnpj' => $request['valor']]);
    }

    /** @test */
    public function salvar_ajax_atualizar()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $valorAntigo = $dados->cep;
        $request = [
            'classe' => 'preRegistro',
            'campo' => 'cep',
            'valor' => '03455-099',
        ];

        $this->assertEquals(null, $dados->salvarAjax($request));
        $this->assertDatabaseHas('pre_registros', ['cep' => $request['valor']]);
        $this->assertDatabaseMissing('pre_registros', ['cep' => $valorAntigo]);

        $request = [
            'classe' => 'contabil',
            'campo' => 'nome_contabil',
            'valor' => 'TESTE NOME CONT',
        ];

        $valorAntigo = $dados->contabil->nome;
        $this->assertEquals(null, $dados->salvarAjax($request));
        $this->assertDatabaseHas('contabeis', ['nome' => $request['valor']]);
        $this->assertDatabaseMissing('contabeis', ['nome' => $valorAntigo]);

        $request = [
            'classe' => 'pessoaFisica',
            'campo' => 'nome_social',
            'valor' => 'TESTE NOME SOCIAL',
        ];

        $valorAntigo = $dados->pessoaFisica->nome_social;
        $this->assertEquals(null, $dados->salvarAjax($request));
        $this->assertDatabaseHas('pre_registros_cpf', ['nome_social' => $request['valor']]);
        $this->assertDatabaseMissing('pre_registros_cpf', ['nome_social' => $valorAntigo]);

        $dados = factory('App\PreRegistroCnpj')->create()->preRegistro;

        $request = [
            'classe' => 'pessoaJuridica',
            'campo' => 'nome_fantasia',
            'valor' => 'TESTE NOME FANTASIA',
        ];

        $valorAntigo = $dados->pessoaJuridica->nome_fantasia;
        $this->assertEquals(null, $dados->salvarAjax($request));
        $this->assertDatabaseHas('pre_registros_cnpj', ['nome_fantasia' => $request['valor']]);
        $this->assertDatabaseMissing('pre_registros_cnpj', ['nome_fantasia' => $valorAntigo]);

        $request = [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'nome_rt',
            'valor' => 'TESTE NOME RT',
        ];

        $valorAntigo = $dados->pessoaJuridica->responsavelTecnico->nome;
        $this->assertEquals(null, $dados->salvarAjax($request));
        $this->assertDatabaseHas('responsaveis_tecnicos', ['nome' => $request['valor']]);
        $this->assertDatabaseMissing('responsaveis_tecnicos', ['nome' => $valorAntigo]);

        $request = [
            'classe' => 'pessoaJuridica.socios',
            'campo' => [1, 'nome_socio'],
            'valor' => 'TESTE NOME SOCIO',
        ];

        $valorAntigo = $dados->pessoaJuridica->socios->get(0)->nome;
        $this->assertEquals(['atualizado', 'id'], array_keys($dados->salvarAjax($request)));
        $this->assertDatabaseHas('socios', ['id' => 1, 'nome' => $request['valor']]);
        $this->assertDatabaseMissing('socios', ['nome' => $valorAntigo]);
    }

    /** @test */
    public function salvar_ajax_admin()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $request = [
            'acao' => 'justificar',
            'campo' => 'cep',
            'valor' => $this->faker()->text(100),
        ];

        $this->assertEquals(null, $dados->salvarAjax($request, null, true));
        $this->assertDatabaseMissing('pre_registros', ['justificativa' => null]);

        $request = [
            'acao' => 'justificar',
            'campo' => 'cep',
            'valor' => null,
        ];

        $this->assertEquals(null, $dados->salvarAjax($request, null, true));
        $this->assertDatabaseHas('pre_registros', ['justificativa' => null]);

        $request = [
            'acao' => 'editar',
            'campo' => 'registro_secundario',
            'valor' => '000000045',
        ];

        $this->assertEquals(null, $dados->salvarAjax($request, null, true));
        $this->assertDatabaseHas('pre_registros', ['registro_secundario' => $request['valor']]);

        $request = [
            'acao' => 'conferir',
            'campo' => 'confere_anexos',
            'valor' => 'CPF',
        ];

        $this->assertEquals(null, $dados->salvarAjax($request, null, true));
        $this->assertDatabaseMissing('pre_registros', ['confere_anexos' => null]);

        $dados = factory('App\PreRegistroCnpj')->states('justificativas')->create()->preRegistro;

        $request = [
            'acao' => 'editar',
            'campo' => 'registro',
            'valor' => '000000002',
        ];

        $this->assertEquals(null, $dados->salvarAjax($request, null, true));
        $this->assertDatabaseHas('responsaveis_tecnicos', ['registro' => $request['valor']]);

        $valorAntigo = $dados->justificativa;
        $request = [
            'acao' => 'exclusao_massa',
            'campo' => 'exclusao_massa',
            'valor' => ['cep', 'bairro', 'uf'],
        ];

        $this->assertStringContainsString('"cep":', $valorAntigo);
        $this->assertStringContainsString('"bairro":', $valorAntigo);
        $this->assertStringContainsString('"uf":', $valorAntigo);

        $this->assertEquals(null, $dados->salvarAjax($request, null, true));
        $this->assertDatabaseMissing('pre_registros', ['justificativa' => $valorAntigo]);
        $this->assertNotEquals($valorAntigo, $dados->justificativa);

        $this->assertStringNotContainsString('"cep":', $dados->justificativa);
        $this->assertStringNotContainsString('"bairro":', $dados->justificativa);
        $this->assertStringNotContainsString('"uf":', $dados->justificativa);
    }

    /** @test */
    public function salvar()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $antigoHS = $dados->historico_status;
        $this->assertEquals(PreRegistro::STATUS_ANALISE_INICIAL, $dados->salvar());
        $this->assertNotEquals($antigoHS, $dados->historico_status);
        $this->assertDatabaseHas('pre_registros', ['id' => 1, 'status' => PreRegistro::STATUS_ANALISE_INICIAL]);

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create(),
        ])->preRegistro;

        $antigoHS = $dados->historico_status;
        $this->assertEquals(PreRegistro::STATUS_ANALISE_INICIAL, $dados->salvar());
        $this->assertEquals($antigoHS, $dados->historico_status);
        $this->assertDatabaseHas('pre_registros', ['id' => 2, 'status' => PreRegistro::STATUS_ANALISE_INICIAL]);

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create(),
        ])->preRegistro;

        $antigoHS = $dados->historico_status;
        $this->assertEquals(PreRegistro::STATUS_ANALISE_CORRECAO, $dados->salvar());
        $this->assertNotEquals($antigoHS, $dados->historico_status);
        $this->assertDatabaseHas('pre_registros', ['id' => 3, 'status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create(),
        ])->preRegistro;

        $antigoHS = $dados->historico_status;
        $this->assertEquals(PreRegistro::STATUS_ANALISE_CORRECAO, $dados->salvar());
        $this->assertEquals($antigoHS, $dados->historico_status);
        $this->assertDatabaseHas('pre_registros', ['id' => 4, 'status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create(),
        ])->preRegistro;

        $antigoHS = $dados->historico_status;
        $this->assertEquals(PreRegistro::STATUS_APROVADO, $dados->salvar());
        $this->assertEquals($antigoHS, $dados->historico_status);
        $this->assertDatabaseHas('pre_registros', ['id' => 5, 'status' => PreRegistro::STATUS_APROVADO]);

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create(),
        ])->preRegistro;

        $antigoHS = $dados->historico_status;
        $this->assertEquals(PreRegistro::STATUS_NEGADO, $dados->salvar());
        $this->assertEquals($antigoHS, $dados->historico_status);
        $this->assertDatabaseHas('pre_registros', ['id' => 6, 'status' => PreRegistro::STATUS_NEGADO]);
    }

    /** @test */
    public function array_validacao_inputs()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals([
            'segmento' => $dados->segmento,
            'cep' => $dados->cep,
            'bairro' => $dados->bairro,
            'logradouro' => $dados->logradouro,
            'numero' => $dados->numero,
            'complemento' => $dados->complemento,
            'cidade' => $dados->cidade,
            'uf' => $dados->uf,
            'telefone' => $dados->getTelefone()[0],
            'telefone_1' => isset($dados->getTelefone()[1]) ? $dados->getTelefone()[1] : null,
            'tipo_telefone' => $dados->getTipoTelefone()[0],
            'tipo_telefone_1' => isset($dados->getTipoTelefone()[1]) ? $dados->getTipoTelefone()[1] : null,
            'opcional_celular' => $dados->getOpcionalCelular()[0],
            'opcional_celular_1' => isset($dados->getOpcionalCelular()[1]) ? $dados->getOpcionalCelular()[1] : [],
            'idregional' => $dados->idregional,
        ], $dados->arrayValidacaoInputs());
    }

    /** @test */
    public function atualizar_final()
    {
        $pr = factory('App\PreRegistroCpf')->create();
        $novo = factory('App\PreRegistro')->raw();

        foreach(Arr::except(PreRegistro::camposPreRegistro(), [2, 3, 4]) as $dado)
        {
            $this->assertEquals(null, PreRegistro::first()->atualizarFinal($dado, $novo[$dado]));
            $this->assertEquals($novo[$dado], PreRegistro::first()[$dado]);
        }

        // Somente telefones
        $this->assertEquals(null, PreRegistro::first()->atualizarFinal('tipo_telefone', mb_strtoupper(tipos_contatos()[1], 'UTF-8')));
        $this->assertEquals(mb_strtoupper(tipos_contatos()[1], 'UTF-8') . ';', PreRegistro::first()['tipo_telefone']);

        $this->assertEquals(null, PreRegistro::first()->atualizarFinal('tipo_telefone_1', mb_strtoupper(tipos_contatos()[2], 'UTF-8')));
        $this->assertEquals(mb_strtoupper(tipos_contatos()[1], 'UTF-8') . ';' . mb_strtoupper(tipos_contatos()[2], 'UTF-8'), PreRegistro::first()['tipo_telefone']);

        $this->assertEquals(null, PreRegistro::first()->atualizarFinal('telefone', '(12) 12334-4567'));
        $this->assertEquals('(12) 12334-4567;', PreRegistro::first()['telefone']);

        $this->assertEquals(null, PreRegistro::first()->atualizarFinal('telefone_1', '(14) 45675-1234'));
        $this->assertEquals('(12) 12334-4567;(14) 45675-1234', PreRegistro::first()['telefone']);

        $this->assertEquals(null, PreRegistro::first()->atualizarFinal('opcional_celular', mb_strtoupper(opcoes_celular()[0], 'UTF-8')));
        $this->assertEquals('TELEGRAM,' . mb_strtoupper(opcoes_celular()[0], 'UTF-8') . ';', PreRegistro::first()['opcional_celular']);

        $this->assertEquals(null, PreRegistro::first()->atualizarFinal('opcional_celular_1', mb_strtoupper(opcoes_celular()[2], 'UTF-8')));
        $this->assertEquals('TELEGRAM,' . mb_strtoupper(opcoes_celular()[0], 'UTF-8') . ';' . mb_strtoupper(opcoes_celular()[2]), PreRegistro::first()['opcional_celular']);

        $pr = factory('App\PreRegistroCnpj')->create();

        $this->assertEquals(false, PreRegistro::find(2)->atualizarFinal('cep', $pr->cep));
        $this->assertEquals(false, PreRegistro::find(2)->atualizarFinal('logradouro', $pr->logradouro));
        $this->assertEquals(false, PreRegistro::find(2)->atualizarFinal('cidade', $pr->cidade));
        $this->assertEquals(false, PreRegistro::find(2)->atualizarFinal('uf', $pr->uf));
        $this->assertEquals(false, PreRegistro::find(2)->atualizarFinal('numero', $pr->numero));
        $this->assertEquals(true, PreRegistro::find(2)->atualizarFinal('bairro', $pr->bairro));
    }

    /** @test */
    public function soft_delete()
    {
        $user = factory('App\PreRegistro')->create();

        $this->assertEquals(1, PreRegistro::count());
        $this->assertDatabaseHas('pre_registros', ['id' => 1, 'deleted_at' => null]);

        $user->delete();

        $this->assertEquals(0, PreRegistro::count());
        $this->assertDatabaseMissing('pre_registros', ['id' => 1, 'deleted_at' => null]);

        PreRegistro::withTrashed()->first()->restore();

        $this->assertEquals(1, PreRegistro::count());
        $this->assertDatabaseHas('pre_registros', ['id' => 1, 'deleted_at' => null]);
    }

    /** 
     * =======================================================================================================
     * TESTES TRAIT PREREGISTROAPOIO
     * =======================================================================================================
     */

    /** @test */
    public function get_menu()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals([
            'Contabilidade',
            'Dados Gerais',
            'Endereço',
            'Contato / RT',
            'Sócios',
            'Canal de Relacionamento',
            'Anexos',
        ], $dados->getMenu());
    }

    /** @test */
    public function get_nomes_relacoes()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals([
            'anexos',
            'contabil',
            'pessoaFisica',
            'pessoaJuridica',
            'preRegistro',
            'pessoaJuridica.responsavelTecnico',
            'pessoaJuridica.socios',
        ], $dados->getNomesRelacoes());
    }

    /** @test */
    public function get_nomes_campos()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals([
            'anexos' => 'path',
            'contabil' => 'nome_contabil,cnpj_contabil,email_contabil,nome_contato_contabil,telefone_contabil',
            'preRegistro' => 'segmento,idregional,cep,bairro,logradouro,numero,complemento,cidade,uf,tipo_telefone,telefone,opcional_celular,tipo_telefone_1,telefone_1,opcional_celular_1,pergunta',
            'pessoaFisica' => 'nome_social,sexo,dt_nascimento,estado_civil,nacionalidade,naturalidade_cidade,naturalidade_estado,nome_mae,nome_pai,tipo_identidade,identidade,orgao_emissor,dt_expedicao,titulo_eleitor,zona,secao,ra_reservista',
            'pessoaJuridica' => 'razao_social,nome_fantasia,capital_social,nire,tipo_empresa,dt_inicio_atividade,checkEndEmpresa,cep_empresa,bairro_empresa,logradouro_empresa,numero_empresa,complemento_empresa,cidade_empresa,uf_empresa',
            'pessoaJuridica.responsavelTecnico' => 'nome_rt,nome_social_rt,sexo_rt,dt_nascimento_rt,cpf_rt,tipo_identidade_rt,identidade_rt,orgao_emissor_rt,dt_expedicao_rt,titulo_eleitor_rt,zona_rt,secao_rt,ra_reservista_rt,cep_rt,bairro_rt,logradouro_rt,numero_rt,complemento_rt,cidade_rt,uf_rt,nome_mae_rt,nome_pai_rt',
            'pessoaJuridica.socios' => 'checkRT_socio,cpf_cnpj_socio,nome_socio,nome_social_socio,dt_nascimento_socio,identidade_socio,orgao_emissor_socio,cep_socio,bairro_socio,logradouro_socio,numero_socio,complemento_socio,cidade_socio,uf_socio,nome_mae_socio,nome_pai_socio,nacionalidade_socio,naturalidade_estado_socio',
        ], $dados->getNomesCampos());
    }

    /** @test */
    public function get_codigos_campos()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals([
            [
                "cnpj_contabil" => "1.1",
                "nome_contabil" => "1.2",
                "email_contabil" => "1.3",
                "nome_contato_contabil" => "1.4",
                "telefone_contabil" => "1.5",
            ],
            [
                "nome_social" => "2.1",
                "sexo" => "2.2",
                "dt_nascimento" => "2.3",
                "estado_civil" => "2.4",
                "nacionalidade" => "2.5",
                "naturalidade_cidade" => "2.6",
                "naturalidade_estado" => "2.7",
                "nome_mae" => "2.8",
                "nome_pai" => "2.9",
                "tipo_identidade" => "2.10",
                "identidade" => "2.11",
                "orgao_emissor" => "2.12",
                "dt_expedicao" => "2.13",
                "titulo_eleitor" => "2.14",
                "zona" => "2.15",
                "secao" => "2.16",
                "ra_reservista" => "2.17",
                "segmento" => "2.18",
                "idregional" => "2.19",
                "pergunta" => "2.20",
            ],
            [
                "cep" => "3.1",
                "bairro" => "3.2",
                "logradouro" => "3.3",
                "numero" => "3.4",
                "complemento" => "3.5",
                "cidade" => "3.6",
                "uf" => "3.7",
                "checkEndEmpresa" => "3.8",
                "cep_empresa" => "3.9",
                "bairro_empresa" => "3.10",
                "logradouro_empresa" => "3.11",
                "numero_empresa" => "3.12",
                "complemento_empresa" => "3.13",
                "cidade_empresa" => "3.14",
                "uf_empresa" => "3.15",
            ],
            [
                "cpf_rt" => "4.1",
                "registro" => "4.2",
                "nome_rt" => "4.3",
                "nome_social_rt" => "4.4",
                "dt_nascimento_rt" => "4.5",
                "sexo_rt" => "4.6",
                "tipo_identidade_rt" => "4.7",
                "identidade_rt" => "4.8",
                "orgao_emissor_rt" => "4.9",
                "dt_expedicao_rt" => "4.10",
                "titulo_eleitor_rt" => "4.11",
                "zona_rt" => "4.12",
                "secao_rt" => "4.13",
                "ra_reservista_rt" => "4.14",
                "cep_rt" => "4.15",
                "bairro_rt" => "4.16",
                "logradouro_rt" => "4.17",
                "numero_rt" => "4.18",
                "complemento_rt" => "4.19",
                "cidade_rt" => "4.20",
                "uf_rt" => "4.21",
                "nome_mae_rt" => "4.22",
                "nome_pai_rt" => "4.23",
            ],
            [
                "checkRT_socio" => "5.1",
                "cpf_cnpj_socio" => "5.2",
                "registro_socio" => "5.3",
                "nome_socio" => "5.4",
                "nome_social_socio" => "5.5",
                "dt_nascimento_socio" => "5.6",
                "identidade_socio" => "5.7",
                "orgao_emissor_socio" => "5.8",
                "cep_socio" => "5.9",
                "bairro_socio" => "5.10",
                "logradouro_socio" => "5.11",
                "numero_socio" => "5.12",
                "complemento_socio" => "5.13",
                "cidade_socio" => "5.14",
                "uf_socio" => "5.15",
                "nome_mae_socio" => "5.16",
                "nome_pai_socio" => "5.17",
                "nacionalidade_socio" => "5.18",
                "naturalidade_estado_socio" => "5.19",
            ],
            [
                "tipo_telefone" => "6.1",
                "telefone" => "6.2",
                "opcional_celular" => "6.3",
                "tipo_telefone_1" => "6.4",
                "telefone_1" => "6.5",
                "opcional_celular_1" => "6.6",
            ],
            [
                "path" => "7.1",
            ],
        ], $dados->getCodigosCampos());
    }

    /** @test */
    public function verifica_se_cria_ou_atualiza_quando_atualizar_sem_gerenti()
    {
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;

        $gerenti = null;
        $objetoExiste = false;
        $request = [
            'classe' => 'preRegistro',
            'campo' => 'cep',
            'valor' => '05656-050',
        ];

        $this->assertEquals([
            'resp' => 'atualizar',
            'classe' => 'preRegistro',
            'campo' => 'cep',
            'valor' => '05656-050',
            'gerenti' => $gerenti,
        ], $dados->verificaSeCriaOuAtualiza($request, $gerenti, $objetoExiste));

        $request = [
            'classe' => 'pessoaFisica',
            'campo' => 'nome_social',
            'valor' => 'NOME FAKE PF',
        ];
        $objetoExiste = $dados->has($request['classe'])->where('id', $dados->id)->exists();

        $this->assertEquals([
            'resp' => 'atualizar',
            'classe' => 'pessoaFisica',
            'campo' => 'nome_social',
            'valor' => 'NOME FAKE PF',
            'gerenti' => $gerenti,
        ], $dados->verificaSeCriaOuAtualiza($request, $gerenti, $objetoExiste));

        $request = [
            'classe' => 'contabil',
            'campo' => 'nome_contabil',
            'valor' => 'NOME FAKE CONTABIL',
        ];
        $objetoExiste = $dados->has($request['classe'])->where('id', $dados->id)->exists();

        $this->assertEquals([
            'resp' => 'atualizar',
            'classe' => 'contabil',
            'campo' => 'nome',
            'valor' => 'NOME FAKE CONTABIL',
            'gerenti' => $gerenti,
        ], $dados->verificaSeCriaOuAtualiza($request, $gerenti, $objetoExiste));

        // PJ
        $dados = factory('App\PreRegistroCnpj')->create()->preRegistro;

        $request = [
            'classe' => 'pessoaJuridica',
            'campo' => 'cep_empresa',
            'valor' => '05656-050',
        ];
        $objetoExiste = $dados->has($request['classe'])->where('id', $dados->id)->exists();

        $this->assertEquals([
            'resp' => 'atualizar',
            'classe' => 'pessoaJuridica',
            'campo' => 'cep',
            'valor' => '05656-050',
            'gerenti' => $gerenti,
        ], $dados->verificaSeCriaOuAtualiza($request, $gerenti, $objetoExiste));

        $request = [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cep_rt',
            'valor' => '05656-050',
        ];
        $objetoExiste = $dados->has($request['classe'])->where('id', $dados->id)->exists();

        $this->assertEquals([
            'resp' => 'atualizar',
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cep',
            'valor' => '05656-050',
            'gerenti' => $gerenti,
        ], $dados->verificaSeCriaOuAtualiza($request, $gerenti, $objetoExiste));

        $request = [
            'classe' => 'pessoaJuridica.socios',
            'campo' => [1, 'cep_socio'],
            'valor' => '05656-050',
        ];
        $objetoExiste = $dados->has($request['classe'])->where('id', $dados->id)->exists();

        $this->assertEquals([
            'resp' => 'atualizar',
            'classe' => 'pessoaJuridica.socios',
            'campo' => [1, 'cep'],
            'valor' => '05656-050',
            'gerenti' => $gerenti,
        ], $dados->verificaSeCriaOuAtualiza($request, $gerenti, $objetoExiste));
    }

    /** @test */
    public function verifica_se_cria_ou_atualiza_quando_criar_sem_gerenti()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null
            ]),
        ])->preRegistro;

        $gerenti = null;
        $request = [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => factory('App\Contabil')->raw()['cnpj'],
        ];
        $objetoExiste = $dados->has($request['classe'])->where('id', $dados->id)->exists();

        $this->assertEquals([
            'resp' => 'criar',
            'classe' => 'contabil',
            'campo' => 'cnpj',
            'valor' => $request['valor'],
            'gerenti' => $gerenti,
        ], $dados->verificaSeCriaOuAtualiza($request, $gerenti, $objetoExiste));

        $request = [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->image('random.jpg')->size(300)],
        ];
        $objetoExiste = $dados->has($request['classe'])->where('id', $dados->id)->exists();

        $this->assertEquals([
            'resp' => 'criar',
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => $request['valor'],
            'gerenti' => $gerenti,
        ], $dados->verificaSeCriaOuAtualiza($request, $gerenti, $objetoExiste));

        // PJ
        $dados = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => null
        ])->preRegistro;

        $request = [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf'],
        ];
        $objetoExiste = $dados->has($request['classe'])->where('id', $dados->id)->exists();

        $this->assertEquals([
            'resp' => 'criar',
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf',
            'valor' => $request['valor'],
            'gerenti' => $gerenti,
        ], $dados->verificaSeCriaOuAtualiza($request, $gerenti, $objetoExiste));

        $request = [
            'classe' => 'pessoaJuridica.socios',
            'campo' => [0, 'cpf_cnpj_socio'],
            'valor' => factory('App\Socio')->raw()['cpf_cnpj'],
        ];
        $objetoExiste = $dados->has($request['classe'])->where('id', $dados->id)->exists();

        $this->assertEquals([
            'resp' => 'criar',
            'classe' => 'pessoaJuridica.socios',
            'campo' => [0, 'cpf_cnpj'],
            'valor' => $request['valor'],
            'gerenti' => $gerenti,
        ], $dados->verificaSeCriaOuAtualiza($request, $gerenti, $objetoExiste));
    }

    /** @test */
    public function verifica_se_cria_ou_atualiza_quando_criar_com_gerenti()
    {
        $gerenti = new GerentiRepositoryMock;

        $dados = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => null
        ])->preRegistro;

        $request = [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => self::CPF_GERENTI,
        ];
        $objetoExiste = $dados->has($request['classe'])->where('id', $dados->id)->exists();

        // Em caso de erro, mudar no metodo gerentiBusca() item ASS_TP_ASSOC para 5
        $this->assertEquals([
            'resp' => 'criar',
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf',
            'valor' => $request['valor'],
            'gerenti' => [
                "nome" => "RC TESTE 1",
                "registro" => "0000000001",
                "nome_mae" => "MAE 1",
                "nome_pai" => "PAI 1",
                "identidade" => "111111111",
                "orgao_emissor" => "SSP-SP",
                "dt_expedicao" => "2012-03-05",
                "dt_nascimento" => "1962-09-30",
                "sexo" => "M",
                "cpf" => "86294373085",
            ],
        ], $dados->verificaSeCriaOuAtualiza($request, $gerenti, $objetoExiste));

        $request = [
            'classe' => 'pessoaJuridica.socios',
            'campo' => [0, 'cpf_cnpj_socio'],
            'valor' => self::CNPJ_GERENTI,
        ];
        $objetoExiste = $dados->has($request['classe'])->where('id', $dados->id)->exists();

        $this->assertEquals([
            'resp' => 'criar',
            'classe' => 'pessoaJuridica.socios',
            'campo' => [0, 'cpf_cnpj'],
            'valor' => $request['valor'],
            'gerenti' => [
                "nome" => "RC TESTE 2",
                "registro" => "0000000002",
                "cpf_cnpj" => "11748345000144",
            ],
        ], $dados->verificaSeCriaOuAtualiza($request, $gerenti, $objetoExiste));
    }

    /** 
     * =======================================================================================================
     * TESTES SERVICE PREREGISTROSERVICE
     * =======================================================================================================
     */

    /** @test */
    public function verificacao()
    {
        $gerenti = new GerentiRepositoryMock;
        $service = new PreRegistroService;

        $externo = factory('App\UserExterno')->create();

        $this->assertEquals([
            'gerenti' => null,
        ], $service->verificacao($gerenti, $externo));

        $externo = factory('App\UserExterno')->create([
            'cpf_cnpj' => self::CPF_GERENTI,
        ]);

        $this->assertEquals([
            'gerenti' => '0000000001',
        ], $service->verificacao($gerenti, $externo));

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Somente usuário externo pode ser verificado no sistema se consta registro.');
        $service->verificacao($gerenti, null);
    }

    /** @test */
    public function set_pre_registro_com_pre_registro_em_andamento()
    {
        $gerenti = new GerentiRepositoryMock;
        $service = new MediadorService;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create()
        ])->preRegistro;

        $contabil = factory('App\Contabil')->create();

        $dados = [
            'cpf_cnpj' => $dadosUser->userExterno->cpf_cnpj,
            'email' => $dadosUser->userExterno->email,
            'nome' => $dadosUser->userExterno->nome,
        ];

        $this->assertEquals([
            'message' => 'Este CPF / CNPJ já possui uma solicitação de registro em andamento. Por gentileza, peça que o representante insira no formulário o seu CNPJ.',
            'class' => 'alert-warning'
        ], $service->getService('PreRegistro')->setPreRegistro($gerenti, $service, $contabil, $dados));
    }

    /** @test */
    public function set_pre_registro_com_pre_registro_e_usuario_externo_existente_no_gerenti()
    {
        $gerenti = new GerentiRepositoryMock;
        $service = new MediadorService;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;
        $dadosUser->userExterno->update(['cpf_cnpj' => self::CPF_GERENTI]);

        $contabil = factory('App\Contabil')->create();

        $dados = [
            'cpf_cnpj' => $dadosUser->userExterno->cpf_cnpj,
            'email' => $dadosUser->userExterno->email,
            'nome' => $dadosUser->userExterno->nome,
        ];

        $this->assertEquals([
            'message' => 'Este CPF / CNPJ já possui registro ativo no Core-SP: 000000/0001',
            'class' => 'alert-info'
        ], $service->getService('PreRegistro')->setPreRegistro($gerenti, $service, $contabil, $dados));
    }

    /** @test */
    public function set_pre_registro_com_pre_registro_aprovado()
    {
        $gerenti = new GerentiRepositoryMock;
        $service = new MediadorService;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ])->preRegistro;

        $contabil = factory('App\Contabil')->create();

        $dados = [
            'cpf_cnpj' => $dadosUser->userExterno->cpf_cnpj,
            'email' => $dadosUser->userExterno->email,
            'nome' => $dadosUser->userExterno->nome,
        ];

        $this->assertEquals([
            'message' => 'Este CPF / CNPJ já possui uma solicitação aprovada.',
            'class' => 'alert-warning'
        ], $service->getService('PreRegistro')->setPreRegistro($gerenti, $service, $contabil, $dados));
    }

    /** @test */
    public function set_pre_registro_com_usuario_externo_existente()
    {
        $gerenti = new GerentiRepositoryMock;
        $service = new MediadorService;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $contabil = factory('App\Contabil')->create();

        $dados = [
            'cpf_cnpj' => $dadosUser->userExterno->cpf_cnpj,
            'email' => $dadosUser->userExterno->email,
            'nome' => $dadosUser->userExterno->nome,
        ];

        $this->assertEquals(PreRegistro::class, get_class($service->getService('PreRegistro')->setPreRegistro($gerenti, $service, $contabil, $dados)));
        $this->assertEquals(2, PreRegistro::find(2)->contabil_id);
    }

    /** @test */
    public function set_pre_registro_sem_usuario_externo_existente_e_no_gerenti()
    {
        $gerenti = new GerentiRepositoryMock;
        $service = new MediadorService;

        $externo = (object) factory('App\UserExterno')->raw();
        $contabil = factory('App\Contabil')->create();

        $dados = [
            'cpf_cnpj' => self::CPF_GERENTI,
            'email' => $externo->email,
            'nome' => $externo->nome,
        ];

        $this->assertEquals([
            'message' => 'Este CPF / CNPJ já possui registro ativo no Core-SP: 000000/0001',
            'class' => 'alert-info'
        ], $service->getService('PreRegistro')->setPreRegistro($gerenti, $service, $contabil, $dados));
    }

    /** @test */
    public function set_pre_registro_sem_usuario_externo_existente()
    {
        $gerenti = new GerentiRepositoryMock;
        $service = new MediadorService;

        $externo = (object) factory('App\UserExterno')->raw();
        $contabil = factory('App\Contabil')->create();

        $dados = [
            'cpf_cnpj' => $externo->cpf_cnpj,
            'email' => $externo->email,
            'nome' => $externo->nome,
        ];

        $this->assertEquals(PreRegistro::class, get_class($service->getService('PreRegistro')->setPreRegistro($gerenti, $service, $contabil, $dados)));
        $this->assertEquals(1, PreRegistro::first()->contabil_id);
    }

    /** @test */
    public function get_pre_registros()
    {
        $service = new PreRegistroService;

        $contabil = factory('App\Contabil')->create();

        $this->assertEquals(0, $service->getPreRegistros($contabil)['resultados']->count());

        $dadosUser = factory('App\PreRegistroCpf')->create()->preRegistro;

        $this->assertEquals(1, $service->getPreRegistros($contabil)['resultados']->count());

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $this->assertEquals(2, $service->getPreRegistros($contabil)['resultados']->count());

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ])->preRegistro;

        $this->assertEquals(3, $service->getPreRegistros($contabil)['resultados']->count());
    }

    /** @test */
    public function get_pre_registro()
    {
        $service = new MediadorService;

        $externo = factory('App\UserExterno')->create();

        $this->assertEquals(0, PreRegistro::count());
        $this->assertEquals(PreRegistro::class, get_class($service->getService('PreRegistro')->getPreRegistro($service, $externo)['resultado']));
        $this->assertEquals(1, PreRegistro::count());

        $dadosUser = factory('App\PreRegistroCpf')->create()->preRegistro->userExterno;

        $this->assertEquals(PreRegistro::STATUS_CRIADO, $service->getService('PreRegistro')->getPreRegistro($service, $externo)['resultado']->status);
        $this->assertEquals(2, PreRegistro::count());

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ])->preRegistro;

        $this->assertEquals('Este CPF / CNPJ já possui uma solicitação aprovada.', $service->getService('PreRegistro')->getPreRegistro($service, $externo)['message']);
        $this->assertEquals(3, PreRegistro::count());

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Somente usuário externo ou contabilidade vinculada a um usuário externo pode solicitar registro.');
        $service->getService('PreRegistro')->getPreRegistro($service, null);
    }

    /** @test */
    public function save_site_ajax()
    {
        Storage::fake('local');

        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->create()->preRegistro;
        $request = [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [
                UploadedFile::fake()->image('random.jpg')->size(300),
            ],
        ];

        $resp = $service->saveSiteAjax($request, null, $dadosUser->userExterno);
        $this->assertEquals([
            'resultado', 'dt_atualizado',
        ], array_keys($resp));

        $this->assertEquals('App\Anexo', get_class($resp['resultado']));

        $request = [
            'classe' => 'pessoaFisica',
            'campo' => 'nome_social',
            'valor' => 'TESTE NOVO DO NOME SOCIAL',
        ];

        $this->assertEquals([
            'resultado', 'dt_atualizado',
        ], array_keys($service->saveSiteAjax($request, null, $dadosUser->userExterno)));

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não autorizado a acessar a solicitação de registro por falta relacionamento com usuário externo');
        $service->saveSiteAjax([], null, null, null);
    }

    /** @test */
    public function save_site_ajax_exception_externo()
    {
        $service = new PreRegistroService;

        $externo = factory('App\UserExterno')->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não autorizado a acessar a solicitação de registro');
        $service->saveSiteAjax([], null, $externo, null);
    }

    /** @test */
    public function save_site_ajax_exception_user_editar()
    {
        $service = new PreRegistroService;

        $externo = factory('App\UserExterno')->create();
        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ])->preRegistro;

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não autorizado a editar o formulário com a solicitação em análise ou finalizada');
        $service->saveSiteAjax([], null, $externo, null);
    }

    /** @test */
    public function save_site()
    {
        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->create()->preRegistro;
        $request = [
            "cep" => "01234-001",
            "bairro" => "TESTE",
            "logradouro" => "RUA TESTE DA ESQUINA",
            "numero" => "2671",
            "complemento" => null,
            "cidade" => "SÃO PAULO",
            "uf" => "SP",
        ];

        $this->assertEquals([
            'message' => '<i class="icon fa fa-check"></i> Solicitação de registro enviada para análise! <strong>Status atualizado para:</strong> Em análise inicial',
            'class' => 'alert-success'
        ], $service->saveSite($request, null, $dadosUser->userExterno));

        factory('App\UserExterno')->create();

        // ID do anexo
        $request = array_merge($request, ['path' => '2']);
        $dadosUser = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create([
                'campos_espelho' => json_encode($request),
            ]),
        ])->preRegistro;

        $this->assertEquals([
            'message' => '<i class="fas fa-times"></i> Formulário não foi enviado para análise da correção, pois precisa editar dados(s) conforme justificativa(s).',
            'class' => 'alert-danger'
        ], $service->saveSite($request, null, $dadosUser->userExterno));

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não autorizado a acessar a solicitação de registro por falta relacionamento com usuário externo');
        $service->saveSite([], null, null, null);
    }

    /** @test */
    public function save_site_exception_externo()
    {
        $service = new PreRegistroService;

        $externo = factory('App\UserExterno')->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não autorizado a acessar a solicitação de registro');
        $service->saveSite([], null, $externo, null);
    }

    /** @test */
    public function save_site_exception_user_editar()
    {
        $service = new PreRegistroService;

        $externo = factory('App\UserExterno')->create();
        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ])->preRegistro;

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não autorizado a editar o formulário com a solicitação em análise ou finalizada');
        $service->saveSite([], null, $externo, null);
    }

    /** @test */
    public function download_anexo()
    {
        Storage::fake('local');

        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->create()->preRegistro;
        $request = [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [
                UploadedFile::fake()->image('random.jpg')->size(300),
            ],
        ];

        $service->saveSiteAjax($request, null, $dadosUser->userExterno);

        $this->assertEquals(Storage::disk('local')->path(Anexo::find(2)->path), $service->downloadAnexo(2, 1));

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Arquivo de anexo do pré-registro não existe / não pode acessar');
        $service->downloadAnexo(1, 1);
    }

    /** @test */
    public function download_anexo_exception_pre_registro()
    {
        $service = new PreRegistroService;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No query results for model [App\PreRegistro] 1');
        $service->downloadAnexo(1, 1);
    }

    /** @test */
    public function excluir_anexo()
    {
        Storage::fake('local');

        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->create()->preRegistro;
        $request = [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [
                UploadedFile::fake()->image('random.jpg')->size(300),
            ],
        ];

        $service->saveSiteAjax($request, null, $dadosUser->userExterno);

        $this->assertEquals(2, $service->excluirAnexo(2, $dadosUser->userExterno)['resultado']);

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Arquivo não existe / não pode acessar');
        $service->excluirAnexo(1, $dadosUser->userExterno);
    }

    /** @test */
    public function excluir_anexo_com_contabil()
    {
        Storage::fake('local');

        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->create()->preRegistro;
        $request = [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [
                UploadedFile::fake()->image('random.jpg')->size(300),
            ],
        ];

        $service->saveSiteAjax($request, null, $dadosUser->userExterno);

        $this->assertEquals(2, $service->excluirAnexo(2, $dadosUser->userExterno, $dadosUser->contabil)['resultado']);
    }

    /** @test */
    public function excluir_anexo_exception_user_editar()
    {
        $service = new PreRegistroService;

        $externo = factory('App\UserExterno')->create();
        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ])->preRegistro;

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não autorizado a excluir arquivo com status diferente de ' . PreRegistro::STATUS_CORRECAO);
        $service->excluirAnexo(1, $dadosUser->userExterno);
    }

    /** @test */
    public function admin()
    {
        $service = new PreRegistroService;

        $this->assertEquals(PreRegistroAdminSubService::class, get_class($service->admin()));
    }

    /** 
     * =======================================================================================================
     * TESTES SUB SERVICE PREREGISTROADMINSUBSERVICE
     * =======================================================================================================
     */

    /** @test */
    public function tipos_docs_atendente()
    {
        $service = new PreRegistroService;

        $this->assertEquals(Anexo::tiposDocsAtendentePreRegistro(), $service->admin()->tiposDocsAtendente());
    }

    /** @test */
    public function get_tipos_anexos()
    {
        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ])->preRegistro;

        $this->assertEquals(Anexo::find(1)->getOpcoesPreRegistro(), $service->admin()->getTiposAnexos(1));

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $this->assertEquals(null, $service->admin()->getTiposAnexos(2));

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ])->preRegistro;
        Anexo::find(3)->delete();

        $this->assertEquals(null, $service->admin()->getTiposAnexos(3));
    }

    /** @test */
    public function listar_sem_filtro()
    {
        $this->inserirControllerUserPolicyParaUnitTest('PreRegistroController');

        $service = new MediadorService;

        $user = $this->signInAsAdmin();

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ])->preRegistro;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ])->preRegistro;

        $resp = $service->getService('PreRegistro')->admin()->listar([], $service, $user);
        $this->assertEquals('string', gettype($resp['tabela']));
        $this->assertEquals(false, $resp['temFiltro']);
        $this->assertEquals(true, isset($resp['variaveis']));
        $this->assertEquals(true, $resp['resultados'] instanceof \Illuminate\Pagination\LengthAwarePaginator);
        $this->assertNotEquals('<i>(filtro ativo)</i>', $resp['variaveis']->continuacao_titulo);
    }

    /** @test */
    public function listar_com_filtro()
    {
        $this->inserirControllerUserPolicyParaUnitTest('PreRegistroController');

        $service = new MediadorService;

        $user = $this->signInAsAdmin();

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ])->preRegistro;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ])->preRegistro;

        $request = new \Illuminate\Http\Request;
        $request->replace(['regional' => 2]);
        $request = (object) $request->all();

        $resp = $service->getService('PreRegistro')->admin()->listar($request, $service, $user, true);
        $this->assertEquals(1, $resp['resultados']->total());
        $this->assertEquals('<i>(filtro ativo)</i>', $resp['variaveis']->continuacao_titulo);

        $request = new \Illuminate\Http\Request;
        $request->replace(['regional' => 'Todas', 'status' => PreRegistro::STATUS_CORRECAO]);
        $request = (object) $request->all();

        $resp = $service->getService('PreRegistro')->admin()->listar($request, $service, $user, true);
        $this->assertEquals(1, $resp['resultados']->total());
        $this->assertEquals('<i>(filtro ativo)</i>', $resp['variaveis']->continuacao_titulo);

        $request = new \Illuminate\Http\Request;
        $request->replace(['regional' => 'Todas', 'atendente' => 3]);
        $request = (object) $request->all();

        $resp = $service->getService('PreRegistro')->admin()->listar($request, $service, $user, true);
        $this->assertEquals(1, $resp['resultados']->total());
        $this->assertEquals('<i>(filtro ativo)</i>', $resp['variaveis']->continuacao_titulo);

        $request = new \Illuminate\Http\Request;
        $request->replace(['regional' => 7, 'atendente' => null, 'status' => 'Qualquer']);
        $request = (object) $request->all();

        $resp = $service->getService('PreRegistro')->admin()->listar($request, $service, $user, true);
        $this->assertEquals(0, $resp['resultados']->total());
        $this->assertEquals('<i>(filtro ativo)</i>', $resp['variaveis']->continuacao_titulo);
    }

    /** @test */
    public function view_admin()
    {
        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ])->preRegistro;

        $resp = $service->admin()->view(1);
        $this->assertEquals(PreRegistro::find(1)->cep, $resp['resultado']->cep);
        $this->assertEquals('<a href="'.route('preregistro.index').'" class="btn btn-primary mr-1">Lista dos Pré-registros</a>', $resp['variaveis']->btn_lista);

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $resp = $service->admin()->view(2);
        $this->assertEquals(PreRegistro::find(2)->status, $resp['resultado']->status);
        $this->assertEquals('<a href="'.route('preregistro.index').'" class="btn btn-primary mr-1">Lista dos Pré-registros</a>', $resp['variaveis']->btn_lista);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No query results for model [App\PreRegistro] 5');
        $service->admin()->view(5);
    }

    /** @test */
    public function buscar_admin()
    {
        $this->inserirControllerUserPolicyParaUnitTest('PreRegistroController');

        $service = new MediadorService;

        $user = $this->signInAsAdmin();

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ])->preRegistro;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create([
                'user_externo_id' => factory('App\UserExterno')
            ])
        ])->preRegistro;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create([
                'user_externo_id' => factory('App\UserExterno')
            ])
        ])->preRegistro;

        $request = new \Illuminate\Http\Request;
        $request->replace(['q' => $dadosUser->userExterno->cpf_cnpj]);
        $request = (object) $request->all();

        $resp = $service->getService('PreRegistro')->admin()->buscar($request->q, $user);
        $this->assertEquals(1, $resp['resultados']->total());

        $request = new \Illuminate\Http\Request;
        $request->replace(['q' => 'a']);
        $request = (object) $request->all();

        $resp = $service->getService('PreRegistro')->admin()->buscar($request->q, $user);
        $this->assertEquals(3, $resp['resultados']->total());

        $request = new \Illuminate\Http\Request;
        $request->replace(['q' => $dadosUser->userExterno->nome]);
        $request = (object) $request->all();

        $resp = $service->getService('PreRegistro')->admin()->buscar($request->q, $user);
        $this->assertEquals(1, $resp['resultados']->total());

        $request = new \Illuminate\Http\Request;
        $request->replace(['q' => null]);
        $request = (object) $request->all();

        $resp = $service->getService('PreRegistro')->admin()->buscar($request->q, $user);
        $this->assertEquals(3, $resp['resultados']->total());
    }

    /** @test */
    public function save_ajax_admin()
    {
        $service = new PreRegistroService;

        $user = $this->signInAsAdmin();

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create()
        ])->preRegistro;

        $resp = $service->admin()->saveAjaxAdmin(['acao' => 'justificar', 'campo' => 'cep', 'valor' => '09876-090'], 1, $user);
        $this->assertEquals($user->nome, $resp['user']);
    }

    /** @test */
    public function save_ajax_admin_exception_atendente_editar()
    {
        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não autorizado a editar o pré-registro sendo elaborado, aguardando correção ou finalizado');
        $service->admin()->saveAjaxAdmin(['acao' => 'justificar', 'campo' => 'cep'], 1, $dadosUser->user);
    }

    /** @test */
    public function update_status()
    {
        $service = new PreRegistroService;

        $user = $this->signInAsAdmin();

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_correcao')->create()
        ])->preRegistro;

        $this->assertDatabaseHas('anexos', ['pre_registro_id' => 1]);

        $this->assertEquals([
            'message' => '<i class="icon fa fa-check"></i>Pré-registro com a ID: 1 foi atualizado para "' . PreRegistro::STATUS_NEGADO . '" com sucesso', 
            'class' => 'alert-success'
        ], $service->admin()->updateStatus(1, $user, PreRegistro::STATUS_NEGADO));
        $this->assertDatabaseMissing('anexos', ['pre_registro_id' => 1]);
    }

    /** @test */
    public function update_status_exception_atendente_editar()
    {
        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ])->preRegistro;

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não permitido atualizar o status do pré-registro já finalizado (Aprovado ou Negado)');
        $service->admin()->updateStatus(1, $dadosUser->user, PreRegistro::STATUS_APROVADO);
    }

    /** @test */
    public function upload_doc()
    {
        Storage::fake('local');

        $service = new PreRegistroService;

        $user = $this->signInAsAdmin();

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $this->assertEquals([
            'message' => '<i class="icon fas fa-times"></i> O pré-registro precisa estar aprovado para anexar documento.',
            'class' => 'alert-danger'
        ], $service->admin()->uploadDoc(1, UploadedFile::fake()->image('random.jpg')->size(300), 'boleto'));

        $dadosUser = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ])->preRegistro;

        $this->assertEquals([
            'message' => '<i class="icon fa fa-check"></i> Boleto anexado com sucesso!',
            'class' => 'alert-success'
        ], $service->admin()->uploadDoc(2, UploadedFile::fake()->image('random.jpg')->size(300), 'boleto'));
    }

    /** @test */
    public function get_justificativa_admin()
    {
        $this->inserirControllerUserPolicyParaUnitTest('PreRegistroController');

        $service = new PreRegistroService;

        $user = $this->signInAsAdmin();

        $dadosUser = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $now = now()->subDay()->format('Y-m-d H:i:s');

        $justificativa = json_decode(PreRegistro::first()->justificativa, true)['nome_social'];
        $this->assertEquals([
            'justificativa' => $justificativa,
            'data_hora' => null,
        ], $service->admin()->getJustificativa($user, 1, 'nome_social'));

        $this->assertEquals([
            'justificativa' => 'Sem justificativa',
            'data_hora' => null,
        ], $service->admin()->getJustificativa($user, 1, 'teste'));

        $resp = $service->admin()->getJustificativa($user, 1, 'cep', urlencode($now));
        $this->assertNotEquals(null, $resp['justificativa']);
        $this->assertEquals(formataData($now), $resp['data_hora']);

        $dadosUser = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $now = now()->subDay()->format('Y-m-d H:i:s');

        $resp = $service->admin()->getJustificativa($user, 1, 'cep', $now);
        $this->assertNotEquals(null, $resp['justificativa']);
        $this->assertEquals(formataData($now), $resp['data_hora']);
    }

    /** @test */
    public function get_justificativa_exception_atendente_sem_permissao()
    {
        $this->inserirControllerUserPolicyParaUnitTest('PreRegistroController');

        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $user = $this->signInAsAdmin();

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não permitido visualizar a justificativa do pré-registro na área administrativa sem permissão!');
        $service->admin()->getJustificativa($dadosUser->user, 1, 'nome_social');
    }

    /** @test */
    public function get_justificativa_exception_user_externo_diferente_do_pre_registro()
    {
        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $user = factory('App\UserExterno')->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não permitido visualizar a justificativa do pré-registro de outro usuário!');
        $service->admin()->getJustificativa($user, 1, 'nome_social');
    }

    /** @test */
    public function get_justificativa_exception_contabil_diferente_do_pre_registro()
    {
        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $user = factory('App\Contabil')->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não permitido visualizar a justificativa do pré-registro de outro usuário!');
        $service->admin()->getJustificativa($user, 1, 'nome_social');
    }

    /** @test */
    public function get_justificativa_exception_user_externo_com_pre_registro_finalizado()
    {
        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $user = $dadosUser->userExterno;

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não permitido visualizar a justificativa do pré-registro finalizado!');
        $service->admin()->getJustificativa($user, 1, 'nome_social');
    }

    /** @test */
    public function get_justificativa_exception_contabil_com_pre_registro_finalizado()
    {
        $service = new PreRegistroService;

        $dadosUser = factory('App\PreRegistroCpf')->states('justificativas')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ])->preRegistro;

        $user = $dadosUser->contabil;

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Não permitido visualizar a justificativa do pré-registro finalizado!');
        $service->admin()->getJustificativa($user, 1, 'nome_social');
    }

    /** @test */
    public function executar_rotina()
    {
        Storage::fake('local');

        $service = new PreRegistroService;

        // Excluir arquivos
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ])->preRegistro;
        $dados->anexos()->first()->delete();

        $anexo1 = $dados->salvarAjax([
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [
                UploadedFile::fake()->image('random.jpg')->size(300),
            ],
        ]);
        $dados->update(['updated_at' => now()->subMonth()->toDateString()]);
        Storage::disk('local')->assertExists($anexo1->path);

        // Excluir arquivos
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;
        $dados->anexos()->first()->delete();

        $anexo2 = $dados->salvarAjax([
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [
                UploadedFile::fake()->image('random.jpg')->size(300),
            ],
        ]);
        $dados->update(['updated_at' => now()->subMonths(2)->toDateString()]);
        Storage::disk('local')->assertExists($anexo2->path);

        // Excluir arquivos
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        $dados->anexos()->first()->delete();

        $anexo3 = $dados->salvarAjax([
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [
                UploadedFile::fake()->image('random.jpg')->size(300),
            ],
        ]);
        $dados->update(['updated_at' => now()->subMonths(2)->toDateString()]);
        Storage::disk('local')->assertExists($anexo3->path);

        // Manter arquivos
        $dados = factory('App\PreRegistroCpf')->create()->preRegistro;
        $dados->anexos()->first()->delete();

        $anexo4 = $dados->salvarAjax([
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [
                UploadedFile::fake()->image('random.jpg')->size(300),
            ],
        ]);
        Storage::disk('local')->assertExists($anexo4->path);

        $service->admin()->executarRotina();

        // Teste somente do registro da última linha no log.
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: ';
        $txt = $inicio . '[Rotina Portal] - Pré-Registro - Rotina de exclusão de arquivos do pré-registro: ';
        $txt .= 'pré-registro com ID 3 possuía 1 e agora possui 0 no Storage e 0 no BD.';
        $this->assertStringContainsString($txt, $log);

        Storage::disk('local')->assertMissing($anexo1->path);
        Storage::disk('local')->assertMissing($anexo2->path);
        Storage::disk('local')->assertMissing($anexo3->path);
        Storage::disk('local')->assertExists($anexo4->path);

        $service->admin()->executarRotina();

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: ';
        $txt = $inicio . '[Rotina Portal] - Pré-Registro - Rotina de exclusão de arquivos do pré-registro: nenhuma alteração.';
        $this->assertStringContainsString($txt, $log);
    }
}
