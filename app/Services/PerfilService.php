<?php

namespace App\Services;

use App\Contracts\PerfilServiceInterface;
use App\Perfil;
use App\Permissao;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class PerfilService implements PerfilServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'perfil',
            'singulariza' => 'o perfil',
            'plural' => 'perfis',
            'pluraliza' => 'perfis',
            'titulo_criar' => 'Cadastrar perfil',
            'btn_criar' => '<a href="' . route('perfis.create') . '" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Novo Perfil</a>'
        ];
    }

    private function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'ID',
            'Nome',
            '<i class="fas fa-user-check text-success"></i>&nbsp;&nbsp;Nº de Usuários',
            '<i class="fas fa-check-square text-primary"></i>&nbsp;&nbsp;Total de permissões',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) 
        {
            $acoes = '<a href="'. route('perfis.permissoes.edit', $resultado->idperfil) . '" class="btn btn-sm btn-primary mr-2"> Permissões</a> ';
            if(($resultado->user_count == 0) && !in_array($resultado->idperfil, [1, 24])) {
                $acoes .= '<form method="POST" action="'. route('perfis.destroy', $resultado->idperfil) . '" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" ';
                $acoes .= 'onclick="return confirm(\'CUIDADO! Isto pode influenciar diretamente no funcionamento do Portal. Tem certeza que deseja excluir o perfil?\')" />';
                $acoes .= '</form>';
            }
            $conteudo = [
                $resultado->idperfil,
                $resultado->nome,
                $resultado->user_count,
                $resultado->permissoes_count,
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];

        $legenda = '<p class="text-danger"><i class="fas fa-exclamation-triangle"></i><i>&nbsp;&nbsp;Somente perfil ';

        $temp = Perfil::select('nome')->whereIn('idperfil', [1, 24])->get()->pluck('nome');
        $legenda = $temp->whenNotEmpty(function ($temp) use($legenda) {
            return $legenda .= '(exceto \'' . Arr::get($temp, 0, 'Admin') . '\' e \'' . Arr::get($temp, 1, 'Bloqueado') . '\')';
        }, function ($legenda) {
            return $legenda .= '';
        }) . ' sem usuário(s) ativo(s) pode ser excluído.</i></p>';
        
        return $legenda . montaTabela($headers, $contents, $classes);
    }

    public function all()
    {
        return Perfil::orderBy('nome')->get();
    }

    public function permissoesAgrupadasPorController()
    {
        return Permissao::select('idpermissao', 'controller', 'metodo', 'nome')->orderBy('nome')->get()->groupBy('controller');
    }

    public function listar()
    {
        $resultados = Perfil::select('idperfil','nome')
        ->withCount(['user', 'permissoes'])
        ->orderBy('nome','ASC')
        ->paginate(10);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($id = null)
    {
        if(!isset($id))
            return ['variaveis' => (object) $this->variaveis];

        $perfil = Perfil::findOrFail($id);
        $this->variaveis['singulariza'] = 'o perfil ' . $perfil->nome;

        return [
            'perfil' => $perfil,
            'permissoes' => $this->permissoesAgrupadasPorController(),
            'variaveis' => (object) $this->variaveis,
        ];
    }

    public function save($dados, $id = null)
    {
        if(!isset($id))
        {
            $perfil = Perfil::create([
                'nome' => $dados['nome']
            ]);
            event(new CrudEvent('perfil de usuário', 'criou', $perfil->idperfil));
    
            return $perfil;
        }

        $dados = isset($dados['permissoes']) ? $dados['permissoes'] : array();

        // salvar permissões com rollback em caso de erro.
        DB::transaction(function () use($dados, $id){

            Perfil::findOrFail($id)->permissoes()->sync($dados);
            event(new CrudEvent('permissões do perfil ' . $id, 'editou', implode(', ', $dados)));

        });
    }

    public function delete($id)
    {
        $perfil = Perfil::withCount(['user', 'permissoes'])->findOrFail($id);

        if(in_array($perfil->idperfil, [1, 24]) || ($perfil->user_count > 0))
            return [
                'message' => 'Perfil com ID ' . $id . ' (' . $perfil->nome . ') não pode ser excluído!',
                'class' => 'alert-danger',
            ];

        if($perfil->permissoes_count > 0)
            $perfil->permissoes()->sync(array());

        $perfil = $perfil->delete();
        if(!$perfil)
            return [
                'message' => 'Perfil com ID ' . $id . ' não foi excluído!', 
                'class' => 'alert-danger'
            ];

        event(new CrudEvent('perfil de usuário e suas permissões', 'apagou', $id));

        return [
            'message' => '<i class="icon fa fa-check"></i>Perfil com ID ' . $id . ' deletado com sucesso!',
            'class' => 'alert-success',
        ];
    }
}
