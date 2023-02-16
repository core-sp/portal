<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RepresentanteAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $repEndereco = factory('App\RepresentanteEndereco')->create();
        $rep = factory('App\Representante')->create([
            'cpf_cnpj' => '04712425008',
            'ass_id' => 123456,
            'registro_core' => '001232022'
        ]);
        $repCedula = factory('App\SolicitaCedula')->create([
            'idrepresentante' => $rep->id
        ]);
        
        $this->get('/admin/representantes')->assertRedirect(route('login'));
        $this->get('/admin/representantes/busca')->assertRedirect(route('login'));
        $this->get('/admin/representantes/buscaGerenti')->assertRedirect(route('login'));
        $this->get(route('admin.representante.buscaGerenti'))->assertRedirect(route('login'));
        $this->get('/admin/representantes/info')->assertRedirect(route('login'));
        $this->get(route('admin.representante.baixarCertidao'))->assertRedirect(route('login'));

        $this->get('/admin/representante-enderecos')->assertRedirect(route('login'));
        $this->get(route('representante-endereco.busca'))->assertRedirect(route('login'));
        $this->get(route('admin.representante-endereco.show', $repEndereco->id))->assertRedirect(route('login'));
        $this->post(route('admin.representante-endereco.post'))->assertRedirect(route('login'));
        $this->post(route('admin.representante-endereco-recusado.post'))->assertRedirect(route('login'));
        $this->get(route('representante-endereco.visualizar'))->assertRedirect(route('login'));
        $this->get(route('representante-endereco.baixar'))->assertRedirect(route('login'));

        $this->get(route('solicita-cedula.index'))->assertRedirect(route('login'));
        $this->get(route('solicita-cedula.filtro'))->assertRedirect(route('login'));
        $this->get(route('solicita-cedula.show', $repCedula->id))->assertRedirect(route('login'));
        $this->get(route('solicita-cedula.pdf', $repCedula->id))->assertRedirect(route('login'));
        $this->get(route('solicita-cedula.busca'))->assertRedirect(route('login'));
        $this->put(route('solicita-cedula.update', $repCedula->id))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $repEndereco = factory('App\RepresentanteEndereco')->create();
        $rep = factory('App\Representante')->create([
            'cpf_cnpj' => '04712425008',
            'ass_id' => 123456,
            'registro_core' => '001232022'
        ]);
        $repCedula = factory('App\SolicitaCedula')->create([
            'idrepresentante' => $rep->id
        ]);

        $this->get('/admin/representantes')->assertForbidden();
        $this->get('/admin/representantes/busca')->assertForbidden();
        $this->get('/admin/representantes/buscaGerenti')->assertForbidden();
        $this->get('/admin/representantes/info')->assertForbidden();

        $this->get('/admin/representante-enderecos')->assertForbidden();
        $this->get(route('representante-endereco.busca'))->assertForbidden();
        $this->get(route('admin.representante-endereco.show', $repEndereco->id))->assertForbidden();
        $this->post(route('admin.representante-endereco.post'))->assertForbidden();
        $this->post(route('admin.representante-endereco-recusado.post'))->assertForbidden();
        $this->get(route('representante-endereco.visualizar'))->assertForbidden();
        $this->get(route('representante-endereco.baixar'))->assertForbidden();

        $this->get(route('solicita-cedula.index'))->assertForbidden();
        $this->get(route('solicita-cedula.filtro'))->assertForbidden();
        $this->get(route('solicita-cedula.show', $repCedula->id))->assertForbidden();
        $this->get(route('solicita-cedula.pdf', $repCedula->id))->assertForbidden();
        $this->get(route('solicita-cedula.busca'))->assertForbidden();
        $this->put(route('solicita-cedula.update', $repCedula->id))->assertForbidden();
    }

    /** @test */
    public function admin_can_access_links()
    {
        $this->signInAsAdmin();
        $repEndereco = factory('App\RepresentanteEndereco')->create();
        $rep = factory('App\Representante')->create([
            'cpf_cnpj' => '04712425008',
            'ass_id' => 123456,
            'registro_core' => '001232022'
        ]);
        $repCedula = factory('App\SolicitaCedula')->create([
            'idrepresentante' => $rep->id
        ]);

        $this->get('/admin/representantes')->assertOk();
        $this->get('/admin/representantes/busca')->assertOk();
        $this->get('/admin/representantes/buscaGerenti')->assertOk();

        $this->get('/admin/representante-enderecos')->assertOk();
        $this->get(route('representante-endereco.busca'))->assertOk();
        $this->get(route('admin.representante-endereco.show', $repEndereco->id))->assertOk();
        $this->post(route('admin.representante-endereco.post'), $repEndereco->toArray())->assertStatus(302);

        $repEndereco->observacao = "Teste de recusa do endereço.";
        $this->post(route('admin.representante-endereco-recusado.post'), $repEndereco->toArray())->assertStatus(302);

        $file = UploadedFile::fake()->image($repEndereco->crimage);
        Storage::putFileAs('representantes/enderecos/', $file, $repEndereco->crimage);
        Storage::assertExists('representantes/enderecos/'.$repEndereco->crimage);

        $this->get(route('representante-endereco.visualizar', ['nome' => $repEndereco->crimage]))->assertOk();
        $this->get(route('representante-endereco.baixar', ['nome' => $repEndereco->crimage]))->assertOk();

        $this->get(route('solicita-cedula.index'))->assertOk();
        $this->get(route('solicita-cedula.filtro'))->assertOk();
        $this->get(route('solicita-cedula.show', $repCedula->id))->assertOk();
        $this->get(route('solicita-cedula.pdf', $repCedula->id))->assertStatus(302);
        $this->get(route('solicita-cedula.busca'))->assertOk();
        $this->put(route('solicita-cedula.update', $repCedula->id), ['status' => 'Aceito'])->assertStatus(302);
    }
}
