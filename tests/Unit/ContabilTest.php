<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Contabil;
use App\Services\UserExternoService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use App\Notifications\UserExternoResetPasswordNotification;

class ContabilTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** 
     * =======================================================================================================
     * TESTES MODEL
     * =======================================================================================================
     */

    /** @test */
    public function enviar_email_reset_password()
    {
        Mail::fake();

        $dados = factory('App\Contabil')->create();
        $token = str_random(32);

        $dados->sendPasswordResetNotification($token);

        Mail::assertQueued(UserExternoResetPasswordNotification::class, function ($mail) use($token){
            return $mail->token == $token;
        });
    }

    /** @test */
    public function possui_login()
    {
        $dados = factory('App\Contabil')->create();

        $this->assertEquals(true, $dados->possuiLogin());

        $dados = factory('App\Contabil')->states('sem_login')->create();

        $this->assertEquals(false, $dados->possuiLogin());
    }

    /** @test */
    public function possui_login_ativo()
    {
        $dados = factory('App\Contabil')->create();

        $this->assertEquals(true, $dados->possuiLoginAtivo());

        $dados = factory('App\Contabil')->states('sem_login')->create();

        $this->assertEquals(false, $dados->possuiLoginAtivo());
    }

    /** @test */
    public function pode_ativar()
    {
        // Externo com login
        $dados = factory('App\Contabil')->create([
            'ativo' => 0
        ]);

        $this->assertEquals(true, $dados->podeAtivar());

        // Externo sem login
        $dados = factory('App\Contabil')->states('sem_login')->create();

        $this->assertEquals(false, $dados->podeAtivar());

        // Externo após 24h
        $dados = factory('App\Contabil')->create([
            'ativo' => 0
        ]);
        $dados->update(['updated_at' => $dados->updated_at->subDays(2)]);

        $this->assertEquals(false, $dados->fresh()->podeAtivar());

        // Externo excluído dentro das 24h
        factory('App\Contabil')->create([
            'ativo' => 0
        ])->delete();

        $this->assertEquals(true, Contabil::onlyTrashed()->first()->podeAtivar());
    }

    /** @test */
    public function pre_registros()
    {
        $dados = factory('App\Contabil')->create();

        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ]);
        factory('App\PreRegistroCpf')->create();

        $this->assertEquals(2, Contabil::first()->preRegistros()->count());

        Contabil::first()->preRegistros->get(1)->delete();

        $this->assertEquals(2, Contabil::first()->preRegistros()->count());
        $this->assertNotEquals(null, Contabil::first()->preRegistros->get(1)->deleted_at);
    }

    /** @test */
    public function campos_pre_registro()
    {
        $this->assertEquals([
            'cnpj',
            'nome',
            'email',
            'nome_contato',
            'telefone'
        ], Contabil::camposPreRegistro());
    }

    /** @test */
    public function array_validacao_inputs_do_pre_registro()
    {
        $contabil = factory('App\Contabil')->create();
        $dados = factory('App\PreRegistroCpf')->create();

        $this->assertEquals([
            'cnpj_contabil' => $contabil->cnpj,
            'nome_contabil' => $contabil->nome,
            'email_contabil' => $contabil->email,
            'nome_contato_contabil' => $contabil->nome_contato,
            'telefone_contabil' => $contabil->telefone
        ], $contabil->arrayValidacaoInputs());
    }

    /** @test */
    public function buscar()
    {
        factory('App\Contabil', 5)->create();
        $nao_existe = factory('App\Contabil')->raw();
        
        // Contabil existe e sem verificação se pode editar
        $this->assertEquals(Contabil::class, get_class(Contabil::buscar(Contabil::first()->cnpj, null)));
        $this->assertEquals(5, Contabil::count());

        // Contabil existe e com verificação se pode editar
        $this->assertEquals(Contabil::class, get_class(Contabil::buscar(Contabil::first()->cnpj, true)));
        $this->assertEquals('notUpdate', Contabil::buscar(Contabil::first()->cnpj, false));
        $this->assertEquals(5, Contabil::count());

        // Contabil não existe, então cria e sem verificação se pode editar
        $this->assertEquals(Contabil::class, get_class(Contabil::buscar($nao_existe['cnpj'], null)));
        $this->assertEquals(6, Contabil::count());

        $nao_existe = factory('App\Contabil')->raw();

        // Contabil não existe, então cria e com verificação se pode editar
        $this->assertEquals(Contabil::class, get_class(Contabil::buscar($nao_existe['cnpj'], true)));
        $this->assertEquals('notUpdate', Contabil::buscar($nao_existe['cnpj'], false));
        $this->assertEquals(7, Contabil::count());

        // sem cnpj
        $this->expectException(\Exception::class);
        Contabil::buscar(null, null);
    }

    /** @test */
    public function criar_final()
    {
        factory('App\Contabil', 5)->create();
        $pr = factory('App\PreRegistroCpf')->create();
        $nao_existe = factory('App\Contabil')->raw();

        // com contabil que existe
        $this->assertEquals(Contabil::class, get_class(Contabil::criarFinal('cnpj', Contabil::first()->cnpj, $pr->preRegistro)));

        // com contabil que não existe
        $pr->preRegistro->update(['historico_contabil' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')])]);
        $this->assertEquals(Contabil::class, get_class(Contabil::criarFinal('cnpj', $nao_existe['cnpj'], $pr->preRegistro->fresh())));

        $pr = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('bloqueado_contabil')->create()
        ]);

        // não pode criar
        $this->assertEquals('update', array_keys(Contabil::criarFinal('cnpj', $nao_existe['cnpj'], $pr->preRegistro))[0]);
        $this->assertEquals(6, Contabil::count());

        $this->expectException(\Exception::class);
        Contabil::criarFinal('cnpj', null, $pr->preRegistro);
    }

    /** @test */
    public function atualizar_final()
    {
        factory('App\Contabil', 2)->create();
        $contabil = factory('App\Contabil', 3)->states('sem_login')->create()->find(5);
        $pr = factory('App\PreRegistroCpf')->create();
        $novo = factory('App\Contabil')->raw();

        // sem cnpj e contabil sem login
        foreach(['nome', 'nome_contato', 'email'] as $dado)
        {
            $this->assertEquals(null, Contabil::find(5)->atualizarFinal($dado, $novo[$dado], $pr->preRegistro));
            $this->assertEquals($novo[$dado], $contabil->fresh()[$dado]);
        }

        // sem cnpj e contabil com login
        $pr->preRegistro->update(['contabil_id' => 2]);
        foreach(['nome', 'nome_contato', 'email'] as $dado)
        {
            $this->assertEquals(null, Contabil::find(2)->atualizarFinal($dado, $novo[$dado], $pr->preRegistro));
            $this->assertNotEquals($novo[$dado], Contabil::find(2)->fresh()[$dado]);
        }

        $novo = factory('App\Contabil')->raw();

        // com cnpj e contabil sem login e pode editar e pre-registro com id 2
        $this->assertEquals('remover', Contabil::find(4)->atualizarFinal('cnpj', Contabil::find(4)->cnpj, $pr->preRegistro));
        $this->assertEquals(null, $pr->preRegistro->fresh()['contabil_id']);

        // com cnpj e contabil com login e pode editar e pre-registro com id 4
        $pr->preRegistro->update(['historico_contabil' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')])]);
        $this->assertEquals('remover', Contabil::find(1)->atualizarFinal('cnpj', Contabil::find(1)->cnpj, $pr->preRegistro));
        $this->assertEquals(null, $pr->preRegistro->fresh()['contabil_id']);

        // com cnpj e remover relação
        $this->assertEquals('remover', Contabil::find(1)->atualizarFinal('cnpj', '', $pr->preRegistro));
        $this->assertEquals(null, $pr->preRegistro->fresh()['contabil_id']);

        $this->expectException(\Exception::class);
        Contabil::criarFinal('cnpj', null, $pr->preRegistro);
    }

    /** @test */
    public function soft_delete()
    {
        $user = factory('App\Contabil')->create();

        $this->assertEquals(1, Contabil::count());
        $this->assertDatabaseHas('contabeis', ['id' => 1, 'deleted_at' => null]);

        $user->delete();

        $this->assertEquals(0, Contabil::count());
        $this->assertDatabaseMissing('contabeis', ['id' => 1, 'deleted_at' => null]);

        Contabil::withTrashed()->first()->restore();

        $this->assertEquals(1, Contabil::count());
        $this->assertDatabaseHas('contabeis', ['id' => 1, 'deleted_at' => null]);
    }

    /** 
     * =======================================================================================================
     * TESTES USEREXTERNOSERVICE
     * =======================================================================================================
     */

    /** @test */
    public function get_definicoes()
    {
        $service = new UserExternoService;
        $this->assertEquals([
            'tipo' => 'contabil',
            'campo' => 'cnpj',
            'classe' => Contabil::class,
            'rotulo' => 'Contabilidade',
            'tabela' => 'contabeis',
            'variavel_url' => 'contabil'
        ], $service->getDefinicoes('contabil'));
    }

    /** @test */
    public function salvar()
    {
        $raw = factory('App\Contabil')->raw();
        $service = new UserExternoService;
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $raw['cnpj'],
            'nome' => $raw['nome'],
            'email' => $raw['email'],
            'password' => 'Teste1234',
            'password_confirmation' => 'Teste1234',
            'aceite' => 'accepted',
        ];

        $this->assertEquals(Contabil::class, get_class($service->save($dados)));
    }
    
    /** @test */
    public function nao_salva_com_mais_dois_emails_iguais()
    {
        $um = factory('App\Contabil')->create();
        factory('App\Contabil')->create([
            'email' => $um->email
        ]);
        $raw = factory('App\Contabil')->raw();
        $service = new UserExternoService;
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $raw['cnpj'],
            'nome' => $raw['nome'],
            'email' => $um->email,
            'password' => 'Teste1234',
            'password_confirmation' => 'Teste1234',
            'aceite' => 'accepted',
        ];

        $this->assertEquals([
            'message' => 'Este email já alcançou o limite de cadastro, por favor insira outro.',
            'class' => 'alert-danger'
        ], $service->save($dados));
    }

    /** @test */
    public function atualiza_ao_salvar_se_usuario_existe()
    {
        $um = factory('App\Contabil')->create([
            'ativo' => 0,
            'updated_at' => now()->subDay(),
        ]);
        $service = new UserExternoService;
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $um['cnpj'],
            'nome' => $um['nome'] . ' Teste',
            'email' => $um->email,
            'password' => 'Teste1234',
            'password_confirmation' => 'Teste1234',
            'aceite' => 'accepted',
        ];

        $this->assertEquals($um['nome'] . ' Teste', $service->save($dados)->nome);
    }

    /** @test */
    public function cria_ao_salvar_se_usuario_nao_existe()
    {
        $um = factory('App\Contabil')->raw();
        $service = new UserExternoService;
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $um['cnpj'],
            'nome' => $um['nome'] . ' Teste',
            'email' => $um['email'],
            'password' => 'Teste1234',
            'password_confirmation' => 'Teste1234',
            'aceite' => 'accepted',
        ];

        $um = $service->save($dados);
        $this->assertDatabaseHas('contabeis', ['id' => $um->id, 'ativo' => 0, 'nome' => $um->nome]);
    }

    /** @test */
    public function restaura_ao_salvar_se_usuario_deletado_apos_24h()
    {
        $um = factory('App\Contabil')->create([
            'ativo' => 0,
        ]);

        $service = new UserExternoService;
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $um['cnpj'],
            'nome' => $um['nome'] . ' Teste',
            'email' => $um->email,
            'password' => 'Teste1234',
            'password_confirmation' => 'Teste1234',
            'aceite' => 'accepted',
        ];
        
        $um->delete();
        $this->assertDatabaseHas('contabeis', ['id' => 1, 'ativo' => 0, 'nome' => str_replace(' Teste', '', $dados['nome']), 'deleted_at' => now()->format('Y-m-d H:i:s')]);

        Contabil::onlyTrashed()->first()->update(['updated_at' => now()->subDays(2)]);

        $um = $service->save($dados);
        $this->assertDatabaseHas('contabeis', ['id' => 1, 'ativo' => 0, 'nome' => $um->nome, 'deleted_at' => null]);
    }

    /** @test */
    public function verifica_email()
    {
        $this->salvar();
        $token = Contabil::first()->verify_token;
        $service = new UserExternoService;

        $um = $service->verificaEmail($token, 'contabil');
        $this->assertDatabaseHas('contabeis', ['id' => 1, 'ativo' => 1, 'verify_token' => null]);
    }

    /** @test */
    public function nao_verifica_email_com_token_invalido()
    {
        $this->salvar();
        $token = Contabil::first()->verify_token . 'p';
        $service = new UserExternoService;

        $this->assertEquals([
            'message' => 'Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.',
            'class' => 'alert-danger'
        ], $service->verificaEmail($token, 'contabil'));

        $this->assertDatabaseHas('contabeis', ['id' => 1, 'ativo' => 0, 'verify_token' => Contabil::first()->verify_token]);
    }

    /** @test */
    public function nao_verifica_email_se_nao_pode_ativar()
    {
        $this->salvar();
        $token = Contabil::first()->verify_token;
        Contabil::first()->update(['updated_at' => now()->subDays(2)]);

        $service = new UserExternoService;

        $this->assertEquals([
            'message' => 'Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.',
            'class' => 'alert-danger'
        ], $service->verificaEmail($token, 'contabil'));
        
        $this->assertDatabaseHas('contabeis', ['id' => 1, 'ativo' => 0, 'verify_token' => Contabil::first()->verify_token]);
    }

    /** @test */
    public function editar_dados()
    {
        $um = factory('App\Contabil')->create();
        $service = new UserExternoService;

        $dados = [
            'nome' => factory('App\Contabil')->raw()['nome'],
            'email' => factory('App\Contabil')->raw()['email']
        ];
        $service->editDados($dados, $um, 'contabil');
        
        $this->assertDatabaseHas('contabeis', ['id' => 1, 'nome' => $dados['nome'], 'email' => $dados['email']]);

        $senha_antiga = $um->password;
        $dados = [
            'password_atual' => 'Teste102030',
            'password' => 'Teste10203040'
        ];
        $service->editDados($dados, $um, 'contabil');
        
        $this->assertDatabaseMissing('contabeis', ['id' => 1, 'password' => $senha_antiga]);
    }

    /** @test */
    public function nao_editar_dados_com_senha_atual_errada()
    {
        $um = factory('App\Contabil')->create();
        $service = new UserExternoService;

        $senha_antiga = $um->password;
        $dados = [
            'password_atual' => 'Teste10203',
            'password' => 'Teste10203040'
        ];
        
        $this->assertEquals([
            'message' => 'A senha atual digitada está incorreta!',
            'class' => 'alert-danger',
        ], $service->editDados($dados, $um, 'contabil'));

        $this->assertDatabaseHas('contabeis', ['id' => 1, 'password' => $senha_antiga]);
    }

    /** @test */
    public function nao_editar_dados_com_limite_email_igual_alcancado()
    {
        $um = factory('App\Contabil')->create();
        factory('App\Contabil')->create([
            'email' => $um->email
        ]);
        factory('App\Contabil')->create([
            'email' => $um->email
        ]);
        $quatro = factory('App\Contabil')->create();
        $service = new UserExternoService;

        $dados = [
            'email' => $um->email,
        ];
        
        $this->assertEquals([
            'message' => 'Este email já alcançou o limite de cadastro, por favor insira outro.',
            'class' => 'alert-danger',
        ], $service->editDados($dados, $quatro, 'contabil'));

        $this->assertDatabaseHas('contabeis', ['id' => 4, 'email' => $quatro->email]);
    }

    /** @test */
    public function encontrar_por_cnpj()
    {
        $um = factory('App\Contabil')->create();
        $service = new UserExternoService;

        $this->assertEquals(Contabil::class, get_class($service->findByCpfCnpj('contabil', formataCpfCnpj($um->cnpj))));
    }

    /** @test */
    public function nao_encontrar_por_cnpj_se_deletado()
    {
        $um = factory('App\Contabil')->create()->delete();
        $service = new UserExternoService;

        $this->assertEquals(null, $service->findByCpfCnpj('contabil', formataCpfCnpj(Contabil::onlyTrashed()->first()->cnpj)));
    }

    /** @test */
    public function verifica_se_ativo()
    {
        // Não ativo
        $um = factory('App\Contabil')->create([
            'ativo' => 0
        ]);
        $service = new UserExternoService;

        $this->assertEquals([
            'message' => 'Por favor, acesse o email informado no momento do cadastro para verificar sua conta. Caso tenha passado de 24h ou tenha um cadastro prévio, se recadastre.',
            'class' => 'alert-warning',
            'cpf_cnpj' => formataCpfCnpj($um->cnpj)
        ], $service->verificaSeAtivo('contabil', formataCpfCnpj(Contabil::first()->cnpj)));

        // Ativo
        $um = factory('App\Contabil')->create();
        $service = new UserExternoService;

        $this->assertEquals([], $service->verificaSeAtivo('contabil', formataCpfCnpj(Contabil::find(2)->cnpj)));

        // Deletado
        $um = factory('App\Contabil')->create()->delete();
        $service = new UserExternoService;

        $this->assertEquals([
            'message' => 'Senha incorreta e/ou CPF/CNPJ não encontrado.',
            'class' => 'alert-danger',
            'cpf_cnpj' => formataCpfCnpj(Contabil::onlyTrashed()->find(3)->cnpj)
        ], $service->verificaSeAtivo('contabil', formataCpfCnpj(Contabil::onlyTrashed()->find(3)->cnpj)));
    }
}
