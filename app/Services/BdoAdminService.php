<?php

namespace App\Services;

use App\Repositories\GerentiRepositoryInterface;
use App\BdoRepresentante;
use App\AlteracaoRC;

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
            $nome_btn = $resultado->btnAcaoHTMLAdmin($id_perfil);
            $cor_btn = $nome_btn == 'Ver' ? 'default' : 'primary';
            $acoes = '<a href="' . route('bdorepresentantes.edit', $resultado->id) . '" class="btn btn-sm btn-' . $cor_btn . '">' . $nome_btn . '</a> ';
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
            $q->where('status->status_final', '!=', "");
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

        $item_publicado = $user->isAdmin() || $user->isEditor() ? 
        '<i class="fas fa-circle fa-xs" style="color: #f08c2d;"></i>&nbsp;' : '';

        // se tiver financeiro
        $gerenti = null;
        if($resultado->statusContemFinanceiro())
            $gerenti['situacao'] = trim(explode(':', $gerentiRepository->gerentiStatus($resultado->representante->ass_id))[1]);

        if($resultado->statusContemFinanceiro() && $resultado->financeiroPendente())
            $gerenti['cobrancas'] = $gerentiRepository->gerentiCobrancas($resultado->representante->ass_id);

        return [
            'muitos_campos' => $resultado->alteracoesRC->count() > 1,
            'campos_atend' => AlteracaoRC::camposBdoRC(),
            'item_publicado' => $item_publicado,
            'gerenti' => $gerenti,
            'resultado' => $resultado, 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function save($dados, $id, $user)
    {
        $resultado = BdoRepresentante::findOrFail($id);

        if(!$user->can('podeAcessarPerfil', $resultado))
            throw new \Exception('Não autorizado', 403);

        if(!$user->can('podeAtualizarPerfil', [$resultado, $dados['setor']]))
            throw new \Exception('Não autorizado', 403);

        // Em validação
        $dados['justificativa'] = isset($dados['justificativa']) ? $dados['justificativa'] : null;
        $dados['campos_recusados'] = isset($dados['campos_recusados']) ? $dados['campos_recusados'] : [];

        // Em validação --> na blade OK
        // if(($resultado->alteracoesRC->count() == 1) && isset($dados['justificativa']))
        //     $dados['campos_recusados'] = [$resultado->alteracoesRC->first()->informacao];

        // Em validação --> toModel()
        if(isset($dados['descricao']))
            $resultado->descricao = $dados['descricao'];

        $ok = $resultado->aceitarOuRecusar($user->idusuario, $dados);

        return [
            'message' => $ok ? '<i class="icon fa fa-check"></i>Perfil Público com a ID: ' . $id . ' foi atualizado com sucesso!' : 'Erro!!!!',
            'class' => $ok ? 'alert-success' : 'alert-danger',
        ];
    }

}
