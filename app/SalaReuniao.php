<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalaReuniao extends Model
{
    protected $table = 'salas_reunioes';
    protected $guarded = [];

    const ITEM_TV = 'TV _ polegadas com entrada HDMI';
    const ITEM_CABO = 'Cabo de _ metro(s)';
    const ITEM_AR = 'Ar-condicionado';
    const ITEM_MESA = 'Mesa com _ cadeira(s)';
    const ITEM_AGUA = 'Água';
    const ITEM_CAFE = 'Café';
    const ITEM_WIFI = 'Wi-Fi';

    public static function itens()
    {
        return [
            'tv' => self::ITEM_TV,
            'cabo' => self::ITEM_CABO,
            'ar' => self::ITEM_AR,
            'mesa' => self::ITEM_MESA,
            'agua' => self::ITEM_AGUA,
            'cafe' => self::ITEM_CAFE,
            'wifi' => self::ITEM_WIFI,
        ];
    }

    public static function itensCoworking()
    {
        $itens = [
            'ar' => self::ITEM_AR,
            'mesa' => self::ITEM_MESA,
            'agua' => self::ITEM_AGUA,
            'cafe' => self::ITEM_CAFE,
            'wifi' => self::ITEM_WIFI,
        ];

        foreach($itens as $key => $val){
            if(strpos($val, '_') !== false)
                $itens[$key] = str_replace('_', '1', $val);
        }

        return $itens;
    }

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function getHorariosManha($tipo)
    {
        return $tipo == 'reuniao' ? json_decode($this->horarios_reuniao, true)['manha'] : json_decode($this->horarios_coworking, true)['manha'];
    }

    public function getHorariosTarde($tipo)
    {
        return $tipo == 'reuniao' ? json_decode($this->horarios_reuniao, true)['tarde'] : json_decode($this->horarios_coworking, true)['tarde'];
    }

    public function getItens($tipo)
    {
        return $tipo == 'reuniao' ? json_decode($this->itens_reuniao, true) : json_decode($this->itens_coworking, true);
    }

    public function getItensOriginaisReuniao()
    {
        if(strlen($this->itens_reuniao) < 5)
            return self::itens();

        $all = json_decode($this->itens_reuniao, true);
        $original = self::itens();
        foreach($all as $key_ => $val_)
            $all[$key_] = preg_replace('/[0-9,]/', '', $val_);
        foreach($original as $key => $val){
            $original[$key] = str_replace('_', '', $val);
            if(in_array($original[$key], $all))
                unset($original[$key]);
        }
        
        return $original;
    }

    public function getItensOriginaisCoworking()
    {
        if(strlen($this->itens_coworking) < 5)
            return self::itensCoworking();
            
        $all = json_decode($this->itens_coworking, true);
        $original = self::itensCoworking();
        foreach($original as $key => $val){
            $original[$key] = str_replace('_', '', $val);
            if(in_array($original[$key], $all))
                unset($original[$key]);
        }
        
        return $original;
    }
}
