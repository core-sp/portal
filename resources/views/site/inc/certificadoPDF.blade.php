<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificado - {{ $inscrito->curso->tema }}</title>
  <style>

    @font-face { 
      font-family: "Constantia Regular";
      src: url({{ storage_path('fonts/constan.ttf') }});
    }

    @font-face { 
      font-family: "Calibri Bold";
      src: url({{ storage_path('fonts/calibri-bold.ttf') }});
    }

  /* Página 1, Certificado com design */
    #img_fundo {
      position: absolute;
      margin-left: auto;
      margin-right: auto;
      width: 100%;
      z-index: -1;
    }

    #box-primeiro-texto {
      margin: auto;
      width: 65%;
      text-align: center;
      position: absolute;
      top: 175px;
      left: 270px;
      padding: 0;
    }

    #box-nome {
      width: 750px;
      position: absolute;
      bottom: 318px;
      left: 245px;
      padding: 0;
    }

    #box-nome div {
      width: 100%;
      text-align: center;
      padding: 0;
      margin: 0;
    }

    #box-nome div span {
      font-family: "Calibri Bold";
      line-height: 83%;
      color: #153f67;
    }

    #box-descricao {
      margin: auto;
      width: 77%;
      position: absolute;
      top: 410px;
      left: 200px;
      padding: 0;
      text-align: center;
    }

    #box-data {
      margin: auto;
      text-align: center;
      position: absolute;
      bottom: 115px;
      left: 20px;
    }

    .texto-fixo {
      font-size: 23px;
      font-family: "Constantia Regular";
      line-height: 83%;
    }

    .inscrito {
      text-transform: uppercase;
      padding: 0;
      margin: 0;
    }

    .qrcode {
      position: fixed;
      bottom: 145px;
      left: 120px;
    }

    /* Sugestão de validação manual */
    .link {
      position: absolute;
      bottom: -35px;
      left: 120px;
    }

    /* Cria a página seguinte */
    .page-break {
      page-break-after: always;
    }

    /* Página 2, Certificado mais simples */
    #simples {
      position: fixed;
      margin-left: auto;
      margin-right: auto;
      width: 100%;
      border: 1px solid black;
      border-radius: 8px;
      padding: 25px;
    }

    #logo {
      width: 20%;
      float: left;
    }

    #box-pg2-sobre {
      margin-left: 35px;
    }

    #box-pg2-sobre p, #box-pg2-curso p {
      margin: 0;
      padding: 0;
    }

    #box-pg2-sobre #titulo {
      font-size: 23px;
      padding-bottom: 10px;
    }

    #box-pg2-sobre .conteudo {
      font-size: 20px;
    }

    #box-pg2-sobre .conteudo a:link {
      text-decoration: none;
      color: black;
    }

    #box-pg2-sobre .conteudo a:visited {
      text-decoration: none;
    }

    #box-pg2-sobre .conteudo a:hover {
      text-decoration: none;
    }

    #box-pg2-sobre .conteudo a:active {
      text-decoration: none;
    }

    #box-pg2-curso {
      margin-top: 45px;
    }

    #box-pg2-curso .conteudo-curso {
      font-size: 20px;
    }

    .curso-upper {
      text-transform: uppercase;
    }

    #pg2-data {
      text-align: center;
      font-size: 20px;
    }

    #simples #assinatura {
      position: absolute;
      top: 70%;
      left: 41%;
      width: 23%;
    }

  </style>
</head>
<body>

@php

$len = strlen(trim($inscrito->nome));
if($len <= 22)
  $font = '50px';
elseif($len <= 62)
  $font = '40px';
elseif($len <= 75)
  $font = '35px';
else
  $font = '30px';

@endphp

  <img id="img_fundo" src="{{ storage_path('app/certificados/') }}{{ $inscrito->curso->tipo == $inscrito->curso::TIPO_CURSO ? 'certificado_temp_curso.jpg' : 'certificado_temp_palestra_work.jpg' }}" />

  <div id="box-primeiro-texto">
    <p class="texto-fixo">O Conselho Regional dos Representantes Comerciais no Estado de São Paulo (Core-SP), agradece a presença de</p>
  </div>

  <div id="box-nome">
    <div>
      <span class="inscrito" style="font-size: {{ $font }}">{{ trim($inscrito->nome) }}</span>
    </div>
  </div>

  <div id="box-descricao">
    <span class="texto-fixo">n{{ $inscrito->curso->tipo == $inscrito->curso::TIPO_PALESTRA ? 'a' : 'o' }} {{ mb_strtolower($inscrito->curso->tipo, 'UTF-8') }} <b>{{ $inscrito->curso->tema }},</b> ministrada pelo(a) conferencista 
      <strong>{{ $inscrito->curso->conferencista }},</strong> {{ $inscrito->curso->dataRealizacaoCertificado() }} e {{ $inscrito->curso->cargaHorariaCertificado() }}.
    </span>
  </div>

  <div id="box-data">
    <p class="texto-fixo">
      São Paulo, {{ now()->isoFormat('D') }} de {{ ucFirst(now()->isoFormat('MMMM')) }} de {{ now()->isoFormat('G') }}.
    </p>
  </div>

  <img class="qrcode" src="{{ storage_path('app/certificados/temp/' . $nome_file) }}">

  <!-- Sugestão de validação manual -->
  <a href="{{ route('cursos.certificado.validar', $inscrito->checksum) }}" class="link">{{ route('cursos.certificado.validar', $inscrito->checksum) }}</a>

  <div class="page-break"></div>

  <div id="simples">

    <img id="logo" src="{{ public_path('img/brasao_cabecalho_cedula.png') }}" />

    <div id="box-pg2-sobre">
      <p id="titulo"><strong>Conselho Regional dos Representantes Comerciais no Estado de São Paulo</strong></p>
      <br />
      <p class="conteudo">Av. Brigadeiro Luís Antônio, 613, 5º andar, Bela Vista - São Paulo / SP.</p>
      <br />
      <p class="conteudo">CNPJ: 60.746.179/0001-52</p>
      <br />
      <p class="conteudo">(11) 3243-5500 / 5519</p>
      <br />
      <p class="conteudo"><a href="https://www.core-sp.org.br/" target="_blank">www.core-sp.org.br</a></p>
    </div>

    <div id="box-pg2-curso">
      <p class="conteudo-curso">Certificamos que <span class="curso-upper">{{ trim($inscrito->nome) }}</span> participou d{{ $inscrito->curso->tipo == $inscrito->curso::TIPO_PALESTRA ? 'a' : 'o' }} {{ mb_strtolower($inscrito->curso->tipo, 'UTF-8') }} "{{ $inscrito->curso->tema }}", ministrada pelo(a) conferencista 
      "{{ $inscrito->curso->conferencista }}", {{ $inscrito->curso->dataRealizacaoCertificado() }} e {{ $inscrito->curso->cargaHorariaCertificado() }}.</p>

      <br />
      <br />
    </div>

    <p id="pg2-data">São Paulo, {{ now()->isoFormat('D') }} de {{ ucFirst(now()->isoFormat('MMMM')) }} de {{ now()->isoFormat('G') }}.</p>

    <img id="assinatura" src="{{ storage_path('app/certificados/assinatura.png') }}" />

    <img class="qrcode" src="{{ storage_path('app/certificados/temp/' . $nome_file) }}">

    <!-- Sugestão de validação manual -->
    <a href="{{ route('cursos.certificado.validar', $inscrito->checksum) }}" class="link">{{ route('cursos.certificado.validar', $inscrito->checksum) }}</a>

  </div>

</body>
</html>