<?php

namespace App\Http\Controllers;

use PDF;
use App\Certidao;
use App\Mail\CertidaoMail;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Repositories\CertidaoRepository;
use App\Http\Requests\ConsultaCertidaoRequest;

class CertidaoController extends Controller
{
    private $certidaoRepository;

    public function __construct(CertidaoRepository $certidaoRepository)
    {
        $this->certidaoRepository = $certidaoRepository;
    }

    /**
     * Método para salvar Certidão de Regularidade. Esse método não possui rota, usado internamento pelo 
     * RepresentanteSiteController.
     */
    public function storeCertidaoRegularidade($tipoPessoa, $dadosRepresentante, $endereco)
    {
        $certidao =  DB::transaction(function () use ($tipoPessoa, $dadosRepresentante, $endereco) {
            // Criar a certidao no banco de dados
            $certidao = $this->certidaoRepository->store(Certidao::$tipo_regularidade , $tipoPessoa, $dadosRepresentante, $endereco);

            // TODO - Atualizar o GERENTI com a certidão criada

            return $certidao;
        });
        // Formata o código para facilitar a visualização (XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX)
        $codigoCertidao = $certidao->codigoFormatado();

        $declaracao = Certidao::declaracaoRegularidade(true, $tipoPessoa, $dadosRepresentante, $endereco, $certidao->data_emissao);

        $data = [
            "hora" => strftime("%H:%M",  strtotime($certidao->hora_emissao)),
            "data" => onlyDate($certidao->data_emissao)
        ];
        
        $titulo = "Certidão de Regularidade";

        // Cria o PDF usando a view de acordo com o tipo de pessoa
        $pdf = PDF::loadView("certidoes.certidao", compact("declaracao", "codigoCertidao", "data", "titulo"));

        // Envio de e-mail com o PDF
        $email = new CertidaoMail($pdf->output());
        Mail::to($dadosRepresentante["email"])->queue($email);

        // Log externo de emissão de certidão
        event(new ExternoEvent('CPF/CNPJ: "'. $dadosRepresentante["cpf_cnpj"] .'" emitiu Certidão de Regularidade.'));

        return $pdf->stream("certidao.pdf");
    }

    /**
     * Método para salvar Certidão de Parcelamento. Esse método não possui rota, usado internamento pelo 
     * RepresentanteSiteController.
     */
    public function storeCertidaoParcelamento($tipoPessoa, $dadosRepresentante, $endereco, $dadosParcelamento)
    {
        $certidao =  DB::transaction(function () use ($tipoPessoa, $dadosRepresentante, $endereco, $dadosParcelamento) {
            // Criar a certidao no banco de dados
            $certidao = $this->certidaoRepository->store(Certidao::$tipo_parcelamento, $tipoPessoa, $dadosRepresentante, $endereco, $dadosParcelamento);

            // TODO - Atualizar o GERENTI com a certidão criada

            return $certidao;
        });

        // Formata o código para facilitar a visualização (XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX)
        $codigoCertidao = $certidao->codigoFormatado();

        $declaracao = Certidao::declaracaoParcelamento(true, $tipoPessoa, $dadosRepresentante, $endereco, $dadosParcelamento);

        $data = [
            "hora" => strftime("%H:%M",  strtotime($certidao->hora_emissao)),
            "data" => onlyDate($certidao->data_emissao)
        ];
        
        $titulo = "Certidão de Parcelamento";

        // Cria o PDF usando a view de acordo com o tipo de pessoa
        $pdf = PDF::loadView("certidoes.certidao", compact("declaracao", "codigoCertidao", "data", "titulo"));

        // Envio de e-mail com o PDF
        $email = new CertidaoMail($pdf->output());
        Mail::to($dadosRepresentante["email"])->queue($email);

        // Log externo de emissão de certidão
        event(new ExternoEvent('CPF/CNPJ: "'. $dadosRepresentante["cpf_cnpj"] .'" emitiu Certidão de Parcelamento.'));

        return $pdf->stream("certidao.pdf");
    }

    public function consultaView() 
    {
        return view("site.consulta-certidao");
    }

    /**
     * Método para verificar autenticidade da Certidão, data/hora de emissão, código são usados para verificar se a Certidão
     * existe no banco de dados do Portal.
     * 
     * Certidão tem validade de 30 dias.
     * 
     */
    public function consulta(ConsultaCertidaoRequest $request)
    {
        //Busca o certificado com os dados fonecidos (removendo a máscara do código)
        $certidao = $this->certidaoRepository->consultaCertidao(str_replace(" - ", "", $request->codigo), $request->hora, $request->data);

        $resultado = null;;

        // Caso os dados fornecidos não traga nenhum resultado, a certidão não existe no banco de dados ou está vencida
        if(!$certidao) {
            $autenticado = false;
        }
        else {
            $autenticado = true;

            // Informações diferentes de acordo com o tipo de pessoa
            if($certidao->tipo_pessoa == "PF") {
                $resultado = [
                    "Tipo" => $certidao->tipo,
                    "Nome" => $certidao->nome,
                    "CPF" => $certidao->cpf_cnpj,
                    "Registro" => $certidao->registro_core,
                    "Data de Inscrição" => onlyDate($certidao->data_inscricao),
                    "Data de Emissão" => organizaData(date('Y-m-d H:i', strtotime($certidao->data_emissao . $certidao->hora_emissao)))
                ];
            }
            else {
                $resultado = [
                    "Tipo" => $certidao->tipo,
                    "Razão Social" => $certidao->nome,
                    "CNPJ" => $certidao->cpf_cnpj,
                    "Registro" => $certidao->registro_core,
                    "Data de Inscrição" => onlyDate($certidao->data_inscricao),
                    "Tipo de Empresa" => $certidao->tipo_empresa,
                    "Responsável Técnico" => $certidao->resp_tecnico,
                    "Registro do Resp. Técnico" => $certidao->resp_tecnico_registro_core,
                    "Data de Emissão" => organizaData(date('Y-m-d H:i', strtotime($certidao->data_emissao . $certidao->hora_emissao)))
                ];
            }

            // Se sertidão de parcelamento, adiciona informações do acordo de parcelamento
            if($certidao->tipo == Certidao::$tipo_parcelamento) {
                $resultado = array_merge($resultado, ["Acordo" => $certidao->acordo_parcelamento]);
            }
        }

        return view("site.consulta-certidao", compact(["autenticado", "resultado"]));
    }
}