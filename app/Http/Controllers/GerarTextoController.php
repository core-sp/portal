<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\GerarTexto;
use Illuminate\Support\Str;

class GerarTextoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['show', 'buscar']]);
    }
    
    public function create($tipo_doc)
    {
        $total = GerarTexto::count() + 1;
        $texto = GerarTexto::create([
            'texto_tipo' => mb_strtoupper('Título do texto...', 'UTF-8'),
            'ordem' => $total,
            'tipo_doc' => $tipo_doc,
        ]);

        return response()->json($texto);
    }

    public function updateCampos($tipo_doc, $id, Request $request)
    {
        // atualiza os campos, não atualiza ordem e indice
        $dados = $request->except(['_token', '_method']);
        $dados['texto_tipo'] = mb_strtoupper($dados['texto_tipo'], 'UTF-8');
        $resultado = GerarTexto::where('tipo_doc', $tipo_doc)->where('id', $id)->firstOrFail();

        $ok = $resultado->update($dados);

        return response()->json($ok);
    }

    public function delete($tipo_doc, $id)
    {
        $ok = GerarTexto::where('tipo_doc', $tipo_doc)->where('id', $id)->firstOrFail()->delete();

        return response()->json($ok);
    }

    public function view($tipo_doc)
    {
        $resultado = GerarTexto::where('tipo_doc', $tipo_doc)->orderBy('ordem','ASC')->get();
        $variaveis = [
            'singular' => 'texto',
            'singulariza' => 'o texto',
            'plural' => 'texto',
            'pluraliza' => 'texto',
            'form' => 'texto'
        ];
        $variaveis = (object) $variaveis;

        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update($tipo_doc, Request $request)
    {
        // Somente atualiza ordem e indice

        $resultado = GerarTexto::where('tipo_doc', $tipo_doc)->orderBy('ordem','ASC')->get();
        $array = $request->except(['_token', '_method']);

        $chunk = array_chunk($array, 2, true);
        $indice = '0';

        foreach($chunk as $key => $valor)
        {
            $ordem = $key + 1;
            $indice_array = explode('.', $indice);
            $id = apenasNumeros(array_keys($valor)[0]);
            $nivel = $valor['nivel-' . $id];
            $com_num = $valor['com_numeracao-' . $id];

            if($com_num)
            {
                if($nivel == 0)
                {
                    $indice = (int) substr($indice, 0, $indice_array[0]);
                    $indice++;
                    $indice = (string) $indice;
                }
                else{
                    $total = substr_count($indice, '.');
                    if($total < $nivel)
                        $indice .= '.1';
                    elseif($total >= $nivel)
                    {
                        $temp = (int) $indice_array[$nivel];
                        $temp++;
                        $indice_array[$nivel] = $temp;
                        foreach($indice_array as $key => $val)
                            if($key > $nivel)
                                unset($indice_array[$key]);
                        $indice = implode('.', $indice_array);
                    }
                }
            }

            $resultado->find($id)->update([
                'ordem' => $ordem,
                'indice' => $com_num ? $indice : null,
            ]);
        }
        
        return redirect()->route('textos.view', $tipo_doc)
            ->with('message', '<i class="icon fa fa-check"></i>Índice atualizada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function publicar($tipo_doc, Request $request)
    {
        $ok = GerarTexto::where('tipo_doc', $tipo_doc)->update([
            'publicar' => $request->input('publicar')
        ]);

        return response()->json($ok);
    }

    public function show($id = null)
    {
        $tipo_doc = \Route::currentRouteName();
        $resultado = GerarTexto::where('tipo_doc', $tipo_doc)->where('publicar', true)->orderBy('ordem','ASC')->get();
        $textos = array();
        if(isset($id))
        {
            $teste = $resultado->find($id);
            if($teste->tipo == 'Título' && $teste->com_numeracao)
                foreach($resultado as $key => $val)
                    Str::startsWith($val->indice, $teste->indice) ? array_push($textos, $val) : null;       
            else
                array_push($textos, $teste);
        }

        return response()
            ->view('site.'.$tipo_doc, compact('resultado', 'textos'))
            ->header('Cache-Control','no-cache');
    }

    public function buscar(Request $request)
    {
        $tipo_doc = Str::beforeLast(\Route::currentRouteName(), '-buscar');
        $busca = $request->only('busca')['busca'];
        $resultado = GerarTexto::where('tipo_doc', $tipo_doc)->where('publicar', true)->orderBy('ordem','ASC')->get();
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

        return response()
            ->view('site.'.$tipo_doc, compact('resultado', 'busca'))
            ->header('Cache-Control','no-cache');
    }
}
