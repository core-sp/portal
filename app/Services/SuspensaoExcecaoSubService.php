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
        $userPodeEditar = $user->can('updateOther', $user);
        $userAdmin = $user->can('onlyAdmin', $user);
        foreach($resultados as $resultado) {
            $acoes = '';
            $acoes .= '<a href="' .route('sala.reuniao.suspensao.view', $resultado->id). '" class="btn btn-sm btn-primary">Ver</a>&nbsp;&nbsp;&nbsp;';
            if($userPodeEditar)
            {
                $acoes .= '<a href="' .route('sala.reuniao.suspensao.edit', [$resultado->id, 'suspensao']). '" class="btn btn-sm btn-warning">Editar Suspensão</a>&nbsp;&nbsp;&nbsp;';
                $acoes .= '<a href="' .route('sala.reuniao.suspensao.edit', [$resultado->id, 'excecao']). '" class="btn btn-sm btn-success">Editar Exceção</a>';
            }
            if($userAdmin)
            {
                $acoes .= '<form method="POST" action="'.route('sala.reuniao.suspensao.delete', $resultado->id).'" class="d-inline acaoTabelaAdmin">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="hidden" class="cor-danger txtTabelaAdmin" value="Tem certeza que deseja excluir a suspensão do CPF / CNPJ <i>' . $resultado->getCpfCnpj() . '</i>?" />';
                $acoes .= '<button type="button" class="btn btn-sm btn-danger ml-2" value="' . $resultado->id . '">Apagar</button>';
                $acoes .= '</form>';
            }
            $conteudo = [
                $resultado->id,
                $resultado->getCpfCnpj() . $resultado->getTextoHTMLSeCadastro(),
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

        $legenda = '<p><i class="fas fa-info-circle text-primary"></i> O <b>período de exceção</b> é para liberar o acesso a criar novos agendamentos e participar de novas reuniões, independentemente do dia do agendamento.</p>';
        $tabela = $legenda . montaTabela($headers, $contents, $classes);
        
        return $tabela;
    }

    public function listar($user)
    {
        $suspensos = SuspensaoExcecao::with(['representante'])
        ->orderBy('situacao')
        ->orderBy('data_final')
        ->orderBy('data_final_excecao')
        ->paginate(15);

        if($user->cannot('create', $user))
            unset($this->variaveis['btn_criar']);

        return [
            'tabela' => $this->tabelaCompleta($suspensos, $user),
            'resultados' => $suspensos,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($user, $id = null)
    {
        if(!isset($id))
            return [
                'variaveis' => (object) $this->variaveis
            ];

        $suspenso = SuspensaoExcecao::findOrFail($id);

        return [
            'resultado' => $suspenso, 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function save($user, $dados, $id = null)
    {
        $acao = isset($id) ? 'editou período' : 'criou período';
        $situacao = 'suspensão';

        if(isset($id))
        {
            $suspenso = SuspensaoExcecao::findOrFail($id);
            $dados['idusuario'] = $user->idusuario;

            if(isset($dados['data_final']))
                $dados['data_final'] = $dados['data_final'] == '00' ? null : $suspenso->addDiasDataFinal($dados['data_final']);
            else{
                $situacao = 'exceção';
                if(($suspenso->data_inicial_excecao == $dados['data_inicial_excecao']) && ($suspenso->data_final_excecao == $dados['data_final_excecao']))
                    return [
                        'message' => '<i class="fas fa-info-circle"></i> Não houve alterações nas datas de exceção. Registro não foi alterado.',
                        'class' => 'alert-info'
                    ];
                $dados['situacao'] = $suspenso->getSituacaoUpdateExcecao($dados['data_inicial_excecao'], $dados['data_final_excecao']);
            }

            $dados['justificativa'] = '[Funcionário(a) '.$user->nome.'] | [Ação - '.$situacao.'] - ' . $dados['justificativa'] . ' Data da justificativa: ' . formataData(now());
            $dados['justificativa'] = $suspenso->addJustificativa($dados['justificativa']);
            $suspenso->update($dados);
        }else{
            $dados['justificativa'] = '[Funcionário(a) '.$user->nome.'] | [Ação - '.$situacao.'] - ' . $dados['justificativa'] . ' Data da justificativa: ' . formataData(now());
            $dados['justificativa'] = json_encode([$dados['justificativa']], JSON_FORCE_OBJECT);
            $id = $user->suspensoes()->create($dados)->id;
        }

        event(new CrudEvent($situacao . ' do representante no agendamento de salas', $acao, $id));
    }

    public function buscar($busca, $user)
    {
        $possuiNumeros = strlen(apenasLetras($busca)) == 0;

        $resultados = SuspensaoExcecao::with('representante')
        ->when($possuiNumeros, function ($query) use ($busca){
            return $query->whereHas('representante', function($q) use($busca){
                $q->where('cpf_cnpj', 'LIKE', '%'.apenasNumeros($busca).'%');
            })
            ->orWhere('cpf_cnpj', 'LIKE', '%'.apenasNumeros($busca).'%')
            ->orWhere('id', apenasNumeros($busca));
        }, function ($query) use($busca) {
            return $query->where('situacao', $busca);
        })
        ->paginate(10);

        $this->variaveis['slug'] = $this->variaveis['busca'];

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados, $user), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function verificaSuspenso($cpf_cnpj)
    {
        return SuspensaoExcecao::existeSuspensao(apenasNumeros($cpf_cnpj));
    }

    public function participantesSuspensos($cpfs)
    {
        if(!is_array($cpfs) || empty($cpfs))
            return null;

        $suspensos = SuspensaoExcecao::participantesSuspensos($cpfs);

        if(!empty($suspensos))
            foreach($suspensos as $chave => $val)
                $suspensos[$chave] = formataCpfCnpj($val);
        return $suspensos;
    }

    public function destroy($id)
    {
        $suspensao = SuspensaoExcecao::findOrFail($id);
        $suspensao->delete() ? event(new CrudEvent('a suspensão com o CPF/CNPJ: ' . $suspensao->getCpfCnpj(), 'excluiu', $id)) : null;
    }

    public function executarRotina($service)
    {
        // Suspensões com data finalizada serão excluídas como soft delete
        SuspensaoExcecao::where('data_final', '<', now()->format('Y-m-d'))
        ->delete();

        // Atualizar situação das suspensoes se exceção válida
        SuspensaoExcecao::where('situacao', SuspensaoExcecao::SITUACAO_SUSPENSAO)
        ->where('data_inicial_excecao', '<=', now()->format('Y-m-d'))
        ->where('data_final_excecao', '>=', now()->format('Y-m-d'))
        ->update(['situacao' => SuspensaoExcecao::SITUACAO_EXCECAO]);

        // Atualizar situação das suspensoes se exceção não mais válida
        SuspensaoExcecao::where('situacao', SuspensaoExcecao::SITUACAO_EXCECAO)
        ->where('data_final_excecao', '<', now()->format('Y-m-d'))
        ->update(['situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO]);

        // Atualizar relacionamento caso o cpf / cnpj se cadastre no portal
        $suspensos = SuspensaoExcecao::whereNotNull('cpf_cnpj')->get();
        foreach($suspensos as $suspenso)
        {
            $rc = $service->getService('Representante')->getRepresentanteByCpfCnpj($suspenso->cpf_cnpj);
            if(isset($rc))
                $suspenso->updateRelacaoByIdRep($rc->id);
        }
    }
}