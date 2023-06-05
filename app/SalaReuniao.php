<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalaReuniao extends Model
{
    protected $table = 'salas_reunioes';
    protected $guarded = [];

    const ITEM_TV = 'TV _ polegadas com entrada HDMI';
    const ITEM_CABO = 'Cabo de _ metro(s) para conexão HDMI';
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
        if($tipo == 'reuniao')
            return json_decode($this->horarios_reuniao, true)['manha'];
        if($tipo == 'coworking')
            return json_decode($this->horarios_coworking, true)['manha'];
        return array();
    }

    public function getHorariosTarde($tipo)
    {
        if($tipo == 'reuniao')
            return json_decode($this->horarios_reuniao, true)['tarde'];
        if($tipo == 'coworking')
            return json_decode($this->horarios_coworking, true)['tarde'];
        return array();
    }

    public function getItens($tipo)
    {
        if($tipo == 'reuniao')
            return json_decode($this->itens_reuniao, true);
        if($tipo == 'coworking')
            return json_decode($this->itens_coworking, true);
        return array();
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
            $temp = str_replace('_', '', $val);
            if(in_array($temp, $all))
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
            $temp = str_replace('_', '', $val);
            if(in_array($temp, $all))
                unset($original[$key]);
        }
        
        return $original;
    }

    public function verificaAlteracaoItens($reuniao, $coworking, $participantes)
    {
        $itens['reuniao'] = array();
        $itens['coworking'] = array();
        $gerente = null;

        if($this->wasChanged('itens_reuniao')){
            $itens['reuniao'] = array_diff($reuniao, $this->getItens('reuniao'));
            $itens['reuniao'] = array_merge($itens['reuniao'], array_diff($this->getItens('reuniao'), $reuniao));
        }
        if($this->wasChanged('participantes_reuniao'))
            $itens['reuniao']['participantes'] = $participantes['reuniao'];
            
        if($this->wasChanged('itens_coworking')){
            $itens['coworking'] = array_diff($coworking, $this->getItens('coworking'));
            $itens['coworking'] = array_merge($itens['coworking'], array_diff($this->getItens('coworking'), $coworking));
        }
        if($this->wasChanged('participantes_coworking'))
            $itens['coworking']['participantes'] = $participantes['coworking'];

        if(!empty($itens['reuniao']) || !empty($itens['coworking'])){
            $gerente = $this->regional->users()
            ->when(in_array($this->idregional, [1, 14]), function ($query){
                return $query->select('nome', 'email')->where('idusuario', 39);
            }, function ($query) {
                return $query->select('nome', 'email')->where('idperfil', 21)->where('email', 'LIKE', 'ger%');
            })->first();
        }

        return [
            'gerente' => $gerente,
            'itens' => $itens,
        ];
    }
}
