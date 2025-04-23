<?php

namespace App\Services;

use App\Contracts\AvisoServiceInterface;
use App\Aviso;
use App\Events\CrudEvent;

class AvisoService implements AvisoServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'aviso',
            'singulariza' => 'o aviso',
            'plural' => 'avisos',
            'pluraliza' => 'avisos',
            'form' => 'aviso'
        ];
    }

    private function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Id',
            'Área',
            'Título',
            'Dia / Hora para <span class="text-success">ativar</span>',
            'Dia / Hora para <span class="text-danger">desativar</span>',
            'Última Atualização',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEditar = auth()->user()->can('updateOther', auth()->user());
        foreach($resultados as $resultado) 
        {
            $statusDesejado = $resultado->isAtivado() ? 'Desativar' : 'Ativar';
            $botao = $resultado->isAtivado() ? 'danger' : 'success';

            $acoes = ' <a href="' .route('avisos.show', $resultado->id). '" class="btn btn-sm btn-default">Ver</a> ';
            $acoes .= '<a href="' .route('avisos.editar.view', $resultado->id). '" class="btn btn-sm btn-primary">Editar</a> ';
            $acoes .= '<form method="POST" action="' .route('avisos.editar.status', $resultado->id). '" class="d-inline acaoTabelaAdmin">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="put" />';
            $acoes .= '<input type="hidden" class="cor-' . $botao . ' txtTabelaAdmin" value="Tem certeza que deseja ' .$statusDesejado. ' o aviso da área <i>' . $resultado->area . '</i>?" />';
            $acoes .= '<button type="button" class="btn btn-sm btn-' .$botao. '" value="' .$resultado->id. '">' .$statusDesejado. '</button>';
            $acoes .= '</form>';

            $user = isset($resultado->user) ? $resultado->user->nome : '------------';
            $conteudo = [
                $resultado->id,
                $resultado->area,
                $resultado->titulo,
                isset($resultado->dia_hora_ativar) ? formataData($resultado->dia_hora_ativar) : '-----',
                isset($resultado->dia_hora_desativar) ? formataData($resultado->dia_hora_desativar) : '-----',
                formataData($resultado->updated_at). '<br><small>Por: ' .$user. '</small>',
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

    public function areas()
    {
        return Aviso::areas();
    }

    public function cores()
    {
        return Aviso::cores();
    }

    public function listar()
    {
        $resultados = Aviso::with(['user'])->orderBy('area')->paginate(10);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function show($id)
    {
        $resultado = Aviso::findOrFail($id);
        $this->variaveis['singulariza'] = 'o aviso da área do ' .$resultado->area;

        return [
            'resultado' => $resultado,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function edit($id)
    {
        $resultado = Aviso::with(['user'])->findOrFail($id);

        return [
            'resultado' => $resultado,
            'variaveis' => (object) $this->variaveis,
            'cores' => Aviso::cores()
        ];
    }

    public function save($validated, $id, $user)
    {
        $validated['idusuario'] = $user->idusuario;
        $aviso = Aviso::findOrFail($id)->update($validated);

        event(new CrudEvent('aviso', 'editou', $id));

        return [];
    }

    public function updateStatus($id, $user)
    {
        $aviso = Aviso::findOrFail($id);
        $status = $aviso->isAtivado() ? Aviso::DESATIVADO : Aviso::ATIVADO;
        $aviso->update([
            'status' => $status,
            'idusuario' => $user->idusuario
        ]);

        event(new CrudEvent('aviso', 'editou o status para ' . $status, $id));

        return $status;
    }

    public function getByArea($area)
    {
        return Aviso::where('area', $area)->first();
    }

    public function avisoAtivado($area)
    {
        $aviso = $this->getByArea($area);

        return isset($aviso) ? $aviso->isAtivado() : false;
    }

    public function getById($id)
    {
        return Aviso::find($id);
    }

    public function existeAtivado()
    {
        return Aviso::where('status', Aviso::ATIVADO)->exists();
    }

    public function executarRotina()
    {
        // ativar
        $avisos = Aviso::where('dia_hora_ativar', '<=', now()->format('Y-m-d H:i'))->get();

        foreach($avisos as $aviso)
        {
            $aviso->update(['status' => Aviso::ATIVADO, 'dia_hora_ativar' => null]);
            \Log::channel('interno')->info('[Rotina Portal - Avisos] - Aviso com ID ' . $aviso->id.' e da área "' . $aviso->area. '" foi ativado.');
        }

        unset($avisos);

        // desativar
        $avisos = Aviso::where('dia_hora_desativar', '<=', now()->format('Y-m-d H:i'))->get();

        foreach($avisos as $aviso)
        {
            $aviso->update(['status' => Aviso::DESATIVADO, 'dia_hora_desativar' => null]);
            \Log::channel('interno')->info('[Rotina Portal - Avisos] - Aviso com ID ' . $aviso->id.' e da área "' . $aviso->area. '" foi desativado.');
        }
    }
}