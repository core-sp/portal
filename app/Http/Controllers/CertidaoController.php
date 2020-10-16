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

    public function verificaRegraCertidao($tipo, $dadosRepresentante, $endereco, $cobrancas)
    {
        $dataEmissao = date("Y-m-d H:i");

        $podeEmitir = true;

        // Representante Comercial precisa estar ativo para poder emitir certidão
        if(!$dadosRepresentante["ativo"]) {
            $podeEmitir = false;

            $falhaEmissao = "Não foi possível emitir Certidão de " . $tipo . " porque Representante Comercial não está ativo.";
        }

        // Representante Comercial PJ que não seja "Empresa Individual" precisa ter responsável técnico para emitir certidão 
        if($dadosRepresentante["tipo_pessoa"] == "PJ" && $dadosRepresentante["tipo_empresa"] != "Empresa Individual" && empty($dadosRepresentante["resp_tecnico"])) {  
            $podeEmitir = false;

            $falhaEmissao = "Não foi possível emitir Certidão de " . $tipo . " porque Representante Comercial não possui responsável técnico cadastrado.";
        }

        // Caso não possa emitir, evita o processamentos das regras específicas para cada tipo de certidão
        if($podeEmitir) {
            switch($tipo) {
                // Regras para emitir Certidão de Regularidade
                case "Regularidade":
                    // Representante Comercial precisa estar com situação "Em dia."
                    if($dadosRepresentante["situacao"] != "Em dia.") {
                        $podeEmitir = false;
    
                        $falhaEmissao = "Não foi possível emitir Certidão de " . $tipo . " porque Representante Comercial não está em dia com o CORE-SP.";
                    }

                    // Depois da validação das regras, se for possível emitir, gera declaração da certidão
                    if($podeEmitir) {
                        $declaracao = Certidao::declaracaoRegularidade($dadosRepresentante, $endereco, $dataEmissao);
                    }
                break;

                // Regras para emitir Certidão de Parcelamento
                case "Parcelamento":
                    // Representante Comercial precisa estar com situação "Parcelamento em aberto."
                    if($dadosRepresentante["situacao"] == "Parcelamento em aberto.") {
                        // Se qualquer anuidade estiver expirada, a certidão não pode ser emitida. Cancelamos a iteração.
                        foreach($cobrancas["anuidades"] as $anuidade) {
                            if($anuidade["SITUACAO"] === "Em aberto" && $anuidade["VENCIMENTOBOLETO"] === null) {
                                $podeEmitir = false;
    
                                $falhaEmissao = "Não foi possível emitir Certidão de " . $tipo . " porque Representante Comercial possui pagamento de anuidade expirado.";
                                break;
                            }
                        }

                        // Se não existe outras cobranças, não há acordo de parcelamento, a certidão não pode ser emitida.
                        if(empty($cobrancas["outros"])) {
                            $podeEmitir = false;
    
                            $falhaEmissao = "Não foi possível emitir Certidão de " . $tipo . " porque Representante Comercial não possui outras cobranças.";
                        }
                        else {
                            $parcelamentosAgrupados = array();
    
                            // Agrupa todos os Acordos por anos parcelados
                            foreach($cobrancas["outros"] as $cobranca) {
                                if(strpos($cobranca["DESCRICAO"], "Acordo") !== false) {
                                    preg_match_all("/\((.*?)\)/", $cobranca["DESCRICAO"], $matches);
    
                                    $parcelamentosAgrupados[$matches[1][0]][] = $cobranca;
    
                                    // Se qualquer parcelamento estiver expirado, a certidão não pode ser emitida. Cancelamos a iteração.
                                    if($cobranca["SITUACAO"] === "Em aberto" && $cobranca["VENCIMENTOBOLETO"] === null) {
                                        $podeEmitir = false;
    
                                        $falhaEmissao = "Não foi possível emitir Certidão de " . $tipo . " porque Representante Comercial possui cobrança expirada expirado.";
                                        break;
                                    }
                                }
                            }
                            
                            // Não existe acordos em outras cobranças, a certidão não pode ser emitida.
                            if (empty($parcelamentosAgrupados)) {
                                $podeEmitir = false;
    
                                $falhaEmissao = "Não foi possível emitir Certidão de " . $tipo . " porque Representante Comercial não possui acordos de parcelamento.";
                            }
                            else {
                                foreach($parcelamentosAgrupados as $grupo) {
                                    $acordoPago = true;
                                    $primeiraParcelaPaga = false;
    
                                    // Iterando para verificar se todas as parcelas foram pagas
                                    foreach($grupo as $index => $parcelamento) {
                                        // Caso uma parcela esteja em aberto, o acordo não foi totalmente pago
                                        if($parcelamento["SITUACAO"] === "Em aberto") {
                                            $acordoPago = false;
                                        }
    
                                        // Último valor do array contêm a primeira parcela do acordo
                                        if($index == count($grupo) - 1) {
                                            // Verifica se a primeira parcela foi paga
                                            if($parcelamento["SITUACAO"] === "Pago") {
                                                $primeiraParcelaPaga = true;
                                            }
                                        }
                                    }
    
                                    // Caso o acordo ainda não esteja totalmente pago e a primeira parcela foi paga, recuperamos dados do acordo
                                    if(!$acordoPago && $primeiraParcelaPaga) {
                                        // Recupera o número de parcelas ([0] = parcela atual, [1] = total de parcelas)
                                        preg_match_all("/Parcela (.*?) Acordo/", $grupo[0]["DESCRICAO"], $matches); 
                                        $numeroParcelas = explode("/", $matches[1][0]);
    
                                        // Recupera os anos do parcelamento
                                        preg_match_all("/\((.*?)\)/", $grupo[0]["DESCRICAO"], $matches); 
                                        $anosParcelas = $matches[1][0];
    
                                        // Recupera data do primeiro pagamento
                                        $primeiroPagamento = onlyDate($grupo[count($grupo) - 1]["VENCIMENTO"]);

                                        break;
                                    }
                                }
                            } 
                        }
                    }
                    else {
                        $podeEmitir = false;

                        $falhaEmissao = "Não foi possível emitir Certidão de " . $tipo . " porque Representante Comercial não possui parcelamentos em aberto.";
                    }

                    // Caso dados de parcelamento não tenham sido encontrados, não foi possível achar um acordo de parcelamento válido.
                    if(!isset($numeroParcelas) || !isset($anosParcelas) || !isset($primeiroPagamento)) {
                        $podeEmitir = false;

                        $falhaEmissao = "Não foi possível emitir Certidão de " . $tipo . " porque nenhum acordo válido foi encontrado.";
                    }

                    // Depois da validação das regras, se for possível emitir, gera declaração da certidão
                    if($podeEmitir) {
                        $declaracao = Certidao::declaracaoParcelamento($dadosRepresentante, $endereco, $numeroParcelas[1], $anosParcelas, $primeiroPagamento);
                    }   
                break;

                // Caso que deve ocorrer, tratamento por precaução.
                default:
                    abort(500, "Tipo de certidão inválida.");
                break;
            }
        }

        // Se possível emitir, salvar a certidão no banco de dados.
        if($podeEmitir) { 
            return $this->salvarCertidao($tipo, $dadosRepresentante, $declaracao, $dataEmissao);
        }
        else {
            // Geração de log externo registrando motivo da falha na emissão.
            event(new ExternoEvent('CPF/CNPJ: "'. $dadosRepresentante["cpf_cnpj"] .'" - ' . $falhaEmissao));

            return view("site.representante.emitir-certidao")
                ->with("erro", $falhaEmissao)
                ->with("titulo", "Certidão de " . $tipo);
        }
    }

    /**
     * Método salva a certidão no banco de dados, gera PDF e envia no e-mail do Representante Comercial
     */
    public function salvarCertidao($tipo, $dadosRepresentante, $declaracao, $dataEmissao) 
    {
        // Usando transaction para garantir consistência caso algum problema ocorra no processo de criação da certidão
        $certidao =  DB::transaction(function () use ($tipo, $dadosRepresentante, $declaracao, $dataEmissao) {
            // Criar a certidao no banco de dados
            $certidao = $this->certidaoRepository->store($tipo, $dadosRepresentante["cpf_cnpj"], $declaracao, $dataEmissao);

            // TODO - Atualizar o GERENTI com a certidão criada

            return $certidao;
        });
        // Formata o código para facilitar a visualização (XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX)
        $codigoCertidao = $certidao->codigoFormatado();

        $data = [
            "hora" => strftime("%H:%M",  strtotime($dataEmissao)),
            "data" => onlyDate($dataEmissao)
        ];

        $titulo = "Certidão de " . $tipo;

        // Cria o PDF usando a view de acordo com o tipo de pessoa
        $pdf = PDF::loadView("certidoes.certidao", compact("declaracao", "codigoCertidao", "data", "titulo"));

        // Envio de e-mail com o PDF
        $email = new CertidaoMail($pdf->output());
        Mail::to($dadosRepresentante["email"])->queue($email);

        // Log externo de emissão de certidão
        event(new ExternoEvent('CPF/CNPJ: "'. $dadosRepresentante["cpf_cnpj"] .'" emitiu Certidão de ' . $tipo . '.'));

        return redirect()->route("representante.visualizaCertidaoRepresentante", $certidao->codigo);
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

            $resultado = $certidao->declaracao;
        }

        return view("site.consulta-certidao", compact(["autenticado", "resultado"]));
    }

    public function consultaView() 
    {
        return view("site.consulta-certidao");
    }
}