<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GerarTexto extends Model
{
    protected $table = 'gerar_textos';
    protected $guarded = [];

    const TIPO_TITULO = 'Título';
    const TIPO_SUBTITULO = 'Subtítulo';
    
    public static function tipos()
    {
        return [
            self::TIPO_TITULO,
            self::TIPO_SUBTITULO,
        ];
    }

    public static function tipos_doc()
    {
        return [
            'carta-servicos' => 'Carta de serviços ao usuário',
        ];
    }

    public static function criar($tipo_doc)
    {
        $total = GerarTexto::where('tipo_doc', $tipo_doc)->count() + 1;
        $publicada = $total > 1 ? GerarTexto::select('publicar')->where('tipo_doc', $tipo_doc)->first()->publicar : false;

        return GerarTexto::create([
            'texto_tipo' => mb_strtoupper('Título do texto...', 'UTF-8'),
            'conteudo' => '<p>Texto...</p>',
            'ordem' => $total,
            'tipo_doc' => $tipo_doc,
            'publicar' => $publicada,
        ]);
    }

    public static function updateIndice($array, $resultado)
    {
        // Somente atualiza ordem e indice
        $chunk = array_chunk($array, 1, true);
        $indice = '0';

        foreach($chunk as $key => $valor)
        {
            $ordem = $key + 1;
            $indice_array = explode('.', $indice);
            $id = (int) apenasNumeros(array_values($valor)[0]);
            $final = $resultado->find($id);
            if(isset($final))
            {
                $nivel = $final->nivel;
                $com_num = $final->com_numeracao;

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
                            foreach($indice_array as $key_1 => $val){
                                if($key_1 > $nivel)
                                    unset($indice_array[$key_1]);
                            }
                            $indice = implode('.', $indice_array);
                        }
                    }
                }
            
                $final->update([
                    'ordem' => $ordem,
                    'indice' => $com_num ? $indice : null,
                ]);
            }
        }
    }

    public function getCorTituloSub()
    {
        return 'style="color: #548dd4;"';
    }

    public function tituloNumerado()
    {
        return $this->tipoTitulo() && $this->com_numeracao;
    }

    public function tipoTitulo()
    {
        return $this->tipo == self::TIPO_TITULO;
    }

    public function indiceFormatada()
    {
        return isset($this->indice) ? $this->indice . '. ' : '';
    }

    public function tituloFormatado()
    {
        return $this->indiceFormatada() . $this->texto_tipo;
    }

    public function subtituloFormatado()
    {
        return $this->indice . ' - ' . $this->texto_tipo;
    }
}
