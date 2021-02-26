<?php

namespace App\Http\Controllers;

use PDF;
use App\Certidao;
use App\Representante;
use App\Mail\CertidaoMail;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function gerarCertidao($tipo, $dadosRepresentante, $numero, $codigo, $data, $hora, $dataValidade) 
    {
        if ($tipo == Certidao::REGULARIDADE) {
            $declaracao = Certidao::declaracaoRegularidade($dadosRepresentante, $numero, $codigo, $data, $hora, $dataValidade);
        }

        // Certidão de Parcelamento não será incluida na solução neste momento
        // else if ($tipo == Certidao::PARCELAMENTO) {
        //     $declaracao = Certidao::declaracaoParcelamento($dadosRepresentante, $numero, $codigo, $data, $hora);
        // }

        $certidao = $this->certidaoRepository->store($tipo, $declaracao, $numero, $codigo, $data, $hora, $dataValidade, $dadosRepresentante);

        // Cria o PDF usando a view de acordo com o tipo de pessoa.
        $pdf = $pdf = PDF::loadView("certidoes.certidao", compact("declaracao"));

        // Envio de e-mail com o PDF.
        $email = new CertidaoMail($pdf->output());
        Mail::to($dadosRepresentante["email"])->queue($email);

        // Log externo de emissão de certidão.
        event(new ExternoEvent('CPF/CNPJ: "'. $dadosRepresentante["cpf_cnpj"] .'" emitiu Certidão de ' . $tipo . '.'));

        return $pdf->download("certidao.pdf");
    }

    /**
     * Faz download do PDF da certidão do Representante Comercial através do númeo da certidão.
     */
    public function baixarCertidao($numero) 
    {
        $certidao = $this->certidaoRepository->recuperaCertidao($numero);

        $declaracao = $certidao->declaracao;

        $pdf = PDF::loadView("certidoes.certidao", compact("declaracao"));

        return $pdf->download("certidao.pdf");
    }

    /**
     * Método para verificar autenticidade da Certidão, data/hora de emissão, código e número são usados para verificar se a Certidão
     * é autêntica'.
     */
    public function consulta(ConsultaCertidaoRequest $request)
    {
        // Autentica a certidão no GERENTI
        $autenticaCertidao = $this->gerentiRepository->gerentiAutenticaCertidao($request->numero, $request->codigo, $request->hora, date('Y-m-d', strtotime(str_replace('/', '-', $request->data))));

        // Caso a certidão seja autenticada pelo GERENTI, mostramos os dados retornados pelo GERENTI
        if($autenticaCertidao['SITUACAO'] == 1) {
            $autenticado = true;

            $certidao = $this->certidaoRepository->recuperaCertidao($numero);

            $resultado = '<p>Nome: ' . $certidao->nome . '</p>';
            $resultado .= '<p>Registro: ' . $certidao->registro . '</p>';
            $resultado .= '<p>CPF/CNPJ: ' . $certidao->cpf_cnpj  . '</p>';
            $resultado .= '<p>Data de validade da certidão: ' . date('d/m/Y', strtotime($autenticaCertidao['DATAVALIDADE'])) . '</p>';
        }
        // Caso os dados fornecidos não sejam autenticados pelo GERENTI, mostra uma mensagem de erro
        else {
            $autenticado = false;

            $resultado = null;
        }

        return view("site.consulta-certidao", compact(["autenticado", "resultado"]));
    }

    public function consultaView() 
    {
        return view("site.consulta-certidao");
    }
}