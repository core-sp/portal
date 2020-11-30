@extends('site.layout.app', ['title' => 'Pre-Cadastro'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/cursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Agendamento
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-licitacao">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-3 align-self-center">
            <h2 class="stronger">Marque seu atendimento no CORE-SP</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mb-4">
      <div class="col">
        <div class="conteudo-txt">
          <!--
          <p><strong>Importante:</strong> O atendimento presencial está suspenso temporariamente, neste período os serviços deverão ser solicitados via email. O prazo para análise e resposta do email depende do tipo de serviço solicitado.</p>
          <p>Para mais informações, acesse <a href="/servicos-atendimento-ao-rc">este link</a>.</p>
          -->
          <p class="pb-0">Agende seu atendimento presencial no Core-SP, com até um mês de antecedência.<br />Ou então, consulte as <a href="/agendamento-consulta" class="text-primary">informações do atendimento já agendado.</a></p>
          <div class="mb-3">
            <a href="https://www.saopaulo.sp.gov.br/planosp/" target="_blank"><img src="{{ asset('img/icone-mapasp.png') }}"></a>
          </div>
        </div>
        <div class="mt-2">
          <form method="POST" class="inscricaoCurso">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <h5>Informações de contato</h5>
            <div class="form-row mt-2">
              <div class="col-md-6">
                <label for="nome">Nome *</label>
                <input type="text"
                  class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                  name="nome"
                  value="{{ old('nome') }}"
                  placeholder="Nome" />
                @if($errors->has('nome'))
                  <div class="invalid-feedback">
                    {{ $errors->first('nome') }}
                  </div>
                @endif
              </div>
              <div class="col-md-6 mt-2-768">
                <label for="cpf">CPF *</label>
                <input type="text"
                  class="form-control cpfInput {{ $errors->has('cpf') ? 'is-invalid' : '' }}"
                  name="cpf"
                  placeholder="CPF"
                  value="{{ old('cpf') }}"
                  />
                @if($errors->has('cpf'))
                  <div class="invalid-feedback">
                    {{ $errors->first('cpf') }}
                  </div>
                @endif
              </div>
            </div>
            <div class="form-row mt-2">
              <div class="col-md-6">
                <label for="email">E-mail *</label>
                <input type="text"
                  class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                  name="email"
                  value="{{ old('email') }}"
                  placeholder="E-mail"
                  />
                @if($errors->has('email'))
                  <div class="invalid-feedback">
                    {{ $errors->first('email') }}
                  </div>
                @endif
              </div>
            </div>
            <div class="float-right mt-4">
              <a href="/" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary">Agendar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div id="dialog_agendamento" title="Atenção"></div>
</section>

@endsection
