<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permissao extends Model
{
    protected $primaryKey = 'idpermissao';
    protected $table = 'permissoes';
    protected $fillable = ['controller', 'metodo', 'perfis'];

    public function variaveis()
    {
        return [
            'UserController' => 'Usuário',
            'RegionalController' => 'Regional',
            'PaginaController' => 'Página',
            'NoticiaController' => 'Notícia',
            'CursoController' => 'Curso',
            'CursoInscritoController' => 'Curso (Inscrito)',
            'BdoEmpresaController' => 'B. de Oportunidades (Empresas)',
            'BdoOportunidadeController' => 'B. de Oportunidades (Oportunidades)',
            'AgendamentoController' => 'Agendamento / Sala de Reunião - Agendados',
            'AgendamentoBloqueioController' => 'Agendamentos Bloqueio / Sala de Reunião Bloqueio',
            'LicitacaoController' => 'Licitação',
            'ConcursoController' => 'Concurso',
            'NewsletterController' => 'Newsletter',
            'HomeImagemController' => 'Imagem (Home)',
            'PostsController' => 'Post',
            'RepresentanteEnderecoController' => 'Representante - Endereço',
            'RepresentanteController' => 'Representante - Cadastrados',
            'FiscalizacaoController' => 'Fiscalização',
            'CompromissoController' => 'Compromisso',
            'AvisoController' => 'Aviso',
            'SolicitaCedulaController' => 'Representante - Cédula',
            'PlantaoJuridicoController' => 'Plantão Jurídico',
            'PlantaoJuridicoBloqueioController' => 'Plantão Jurídico Bloqueio',
            'SalaReuniaoController' => 'Sala Reunião',
            'SuspensaoExcecaoController' => 'Sala Reunião - Suspensos / Exceções',
            // 'CartaServicos' => 'Carta de Serviços',
        ];
    }
}
