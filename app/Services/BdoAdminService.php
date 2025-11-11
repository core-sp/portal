<?php

namespace App\Services;

use App\Repositories\GerentiRepositoryInterface;
use App\BdoRepresentante;
use Illuminate\Support\Str;

class BdoAdminService {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'perfil público do representante',
            'singulariza' => 'perfil público do representante',
            'plural' => 'perfis públicos dos Representantes',
            'pluraliza' => 'perfis públicos dos Representantes',
            'slug' => 'perfil-publico-representantes',
            'busca' => 'perfil-publico-representantes',
            'form' => 'bdo-perfil',
        ];
    }

    private function tabelaCompleta($resultados, $user)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'ID',
            'Nome',
            'CNPJ',
            'Registro CORE',
            'Cadastrado em:',
            'Atualizado em:',
            'Status',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $id_perfil = $user->idperfil;
        foreach($resultados as $resultado) 
        {
            $acoes = '<a href="' . route('bdorepresentantes.edit', $resultado->id) . '" class="btn btn-sm btn-primary">Editar</a> ';
            $conteudo = [
                $resultado->id,
                $resultado->representante->nome,
                $resultado->representante->cpf_cnpj,
                $resultado->representante->registro_core,
                formataData($resultado->created_at),
                formataData($resultado->updated_at),
                $resultado->statusHTMLAdmin($id_perfil),
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];

        $legenda = '<p><b><i>Legenda:</i></b><span class="badge badge-primary ml-2">Atendimento</span>';
        $legenda .= '&nbsp;&nbsp;|&nbsp;&nbsp;';
        $legenda .= '<span class="badge badge-secondary">Financeiro</span>';
        $legenda .= '&nbsp;&nbsp;|&nbsp;&nbsp;';
        $legenda .= '<span class="badge badge-success">Comunicação</span></p><hr>';

        $legenda = $user->isAdmin() ? $legenda : '';

        return $legenda . montaTabela($headers, $contents, $classes);
    }

    public function listar($user)
    {
        $resultados = BdoRepresentante::when(in_array($user->idperfil, [3]), function($q){
            $q->where('status->status_final', BdoRepresentante::STATUS_ADMIN_COMUN);
        })
        ->when($user->idperfil == 16, function($q){
            $q->whereNotNull('status->financeiro');
        })
        ->when(in_array($user->idperfil, [6, 8]), function($q){
            $q->whereNotNull('status->atendimento');
        })
        ->orderBy('id', 'DESC')
        ->paginate(10);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados, $user), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function editar($user, $id, GerentiRepositoryInterface $gerentiRepository)
    {
        $resultado = BdoRepresentante::findOrFail($id);

        if(!$user->isAdmin() && !$user->can('podeAcessarPerfil', $resultado))
            throw new \Exception('Não autorizado', 403);

        // se tiver financeiro
        $gerenti['situacao'] = trim(explode(':', $gerentiRepository->gerentiStatus($resultado->representante->ass_id))[1]);
        $gerenti['cobrancas'] = $gerentiRepository->gerentiCobrancas($resultado->representante->ass_id);

        return [
            'gerenti' => $gerenti,
            'resultado' => $resultado, 
            'variaveis' => (object) $this->variaveis
        ];
    }

}
