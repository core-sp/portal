<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Regional;
use App\BdoOportunidade;

class BdoOportunidadeControllerHelper extends Controller
{
    public static function status()
    {
    	$status = [
            'Sob Análise',
            'Recusado',
            'Em andamento',
            'Concluído',
    		'Expirado',
    	];
    	sort($status);
        return $status;
    }

    public static function onlyDate($data)
    {
        $date = new \DateTime($data);
        $format = $date->format('d\/m\/Y');
        return $format;
    }

    public static function regioes()
    {
        $regioes = [
            'Capital',
            'Grande São Paulo',
            'Interior',
            'Litoral'
        ];
        return $regioes;
    }

    public static function segmentos()
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

    public static function listRegioes($string)
    {
        if($string !== ',1,2,3,4,5,6,7,8,9,10,11,12,13,') {
            $string = explode(',',$string);
            $array = [];
            foreach($string as $s)
                array_push($array, $s);
            $regionais = Regional::findMany($array);
            $regArray = [];
            foreach($regionais as $r)
                array_push($regArray, $r->regional);
            $corrigido = implode(',',$regArray);
            $mostra = str_replace(',',' / ',$corrigido);
            return $mostra;
        } else {
            $mostra = "Em todo o estado de São Paulo";
            return $mostra;
        }
    }
}
