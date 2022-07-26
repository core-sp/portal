<?php

use App\Permissao;
use App\Representante;

function montaTabela($headers, $contents, $classes = null)
{
    if(isset($classes)) {
        $classes = implode(' ', $classes);
        $table = "<table class='".$classes."' />";
    } else {
        $table = "<table>";
    }
    $table .= "<thead>";
    $table .= "<tr>";
    foreach($headers as $header) {
        $table .= "<th>";
        $table .= $header;
        $table .= "</th>";
    }
    $table .= "</tr>";
    $table .= "</thead>";
    $table .= "<tbody>";
    foreach($contents as $content) {
        $table .= isset($content['corDaLinha']) ? $content['corDaLinha'] : '<tr>';
        unset($content['corDaLinha']);
        foreach($content as $single) {
            if($single === end($content))
                $table .= "<td class='nowrap'>";
            else
                $table .= "<td>";
            $table .= $single;
            $table .= "</td>";
        }
        $table .= "</tr>";
    }
    $table .= "</tbody>";
    $table .= "</table>";

    return $table;
}

function getFiltroOptions($value, $texto, $selected = false)
{
    $opcao = $selected ? 'selected' : '';
    return '<option value="'.$value.'" '.$opcao.'>'.$texto.'</option>';
}

function getFiltroCamposSelect($label, $atributoNameId, $options)
{
    $filtro = '<div class="form-group mb-0 col">';
    $filtro .= '<label>'.$label.'</label>';
    $filtro .= '<select class="custom-select custom-select-sm" id="'.$atributoNameId.'" name="'.$atributoNameId.'">';
    $filtro .= $options;
    $filtro .= '</select>';
    $filtro .= '</div>';

    return $filtro;
}

function getFiltroCamposDate($mindia, $maxdia)
{
    $filtro = '<div class="form-group mb-0 col">';
    $filtro .= '<label>De</label>';
    $textoData = '<input type="date" class="form-control d-inline-block form-control-sm" name="datemin" id="datemin" value="';
    $filtro .= isset($mindia) ? $textoData.$mindia.'" />' : $textoData.date('Y-m-d').'" />';
    $filtro .= '</div>';

    $filtro .= '<div class="form-group mb-0 col">';
    $filtro .= '<label>Até</label>';
    $textoData = '<input type="date" class="form-control d-inline-block form-control-sm" name="datemax" id="datemax" value="';
    $filtro .= isset($maxdia) ? $textoData.$maxdia.'" />' : $textoData.date('Y-m-d').'" />';
    $filtro .= '</div>';

    return $filtro;
}

function getFiltro($action, $filtrosCampos, $idform = 'filtroDate')
{
    $filtro = '<form method="GET" action="'.$action.'" id="'.$idform.'" class="mb-0">';
    $filtro .= '<div class="form-row filtroAge">';

    $filtro .= $filtrosCampos;

    $filtro .= '<div class="form-group mb-0 col-auto align-self-end">';
    $filtro .= '<input type="submit" class="btn btn-sm btn-default" value="Filtrar" />';
    $filtro .= '</div>';
    $filtro .= '</div>';
    $filtro .= '</form>';

    return $filtro;
}

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
        'Outro'
    ];
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

function emailResetRepresentante($token)
{
    $body = 'Você está recebendo este email pois solicitou alteração de senha no Portal Core-SP.';
    $body .= '<br>';
    $body .= 'Lembrando que este link é válido por 60 minutos.';
    $body .= '<br>';
    $body .= 'Clique no link abaixo para continuar o procedimento.';
    $body .= '<br><br>';
    $body .= '<a href="'. route('representante.password.reset', $token) .'">Alterar senha</a>';
    $body .= '<br><br>';
    $body .= 'Caso não tenha solicitado, favor desconsiderar este email.';
    $body .= '<br><br>';
    $body .= 'Atenciosamente,';
    $body .= '<br>';
    $body .= 'Portal Core-SP';

    return $body;
}

function formataEnderecoGerenti($logradouro, $num = '', $comp = '')
{
    $end = $logradouro;
    !empty($num) ? $end .= ', ' . $num : $end .= '';
    !empty($comp) ? $end .= ' - ' . $comp : $end .= '';

    return $end;
}

function estados()
{
    return [
        'AC'=>'Acre',
        'AL'=>'Alagoas',
        'AP'=>'Amapá',
        'AM'=>'Amazonas',
        'BA'=>'Bahia',
        'CE'=>'Ceará',
        'DF'=>'Distrito Federal',
        'ES'=>'Espírito Santo',
        'GO'=>'Goiás',
        'MA'=>'Maranhão',
        'MT'=>'Mato Grosso',
        'MS'=>'Mato Grosso do Sul',
        'MG'=>'Minas Gerais',
        'PA'=>'Pará',
        'PB'=>'Paraíba',
        'PR'=>'Paraná',
        'PE'=>'Pernambuco',
        'PI'=>'Piauí',
        'RJ'=>'Rio de Janeiro',
        'RN'=>'Rio Grande do Norte',
        'RS'=>'Rio Grande do Sul',
        'RO'=>'Rondônia',
        'RR'=>'Roraima',
        'SC'=>'Santa Catarina',
        'SP'=>'São Paulo',
        'SE'=>'Sergipe',
        'TO'=>'Tocantins'
    ];
}

function gerentiTiposContatos()
{
    return [
        '1' => 'Telefone',
        '2' => 'Celular',
        '3' => 'E-mail',
        '4' => 'Fax',
        '5' => 'Home Page',
        '6' => 'Tel. Emergência',
        '7' => 'Tel. Contato',
        '8' => 'Tel. Referência',
        '51' => 'SMS',
        '52' => 'WhatsApp',
        '53' => 'Telegram'
    ];
}

function gerentiTiposContatosInserir()
{
    return [
        '1' => 'Telefone',
        '2' => 'Celular',
        '3' => 'E-mail',
        '4' => 'Fax',
        '5' => 'Home Page',
        '6' => 'Tel. Emergência',
        '7' => 'Tel. Contato',
        '8' => 'Tel. Referência'
    ];
}

function nacionalidades()
{
    return [
        'Afegã',
        'Alemã',
        'Americana',
        'Angolana',
        'Antiguana',
        'Árabe',
        'Argelina',
        'Argentina',
        'Armena',
        'Australiana',
        'Austríaca',
        'Bahamense',
        'Bengali',
        'Barbadiana',
        'Belga',
        'Belizense',
        'Boliviana',
        'Brasileira',
        'Britânica',
        'Camaronense',
        'Canadense',
        'Chilena',
        'Chinesa',
        'Cingalesa',
        'Colombiana',
        'Comorense',
        'Costarriquenha',
        'Croata',
        'Cubana',
        'Dinamarquesa',
        'Dominicana',
        'Egípcia',
        'Equatoriana',
        'Escocesa',
        'Eslovaca',
        'Eslovena',
        'Espanhola',
        'Francesa',
        'Galesa',
        'Ganesa',
        'Granadina',
        'Grega',
        'Guatemalteca',
        'Guianense',
        'Haitiana',
        'Holandesa',
        'Hondurenha',
        'Húngara',
        'Iemenita',
        'Indiana',
        'Indonésia',
        'Inglesa',
        'Iraniana',
        'Iraquiana',
        'Irlandesa',
        'Israelita',
        'Italiana',
        'Jamaicana',
        'Japonesa',
        'Leste-Timorense',
        'Líbico',
        'Malaia',
        'Marfinense',
        'Marroquina',
        'Mexicana',
        'Moçambicana',
        'Neozelandesa',
        'Nepalês',
        'Nicaraguense',
        'Nigeriana',
        'Norte-coreana',
        'Norueguesa',
        'Omanense',
        'Palestiniano',
        'Panamense',
        'Paquistanesa',
        'Paraguaia',
        'Peruana',
        'Polonesa',
        'Portorriquenha',
        'Portuguesa',
        'Qatarense',
        'Queniana',
        'Romena',
        'Ruandês',
        'Russa',
        'Salvadorense',
        'Santa lucense',
        'São-cristovense',
        'São-vicentino',
        'Saudita',
        'Sérvio',
        'Síria',
        'Somali',
        'Sueca',
        'Suíça',
        'Sul-africana',
        'Sul-coreana',
        'Surinamês',
        'Tailandesa',
        'Trindadense',
        'Turca',
        'Ucraniana',
        'Ugandense',
        'Uruguaia',
        'Venezuelana',
        'Vietnamita',
        'Zimbabuense',  
    ]; 
}

function estados_civis()
{
    return [
        'Casado(a)', 
        'Divorciado(a)',
        'Separado(a)',
        'Solteiro(a)', 
        'União estável',
        'Viúvo(a)',
    ];
}

function tipos_empresa()
{
    return [
        'EMPRESÁRIO INDIVIDUAL',
        'FILIAL',
        'LTDA',
        'S.A.',
        'UNIPESSOAL',
    ];
}

function tipos_contatos()
{
    return [
        'Celular',
        'Fixo - Comercial',
        'Fixo - Residencial',
    ];
}

function opcoes_celular()
{
    return [
        'SMS',
        'Telegram',
        'WhatsApp',
    ];
}

function formataDataGerenti($date)
{
    $array = explode('-', $date);
    if(count($array) === 3) {
        return $array[2] . '/' . $array[1] . '/' . $array[0];
    } else {
        return '----------';
    }
}

function segmentosWithAddons($addOn)
{
    if($addOn === null)
        return segmentos();

    $segmentos = segmentos();

    if(!in_array($addOn, $segmentos)) {
        array_push($segmentos, $addOn);
    }

    return $segmentos;
}

function limitRepresentanteName($str)
{
    if (strlen($str) > 30)
        $sub = substr($str, 0, 27) . '...';
    else
        $sub = $str;

    return $sub;
}

function formataCepGerenti($cep)
{
    return substr_replace($cep, '-', -3, 0);
}

function utf8_converter($array)
{
    array_walk_recursive($array, function(&$item, $key){
        if(!mb_detect_encoding($item, 'utf-8', true)){
            $item = utf8_encode($item);
        }
    });

    return $array;
}

function formataDataGerentiRecursive($array)
{
    array_walk_recursive($array, function(&$item, $key){
        if($key === 'Data de cadastro' || $key === 'Data do Registro Social' || $key === 'Data de homologação') {
            $item = formataDataGerenti($item);
        }
    });

    return $array;
}

function secondLine($situacao, $vencimento = null, $link = null, $descricao = null, $boleto = null)
{
    if($situacao === 'Em aberto' && $vencimento === null) {
        $str = '<strong class="text-danger">EXPIRADO</strong>';
    } elseif($situacao === 'Em aberto' && $link !== null) {
        $str = '<strong class="text-warning">EM ABERTO</strong> ⋅ <a href="' . $link . '" class="normal text-info" onclick="clickBoleto(\''. $descricao .'\')">BAIXAR BOLETO</a>';
    } elseif($situacao === 'Em aberto' && $boleto !== null) {
        $str = '<strong class="text-warning">EM ABERTO</strong> ⋅ <a href="https://boletoonline.caixa.gov.br/ecobranca/SIGCB/imprimir/0779951/' . $boleto . '" class="normal text-info" onclick="clickBoleto(\''. $descricao .'\')">BAIXAR BOLETO</a>';
    } elseif($situacao === 'Pago') {
        $str = '<strong class="text-success">PAGO</strong>';
    } elseif($situacao === 'Pago em Parcelamento') {
        $str = '<strong class="text-success">PAGO EM PARCELAMENTO</strong>';
    } elseif($situacao === 'Proc. Adm.') {
        $str = '<strong class="text-info">PROC. ADM.</strong>';
    } else {
        $str = '<strong class="text-info">INDEFINIDO</strong>';
    }

    return $str;
}

function toReais($float)
{
    return number_format($float, 2, ',', '.');
}

function statusBold($string)
{
    $array = explode(':', $string);

    return $array[0] . ': <strong>' . $array[1] . '</strong>';
}

function termoDeUso()
{
    return '
        <p><strong>1. Aceitação dos Termos e Condições de Uso</strong></p>
        <p>O uso da Área Restrita do Representante Comercial oferecida pela Core-SP está condicionado à aceitação e ao cumprimento dos Termos e Condições de Uso descritos abaixo.</p>
        <p>Para fazer uso da Área Restrita, é preciso: (i) ler atentamente os termos descritos abaixo; (ii) concordar expressamente com eles, (iii) se cadastrar fornecendo o CPF/CNPJ, número de registro junto ao Core-SP e o e-mail válido cadastrado no ato de registro profissional, para ter acesso à Área Restrita.</p>
    ';
}

function stringTipoPessoa($number)
{
    switch ($number) {
        case '1':
            return 'PJ';
        break;

        case '2':
            return 'PF';
        break;

        case '5':
            return 'RT';
        break;

        default:
            return 'Indefinida';
        break;
    }
}

function formataRegistro($registro)
{
    return substr_replace($registro, '/', -4, 0);
}

function formataCpfCnpj($value)
{
    $cnpj_cpf = preg_replace("/\D/", '', $value);
    if (strlen($cnpj_cpf) === 11) {
        return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
    }
    return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
}

function tipoPessoaCpfCnpj($cpf_cpnj)
{
    $cpf_cnpj_numero = apenasNumeros($cpf_cpnj);

    return strlen($cpf_cnpj_numero) === 11 ? Representante::PESSOA_FISICA : Representante::PESSOA_JURIDICA;
}

/**
 * Função usada para remover caracteres não numéricos (CPF, CNPJ, Registro CORE e CEP).
 */
function apenasNumeros($value)
{
    return preg_replace('/[^0-9]/', '', $value);
}

function onlyDate($data)
{
    $date = new \DateTime($data);
    $format = $date->format('d\/m\/Y');
    return $format;
}

function onlyHour($data)
{
    $date = new \DateTime($data);
    $format = $date->format('H:i');
    return $format;
}

function organizaData($data)
{
    $date = new \DateTime($data);
    $format = $date->format('H:i\ \d\o \d\i\a d\/m\/Y');
    return $format;
}

function noticiaCategorias()
{
    return [
        'Benefícios',
        'Cotidiano',
        'Feiras',
        'Fiscalização',
        'Espaço do Contador'
    ];
}

function tailCustom($filepath, $lines = 1, $adaptive = true) {
    $f = @fopen($filepath, "rb");
    if ($f === false) return false;
    if (!$adaptive) $buffer = 4096;
    else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
    fseek($f, -1, SEEK_END);
    if (fread($f, 1) != "\n") $lines -= 1;
    $output = '';
    $chunk = '';
    while (ftell($f) > 0 && $lines >= 0) {
        $seek = min(ftell($f), $buffer);
        fseek($f, -$seek, SEEK_CUR);
        $output = ($chunk = fread($f, $seek)) . $output;
        fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
        $lines -= substr_count($chunk, "\n");
    }
    while ($lines++ < 0) {
        $output = substr($output, strpos($output, "\n") + 1);
    }
    fclose($f);
    return trim($output);
}

function retornaDateTime($dia, $hora)
{
    $dia = str_replace('/','-',$dia);
    $date = $dia.' '.$hora;
    $date = new \DateTime($date);
    $format = $date->format('Y-m-d\TH:i:s');
    return $format;
}

function retornaDate($dia)
{
    $dia = str_replace('/','-',$dia);
    
    return new \DateTime($dia);
}

function btnSituacao($situacao)
{
    switch ($situacao) {
        case 'Aberto':
            echo "<div class='sit-btn sit-verde'>Aberto</div>";
        break;

        case 'Homologado':
            echo "<div class='sit-btn sit-verde'>Homologado</div>";
        break;

        case 'Cancelado':
            echo "<div class='sit-btn sit-vermelho'>Cancelado</div>";
        break;

        default:
            echo "<div class='sit-btn sit-default'>".$situacao."</div>";
        break;
    }
}

function noticiaPublicada()
{
    if(Auth::user()->perfil === 'Estagiário')
        return 'Não';
    else
        return 'Sim';
}

function imgToThumb($string)
{
    $str = basename($string);
    $num = strlen($str);
    $num = $num <= 0 ? $num : -$num;
    $add = substr_replace($string,'thumbnails/'.$str,$num);
    return $add;
}

function resumo($string)
{
    if (strlen($string) > 100)
        $string = strip_tags($string);
        $string = html_entity_decode($string);
        $string = substr($string, 0, 240) . '...';
    return $string;
}

function concursoModalidades()
{
    return [
        'Concurso Público',
        'Processo Seletivo'
    ];
}

function concursoSituacoes()
{
    return [
        'Aberto',
        'Anulado',
        'Cancelado',
        'Concluído',
        'Deserto',
        'Em Andamento',
        'Homologado'
    ];
}

function mostraChatScript()
{
    if(config('app.env') !== 'local') {
        $hour = date('H');
        $day = date('w');
        if($hour >= 9 && $hour < 18 && $day !== '6' && $day !== '0') {
            return '<script src="//code.jivosite.com/widget/X12I8gg4Qy" async></script>';
        }
    }
}

function cursoTipos()
{
    return [
        'Curso',
        'Evento Comemorativo',
        'Live',
        'Palestra',
        'Workshop'
    ];
}

function todasHoras()
{
    return [
        '09:00',
        '09:30',
        '10:00',
        '10:30',
        '11:00',
        '11:30',
        '12:00',
        '12:30',
        '13:00',
        '13:30',
        '14:00',
        '14:30',
        '15:00',
        '15:30',
        '16:00',
        '16:30',
        '17:00',
        '17:30',
    ];
}

function converterParaTextoCru($html)
{
    return html_entity_decode(strip_tags($html));
}

function resumoTamanho($string, $tamanho)
{
    if (strlen($string) > 100)
        $string = strip_tags($string);
        $string = html_entity_decode($string);
        $string = substr($string, 0, $tamanho) . '...';
    return $string;
}

/**
 * Função usada para validar data. Usa formato padrão como 'd-m-Y'. 
 * 
 * Datas menores que 01/01/1970 não são aceitas.
 */
function validDate($date, $minDate, $format) {

    $checkMinDate = true;

    date_create_from_format($format, $date);

    $errors = date_get_last_errors();

    if ($minDate !== null) {
        $checkMinDate = date_create_from_format($format, $date) > date_create_from_format($format, $minDate);
    }
    

    return ($errors['warning_count'] + $errors['error_count'] === 0) && $checkMinDate ? true : false;
}

function perfisPermitidos($nameController, $metodo)
{
    $idProfile = auth()->user()->idperfil;
    $permissao = Permissao::where('controller', $nameController)->where('metodo', $metodo)->first();
    return $permissao ? in_array($idProfile, explode(',', $permissao->perfis)) : false;
}

function perfisPermitidosMenu()
{
    return Permissao::select('idpermissao', 'perfis')->whereIn('idpermissao', [
        1, 3, 4, 7, 8, 11, 12, 19, 23, 27, 29, 33, 34, 37, 38, 42, 43, 45, 47, 48, 50, 51, 53, 54, 57, 59, 61, 63
        ])
    ->get()
    ->each(function ($item, $key) {
        $item->perfis = explode(',', $item->perfis);
        $item->perfis = array_filter($item->perfis, function($value) {
            return isset($value) && ($value != '');
        });
    });
}

// Máscara para quantos dígitos forem necessários no rg
function mascaraRG($rg)
{
    $dv = '-' .substr($rg, strlen($rg) - 1, strlen($rg));
    $rgSemDV = substr($rg, 0, strlen($rg) - 1);
    $rgMascara = $dv;
    while(strlen($rgSemDV) > 3)
    {
        $rgMascara = '.' .substr($rgSemDV, strlen($rgSemDV) - 3, strlen($rgSemDV)). $rgMascara;
        $rgSemDV = substr($rgSemDV, 0, strlen($rgSemDV) - 3);
    }
    $rgMascara = $rgSemDV . $rgMascara;

    return $rgMascara;
}

function apenasNumerosLetras($string)
{
    return preg_replace('/[^a-zA-Z0-9]/', '', $string);
}

function generos()
{
    return [
        'F' => 'Feminino',
        'M' => 'Masculino',
        'O' => 'Outro'
    ];
}

function tipos_identidade()
{
    return [
        'Carteira de Conselho Profissional',
        'CNH',
        'Passaporte',
        'RG',
        'RNE'
    ];
}
