<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\UserExterno;
use App\Services\UserExternoService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use App\Notifications\UserExternoResetPasswordNotification;

class UserExternoTest extends TestCase
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
    public function enviar_email_reset_password()
    {
        Mail::fake();

        $dados = factory('App\UserExterno')->create();
        $token = str_random(32);

        $dados->sendPasswordResetNotification($token);

        Mail::assertQueued(UserExternoResetPasswordNotification::class, function ($mail) use($token){
            return $mail->token == $token;
        });
    }

    /** @test */
    public function tipo_pessoa()
    {
        $dados = factory('App\UserExterno')->create();

        $this->assertEquals(true, $dados->isPessoaFisica());

        $dados = factory('App\UserExterno')->states('pj')->create();

        $this->assertEquals(false, $dados->isPessoaFisica());
    }

    /** @test */
    public function possui_login()
    {
        $dados = factory('App\UserExterno')->create();

        $this->assertEquals(true, $dados->possuiLogin());

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->create();

        $this->assertEquals(false, $dados->possuiLogin());
    }

    /** @test */
    public function possui_login_ativo()
    {
        $dados = factory('App\UserExterno')->create();

        $this->assertEquals(true, $dados->possuiLoginAtivo());

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->create();

        $this->assertEquals(false, $dados->possuiLoginAtivo());
    }

    /** @test */
    public function pode_ativar()
    {
        // Externo com login
        $dados = factory('App\UserExterno')->create([
            'ativo' => 0
        ]);

        $this->assertEquals(true, $dados->podeAtivar());

        // Externo sem login
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->create();

        $this->assertEquals(false, $dados->podeAtivar());

        // Externo após 24h
        $dados = factory('App\UserExterno')->create([
            'ativo' => 0
        ]);
        $dados->update(['updated_at' => $dados->updated_at->subDays(2)]);

        $this->assertEquals(false, $dados->fresh()->podeAtivar());

        // Externo excluído dentro das 24h
        factory('App\UserExterno')->create([
            'ativo' => 0
        ])->delete();

        $this->assertEquals(true, UserExterno::onlyTrashed()->first()->podeAtivar());
    }

    /** @test */
    public function possui_registro_ativo()
    {
        // PF
        $dados = factory('App\UserExterno')->create([
            'cpf_cnpj' => self::CPF_GERENTI
        ]);

        // PF, ativo, não cancelado
        $this->assertEquals(true, $dados->possuiRegistroAtivo(2, 'F', 'T'));

        // PF, nao ativo, cancelado
        $this->assertEquals(false, $dados->possuiRegistroAtivo(2, 'T', 'F'));

        // RT, ativo, não cancelado
        $this->assertEquals(true, $dados->possuiRegistroAtivo(5, 'F', 'T'));

        // RT, nao ativo, cancelado
        $this->assertEquals(false, $dados->possuiRegistroAtivo(5, 'T', 'F'));

        // PJ
        $dados = factory('App\UserExterno')->create([
            'cpf_cnpj' => self::CNPJ_GERENTI
        ]);

        // PJ, ativo, não cancelado
        $this->assertEquals(true, $dados->possuiRegistroAtivo(1, 'F', 'T'));

        // PJ, nao ativo, cancelado
        $this->assertEquals(false, $dados->possuiRegistroAtivo(1, 'T', 'F'));
    }

    /** @test */
    public function criar_pre_registro()
    {
        $dados = factory('App\UserExterno')->create();
        $pr = $dados->criarPreRegistro();

        $this->assertEquals(1, $pr->user_externo_id);
        $this->assertEquals('Sendo elaborado', $pr->status);
        $this->assertEquals('BRASILEIRA', $pr->pessoaFisica->nacionalidade);

        $dados = factory('App\UserExterno')->states('pj')->create();
        $pr = $dados->criarPreRegistro();
        $json = json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')]);

        $this->assertEquals(2, $pr->user_externo_id);
        $this->assertEquals('Sendo elaborado', $pr->status);
        $this->assertEquals($json, $pr->pessoaJuridica->historico_rt);
    }

    /** @test */
    public function pre_registros()
    {
        $dados = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ]);
        factory('App\PreRegistroCpf')->create();

        $this->assertEquals(2, UserExterno::first()->preRegistros()->count());

        UserExterno::first()->preRegistros->get(1)->delete();

        $this->assertEquals(2, UserExterno::first()->preRegistros()->count());
        $this->assertNotEquals(null, UserExterno::first()->preRegistros->get(1)->deleted_at);
    }

    /** @test */
    public function pre_registro()
    {
        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ]);
        factory('App\PreRegistroCpf')->create();

        $this->assertEquals(1, UserExterno::first()->preRegistro()->count());

        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ]);

        UserExterno::first()->preRegistros->get(1)->delete();

        $this->assertEquals(0, UserExterno::first()->preRegistro()->count());
    }

    /** @test */
    public function pre_registro_aprovado()
    {
        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ]);
        factory('App\PreRegistroCpf')->create();

        $this->assertEquals(0, UserExterno::first()->preRegistroAprovado());

        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ]);

        UserExterno::first()->preRegistros->get(1)->delete();

        $this->assertEquals(1, UserExterno::first()->preRegistroAprovado());
    }

    /** @test */
    public function pre_registro_doc()
    {
        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create()
        ]);
        factory('App\PreRegistroCpf')->create();

        $this->assertEquals(0, UserExterno::first()->preRegistroDoc()->count());

        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create()
        ]);

        UserExterno::first()->preRegistros->get(1)->delete();

        $this->assertEquals(1, UserExterno::first()->preRegistroDoc()->count());
    }

    /** @test */
    public function soft_delete()
    {
        $user = factory('App\UserExterno')->create();

        $this->assertEquals(1, UserExterno::count());
        $this->assertDatabaseHas('users_externo', ['id' => 1, 'deleted_at' => null]);

        $user->delete();

        $this->assertEquals(0, UserExterno::count());
        $this->assertDatabaseMissing('users_externo', ['id' => 1, 'deleted_at' => null]);

        UserExterno::withTrashed()->first()->restore();

        $this->assertEquals(1, UserExterno::count());
        $this->assertDatabaseHas('users_externo', ['id' => 1, 'deleted_at' => null]);
    }

    /** 
     * =======================================================================================================
     * TESTES SERVICE
     * =======================================================================================================
     */

    /** @test */
    public function get_definicoes()
    {
        $service = new UserExternoService;
        $this->assertEquals([
            'tipo' => 'user_externo',
            'campo' => 'cpf_cnpj',
            'classe' => UserExterno::class,
            'rotulo' => 'Usuário Externo',
            'tabela' => 'users_externo',
            'variavel_url' => 'user-externo'
        ], $service->getDefinicoes('user-externo'));
    }

    /** @test */
    public function salvar()
    {
        $raw = factory('App\UserExterno')->raw();
        $service = new UserExternoService;
        $dados = [
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $raw['cpf_cnpj'],
            'nome' => $raw['nome'],
            'email' => $raw['email'],
            'password' => 'Teste1234',
            'password_confirmation' => 'Teste1234',
            'aceite' => 'accepted',
        ];

        $this->assertEquals(UserExterno::class, get_class($service->save($dados)));
    }
    
    /** @test */
    public function nao_salva_com_mais_dois_emails_iguais()
    {
        $um = factory('App\UserExterno')->create();
        factory('App\UserExterno')->create([
            'email' => $um->email
        ]);
        $raw = factory('App\UserExterno')->raw();
        $service = new UserExternoService;
        $dados = [
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $raw['cpf_cnpj'],
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
        $um = factory('App\UserExterno')->create([
            'ativo' => 0,
            'updated_at' => now()->subDay(),
        ]);
        $service = new UserExternoService;
        $dados = [
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $um['cpf_cnpj'],
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
        $um = factory('App\UserExterno')->raw();
        $service = new UserExternoService;
        $dados = [
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $um['cpf_cnpj'],
            'nome' => $um['nome'] . ' Teste',
            'email' => $um['email'],
            'password' => 'Teste1234',
            'password_confirmation' => 'Teste1234',
            'aceite' => 'accepted',
        ];

        $um = $service->save($dados);
        $this->assertDatabaseHas('users_externo', ['id' => $um->id, 'ativo' => 0, 'nome' => $um->nome]);
    }

    /** @test */
    public function restaura_ao_salvar_se_usuario_deletado_apos_24h()
    {
        $um = factory('App\UserExterno')->create([
            'ativo' => 0,
        ]);

        $service = new UserExternoService;
        $dados = [
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $um['cpf_cnpj'],
            'nome' => $um['nome'] . ' Teste',
            'email' => $um->email,
            'password' => 'Teste1234',
            'password_confirmation' => 'Teste1234',
            'aceite' => 'accepted',
        ];
        
        $um->delete();
        $this->assertDatabaseHas('users_externo', ['id' => 1, 'ativo' => 0, 'nome' => str_replace(' Teste', '', $dados['nome']), 'deleted_at' => now()->format('Y-m-d H:i:s')]);

        UserExterno::onlyTrashed()->first()->update(['updated_at' => now()->subDays(2)]);

        $um = $service->save($dados);
        $this->assertDatabaseHas('users_externo', ['id' => 1, 'ativo' => 0, 'nome' => $um->nome, 'deleted_at' => null]);
    }

    /** @test */
    public function verifica_email()
    {
        $this->salvar();
        $token = UserExterno::first()->verify_token;
        $service = new UserExternoService;

        $um = $service->verificaEmail($token, 'user-externo');
        $this->assertDatabaseHas('users_externo', ['id' => 1, 'ativo' => 1, 'verify_token' => null]);
    }

    /** @test */
    public function nao_verifica_email_com_token_invalido()
    {
        $this->salvar();
        $token = UserExterno::first()->verify_token . 'p';
        $service = new UserExternoService;

        $this->assertEquals([
            'message' => 'Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.',
            'class' => 'alert-danger'
        ], $service->verificaEmail($token, 'user-externo'));

        $this->assertDatabaseHas('users_externo', ['id' => 1, 'ativo' => 0, 'verify_token' => UserExterno::first()->verify_token]);
    }

    /** @test */
    public function nao_verifica_email_se_nao_pode_ativar()
    {
        $this->salvar();
        $token = UserExterno::first()->verify_token;
        UserExterno::first()->update(['updated_at' => now()->subDays(2)]);

        $service = new UserExternoService;

        $this->assertEquals([
            'message' => 'Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.',
            'class' => 'alert-danger'
        ], $service->verificaEmail($token, 'user-externo'));
        
        $this->assertDatabaseHas('users_externo', ['id' => 1, 'ativo' => 0, 'verify_token' => UserExterno::first()->verify_token]);
    }

    /** @test */
    public function editar_dados()
    {
        $um = factory('App\UserExterno')->create();
        $service = new UserExternoService;

        $dados = [
            'nome' => factory('App\UserExterno')->raw()['nome'],
            'email' => factory('App\UserExterno')->raw()['email']
        ];
        $service->editDados($dados, $um, 'user-externo');
        
        $this->assertDatabaseHas('users_externo', ['id' => 1, 'nome' => $dados['nome'], 'email' => $dados['email']]);

        $senha_antiga = $um->password;
        $dados = [
            'password_atual' => 'Teste102030',
            'password' => 'Teste10203040'
        ];
        $service->editDados($dados, $um, 'user-externo');
        
        $this->assertDatabaseMissing('users_externo', ['id' => 1, 'password' => $senha_antiga]);
    }

    /** @test */
    public function nao_editar_dados_com_senha_atual_errada()
    {
        $um = factory('App\UserExterno')->create();
        $service = new UserExternoService;

        $senha_antiga = $um->password;
        $dados = [
            'password_atual' => 'Teste10203',
            'password' => 'Teste10203040'
        ];
        
        $this->assertEquals([
            'message' => 'A senha atual digitada está incorreta!',
            'class' => 'alert-danger',
        ], $service->editDados($dados, $um, 'user-externo'));

        $this->assertDatabaseHas('users_externo', ['id' => 1, 'password' => $senha_antiga]);
    }

    /** @test */
    public function nao_editar_dados_com_limite_email_igual_alcancado()
    {
        $um = factory('App\UserExterno')->create();
        factory('App\UserExterno')->create([
            'email' => $um->email
        ]);
        factory('App\UserExterno')->create([
            'email' => $um->email
        ]);
        $quatro = factory('App\UserExterno')->create();
        $service = new UserExternoService;

        $dados = [
            'email' => $um->email,
        ];
        
        $this->assertEquals([
            'message' => 'Este email já alcançou o limite de cadastro, por favor insira outro.',
            'class' => 'alert-danger',
        ], $service->editDados($dados, $quatro, 'user-externo'));

        $this->assertDatabaseHas('users_externo', ['id' => 4, 'email' => $quatro->email]);
    }

    /** @test */
    public function encontrar_por_cpf_cnpj()
    {
        $um = factory('App\UserExterno')->create();
        $service = new UserExternoService;

        $this->assertEquals(UserExterno::class, get_class($service->findByCpfCnpj('user-externo', formataCpfCnpj($um->cpf_cnpj))));
        $this->assertEquals(true, $service->findByCpfCnpj('user-externo', formataCpfCnpj($um->cpf_cnpj))->isPessoaFisica());

        $um = factory('App\UserExterno')->states('pj')->create();
        $service = new UserExternoService;

        $this->assertEquals(UserExterno::class, get_class($service->findByCpfCnpj('user-externo', formataCpfCnpj($um->cpf_cnpj))));
        $this->assertEquals(false, $service->findByCpfCnpj('user-externo', formataCpfCnpj($um->cpf_cnpj))->isPessoaFisica());
    }

    /** @test */
    public function nao_encontrar_por_cpf_cnpj_se_deletado()
    {
        $um = factory('App\UserExterno')->create()->delete();
        $service = new UserExternoService;

        $this->assertEquals(null, $service->findByCpfCnpj('user-externo', formataCpfCnpj(UserExterno::onlyTrashed()->first()->cpf_cnpj)));
    }

    /** @test */
    public function verifica_se_ativo()
    {
        // Não ativo
        $um = factory('App\UserExterno')->create([
            'ativo' => 0
        ]);
        $service = new UserExternoService;

        $this->assertEquals([
            'message' => 'Por favor, acesse o email informado no momento do cadastro para verificar sua conta. Caso tenha passado de 24h ou tenha um cadastro prévio, se recadastre.',
            'class' => 'alert-warning',
            'cpf_cnpj' => formataCpfCnpj($um->cpf_cnpj)
        ], $service->verificaSeAtivo('user-externo', formataCpfCnpj(UserExterno::first()->cpf_cnpj)));

        // Ativo
        $um = factory('App\UserExterno')->create();
        $service = new UserExternoService;

        $this->assertEquals([], $service->verificaSeAtivo('user-externo', formataCpfCnpj(UserExterno::find(2)->cpf_cnpj)));

        // Deletado
        $um = factory('App\UserExterno')->create()->delete();
        $service = new UserExternoService;

        $this->assertEquals([
            'message' => 'Senha incorreta e/ou CPF/CNPJ não encontrado.',
            'class' => 'alert-danger',
            'cpf_cnpj' => formataCpfCnpj(UserExterno::onlyTrashed()->find(3)->cpf_cnpj)
        ], $service->verificaSeAtivo('user-externo', formataCpfCnpj(UserExterno::onlyTrashed()->find(3)->cpf_cnpj)));
    }

    /** @test */
    public function cadastro_previo()
    {
        $contabil = factory('App\Contabil')->create();
        $service = new UserExternoService;

        // Com dados e retorno previo do objeto
        $dados_externo = factory('App\UserExterno')->raw();
        $this->assertEquals(UserExterno::class, get_class($service->cadastroPrevio($contabil, $dados_externo, true)));
        $this->assertDatabaseMissing('users_externo', ['id' => 1, 'email' => $dados_externo['email']]);

        // Com dados e salva sem retorno previo do objeto
        $this->assertEquals(UserExterno::class, get_class($service->cadastroPrevio($contabil, $dados_externo, false)));
        $this->assertDatabaseHas('users_externo', ['id' => 1, 'email' => $dados_externo['email']]);

        // Com objeto e retorno previo do objeto
        $dados_externo = factory('App\UserExterno')->make();
        $this->assertEquals(UserExterno::class, get_class($service->cadastroPrevio($contabil, $dados_externo, true)));
        $this->assertDatabaseMissing('users_externo', ['id' => 2, 'email' => $dados_externo->email]);

        // Com dados e salva sem retorno previo do objeto
        $this->assertEquals(UserExterno::class, get_class($service->cadastroPrevio($contabil, $dados_externo, false)));
        $this->assertDatabaseHas('users_externo', ['id' => 2, 'email' => $dados_externo->email]);
    }
}
