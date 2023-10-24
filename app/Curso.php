<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Curso extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idcurso';
    protected $table = 'cursos';
    protected $guarded = [];

    const ACESSO_PRI = 'Privado';
    const ACESSO_PUB = 'Público';

    const TIPO_CURSO = 'Curso';
    const TIPO_EVENTO = 'Evento Comemorativo';
    const TIPO_LIVE = 'Live';
    const TIPO_PALESTRA = 'Palestra';
    const TIPO_WORK = 'Workshop';

    const TEXTO_BTN_INSCRITO = "btn btn-sm btn-dark text-center text-uppercase text-white mt-2 disabled";
    
    private static function inputText($rotulo, $value, $possuiErro = false, $classes = '')
    {
        $textoErro = $possuiErro ? 'is-invalid' : '';

        return '<input type="text" name="' . $rotulo . '" class="form-control '.$textoErro. ' ' .$classes.'" value="'.$value.'" />';
    }

    public static function tipos()
    {
        return [
            self::TIPO_CURSO,
            self::TIPO_EVENTO,
            self::TIPO_LIVE,
            self::TIPO_PALESTRA,
            self::TIPO_WORK,
        ];
    }

    public static function acessos()
    {
        return [
            self::ACESSO_PRI,
            self::ACESSO_PUB,
        ];
    }

    public static function rotulos()
    {
        return [
            'placa_veiculo' => 'Placa do veículo'
        ];
    }

    public static function inputs($value, $possuiErro = false)
    {
        return [
            'placa_veiculo' => self::inputText('placa_veiculo', $value, $possuiErro, 'placaVeiculo'),
        ];
    }

    public static function regras()
    {
        return [
            'placa_veiculo' => 'size:8',
        ];
    }

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function cursoinscrito()
    {
    	return $this->hasMany('App\CursoInscrito', 'idcurso');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function noticia()
    {
        return $this->hasMany('App\Noticia', 'idcurso');
    }

    private function possuiVagas()
    {
        return $this->nrvagas > $this->loadCount('cursoinscrito')->cursoinscrito_count;
    }

    public function noPeriodoDeInscricao()
    {
        $now = now()->format('Y-m-d H:i');
        return ($this->inicio_inscricao <= $now) && ($this->termino_inscricao >= $now);
    }

    public function representanteInscrito($cpf)
    {
    	return $this->cursoinscrito()->where('cpf', $cpf)->exists();
    }

    public function publicado()
    {
        return $this->publicado == 'Sim';
    }

    public function acessoPrivado()
    {
        return $this->acesso == self::ACESSO_PRI;
    }

    public function liberarAcesso($rep = false, $situacao = '')
    {
        return !$this->acessoPrivado() || ($this->acessoPrivado() && $rep && ($situacao == 'Situação: Em dia.'));
    }

    public function textoAcesso()
    {
        if(!$this->acessoPrivado())
            return 'Aberta ao público';
        if($this->acessoPrivado())
            return 'Restrita para representantes';
    }

    public function podeInscrever()
    {
        return !$this->encerrado() && $this->noPeriodoDeInscricao() && $this->possuiVagas();
    }

    public function podeInscreverExterno()
    {
        return !$this->encerrado() && $this->noPeriodoDeInscricao() && $this->possuiVagas() && $this->publicado();
    }

    public function encerrado()
    {
        return $this->datatermino <= now()->format('Y-m-d H:i');
    }

    public function btnSituacao()
    {
        if($this->encerrado())
            return '<div class="sit-btn sit-vermelho">Já realizado</div>';

        return $this->podeInscreverExterno() ? '<div class="sit-btn sit-verde">Vagas Abertas</div>' : '<div class="sit-btn sit-vermelho">Vagas esgotadas</div>';
    }

    public function possuiNoticia()
    {
        $noticia = $this->noticia->first();
        return isset($noticia);
    }

    public function getNoticia()
    {
        $noticia = $this->noticia->first();
        return isset($noticia) ? $noticia->slug : null;
    }

    public function getRegras()
    {
        if(!isset($this->campo_rotulo))
            return [];

        $regras = self::regras()[$this->campo_rotulo];
        $required = $this->campo_required ? 'required|' : 'nullable|';

        return [$this->campo_rotulo => $required . $regras];
    }

    public function nomeRotulo()
    {
        if(!isset($this->campo_rotulo))
            return '';

        return self::rotulos()[$this->campo_rotulo];
    }
}
