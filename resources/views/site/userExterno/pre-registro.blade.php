@extends('site.userExterno.app')

@section('content-user-externo')

@if(Session::has('message'))
<div class="d-block w-100 alert alert-dismissible {{ Session::get('class') }}">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {!! Session::get('message') !!}
</div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Solicitação de registro para ser <strong>Representante Comercial</strong></h4>
        <div class="linha-lg-mini mb-2"></div>
            <div class="list-group w-100">

                <!-- Para todas as autenticações ********************************************************************************** -->
                <div class="d-block mt-2 mb-3">
                    <p>
                        Esse formulário é para realizar a solicitação de registro para ser <strong>REPRESENTANTE COMERCIAL</strong>. 
                        Durante o preenchimento será salvo automaticamente os dados. Os anexos serão excluídos automaticamente após ser encerrada pelo atendente a solicitação ou após muito tempo inativo. 
                        E somente o solicitante pode excluir um anexo enquanto a solicitação não é finalizada.
                    </p>
                    <br>
                    @if(isset($gerenti))
                    <p>Você já possui registro ativo no Core-SP: <strong>{{ formataRegistro($gerenti) }}</strong></p>
                    @elseif(auth()->guard('user_externo')->check())
                    <hr />
                        @foreach(auth()->guard('user_externo')->user()->load('preRegistros')->preRegistros as $preRegistro)
                            @if(in_array($preRegistro->status, [$preRegistro::STATUS_NEGADO, $preRegistro::STATUS_APROVADO]))
                                <p><strong>ID da solicitação:</strong> {{ $preRegistro->id }}</p>
                                <p><strong>Solicitado em:</strong> {{ onlyDate($preRegistro->created_at) }} <strong>e encerrado em:</strong> {{ onlyDate($preRegistro->updated_at) }}</p>
                                <p>
                                    <strong>Status:</strong> <span class="badge badge{{ $preRegistro->getLabelStatus($preRegistro->status) }}">{{ $preRegistro->status }}</span>
                                    {{ $preRegistro->status == $preRegistro::STATUS_NEGADO ? '- ' . $preRegistro->getJustificativaNegado() : '' }}
                                </p>
                                <hr />
                            @endif
                        @endforeach

                    @endif

                    @if(isset($resultado->id))
                        <h5>ID da solicitação: {{ $resultado->id }}</h5>
                        <h5>Solicitado em: {{ onlyDate($resultado->created_at) }}</h5>
                        <h4>Status: {!! $resultado->getLabelStatusUser() !!}</h4>
                        @if($resultado->status == $resultado::STATUS_NEGADO)
                        <h5><span class="text-danger">Justificativa: </span>{{ $resultado->getJustificativaNegado() }}</h5>
                        @endif
                    @endif
                    <!-- ***************************************************************************************************************** -->

                    <!-- Para autenticação user_externo ********************************************************************************** -->
                    @if(!isset($gerenti) && auth()->guard('user_externo')->check() && !auth()->guard('user_externo')->user()->preRegistroAprovado())
                        <form action="{{ route('externo.inserir.preregistro.view') }}" autocomplete="off">
                            <div class="form-check mt-3">
                                <input type="checkbox"
                                    id="checkPreRegistro"
                                    name="checkPreRegistro"
                                    class="form-check-input"
                                    required
                                    {{ isset($resultado->status) && ($resultado->status != $resultado::STATUS_CRIADO) ? 'checked' : '' }}
                                />
                                <label for="checkPreRegistro" class="text-justify font-weight-light">
                                    Estou ciente que iniciarei o processo de solicitação de registro para ser <strong>REPRESENTANTE COMERCIAL</strong>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-link {{ isset($resultado) ? 'btn-secondary' : 'btn-success' }} link-nostyle branco mt-3">
                                @if(!isset($resultado->id))
                                    Iniciar a solicitação do registro
                                @elseif($resultado->status == $resultado::STATUS_CRIADO)
                                    Continuar a solicitação do registro
                                @else
                                    {{ $resultado->userPodeCorrigir() ? 'Corrigir' : 'Visualizar' }} a solicitação do registro
                                @endif
                            </button>
                        </form>
                    @endif
                    <!-- ***************************************************************************************************************** -->

                    <!-- Para autenticação contabil ********************************************************************************** -->
                    @if(!isset($gerenti) && auth()->guard('contabil')->check())
                        @if(!isset($resultado))
                        <p>
                            Após a solicitação ser criada, o representante com o respectivo CPF / CNPJ 
                            terá uma conta previamente criada no Login Externo com esses dados abaixo e será informado pelo e-mail inserido para realizar a confirmação do cadastro 
                            através do link: <a href="{{ route('externo.cadastro') }}">{{ route('externo.cadastro') }}</a>.
                            <br>
                            A não confirmação do cadastro não impede a solicitação de ser realizada.
                        </p>

                        <form action="{{ route('externo.contabil.inserir.preregistro') }}" method="POST" autocomplete="off" class="cadastroRepresentante">
                            @csrf
                            <div class="form-row mt-3">
                                <div class="col-sm-4 mb-2-576">
                                    <label>CPF / CNPJ <span class="text-danger">*</span></label>
                                    <input 
                                        type="text"
                                        name="cpf_cnpj"
                                        class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj') ? 'is-invalid' : '' }}"
                                        value="{{ old('cpf_cnpj') }}"
                                        placeholder="CPF ou CNPJ"
                                        required
                                    />
                                    @if($errors->has('cpf_cnpj'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('cpf_cnpj') }}
                                    </div>
                                    @endif
                                </div>
                                <div class="col-sm mb-2-576">
                                    <label>E-mail <span class="text-danger">*</span></label>
                                    <input 
                                        type="email"
                                        name="email"
                                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                        value="{{ old('email') }}"
                                        placeholder="E-mail"
                                        maxlength="191"
                                        minlength="10"
                                        required
                                    />
                                    @if($errors->has('email'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('email') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-row mt-2">
                                <div class="col-sm mb-2-576">
                                    <label>Nome <span class="text-danger">*</span></label>
                                    <input 
                                        type="text"
                                        name="nome"
                                        class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }} upperCase"
                                        value="{{ old('nome') }}"
                                        placeholder="Nome Completo"
                                        maxlength="191"
                                        minlength="5"
                                        required
                                    />
                                    @if($errors->has('nome'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('nome') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <button type="submit" class="btn btn-link btn-success link-nostyle branco mt-3">
                                Iniciar a solicitação do registro
                            </button>
                        </form>
                        @elseif(!$resultado->isFinalizado())
                        <a 
                            class="btn btn-link btn-secondary link-nostyle branco mt-3"
                            href="{{ route('externo.inserir.preregistro.view', $resultado->id) }}"
                        >
                            @if($resultado->status == $resultado::STATUS_CRIADO)
                                Continuar a solicitação do registro
                            @elseif($resultado->userPodeCorrigir())
                                Corrigir a solicitação do registro
                            @else
                                Visualizar a solicitação do registro
                            @endif
                        </a>
                        @endif
                    @endif
                    <!-- ***************************************************************************************************************** -->

                </div>      
            </div>
    </div>
</div>

@endsection