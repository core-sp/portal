<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GerarTexto extends Model
{
    protected $table = 'gerar_textos';
    protected $guarded = [];

    const SEPARADOR = '.';
    const TIPO_TITULO = 'Título';
    const TIPO_SUBTITULO = 'Subtítulo';

    const DOC_CARTA_SERV = 'carta-servicos';
    
    public static function tipos()
    {
        return [
            self::TIPO_TITULO,
            self::TIPO_SUBTITULO,
        ];
    }

    public static function tiposDoc()
    {
        return [
            self::DOC_CARTA_SERV => 'Carta de serviços ao usuário',
        ];
    }

    public static function criar($tipo_doc)
    {
        $total = self::where('tipo_doc', $tipo_doc)->count() + 1;
        $publicada = $total > 1 ? self::select('publicar')->where('tipo_doc', $tipo_doc)->first()->publicar : false;

        return self::create([
            'texto_tipo' => mb_strtoupper('Título do texto...', 'UTF-8'),
            'conteudo' => '<p>Texto...</p>',
            'ordem' => $total,
            'tipo_doc' => $tipo_doc,
            'publicar' => $publicada,
        ]);
    }

    public static function updateIndice($array, $resultado)
    {
        // Somente atualiza ordem e índice
        $chunk = array_values($array);
        $array = null;
        $indice = '0';

        foreach($chunk as $key => $valor)
        {
            $ordem = $key + 1;
            $indice_array = explode(self::SEPARADOR, $indice);
            $id = (int) apenasNumeros($valor);
            $final = $resultado->find($id);
            if(isset($final))
            {
                $nivel = $final->nivel;

                if($final->com_numeracao)
                {
                    if($nivel == 0)
                    {
                        $indice = (int) substr($indice, 0, $indice_array[0]);
                        $indice = (string) ++$indice;
                    }
                    else{
                        $total = substr_count($indice, self::SEPARADOR);
                        if($total < $nivel)
                            $indice = $indice . self::SEPARADOR . '1';
                        elseif($total >= $nivel)
                        {
                            $temp = (int) $indice_array[$nivel];
                            $indice_array[$nivel] = ++$temp;
                            foreach($indice_array as $key_1 => $val){
                                if($key_1 > $nivel)
                                    unset($indice_array[$key_1]);
                            }
                            $indice = implode(self::SEPARADOR, $indice_array);
                        }
                    }
                }
                
                $final->atualizaOrdemIndice($ordem, $indice);
            }
        }
    }

    public static function resultadoByDoc($tipo_doc, $user)
    {
        return self::where('tipo_doc', $tipo_doc)
            ->when(!isset($user), function($query){
                $query->where('publicar', true);
            })
            ->orderBy('ordem', 'ASC')
            ->get();
    }

    private function atualizaOrdemIndice($ordem, $indice)
    {
        $this->ordem = $ordem;
        $this->indice = $this->com_numeracao ? $indice : null;

        // Somente salva o registro que foi alterado
        if($this->isDirty('ordem') || $this->isDirty('indice'))
            $this->save();
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
