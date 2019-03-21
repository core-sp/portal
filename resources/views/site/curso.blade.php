@php
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\CursoInscritoController;
use \App\Http\Controllers\Helpers\CursoHelper;
@endphp

Tipo: {{ $curso->tipo }}
<hr>
Nº do processo: {{ $curso->tema }}
<hr>
Data do evento: {{ Helper::formataData($curso->datarealizacao) }}
<hr>
Duração: {{ $curso->duracao }} horas
<hr>
Endereço: {{ $curso->endereco }}
<hr>
Quantidade de vagas: {{ CursoHelper::contagem($curso->idcurso) }} / {{ $curso->nrvagas }}
<hr>
Descrição: <br />
{!! $curso->descricao !!}
<hr>
@if(CursoInscritoController::permiteInscricao($curso->idcurso))
  <a href="/curso/inscricao/{{ $curso->idcurso }}">Inscrever-se</a>
@else
  Inscrições esgotadas
@endif