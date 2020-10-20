<?php

namespace App\Http\Controllers;

use App\Rules\CpfCnpj;
use App\Traits\GerentiProcedures;
use App\Connections\FirebirdConnection;

class ConsultaSituacaoController extends Controller
{
    use GerentiProcedures;

    public function consultaView()
    {
        return view('site.consulta');
    }

    protected function validateRequest()
    {
        $cpfCnpj = apenasNumeros(request('cpfCnpj'));

        request()->request->set('cpfCnpj', $cpfCnpj);

        // Validação dos campos do formulário
        return request()->validate([
            'cpfCnpj' => ['required', new CpfCnpj],
        ], [
            'cpfCnpj.required' => 'Por favor, informe o CPF!',
        ]);
    }

    public function consulta()
    {
        $this->validateRequest();
        
        $resultado = $this->gerentiAtivo(apenasNumeros($cpfCnpj));

        return view('site.consulta', compact('resultado'));
    }
}
