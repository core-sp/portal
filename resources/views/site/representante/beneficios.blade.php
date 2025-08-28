@extends('site.representante.app')

@section('content-representante')

@if(Session::has('message'))
<div class="d-block w-100">
    <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
</div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Benefícios</h4>
        <div class="linha-lg-mini mb-1"></div>

        <p>
            <em>Gerencie as inscrições dos <a href="{{ route('paginas.site', 'beneficios') }}" target="_blank">benefícios</a>.</em>
        </p>

        <form class="mt-3" action="{{ route('representante.beneficios.acao') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="termo" class="text-justify">
                    Li e concordo com o <a href="{{ route('termo.consentimento.pdf') }}" target="_blank" id="termo"><u>Termo de Consentimento</u></a> de uso de dados, e estou ciente de que os meus dados serão utilizados apenas para notificações por e-mail a respeito da inscrição no(s) benefício(s) abaixo.
                </label>
            </div>

            @foreach($beneficios as $beneficio)
            <div class="form-check-inline mb-2">
                <label for="bene-{{ $loop->index }}" class="form-check-label">
                    <input 
                        type="checkbox" 
                        id="bene-{{ $loop->index }}"
                        class="form-check-input {{ $errors->has('inscricoes') || $errors->has('inscricoes.*') ? 'is-invalid' : '' }}" 
                        name="inscricoes[]" 
                        value="{{ $beneficio }}" 
                        {{ $inscricoes->contains($beneficio) ? 'checked' : '' }}
                    />{{ $beneficio }}
                    
                    @if($errors->has('inscricoes') || $errors->has('inscricoes.*'))
                    <div class="invalid-feedback">
                        {{ $errors->has('inscricoes') ? $errors->first('inscricoes') : $errors->first('inscricoes.*') }}
                    </div>
                    @endif
                </label>
            </div>
            @endforeach

            <div class="linha-lg-mini mb-1"></div>

            <div class="form-group">
                <div class="float-left mt-2">
                    <button type="submit" class="btn btn-primary loadingPagina">Salvar</button>
                </div>
            </div>

        </form>
    </div>
</div>

<script type="module" src="{{ asset('/js/restrita-rc/modulos/beneficios.js?'.hashScriptJs()) }}" data-modulo-id="beneficios" data-modulo-acao="editar"></script>

@endsection
