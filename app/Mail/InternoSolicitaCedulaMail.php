<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InternoSolicitaCedulaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;
    public $dia;
    public $cedulas;

    public function __construct($cedulas, $dia)
    {
        $this->cedulas = $cedulas;
        $this->dia = $dia;
        $this->body = $this->textoEmail($this->cedulas);
    }

    private function textoEmail($cedulas)
    {
        if($cedulas->isNotEmpty())
        {
            $body = '<h3><i>(Mensagem Programada)</i></h3>';
            $body .= '<p>Confira abaixo a lista de cédulas solicitadas pelo Portal CORE-SP ontem, <strong>'.$this->dia.':</strong></p>';
            $body .= '<table border="1" cellspacing="0" cellpadding="6">';
            $body .= '<thead>';
            $body .= '<tr>';
            $body .= '<th>Regional</th>';
            $body .= '<th>Representante</th>';
            $body .= '<th>Registro Core</th>';
            $body .= '<th>Tipo da cédula</th>';
            $body .= '</tr>';
            $body .= '</thead>';
            $body .= '<tbody>';
            foreach($cedulas as $cedula) {
                $body .= '<tr>';
                $body .= '<td>'.$cedula->regional->regional.'</td>';
                $body .= '<td>'.$cedula->representante->nome.'</td>';
                $body .= '<td>'.$cedula->representante->registro_core.'</td>';
                $body .= '<td>'.$cedula->tipo.'</td>';
                $body .= '</tr>';
            }
            $body .= '</tbody>';
            $body .= '</table>';
            $body .= '<p>';
            $body .= 'Por favor, acesse o <a href="https://core-sp.org.br/admin" target="_blank">painel de administrador</a> do Portal CORE-SP para mais informações.';
            $body .= '</p>';

            return $body;
        }
    }

    public function build()
    {
        return $this->subject('Solicitações de Cédulas Seccionais (dia: '.$this->dia.')')
            ->view('emails.interno')
            ->with([
                'body' => $this->body,
            ]);
    }
}
