<?php

namespace App\Services;

use App\Contracts\SalaReuniaoBloqSubServiceInterface;
use App\SalaReuniaoBloqueio;
use App\Events\CrudEvent;
use Carbon\Carbon;

class SalaReuniaoBloqSubService implements SalaReuniaoBloqSubServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'bloqueio sala reunião / coworking',
            'singulariza' => 'o bloqueio da sala de reunião / coworking',
            'plural' => 'bloqueios das salas de reuniões / coworking',
            'pluraliza' => 'bloqueios sala reunião / coworking',
            'form' => 'sala_reuniao_bloqueio',
            'btn_criar' => '<a href="'.route('sala.reuniao.bloqueio.criar').'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Novo Bloqueio</a>',
            'titulo_criar' => 'Criar bloqueio',
            'busca' => 'salas-reunioes/bloqueios',
        ];
    }

    private function tabelaCompleta($resultados, $user)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Id',
            'Sala',
            'Período do Bloqueio',
            'Horas Bloqueadas',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEditar = $user->can('updateOther', $user);
        $userPodeExcluir = $user->can('delete', $user);
        foreach($resultados as $resultado) {
            $acoes = '';
            if($userPodeEditar)
                $acoes .= '<a href="' .route('sala.reuniao.bloqueio.edit', $resultado->id). '" class="btn btn-sm btn-primary">Editar</a> ';
            if($userPodeExcluir)
            {
                $acoes .= '<form method="POST" action="'.route('sala.reuniao.bloqueio.delete', $resultado->id).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir esse bloqueio?\')" />';
                $acoes .= '</form>';
            }
            $conteudo = [
                $resultado->id,
                $resultado->sala->regional->regional,
                $resultado->mostraPeriodo(),
                $resultado->horarios,
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
        $salas = SalaReuniaoBloqueio::with('sala')
        ->where('dataFinal', '>=', now()->format('Y-m-d'))
        ->orWhereNull('dataFinal')
        ->orderBy('dataFinal')
        ->paginate(15);

        if($user->cannot('create', $user))
            unset($this->variaveis['btn_criar']);

        return [
            'tabela' => $this->tabelaCompleta($salas, $user),
            'resultados' => $salas,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($user, $service = null, $id = null)
    {
        if(isset($id))
        {
            $bloqueio = SalaReuniaoBloqueio::findOrFail($id);

            return [
                'resultado' => $bloqueio, 
                'variaveis' => (object) $this->variaveis
            ];
        }

        return [
            'salas' => $service->getService('SalaReuniao')->salasAtivas(),
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function save($user, $dados, $id = null)
    {
        $dados['horarios'] = implode(',', $dados['horarios']);
        $acao = isset($id) ? 'editou' : 'criou';

        if(isset($id))
        {
            unset($dados['sala_reuniao_id']);
            $bloqueio = SalaReuniaoBloqueio::findOrFail($id)->update($dados);
        }
        else  
            $id = $user->salasReunioesBloqueios()->create($dados)->id;

        event(new CrudEvent('sala reunião / coworking bloqueio', $acao, $id));
    }

    public function destroy($id)
    {
        return SalaReuniaoBloqueio::findOrFail($id)->delete() ? event(new CrudEvent('sala reunião / coworking bloqueio', 'excluiu', $id)) : null;
    }

    public function buscar($busca, $user)
    {
        $resultados = SalaReuniaoBloqueio::with('sala.regional')
            ->whereHas('sala.regional', function($q) use($busca){
                $q->where('regional', 'LIKE', '%'.$busca.'%');
            })->paginate(10);

        $this->variaveis['slug'] = $this->variaveis['busca'];

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados, $user), 
            'variaveis' => (object) $this->variaveis
        ];
    }
}