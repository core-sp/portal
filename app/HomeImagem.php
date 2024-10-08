<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeImagem extends Model
{
    protected $table = 'home_imagens';
    protected $primaryKey = 'idimagem';
    protected $guarded = [];

    const TOTAL = 10;
    const TOTAL_ITENS_HOME = 10;
    const DEFAULT_CARD_ESCURO = "#004587";
    const DEFAULT_CARD_CLARO = "#15AAE2";
    const DEFAULT_CARD_LATERAL_ESCURO = "#004587";
    const DEFAULT_CARD_LATERAL_CLARO = "#15AAE2";
    const DEFAULT_RODAPE = "#004587";
    const DEFAULT_CALENDARIO = 'img/arte-calendario-2023.png';
    const DEFAULT_HEADER_LOGO = 'img/Selo-para-site002.png';
    const DEFAULT_HEADER_FUNDO = 'img/banner-55-anos.png';
    const DEFAULT_NEVE = 'img/snowing.gif';
    const DEFAULT_POPUP_VIDEO = 'https://www.youtube.com/embed/ACXUu6WiC5k';

    private static function arrayPadrao($nome)
    {
        if(in_array($nome, ['cards_1', 'cards_2']))
            return $nome == 'cards_1' ? ['funcao' => 'cards', 'ordem' => 1] : ['funcao' => 'cards', 'ordem' => 2];

        if(in_array($nome, ['cards_laterais_1', 'cards_laterais_2']))
            return $nome == 'cards_laterais_1' ? ['funcao' => 'cards_laterais', 'ordem' => 1] : ['funcao' => 'cards_laterais', 'ordem' => 2];

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

    public static function recriarItensHome()
    {
        self::where('funcao', '!=', 'bannerprincipal')->delete();

        self::create(['funcao' => 'header_logo', 'ordem' => 1, 'url' => self::DEFAULT_HEADER_LOGO, 'url_mobile' => self::DEFAULT_HEADER_LOGO]);
        self::create(['funcao' => 'header_fundo', 'ordem' => 1, 'url' => self::DEFAULT_HEADER_FUNDO, 'url_mobile' => self::DEFAULT_HEADER_FUNDO]);
        self::create(['funcao' => 'cards', 'ordem' => 1, 'url' => self::DEFAULT_CARD_ESCURO, 'url_mobile' => self::DEFAULT_CARD_ESCURO]);
        self::create(['funcao' => 'cards', 'ordem' => 2, 'url' => self::DEFAULT_CARD_CLARO, 'url_mobile' => self::DEFAULT_CARD_CLARO]);
        self::create(['funcao' => 'cards_laterais', 'ordem' => 1, 'url' => self::DEFAULT_CARD_LATERAL_ESCURO, 'url_mobile' => self::DEFAULT_CARD_LATERAL_ESCURO]);
        self::create(['funcao' => 'cards_laterais', 'ordem' => 2, 'url' => self::DEFAULT_CARD_LATERAL_CLARO, 'url_mobile' => self::DEFAULT_CARD_LATERAL_CLARO]);
        self::create(['funcao' => 'calendario', 'ordem' => 1, 'url' => self::DEFAULT_CALENDARIO, 'url_mobile' => self::DEFAULT_CALENDARIO]);
        self::create(['funcao' => 'footer', 'ordem' => 1, 'url' => self::DEFAULT_RODAPE, 'url_mobile' => self::DEFAULT_RODAPE]);
        self::create(['funcao' => 'neve', 'ordem' => 1, 'url' => null, 'url_mobile' => null]);
        self::create(['funcao' => 'popup_video', 'ordem' => 1, 'url' => null, 'url_mobile' => null]);
    }

    public static function padrao()
    {
        return [
            'cards_1_default' => self::DEFAULT_CARD_ESCURO,
            'cards_2_default' => self::DEFAULT_CARD_CLARO,
            'cards_laterais_1_default' => self::DEFAULT_CARD_LATERAL_ESCURO,
            'cards_laterais_2_default' => self::DEFAULT_CARD_LATERAL_CLARO,
            'footer_default' => self::DEFAULT_RODAPE,
            'calendario_default' => self::DEFAULT_CALENDARIO,
            'header_logo_default' => self::DEFAULT_HEADER_LOGO,
            'header_fundo_default' => self::DEFAULT_HEADER_FUNDO,
            'neve_default' => self::DEFAULT_NEVE,
            'popup_video_default' => self::DEFAULT_POPUP_VIDEO,
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
        if((in_array($campo, ['neve_default', 'popup_video_default'])) && (!isset($valor)))
            return null;
        
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
            $teste = preg_match('/^(img|img-mobile|link|target)-([1-9]|1[0])$/', $key);
            if(($teste === false) || ($teste === 0))
                throw new \Exception('Campo (' .$key. ') não é válido ao atualizar o carrossel.', 400);
            if((strpos($key, 'target') !== false) && (!in_array($value, ['_blank', '_self'])))
                throw new \Exception('Campo (' .$key. ') não é válido ao atualizar o carrossel devido seu valor (' .$value. ') não ser aceito: _blank, _self.', 400);
        }

        return array_chunk($array, 4);
    }

    public static function itensHome()
    {
        return self::whereIn('funcao', ['header_logo', 'header_fundo', 'calendario', 'cards', 'cards_laterais', 'footer', 'neve', 'popup_video'])->get();
    }

    public static function getItemPorResultado($resultado, $campo)
    {
        return $resultado->where('funcao', self::padraoUpdate()[$campo]['funcao'])
            ->where('ordem', self::padraoUpdate()[$campo]['ordem'])
            ->first();
    }

    public function itemDefault()
    {
        if(!isset($this->url))
            return false;

        $campo = ($this->funcao == 'cards') || ($this->funcao == 'cards_laterais') ? $this->funcao . '_' . $this->ordem . '_default' : $this->funcao . '_default';

        return $this->url == self::padrao()[$campo];
    }

    public function possuiImagem()
    {
        if(!isset($this->url))
            return false;

        return preg_match('/#{1}([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})/', $this->url) === 0;
    }

    public function getHeaderFundo()
    {
        return $this->possuiImagem() ? 'background-image: url('.$this->getLinkHref().')' : 'background-color: '.$this->url;
    }

    public function getNeve()
    {
        return $this->possuiImagem() ? 'background-image: url('.$this->getLinkHref().')' : null;
    }

    public function getLinkHref()
    {
        if(!isset($this->url))
            return '';

        $url = '/' . $this->url;

        return strpos('/', $this->url) !== 0 ? $url : $this->url;
    }
}