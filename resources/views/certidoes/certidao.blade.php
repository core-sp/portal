<html>
  <head>
    <style>
      .texto-certidao {
        margin-top: 50px;
        margin-bottom: 50px;
        margin-right: 10px;
        margin-left: 10px;
        text-align: justify;
      }
      .centro {
        text-align:center;
      }
      .tab {
        padding-left: 6em;
      }
    </style>
  </head>
  <body>
    <div style="padding: 70 0;">
      <div class="centro">
          <img src="{{ public_path('img/logo-core.png') }}" alt="CORE-SP" />
      </div>
      <div>
        <h1 class="centro">{!! $titulo !!}</h1>

        <p class="texto-certidao">
          {!! $declaracao !!}
        </p>

        <p class="texto-certidao">
          Esta certidão foi emitida em <b>{{ $data["data"] }},</b> às <b>{{ $data["hora"] }}</b> e possui validade de 30 dias. Para verificar autenticidade deste documento entre no site do CORE-SP https://www.core-sp.org.br/certidao/consulta e utilize o código abaixo:
        </p>
        <p class="centro"><b>{{ $codigoCertidao }}</b><p>
      </div>
    </div>
  </body>
</html>