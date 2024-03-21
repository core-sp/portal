@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    @if(Session::has('message'))
    <p class="alert {{ Session::get('class') }}">{!! Session::get('message') !!}</p>
    @endif
    
    <!-- CURSOS PRIVADOS COM INSCRIÇÕES ABERTAS -->
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1"><i class="fas fa-graduation-cap"></i> Cursos com vagas abertas</h4>
        <div class="linha-lg-mini mb-1"></div>
    @if(isset($cursos) && $cursos->isNotEmpty())
        <div class="row mb-3">
        @foreach($cursos as $curso)
            @if($curso->podeInscreverExterno())
            <div class="col-lg-4 col-sm-6 mb-2">
                <div class="h-100 d-flex flex-column">
                    <a href="{{ route('cursos.show', $curso->idcurso) }}">
                        <div class="curso-grid">
                            <img src="{{ asset(imgToThumb($curso->img)) }}" class="bn-img" />
                            <div class="curso-grid-txt">
                                <h6 class="light cinza-claro">{{ $curso->regional->regional }} - {{ onlyDate($curso->datarealizacao) }}</h6>
                                <h5 class="branco mt-1">{{ $curso->tipo }} - {{ $curso->tema }}</h5>
                            </div>
                        </div>
                    </a>
                    @if($curso->representanteInscrito(auth()->guard('representante')->user()->cpf_cnpj))
                    <span class="{{ $curso::TEXTO_BTN_INSCRITO }}">Inscrição realizada</span>
                    @else
                    <a href="{{ route('cursos.inscricao.website', $curso->idcurso) }}" class="btn btn-sm btn-primary text-white mt-2">Inscrever-se</a>
                    @endif
            @endif
                </div>
            </div>
        @endforeach
        </div>
    @else
        <div class="contatos-table space-single">
            <p class="light pb-0">No momento não há cursos restritos com vagas abertas para o representante.</p>
        </div>
    @endif

        <div class="text-right">
        @if(isset($cursos))
            {{ $cursos->links() }}
        @endif
        </div>
    </div>

    <!-- CERTIFICADOS -->
    <div class="conteudo-txt-mini light mt-5">
        <h4 class="pt-1 pb-1"><i class="fas fa-award"></i> Certificados</h4>
        <div class="linha-lg-mini mb-1"></div>
    @if(isset($certificados) && $certificados->isNotEmpty())
        <div class="row mb-3">
        @foreach($certificados as $certificado)
            <div class="col-lg-4 col-sm-6 mb-2">
                <div class="h-100 d-flex flex-column">
                    <a href="{{ route('cursos.show', $certificado->idcurso) }}">
                        <div class="curso-grid">
                            <img src="{{ asset(imgToThumb($certificado->img)) }}" class="bn-img" />
                            <div class="curso-grid-txt">
                                <h6 class="light cinza-claro">{{ onlyDate($certificado->datarealizacao) }}</h6>
                                <h5 class="branco mt-1">{{ $certificado->tipo }} - {{ $certificado->tema }}</h5>
                            </div>
                        </div>
                    </a>
                    <a href="{{-- route('cursos.inscricao.website', $curso->idcurso) --}}" class="btn btn-sm btn-primary text-white mt-2"><i class="fas fa-award"></i> Certificado</a>
                </div>
            </div>
        @endforeach
        </div>
    @else
        <div class="contatos-table space-single">
            <p class="light pb-0">No momento não há inscrições em cursos anteriores para gerar certificados para download.</p>
        </div>
    @endif

        <div class="text-right">
        @if(isset($certificados))
            {{ $certificados->links() }}
        @endif
        </div>
    </div>

</div>

@endsection