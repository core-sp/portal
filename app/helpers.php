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

function retornaDescription($string)
{
    return substr(html_entity_decode(strip_tags($string)), 0, 150) . '...';
}

function segmentos()
{
    $segmentos = [
        'Abrasivos',
        'Aeronáutica',
        'Agropecuária',
        'Alarme e Monitoramento',
        'Alimentício',
        'Aquarismo',
        'Artigos de festa',
        'Atacadista',
        'Audiovisual',
        'Auto Peças',
        'Automação Industrial',
        'Automobilística',
        'Bebidas e Congêneres',
        'Bens de Capital',
        'Bobinas PVD',
        'Bombas e Válvulas',
        'Bombas Submersas',
        'Brindes',
        'Brinquedos',
        'Calçados',
        'Colchões',
        'Combustíveis',
        'Comércio Exterior',
        'Compras Coletivas',
        'Compressores',
        'Comunicação Visual',
        'Consórcios',
        'Construção Civil',
        'Cosméticos',
        'Couro',
        'Decoração',
        'Descartáveis',
        'Educação/Cultura/Lazer',
        'Eletro Domésticos',
        'Eletro Eletrônicos',
        'Eletrônicos',
        'Embalagens',
        'Energia',
        'Engenharia Elétrica',
        'Equip. para Posto de Combustível',
        'Equipamento de Segurança',
        'Equipamento Industrial',
        'Equipamentos Agrícolas',
        'Equip. de Energia Solar',
        'Esporte e Lazer',
        'Exportação/Importação',
        'Farmacêutica',
        'Ferragens',
        'Ferramenta de Corte',
        'Ferramentas em Geral',
        'Fertilizantes',
        'Filmes',
        'Gêneros Alimentícios',
        'GLP',
        'GPS',
        'Gráficos',
        'Higiene',
        'Hospitalar',
        'Iluminação',
        'Indústria Naval',
        'Industrial',
        'Informática/Telecom.',
        'Instrumentos Musicais',
        'Isolamento Térmico',
        'Jóias e Acessórios',
        'Jornais e Revistas',
        'Laboratorial',
        'Langerie',
        'Limpeza e Conservação',
        'Lubrificantes',
        'Madeira',
        'Máquinas e Equip. Industriais',
        'Máquinas e Equipamentos',
        'Máquinas/Ferramentas',
        'Matéria Prima',
        'Materiais Elétricos',
        'Materiais Hidráulicos',
        'Mecânica Industrial',
        'Medicamentos',
        'Médico/Hospitalar',
        'Meio Ambiente (análise/coleta)',
        'Metais',
        'Metalurgia/Mecânica',
        'Mobiliário/Móveis',
        'Moto Peças',
        'Motos',
        'Nutrição Animal',
        'Odontológicos',
        'Óticos',
        'Ortodônticos',
        'Papel e Celulose',
        'Papelaria/Livraria/Revistas',
        'Passagens de Viagens',
        'Peças e Acess. Automotivos (Motos)',
        'Peças p/ Máquinas Agrícolas',
        'Pecuária',
        'Pedreiras',
        'Perfumaria',
        'Pet/Animais de Estimação',
        'Plástico em Geral',
        'Plásticos/Borrachas',
        'Produtos Agrícolas',
        'Produtos Frigoríficos',
        'Produtos Laboratoriais',
        'Produtos Sustentáveis',
        'Publicidade e Propaganda',
        'Químico',
        'Químico/Farmacêutica',
        'Reciclagem',
        'Refrigeração',
        'Rolamentos',
        'Saúde',
        'Segurança Patrimonial',
        'Segurança',
        'Segurança Industrial',
        'Sensores Óticos',
        'Serviço de Proteção ao Consumidor',
        'Siderúrgica',
        'Suplemento Alimentar',
        'Tabacaria',
        'Telecomunicações',
        'Telefonia',
        'Têxtil/Vestuária/Acessórios',
        'Tintas',
        'Toldos',
        'Transportes',
        'Utilidades domésticas',
        'Válvula',
        'Vestuário',
        'Veterinário',
        'Vidros',
    ];
    sort($segmentos);
    return $segmentos;
}

function capitais()
{
    $capitais = [
        'Até R$ 10.000,00',
        'Até R$ 50.000,00',
        'Até R$ 100.000,00',
        'Até R$ 300.000,00',
        'Até R$ 500.000,00',
        'Maior que R$ 500.000,00'
    ];
    return $capitais;
}

function regioes()
{
    return [
        1 => 'São Paulo',
        2 => 'Campinas',
        3 => 'Bauru',
        4 => 'Ribeirão Preto',
        5 => 'São José dos Campos',
        6 => 'São José do Rio Preto',
        7 => 'Presidente Prudente',
        8 => 'Araraquara',
        9 => 'Sorocaba',
        10 => 'Santos',
        11 => 'Araçatuba',
        12 => 'Rio Claro',
        13 => 'Marília'
    ];
}