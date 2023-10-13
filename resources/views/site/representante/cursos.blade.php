@extends('site.representante.app')

@section('content-representante')

@php
use \App\Http\Controllers\CursoInscritoController;
@endphp

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Cursos com vagas abertas</h4>
        <div class="linha-lg-mini mb-1"></div>
    @if(isset($cursos) && $cursos->isNotEmpty())
        <div class="row mb-3">
        @foreach($cursos as $curso)
            @if(CursoInscritoController::permiteInscricao($curso->idcurso))
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
                    @if($curso->cursoinscrito()->where('cpf', auth()->guard('representante')->user()->cpf_cnpj)->exists())
                    <span class="btn btn-sm btn-secondary text-center mt-2 disabled">Inscrição realizada</span>
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
</div>

@endsection