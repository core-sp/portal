@extends('site.layout.app', ['title' => 'Consulta de Certidão'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/cursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Consulta de Certidão
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-conteudo">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-3 align-self-center">
            <h2 class="stronger">Consulte certidões no CORE-SP</h2>
          </div>
          <div class="align-self-center">
            <a href="{{ isset($autenticado) ? route('certidao.consultaView') : '/' }}" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>

    @if(isset($autenticado))
      <div class="mt-3">
        @if($autenticado)
        <div class="alert alert-warning"> 
          <h5><i class="icon fa fa-check"></i>Esta é uma certidão autêntica emitida pelo site oficial do CORE-SP.</h5>
          <p><strong>{!! $resultado !!}</strong></p>
        </div>
        @else
        <div class="alert">
          <h5><i class="icon fa fa-times"></i>Certidão não encontrada ou vencida. Por favor verifique se as informações fornecidas estão corretas e tente novamente.</h5>
        </div>
        @endif
      </div>
    @else
    <div class="row mb-4">
      <div class="col">
        <div class="conteudo-txt">
          <p class="pb-0">Forneça as informações abaixo para verificar a autencidade de certidões.</p>
        </div>
        <div class="mt-2">
          <form method="GET" action="{{ route('certidao.consulta') }}" class="inscricaoCurso">

            <h5>Informações da certidão</h5>

            <div class="form-row mt-4">
              <div class="col-md-12">
                <label for="codigo">Código *</label>
                <input type="text"
                  class="form-control {{ $errors->has('codigo') ? 'is-invalid' : '' }}"
                  name="codigo"
                  value="{{ old('codigo') }}"
                />
                @if($errors->has('codigo'))
                  <div class="invalid-feedback">
                    {{ $errors->first('codigo') }}
                  </div>
                @endif
              </div>
            </div>

            <div class="form-row mt-3">
            <div class="col-md-4">
                <label for="numero">Número *</label>
                <input type="text"
                  class="form-control {{ $errors->has('numero') ? 'is-invalid' : '' }}"
                  name="numero"
                  value="{{ old('numero') }}"
                  />
                @if($errors->has('numero'))
                  <div class="invalid-feedback">
                    {{ $errors->first('numero') }}
                  </div>
                @endif
              </div>
              <div class="col-md-4">
                <label for="hora">Hora da emissão *</label>
                <input type="text"
                  class="form-control horaInput {{ $errors->has('hora') ? 'is-invalid' : '' }}"
                  name="hora"
                  placeholder="hh:mm:ss"
                  value="{{ old('hora') }}"
                  />
                @if($errors->has('hora'))
                  <div class="invalid-feedback">
                    {{ $errors->first('hora') }}
                  </div>
                @endif
              </div>
              <div class="col-md-4">
                <label for="data">Data da emissão *</label>
                <input type="text"
                  class="form-control dataInput {{ $errors->has('data') ? 'is-invalid' : '' }}"
                  name="data"
                  placeholder="dd/mm/aaaaa"
                  value="{{ old('data') }}"
                  />
                @if($errors->has('data'))
                  <div class="invalid-feedback">
                    {{ $errors->first('data') }}
                  </div>
                @endif
              </div>
            </div>
           
            <div class="form-group mt-2">
            @if(env('GOOGLE_RECAPTCHA_KEY'))
              <div class="g-recaptcha {{ $errors->has('g-recaptcha-response') ? 'is-invalid' : '' }}" data-sitekey="{{ env('GOOGLE_RECAPTCHA_KEY') }}"></div>
              @if($errors->has('g-recaptcha-response'))
                <div class="invalid-feedback" style="display:block;">
                {{ $errors->first('g-recaptcha-response') }}
                </div>
              @endif
            @endif
            </div>

            <div class="float-right mt-4">
              <button type="submit" class="btn btn-primary">Consultar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endif
  </div>
</section>

@endsection
