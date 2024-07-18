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

class PreRegistroTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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

        $dados->confereJustificadosSubmit($request);
        $this->assertNotEquals($antigoAnexos, json_decode($dados->campos_espelho, true)['path']);
        $this->assertNotEquals('1, 2', json_decode($dados->campos_editados, true)['path']);
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

    /** 
     * =======================================================================================================
     * TESTES SERVICE PREREGISTROSERVICE
     * =======================================================================================================
     */

    /** 
     * =======================================================================================================
     * TESTES SUB SERVICE PREREGISTROADMINSUBSERVICE
     * =======================================================================================================
     */
}
