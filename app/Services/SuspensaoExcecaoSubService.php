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
            'singular' => 'suspensão',
            'singulariza' => 'a suspensão',
            'plural' => 'suspensões',
            'pluraliza' => 'suspensões',
            'btn_criar' => '<a href="'.route('sala.reuniao.suspensao.criar').'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Nova Suspensão</a>',
            'titulo_criar' => 'Nova Suspensão',
            'busca' => 'salas-reunioes/suspensoes-excecoes',
            'slug' => 'salas-reunioes/suspensoes-excecoes',
            'busca' => 'salas-reunioes/suspensoes-excecoes',
            'mostra' => 'suspensao_excecao',
            'form' => 'suspensao_excecao',
        ];
    }

    private function tabelaCompleta($resultados, $user)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'ID',
            'CPF / CNPJ',
            'Situação',
            'Período Suspensão',
            'Período Exceção',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        // $userPodeEditar = $user->can('updateOther', $user);
        foreach($resultados as $resultado) {
            $acoes = '';
            $acoes .= '<a href="' .route('sala.reuniao.suspensao.view', $resultado->id). '" class="btn btn-sm btn-primary">Ver</a>&nbsp;&nbsp;&nbsp;';
            // if($userPodeEditar)
            $acoes .= '<a href="' .route('sala.reuniao.suspensao.edit', [$resultado->id, 'suspensao']). '" class="btn btn-sm btn-warning">Editar Suspensão</a>&nbsp;&nbsp;&nbsp;';
            $acoes .= '<a href="' .route('sala.reuniao.suspensao.edit', [$resultado->id, 'excecao']). '" class="btn btn-sm btn-success">Editar Exceção</a>';
            $conteudo = [
                $resultado->id,
                $resultado->getCpfCnpj(),
                $resultado->getSituacaoHTML(),
                $resultado->mostraPeriodo().'<br><small><em>'. $resultado->mostraPeriodoEmDias() .'</em></small>',
                $resultado->mostraPeriodoExcecao().'<br><small><em>'. $resultado->mostraPeriodoExcecaoEmDias() .'</em></small>',
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

    public function view($user, $id = null)
    {
        $suspenso = SuspensaoExcecao::findOrFail($id);

        return [
            'resultado' => $suspenso, 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function save($user, $dados, $id = null)
    {
        $acao = isset($id) ? 'editou período' : 'criou';

        if(isset($id))
        {
            $suspenso = SuspensaoExcecao::findOrFail($id);
            $dados['idusuario'] = $user->idusuario;
            $dados['justificativa'] = '[Funcionário(a) '.$user->nome.'] - ' . $dados['justificativa'] . ' Data da justificativa: ' . formataData(now());
            $dados['justificativa'] = $suspenso->addJustificativa($dados['justificativa']);

            if(isset($dados['data_final']))
                $dados['data_final'] = $dados['data_final'] == '00' ? null : $suspenso->addDiasDataFinal($dados['data_final']);

            $suspenso->update($dados);
        }

        event(new CrudEvent('suspensão / exceção', $acao, $id));
    }
}