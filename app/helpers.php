<?php

function badgeConsulta($situacao)
{
    switch ($situacao) {
        case 'Ativo':
            return '<span class="badge badge-success">'.$situacao.'</span>';
        break;
        
        case 'Cancelado':
            return '<span class="badge badge-danger">'.$situacao.'</span>';
        break;

        default:
            return '<span class="badge badge-secondary">'.$situacao.'</span>';
        break;
    }
}