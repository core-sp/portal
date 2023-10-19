<?php

namespace App\Services;

use App\CursoInscrito;
use App\Events\CrudEvent;
use App\Contracts\CursoSubServiceInterface;

class CursoSubService implements CursoSubServiceInterface {

    private function variaveis($curso)
    {
        return [
            'pluraliza' => 'inscritos',
            'plural' => 'inscritos',
            'singular' => 'inscrito',
            'singulariza' => 'o inscrito',
            'continuacao_titulo' => 'em <strong>'.$curso->tipo.': '.$curso->tema.'</strong>',
            'btn_lixeira' => '<a href="'.route('cursos.index').'" class="btn btn-default"><i class="fas fa-list"></i> Lista de Cursos</a>',
            'busca' => 'cursos/inscritos/'.$curso->idcurso,
            'addonsHome' => '<a href="/admin/cursos/inscritos/download/'.$curso->idcurso.'" class="btn btn-primary mb-2">Baixar CSV</a>',
            'btn_criar' => '<a href="'.route('inscritos.create', $curso->idcurso).'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Adicionar inscrito</a> ',
        ];
    }

    private function tabelaCompleta($resultados, $user)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'ID',
            'CPF',
            'Nome',
            'Telefone',
            'Email',
            'Tipo da Inscrição',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEdit = $user->can('updateOther', $user);
        $userPodeDestroy = $user->can('delete', $user);
        foreach($resultados as $resultado) 
        {
            $acoes = '';
            if($userPodeEdit)
                $acoes .= ' <a href="'.route('inscritos.edit', $resultado->idcursoinscrito).'" class="btn btn-sm btn-default">Editar</a> ';
            if($userPodeDestroy && $resultado->podeCancelar()) {
                $acoes .= '<form method="POST" action="'.route('inscritos.destroy', $resultado->idcursoinscrito).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Cancelar Inscrição" onclick="return confirm(\'Tem certeza que deseja cancelar a inscrição?\')" />';
                $acoes .= '</form>';
            }elseif(!$resultado->possuiPresenca()){
                $acoes .= '<form method="POST" action="/admin/cursos/inscritos/confirmar-presenca/'.$resultado->idcursoinscrito.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="put" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-success" value="Confirmar presença" />';
                $acoes .= '</form> ';
                $acoes .= '<form method="POST" action="/admin/cursos/inscritos/confirmar-falta/'.$resultado->idcursoinscrito.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="put" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-warning" value="Dar falta" />';
                $acoes .= '</form>';
            }elseif($resultado->possuiPresenca())
                $acoes .= $resultado->compareceu() ? "<p class='d-inline text-success'><strong><i class='fas fa-check checkIcone'></i> Compareceu&nbsp;</strong></p>" :
                "<p class='d-inline text-danger'><strong><i class='fas fa-ban checkIcone'></i> Não Compareceu&nbsp;</strong></p>";

            if(empty($acoes))
                $acoes = '<i class="fas fa-lock text-muted"></i>';
            $conteudo = [
                $resultado->idcursoinscrito,
                $resultado->cpf,
                $resultado->nome,
                $resultado->telefone,
                $resultado->email,
                $resultado->tipo_inscrito,
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

    public function tiposInscricao()
    {
        return CursoInscrito::tiposInscricao();
    }

    public function listar($curso, $user)
    {
        $resultados = $curso->cursoinscrito()
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        $variaveis = $this->variaveis($curso);

        if($user->cannot('create', $user) || !$curso->podeInscrever())
            unset($variaveis['btn_criar']);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados, $user), 
            'variaveis' => (object) $variaveis
        ];
    }

    public function view($curso = null, $id = null)
    {
        if(!isset($id) && !isset($curso))
            throw new \Exception('Deve inserir model curso ou id do inscrito', 500);

        $resultado = isset($id) ? CursoInscrito::findOrFail($id) : null;
        $curso = isset($resultado) ? $resultado->curso : $curso;

        if(!$curso->podeInscrever())
            throw new \Exception('Não autorizado a adicionar inscrito fora do período de inscrição no curso com ID '.$curso->idcurso.'.', 403);

        $variaveis = $this->variaveis($curso);
        $variaveis['form'] = 'cursoinscrito';
        $variaveis['titulo_criar'] = 'Adicionar inscrito em '.$curso->tipo.': '.$curso->tema;

        return [
            'resultado' => $resultado,
            'idcurso' => $curso->idcurso,
            'variaveis' => (object) $variaveis,
            'tipos' => CursoInscrito::tiposInscricao(),
        ];
    }

    public function save($validated, $user, $curso = null, $id = null)
    {
        if(!isset($id) && !isset($curso))
            throw new \Exception('Deve inserir model curso ou id do inscrito', 500);

        $validated['idusuario'] = $user->idusuario;
        $validated['nome'] = mb_convert_case(mb_strtolower($validated['nome']), MB_CASE_TITLE);
        $acao = (!isset($id)) ? 'adicionou' : 'editou';

        $inscrito = isset($id) ? CursoInscrito::findOrFail($id) : null;
        $curso = isset($inscrito) ? $inscrito->curso : $curso;

        if(!$curso->podeInscrever())
            throw new \Exception('Não autorizado a adicionar inscrito fora do período de inscrição no curso com ID '.$curso->idcurso.'.', 403);

        if(!isset($inscrito))
            $id = $curso->cursoinscrito()->create($validated)->idcursoinscrito;
        else
            $inscrito->update($validated);
        
        event(new CrudEvent('inscrito em curso', $acao, $id));

        return [
            'idcurso' => $curso->idcurso
        ];
    }

    public function buscar($curso, $busca, $user)
    {
        $resultados = $curso->cursoinscrito()
        ->where(function($query) use($busca){
            $query->where('cpf','LIKE','%'.$busca.'%')
            ->orWhere('nome','LIKE','%'.$busca.'%')
            ->orWhere('email','LIKE','%'.$busca.'%');
        })
        ->paginate(10);

        $variaveis = $this->variaveis($curso);
        $variaveis['slug'] = 'cursos/inscritos/'.$curso->idcurso;

        if(!$curso->podeInscrever())
            unset($variaveis['btn_criar']);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados, $user), 
            'variaveis' => (object) $variaveis
        ];
    }

    public function destroy($id)
    {
        $inscrito = CursoInscrito::findOrFail($id);

        if(!$inscrito->podeCancelar())
            throw new \Exception('Não autorizado a cancelar inscrição com ID '.$id.' fora do período de inscrição.', 403);

        $inscrito->delete() ? event(new CrudEvent('inscrito em curso', 'cancelou inscrição', $id)) : null;

        return [
            'idcurso' => $inscrito->curso->idcurso
        ];
    }
}