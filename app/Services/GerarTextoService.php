<?php

namespace App\Services;

use App\Contracts\GerarTextoServiceInterface;
use App\GerarTexto;
use App\Events\CrudEvent;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

    public function view($tipo_doc, $id = null)
    {
        $resultado = GerarTexto::select('id', 'tipo', 'texto_tipo', 'com_numeracao', 'ordem', 'nivel', 'indice', 'publicar')
        ->where('tipo_doc', $tipo_doc)
        ->when(isset($id), function($query) use($id){
            $query->select('tipo', 'texto_tipo', 'com_numeracao', 'nivel', 'indice', 'conteudo')->where('id', $id);
        })
        ->orderBy('ordem','ASC')
        ->get();

        return [
            'resultado' => $resultado,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function criar($tipo_doc)
    {
        $texto = GerarTexto::criar($tipo_doc);
        event(new CrudEvent('novo texto do documento '.$tipo_doc, 'criou', $texto->id));

        return $texto;
    }

    public function update($tipo_doc, $dados, $id = null)
    {
        // atualiza os campos, não atualiza ordem e indice
        if(isset($id))
        {
            $dados['texto_tipo'] = $dados['tipo'] == GerarTexto::TIPO_TITULO ? mb_strtoupper($dados['texto_tipo'], 'UTF-8') : $dados['texto_tipo'];
            $resultado = GerarTexto::where('tipo_doc', $tipo_doc)->where('id', $id)->firstOrFail();
            $resultado->update($dados);
            event(new CrudEvent('campos do texto do documento '.$tipo_doc, 'atualizou', $id));
            $resultado->texto_tipo = $resultado->tipoTitulo() ? $resultado->tituloFormatado() : $resultado->subtituloFormatado();
    
            return $resultado;
        }

        // Somente atualiza ordem e indice
        $resultado = GerarTexto::select('nivel', 'com_numeracao', 'ordem', 'indice', 'id')->where('tipo_doc', $tipo_doc)->orderBy('ordem','ASC')->get();
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

        if(!empty($ids) && ($total > count($ids)))
        {
            $ok = array();
            $textos = GerarTexto::where('tipo_doc', $tipo_doc)->whereIn('id', $ids)->get();
            foreach($textos as $texto)
            {
                $nome_temp = $texto->texto_tipo;
                $id_temp = $texto->id;
                $temp = $texto->delete();
                if($temp){
                    event(new CrudEvent('o texto do documento '.$tipo_doc.' com o nome: '.$nome_temp, 'excluiu', $id_temp));
                    array_push($ok, $id_temp);
                }
            }
        }else
            $ok = 'Deve existir no mínimo um texto.';
        
        return $ok;
    }

    public function show($tipo_doc, $id = null, $user = null)
    {
        $resultado = GerarTexto::resultadoByDoc($tipo_doc, $user)->except(['conteudo']);

        $textos = array();
        if(isset($id))
        {
            $texto = $resultado->find($id);
            if(!isset($texto))
                throw new ModelNotFoundException("No query results for model [App\GerarTexto] no documento ".$tipo_doc.", id = " . $id);

            array_push($textos, $texto);
            // junta os subtítulos com o título escolhido
            if($texto->tituloNumerado())
            {
                foreach($resultado as $key => $val)
                {
                    // somente verifica os itens das ordens seguintes
                    if($val->ordem <= $texto->ordem)
                        continue;
                    // quando encontra o próximo título, encerra
                    if($val->tipoTitulo())
                        break;
                    array_push($textos, $val);
                }
            }

            $btn_anterior = $resultado->where('ordem', '<', $texto->ordem)->where('tipo', GerarTexto::TIPO_TITULO)->last();
            $btn_proximo = $resultado->where('ordem', '>', $texto->ordem)->where('tipo', GerarTexto::TIPO_TITULO)->first();
        }

        return [
            'resultado' => $resultado,
            'textos' => $textos,
            'btn_anterior' => isset($btn_anterior) ? route($tipo_doc, $btn_anterior->id) : null,
            'btn_proximo' => isset($btn_proximo) ? route($tipo_doc, $btn_proximo->id) : null,
        ];
    }

    public function buscar($tipo_doc, $busca, $user = null)
    {
        $resultado = GerarTexto::resultadoByDoc($tipo_doc, $user);

        $textos = array();

        if(isset($busca))
        {
            $busca = $resultado->filter(function ($value, $key) use($busca) {
                $conteudo = strip_tags($value->conteudo);
                $conteudo = stripos($conteudo, htmlentities($busca, ENT_NOQUOTES, 'UTF-8')) !== false;
                $titulo = stripos($value->texto_tipo, $busca) !== false;
                return $conteudo || $titulo;
            });
        }else
            $busca = collect();

        return [
            'resultado' => $resultado,
            'busca' => $busca
        ];
    }
}