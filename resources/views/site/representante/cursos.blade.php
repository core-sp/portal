@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    @if(Session::has('message') || $errors->has('inscrito'))
    <p class="alert {{ Session::has('message') ? Session::get('class') : 'alert-danger' }}">{!! Session::has('message') ? Session::get('message') : $errors->first('inscrito') !!}</p>
    <hr />
    @endif
    
    <h4 class="pt-1 pb-1">Cursos</h4>
    <div class="linha-lg-mini mb-2"></div>

    <!-- TABS / PILLS -->
    <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link {{ isset(request()->query()['cursosPage']) || empty(request()->query()) ? 'active' : '' }}" data-toggle="pill" href="#cursos-abertos"><i class="fas fa-graduation-cap"></i> Cursos privados com vagas abertas</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ isset(request()->query()['certificadosPage']) ? 'active' : '' }}" data-toggle="pill" href="#certificados"><i class="fas fa-award"></i> Certificados</a>
        </li>
    </ul>

    <!-- CONTEÚDO -->
    <div class="tab-content">
        <div class="linha-lg-mini mb-2"></div>

        <!-- CURSOS PRIVADOS COM INSCRIÇÕES ABERTAS -->
        <div class="tab-pane container {{ isset(request()->query()['cursosPage']) || empty(request()->query()) ? 'active' : 'fade' }}" id="cursos-abertos">
        
            @if(isset($cursos) && $cursos->isNotEmpty())
            <div class="row mt-3">
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
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
            @else
            <div class="contatos-table space-single">
                <p class="light pb-0">No momento não há cursos restritos com vagas abertas para o representante.</p>
            </div>
            @endif

            <div class="text-right mt-2">
            @if(isset($cursos))
                {{ $cursos->links() }}
            @endif
            </div>

        </div>

        <!-- CERTIFICADOS -->
        <div class="container tab-pane {{ isset(request()->query()['certificadosPage']) ? 'active' : 'fade' }}" id="certificados">

            @if(isset($certificados) && $certificados->isNotEmpty())
            <div class="row mt-3">
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
                        <form method="POST" action="{{ route('cursos.certificado', $certificado->idcurso) }}">
                            @csrf
                            <input type="hidden" name="idcursoinscrito" value="{{ $certificado->cursoInscrito->first()->idcursoinscrito }}" />
                            <button type="submit" class="btn btn-sm btn-primary btn-block text-white mt-2"><i class="fas fa-award"></i> Certificado</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="contatos-table space-single">
                <p class="light pb-0">No momento não há inscrições em cursos anteriores para gerar certificados para download.</p>
            </div>
            @endif

            <div class="text-right mt-2">
            @if(isset($certificados))
                {{ $certificados->links() }}
            @endif
            </div>
        </div>
    </div>

</div>

@endsection