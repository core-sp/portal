<?php

namespace App\Services;

use App\Contracts\GerarTextoServiceInterface;
use App\GerarTexto;
use App\Events\CrudEvent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class GerarTextoService implements GerarTextoServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'texto',
            'singulariza' => 'o texto',
            'plural' => 'texto',
            'pluraliza' => 'texto',
            'form' => 'texto'
        ];
    }

    public function limiteCriarTextos()
    {
        return GerarTexto::TOTAL_N_VEZES;
    }

    public function view($tipo_doc, $id = null)
    {
        $resultado = GerarTexto::select('id', 'tipo', 'texto_tipo', 'com_numeracao', 'ordem', 'nivel', 'indice', 'publicar')
        ->where('tipo_doc', $tipo_doc)
        ->when(isset($id), function($query) use($id){
            $query->select('tipo', 'texto_tipo', 'com_numeracao', 'nivel', 'indice', 'conteudo')->where('id', $id);
        })
        ->orderBy('ordem','ASC')
        ->get();

        $this->variaveis['singular'] = $this->variaveis['singular'] . ' '. GerarTexto::tiposDoc()[$tipo_doc];
        $this->variaveis['singulariza'] = $this->variaveis['singulariza'] . ' '. GerarTexto::tiposDoc()[$tipo_doc];

        return [
            'resultado' => $resultado,
            'variaveis' => (object) $this->variaveis,
            'orientacao_sumario' => GerarTexto::orientacaoSumario()[$tipo_doc],
            'limite_criar_textos' => GerarTexto::TOTAL_N_VEZES,
        ];
    }

    public function criar($tipo_doc, $n_vezes = null)
    {
        $n_vezes = isset($n_vezes) ? (int) $n_vezes : $n_vezes;
        $texto = GerarTexto::criar($tipo_doc, $n_vezes);

        if(isset($n_vezes) && ($n_vezes > 1)){
            $ids = implode(', ', array_keys(Arr::except($texto, ['texto_tipo'])));
            event(new CrudEvent('novos textos do documento '.$tipo_doc, 'criou', $ids));
            $texto = (object) $texto;
            $texto->novo_texto = ['novos_textos' => explode(', ', $ids)];
        }else{
            event(new CrudEvent('novo texto do documento '.$tipo_doc, 'criou', $texto->id));
            $texto->novo_texto = ['novo_texto' => $texto->id];
        }

        return $texto;
    }

    public function update($tipo_doc, $dados, $id = null)
    {
        // atualiza os campos, não atualiza ordem e indice
        if(isset($id))
        {
            $dados['texto_tipo'] = $dados['tipo'] == GerarTexto::TIPO_TITULO ? mb_strtoupper($dados['texto_tipo'], 'UTF-8') : $dados['texto_tipo'];
            $resultado = GerarTexto::where('tipo_doc', $tipo_doc)->where('id', $id)->firstOrFail();

            $resultado->fill($dados);
            
            if($resultado->isDirty())
            {
                $resultado->save();
                event(new CrudEvent('campos do texto do documento '.$tipo_doc, 'atualizou', $id));
                $resultado->texto_tipo = $resultado->tipoTitulo() ? $resultado->tituloFormatado() : $resultado->subtituloFormatado();
            }
            
            return $resultado;
        }

        // Somente atualiza ordem e indice
        $resultado = GerarTexto::select('nivel', 'com_numeracao', 'ordem', 'indice', 'id')->where('tipo_doc', $tipo_doc)->orderBy('ordem','ASC')->get();
        unset($dados['n_vezes']);
        GerarTexto::updateIndice($dados, $resultado);
        event(new CrudEvent('índice do texto do documento '.$tipo_doc, 'atualizou', '----'));

        return true;
    }

    public function publicar($tipo_doc, $publicar = false)
    {
        $ok = GerarTexto::where('tipo_doc', $tipo_doc)->update([
            'publicar' => $publicar
        ]);
        $texto = $publicar ? 'publicou' : 'reverteu publicação';
        $ok > 0 ? event(new CrudEvent('os textos do documento '.$tipo_doc, $texto, '---')) : null;

        return $ok;
    }

    public function excluir($tipo_doc, $ids = array())
    {
        // remover valores vazios
        $ids = array_filter($ids);
        $total = GerarTexto::where('tipo_doc', $tipo_doc)->count();

        if(empty($ids) || ($total <= count($ids)))
            throw new \Exception('Deve existir no mínimo um texto.', 400);

        $ok = array();
        $textos = GerarTexto::where('tipo_doc', $tipo_doc)->whereIn('id', $ids)->get();
        foreach($textos as $texto)
        {
            $nome_temp = $texto->texto_tipo;
            $id_temp = $texto->id;
            if($texto->delete()){
                event(new CrudEvent('o texto do documento '.$tipo_doc.' com o nome: '.$nome_temp, 'excluiu', $id_temp));
                array_push($ok, $id_temp);
            }
        }
        GerarTexto::reordenarPorTipo($tipo_doc);            
        
        return $ok;
    }

    public function show($tipo_doc, $id = null, $user = null)
    {
        if(isset($id))
            $final = GerarTexto::where('tipo_doc', $tipo_doc)
            ->where('id', $id)
            ->when(!isset($user), function($query){
                $query->where('publicar', true);
            })
            ->firstOrFail()
            ->conteudoTituloComSubtitulo(isset($user));

        return [
            'resultado' => GerarTexto::getLayoutCliente(GerarTexto::resultadoByDoc($tipo_doc, $user), $tipo_doc),
            'textos' => isset($final['textos']) ? $final['textos'] : array(),
            'btn_anterior' => isset($final['btn_anterior']) ? route($tipo_doc, $final['btn_anterior']->id) : null,
            'btn_proximo' => isset($final['btn_proximo']) ? route($tipo_doc, $final['btn_proximo']->id) : null,
            'dt_atualizacao' => onlyDate(GerarTexto::ultimaAtualizacao($tipo_doc)),
        ];
    }

    public function buscar($tipo_doc, $busca, $user = null)
    {
        return [
            'resultado' => GerarTexto::resultadoByDoc($tipo_doc, $user),
            'busca' => isset($busca) ? GerarTexto::resultadoByDoc($tipo_doc, $user, $busca) : collect(),
        ];
    }
}