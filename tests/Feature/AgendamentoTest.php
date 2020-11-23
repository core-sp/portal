<?php

namespace Tests\Feature;

use App\Regional;
use App\Permissao;
use Tests\TestCase;
use App\Agendamento;
use App\Mail\AgendamentoMailGuest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgendamentoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permissao::insert([
            [
                'controller' => 'AgendamentoController',
                'metodo' => 'index',
                'perfis' => '1,6,12,13,8,'
            ], [
                'controller' => 'AgendamentoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ]
        ]);
    }

    /** 
     * =======================================================================================================
     * TESTES DE AUTORIZAÇÃO NO ADMIN
     * =======================================================================================================
     */

    /** @test 
     * 
     * Usuário sem autorização não pode listar Agendamentos. Verificando autorização no uso
     * dos filtros, da busca e da visualização de Agendamentos pendentes.
    */
    public function non_authorized_users_cannot_list_agendamento()
    {
        $this->signIn();

        $this->get(route('agendamentos.lista'))->assertForbidden();

        $this->get(route('agendamentos.busca'))->assertForbidden();

        $this->get(route('agendamentos.filtro'))->assertForbidden();

        $this->get(route('agendamentos.pendentes'))->assertForbidden();   
    }

    /** @test 
     * 
     * Usuário sem autorização não pode editar Agendamentos.
    */
    public function non_authorized_users_cannot_edit_agendamento()
    {
        $user = $this->signIn();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);
        $agendamento->status = Agendamento::STATUS_NAO_COMPARECEU;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertForbidden();

        $this->put(route('agendamentos.update', $agendamento->idagendamento), $agendamento->toArray())->assertForbidden();

        $this->assertNotEquals(Agendamento::find($agendamento->idagendamento)->status, $agendamento->status);
    }

    /** @test 
     * 
     * Usuário com autorização pode listar Agendamentos. Verificando autorização no uso
     * dos filtros, da busca e da visualização de Agendamentos pendentes.
    */
    public function authorized_users_can_list_agendamento()
    {
        $this->signInAsAdmin();

        $this->get(route('agendamentos.lista'))->assertOk();

        $this->get(route('agendamentos.busca'))->assertOk();

        $this->get(route('agendamentos.filtro'))->assertOk();

        $this->get(route('agendamentos.pendentes'))->assertOk();   
    }

    /** @test 
     * 
     * Usuário com autorização pode editar Agendamentos.
    */
    public function authorized_users_can_edit_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);
        $agendamento->status = Agendamento::STATUS_NAO_COMPARECEU;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();

        $this->put(route('agendamentos.update', $agendamento->idagendamento), $agendamento->toArray())->assertStatus(302);

        $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, $agendamento->status);
    }


    /** 
     * =======================================================================================================
     * TESTES DE REGRA DE NEGÓCIOS
     * =======================================================================================================
     */

    /** @test 
     * 
     * Atualizando Agendamento com o status "Compareceu" exige informação sobre o atendente.
    */
    public function agendamento_updated_to_compareceu_requires_agent()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);
        $agendamento->status = Agendamento::STATUS_COMPARECEU;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();

        $this->put(route('agendamentos.update', $agendamento->idagendamento), $agendamento->toArray())->assertSessionHasErrors(['idusuario']);

        $this->assertNotEquals(Agendamento::find($agendamento->idagendamento)->status, $agendamento->status);

        $agendamento->idusuario = $user->idusuario;

        $this->put(route('agendamentos.update', $agendamento->idagendamento), $agendamento->toArray())->assertStatus(302);

        $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, $agendamento->status);
    }

    /** @test 
     * 
     * Atualizando status do Agendamento pelos botões "Confirmar"/"Não Compareceu" na tabela que lista os Agendamentos.
    */
    public function agendamento_update_from_table()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);

        $this->put(route('agendamentos.updateStatus'), ['idagendamento' => $agendamento->idagendamento, 'status' => Agendamento::STATUS_COMPARECEU])->assertStatus(302);

        $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, Agendamento::STATUS_COMPARECEU);

        $this->put(route('agendamentos.updateStatus'), ['idagendamento' => $agendamento->idagendamento, 'status' => Agendamento::STATUS_NAO_COMPARECEU])->assertStatus(302);

        $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, Agendamento::STATUS_NAO_COMPARECEU);
    }

    /** @test 
     * 
     * Testando reenvio de e-mail sobre Agendamento. Opção de reenvio fica disponível quando usuário
     * abre tela de edição para Agendamentos que estão marcados para dias futuros e que não esteja cancelados.
    */
    public function resend_agendamento_mail()
    {
        Mail::fake();

        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);

        $this->post(route('agendamentos.reenviarEmail', $agendamento->idagendamento))->assertStatus(302);

        Mail::assertSent(AgendamentoMailGuest::class);
    }
    
    /** @test 
     * 
     * Testando os critérios de busca de Agendamento.
     * (nome, idagendamento, cpf, email, protocolo)
    */
    public function search_criteria_for_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);

        $this->get(route('agendamentos.busca', ['q' => $agendamento->nome]))
            ->assertSeeText($agendamento->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->idagendamento]))
            ->assertSeeText($agendamento->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->cpf]))
            ->assertSeeText($agendamento->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->email]))
            ->assertSeeText($agendamento->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->protocolo]))
            ->assertSeeText($agendamento->protocolo); 
            
        $this->get(route('agendamentos.busca', ['q' => 'Critério de busca sem resultado']))
            ->assertDontSeeText($agendamento->protocolo);
    }

    /** @test 
     * 
     * Testando a lista de Agendamentos pendentes por perfil e regional.
     * Agendamentos pendentes de análise são listados de acordo com perfil e regional do usuário.
     * Datas dos Agendamentos devem ser anterior a data atual.
    */
    public function peding_agendamentos_by_role_and_region()
    {
        // Criando usuário Admin. A Regional Sede (idregional = 1) é criada junta
        $admin = $this->signInAsAdmin();

        // Criando regional seccional (idregional != 1)
        $regional_seccional = factory('App\Regional')->create([
            'idregional' => 2,
            'regional' => 'Seccional', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Criando regional seccional (idregional != 1) adicional
        $regional_seccional_2 = factory('App\Regional')->create([
            'idregional' => 3,
            'regional' => 'Seccional', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Criando Perfil 'Coordenadoria de Atendimento'
        $coordenadoria_atendimento = factory('App\Perfil')->create([
            'idperfil' => 6,
            'nome' => 'Coordenadoria de Atendimento'
        ]);

        // Criando Perfil 'Gestão Atendimento Sede'
        $gestao_atendimento_sede = factory('App\Perfil')->create([
            'idperfil' => 12,
            'nome' => 'Gestão Atendimento Sede'
        ]);

        // Criando Perfil 'Gestão Atendimento Seccionais'
        $gestao_atendimento_seccional = factory('App\Perfil')->create([
            'idperfil' => 13,
            'nome' => 'Gestão Atendimento Seccionais'
        ]);

        // Criando Perfil 'Atendimento'
        $atendimento = factory('App\Perfil')->create([
            'idperfil' => 8,
            'nome' => 'Atendimento'
        ]);

        // Criando usuário com Perfil 'Coordenadoria de Atendimento'
        $user_coordenadoria_atendimento = factory('App\User')->create([
            'nome' => 'Coordenadoria de Atendimento',
            'idregional' => 1,
            'idperfil' => $coordenadoria_atendimento->idperfil
        ]);

        // Criando usuário com Perfil 'Gestão Atendimento Sede'
        $user_gestao_atendimento_sede = factory('App\User')->create([
            'nome' => 'Gestão Atendimento Sede',
            'idregional' => 1,
            'idperfil' => $gestao_atendimento_sede->idperfil
        ]);

        // Criando usuário com Perfil 'Gestão Atendimento Seccionais'
        $user_gestao_atendimento_seccional = factory('App\User')->create([
            'nome' => 'Gestão Atendimento Seccionais',
            'idregional' => 1,
            'idperfil' => $gestao_atendimento_seccional->idperfil
        ]);

        // Criando usuário com Perfil 'Atendimento'
        $user_atendimento = factory('App\User')->create([
            'nome' => 'Atendimento',
            'idregional' => 2,
            'idperfil' => $atendimento->idperfil
        ]);

        // Criando Agendamento pendente no passado na sede
        $agendamento_sede_pendente = factory('App\Agendamento')->create([
            'idregional' => 1,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000001'
        ]);

        // Criando Agendamento concluído no passado na sede
        $agendamento_sede_concluido = factory('App\Agendamento')->create([
            'idregional' => 1,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000002',
            'status' => Agendamento::STATUS_COMPARECEU
        ]);

        // Criando Agendamento pendente no futuro na sede
        $agendamento_sede_pendente_futuro = factory('App\Agendamento')->create([
            'idregional' => 1,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000003'
        ]);
        
        // Criando Agendamento pendente no passado na seccional
        $agendamento_seccional_pendente = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000004'
        ]);

        // Criando Agendamento concluído no passado na seccional
        $agendamento_seccional_concluido = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000005',
            'status' => Agendamento::STATUS_COMPARECEU
        ]);

        // Criando Agendamento pendente no futuro na seccional
        $agendamento_seccional_pendente_futuro = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000006'
        ]);

        // Criando Agendamento pendente no passado na seccional
        $agendamento_seccional_pendente_2 = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional_2->idregional,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000007'
        ]);

        // Testando listagem com usuário Admin

        // Usuário deve ver todos os Agendamentos pendentes do passado (tanto sede como seccional)
        $this->get(route('agendamentos.pendentes'))
            ->assertSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006')
            ->assertSeeText('AGE-000007');

        // Testando listagem com usuário 'Coordenadoria de Atendimento'
        $this->signIn($user_coordenadoria_atendimento);

        // Usuário deve ver todos os Agendamentos pendentes do passado (tanto sede como seccional)
        $this->get(route('agendamentos.pendentes'))
            ->assertSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006')
            ->assertSeeText('AGE-000007');

        // Testando listagem com usuário 'Gestão Atendimento Sede' 
        $this->signIn($user_gestao_atendimento_sede);

        // Usuário deve ver apenas os Agendamentos pendentes do passado da sede
        $this->get(route('agendamentos.pendentes'))
            ->assertSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006')
            ->assertDontSeeText('AGE-000007');

        // Testando listagem com usuário 'Gestão Atendimento Seccionais'
        $this->signIn($user_gestao_atendimento_seccional);

        // Usuário deve ver apenas os Agendamentos pendentes do passado de todas as seccionais
        $this->get(route('agendamentos.pendentes'))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006')
            ->assertSeeText('AGE-000007');

        // Testando listagem com usuário 'Atendimento'
        $this->signIn($user_atendimento);

        // Usuário deve ver apenas os Agendamentos pendentes do passado da sua regional
        $this->get(route('agendamentos.pendentes'))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006')
            ->assertDontSeeText('AGE-000007');
    }

    /** @test 
     * 
     * Testando o filtro de agendamentos.
     * (Regional, status, data mínima e máxima)
    */
    public function agendamentos_filter()
    {
        // Criando usuário Admin. A Regional Sede (idregional = 1) é criada junta
        $admin = $this->signInAsAdmin();

        // Criando regional seccional (idregional != 1)
        $regional_seccional = factory('App\Regional')->create([
            'idregional' => 2,
            'regional' => 'Seccional', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Criando Agendamento pendente no passado na sede
        $agendamento_sede_pendente = factory('App\Agendamento')->create([
            'idregional' => 1,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000001'
        ]);

        // Criando Agendamento concluído no passado na sede
        $agendamento_sede_concluido = factory('App\Agendamento')->create([
            'idregional' => 1,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000002',
            'status' => Agendamento::STATUS_COMPARECEU
        ]);

        // Criando Agendamento pendente no futuro na sede
        $agendamento_sede_pendente_futuro = factory('App\Agendamento')->create([
            'idregional' => 1,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000003'
        ]);
        
        // Criando Agendamento pendente no passado na seccional
        $agendamento_seccional_pendente = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000004'
        ]);

        // Criando Agendamento concluído no passado na seccional
        $agendamento_seccional_concluido = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000005',
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        // Criando Agendamento pendente no futuro na seccional
        $agendamento_seccional_pendente_futuro = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000006'
        ]);

        // Listando todos os agendamentos (qualquer regional, status e datas cobrindos todos os agendamentos)
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => '', 
            'status' => 'Qualquer', 
            'mindia' => date('d/m/Y', strtotime('-1 day')), 
            'maxdia' => date('d/m/Y', strtotime('+1 day'))
        ]))
            ->assertSeeText('AGE-000001') 
            ->assertSeeText('AGE-000002') 
            ->assertSeeText('AGE-000003') 
            ->assertSeeText('AGE-000004') 
            ->assertSeeText('AGE-000005')
            ->assertSeeText('AGE-000006');

        // Listando todos os agendamentos da Sede (qualquer status e datas cobrindos todos os agendamentos)
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => 1, 
            'status' => 'Qualquer', 
            'mindia' => date('d/m/Y', strtotime('-1 day')), 
            'maxdia' => date('d/m/Y', strtotime('+1 day'))
        ]))
            ->assertSeeText('AGE-000001') 
            ->assertSeeText('AGE-000002') 
            ->assertSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006');

        // Listando apenas os agendamentos com status "Compareceu" da Sede (datas cobrindos todos os agendamentos)
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => 1, 
            'status' => Agendamento::STATUS_COMPARECEU, 
            'mindia' => date('d/m/Y', strtotime('-1 day')), 
            'maxdia' => date('d/m/Y', strtotime('+1 day'))
        ]))
            ->assertDontSeeText('AGE-000001') 
            ->assertSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006');

        // Listando apenas agendamentos da Sede do dia -1
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => 1, 
            'status' => 'Qualquer', 
            'mindia' => date('d/m/Y', strtotime('-1 day')), 
            'maxdia' => date('d/m/Y', strtotime('-1 day'))
        ]))
            ->assertSeeText('AGE-000001') 
            ->assertSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006');

        // Listando nenhum o agendamentos da Sede por causa de data sem agendamento
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => 1, 
            'status' => Agendamento::STATUS_COMPARECEU, 
            'mindia' => date('d/m/Y'), 
            'maxdia' => date('d/m/Y')
        ]))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006');


        // Listando todos os agendamentos da Seccional (qualquer status e datas cobrindos todos os agendamentos)
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => $regional_seccional->idregional, 
            'status' => 'Qualquer', 
            'mindia' => date('d/m/Y', strtotime('-1 day')), 
            'maxdia' => date('d/m/Y', strtotime('+1 day'))
        ]))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertSeeText('AGE-000004') 
            ->assertSeeText('AGE-000005')
            ->assertSeeText('AGE-000006');

        // Listando apenas os agendamentos com status "Não Compareceu" da Seccional (datas cobrindos todos os agendamentos)
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => $regional_seccional->idregional,
            'status' => Agendamento::STATUS_NAO_COMPARECEU, 
            'mindia' => date('d/m/Y', strtotime('-1 day')), 
            'maxdia' => date('d/m/Y', strtotime('+1 day'))
        ]))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006');

        // Listando apenas agendamentos da Seccional do dia +1
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => $regional_seccional->idregional,
            'status' => 'Qualquer', 
            'mindia' => date('d/m/Y', strtotime('+1 day')), 
            'maxdia' => date('d/m/Y', strtotime('+1 day'))
        ]))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertSeeText('AGE-000006');

        // Listando nenhum o agendamentos da Seccional por causa de data sem agendamento
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => $regional_seccional->idregional,
            'status' => Agendamento::STATUS_COMPARECEU, 
            'mindia' => date('d/m/Y'), 
            'maxdia' => date('d/m/Y')
        ]))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006');
    }


    /** 
     * =======================================================================================================
     * TESTES NO PORTAL
     * =======================================================================================================
     */

    /** @test 
     * 
     * Testando acesso a página de criação de Agendamentos.
    */
    public function access_agendamentos_from_portal()
    {
        $this->get(route('agendamentosite.formview'))->assertOk();
    }

    /** @test 
     * 
     * Testando acesso a página de consulta de Agendamentos.
    */
    public function access_search_agendamentos_from_portal()
    {
        $this->get(route('agendamentosite.consultaView'))->assertOk();
    }

    /** @test 
     * 
     * Testando criação de agendamento pelo Portal.
     * Verificando o envio de email.
    */
    public function agendamento_can_be_created_on_portal()
    {
        Mail::fake();

        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        $agendamento = factory('App\Agendamento')->raw([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)->assertOk();

        $this->assertEquals(Agendamento::count(), 1);

        Mail::assertQueued(AgendamentoMailGuest::class);
    }

    /** @test 
     * 
     * Testando consulta de Agendamento pelo protocolo no Portal.
    */
    public function search_agendamento_on_portal()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);

        $this->get(route('agendamentosite.consulta', ['protocolo' => 'XXXXXX']))->assertSee($agendamento->protocolo);
    }

    /** @test 
     * 
     * Testando cancelamento de Agendamento no Portal.
    */
    public function cancel_agendamento_on_portal()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);

        $this->put(route('agendamentosite.cancelamento'), [
            'idagendamento' => $agendamento->idagendamento,
            'protocolo' => $agendamento->protocolo, 
            'cpf' => $agendamento->cpf
        ]);

        $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, 'Cancelado');
    }

    /** @test 
     * 
     * Testando a API que retorna os horários de acordo com regional e dia.
    */
    public function retrieve_agendamentos_by_api()
    {
        // regional_1 permite 2 agendamentos por horário
        $regional_1 = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        $regional_2 = factory('App\Regional')->create([
            'idregional' => 2,
            'regional' => 'Campinas', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Registrando um agendamento na regional_1 às 10:00
        $agendamento_1 = factory('App\Agendamento')->create([
            'idregional' => $regional_1->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);

        // Verificando que ainda é possível agendar na regional_1 às 10:00 e em todos os outros horários
        $this->post(route('agendamentosite.checaHorarios'), ['idregional' => 1, 'dia' => date('d/m/Y', strtotime('+1 day'))])
            ->assertSee('10:00')
            ->assertSee('11:00')
            ->assertSee('12:00')
            ->assertSee('13:00')
            ->assertSee('14:00');

        // Registrando mais um agendamento na regional_1 às 10:00
        $agendamento_2 = factory('App\Agendamento')->create([
            'idregional' => $regional_1->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-YYYYYY'
        ]);

        // Verificando que não é mais possível agendar na regional_1 às 10:00, mas ainda é possível em todos os outros horários
        $this->post(route('agendamentosite.checaHorarios'), ['idregional' => 1, 'dia' => date('d/m/Y', strtotime('+1 day'))])
            ->assertDontSee('10:00')
            ->assertSee('11:00')
            ->assertSee('12:00')
            ->assertSee('13:00')
            ->assertSee('14:00');

        // Verificando que o horário as 10:00 está disponível na outra regional "regional_2"
        $this->post(route('agendamentosite.checaHorarios'), ['idregional' => 2, 'dia' => date('d/m/Y', strtotime('+1 day'))])
            ->assertSee('10:00')
            ->assertSee('11:00')
            ->assertSee('12:00')
            ->assertSee('13:00')
            ->assertSee('14:00');
    }

    /** @test 
     * 
     * Testando campos obrigatórios para criação de Agendamento.
     * 
     * TODO adicionar testes para valores pré-definidos (tiposervico, regional)
    */
    public function agendamento_missing_mandatory_input_cannot_be_created()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        $agendamento = factory('App\Agendamento')->raw([
            'idregional' => null,
            'nome' => null,
            'cpf' => null,
            'email' => null,
            'celular' => null,
            'dia' => null,
            'hora' => null
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)->assertSessionHasErrors([
            'nome',
            'cpf',
            'email',
            'celular',
            'dia',
            'hora'
        ]);

        $this->assertEquals(Agendamento::count(), 0);
    }

    /** @test 
     * 
     * Testando validação de CPF na criação de Agendamento.
    */
    public function agendamento_with_invalid_cpf_cannot_be_created()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        $agendamento = factory('App\Agendamento')->raw([
            'idregional' => $regional->idregional,
            'cpf' => '00.000.000/0000-00',
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)->assertSessionHasErrors(['cpf',]);

        $this->assertEquals(Agendamento::count(), 0);
    }

    /** @test 
     * 
     * Testando validação que permite que um CPF possa ser usado apenas em dois Agendamentos no mesmo dia.
    */
    public function agendamento_with_same_cpf_can_be_created_two_time_on_same_day()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Primeiro Agendamento do dia com o CPF
        $agendamento_1 = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);

        // Segundo Agendamento do dia com o mesmo CPF
        $agendamento_2 = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '11:00',
            'protocolo' => 'AGE-YYYYYY'
        ]);

        // Terceiro Agendamento do dia com o mesmo CPF
        $agendamento_3 = factory('App\Agendamento')->raw([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '12:00'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento_3)->assertStatus(500);

        // Apenas os dois primeiros devem estar no banco de dados
        $this->assertEquals(Agendamento::count(), 2);
    }

    /** @test 
     * 
     * Testando validação que bloqueia Agendamento com CPF que deixou de comparecer três vezes em Agendamentos anteriores
     * nos últimos 90 dias.
    */
    public function agendamento_with_cpf_that_didnt_show_up_three_times_in_90_days()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Primeiro Agendamento em que a pessoa com o CPF não compareceu
        $agendamento_1 = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d'),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX',
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        // Segundo Agendamento em que a pessoa com o CPF não compareceu
        $agendamento_2 = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d'),
            'hora' => '10:00',
            'protocolo' => 'AGE-YYYYYY',
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        // Terceiro Agendamento em que a pessoa com o CPF não compareceu
        $agendamento_3 = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d'),
            'hora' => '10:00',
            'protocolo' => 'AGE-WWWWWW',
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        // Quarto Agendamento com o CPF da pessoa que não compareceu três vezes
        $agendamento_4 = factory('App\Agendamento')->raw([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento_4)->assertStatus(405);

        // Apenas os três primeiros Agendamentos devem estar no banco de dados
        $this->assertEquals(Agendamento::count(), 3);
    }

    /** @test 
     * 
     * Testando validação que permite Agendamento com CPF que deixou de comparecer três vezes em Agendamentos anteriores
     * com mais de 90 dias.
    */
    public function agendamento_with_cpf_that_didnt_show_up_three_times_in_more_than_90_days()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Primeiro Agendamento em que a pessoa com o CPF não compareceu (91 dias atrás)
        $agendamento_1 = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('-91 days')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX',
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        // Segundo Agendamento em que a pessoa com o CPF não compareceu (91 dias atrás)
        $agendamento_2 = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('-91 days')),
            'hora' => '10:00',
            'protocolo' => 'AGE-YYYYYY',
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        // Terceiro Agendamento em que a pessoa com o CPF não compareceu (91 dias atrás)
        $agendamento_3 = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('-91 days')),
            'hora' => '10:00',
            'protocolo' => 'AGE-WWWWWW',
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        // Quarto Agendamento com o CPF da pessoa que não compareceu três vezes
        $agendamento_4 = factory('App\Agendamento')->raw([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento_4)->assertOk();

        // Todos os agendamentos devem estar presentes
        $this->assertEquals(Agendamento::count(), 4);
    }

    /** @test 
     * 
     * Testando validação que bloqueia Agendamento com data anterior a atual.
    */
    public function agendamento_with_older_date_cannot_be_created()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Usando data -1
        $agendamento = factory('App\Agendamento')->raw([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)->assertStatus(500);

        $this->assertEquals(Agendamento::count(), 0);
    }

    /** @test 
     * 
     * Testando validação que bloqueia Agendamento quando o horário requerido não está disponível.
     * 
     * TODO - adicionar uma mensagem ao erro
    */
    public function agendamento_with_no_available_time()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 1, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Ocupando o único horário disponível às 10:00
        $agendamento_1 = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);

        // Tentando criar Agendamento novamente às 10:00
        $agendamento_2 = factory('App\Agendamento')->raw([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
        ]);

        $this->post(route('agendamentosite.store'), $agendamento_2)->assertStatus(500);

        // Apenas o primeiro Agendamento deve estar presente no banco de dados
        $this->assertEquals(Agendamento::count(), 1);

        // Nova regional onde não há zero atendimentos por horário
        $regional_2 = factory('App\Regional')->create([
            'idregional' => 2,
            'regional' => 'Campinas', 
            'ageporhorario' => 0, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        $agendamento_3 = factory('App\Agendamento')->raw([
            'idregional' => $regional_2->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
        ]);

        $this->post(route('agendamentosite.store'), $agendamento_3)->assertStatus(500);

        // Banco de dados deve continuar apenas com um agendamento
        $this->assertEquals(Agendamento::count(), 1);
    }

    /** @test 
     * 
     * Testando consulta de Agendamento com protocolo errado no Portal.
    */
    public function wrong_protocol_search_agendamento_on_portal()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);

        // Usando protocolo diferente do usado na criação do Agendamento
        $this->get(route('agendamentosite.consulta', ['protocolo' => 'YYYYYY']))->assertDontSee($agendamento->protocolo);
    }

    /** @test 
     * 
     * Testando bloqueio de cancelamento de Agendamento no Portal quando CPF fornecido não bate com protocolo.
    */
    public function cancel_agendamento_with_wrong_cpf_on_portal()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);

        // Usando CPF diferente na consulta
        $this->put(route('agendamentosite.cancelamento'), [
            'idagendamento' => $agendamento->idagendamento,
            'protocolo' => $agendamento->protocolo, 
            'cpf' => '000.000.000-00'
        ]);

        // Garantir que o status do Agendamento é nulo e não "Cancelado"
        $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, null);
    }

    /** @test 
     * 
     * Testando bloqueio de cancelamento de Agendamento no Portal quando cancelamento é feito no mesmo dia do agendamento.
    */
    public function cancel_agendamento_on_agendamento_day_on_portal()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Criando Agendamento no dia atual para tentar cancelar no mesmo dia
        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'dia' => date('Y-m-d'),
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);

        $this->put(route('agendamentosite.cancelamento'), [
            'idagendamento' => $agendamento->idagendamento,
            'protocolo' => $agendamento->protocolo, 
            'cpf' => $agendamento->cpf
        ])->assertStatus(302);

        // Garantir que o status do Agendamento é nulo e não "Cancelado"
        $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, null);
    }
}