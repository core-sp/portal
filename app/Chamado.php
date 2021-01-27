<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chamado extends Model
{
    use SoftDeletes;

	protected $primaryKey = 'idchamado';
    protected $table = 'chamados';
    protected $fillable = ['tipo', 'prioridade', 'mensagem', 'img', 'idusuario', 'resposta'];

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public static function tipos()
    {
        return [
            'Dúvida',
            'Reportar Bug',
            'Sugestão',
            'Solicitar Funcionalidade'
        ]; 
    }

    public static function prioridades()
    {
        return [
            'Muito Baixa',
            'Baixa',
            'Normal',
            'Alta',
            'Muito Alta'
        ];
    }
}
