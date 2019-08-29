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

function formataData($data)
{
    $date = new \DateTime($data);
    $format = $date->format('d\/m\/Y, \à\s H:i');
    return $format;
}

function formataImageUrl($urlBruta)
{
    $lastSlash = strrpos($urlBruta, '/') + 1;
    $imageName = substr($urlBruta, $lastSlash);
    $urlName = substr($urlBruta, 0, $lastSlash);
    return $urlName . rawurlencode($imageName);
}