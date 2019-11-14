<tr>
    <td class="ls-meio-neg">
        {{ $cobranca['DESCRICAO'] }} ⋅ {!! secondLine($cobranca['SITUACAO'], $cobranca['VENCIMENTOBOLETO'], $cobranca['LINK']) !!}
    </td>
    <td class="ls-meio-neg">R$ {{ toReais($cobranca['VALOR']) }}</td>
</tr>