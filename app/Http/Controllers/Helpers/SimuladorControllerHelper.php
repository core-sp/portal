<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SimuladorControllerHelper extends Controller
{
    public static function listaCores()
    {
        return [
            1 => 'Core-AL',
            2 => 'Core-AM',
            3 => 'Core-BA',
            4 => 'Core-CE',
            5 => 'Core-DF',
            6 => 'Core-ES',
            7 => 'Core-GO',
            8 => 'Core-MA',
            9 => 'Core-MG',
            10 => 'Core-MS',
            11 => 'Core-MT',
            12 => 'Core-PA',
            13 => 'Core-PB',
            14 => 'Core-PE',
            15 => 'Core-PI',
            16 => 'Core-PR',
            17 => 'Core-RJ',
            18 => 'Core-RN',
            19 => 'Core-RO',
            20 => 'Core-RS',
            21 => 'Core-SC',
            22 => 'Core-SE',
            23 => 'Core-TO',
            24 => 'Core-SP'
        ];
    }

    public static function tipoPessoa()
    {
        return [
            2 => 'Física',
            5 => 'Física RT',
            1 => 'Jurídica'
        ];
    }

    public static function textoPessoaFisica()
    {
        return '
            <h4>RELAÇÃO DE DOCUMENTOS PARA REGISTRO PESSOA FÍSICA</h4>
            <p><i>(Lei nº 4.886/65, Art. 3º)</i></p>
            <p>Com a aprovação do novo Código Civil em 11 de janeiro de 2003 (através da Lei Nº 10.406, de 10 de janeiro de 2002), <strong>a maioridade civil passou a ser a partir dos 18 anos no Brasil.</strong></p>
            <p>Apresentar cópia dos documentos abaixo relacionados:</p>
            <ol type="a">
                <li>Carteira de Identidade, CNH (data de expedição -10 anos), e ou RNE para estrangeiros;</li>
                <li>Comprovante de residência recente (máximo 03 meses);</li>
                <li>2 fotos 3x4 (sem data);</li>
                <li>Comprovante de quitação com o serviço militar, para os profissionais do sexo masculino que tenham até 45 anos (exceto estrangeiro);</li>
                <li>Prova de estar em dia com as obrigações eleitorais - certidão de quitação eleitoral (site www.tse.jus.br >serviços ao eleitor>certidão de quitação eleitoral); (exceto estrangeiro);</li>
                <li>Cópia da quitação das contribuições devidas ao Sindicato conforme artigo 579 e 608 da CLT e nota técnica da Secretaria de Relações do Trabalho (SRT) nº 202 de 10 de dezembro de 2009. (www.sircesp.com.br); <small><strong>(caráter facultativo)</strong></small></li>
            </ol>
            <p>OBS. Se o registro for requerido através de terceiros será necessário apresentar procuração especifica para realizar o registro no CORE-SP, dando poderes para representá-lo, e cópia do documento do procurador;</p>
            <strong>Conselho Regional dos Representantes Comerciais no Estado de São Paulo</strong>
            <p>Av. Brigadeiro Luis Antonio, 613 - CEP 01317-000 - São Paulo<br />SARC: (11) 3243-5519 - Fone: (11) 3243-5500<br />Atendimento de 2ª à 6ª feiras - das 9h às 18h<br />Horário de registro - das 9h às 15h30<br />Email: atendimento@core-sp.org.br</p>
        ';
    }

    public static function textoPessoaFisicaRt()
    {
        return '
            <h4>RELAÇÃO DE DOCUMENTOS PARA REGISTRO PESSOA FÍSICA - RESPONSÁVEL TÉCNICO</h4>
            <p><i>(Lei nº 4.886/65, Art. 3º)</i></p>
            <p>Com a aprovação do novo Código Civil em 11 de janeiro de 2003 (através da Lei Nº 10.406, de 10 de janeiro de 2002), <strong>a maioridade civil passou a ser a partir dos 18 anos no Brasil.</strong></p>
            <p>Apresentar cópia dos documentos abaixo relacionados:</p>
            <ol type="a">
                <li>Contrato Social de constituição e alterações contratuais consolidadas devidamente registradas no órgão competente;</li>
                <li>Carteira de Identidade, CNH (data de expedição -10 anos), e ou RNE para estrangeiros;</li>
                <li>Comprovante de residência recente (máximo 03 meses);</li>
                <li>2 fotos 3x4 (sem data);</li>
                <li>Comprovante de quitação com o serviço militar, para os profissionais do sexo masculino que tenham até 45 anos (exceto estrangeiro);</li>
                <li>Prova de estar em dia com as obrigações eleitorais - certidão de quitação eleitoral (site www.tse.jus.br >serviços ao eleitor>certidão de quitação eleitoral); (exceto estrangeiro);</li>
                <li>Declaração de indicação do responsável técnico assinada por todos os sócios com a concordância do indicado, com reconhecimento de firma; (www.core-sp.org.br/downloads/IndRespTec.pdf)</li>
                <li>Cópia da quitação das contribuições devidas ao Sindicato conforme artigo 579 e 608 da CLT e nota técnica da Secretaria de Relações do Trabalho (SRT) nº 202 de 10 de dezembro de 2009. (www.sircesp.com.br); <small><strong>(caráter facultativo)</strong></small></li>
            </ol>
            <p>OBS. Se o registro for requerido através de terceiros será necessário apresentar procuração especifica para realizar o registro no CORE-SP, dando poderes para representá-lo, e cópia do documento do procurador;</p>
            <strong>Conselho Regional dos Representantes Comerciais no Estado de São Paulo</strong>
            <p>Av. Brigadeiro Luis Antonio, 613 - CEP 01317-000 - São Paulo<br />SARC: (11) 3243-5519 - Fone: (11) 3243-5500<br />Atendimento de 2ª à 6ª feiras - das 9h às 18h<br />Horário de registro - das 9h às 15h30<br />Email: atendimento@core-sp.org.br</p>
        ';
    }

    public static function textoPessoaJuridica()
    {
        return '
            <h4>RELAÇÃO DE DOCUMENTOS PARA REGISTRO PESSOA JURÍDICA</h4>
            <p><i>(Lei nº 4.886/65, Art. 3º)</i></p>
            <p>As pessoas jurídicas legalmente constituídas para os serviços de Representação Comercial, agência, agenciamento, intermediação de negócios, intermediação por conta de terceiros, distribuição ou atividade equivalente estão obrigadas a se registrarem no Conselho Regional em cuja jurisdição exerçam suas atividades, sendo-lhes exigidos CÓPIA LEGIVEL dos seguintes documentos:</p>
            <ol type="a">
                <li>Contrato social e alterações contratuais consolidadas devidamente registradas no órgão competente e inscrição no CNPJ;</li>
                <li>Carteira de Identidade, CNH (data de expedição -10 anos), e ou RNE para estrangeiros de todos os sócios;</li>
                <li>Comprovante de residência recente (máximo 03 meses) de todos os sócios;</li>
                <li>Quitação das contribuições devidas ao Sindicato conforme artigo 579 e 608 da CLT e nota técnica da Secretaria de Relações do Trabalho (SRT) nº 202 de 10 de dezembro de 2009 (www.sircesp.com.br); <small><strong>(caráter facultativo)</strong></small></li>
                <li>Declaração de indicação do responsável técnico assinada por todos os sócios com a concordância do indicado, com reconhecimento de firma;</li>
                <li>Alvará de localização e inscrição ISS;</li>
            </ol>
            <p>OBS. Se o registro for requerido através de terceiros será necessário apresentar procuração especifica para realizar o registro no CORE-SP, dando poderes para representá-lo, e cópia do documento do procurador;</p>
            <h4 class="mt-4">RELAÇÃO DE DOCUMENTOS DO RESPONSÁVEL TÉCNICO EXIGIDOS PARA O REGISTRO:</h4>
            <ol type="a">
                <li>Carteira de Identidade, CNH (data de expedição -10 anos), e ou RNE para estrangeiros;</li>
                <li>Comprovante de residência recente (máximo 03 meses);</li>
                <li>2 fotos 3x4 (sem data);</li>
                <li>Comprovante de quitação com o serviço militar, para os profissionais do sexo masculino que tenham até 45 anos (exceto estrangeiro);</li>
                <li>Prova de estar em dia com as obrigações eleitorais - certidão de quitação eleitoral (site www.tse.jus.br >serviços ao eleitor>certidão de quitação eleitoral); (exceto estrangeiro);</li>
                <li>Declaração de indicação do responsável técnico assinada por todos os sócios com a concordância do indicado, com reconhecimento de firma; (www.core-sp.org.br/downloads/IndRespTec.pdf)</li>
                <li>Cópia da quitação das contribuições devidas ao Sindicato conforme artigo 579 e 608 da CLT e nota técnica da Secretaria de Relações do Trabalho (SRT) nº 202 de 10 de dezembro de 2009. (www.sircesp.com.br); <small><strong>(caráter facultativo)</strong></small></li>
            </ol>
            <p>OBS. Se o registro for requerido através de terceiros será necessário apresentar procuração especifica para realizar o registro no CORE-SP, dando poderes para representá-lo, e cópia do documento do procurador;</p>
            <strong>Conselho Regional dos Representantes Comerciais no Estado de São Paulo</strong>
            <p>Av. Brigadeiro Luis Antonio, 613 - CEP 01317-000 - São Paulo<br />SARC: (11) 3243-5519 - Fone: (11) 3243-5500<br />Atendimento de 2ª à 6ª feiras - das 9h às 18h<br />Horário de registro - das 9h às 15h30<br />Email: atendimento@core-sp.org.br</p>
        ';
    }

    public static function textoPessoaJuridicaEmpresaIndividual()
    {
        return '
            <h4>RELAÇÃO DE DOCUMENTOS PARA REGISTRO EMPRESA INDIVIDUAL</h4>
            <p><i>(Lei nº 4.886/65, Art. 3º)</i></p>
            <p>As pessoas jurídicas legalmente constituídas para os serviços de Representação Comercial, agência, agenciamento, intermediação de negócios, intermediação por conta de terceiros, distribuição ou atividade equivalente estão obrigadas a se registrarem no Conselho Regional em cuja jurisdição exerçam suas atividades, sendo-lhes exigidos <strong>CÓPIA LEGIVEL</strong> dos seguintes documentos:</p>
            <ol type="a">
                <li>Contrato social e alterações contratuais consolidadas devidamente registradas no órgão competente e inscrição no CNPJ;</li>
                <li>Documento de identidade (RG) e CPF responsável;</li>
                <li>Comprovante de residência responsável;</li>
                <li>Cópia da quitação das contribuições devidas ao Sindicato conforme artigo 579 e 608 da CLT e nota técnica da Secretaria de Relações do Trabalho (SRT) nº 202 de 10 de dezembro de 2009. (www.sircesp.com.br); <small><strong>(caráter facultativo)</strong></small></li>
                <li>Alvará de localização e inscrição ISS;</li>
            </ol>
            <p>OBS. Se o registro for requerido através de terceiros será necessário apresentar procuração especifica para realizar o registro no CORE-SP, dando poderes para representá-lo, e cópia do documento do procurador;</p>
            <strong>Conselho Regional dos Representantes Comerciais no Estado de São Paulo</strong>
            <p>Av. Brigadeiro Luis Antonio, 613 - CEP 01317-000 - São Paulo<br />SARC: (11) 3243-5519 - Fone: (11) 3243-5500<br />Atendimento de 2ª à 6ª feiras - das 9h às 18h<br />Horário de registro - das 9h às 15h30<br />Email: atendimento@core-sp.org.br</p>
        ';
    }
}
