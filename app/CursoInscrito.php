<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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

    public static function gerarCodigoCertificado($conta_portal = null)
    {
        return !isset($conta_portal) ? (string) Str::uuid() : null;
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

    private function linkTextoPaginaCertificado()
    {
        $txt = 'Link com o código para gerar o certificado em caso de presença confirmada após encerramento: <a href="';
        $txt .= route('cursos.show', ['id' => $this->curso->idcurso, 'certificado' => $this->codigo_certificado]);
        $txt .= '" target="_blank">Abrir</a><br>Código: '.$this->codigo_certificado;

        return $txt;
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

        if($this->curso->tipoParaCertificado())
        {
            $agradece .= "<br><strong>Certificado</strong><br>";
            $agradece .= isset($this->codigo_certificado) ? $this->linkTextoPaginaCertificado().'<br>' : 
                "Em caso de presença confirmada após encerramento, pode ser gerado o certificado na sua área restrita.<br>";
        }

        $adendo = '<i>* As informações foram enviadas ao email cadastrado no formulário</i>';

        return [
            'agradece' => $agradece,
            'adendo' => $adendo
        ];
    }

    public function textoReenvio()
    {
        $agradece = "Sua presença em <strong>".$this->curso->tipo;
        $agradece .= " - ".$this->curso->tema."</strong>";
        $agradece .= " (turma ".$this->curso->idcurso.") foi confirmada.";
        $agradece .= "<br><br>";
        $agradece .= "<strong>Detalhes da inscrição</strong><br>";
        $agradece .= "Nome: ".$this->nome."<br>";
        $agradece .= "CPF: ".$this->cpf."<br>";
        $agradece .= "Telefone: ".$this->telefone;
        $agradece .= "<br>";
        $agradece .= '<br><strong>Certificado</strong><br>'.$this->linkTextoPaginaCertificado().'<br>';

        return $agradece;
    }

    public function valorCampoAdicional()
    {
        if(!isset($this->campo_adicional))
            return '';

        return explode(': ', $this->campo_adicional)[1];
    }

    public function possuiCodigoCertificado()
    {
        return isset($this->codigo_certificado);
    }

    public function getChecksum()
    {
        $checksum = hash('sha256', $this->nome . '|' . $this->cpf . '|' . $this->idcurso . '|' . $this->idcursoinscrito . '|' . now()->timestamp);
        $this->update(['checksum' => $checksum]);

        return $checksum;
    }

    public function podeGerarCertificado($conta_portal)
    {
        if(!$this->curso->tipoParaCertificado())
            return [
                'message' => 'O tipo do curso não está incluso para gerar certificado.',
                'class' => 'alert-danger'
            ];

        if(!$this->curso->encerrado())
            return [
                'message' => 'O curso deve estar encerrado para gerar certificado.',
                'class' => 'alert-danger'
            ];

        if($this->possuiCodigoCertificado() && isset($conta_portal))
        {
            $this->update(['codigo_certificado' => null]);

            return [
                'message' => 'Este CPF / CNPJ já possui conta no Portal, deve gerar o certificado pela área restrita. O código foi invalidado.',
                'class' => 'alert-warning'
            ];
        }

        if(!isset($this->presenca))
            return [
                'message' => 'Ainda não foi atualizada a presença no sistema para gerar o certificado.',
                'class' => 'alert-danger'
            ];

        if(!$this->compareceu())
            return [
                'message' => 'Este CPF / CNPJ não teve a presença confirmada para gerar o certificado.',
                'class' => 'alert-danger'
            ];
    }
}
