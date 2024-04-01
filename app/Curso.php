<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

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

    const CERT_ID_MAIOR_QUE = 67;
    
    private static function inputText($rotulo, $value, $required = false, $possuiErro = false, $classes = '')
    {
        $textoErro = $possuiErro ? 'is-invalid' : '';
        $textoRequired = $required ? 'required' : '';

        return '<input type="text" name="' . $rotulo . '" class="form-control '.$textoErro. ' ' .$classes.'" value="'.$value.'" '.$textoRequired.' />';
    }

    private static function inputDate($rotulo, $value, $required = false, $possuiErro = false, $classes = '')
    {
        $textoErro = $possuiErro ? 'is-invalid' : '';
        $textoRequired = $required ? 'required' : '';

        return '<input type="date" name="' . $rotulo . '" class="form-control '.$textoErro. ' ' .$classes.'" value="'.$value.'" '.$textoRequired.' />';
    }

    private static function inputSelect($rotulo, $options = [], $value, $required = false, $possuiErro = false, $classes = '')
    {
        $textoErro = $possuiErro ? 'is-invalid' : '';
        $textoRequired = $required ? 'required' : '';

        $select = '<select name="' . $rotulo . '" class="form-control '.$textoErro. ' ' .$classes.'" '.$textoRequired.'>';
        $option = '<option value="">Selecione...</option>';

        foreach($options as $key => $valor)
        {
            $selected = $value == $key ? 'selected' : '' ;
            $option .= '<option value="'.$key.'" '.$selected.'>'.$valor.'</option>';
        }

        return $select . $option . '</select>';
    }

    private static function optionsPorRotulo()
    {
        return [
            'exemplo_select' => ['exemplo_1' => 'Exemplo Um', 'exemplo_2' => 'Exemplo Dois'],
        ];
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

    public static function tiposCertificado()
    {
        return [
            self::tipos()[0],
            self::tipos()[3],
            self::tipos()[4],
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
            'placa_veiculo' => 'Placa do veículo',
            // 'exemplo_select' => 'Exemplo Select',
        ];
    }

    public static function inputs($value, $required = false, $possuiErro = false)
    {
        return [
            'placa_veiculo' => self::inputText('placa_veiculo', $value, $required, $possuiErro, 'placaVeiculo'),
            // 'exemplo_select' => self::inputSelect('exemplo_select', self::optionsPorRotulo()['exemplo_select'], $value, $required, $possuiErro),
        ];
    }

    public static function regras()
    {
        return [
            'placa_veiculo' => 'size:8|regex:/([A-Z]{3})([\s\-]{1})([0-9]{1})([A-Z0-9]{1})([0-9]{2})/',
            // 'exemplo_select' => 'in:' . implode(',', array_keys(self::optionsPorRotulo()['exemplo_select'])),
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

    public function semPeriodoInscricao()
    {
        return !isset($this->inicio_inscricao) && !isset($this->termino_inscricao);
    }

    public function aguardandoAbrirInscricao()
    {
        return !$this->encerrado() && ($this->semPeriodoInscricao() || ($this->inicio_inscricao > now()->format('Y-m-d H:i')));
    }

    public function btnSituacao()
    {
        if($this->encerrado())
            return '<div class="sit-btn sit-vermelho">Já realizado</div>';

        if($this->podeInscreverExterno())
            return '<div class="sit-btn sit-verde">Vagas Abertas</div>';

        if(!$this->aguardandoAbrirInscricao())
            return '<div class="sit-btn sit-vermelho">Vagas esgotadas</div>';

        return '<div class="sit-btn sit-azul">Divulgação</div>';
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

    public function getInputHTML($old, $errors = false)
    {        
        return !$this->add_campo ? '' : self::inputs($old, $this->campo_required, $errors)[$this->campo_rotulo];
    }

    public function getInputHTMLInterno($old, $errors = false)
    {
        return !$this->add_campo ? '' : self::inputs($old, false, $errors)[$this->campo_rotulo];
    }

    public function getFormatCampoAdicional($valor)
    {
        return $this->nomeRotulo() . ': ' . $valor;
    }

    public function tipoParaCertificado()
    {
        return in_array($this->tipo, self::tiposCertificado())/* && ($this->idcurso > self::CERT_ID_MAIOR_QUE)*/;
    }

    public function acessarCertificado()
    {
        return $this->encerrado() && $this->tipoParaCertificado();
    }

    public function dataRealizacaoCertificado()
    {
        $data_inicial = Carbon::parse($this->datarealizacao);
        $data_final = Carbon::parse($this->datatermino);
        $temp = 'no dia ';
        $temp_data_corrida = $data_inicial->isoFormat('D') . ' de ' . ucFirst($data_inicial->isoFormat('MMMM'));
        $temp_hora_incial = $data_inicial->minute == 0 ? $data_inicial->format('H\h') : $data_inicial->format('H:i');
        $temp_hora_final = $data_final->minute == 0 ? $data_final->format('H\h') : $data_final->format('H:i');

        if($data_inicial->day != $data_final->day){
            $temp = 'com início ' . $temp;
            $temp .= $temp_data_corrida . ', às ';
            $temp .= $temp_hora_incial;
            $temp .= ', e término no dia ';
            $temp .= $data_final->isoFormat('D') . ' de ' . ucFirst($data_final->isoFormat('MMMM')) . ', às ';
        }else{
            $temp .= $temp_data_corrida . ', das ';
            $temp .= $temp_hora_incial;
            $temp .= ' às ';
        }
        
        $temp .= $temp_hora_final;
        $temp .= ', em ' . $this->regional->regional;

        return $temp;
    }

    public function cargaHorariaCertificado()
    {
        if($this->carga_horaria == '00:00')
            return 'sem carga horária';

        $carga_horaria = Carbon::parse($this->carga_horaria);
        $temp_hora = $carga_horaria->minute == 0 ? $carga_horaria->format('H\h') : $carga_horaria->format('H') . ' hora(s) e ' . $carga_horaria->format('i') . ' minutos';
        
        return 'com carga horária de ' . $temp_hora;
    }

    // ------ PARA TESTES ----------
    public static function duranteTestes()
    {
        self::find(66)->update(['tipo' => self::TIPO_CURSO, 'conferencista' => 'Gisele Paula', 'carga_horaria' => '01:30']);
        self::find(67)->update(['conferencista' => 'José Ricardo Noronha', 'carga_horaria' => '02:00']);
    }
}
