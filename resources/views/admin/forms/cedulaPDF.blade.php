<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Documento - Cédula Código - {{ $resultado->id }}</title>
  <style>

    #img_fundo {
      opacity: 0.2;
      margin: 0;
      position: absolute;
      top: 45%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    #header {
      margin-top: 0;
      font-family: 'Times New Roman', serif;
      font-size: 16px;
      /* no doc do word o tamanho da fonte é em pontos */
      text-align: center;
    }

    #header img {
      float: left;
      margin-left: 1%;
      margin-top: 0;
    }

    #header #primeiro-p {
      font-weight: bolder;
      font-weight: 900;
      margin-top: 2%;
      margin-right: 4%;
      margin-bottom: 0;
    }

    #header #segundo-p {
      font-weight: bolder;
      font-weight: 900;
      margin-top: 0;
      margin-right: 4%;
      margin-bottom: 0;
    }

    #header #terceiro-p {
      margin-top: 0.5%;
      margin-right: 4%;
    }

    #conteudo {
      font-family: Tahoma, Verdana, sans-serif;
      font-size: 15px;
      text-align: center;
      margin: 0;
      margin-top: 6%;
    }

    #titulo {
      font-size: 16px;
      margin-top: 1%;
      margin-bottom: 7%;
      margin-left: 6%;
      margin-right: 0;
    }

    #titulo p {
      margin: 0;
      margin-bottom: 1.5%;
    }

    .texto {
      margin: 0;
      margin-left: 11%;
      margin-right: 8%;
      margin-bottom: 2%;
      text-align: justify;
      line-height: 1.5;
    }

    #data {
      float: right;
      margin-right: 8%;
      margin-top: 8%;
    }

    #hr-texto {
      text-align: center;
      width: 45%;
      margin-top: 10%;
      margin-bottom: 0;
    }

    #assinatura {
      text-align: center;
      margin-top: 0;
    }

    #footer {
      font-family: Tahoma, Verdana, sans-serif;
      font-size: 10px;
      text-align: center;
      position: absolute;
      bottom: 0;
    }

    #hr-rodape {
      text-align: center;
      margin: 0;
    }

    #footer p {
      margin: 0;
    }

  </style>
</head>
<body>

  <img id="img_fundo" src="img/brasao_cabecalho_cedula.png" width="446" height="438" />

  <!-- cabeçalho do doc -->
  <div id="header">
    <img src="img/brasao_cabecalho_cedula.png" width="109" height="109"/>
    <p id="primeiro-p">
      CONSELHO REGIONAL DOS REPRESENTANTES COMERCIAIS
    </p>
    <p id="segundo-p">
      NO ESTADO DE SÃO PAULO
    </p>
    <p id="terceiro-p">
      CORE-SP
    </p>
  </div>
  <!-- fim do cabeçalho do doc -->


  <!-- conteúdo do doc -->
  <div id="conteudo">
    <div id="titulo">
      <p>
        <b>RECIBO DE REQUERIMENTO DE ENVIO POSTAL DE CÉDULA DE</b>
      </p>
      <p>
        <b>IDENTIDADE PROFISSIONAL</b>
      </p>
    </div>
    <p class="texto">
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      Eu, {{ $resultado->representante->nome }}, portador(a) da identidade nº {{ $identidade }}, do CPF/CNPJ
      {{ $resultado->representante->cpf_cnpj }} e do registro nº {{ $resultado->representante->registro_core }}, realizado em {{ onlyDate($resultado->updated_at->toDateString()) }}, venho requerer,
      pelo presente, o envio, via postal, por meio do serviço de remessa de documentos dos Correios, de minha cédula de identidade profissional, com 
      fundamento na Resolução nº 1.186/2021 - CORE-SP, declarando estar de acordo com a Resolução em referência.
      </p>
    <p class="texto">
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      O endereço que, nesta data, opto para o encaminhamento da cédula de identidade profissional é 
      {{ $resultado->logradouro }}, {{ $resultado->numero }}{{ $resultado->complemento ? ' - '.$resultado->complemento : '' }}, 
      {{ $resultado->bairro }} - {{ $resultado->municipio }} / {{ $resultado->estado }} - CEP: {{ $resultado->cep }}
    </p>
    <p class="texto">
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      Desde já, fico ciente da obrigatoriedade de manter atualizados todos os meios 
      de contato por mim informados a esse Conselho Regional.
    </p>
    <p id="data">
      São Paulo, {{ now()->isoFormat('D') }} de {{ ucFirst(now()->isoFormat('MMMM')) }} de {{ now()->isoFormat('G') }}.
    </p>
    <hr id="hr-texto">
    <p id="assinatura">
      xxxxxxxxxxxxxxxxxxxxxxxx
    </p>
  </div>
  <!-- fim do conteúdo do doc -->



  <!-- rodapé do doc -->
  <div id="footer">
    <hr id="hr-rodape">
    <p>
      Sede: Av. Brigadeiro Luis Antonio, 613 - 5º andar - Bela Vista - São Paulo - 01317-000 - (11) 3243 5500 - core@core-sp.org.br - www.core-sp.org.br
    </p>
    <p>
      Seccionais: Araçatuba (18) 3625 2080; Campinas (19) 3236 8867; Ribeirão Preto (16) 3964 6636; São José do Rio Preto (17) 3211 9953;
    </p>
    <p>
      Araraquara (16) 3332 2630; Marília (14) 3454 7355; Rio Claro (19) 3533 1912; São José dos Campos (12) 3922 0508; Bauru (14) 3214 4318;
    </p>
    <p>
    Presidente Prudente (18) 3903 6198; Santos (13) 3219 7462; Sorocaba (15) 3233 4322.    
    </p>
  </div>
  <!-- fim do rodapé do doc -->

</body>
</html>