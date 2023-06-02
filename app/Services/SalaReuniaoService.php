<?php

namespace App\Services;

use App\Contracts\SalaReuniaoServiceInterface;
use App\SalaReuniao;
use Carbon\Carbon;
use App\Events\CrudEvent;

class SalaReuniaoService implements SalaReuniaoServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'sala de reunião',
            'singulariza' => 'a sala de reunião',
            'plural' => 'salas de reuniões',
            'pluraliza' => 'salas de reuniões',
            'form' => 'sala_reuniao',
        ];
    }

    private function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Id',
            'Regional',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        // $userPodeEditar = auth()->user()->can('updateOther', auth()->user());
        foreach($resultados as $resultado) {
            $acoes = '';
            // if($userPodeEditar)
                $acoes = '<a href="' .route('sala.reuniao.editar.view', $resultado->id). '" class="btn btn-sm btn-primary">Editar</a> ';
            $conteudo = [
                $resultado->id,
                $resultado->regional->regional,
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

    public function getItensByTipo($tipo)
    {
        switch($tipo){
            case 'coworking': 
                return SalaReuniao::itensCoworking();
                break;
            case 'reuniao': 
            default:
                return SalaReuniao::itens();
        }            
    }

    public function listar()
    {
        $salas = SalaReuniao::with('regional')->get();

        return [
            'tabela' => $this->tabelaCompleta($salas),
            'resultados' => $salas,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($id)
    {
        $sala = SalaReuniao::with('regional')->findOrFail($id);
        
        return [
            'resultado' => $sala,
            'variaveis' => (object) $this->variaveis,
        ];
    }

    public function save($dados, $id, $user)
    {
        SalaReuniao::findOrFail($id)->update([
            'horarios_reuniao' => json_encode([
                'manha' => isset($dados['manha_horarios_reuniao']) ? $dados['manha_horarios_reuniao'] : array(),
                'tarde' => isset($dados['tarde_horarios_reuniao']) ? $dados['tarde_horarios_reuniao'] : array()
            ], JSON_FORCE_OBJECT),
            'horarios_coworking' => json_encode([
                'manha' => isset($dados['manha_horarios_coworking']) ? $dados['manha_horarios_coworking'] : array(),
                'tarde' => isset($dados['tarde_horarios_coworking']) ? $dados['tarde_horarios_coworking'] : array(),
            ], JSON_FORCE_OBJECT),
            'participantes_reuniao' => $dados['participantes_reuniao'],
            'participantes_coworking' => $dados['participantes_coworking'],
            'itens_reuniao' => json_encode(isset($dados['itens_reuniao']) ? $dados['itens_reuniao'] : array(), JSON_FORCE_OBJECT),
            'itens_coworking' => json_encode(isset($dados['itens_coworking']) ? $dados['itens_coworking'] : array(), JSON_FORCE_OBJECT),
            'idusuario' => $user->idusuario
        ]);

        event(new CrudEvent('sala de reunião', 'editou', $id));
    }
}