<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeImagem extends Model
{
    protected $table = 'home_imagens';
    protected $primaryKey = 'idimagem';
    protected $guarded = [];

    const TOTAL = 7;
    const DEFAULT_CARD_ESCURO = "#004587";
    const DEFAULT_CARD_CLARO = "#15AAE2";
    const DEFAULT_RODAPE = "#004587";
    const DEFAULT_CALENDARIO = 'img/arte-calendario-2023.png';
    const DEFAULT_HEADER_LOGO = 'img/Selo-para-site002.png';
    const DEFAULT_HEADER_FUNDO = '/img/banner-55-anos.png';

    private static function arrayPadrao($nome)
    {
        if(in_array($nome, ['cards_1', 'cards_2']))
            return $nome == 'cards_1' ? ['funcao' => 'cards', 'ordem' => 1] : ['funcao' => 'cards', 'ordem' => 2];

        return ['funcao' => $nome, 'ordem' => 1];
    }

    public static function pathCompleto()
    {
        return public_path() . '/';
    }

    public static function caminhoStorage()
    {
        return str_replace(self::pathCompleto(), '', Storage::disk('itens_home')->getAdapter()->getPathPrefix());
    }

    public static function padrao()
    {
        return [
            'cards_1_default' => self::DEFAULT_CARD_ESCURO,
            'cards_2_default' => self::DEFAULT_CARD_CLARO,
            'footer_default' => self::DEFAULT_RODAPE,
            'calendario_default' => self::DEFAULT_CALENDARIO,
            'header_logo_default' => self::DEFAULT_HEADER_LOGO,
            'header_fundo_default' => self::DEFAULT_HEADER_FUNDO,
        ];
    }

    public static function padraoUpdate()
    {
        $padrao = self::padrao();
        $final = array();

        foreach($padrao as $key => $valor)
        {
            $sem_default = str_replace('_default', '', $key);
            $final[$key] = self::arrayPadrao(str_replace('_default', '', $key));
            $final[$sem_default] = $final[$key];
        }

        return $final;
    }

    public static function getValor($campo, $valor)
    {
        if($valor instanceof \Illuminate\Http\UploadedFile)
        {
            $url = $valor->getClientOriginalName();
            $valor->storeAs('/', $url, 'itens_home');
            $valor = str_replace(self::pathCompleto(), '', Storage::disk('itens_home')->path($url));
        }

        return isset(self::padrao()[$campo]) ? self::padrao()[$campo] : $valor;
    }

    public static function validacao($array)
    {
        $total = count($array);
        $totalPermitido = 4 * self::TOTAL;

        // sendo 4 a quantidade de campos diferentes: img|img-mobile|link|target
        if($total != $totalPermitido)
            throw new \Exception('Possui total de campos (' .$total. ') diferente do permitido (' .$totalPermitido. '), então não é válido ao atualizar o carrossel.', 400);

        foreach($array as $key => $value)
        {
            $teste = preg_match('/^(img|img-mobile|link|target)-([1-' .self::TOTAL. ']){1}$/', $key);
            if(($teste === false) || ($teste === 0))
                throw new \Exception('Campo (' .$key. ') não é válido ao atualizar o carrossel devido não ser compatível com: img-1 ou img-mobile-1 ou link-1 ou target-1.', 400);
            if((strpos($key, 'target') !== false) && (!in_array($value, ['_blank', '_self'])))
                throw new \Exception('Campo (' .$key. ') não é válido ao atualizar o carrossel devido seu valor (' .$value. ') não ser aceito: _blank, _self.', 400);
        }

        return array_chunk($array, 4);
    }

    public static function getItemPorResultado($resultado, $campo)
    {
        return $resultado->where('funcao', self::padraoUpdate()[$campo]['funcao'])
            ->where('ordem', self::padraoUpdate()[$campo]['ordem'])
            ->first();
    }

    public function itemDefault()
    {
        $campo = $this->funcao == 'cards' ? $this->funcao . '_' . $this->ordem . '_default' : $this->funcao . '_default';

        return $this->url == self::padrao()[$campo];
    }

    public function possuiImagem()
    {
        return preg_match('/#{1}([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})/', $this->url) === 0;
    }

    public function getHeaderFundo()
    {
        if($this->possuiImagem())
            return 'background-image: url('.$this->url.')';
        return 'background-color: '.$this->url;
    }
}