<?php

namespace Tests\Feature;

use App\Permissao;
use Tests\TestCase;
use App\PeriodoFiscalizacao;
use App\DadoFiscalizacao;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FiscalizacaoTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES DE AUTORIZAÇÃO NO ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $fiscal = factory('App\PeriodoFiscalizacao')->create();

        $this->get(route('fiscalizacao.index'))->assertRedirect(route('login'));
        $this->get(route('fiscalizacao.createperiodo'))->assertRedirect(route('login'));
        $this->get(route('fiscalizacao.editperiodo', $fiscal->id))->assertRedirect(route('login'));
        $this->get(route('fiscalizacao.busca'))->assertRedirect(route('login'));
        $this->post(route('fiscalizacao.storeperiodo'))->assertRedirect(route('login'));
        $this->put(route('fiscalizacao.updatestatus', $fiscal->id))->assertRedirect(route('login'));
        $this->put(route('fiscalizacao.updateperiodo', $fiscal->id))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $fiscal = factory('App\PeriodoFiscalizacao')->create();
        factory('App\DadoFiscalizacao', 13)->create();
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
                'idperiodo' => $fiscal->id
        ])['final'];    

        $this->get(route('fiscalizacao.index'))->assertForbidden();
        $this->get(route('fiscalizacao.createperiodo'))->assertForbidden();
        $this->get(route('fiscalizacao.editperiodo', $fiscal->id))->assertForbidden();
        $this->get(route('fiscalizacao.busca'))->assertForbidden();

        $fiscal->periodo = '2021';
        $this->post(route('fiscalizacao.storeperiodo'), $fiscal->toArray())->assertForbidden();
        $this->put(route('fiscalizacao.updatestatus', $fiscal->id))->assertForbidden();
        $this->put(route('fiscalizacao.updateperiodo', $fiscal->id), $dados)->assertForbidden();
    }
    
    /** @test 
     * 
     * Usuário sem autorização não pode listar periodos de fiscalização.
    */
    public function non_authorized_users_cannot_list_periodo_fiscalizacao()
    {
        $this->signIn();

        $this->get(route("fiscalizacao.index"))->assertForbidden();  
    }

    /** @test 
     * 
     * Usuário sem autorização não pode criar periodo de fiscalização.
    */
    public function non_authorized_users_cannot_create_periodo_fiscalizacao()
    {
        $this->signIn();

        $atributos = factory("App\PeriodoFiscalizacao")->raw();

        $this->get(route("fiscalizacao.createperiodo"))->assertForbidden();
        $this->post(route("fiscalizacao.storeperiodo", $atributos))->assertForbidden();

        $this->assertDatabaseMissing("periodos_fiscalizacao", ["periodo" => $atributos["periodo"]]);  
    }

    /** @test 
     * 
     * Usuário sem autorização não pode editar periodo de fiscalização.
    */
    public function non_authorized_users_cannot_edit_periodo_fiscalizacao()
    {
        $this->signIn();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        factory('App\DadoFiscalizacao', 13)->create();
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
                'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  

        $this->get(route("fiscalizacao.editperiodo", $periodoFiscalizacao->id))->assertForbidden();
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)->assertForbidden();

        for($i = 0; $i < 13; $i++)
            $this->assertDatabaseMissing("dados_fiscalizacao", 
                array_combine($dados['dados'][$i]['campo'], $dados['dados'][$i]['valor'])
            );
    }

    /** @test 
     * 
     * Usuário sem autorização não pode publicar periodo de fiscalização.
    */
    public function non_authorized_users_cannot_publish_periodo_fiscalizacao()
    {
        $this->signIn();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();

        $this->put(route("fiscalizacao.updatestatus", $periodoFiscalizacao->id))->assertForbidden();
            
        $this->assertEquals(PeriodoFiscalizacao::find($periodoFiscalizacao->id)->status, 0);
    }

    /** @test 
     * 
     * Usuário sem autorização não pode buscar periodo de fiscalização.
    */
    public function non_authorized_users_cannot_search_periodo_fiscalizacao()
    {
        $this->signIn();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);

        $this->get(route("fiscalizacao.busca", ["q" => "2020"]))->assertForbidden();
    }

    /** @test 
     * 
     * Usuário com autorização pode listar periodos de fiscalização.
    */
    public function authorized_users_can_list_periodo_fiscalizacao()
    {
        $this->signInAsAdmin();

        $this->get(route("fiscalizacao.index"))->assertOk();  
    }

    /** @test 
     * 
     * Usuário com autorização pode criar periodo de fiscalização.
    */
    public function authorized_users_can_create_periodo_fiscalizacao()
    {
        $this->signInAsAdmin();

        $atributos = factory("App\PeriodoFiscalizacao")->raw();

        $this->get(route("fiscalizacao.createperiodo"))->assertOk();
        $this->post(route("fiscalizacao.storeperiodo", $atributos));

        $this->assertDatabaseHas("periodos_fiscalizacao", [
            "periodo" => $atributos["periodo"]
        ]);  
        $this->assertEquals(DadoFiscalizacao::count(), 1);
    }

    /** @test */
    public function sum_total_periodo_fiscalizacao()
    {
        $this->signInAsAdmin();

        $periodo = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            'idperiodo' => $periodo->id
        ])->makeHidden(['id', 'idperiodo', 'idregional', 'regional', 'created_at', 'updated_at']);

        $totalFinal = 0;
        foreach($dadoFiscalizacao as $val)
        {
            $total = 0;

            $total += $val->processofiscalizacaopf;
            $total += $val->processofiscalizacaopj;
            $total += $val->registroconvertidopf;
            $total += $val->registroconvertidopj;
            $total += $val->processoverificacao;
            $total += $val->dispensaregistro;
            $total += $val->notificacaort;
            $total += $val->orientacaorepresentada;
            $total += $val->orientacaorepresentante;
            $total += $val->cooperacaoinstitucional;
            $total += $val->autoconstatacao;
            $total += $val->autosdeinfracao;
            $total += $val->multaadministrativa;
            $total += $val->orientacaocontabil;
            $total += $val->oficioprefeitura;
            $total += $val->oficioincentivo;
            $total += $val->notificacandidatoeleicao;

            $totalFinal += $total;
            $this->assertEquals($val->somaTotal(), $total);
        }

        $this->assertEquals($periodo->somaTotal(), $totalFinal);
    }

    /** @test */
    public function sum_by_actions_periodo_fiscalizacao()
    {
        $this->signInAsAdmin();

        $periodo = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            'idperiodo' => $periodo->id
        ])->makeHidden(['id', 'idperiodo', 'idregional', 'regional', 'created_at', 'updated_at']);

        $acoes = array_fill_keys(DadoFiscalizacao::campos(), 0);
        unset($acoes['Processos de Fiscalização<span class="invisible">F</span>']);
        unset($acoes['Processos de Fiscalização<span class="invisible">J</span>']);
        unset($acoes['Registros Convertidos<span class="invisible">F</span>']);
        unset($acoes['Dispensa de Registro (de ofício)']);
        unset($acoes['Orientações aos representantes']);
        unset($acoes['Auto de Constatação']);

        foreach($dadoFiscalizacao as $val)
        {
            // $acoes['Processos de Fiscalização<span class="invisible">F</span>'] += $val->processofiscalizacaopf;
            // $acoes['Processos de Fiscalização<span class="invisible">J</span>'] += $val->processofiscalizacaopj;
            // $acoes['Registros Convertidos<span class="invisible">F</span>'] += $val->registroconvertidopf;
            $acoes['Registros Convertidos<span class="invisible">J</span>'] += $val->registroconvertidopj;
            $acoes['Processos de Verificação'] += $val->processoverificacao;
            // $acoes['Dispensa de Registro (de ofício)'] += $val->dispensaregistro;
            $acoes['Notificações de RT'] += $val->notificacaort;
            $acoes['Orientações às representadas'] += $val->orientacaorepresentada;
            // $acoes['Orientações aos representantes'] += $val->orientacaorepresentante;
            $acoes['Diligências externas'] += $val->cooperacaoinstitucional;
            // $acoes['Auto de Constatação'] += $val->autoconstatacao;
            $acoes['Autos de Infração'] += $val->autosdeinfracao;
            $acoes['Multa Administrativa'] += $val->multaadministrativa;
            $acoes['Orientação às contabilidades'] += $val->orientacaocontabil;
            $acoes['Ofício às prefeituras'] += $val->oficioprefeitura;
            $acoes['Ofício de incentivo a contratação de representantes comerciais'] += $val->oficioincentivo;
            $acoes['Notificação Candidatos Eleições'] += $val->notificacandidatoeleicao;
        }

        $this->assertEquals($periodo->somaTotalPorAcao(), $acoes);
    }

    /** @test 
     * 
     * Usuário com autorização pode editar periodo de fiscalização.
    */
    public function authorized_users_can_edit_dados_fiscalizacao()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  

        $this->get(route("fiscalizacao.editperiodo", $periodoFiscalizacao->id))->assertOk();
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertDatabaseHas("dados_fiscalizacao", 
                array_combine($dados['dados'][$i]['campo'], $dados['dados'][$i]['valor'])
            );
            $this->assertDatabaseMissing("dados_fiscalizacao", 
                array_combine(array_keys($dadoFiscalizacao->get($i)->toArray()), array_values($dadoFiscalizacao->get($i)->toArray()))
            );
        }
    }

    /** @test 
     * 
     * Usuário com autorização pode editar periodo de fiscalização.
    */
    public function authorized_users_can_edit_one_input_dados_fiscalizacao()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  

        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
                $dados['dados'][$key]['valor'] = array_values($temp);
        }

        $indice = 11;
        $valor = 0;
        $dados['dados'][$indice]['valor'][3] = $valor;
        $nomeCampo = $dados['dados'][$indice]['campo'][3];
        $dadoFiscalizacao->get($indice)->update([$nomeCampo => $valor]);

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_processofiscalizacaopf()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'processofiscalizacaopf')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->processofiscalizacaopf);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'processofiscalizacaopf' => $dadoFiscalizacao->get($i)->processofiscalizacaopf
            ]);
            $dadoFiscalizacao->get($i)->update(['processofiscalizacaopf' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_processofiscalizacaopj()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'processofiscalizacaopj')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->processofiscalizacaopj);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'processofiscalizacaopj' => $dadoFiscalizacao->get($i)->processofiscalizacaopj
            ]);
            $dadoFiscalizacao->get($i)->update(['processofiscalizacaopj' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_registroconvertidopf()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'registroconvertidopf')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->registroconvertidopf);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'registroconvertidopf' => $dadoFiscalizacao->get($i)->registroconvertidopf
            ]);
            $dadoFiscalizacao->get($i)->update(['registroconvertidopf' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_registroconvertidopj()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'registroconvertidopj')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->registroconvertidopj);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'registroconvertidopj' => $dadoFiscalizacao->get($i)->registroconvertidopj
            ]);
            $dadoFiscalizacao->get($i)->update(['registroconvertidopj' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_processoverificacao()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'processoverificacao')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->processoverificacao);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'processoverificacao' => $dadoFiscalizacao->get($i)->processoverificacao
            ]);
            $dadoFiscalizacao->get($i)->update(['processoverificacao' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_dispensaregistro()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'dispensaregistro')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->dispensaregistro);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'dispensaregistro' => $dadoFiscalizacao->get($i)->dispensaregistro
            ]);
            $dadoFiscalizacao->get($i)->update(['dispensaregistro' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_notificacaort()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'notificacaort')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->notificacaort);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'notificacaort' => $dadoFiscalizacao->get($i)->notificacaort
            ]);
            $dadoFiscalizacao->get($i)->update(['notificacaort' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_orientacaorepresentada()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'orientacaorepresentada')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->orientacaorepresentada);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'orientacaorepresentada' => $dadoFiscalizacao->get($i)->orientacaorepresentada
            ]);
            $dadoFiscalizacao->get($i)->update(['orientacaorepresentada' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_orientacaorepresentante()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'orientacaorepresentante')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->orientacaorepresentante);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'orientacaorepresentante' => $dadoFiscalizacao->get($i)->orientacaorepresentante
            ]);
            $dadoFiscalizacao->get($i)->update(['orientacaorepresentante' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_cooperacaoinstitucional()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'cooperacaoinstitucional')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->cooperacaoinstitucional);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'cooperacaoinstitucional' => $dadoFiscalizacao->get($i)->cooperacaoinstitucional
            ]);
            $dadoFiscalizacao->get($i)->update(['cooperacaoinstitucional' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_autoconstatacao()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'autoconstatacao')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->autoconstatacao);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'autoconstatacao' => $dadoFiscalizacao->get($i)->autoconstatacao
            ]);
            $dadoFiscalizacao->get($i)->update(['autoconstatacao' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_autosdeinfracao()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'autosdeinfracao')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->autosdeinfracao);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'autosdeinfracao' => $dadoFiscalizacao->get($i)->autosdeinfracao
            ]);
            $dadoFiscalizacao->get($i)->update(['autosdeinfracao' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_multaadministrativa()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'multaadministrativa')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->multaadministrativa);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'multaadministrativa' => $dadoFiscalizacao->get($i)->multaadministrativa
            ]);
            $dadoFiscalizacao->get($i)->update(['multaadministrativa' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_orientacaocontabil()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'orientacaocontabil')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->orientacaocontabil);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'orientacaocontabil' => $dadoFiscalizacao->get($i)->orientacaocontabil
            ]);
            $dadoFiscalizacao->get($i)->update(['orientacaocontabil' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_oficioprefeitura()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'oficioprefeitura')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->oficioprefeitura);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'oficioprefeitura' => $dadoFiscalizacao->get($i)->oficioprefeitura
            ]);
            $dadoFiscalizacao->get($i)->update(['oficioprefeitura' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_oficioincentivo()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'oficioincentivo')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->oficioincentivo);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'oficioincentivo' => $dadoFiscalizacao->get($i)->oficioincentivo
            ]);
            $dadoFiscalizacao->get($i)->update(['oficioincentivo' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
    */
    public function authorized_users_can_edit_notificacandidatoeleicao()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        
        foreach($dados['dados'] as $key => $value)
        {
            $temp = $dadoFiscalizacao->get($key)->makeHidden(['id', 'idperiodo', 'idregional', 'created_at', 'updated_at'])->toArray();
            foreach($temp as $chave => $valor)
            {
                if($chave == 'notificacandidatoeleicao')
                    $temp[$chave] = 0;
                $dados['dados'][$key]['valor'] = array_values($temp);
            }
        }
            
        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);

        for($i = 0; $i < 13; $i++)
        {
            $this->assertEquals(0, PeriodoFiscalizacao::find(1)->dadoFiscalizacao->get($i)->notificacandidatoeleicao);
            $this->assertDatabaseMissing("dados_fiscalizacao", [
                'notificacandidatoeleicao' => $dadoFiscalizacao->get($i)->notificacandidatoeleicao
            ]);
            $dadoFiscalizacao->get($i)->update(['notificacandidatoeleicao' => 0]);
            $this->assertDatabaseHas("dados_fiscalizacao", $dadoFiscalizacao->get($i)->toArray());
        }
    }

    /** @test 
     * 
     * Usuário com autorização pode publicar periodo de fiscalização.
    */
    public function authorized_users_can_publish_periodo_fiscalizacao()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);

        $this->put(route("fiscalizacao.updatestatus", $periodoFiscalizacao->id));
            
        $this->assertEquals(PeriodoFiscalizacao::find($periodoFiscalizacao->id)->status, 1);
    }

    /** @test 
     * 
     * Usuário com autorização pode buscar periodo de fiscalização.
    */
    public function authorized_users_can_search_periodo_fiscalizacao()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020
        ]);

        $this->get(route("fiscalizacao.busca", ["q" => "2020"]))->assertSeeText($periodoFiscalizacao->periodo);
    }

    /** @test 
     * 
     * Sistema não deve permitir criação de periodos repetidos.
    */
    public function cannot_create_duplicated_periodo()
    {
        $this->signInAsAdmin();

        factory("App\PeriodoFiscalizacao")->create();

        $atributos = factory("App\PeriodoFiscalizacao")->raw();

        $this->post(route("fiscalizacao.storeperiodo", $atributos))->assertSessionHasErrors("periodo");

        $this->assertEquals(PeriodoFiscalizacao::count(), 1);
    }

    /** @test 
     * 
     * Sistema não deve permitir criação de periodos com valores de periodo inválido.
    */
    public function cannot_create_periodo_with_invalid_periodo()
    {
        $this->signInAsAdmin();

        $atributos = factory("App\PeriodoFiscalizacao")->raw([
            "periodo" => 0
        ]);

        $this->post(route("fiscalizacao.storeperiodo", $atributos))
        ->assertSessionHasErrors("periodo");

        $this->assertEquals(PeriodoFiscalizacao::count(), 0);
    }

    /** @test 
     * 
    */
    public function cannot_create_periodo_with_less_than_4_chars()
    {
        $this->signInAsAdmin();

        $atributos = factory("App\PeriodoFiscalizacao")->raw([
            "periodo" => 202
        ]);

        $this->post(route("fiscalizacao.storeperiodo", $atributos))
        ->assertSessionHasErrors("periodo");

        $this->assertEquals(PeriodoFiscalizacao::count(), 0);
    }

    /** @test 
     * 
    */
    public function cannot_create_periodo_with_date_before_2000()
    {
        $this->signInAsAdmin();

        $atributos = factory("App\PeriodoFiscalizacao")->raw([
            "periodo" => 1999
        ]);

        $this->post(route("fiscalizacao.storeperiodo", $atributos))
        ->assertSessionHasErrors("periodo");

        $this->assertEquals(PeriodoFiscalizacao::count(), 0);
    }

    /** @test 
     * 
    */
    public function cannot_create_periodo_without_date_format()
    {
        $this->signInAsAdmin();

        $atributos = factory("App\PeriodoFiscalizacao")->raw([
            "periodo" => '202A'
        ]);

        $this->post(route("fiscalizacao.storeperiodo", $atributos))
        ->assertSessionHasErrors("periodo");

        $this->assertEquals(PeriodoFiscalizacao::count(), 0);
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_with_array_less_than_13()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        unset($dados['dados'][12]);

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_without_array_format()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), ['dados' => 'teste'])
        ->assertSessionHasErrors("dados");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_without_array()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), ['dados' => null])
        ->assertSessionHasErrors("dados");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_with_wrong_id()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id,
        ])['final'];  
        $dados['dados'][0]['id'] = 25;

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.id");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_with_duplicated_id()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id,
        ])['final'];  
        $dados['dados'][0]['id'] = 2;
        $dados['dados'][2]['id'] = 2;

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.id");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_with_duplicated_campo()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id,
        ])['final'];  
        $dados['dados'][0]['campo'][3] = $dados['dados'][0]['campo'][5];
        $dados['dados'][2]['campo'][10] = $dados['dados'][2]['campo'][7];

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.campo");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_without_id()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id,
        ])['final'];  
        $dados['dados'][0]['id'] = null;

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.id");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_with_campo_array_less_than_13()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        unset($dados['dados'][0]['campo'][12]);

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.campo");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_without_campo_array_format()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        $dados['dados'][0]['campo'] = 'teste';

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.campo");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_without_campo_array()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        $dados['dados'][0]['campo'] = null;

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.campo");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_without_value_in_campo_array()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        $dados['dados'][0]['campo'][0] = null;

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.campo.*");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_with_wrong_value_in_campo_array()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        $dados['dados'][0]['campo'][0] = 'teste';

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.campo.*");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_with_valor_array_less_than_13()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        unset($dados['dados'][0]['valor'][12]);

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.valor");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_without_valor_array_format()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        $dados['dados'][0]['valor'] = 'teste';

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.valor");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_without_valor_array()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        $dados['dados'][0]['valor'] = null;

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.valor");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_without_value_in_valor_array()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        $dados['dados'][0]['valor'][0] = null;

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.valor.*");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_with_valor_not_integer()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        $dados['dados'][0]['valor'][0] = 'ABC';

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.valor.*");
    }

    /** @test 
     * 
    */
    public function cannot_edit_dados_fiscalizacao_with_valor_more_than_999999999()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  
        $dados['dados'][0]['valor'][0] = 9999999991;

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados)
        ->assertSessionHasErrors("dados.*.valor.*");
    }

    /** @test 
     * 
    */
    public function view_inputs_when_edit_periodo_fiscalizacao()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        factory('App\DadoFiscalizacao')->create();
        factory('App\DadoFiscalizacao')->create([
                'idperiodo' => $periodoFiscalizacao->id
        ]);  

        $campos = array_keys(PeriodoFiscalizacao::with('dadoFiscalizacao')
            ->find(1)
            ->dadoFiscalizacao
            ->get(0)
            ->toArray()
        );
        unset($campos[array_search('id', $campos)]);
        unset($campos[array_search('idregional', $campos)]);
        unset($campos[array_search('idperiodo', $campos)]);
        unset($campos[array_search('created_at', $campos)]);
        unset($campos[array_search('updated_at', $campos)]);

        $this->get(route("fiscalizacao.editperiodo", $periodoFiscalizacao->id))
        ->assertSeeInOrder($campos);
    }

    /** @test */
    public function log_is_generated_when_periodo_is_created()
    {
        $user = $this->signInAsAdmin();
        $atributos = factory("App\PeriodoFiscalizacao")->raw();
        $this->post(route("fiscalizacao.storeperiodo", $atributos));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') criou *período fiscalização* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function log_is_generated_when_status_periodo_is_updated()
    {
        $user = $this->signInAsAdmin();
        $atributos = factory("App\PeriodoFiscalizacao")->create();
        $this->put(route("fiscalizacao.updatestatus", $atributos->id));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.')  atualizou a publicação do período da fiscalização com o status  *publicado* (id: 1)';
        $this->assertStringContainsString($txt, $log);

        $this->put(route("fiscalizacao.updatestatus", $atributos->id));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.')  atualizou a publicação do período da fiscalização com o status  *não publicado* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function log_is_generated_when_periodo_is_updated()
    {
        $user = $this->signInAsAdmin();
        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dadoFiscalizacao = factory("App\DadoFiscalizacao", 13)->create([
            "idperiodo" => $periodoFiscalizacao->id
        ]);
        $dados['dados'] = factory('App\DadoFiscalizacao')->state('raw_request')->make([
            'idperiodo' => $periodoFiscalizacao->id
        ])['final'];  

        $this->put(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), $dados);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') atualizou *dados do período da fiscalização* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** 
     * =======================================================================================================
     * TESTES NO PORTAL
     * =======================================================================================================
     */

    /** @test 
     * 
     * Testando acesso a página do mapa.
    */
    public function access_mapas_from_portal()
    {
        $this->get(route("fiscalizacao.mapa"))->assertOk();
    }

    /** @test 
     * 
    */
    public function view_periodos_and_msg_when_access_mapas_from_portal()
    {
        $periodo = factory("App\PeriodoFiscalizacao")->create([
            "status" => 1
        ]);
        factory("App\DadoFiscalizacao")->create([
            "idperiodo" => $periodo->id
        ]);

        $this->get(route("fiscalizacao.mapa"))
        ->assertOk()
        ->assertSeeText($periodo->periodo)
        ->assertSee('<h5 class="p-0">Total em '.$periodo->periodo.'</h5>')
        ->assertSeeText('Clique em uma das regionais para obter mais detalhes sobre fiscalização do ano ' . $periodo->periodo)
        ->assertSeeText(onlyDate($periodo->dadoFiscalizacao->get(0)->updated_at));
    }

    /** @test 
     * 
     * Se nenhum periodo estiver publicado, mapa deve ser aberto com o combobox desabilitado e
     * com o valor "Indisponível".
    */
    public function access_periodo_mapas_from_portal_with_no_periodo_published()
    {
        $this->get(route("fiscalizacao.mapa"))
            ->assertOk()
            ->assertSee("Indisponível");
    }

    /** @test 
     * 
     * Se algum periodo estiver publicado, mapa deve ser aberto com o combobox habilitado e
     * com o valores dos periodos publicados.
    */
    public function access_periodo_mapas_from_portal_with_periodo_published()
    {
        $periodo2020 = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020, "status" => 1
        ]);
        $periodo2021 = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2021, "status" => 1
        ]);

        factory("App\DadoFiscalizacao")->create([
            "idperiodo" => $periodo2020->id
        ]);
        factory("App\DadoFiscalizacao")->create([
            "idperiodo" => $periodo2021->id
        ]);

        $this->get(route("fiscalizacao.mapa"))
            ->assertOk()
            ->assertSee("2020")
            ->assertSee("2021")
            ->assertSeeText(onlyDate($periodo2021->dadoFiscalizacao->get(0)->updated_at));
    }

    /** @test 
     * 
     * periodos publicados devem mostrar seus respectivos dados de fiscalização.
     * Página padrão sempre mostrar o maior periodo.
    */
    public function access_periodo_mapas_from_portal_with_dados()
    {
        $fiscal = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020, 
            "status" => 1
        ]);
        $dados = factory("App\DadoFiscalizacao")->create([
            'idperiodo' => $fiscal->id
        ]);

        $this->get(route("fiscalizacao.mapaperiodo", $fiscal->id))
            ->assertOk()
            ->assertSee('<h5 class="p-0">Total em '.$fiscal->periodo.'</h5>')
            ->assertDontSeeText($dados->processofiscalizacaopf)
            ->assertDontSeeText($dados->processofiscalizacaopj)
            ->assertDontSeeText($dados->registroconvertidopf)
            ->assertSeeText($dados->registroconvertidopj)
            ->assertSeeText($dados->processoverificacao)
            ->assertDontSeeText($dados->dispensaregistro)
            ->assertSeeText($dados->notificacaort)
            ->assertSeeText($dados->orientacaorepresentada)
            ->assertDontSeeText($dados->orientacaorepresentante)
            ->assertSeeText($dados->cooperacaoinstitucional)
            ->assertDontSeeText($dados->autoconstatacao)
            ->assertSeeText($dados->autosdeinfracao)
            ->assertSeeText($dados->multaadministrativa)
            ->assertSeeText($dados->notificacandidatoeleicao)
            ->assertSeeText(onlyDate($dados->updated_at));
    }

    /** @test 
     * 
     * Múltiplos periodos publicados devem mostrar seus respectivos dados de fiscalização.
    */
    public function access_periodo_mapas_from_portal_with_multiple_periodos_and_dados()
    {
        $periodo2020 = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020,
            "status" => 1
        ]);
        $periodo2021 = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2021,
            "status" => 1
        ]);

        factory("App\DadoFiscalizacao")->create([
            "idperiodo" => $periodo2020->id,
            "notificacandidatoeleicao" => 11111
        ]);
        factory("App\DadoFiscalizacao")->create([
            "idperiodo" => $periodo2021->id,
            "notificacandidatoeleicao" => 22222
        ]);

        $this->get(route("fiscalizacao.mapaperiodo", $periodo2020->id))
            ->assertOk()
            ->assertSee('<h5 class="p-0">Total em '.$periodo2020->periodo.'</h5>')
            ->assertDontSee('<h5 class="p-0">Total em '.$periodo2021->periodo.'</h5>')
            ->assertSee("11111")
            ->assertDontSee("22222");

        $this->get(route("fiscalizacao.mapaperiodo", $periodo2021->id))
            ->assertOk()
            ->assertDontSee('<h5 class="p-0">Total em '.$periodo2020->periodo.'</h5>')
            ->assertSee('<h5 class="p-0">Total em '.$periodo2021->periodo.'</h5>')
            ->assertDontSee("11111")
            ->assertSee("22222");
    }

    /** @test 
     * 
     * periodos publicados devem mostrar seus respectivos dados de fiscalização.
     * Página padrão sempre mostrar o maior periodo.
    */
    public function cannot_access_periodo_mapas_from_portal_with_dados_with_wrong_id()
    {
        $fiscal = factory("App\PeriodoFiscalizacao")->create([
            "periodo" => 2020, 
            "status" => 1
        ]);
        $dados = factory("App\DadoFiscalizacao")->create([
            'idperiodo' => $fiscal->id
        ]);

        $this->get(route("fiscalizacao.mapaperiodo", 22))
            ->assertNotFound();
    }
}