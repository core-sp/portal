@php
use \App\Http\Controllers\Helper;
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
Quantidade de vagas: {{ $curso->nrvagas }}
<hr>
Descrição: <br />
{!! $curso->descricao !!}
<hr>
<a href="/curso/inscricao/{{ $curso->idcurso }}">Inscrever-se</a>