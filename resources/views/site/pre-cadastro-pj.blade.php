@extends('site.layout.app', ['title' => 'Pre-Cadastro - ' . $tipo])

@php 
  use App\PreCadastro;
  $listaEstadoCivil = ['Solteiro', 'Casado'];
  $listaSexo = ['Masculino', 'Feminino'];
  $listaNaturalizado = ['Não', 'Sim'];
  $listaTipoDocumento = ['RG', 'CNH'];
  $listaFormaRegistro = ['JUCESP', 'Cartório'];
  $listaSegmento = ['segmento1', 'segmento2'];
  $listaRamoAtividade = ['Representação', 'Intermediação e agenciamento', 'Distribuição'];
  $listaCapitalSocial = [
        'Até R$ 10.000,00',
        'Até R$ 50.000,00',
        'Até R$ 100.000,00',
        'Até R$ 300.000,00',
        'Até R$ 500.000,00',
        'Maior que R$ 500.000,00'
    ];
@endphp


@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/cursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Pré-Cadastro - {{ $tipo }}
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
            <h2 class="stronger">Forneça informações para o Pré-cadastro de {{ $tipo }}</h2>
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
          <form method="POST" action="{{ route('pre-cadastro.store') }}" class="inscricaoCurso" enctype="multipart/form-data">
            <input type="hidden" name="tipo" value="{{ $tipo }}" />
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">

            <h5>Informações do Cadastro</h5>

            <div class="form-row mt-2">
              <div class="col-md-12">
                <label for="razaoSocial">Razão Social *</label>
                <input type="text"
                  class="form-control {{ $errors->has('razaoSocial') ? 'is-invalid' : '' }}"
                  name="razaoSocial"
                  value="{{ old('razaoSocial') }}"
                  placeholder="Razão Social" 
                />
                @if($errors->has('razaoSocial'))
                  <div class="invalid-feedback">
                    {{ $errors->first('razaoSocial') }}
                  </div>
                @endif
              </div>
            </div>

            <div class="form-row mt-2">
              <div class="col-md-4">
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

              <div class="col-md-2 mt-2-768">
                <label for="formaRegistro">Forma de Registro *</label>
                <select name="formaRegistro" class="form-control {{ $errors->has('formaRegistro') ? 'is-invalid' : '' }}">
                  <option value="">Selecione</option>
                  @foreach($listaFormaRegistro as $formaRegistro)
                  <option value="{{ $formaRegistro }}" {{ old('formaRegistro') === $formaRegistro ? 'selected' : '' }}>{{ $formaRegistro }}</option>
                  @endforeach
                </select>
                @if($errors->has('formaRegistro'))
                <div class="invalid-feedback">
                  {{ $errors->first('formaRegistro') }}
                </div>
                @endif
              </div>

              <div class="col-md-4 mt-2-768">
                <label for="numeroRegistro">Número do Registro *</label>
                <input type="text"
                  class="form-control {{ $errors->has('numeroRegistro') ? 'is-invalid' : '' }}"
                  name="numeroRegistro"
                  placeholder="Número do documento"
                  value="{{ old('numeroRegistro') }}"
                />
                @if($errors->has('numeroRegistro'))
                <div class="invalid-feedback">
                  {{ $errors->first('numeroRegistro') }}
                </div>
                @endif
              </div>

              <div class="col-md-2 mt-2-768">
                <label for="dataRegistro">Data do Registro *</label>
                <input type="text"
                  class="form-control dataInput {{ $errors->has('dataRegistro') ? 'is-invalid' : '' }}"
                  name="dataRegistro"
                  placeholder="dd/mm/aaaa"
                  value="{{ old('dataRegistro') }}"
                />
                @if($errors->has('dataRegistro'))
                <div class="invalid-feedback">
                  {{ $errors->first('dataRegistro') }}
                </div>
                @endif
              </div>
            </div>

            <div class="form-row mt-2">
              <div class="col-md-12 mt-2-768">
                <label for="ramoAtividade">Ramo Atividade *</label>
                <select name="ramoAtividade" class="form-control {{ $errors->has('ramoAtividade') ? 'is-invalid' : '' }}">
                  <option value="">Selecione</option>
                  @foreach($listaRamoAtividade as $ramoAtividade)
                    <option value="{{ $ramoAtividade }}" {{ old('ramoAtividade') === $ramoAtividade ? 'selected' : '' }}>{{ $ramoAtividade }}</option>
                  @endforeach
                </select>
                @if($errors->has('ramoAtividade'))
                <div class="invalid-feedback">
                  {{ $errors->first('ramoAtividade') }}
                </div>
                @endif
              </div>
            </div>

            </br><h5>Informações de Relacionamento</h5>
            
            <div class="form-row mt-2">
              <div class="col-md-12">
                <label for="nome">Nome Completo *</label>
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
            </div>

            <div class="form-row mt-2">
              <div class="col-md-3 mt-2-768">
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

              <div class="col-md-2 mt-2-768">
                <label for="tipoDocumento">Tipo Documento *</label>
                <select name="tipoDocumento" class="form-control {{ $errors->has('tipoDocumento') ? 'is-invalid' : '' }}">
                  <option value="">Selecione</option>
                  @foreach($listaTipoDocumento as $tipoDocumento)
                  <option value="{{ $tipoDocumento }}" {{ old('tipoDocumento') === $tipoDocumento ? 'selected' : '' }}>{{ $tipoDocumento }}</option>
                  @endforeach
                </select>
                @if($errors->has('tipoDocumento'))
                <div class="invalid-feedback">
                  {{ $errors->first('tipoDocumento') }}
                </div>
                @endif
              </div>

              <div class="col-md-3 mt-2-768">
                <label for="numeroDocumento">Número do Documento *</label>
                <input type="text"
                  class="form-control {{ $errors->has('numeroDocumento') ? 'is-invalid' : '' }}"
                  name="numeroDocumento"
                  placeholder="Número do documento"
                  value="{{ old('numeroDocumento') }}"
                />
                @if($errors->has('numeroDocumento'))
                <div class="invalid-feedback">
                  {{ $errors->first('numeroDocumento') }}
                </div>
                @endif
              </div>

              <div class="col-md-2 mt-2-768">
                <label for="orgaoEmissor">Orgão Emissor *</label>
                <input type="text"
                  class="form-control {{ $errors->has('orgaoEmissor') ? 'is-invalid' : '' }}"
                  name="orgaoEmissor"
                  placeholder="Orgão emissor"
                  value="{{ old('orgaoEmissor') }}"
                />
                @if($errors->has('orgaoEmissor'))
                <div class="invalid-feedback">
                  {{ $errors->first('orgaoEmissor') }}
                </div>
                @endif
              </div>

              <div class="col-md-2 mt-2-768">
                <label for="dataExpedicao">Data de Expedição *</label>
                <input type="text"
                  class="form-control dataInput {{ $errors->has('dataExpedicao') ? 'is-invalid' : '' }}"
                  name="dataExpedicao"
                  placeholder="dd/mm/aaaa"
                  value="{{ old('dataExpedicao') }}"
                />
                @if($errors->has('dataExpedicao'))
                <div class="invalid-feedback">
                  {{ $errors->first('dataExpedicao') }}
                </div>
                @endif
              </div>

            </div>

            <div class="form-row mt-2">
              <div class="col-md-2 mt-2-768">
                <label for="dataNascimento">Data de Nascimento *</label>
                <input type="text"
                  class="form-control dataInput {{ $errors->has('dataNascimento') ? 'is-invalid' : '' }}"
                  name="dataNascimento"
                  placeholder="dd/mm/aaaa"
                  value="{{ old('dataNascimento') }}"
                />
                @if($errors->has('dataNascimento'))
                <div class="invalid-feedback">
                  {{ $errors->first('dataNascimento') }}
                </div>
                @endif
              </div>

              <div class="col-md-2 mt-2-768">
                <label for="estadoCivil">Estado Civil *</label>
                <select name="estadoCivil" class="form-control {{ $errors->has('estadoCivil') ? 'is-invalid' : '' }}">
                  <option value="">Selecione</option>
                  @foreach($listaEstadoCivil as $estadoCivil)
                    <option value="{{ $estadoCivil }}" {{ old('estadoCivil') === $estadoCivil ? 'selected' : '' }}>{{ $estadoCivil }}</option>
                  @endforeach
                </select>
                @if($errors->has('estadoCivil'))
                <div class="invalid-feedback">
                  {{ $errors->first('estadoCivil') }}
                </div>
                @endif
              </div>

              <div class="col-md-2 mt-2-768">
                <label for="sexo">Sexo *</label>
                <select name="sexo" class="form-control {{ $errors->has('sexo') ? 'is-invalid' : '' }}">
                  <option value="">Selecione</option>
                  @foreach($listaSexo as $sexo)
                    <option value="{{ $sexo }}" {{ old('sexo') === $sexo ? 'selected' : '' }}>{{ $sexo }}</option>
                  @endforeach
                </select>
                @if($errors->has('sexo'))
                <div class="invalid-feedback">
                  {{ $errors->first('sexo') }}
                </div>
                @endif
              </div>

              <div class="col-md-2 mt-2-768">
                <label for="naturalizado">Naturalizado *</label>
                <select name="naturalizado" class="form-control {{ $errors->has('naturalizado') ? 'is-invalid' : '' }}">
                  <option value="">Selecione</option>
                  @foreach($listaNaturalizado as $naturalizado)
                    <option value="{{ $naturalizado }}" {{ old('naturalizado') === $naturalizado ? 'selected' : '' }}>{{ $naturalizado }}</option>
                  @endforeach
                </select>
                @if($errors->has('naturalizado'))
                <div class="invalid-feedback">
                  {{ $errors->first('naturalizado') }}
                </div>
                @endif
              </div>

              <div class="col-md-4 mt-2-768">
                <label for="nacionalidade">Nacionalidade *</label>
                <input type="text"
                  class="form-control {{ $errors->has('nacionalidade') ? 'is-invalid' : '' }}"
                  name="nacionalidade"
                  placeholder=""
                  value="{{ old('nacionalidade') }}"
                />
                @if($errors->has('nacionalidade'))
                <div class="invalid-feedback">
                  {{ $errors->first('nacionalidade') }}
                </div>
                @endif
              </div>

            </div>

            <div class="form-row mt-2">
              <div class="col-md-12">
                <label for="nomeMae">Nome da Mãe *</label>
                <input type="text"
                  class="form-control {{ $errors->has('nomeMae') ? 'is-invalid' : '' }}"
                  name="nomeMae"
                  value="{{ old('nomeMae') }}"
                  placeholder="Nome da mãe" 
                />
                @if($errors->has('nomeMae'))
                  <div class="invalid-feedback">
                    {{ $errors->first('nomeMae') }}
                  </div>
                @endif
              </div>
            </div>

            <div class="form-row mt-2">
              <div class="col-md-12">
                <label for="nomePai">Nome do Pai *</label>
                <input type="text"
                  class="form-control {{ $errors->has('nomePai') ? 'is-invalid' : '' }}"
                  name="nomePai"
                  value="{{ old('nomePai') }}"
                  placeholder="Nome do pai" 
                />
                @if($errors->has('nomePai'))
                  <div class="invalid-feedback">
                    {{ $errors->first('nomePai') }}
                  </div>
                @endif
              </div>
            </div>

            </br><h5>Informações de Contato</h5>
            <div class="form-row mt-2">
              <div class="col-md-4">
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

              <div class="col-md-4">
                <label for="celular">Celular *</label>
                <input type="text"
                  class="form-control celularInput {{ $errors->has('celular') ? 'is-invalid' : '' }}"
                  name="celular"
                  value="{{ old('celular') }}"
                  placeholder="Celular"
                />
                @if($errors->has('celular'))
                  <div class="invalid-feedback">
                    {{ $errors->first('celular') }}
                  </div>
                @endif
              </div>

              <div class="col-md-4">
                <label for="telefoneFixo">Telefone(fixo) *</label>
                <input type="text"
                  class="form-control telefoneInput {{ $errors->has('telefoneFixo') ? 'is-invalid' : '' }}"
                  name="telefoneFixo"
                  value="{{ old('telefoneFixo') }}"
                  placeholder="Telefone(fixo)"
                />
                @if($errors->has('telefoneFixo'))
                  <div class="invalid-feedback">
                    {{ $errors->first('telefoneFixo') }}
                  </div>
                @endif
              </div>
            </div>

            </br><h5>Informações de Endereço - Sede da Empresa</h5>
            <div class="form-row mb-2 ">
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
            <div class="form-group mb-2 ">
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
            <div class="form-row mb-2 ">
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
            <div class="form-row mb-2 ">
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


            </br><h5>Informações de Atividade</h5>
            <div class="form-row mt-2">
              <div class="col-md-4 mt-2-768">
                <label for="segmento">Segmento *</label>
                <select name="segmento" class="form-control {{ $errors->has('segmento') ? 'is-invalid' : '' }}">
                  <option value="">Selecione</option>
                  @foreach($listaSegmento as $segmento)
                    <option value="{{ $segmento }}" {{ old('segmento') === $segmento ? 'selected' : '' }}>{{ $segmento }}</option>
                  @endforeach
                </select>
                @if($errors->has('segmento'))
                <div class="invalid-feedback">
                  {{ $errors->first('segmento') }}
                </div>
                @endif
              </div>
            </div>

            <div class="form-row mt-2">
              <div class="col-md-4 mt-2-768">
                <label for="capitalSocial">Capital Social *</label>
                <select name="capitalSocial" class="form-control {{ $errors->has('capitalSocial') ? 'is-invalid' : '' }}">
                  <option value="">Selecione</option>
                  @foreach($listaCapitalSocial as $capitalSocial)
                    <option value="{{ $capitalSocial }}" {{ old('capitalSocial') === $capitalSocial ? 'selected' : '' }}>{{ $capitalSocial }}</option>
                  @endforeach
                </select>
                @if($errors->has('capitalSocial'))
                <div class="invalid-feedback">
                  {{ $errors->first('capitalSocial') }}
                </div>
                @endif
              </div>
            </div>

            </br><h5>Anexos</h5>

            <div class="mt-2">
              <label>CNPJ *</label>
            </div>
            <div class="custom-file">
              <input
                type="file"
                name="anexoCnpj"
                class="custom-file-input anexo {{ $errors->has('anexoCnpj') ? 'is-invalid' : '' }}"
                id="anexoCnpj"
                role="button"
              />
              <label class="custom-file-label" for="anexoCnpj">Selecionar arquivo...</label>
              @if($errors->has('anexoCnpj'))
                <div class="invalid-feedback">
                  {{ $errors->first('anexoCnpj') }}
                </div>
              @endif
            </div>

            @if($tipo === PreCadastro::TIPO_PRE_CADASTRO_PJ_INDIVIDUAL)
            <div class="mt-2">
              <label>Requerimento de Firma Individual *</label>
            </div>
            <div class="custom-file">
              <input
                type="file"
                name="anexoRequerimentoFirmaIndividual"
                class="custom-file-input anexo {{ $errors->has('anexoRequerimentoFirmaIndividual') ? 'is-invalid' : '' }}"
                id="anexoRequerimentoFirmaIndividual"
                role="button"
              />
              <label class="custom-file-label" for="anexoRequerimentoFirmaIndividual">Selecionar arquivo...</label>
              @if($errors->has('anexoRequerimentoFirmaIndividual'))
                <div class="invalid-feedback">
                  {{ $errors->first('anexoRequerimentoFirmaIndividual') }}
                </div>
              @endif
            </div>

            <div class="mt-2">
              <label>CPF *</label>
            </div>
            <div class="custom-file">
              <input
                type="file"
                name="anexoCpf"
                class="custom-file-input anexo {{ $errors->has('anexoCpf') ? 'is-invalid' : '' }}"
                id="anexoCpf"
                role="button"
              />
              <label class="custom-file-label" for="anexoCpf">Selecionar arquivo...</label>
              @if($errors->has('anexoCpf'))
                <div class="invalid-feedback">
                  {{ $errors->first('anexoCpf') }}
                </div>
              @endif
            </div>

            <div class="mt-2">
              <label>Documento de Identificação (RG ou CNH, de acordo com informações fornecidas acima) *</label>
            </div>
            <div class="custom-file">
              <input
                type="file"
                name="anexoDocumento"
                class="custom-file-input anexo {{ $errors->has('anexoDocumento') ? 'is-invalid' : '' }}"
                id="anexoDocumento"
                role="button"
              />
              <label class="custom-file-label" for="anexoDocumento">Selecionar arquivo...</label>
              @if($errors->has('anexoDocumento'))
                <div class="invalid-feedback">
                  {{ $errors->first('anexoDocumento') }}
                </div>
              @endif
            </div>

            <div class="mt-2">
              <label>Comprovante de Residência *</label>
            </div>
            <div class="custom-file">
              <input
                type="file"
                name="anexoComprovanteResidencia"
                class="custom-file-input anexo {{ $errors->has('anexoComprovanteResidencia') ? 'is-invalid' : '' }}"
                id="anexoComprovanteResidencia"
                role="button"
              />
              <label class="custom-file-label" for="anexoComprovanteResidencia">Selecionar arquivo...</label>
              @if($errors->has('anexoComprovanteResidencia'))
                <div class="invalid-feedback">
                  {{ $errors->first('anexoComprovanteResidencia') }}
                </div>
              @endif
            </div>

            @elseif($tipo === PreCadastro::TIPO_PRE_CADASTRO_PJ_VARIADO)
            <div class="mt-2">
              <label>Contrato Social *</label>
            </div>
            <div class="custom-file">
              <input
                type="file"
                name="anexoContratoSocial"
                class="custom-file-input anexo {{ $errors->has('anexoContratoSocial') ? 'is-invalid' : '' }}"
                id="anexoContratoSocial"
                role="button"
              />
              <label class="custom-file-label" for="anexoContratoSocial">Selecionar arquivo...</label>
              @if($errors->has('anexoContratoSocial'))
                <div class="invalid-feedback">
                  {{ $errors->first('anexoContratoSocial') }}
                </div>
              @endif
            </div>

            <div class="mt-2">
              <label>CPF (de todo o quadro societário) *</label>
            </div>
            <div class="custom-file">
              <input
                type="file"
                name="anexoCpfQuadro"
                class="custom-file-input anexo {{ $errors->has('anexoCpfQuadro') ? 'is-invalid' : '' }}"
                id="anexoCpfQuadro"
                role="button"
              />
              <label class="custom-file-label" for="anexoCpfQuadro">Selecionar arquivo...</label>
              @if($errors->has('anexoCpfQuadro'))
                <div class="invalid-feedback">
                  {{ $errors->first('anexoCpfQuadro') }}
                </div>
              @endif
            </div>

            <div class="mt-2">
              <label>Documento de Identificação (RG ou CNH de todo o quadro societário) *</label>
            </div>
            <div class="custom-file">
              <input
                type="file"
                name="anexoDocumentoQuadro"
                class="custom-file-input anexo {{ $errors->has('anexoDocumentoQuadro') ? 'is-invalid' : '' }}"
                id="anexoDocumentoQuadro"
                role="button"
              />
              <label class="custom-file-label" for="anexoDocumentoQuadro">Selecionar arquivo...</label>
              @if($errors->has('anexoDocumentoQuadro'))
                <div class="invalid-feedback">
                  {{ $errors->first('anexoDocumentoQuadro') }}
                </div>
              @endif
            </div>

            <div class="mt-2">
              <label>Comprovante de Residência (de todo o quadro societário) *</label>
            </div>
            <div class="custom-file">
              <input
                type="file"
                name="anexoComprovanteResidenciaQuadro"
                class="custom-file-input anexo {{ $errors->has('anexoComprovanteResidenciaQuadro') ? 'is-invalid' : '' }}"
                id="anexoComprovanteResidenciaQuadro"
                role="button"
              />
              <label class="custom-file-label" for="anexoComprovanteResidenciaQuadro">Selecionar arquivo...</label>
              @if($errors->has('anexoComprovanteResidenciaQuadro'))
                <div class="invalid-feedback">
                  {{ $errors->first('anexoComprovanteResidenciaQuadro') }}
                </div>
              @endif
            </div>

            @endif

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
