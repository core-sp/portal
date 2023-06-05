<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SalaReuniaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    public function __construct($sala, $itens = [])
    {
        $this->alteracao($sala, $itens);
    }

    private function alteracao($sala, $itens)
    {
        $this->body = "<strong>A seccional ". $sala->regional->regional ." sofreu alteração nos itens e/ou no total de participantes da(s) sala(s)!</strong>";
        $this->body .= "<br><br>";
        $this->body .= "<strong>Os itens / participantes abaixo foram alterados:</strong>";
        $this->body .= "<br><br>";
        if(isset($itens['reuniao']) && !empty($itens['reuniao'])){
            $this->body .= "<strong>Sala de Reunião</strong>";
            $this->body .= "<br><br>";
            if(isset($itens['reuniao']['participantes']))
                $this->body .= "Participantes: " . $itens['reuniao']['participantes'] . "<br><br>";
            unset($itens['reuniao']['participantes']);
            foreach($itens['reuniao'] as $itemR)
                $this->body .= $itemR . "<br><br>";
        }
        if(isset($itens['coworking']) && !empty($itens['coworking'])){
            $this->body .= "<strong>Sala de Coworking</strong>";
            $this->body .= "<br><br>";
            if(isset($itens['coworking']['participantes']))
                $this->body .= "Participantes: " . $itens['coworking']['participantes'] . "<br><br>";
            unset($itens['coworking']['participantes']);
            foreach($itens['coworking'] as $itemC)
                $this->body .= $itemC . "<br><br>";
        } 

        $this->body .= "<br>";
        $this->body .= "<strong>Itens / participantes das salas após alteração:</strong>";
        $this->body .= "<br><br>";
        $this->body .= "<strong>Sala de Reunião</strong>";
        $this->body .= "<br><br>";
        $this->body .= "Participantes: " . $sala->participantes_reuniao;
        $this->body .= "<br><br>";

        foreach($sala->getItens('reuniao') as $iR)
            $this->body .= $iR . "<br><br>";

        $this->body .= "<strong>Sala de Coworking</strong>";
        $this->body .= "<br><br>";
        $this->body .= "Participantes: " . $sala->participantes_coworking;
        $this->body .= "<br><br>";

        foreach($sala->getItens('coworking') as $iC)
            $this->body .= $iC . "<br><br>";
        
        $this->body .= "<h3>Oriente, se necessário, os agendados sobre as alterações para um possível cancelamento e abertura na agenda.</h3>";
    }

    public function build()
    {
        return $this->subject('Alteração de itens / participantes da sala de reunião')
            ->view('emails.default');
    }
}
