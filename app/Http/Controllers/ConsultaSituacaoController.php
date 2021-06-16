<?php

namespace App\Http\Controllers;

use App\Rules\CpfCnpj;
use App\Repositories\GerentiRepositoryInterface;

class ConsultaSituacaoController extends Controller
{
    private $gerentiRepository;
    
    public function __construct(GerentiRepositoryInterface $gerentiRepository)
    {
        $this->gerentiRepository = $gerentiRepository;
    }

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
        
        $resultado = $this->gerentiRepository->gerentiAtivo(request('cpfCnpj'));

        return view('site.consulta', compact('resultado'));
    }
}
