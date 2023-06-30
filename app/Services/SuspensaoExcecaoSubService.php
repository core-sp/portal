<?php

namespace App\Services;

use App\Contracts\SuspensaoExcecaoSubServiceInterface;
use App\SuspensaoExcecao;
use App\Events\CrudEvent;
use Carbon\Carbon;

class SuspensaoExcecaoSubService implements SuspensaoExcecaoSubServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'suspensão / exceção',
            'singulariza' => 'a suspensão / exceção',
            'plural' => 'suspensões / exceções',
            'pluraliza' => 'suspensões / exceções',
            'btn_criar' => '<a href="'.route('sala.reuniao.bloqueio.criar').'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Nova Suspensão</a>',
            'titulo_criar' => 'Nova Suspensão',
            'busca' => 'salas-reunioes/suspensoes-excecoes',
            'slug' => 'salas-reunioes/suspensoes-excecoes',
            'busca' => 'salas-reunioes/suspensoes-excecoes',
            'mostra' => 'suspensao_excecao',
        ];
    }

    private function tabelaCompleta($resultados, $user)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'ID',
            'CPF / CNPJ',
            'Situação',
            'Período',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        // $userPodeEditar = $user->can('updateOther', $user);
        // $userPodeExcluir = $user->can('delete', $user);
        foreach($resultados as $resultado) {
            $acoes = '';
            $acoes .= '<a href="' .route('sala.reuniao.suspensao.view', $resultado->id). '" class="btn btn-sm btn-primary">Ver</a> ';
            // if($userPodeEditar)
            //     $acoes .= '<a href="' .route('sala.reuniao.bloqueio.edit', $resultado->id). '" class="btn btn-sm btn-primary">Editar</a> ';
            // if($userPodeExcluir)
            // {
            //     $acoes .= '<form method="POST" action="'.route('sala.reuniao.bloqueio.delete', $resultado->id).'" class="d-inline">';
            //     $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            //     $acoes .= '<input type="hidden" name="_method" value="delete" />';
            //     $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir esse bloqueio?\')" />';
            //     $acoes .= '</form>';
            // }
            $conteudo = [
                $resultado->id,
                $resultado->getCpfCnpj(),
                $resultado->getSituacaoHTML(),
                $resultado->mostraPeriodo().'<br><small><em>'. $resultado->mostraPeriodoEmDias() .'</em></small>',
                $acoes
            ];
            array_push($contents, $conteudo);
        }

        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        $tabela = montaTabela($headers, $contents, $classes);
        
        return $tabela;
    }

    public function listar($user)
    {
        $suspensos = SuspensaoExcecao::with(['representante'])
        ->where('data_final', '>=', now()->format('Y-m-d'))
        ->orWhereNull('data_final')
        ->orderBy('data_final')
        ->paginate(15);

        // if($user->cannot('create', $user))
        //     unset($this->variaveis['btn_criar']);

        return [
            'tabela' => $this->tabelaCompleta($suspensos, $user),
            'resultados' => $suspensos,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($user, $id)
    {
        $suspenso = SuspensaoExcecao::findOrFail($id);

        return [
            'resultado' => $suspenso, 
            'variaveis' => (object) $this->variaveis
        ];
    }

    // public function save($user, $dados, $id = null)
    // {
    //     $dados['horarios'] = implode(',', $dados['horarios']);
    //     $acao = isset($id) ? 'editou' : 'criou';

    //     if(isset($id))
    //     {
    //         unset($dados['sala_reuniao_id']);
    //         $bloqueio = SalaReuniaoBloqueio::findOrFail($id)->update($dados);
    //     }
    //     else  
    //         $id = $user->salasReunioesBloqueios()->create($dados)->id;

    //     event(new CrudEvent('sala reunião bloqueio', $acao, $id));
    // }

    // public function destroy($id)
    // {
    //     return SalaReuniaoBloqueio::findOrFail($id)->delete() ? event(new CrudEvent('sala reunião bloqueio', 'excluiu', $id)) : null;
    // }

    // public function buscar($busca, $user)
    // {
    //     $resultados = SalaReuniaoBloqueio::with('sala.regional')
    //         ->whereHas('sala.regional', function($q) use($busca){
    //             $q->where('regional', 'LIKE', '%'.$busca.'%');
    //         })->paginate(10);

    //     $this->variaveis['slug'] = $this->variaveis['busca'];

    //     return [
    //         'resultados' => $resultados,
    //         'tabela' => $this->tabelaCompleta($resultados, $user), 
    //         'variaveis' => (object) $this->variaveis
    //     ];
    // }
}