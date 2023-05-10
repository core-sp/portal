<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\GerarTexto;
use Illuminate\Support\Arr;

class GerarTextoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function create()
    {
        $dados = GerarTexto::create([
            'texto_tipo' => 'Título do texto...'
        ]);

        return response()->json($dados);
    }

    public function updateCampos($id, Request $request)
    {
        // atualiza os campos, não atualiza ordem e indice
        $dados = $request->except(['_token', '_method']);
        $resultado = GerarTexto::findOrFail($id);

        $ok = $resultado->update($dados);

        return response()->json($ok);
    }

    public function delete($id)
    {
        $ok = GerarTexto::findOrFail($id)->delete();

        return response()->json($ok);
    }

    public function view()
    {
        $resultado = GerarTexto::orderBy('ordem','ASC')->get();
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

    public function update(Request $request)
    {
        // Somente atualiza ordem e indice

        $resultado = GerarTexto::orderBy('ordem','ASC')->get();
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
        
        return redirect('/admin/textos/teste')
            ->with('message', '<i class="icon fa fa-check"></i>Índice atualizada com sucesso!')
            ->with('class', 'alert-success');
    }
}
