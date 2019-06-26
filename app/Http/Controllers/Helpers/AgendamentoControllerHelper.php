<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Agendamento;
use App\User;
use App\Http\Controllers\Helper;
use App\AgendamentoBloqueio;

class AgendamentoControllerHelper extends Controller
{
    public static function countAtendentes($idregional)
    {
        $count = User::where('idregional',$idregional)
            ->whereHas('perfil', function($q) {
                $q->where('nome','=','Atendimento');
            })->count();
        if($count === 0)
            $count = 1;
        return $count;
    }

    public static function horas($regional, $dia)
    {
        $horas = [
            '09:00',
            '09:30',
            '10:00',
            '10:30',
            '11:00',
            '11:30',
            '12:00',
            '12:30',
            '13:00',
            '13:30',
            '14:00',
            '14:30',
            '15:00',
            '15:30',
            '16:00',
            '16:30',
            '17:00',
        ];
        $bloqueios = AgendamentoBloqueio::where('idregional',$regional)
            ->whereDate('diainicio','<=',$dia)
            ->whereDate('diatermino','>=',$dia)
            ->get();
        if($bloqueios) {
            foreach($bloqueios as $bloqueio) {
                $horaInicio = $bloqueio->horainicio;
                $horaTermino = $bloqueio->horatermino;
                $keyHoraInicio = array_search($horaInicio, $horas);
                $keyHoraTermino = array_search($horaTermino, $horas);
                if(!$keyHoraTermino) {
                    $ultimoHoras = end($horas);
                    $keyHoraTermino = key($horas);
                    
                }
                if(!$keyHoraInicio) {
                    $primeiroHoras = reset($horas);
                    $keyHoraInicio = 0;
                }
                for($i = $keyHoraInicio; $i <= $keyHoraTermino; $i++)
                    unset($horas[$i]);
            }
            return $horas;
        }
    }

    public static function todasHoras()
    {
        $horas = [
            '09:00',
            '09:30',
            '10:00',
            '10:30',
            '11:00',
            '11:30',
            '12:00',
            '12:30',
            '13:00',
            '13:30',
            '14:00',
            '14:30',
            '15:00',
            '15:30',
            '16:00',
            '16:30',
            '17:00',
        ];
        return $horas;
    }

    public static function servicos()
    {
        $servicos = [
            'Refis',
        ];
        return $servicos;
    }

    public static function pessoas()
    {
        $pessoas = [
            'Pessoa Física' => 'PF',
            'Pessoa Jurídica' => 'PJ',
            'Ambas' => 'PF e PJ'
        ];
        return $pessoas;
    }

    public static function status()
    {
        $status = [
            'Não Compareceu',
            'Compareceu',
            'Cancelado'
        ];
        return $status;
    }

    public static function txtAgendamento($dia, $hora, $status, $protocolo, $id)
    {
        $now = date('Y-m-d');
        if($now > $dia) {
            if($status === 'Cancelado') {
                echo "<p class='mb-0 text-muted'><strong><i class='fas fa-ban'></i>&nbsp;&nbsp;Atendimento cancelado</strong></p>";
            } elseif($status === 'Não Compareceu') {
                echo "<p class='mb-0 text-warning'><strong><i class='fas fa-user-alt-slash'></i>&nbsp;&nbsp;Não compareceu</strong></p>";
            } elseif($status === null) {
                echo "<p class='mb-0 text-danger'><strong><i class='fas fa-exclamation-triangle'></i>&nbsp;&nbsp;Validação pendente</strong></p>";
            } else {
                echo "<p class='mb-0 text-success'><strong><i class='fas fa-check-circle'></i>&nbsp;&nbsp;Atendimento realizado com sucesso no dia ".Helper::onlyDate($dia).", às ".$hora."</strong></p>";
            }
        } else {
            if($status === 'Cancelado') {
                echo "<p class='mb-0 text-muted'><strong><i class='fas fa-ban'></i> Atendimento cancelado</strong></p>";
            } else {
                // Botão de reenviar email
                $botao = '<form method="POST" action="/admin/agendamentos/reenviar-email/'.$id.'" class="d-inline">';
                $botao .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $botao .= '<input type="submit" class="btn btn-sm btn-default" value="Reenviar email de confirmação"></input>';
                $botao .= '</form>';
                echo $botao;
            }
        }
    }

    public static function textoSuplementarMail()
    {
        $sup = '<hr>';
        $sup .= '<h3><strong>DOCUMENTAÇÃO PARA REALIZAR ACORDO / PARCELAMENTO DE DÉBITOS:</strong></h3>';
        $sup .= '<p><strong>Pessoa Jurídica / Cópias Simples:</strong></p>';
        $sup .= '<ol>';
        $sup .= '<li>Contrato Social atualizado devidamente registrado no órgão competente;</li>';
        $sup .= '<li>Atualização Cadastral Pessoa Jurídica devidamente preenchida e assinada pelo administrador;</li>';
        $sup .= '<li>Cédula de Identidade (não pode ser superior a 10 anos a data de emissão) e CPF e ou CNH (Carteira Nacional de Habilitação) válida e ou RNE para estrangeiros do administrador;</li>';
        $sup .= '<li>Comprovante de Residência atual de no mínimo até 03 meses do administrador;</li>';
        $sup .= '</ol>';
        $sup .= '<p>Observação: Se o acordo / parcelamento for solicitado através de terceiros será necessário apresentar procuração específica, dando amplos poderes para representar a Empresa perante o CORE-SP e cópia Cédula de Identidade (não pode ser superior a 10 anos a data de emissão) do procurador.</p>';
        $sup .= '<p><strong>Pessoa Física Responsável Técnico / Cópias Simples:</strong></p>';
        $sup .= '<ol>';
        $sup .= '<li>Cédula de Identidade (não pode ser superior a 10 anos a data de emissão) e CPF e ou CNH (Carteira Nacional de Habilitação) válida e ou RNE para estrangeiros do administrador;</li>';
        $sup .= '<li>Comprovante de Residência atual de no mínimo até 03 meses do Representante Comercial;</li>';
        $sup .= '</ol>';
        $sup .= '<p>Observação: Se o acordo / parcelamento for solicitado através de terceiros será necessário apresentar procuração específica, dando amplos poderes para representar o Represente Comercial perante o CORE-SP e cópia Cédula de Identidade (não pode ser superior a 10 anos a data de emissão) do procurador.</p>';
        $sup .= '<p><strong>Pessoa Física Autônomo / Cópias Simples:</strong></p>';
        $sup .= '<ol>';
        $sup .= '<li>Cédula de Identidade (não pode ser superior a 10 anos a data de emissão) e CPF e ou CNH (Carteira Nacional de Habilitação) válida e ou RNE para estrangeiros do Representante Comercial;</li>';
        $sup .= '<li>Comprovante de Residência atual de no mínimo até 03 meses do Representante Comercial;</li>';
        $sup .= '<li>Atualização Cadastral Pessoa Física devidamente preenchida e assinada pelo Representante Comercial;</li>';
        $sup .= '</ol>';
        $sup .= '<p>Observação: Se o acordo / parcelamento for solicitado através de terceiros será necessário apresentar procuração específica, dando amplos poderes para representar o Represente Comercial perante o CORE-SP e cópia Cédula de Identidade (não pode ser superior a 10 anos a data de emissão) do procurador.</p>';
        $sup .= '<p><strong>Os acordos / parcelamentos são realizados presencialmente na SEDE ou em nossos Escritórios Seccionais, mediante assinatura do Termo de Confissão de Dívida.</strong></p>';
        return $sup;
    }

    public static function tabelaEmailTop()
    {
        $body = '<table border="1" cellspacing="0" cellpadding="6">';
        $body .= '<thead>';
        $body .= '<tr>';
        $body .= '<th>Regional</th>';
        $body .= '<th>Horário</th>';
        $body .= '<th>Protocolo</th>';
        $body .= '<th>Nome</th>';
        $body .= '<th>CPF</th>';
        $body .= '<th>Serviço</th>';
        $body .= '</tr>';
        $body .= '</thead>';
        $body .= '<tbody>';
        return $body;
    }

    public static function tabelaEmailBot()
    {
        $body = '</tbody>';
        $body .= '</table>';
        return $body;
    }
}
