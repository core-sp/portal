@extends('site.layout.app', ['title' => 'Pre-Cadastro'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/cursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Pré-Cadastro
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
            <h2 class="stronger">Forneça informações para o pré-cadastro</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>

    <div class="form-row mb-4">
      <div class="col">
        <div class="mt-2">
          <form method="POST" class="inscricaoCurso" enctype="multipart/form-data">

            <h5>Informações do Representante Comercial</h5>
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="form-row mt-2">
              <div class="col-md-4">
                <label for="nome">Nome *</label>
                <input type="text"
                  class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                  name="nome"
                  value="{{ old('nome') }}"
                  placeholder="Nome" 
                />
                @if($errors->has('nome'))
                  <div class="invalid-feedback">
                    {{ $errors->first('nome') }}
                  </div>
                @endif
              </div>
              <div class="col-md-4 mt-2-768">
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
              <div class="col-md-4 mt-2-768">
                <label for="cnpj">CNPJ *</label>
                <input type="text"
                  class="form-control cnpjInput {{ $errors->has('cnpj') ? 'is-invalid' : '' }}"
                  name="cnpj"
                  placeholder="CNPJ"
                  value="{{ old('cnpj') }}"
                />
                @if($errors->has('cnpj'))
                  <div class="invalid-feedback">
                    {{ $errors->first('cnpj') }}
                  </div>
                @endif
              </div>
            </div>
            <div class="form-row mt-2">
              <div class="col-md-12">
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

            <h5>Informações de endereço</h5>
            <div class="form-row mb-2 cadastroRepresentante">
              <div class="col-sm mb-2-576">
                <label for="cep">CEP *</label>
                <input
                  type="text"
                  name="cep"
                  class="form-control cep {{ $errors->has('cep') ? 'is-invalid' : '' }}"
                  id="cep"
                  placeholder="CEP"
                  value="{{ old('cep') }}"
                />
                @if($errors->has('cep'))
                  <div class="invalid-feedback">
                    {{ $errors->first('cep') }}
                  </div>
                @endif
              </div>
              <div class="col-sm">
                <label for="bairro">Bairro *</label>
                  <input
                    type="text"
                    name="bairro"
                    class="form-control {{ $errors->has('bairro') ? 'is-invalid' : '' }}"
                    id="bairro"
                    placeholder="Bairro"
                    value="{{ old('bairro') }}"
                  />
                  @if($errors->has('bairro'))
                    <div class="invalid-feedback">
                      {{ $errors->first('bairro') }}
                    </div>
                  @endif
              </div>
            </div>
            <div class="form-group mb-2 cadastroRepresentante">
              <label for="rua">Logradouro *</label>
              <input
                type="text"
                name="logradouro"
                class="form-control {{ $errors->has('logradouro') ? 'is-invalid' : '' }}"
                id="rua"
                placeholder="Logradouro"
                value="{{ old('logradouro') }}"
              />
              @if($errors->has('logradouro'))
                <div class="invalid-feedback">
                  {{ $errors->first('logradouro') }}
                </div>
              @endif
            </div>
            <div class="form-row mb-2 cadastroRepresentante">
              <div class="col-sm mb-2-576">
                <label for="numero">Número *</label>
                <input
                  type="text"
                  name="numero"
                  class="form-control numero {{ $errors->has('numero') ? 'is-invalid' : '' }}"
                  id="numero"
                  placeholder="Número"
                  value="{{ old('numero') }}"
                />
                @if($errors->has('numero'))
                  <div class="invalid-feedback">
                    {{ $errors->first('numero') }}
                  </div>
                @endif
              </div>
              <div class="col-sm">
                <label for="complemento">Complemento</label>
                <input
                  type="text"
                  name="complemento"
                  class="form-control {{ $errors->has('complemento') ? 'is-invalid' : '' }}"
                  id="complemento"
                  placeholder="Complemento"
                  value="{{ old('complemento') }}"
                />
                @if($errors->has('complemento'))
                  <div class="invalid-feedback">
                    {{ $errors->first('complemento') }}
                  </div>
                @endif
              </div>
            </div>
            <div class="form-row mb-2 cadastroRepresentante">
              <div class="col-sm mb-2-576">
                <label for="uf">Estado *</label>
                <select name="estado" id="uf" class="form-control {{ $errors->has('estado') ? 'is-invalid' : '' }}">
                  @foreach (estados() as $key => $estado)
                    <option value="{{ $key }}" {{ old('estado') === $key ? 'selected' : '' }}>{{ $estado }}</option>
                  @endforeach
                </select>
                @if($errors->has('estado'))
                  <div class="invalid-feedback">
                    {{ $errors->first('estado') }}
                  </div>
                @endif
              </div>
              <div class="col-sm">
                <label for="cidade">Município *</label>
                <input
                  type="text"
                  name="municipio"
                  id="cidade"
                  class="form-control {{ $errors->has('municipio') ? 'is-invalid' : '' }}"
                  placeholder="Município"
                  value="{{ old('municipio') }}"
                />
                @if($errors->has('municipio'))
                  <div class="invalid-feedback">
                    {{ $errors->first('municipio') }}
                  </div>
                @endif
              </div>
            </div>

            <h5>Anexos</h5>
            <div class="cadastroRepresentante">
              <label>Anexo 1 *</label>
            </div>
            <div class="custom-file">
              <input
                type="file"
                name="anexo1"
                class="custom-file-input {{ $errors->has('anexo1') ? 'is-invalid' : '' }}"
                id="comprovante-residencia"
                role="button"
              />
              <label class="custom-file-label" for="comprovante-residencia">Selecionar arquivo...</label>
              @if($errors->has('anexo1'))
                <div class="invalid-feedback">
                  {{ $errors->first('anexo1') }}
                </div>
              @endif
            </div>
            <div class="cadastroRepresentante">
              <label>Anexo 2 *</label>
            </div>
            <div class="custom-file">
              <input
                type="file"
                name="anexo2"
                class="custom-file-input {{ $errors->has('anexo2') ? 'is-invalid' : '' }}"
                id="comprovante-residencia"
                role="button"
              />
              <label class="custom-file-label" for="comprovante-residencia">Selecionar arquivo...</label>
              @if($errors->has('anexo2'))
                <div class="invalid-feedback">
                  {{ $errors->first('anexo2') }}
                </div>
              @endif
            </div>

            <div class="float-right mt-4">
              <a href="/" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary">Criar</button>
            </div>
            
          </form>
        </div>
      </div>
    </div>
  </div>
  <div id="dialog_agendamento" title="Atenção"></div>
</section>

@endsection
