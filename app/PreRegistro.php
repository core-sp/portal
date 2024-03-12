<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\PreRegistroApoio;
use Illuminate\Support\Arr;

class PreRegistro extends Model
{
    use SoftDeletes, PreRegistroApoio;

    protected $table = 'pre_registros';
    protected $guarded = [];

    const STATUS_CRIADO = 'Sendo elaborado';
    const STATUS_ANALISE_INICIAL = 'Em análise inicial';
    const STATUS_CORRECAO = 'Aguardando correção';
    const STATUS_ANALISE_CORRECAO = 'Em análise da correção';
    const STATUS_APROVADO = 'Aprovado';
    const STATUS_NEGADO = 'Negado';
    const TOTAL_HIST = 1;

    private function atualizarCampoEspelho($request, $final)
    {
        if($this->correcaoEnviada())
        {
            $camposEspelho = isset($this->campos_espelho) ? $this->fromJson($this->campos_espelho) : array();
            $dados = array_merge(array_diff_assoc($camposEspelho, $final), array_diff_assoc($final, $camposEspelho));
            if(isset($dados['path']))
                $dados['path'] = $final['path'];
            $this->update(['campos_editados' => $this->asJson($dados)]);
        }
        $this->update(['campos_espelho' => $this->asJson($request)]);
    }

    private function horaUpdateHistorico()
    {
        $update = $this->getHistoricoArray()['update'];
        $updateCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $update);
        $updateCarbon->addDay();

        return $updateCarbon;
    }

    protected function atualizarFinal($campo, $valor)
    {
        $resultado = null;
        $valido = $this->validarUpdateAjax($campo, $valor);
        if(isset($valido))
        {
            $this->update($valido);
            if(in_array($campo, array_keys($this->getEndereco())) && !$this->userExterno->isPessoaFisica())
                $resultado = $this->pessoaJuridica->mesmoEndereco();
        }

        return $resultado;
    }

    private function formatTelefones($campo, $valor)
    {
        switch ($campo) {
            case 'tipo_telefone':
            case 'tipo_telefone_1':
                $temp = array_filter(explode(';', $this->tipo_telefone));
                break;
            case 'telefone':
            case 'telefone_1':
                $temp = array_filter(explode(';', $this->telefone));
                break;
            case 'opcional_celular':
            case 'opcional_celular_1':
                $temp = array_filter(explode(';', $this->opcional_celular));
                $temp[0] = isset($temp[0]) ? $temp[0] : null;
                $temp[1] = isset($temp[1]) ? $temp[1] : null;
                $array_opcoes = $campo == 'opcional_celular' ? array_filter(explode(',', $temp[0])) : array_filter(explode(',', $temp[1]));
                $valor = !in_array($valor, $array_opcoes) ? implode(',', Arr::add($array_opcoes, count($array_opcoes), $valor)) : implode(',', Arr::except($array_opcoes, array_search($valor, $array_opcoes, true)));
                break;
        }

        if(strpos($campo, '_1') === false)
            $temp[0] = $valor;
        else
        {
            $temp[0] = isset($temp[0]) ? $temp[0] : '';
            $temp[1] = $valor;
        }

        ksort($temp, SORT_NUMERIC);

        return implode(';', $temp);
    }

    private function formatTextoCorrecaoAdmin($campo, $valor)
    {
        if($campo == 'negado')
            $this->update(['justificativa' => null]);

        $original = $campo == 'confere_anexos' ? $this->confere_anexos : $this->justificativa;
        $texto = isset($original) ? $this->fromJson($original) : array();

        switch ($campo) {
            case 'confere_anexos':
                if(!isset($texto[$valor]))
                    $texto[$valor] = "OK";
                else
                    unset($texto[$valor]);
                break;
            default:
                if(isset($valor) && (strlen($valor) > 0))
                    $texto[$campo] = $valor;
                elseif(isset($texto[$campo]))
                    unset($texto[$campo]);
        }

        return count($texto) == 0 ? null : $this->asJson($texto);
    }

    private function validarUpdateAjax($campo, $valor)
    {
        switch ($campo) {
            case 'tipo_telefone':
            case 'tipo_telefone_1':
            case 'telefone':
            case 'telefone_1':
            case 'opcional_celular':
            case 'opcional_celular_1':
                $valor = $this->formatTelefones($campo, $valor);
                $campo = str_replace('_1', '', $campo);
                break;
            case 'justificativa':
                $valor = $this->formatTextoCorrecaoAdmin($valor['campo'], $valor['valor']);
                break;
            case 'confere_anexos':
                $valor = $this->formatTextoCorrecaoAdmin($campo, $valor);
                break;
            case 'pergunta':
                return null;
                break;
        }

        return [$campo => $valor];
    }

    private function finalArray($arrayCampos)
    {
        $arrayCampos['tipo_telefone'] = isset($arrayCampos['tipo_telefone_1']) ? 
        $arrayCampos['tipo_telefone'] . ';' . $arrayCampos['tipo_telefone_1'] : $arrayCampos['tipo_telefone'] . ';';

        $arrayCampos['telefone'] = isset($arrayCampos['telefone_1']) ? 
        $arrayCampos['telefone'] . ';' . $arrayCampos['telefone_1'] : $arrayCampos['telefone'] . ';';

        if(isset($arrayCampos['opcional_celular']) || isset($arrayCampos['opcional_celular_1']))
            $arrayCampos['opcional_celular'] = isset($arrayCampos['opcional_celular_1']) ? 
            $arrayCampos['opcional_celular'] . ';' . $arrayCampos['opcional_celular_1'] : $arrayCampos['opcional_celular'] . ';';

        unset($arrayCampos['tipo_telefone_1']);
        unset($arrayCampos['telefone_1']);
        unset($arrayCampos['opcional_celular_1']);

        return $this->update($arrayCampos);
    }

    private static function colorLabelStatusAdmin()
    {
        return [
            PreRegistro::STATUS_CRIADO => '-info',
            PreRegistro::STATUS_ANALISE_INICIAL => '-primary',
            PreRegistro::STATUS_CORRECAO => '-secondary',
            PreRegistro::STATUS_ANALISE_CORRECAO => '-warning',
            PreRegistro::STATUS_APROVADO => '-success',
            PreRegistro::STATUS_NEGADO => '-danger',
        ];
    }

    public static function camposPreRegistro()
    {
        return [
            'segmento',
            'idregional',
            'tipo_telefone',
            'telefone',
            'opcional_celular',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
        ];
    }

    public static function getStatus()
    {
        $array = [
            PreRegistro::STATUS_CRIADO,
            PreRegistro::STATUS_ANALISE_INICIAL,
            PreRegistro::STATUS_CORRECAO,
            PreRegistro::STATUS_ANALISE_CORRECAO,
            PreRegistro::STATUS_APROVADO,
            PreRegistro::STATUS_NEGADO,
        ];
        sort($array, SORT_STRING);
        
        return $array;
    }

    public static function getLegendaStatus()
    {
        $inicio = '<button type="button" class="btn btn-sm mr-3 bg';
        $meio = ' font-weight-bolder font-italic" data-toggle="popover" data-placement="bottom" data-content=';
        $legenda = '<p><strong><em>Legenda<small> (click)</small>: </em></strong>';
        $legenda .= $inicio . self::colorLabelStatusAdmin()[self::STATUS_CRIADO] . $meio . '"<strong>Solicitante está em processo de preenchimento do formulário</strong>">' . self::STATUS_CRIADO . '</button>';
        $legenda .= $inicio . self::colorLabelStatusAdmin()[self::STATUS_ANALISE_INICIAL] . $meio . '"<strong>Solicitante está aguardando o atendente analisar os dados</strong>">' . self::STATUS_ANALISE_INICIAL . '</button>';
        $legenda .= $inicio . self::colorLabelStatusAdmin()[self::STATUS_CORRECAO] . $meio . '"<strong>Atendente está aguardando o solicitante corrigir os dados</strong>">' . self::STATUS_CORRECAO . '</button>';
        $legenda .= $inicio . self::colorLabelStatusAdmin()[self::STATUS_ANALISE_CORRECAO] . $meio . '"<strong>Solicitante está aguardando o atendente analisar os dados após correção</strong>">' . self::STATUS_ANALISE_CORRECAO . '</button>';
        $legenda .= $inicio . self::colorLabelStatusAdmin()[self::STATUS_APROVADO] . $meio . '"<strong>Atendente aprovou a solicitação e pode realizar o anexo do boleto</strong>">' . self::STATUS_APROVADO . '</button>';
        $legenda .= $inicio . self::colorLabelStatusAdmin()[self::STATUS_NEGADO] . $meio . '"<strong>Atendente negou a solicitação</strong>">' . self::STATUS_NEGADO . '</button>';
        $legenda .= '</p><hr/>';

        return $legenda;
    }

    public function userExterno()
    {
        return $this->belongsTo('App\UserExterno')->withTrashed();
    }

    public function regional()
    {
        return $this->belongsTo('App\Regional', 'idregional');
    }

    public function contabil()
    {
        return $this->belongsTo('App\Contabil')->withTrashed();
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function pessoaFisica()
    {
        return $this->hasOne('App\PreRegistroCpf')->withTrashed();
    }

    public function pessoaJuridica()
    {
        return $this->hasOne('App\PreRegistroCnpj')->withTrashed();
    }

    public function anexos()
    {
        return $this->hasMany('App\Anexo');
    }

    public function excluirAnexos()
    {
        if($this->anexos->count() > 0)
        {
            $deleted = $this->anexos->first()->excluirDiretorioPreRegistro();
            if(!$deleted)
                throw new \Exception('Não foi possível excluir o diretório com os arquivos para o pré-registro com id ' . $this->id, 500);
            if($deleted)
            {
                $this->anexos()->delete();
                $this->touch();
            }
        }
    }

    public function setHistorico()
    {
        $array = $this->getHistoricoArray();
        $totalTentativas = intval($array['tentativas']) < self::TOTAL_HIST;

        if($totalTentativas)
            $array['tentativas'] = intval($array['tentativas']) + 1;
        $array['update'] = now()->format('Y-m-d H:i:s');

        return $this->asJson($array);
    }

    public function getHistoricoCanEdit()
    {
        $array = $this->getHistoricoArray();
        $can = intval($array['tentativas']) < self::TOTAL_HIST;
        $horaUpdate = $this->horaUpdateHistorico();
        
        return $can || (!$can && ($horaUpdate < now()));
    }

    public function getLabelStatus($status = null)
    {
        return isset($status) && isset(self::colorLabelStatusAdmin()[$status]) ? self::colorLabelStatusAdmin()[$status] : self::colorLabelStatusAdmin()[$this->status];
    }

    public function getLabelStatusUser($semExplicacao = false)
    {
        $cor = '';
        $texto = '';

        switch ($this->status) {
            case self::STATUS_CRIADO:
                $cor = 'secondary';
                $texto = 'O formulário ainda está sendo elaborado pelo solicitante';
                break;
            case self::STATUS_ANALISE_INICIAL:
                $cor = 'primary';
                $texto = 'O formulário foi enviado pelo solicitante e está aguardando a análise pelo atendente';
                break;
            case self::STATUS_CORRECAO:
                $cor = 'warning';
                $texto = 'O formulário foi analisado pelo atendente e possui correções a serem realizadas pelo solicitante';
                break;
            case self::STATUS_ANALISE_CORRECAO:
                $cor = 'info';
                $texto = 'O formulário foi enviado pelo solicitante e está aguardando a análise da correção pelo atendente';
                break;
            case self::STATUS_APROVADO:
                $cor = 'success';
                $texto = 'O formulário foi aprovado pelo atendente e estará disponível o boleto para pagamento';
                break;
            case self::STATUS_NEGADO:
                $cor = 'danger';
                $texto = 'O formulário foi negado pelo atendente com justificativa';
                break;
            default:
                return null;
        }

        $inicio = '<span class="badge badge-'. $cor .'">' . $this->status . '</span>';

        return !$semExplicacao ? $inicio . '<small> - '. $texto .'</small>' : $inicio;
    }

    public function getBoleto()
    {
        return ($this->anexos->count() > 0) && $this->isAprovado() ? $this->anexos->where('nome_original', 'boleto_aprovado_' . $this->id)->first() : null;
    }

    public function temBoleto()
    {
        return null !== $this->getBoleto();
    }

    public function getTipoTelefone()
    {
        return array_filter(explode(';', $this->tipo_telefone));
    }

    public function getTelefone()
    {
        return array_filter(explode(';', $this->telefone));
    }

    public function getOpcionalCelular()
    {
        return collect(array_filter(explode(';', $this->opcional_celular)))->map(function ($item, $key) {
            return array_filter(explode(',', $item));
        })->toArray();
    }

    public function getJustificativaArray()
    {
        return isset($this->justificativa) ? $this->fromJson($this->justificativa) : array();
    }

    public function getConfereAnexosArray()
    {
        return isset($this->confere_anexos) ? $this->fromJson($this->confere_anexos) : array();
    }

    public function getHistoricoArray()
    {
        return isset($this->historico_contabil) ? $this->fromJson($this->historico_contabil) : array();
    }

    public function getNextUpdateHistorico()
    {
        return $this->horaUpdateHistorico()->format('d\/m\/Y, \à\s H:i');
    }

    public function getJustificativaNegado()
    {
        return isset($this->getJustificativaArray()['negado']) ? $this->getJustificativaArray()['negado'] : null;
    }

    public function criado()
    {
        return $this->status == PreRegistro::STATUS_CRIADO;
    }

    public function isFinalizado()
    {
        return ($this->status == PreRegistro::STATUS_NEGADO) || ($this->status == PreRegistro::STATUS_APROVADO);
    }

    public function isAprovado()
    {
        return $this->status == PreRegistro::STATUS_APROVADO;
    }

    public function correcaoEnviada()
    {
        return $this->status == PreRegistro::STATUS_CORRECAO;
    }

    public function correcaoEmAnalise()
    {
        return $this->status == PreRegistro::STATUS_ANALISE_CORRECAO;
    }

    public function userPodeCorrigir()
    {
        return $this->correcaoEnviada();
    }

    public function userPodeEditar()
    {
        return ($this->status == PreRegistro::STATUS_CRIADO) || $this->correcaoEnviada();
    }

    public function atendentePodeEditar()
    {
        return in_array($this->status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]);
    }

    public function getCodigosJustificadosByAba($arrayAba)
    {
        if($this->userPodeCorrigir())
        {
            $array = collect($this->getJustificativaArray())->map(function ($item, $key) use($arrayAba){
                if(isset($arrayAba[$key]))
                    return $arrayAba[$key];
            })
            ->filter()
            ->toArray();
            
            natsort($array);
            return $array;
        }

        return null;
    }

    public function getTextosJustificadosByAba($arrayAba)
    {
        if($this->userPodeCorrigir())
        {
            $array = collect($this->getJustificativaArray())->keyBy(function ($item, $key) use($arrayAba){
                return isset($arrayAba[$key]) ? $arrayAba[$key] : 'remover';
            })
            ->forget('remover')
            ->toArray();
            
            ksort($array, SORT_NATURAL);
            return $array;
        }

        return null;
    }

    public function setHistoricoStatus()
    {
        $historico = isset($this->historico_status) ? $this->fromJson($this->historico_status) : array();
        array_push($historico, $this->status . ';' . $this->updated_at);
        $this->update(['historico_status' => $this->asJson($historico)]);
    }

    public function setHistoricoJustificativas()
    {
        if(strlen($this->justificativa) < 3)
            return null;

        $justificativas = isset($this->historico_justificativas) ? $this->fromJson($this->historico_justificativas) : array();
        array_push($justificativas, $this->justificativa . ';' . $this->updated_at);
        $this->update(['historico_justificativas' => $this->asJson($justificativas)]);
    }

    public function getHistoricoStatus()
    {
        return isset($this->historico_status) ? $this->fromJson($this->historico_status) : array();
    }

    public function getHistoricoJustificativas()
    {
        $justificados = isset($this->historico_justificativas) ? collect($this->fromJson($this->historico_justificativas)) : collect();
        $campos = collect($this->getCodigosCampos($this->userExterno->isPessoaFisica()))->mapWithKeys(function ($item){
            return $item;
        })
        ->flip()
        ->toArray();

        return $justificados->map(function ($values) use($campos){
            $temp = array_filter(explode(';', $values));
            $textos = collect($this->fromJson($temp[0]));
            $array = $textos->keyBy(function ($item, $key) use($campos){
                if(in_array($key, $campos))
                    return array_keys($campos, $key, true)[0];
            })
            ->toArray();
            ksort($array, SORT_NATURAL);
            return $array;
        })
        ->toArray();
    }

    public function setCamposEspelho($request)
    {
        $request = $this->formatarCamposRequest($request);
        $idAnexos = isset($this->anexos) ? $this->anexos->pluck('id')->toArray() : array();
        $request['path'] = !empty($idAnexos) ? implode(',', $idAnexos) : '';
        $final = $request;

        if(isset($this->campos_espelho))
        {
            $anexosAntigo = explode(',', $this->fromJson($this->campos_espelho)['path']);
            $idAnexos = array_filter($idAnexos, function($v, $k) use($anexosAntigo) {
                return !in_array($v, $anexosAntigo);
            }, ARRAY_FILTER_USE_BOTH);

            if(!empty($idAnexos))
                $final['path'] = implode(',', $idAnexos);
        }

        $this->atualizarCampoEspelho($request, $final);
    }

    public function possuiCamposEditados()
    {
        if(isset($this->campos_editados))
            return count($this->fromJson($this->campos_editados)) > 0;
        return false;
    }

    public function getCamposEditados()
    {
        return $this->correcaoEmAnalise() && $this->possuiCamposEditados() ? $this->fromJson($this->campos_editados) : array();
    }

    public function confereJustificadosSubmit()
    {
        return !$this->correcaoEnviada() || ($this->correcaoEnviada() && $this->possuiCamposEditados());
    }

    public function getEndereco()
    {
        return $this->only(['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf']);
    }

    public function salvarAjax($request, $gerentiRepository = null)
    {
        $classe = $request['classe'];
        $request['campo'] = $this->limparNomeCamposAjax($request['classe'], $request['campo']);
        $gerenti = $this->getRTGerenti($request['classe'], $gerentiRepository, $request['campo'] == 'cpf' ? $request['valor'] : '');
        $campo = $request['campo'];
        $valor = $request['valor'];

        if(($classe != $this->getNomeClasses()[0]) && ($classe != $this->getNomeClasses()[4]))
            return !$this->has($classe)->where('id', $this->id)->exists() ? $this->criarAjax($classe, $campo, $valor, $gerenti) : $this->atualizarAjax($classe, $campo, $valor, $gerenti);

        if($classe == $this->getNomeClasses()[4])
            return $this->atualizarAjax($classe, $campo, $valor, $gerenti);

        return $this->criarAjax($classe, $campo, $valor, $gerenti);
    }

    public function salvar($request, $gerentiRepository)
    {
        try{
            $camposLimpos = $this->getCamposLimpos($request, $this->userExterno->getCamposPreRegistro());
            unset($request);

            foreach($camposLimpos as $classe => $arrayCampos)
                $resultado = $this->salvarArray($classe, $arrayCampos, $this->getRTGerenti($classe, $gerentiRepository, isset($arrayCampos['cpf']) ? $arrayCampos['cpf'] : ''));

            unset($camposLimpos);
            $status = $this->criado() ? self::STATUS_ANALISE_INICIAL : self::STATUS_ANALISE_CORRECAO;
            $resultado = $this->update(['status' => $status]);

            if(!$resultado)
                throw new \Exception('Não atualizou o status da solicitação de registro', 500);

            $this->setHistoricoStatus();
            $resultado = $status;
        }catch(\Throwable $e){
            throw new \Exception('Erro ao salvar dados finais do pré-registro com id ' . $this->id . ' na classe: ' . $classe .', com a seguinte mensagem: ' . $e->getMessage(), 500);
        }

        return $resultado;
    }
}
