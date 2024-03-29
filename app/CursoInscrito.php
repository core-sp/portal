<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CursoInscrito extends Model
{
    use SoftDeletes;
    
    protected $primaryKey = 'idcursoinscrito';
    protected $table = 'curso_inscritos';
    protected $guarded = [];
    protected $with = ['curso'];

    const INSCRITO_FUN = 'Funcionário';
    const INSCRITO_AUT = 'Autoridade';
    const INSCRITO_CON = 'Convidado';
    const INSCRITO_PAR = 'Parceiro';
    const INSCRITO_SITE = 'Site';

    public static function tiposInscricao()
    {
        $tipos = [
            self::INSCRITO_FUN,
            self::INSCRITO_AUT,
            self::INSCRITO_CON,
            self::INSCRITO_PAR,
            self::INSCRITO_SITE,
        ];

        sort($tipos);

        return $tipos;
    }

    public function curso()
    {
    	return $this->belongsTo('App\Curso', 'idcurso');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario');
    }

    public function termos()
    {
        return $this->hasMany('App\TermoConsentimento', 'idcursoinscrito');
    }

    public function possuiPresenca()
    {
        return isset($this->presenca);
    }

    public function compareceu()
    {
        return isset($this->presenca) && ($this->presenca == 'Sim');
    }

    public function podeCancelar()
    {
        return !$this->curso->encerrado() && $this->curso->noPeriodoDeInscricao();
    }

    public function textoAgradece()
    {
        $agradece = "Sua inscrição em <strong>".$this->curso->tipo;
        $agradece .= " - ".$this->curso->tema."</strong>";
        $agradece .= " (turma ".$this->curso->idcurso.") foi efetuada com sucesso.";
        $agradece .= "<br><br>";
        $agradece .= "<strong>Detalhes da inscrição</strong><br>";
        $agradece .= "Nome: ".$this->nome."<br>";
        $agradece .= "CPF: ".$this->cpf."<br>";
        $agradece .= "Telefone: ".$this->telefone;
        $agradece .= "<br><br>";
        $agradece .= "<strong>Detalhes do curso</strong><br>";
        $agradece .= "Nome: ".$this->curso->tipo." - ".$this->curso->tema."<br>";
        $agradece .= "Nº da turma: ".$this->curso->idcurso."<br>";
        $agradece .= "Endereço: ".$this->curso->endereco."<br>";
        $agradece .= "Data de Início: ".onlyDate($this->curso->datarealizacao)."<br>";
        $agradece .= "Horário: ".onlyHour($this->curso->datarealizacao)."h<br>";
        $adendo = '<i>* As informações foram enviadas ao email cadastrado no formulário</i>';

        return [
            'agradece' => $agradece,
            'adendo' => $adendo
        ];
    }

    public function valorCampoAdicional()
    {
        if(!isset($this->campo_adicional))
            return '';

        return explode(': ', $this->campo_adicional)[1];
    }
}
