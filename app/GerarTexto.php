<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GerarTexto extends Model
{
    protected $table = 'gerar_textos';
    protected $guarded = [];

    const TOTAL_N_VEZES = 10;
    const SEPARADOR = '.';
    const TIPO_TITULO = 'Título';
    const TIPO_SUBTITULO = 'Subtítulo';

    const DOC_CARTA_SERV = 'carta-servicos';
    const DOC_PREST_CONT = 'prestacao-contas';
    
    public static function orientacaoSumario()
    {
        return [
            self::DOC_CARTA_SERV => 'vertical',
            self::DOC_PREST_CONT => 'horizontal',
        ];
    }
    
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
            self::DOC_PREST_CONT => 'Prestação de Contas',
        ];
    }

    public static function reordenarPorTipo($tipo_doc)
    {
        self::where('tipo_doc', $tipo_doc)->orderBy('ordem')->get()->each(function ($item, $key) {
            $item->update(['ordem' => $key + 1]);
        });
    }

    public static function criar($tipo_doc, $n_vezes = null)
    {
        $total = self::where('tipo_doc', $tipo_doc)->max('ordem') + 1;
        $publicada = $total > 1 ? self::select('publicar')->where('tipo_doc', $tipo_doc)->first()->publicar : false;
        $n_vezes = isset($n_vezes) && ($n_vezes > 1) && ($n_vezes <= self::TOTAL_N_VEZES) ? ($total - 1) + $n_vezes : null;
        $titulo = mb_strtoupper('Título do texto...', 'UTF-8');

        if(isset($n_vezes))
        {
            for($i=$total; $i <= $n_vezes; $i++)
                $final[self::create([
                    'texto_tipo' => $titulo,
                    'ordem' => $i,
                    'tipo_doc' => $tipo_doc,
                    'publicar' => $publicada,
                ])->id] = null;
            $final['texto_tipo'] = $titulo;
        }

        return isset($n_vezes) ? $final : self::create([
            'texto_tipo' => $titulo,
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
            $indice_array = explode(self::SEPARADOR, $indice);
            $id = (int) apenasNumeros($valor);
            $final = $resultado->find($id);
            if(!isset($final))
                continue;

            $nivel = $final->nivel;

            if($nivel == 0)
            {
                $indice = (int) substr($indice, 0, $indice_array[0]);
                $indice = $final->com_numeracao ? (string) ++$indice : (string) $indice;
            }
            else{
                $total = substr_count($indice, self::SEPARADOR);
                if($total < $nivel)
                    $indice = $indice . self::SEPARADOR . '1';
                elseif($total >= $nivel)
                {
                    $temp = (int) $indice_array[$nivel];
                    $indice_array[$nivel] = ++$temp;
                    $indice_array = array_filter($indice_array, function($v, $k) use($nivel){
                        return $k <= $nivel;
                    }, ARRAY_FILTER_USE_BOTH);
                    $indice = implode(self::SEPARADOR, $indice_array);
                }
            }
                
            $final->atualizaOrdemIndice($key + 1, $indice);
        }
    }

    public static function resultadoByDoc($tipo_doc, $user = null)
    {
        return self::where('tipo_doc', $tipo_doc)
            ->when(!isset($user), function($query){
                $query->where('publicar', true);
            })
            ->orderBy('ordem', 'ASC')
            ->get();
    }

    public static function ultimaAtualizacao($tipo_doc)
    {
        return self::where('tipo_doc', $tipo_doc)->max('updated_at');
    }

    public static function getLayoutCliente($resultado, $tipo_doc)
    {
        if($tipo_doc != self::DOC_PREST_CONT)
            return $resultado;

        return self::getLayoutClienteComCollapse($resultado);
    }

    private static function getLayoutClienteComCollapse($resultado)
    {
        if($resultado->isEmpty())
            return '<p><i>Informações sendo atualizadas.</i></p>';

        $fila = collect();
        $data_parent = collect([0 => null, 1 => null, 2 => null, 3 => null]);
        $final = '';

        foreach($resultado as $key => $texto)
        {
            $proximo = $key + 1;

            // Fecha os collapses que sobraram antes do próximo collapse caso o nível atual seja menor ou igual ao nivel do collapse da fila
            foreach($fila as $chave => $collapse)
                if(($chave >= $texto->nivel) && ($chave > 0))
                    $final .= $fila->pull($chave);

            if($texto->tipoTitulo())
            {
                // Fecha o titulo anterior
                if($fila->has(0))
                    $final .= $fila->pull(0);

                $data_parent->put(0, $texto);
                $fila->put(0, '</ul></div></div>');
                $final .= $texto->tituloLayoutHTML();
            }
            else
            {
                if(isset($collapse_texto_anterior) && ($collapse_texto_anterior->nivel < $texto->nivel) && !$texto->possuiConteudo())
                    $data_parent->put($collapse_texto_anterior->nivel, $collapse_texto_anterior);
                if(!$texto->possuiConteudo())
                    $fila->put($texto->nivel, '</ul></div></li>');

                $final .= $texto->possuiConteudo() ? $texto->subtituloLinkLayoutHTML() : $texto->subtituloCollapseLayoutHTML($data_parent);

                // Se não for último loop, verifica se o próximo é um collapse e o anterior não é titulo
                    // Se for o atual maior que o próximo, fecha o collapse pai do nivel atual
                        // Se for igual fecha o collapse atual se existir
                if(($proximo) < $resultado->count())
                {
                    if(($texto->nivel > $resultado->get($proximo)->nivel) && isset($collapse_texto_anterior) && !$collapse_texto_anterior->tipoTitulo())
                        $final .= $fila->pull($collapse_texto_anterior->nivel);
                    elseif(($texto->nivel == $resultado->get($proximo)->nivel) && $fila->has($texto->nivel))
                        $final .= $fila->pull($texto->nivel);
                }
            }

            if(!$texto->possuiConteudo())
              $collapse_texto_anterior = $texto;

            // Fecha tudo que sobrou do nivel maior ao menor
            if(($proximo) >= $resultado->count())
                foreach($fila->sortKeysDesc() as $chave => $collapse)
                    $final .= $fila->pull($chave);
        }

        return '<div id="accordionPrimario" class="accordion">' . $final . '</div>';
    }

    private function tituloLayoutHTML()
    {
        if($this->tipo_doc == self::DOC_PREST_CONT)
        {
            $imagem = $this->existeImg() ? $this->getImgHTML() : $this->texto_tipo;
            $teste = '<p class="pb-0"><a href="#lista-' . $this->id . '-' . $this->textoTipoSlug() . '" data-toggle="collapse">';
            $teste .= $this->existeImg() ? $imagem : '<strong><u>' . $imagem . '</u></strong>';
            $teste .= '</a></p><div id="lista-' . $this->id . '-' . $this->textoTipoSlug() . '" class="collapse" data-parent="#accordionPrimario">';
            $teste .= '<div id="accordion' . $this->textoTipoStudly() . '" class="accordion"><ul class="mb-0 pb-0">';
            return $teste;
        }
    }

    private function subtituloCollapseLayoutHTML($data_parent)
    {
        if($this->tipo_doc == self::DOC_PREST_CONT)
        {
            $antes = ($this->nivel > 1) ? $data_parent->get($this->nivel - 1) : $data_parent->get(0);
            $teste = '<li><a href="#lista-' . $this->id . '-' . $this->textoTipoSlug() . '" target="_blank" rel="noopener" data-toggle="collapse">';
            $teste .= $this->texto_tipo . '</a><div id="lista-' . $this->id . '-' . $this->textoTipoSlug() . '" class="collapse" data-parent="#';
            $teste .= ($this->nivel > 1) ? 'lista-' . $antes->id . '-' . $antes->textoTipoSlug() : 'accordion' . $antes->textoTipoStudly();
            $teste .= '"><ul class="mb-0 pb-0">';
            return $teste;
        }
    }

    private function subtituloLinkLayoutHTML()
    {
        if($this->tipo_doc == self::DOC_PREST_CONT)
            return '<li><a href="' . $this->conteudo . '" target="_blank" rel="noopener">' . $this->texto_tipo . '</a></li>';
    }

    private function atualizaOrdemIndice($ordem, $indice)
    {
        $this->nivel = substr_count($indice, self::SEPARADOR);
        $this->ordem = $ordem;
        $this->indice = $this->com_numeracao ? $indice : null;

        // Somente salva o registro que foi alterado
        if($this->isDirty('ordem') || $this->isDirty('indice') || $this->isDirty('nivel'))
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
        return ($this->tipo == self::TIPO_TITULO) || ($this->nivel == 0);
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

    public function possuiConteudo()
    {
        if($this->tipo_doc == self::DOC_PREST_CONT)
            return isset($this->conteudo) && Str::startsWith($this->conteudo, 'https://');
        return strlen($this->conteudo) > 0;
    }

    public function textoTipoSlug()
    {
        return Str::slug(strtolower($this->texto_tipo), '-');
    }

    public function textoTipoStudly()
    {
        return Str::studly($this->textoTipoSlug());
    }

    public function existeImg()
    {
        return ($this->tipo_doc == self::DOC_PREST_CONT) && \File::exists(public_path('img/icone-' . $this->textoTipoSlug() . '.png'));
    }

    public function getImgHTML()
    {
        if($this->tipo_doc == self::DOC_PREST_CONT)
            return '<img src="'. asset('img/icone-' . $this->textoTipoSlug() . '.png') . '" width="320" height="143" />';
    }
}
