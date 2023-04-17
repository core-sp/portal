<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $table = 'newsletters';
    protected $primaryKey = 'idnewsletter';
    protected $guarded = [];

    public function termos()
    {
        return $this->hasMany('App\TermoConsentimento', 'idnewsletter');
    }

    public static function getLista()
    {
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=newsletter-'.date('Ymd').'.csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ];
        $lista = Newsletter::select('email','nome','celular','created_at')->get();
        $lista = $lista->toArray();
        array_unshift($lista, array_keys($lista[0]));
        $callback = function() use($lista) {
            $fh = fopen('php://output','w');
            fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
            foreach($lista as $linha) {
                fputcsv($fh,$linha,';');
            }
            fclose($fh);
        };

        return [
            'arquivo' => $callback,
            'headers' => $headers,
        ];
    }
}
