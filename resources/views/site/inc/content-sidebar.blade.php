<div class="clippers">
  <a href="http://core-sp.implanta.net.br/portaltransparencia/#publico/inicio" target="_blank">
    <div class="box-dois almost-white-bg mb-3">
      <div class="inside-box-dois inside-box-transparencia d-flex">
        <div class="align-self-center faleRow">
          <img src="{{ asset('img/icon_transparencia.png') }}" alt="Transparência | Core-SP">
        </div>
        <div class="flex-one align-self-center pl-4">
          <h5 class="text-uppercase verde-transparencia">Acesso à informação</h5>
        </div>
      </div>
    </div>
  </a>
  <a href="/agendamento">
    <div class="box-dois mb-3 {{ isset($itens_home['cards_laterais_1']) ? '' : 'azul-escuro-bg' }}" style="{{ isset($itens_home['cards_laterais_1']) ? 'background-color:'.$itens_home['cards_laterais_1'] : '' }}">
      <div class="inside-box-dois d-flex">
        <div class="align-self-center">
          <img src="{{ asset('img/appointment.png') }}" class="inside-img">
        </div>
        <div class="flex-one align-self-center pl-4">
          <h5 class="text-uppercase normal branco">Agendamento de atendimento</h5>
        </div>
      </div>
    </div>
  </a>
  <a href="/representante/login">
    <div class="box-dois mb-3 {{ isset($itens_home['cards_laterais_2']) ? '' : 'azul-bg' }}" style="{{ isset($itens_home['cards_laterais_2']) ? 'background-color:'.$itens_home['cards_laterais_2'] : '' }}">
      <div class="inside-box-dois d-flex">
        <div class="align-self-center">
          <img src="{{ asset('img/padlock.png') }}" class="inside-img">
        </div>
        <div class="flex-one align-self-center pl-4">
          <h5 class="text-uppercase normal branco">Área restrita do Representante</h5>
        </div>
      </div>
    </div>
  </a>
  <a href="/balcao-de-oportunidades">
    <div class="box-dois mb-3 {{ isset($itens_home['cards_laterais_1']) ? '' : 'azul-escuro-bg' }}" style="{{ isset($itens_home['cards_laterais_1']) ? 'background-color:'.$itens_home['cards_laterais_1'] : '' }}">
      <div class="inside-box-dois d-flex">
        <div class="align-self-center">
          <img src="{{ asset('img/001-work.png') }}" class="inside-img">
        </div>
        <div class="flex-one align-self-center pl-4">
          <h5 class="text-uppercase normal branco">Balcão de Oportunidades</h5>
        </div>
      </div>
    </div>
  </a>
  <a href="/consulta-de-situacao">
    <div class="box-dois mb-3 {{ isset($itens_home['cards_laterais_2']) ? '' : 'azul-bg' }}" style="{{ isset($itens_home['cards_laterais_2']) ? 'background-color:'.$itens_home['cards_laterais_2'] : '' }}">
      <div class="inside-box-dois d-flex">
        <div class="align-self-center">
          <img src="{{ asset('img/file.png') }}" class="inside-img" alt="Consulta de Ativos | Core-SP">
        </div>
        <div class="flex-one align-self-center pl-4">
          <h5 class="text-uppercase normal branco">consulta pública</h5>
        </div>
      </div>
    </div>
  </a>
  <a href="/cursos">
    <div class="box-dois mb-3 {{ isset($itens_home['cards_laterais_1']) ? '' : 'azul-escuro-bg' }}" style="{{ isset($itens_home['cards_laterais_1']) ? 'background-color:'.$itens_home['cards_laterais_1'] : '' }}">
      <div class="inside-box-dois d-flex">
        <div class="align-self-center">
          <img src="{{ asset('img/teacher.png') }}" class="inside-img">
        </div>
        <div class="flex-one align-self-center pl-4">
          <h5 class="text-uppercase normal branco">Cursos Programados</h5>
        </div>
      </div>
    </div>
  </a>
  <a href="/simulador">
    <div class="box-dois mb-3 {{ isset($itens_home['cards_laterais_2']) ? '' : 'azul-bg' }}" style="{{ isset($itens_home['cards_laterais_2']) ? 'background-color:'.$itens_home['cards_laterais_2'] : '' }}">
      <div class="inside-box-dois d-flex">
        <div class="align-self-center">
          <img src="{{ asset('img/001-paper.png') }}" class="inside-img" alt="Simulador | Core-SP">
        </div>
        <div class="flex-one align-self-center pl-4">
          <h5 class="text-uppercase normal branco">simulador de valores</h5>
        </div>
      </div>
    </div>
  </a>
</div>