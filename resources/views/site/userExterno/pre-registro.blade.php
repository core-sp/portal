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
        <h4 class="pt-1 pb-1">Solicitação de Registro</h4>
        <div class="linha-lg-mini mb-2"></div>
            <div class="list-group w-100">
                <div class="d-block mt-2 mb-3">
                    <p>Algum texto explicando sobre o formulário, e onde pode visualizar a atual situação da solicitação</p>
                    <br>
                    @if(isset($gerenti))
                    <p>Você já possui registro ativo no Core-SP: <strong>{{ formataRegistro($gerenti) }}</strong></p>
                    @else
                    <form action="{{ route('externo.inserir.preregistro.view') }}" autocomplete="off">
                        <div class="form-check mt-3">
                            <input type="checkbox"
                                name="checkPreRegistro"
                                class="form-check-input"
                                required
                            /> 
                            <label for="termo" class="text-justify">
                                Estou ciente que iniciarei o processo de solicitação de registro para ser <strong>REPRESENTANTE COMERCIAL</strong>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-link {{ isset($resultado->id) ? 'btn-secondary' : 'btn-success' }} link-nostyle branco mt-3">
                            {{ isset($resultado->id) ? 'Continuar' : 'Iniciar' }} a solicitação do registro
                        </button>
                    </form>
                    @endif

                </div>      
            </div>
    </div>
</div>

@endsection