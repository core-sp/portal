<?php

namespace App;

use App\Traits\ControleAcesso;
use App\Traits\TabelaAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BdoEmpresa extends Model
{
    use SoftDeletes, ControleAcesso, TabelaAdmin;

	protected $primaryKey = 'idempresa';
    protected $table = 'bdo_empresas';
    protected $fillable = ['segmento', 'cnpj', 'razaosocial', 'fantasia', 'descricao', 'capitalsocial',
    'endereco', 'site', 'email', 'telefone', 'contatonome', 'contatotelefone', 'contatoemail', 'idusuario'];

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function oportunidade()
    {
    	return $this->hasMany('App\BdoOportunidade', 'idempresa');
    }

    /** Variáveis usadas para abrir telas no Portal Admin */
    public function variaveis() {
        return [
            'singular' => 'empresa',
            'singulariza' => 'a empresa',
            'plural' => 'empresas',
            'pluraliza' => 'empresas',
            'titulo_criar' => 'Cadastrar nova empresa',
            'form' => 'bdoempresa',
            'btn_criar' => '<a href="/admin/bdo/empresas/criar" class="btn btn-primary mr-1">Nova Empresa</a>',
            'busca' => 'bdo/empresas',
            'slug' => 'bdo/empresas'
        ];
    }

    /** Valores pré-definidos para o campo segmento (BdoEmpresa.segmento) */
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
            'Outro'
        ];

        return $segmentos;
    }

    /** Valores pré-definidos para o campo capitalSocial (BdoEmpresa.capitalSocial) */
    public static function capitalSocial()
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

    protected function tabelaHeaders()
    {
        return [
            'Código',
            'Segmento',
            'Razão Social',
            'Ações'
        ];
    }

    protected function tabelaContents($query)
    {
        return $query->map(function($row){
            if($this->mostra('BdoOportunidadeController', 'create')) {
                $acoes = '<a href="/admin/bdo/criar/'.$row->idempresa.'" class="btn btn-sm btn-secondary">Nova Oportunidade</a> ';
            }
            else {
                $acoes = '';
            }
               
            if($this->mostra('BdoEmpresaController', 'edit')) {
                $acoes .= '<a href="/admin/bdo/empresas/editar/'.$row->idempresa.'" class="btn btn-sm btn-primary">Editar</a> ';
            }
                
            if($this->mostra('BdoEmpresaController', 'destroy')) {
                $acoes .= '<form method="POST" action="/admin/bdo/empresas/apagar/'.$row->idempresa.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a empresa?\')" />';
                $acoes .= '</form>';
            }

            if(empty($acoes)) {
                $acoes = '<i class="fas fa-lock text-muted"></i>';
            }
                        
            return [
                $row->idempresa,
                $row->segmento,
                $row->razaosocial,
                $acoes
            ];
        })->toArray();
    }

    public function tabelaCompleta($query)
    {
        return $this->montaTabela(
            $this->tabelaHeaders(), 
            $this->tabelaContents($query),
            [ 'table', 'table-hover' ]
        );
    }
}
