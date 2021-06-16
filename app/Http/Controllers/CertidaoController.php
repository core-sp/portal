<?php

namespace App\Http\Controllers;

use PDF;
use App\Certidao;
use App\Representante;
use App\Mail\CertidaoMail;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Repositories\CertidaoRepository;
use App\Http\Requests\ConsultaCertidaoRequest;
use App\Repositories\GerentiRepositoryInterface;

class CertidaoController extends Controller
{
    private $certidaoRepository;
    private $gerentiRepository;

    public function __construct(CertidaoRepository $certidaoRepository, GerentiRepositoryInterface $gerentiRepository)
    {
        $this->certidaoRepository = $certidaoRepository;
        $this->gerentiRepository = $gerentiRepository;
    }

    // public function gerarCertidao($tipo, $dadosRepresentante, $numero, $codigo, $data, $hora, $dataValidade) 
    // {
    //     if ($tipo == Certidao::REGULARIDADE) {
    //         $declaracao = Certidao::declaracaoRegularidade($dadosRepresentante, $numero, $codigo, $data, $hora, $dataValidade);
    //     }

    //     // Certidão de Parcelamento não será incluida na solução neste momento
    //     // else if ($tipo == Certidao::PARCELAMENTO) {
    //     //     $declaracao = Certidao::declaracaoParcelamento($dadosRepresentante, $numero, $codigo, $data, $hora);
    //     // }

    //     $dataFormatada = date('d/m/Y', strtotime($data));

    //     $certidao = $this->certidaoRepository->store($tipo, $declaracao, $numero, $codigo, $dataFormatada, $hora, $dataValidade, $dadosRepresentante);

    //     // Cria o PDF usando a view de acordo com o tipo de pessoa.
    //     $pdf = $pdf = PDF::loadView("certidoes.certidao", compact('declaracao', 'numero', 'codigo', 'data', 'hora'));

    //     // Envio de e-mail com o PDF.
    //     $email = new CertidaoMail($pdf->output());
    //     Mail::to($dadosRepresentante["email"])->queue($email);

    //     // Log externo de emissão de certidão.
    //     event(new ExternoEvent('Usuário ' . Auth::guard('representante')->user()->id . ' ("'. Auth::guard('representante')->user()->registro_core .'") emitiu Certidão de ' . $tipo . '.'));

    //     return $pdf->download("certidao.pdf");
    // }

    /**
     * Faz download do PDF da certidão do Representante Comercial através do númeo da certidão.
     */
    // public function baixarCertidao($numero) 
    // {
    //     $certidao = $this->certidaoRepository->recuperaCertidao($numero);

    //     $declaracao = $certidao->declaracao;
    //     $codigo = $certidao->codigo;
    //     $data = date('d/m/Y', strtotime($certidao->data_emissao));
    //     $hora = $certidao->hora_emissao;

    //     $pdf = PDF::loadView('certidoes.certidao', compact('declaracao', 'numero', 'codigo', 'data', 'hora'));

    //     return $pdf->download('certidao.pdf');
    // }

    /**
     * Método para verificar autenticidade da Certidão, data/hora de emissão, código e número são usados para verificar se a Certidão
     * é autêntica'.
     */
    // public function consulta(ConsultaCertidaoRequest $request)
    // {
    //     // Autentica a certidão no GERENTI
    //     $autenticaCertidao = $this->gerentiRepository->gerentiAutenticaCertidao($request->numero, $request->codigo, $request->hora, date('Y-m-d', strtotime(str_replace('/', '-', $request->data))));

    //     // Caso a certidão seja autenticada pelo GERENTI, mostramos os dados retornados pelo GERENTI
    //     if($autenticaCertidao['SITUACAO'] == 'Válida') {
    //         $autenticado = true;

    //         $certidao = $this->certidaoRepository->recuperaCertidao($request->numero);

    //         $resultado = '<p>Nome: ' . $certidao->nome . '</p>';
    //         $resultado .= '<p>Registro: ' . $certidao->registro_core . '</p>';
    //         $resultado .= '<p>CPF/CNPJ: ' . formataCpfCnpj($certidao->cpf_cnpj)  . '</p>';
    //         $resultado .= '<p>Data de validade da certidão: ' . date('d/m/Y', strtotime($autenticaCertidao['DATAVALIDADE'])) . '</p>';
    //     }
    //     // Caso os dados fornecidos não sejam autenticados pelo GERENTI, mostra uma mensagem de erro
    //     else {
    //         $autenticado = false;

    //         $resultado = null;
    //     }

    //     return view('site.consulta-certidao', compact(['autenticado', 'resultado']));
    // }

    // public function consultaView(Request $request) 
    // {
    //     $numero = $request->numero;
    //     $codigo = $request->codigo;
    //     $data = $request->data;
    //     $hora = $request->hora;

    //     return view('site.consulta-certidao', compact(['numero', 'codigo', 'data', 'hora']));
    // }
}