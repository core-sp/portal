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
        {!! $declaracao !!}
      </div>
    </div>
  </body>
</html>