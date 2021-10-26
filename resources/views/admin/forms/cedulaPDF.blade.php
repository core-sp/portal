<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Documento - Cédula Código - {{ $resultado->id }}</title>
  <style>
    #header {
      top: 0;
      font-family: "Times New Roman", Times, serif;
      font-size: 16px;
      /* no doc do word o tamanho da fonte é em pontos ou point */
    }
    #header img {
      float: left;
    }
    #img_conteudo {
      opacity: 0.1;
    }
    #footer {
      bottom: 0;
      font-family: Tahoma, sans-serif;;
      font-size: 9px;
      /* no doc do word o tamanho da fonte é em pontos ou point */
      text-align: center;
    }
    #footer p {
      margin: 0;
    }
  </style>
</head>
<body>


  <!-- cabeçalho do doc -->
  <div id="header">
    <img src="img/brasao_cabecalho_cedula.png" width="109" height="109"/>
    <div>
      <strong>CONSELHO REGIONAL DOS REPRESENTANTES COMERCIAIS</strong>
    </div>
    <div>
      <strong>NO ESTADO DE SÃO PAULO</strong>
    </div>
    <div>
      CORE-SP
    </div>
  </div>
  <!-- fim do cabeçalho do doc -->



  <!-- conteúdo do doc -->
  <div id="conteudo">
    <img id="img_conteudo" src="img/brasao_cabecalho_cedula.png" width="446" height="438" />
  </div>
  <!-- fim do conteúdo do doc -->



  <!-- rodapé do doc -->
  <div id="footer">
    <p>________________________________________________________________________________________________________________________________</p>
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