@extends('site.layout.app', ['title' => 'Agendamento'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative">
    <img src="{{ asset('img/banner-interno-agendamento.png') }}" />
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
          <p class="pb-0">Agende seu atendimento presencial no Core-SP, com até um mês de antecedência.<br />Ou então, consulte as <a href="/agendamento-consulta" class="text-primary">informações do atendimento já agendado.</a></p></br>
          <p><strong>Atenção:</strong> Orientação jurídica – dúvidas deverão ser enviadas para o e-mail juridico@core-sp.org.br, que serão respondidas em até 5 (cinco) dias úteis, conforme PORTARIA 27/2020. Não há atendimento jurídico presencial, excepcionalmente, nesse período de pandemia.</p>
          <p>O parcelamento de anuidades em Execução Fiscal só será realizado pelo atendimento presencialmente, se houver o contato via e-mail com o setor de Dívida Ativa (<a href="mailto:juridico.dividaativa@core-sp.org.br">juridico.dividaativa@core-sp.org.br</a>) previamente para solicitação dos valores de custas processuais e honorários advocatícios, caso haja.</p>
          
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
              <div class="col-md-6 mt-2-768">
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
            </div>
            <h5 class="mt-4">Informações de agendamento</h5>
            <div class="form-row mt-2">
              <div class="col-md-4">
                <label for="idregional">Regional *</label>
                <select name="idregional" id="idregional" class="form-control {{ $errors->has('idregional') ? 'is-invalid' : '' }}">
                  <option value="" selected>Selecione a regional</option>
                  @foreach($regionais as $regional)
                    <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                  @endforeach 
                </select>
                @if($errors->has('idregional'))
                <div class="invalid-feedback">
                  {{ $errors->first('idregional') }}
                </div>
                @endif
              </div>
              <div class="col-md-4 mt-2-768">
                <label for="dia">Dia * <span>(datas em vermelho não possuem horário disponível)</span></label>
                <div class="input-group">
                  <input type="text" 
                    class="form-control {{ $errors->has('dia') ? 'is-invalid' : '' }}"
                    id="datepicker"
                    name="dia"
                    placeholder="Selecione a regional"
                    autocomplete="off"
                    readonly
                    disabled
                    />
                  @if($errors->has('dia'))
                  <div class="invalid-feedback">
                    {{ $errors->first('dia') }}
                  </div>
                  @endif
                </div>
                <div id="loadCalendario" class="loadImage">
                  <img src="{{ asset('img/ajax-loader.gif') }}" alt="Loading">
                </div>
              </div>
              <div class="col-md-4 mt-2-768">
                <label for="hora">Horários disponíveis *</label>
                <select name="hora" id="horarios" disabled class="form-control {{ $errors->has('hora') ? 'is-invalid' : '' }}">
                  <option value="" selected>Selecione o dia do atendimento</option>
                </select>
                @if($errors->has('hora'))
                <div class="invalid-feedback">
                  {{ $errors->first('hora') }}
                </div>
                @endif
                <div id="loadHorario" class="loadImage">
                  <img src="{{ asset('img/ajax-loader.gif') }}" alt="Loading">
                </div>
              </div>
            </div>
            <div class="form-row mt-2">
              <div class="col-md-6">
                <label for="servico">Tipo de Serviço *</label>
                <select name="servico" class="form-control" id="selectServicos">
                  @foreach($servicos as $servico)
                    @if($servico == old('servico'))
                    <option value="{{ $servico }}" selected>{{ $servico }}</option>
                    @else
                    <option value="{{ $servico }}">{{ $servico }}</option>
                    @endif
                  @endforeach 
                </select>
                @if($errors->has('servico'))
                <div class="invalid-feedback">
                  {{ $errors->first('servico') }}
                </div>
                @endif
              </div>
              <div class="col-md-6 mt-2-768">
                <label for="pessoa">Para:</label>
                <select name="pessoa" class="form-control">
                  @foreach($pessoas as $pessoa => $diminutivo)
                    @if(old('pessoa') == $diminutivo)
                    <option value="{{ $diminutivo }}" selected>{{ $pessoa }}</option>
                    @else
                    <option value="{{ $diminutivo }}">{{ $pessoa }}</option>
                    @endif
                  @endforeach 
                </select>
                @if($errors->has('pessoa'))
                <div class="invalid-feedback">
                  {{ $errors->first('pessoa') }}
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
