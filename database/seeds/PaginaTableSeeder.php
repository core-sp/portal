<?php

use Illuminate\Database\Seeder;
use App\Pagina;

class PaginaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pagina = new Pagina();
        $pagina->titulo = "Legislação";
        $pagina->subtitulo = "CONTEÚDO NORMATIVO";
        $pagina->slug = "legislacao";
        $pagina->img = "/imagens/2019-04/legislacao.png";
        $pagina->conteudo = "<ul>
        <li>Legisla&ccedil;&atilde;o</li>
        <li>Resolu&ccedil;&otilde;es</li>
        <li>Portarias</li>
        <li>Ordens de Servi&ccedil;o</li>
        </ul>";
        $pagina->idusuario = 6;
        $pagina->save();

        $pagina = new Pagina();
        $pagina->titulo = "Mapa do Site";
        $pagina->subtitulo = "Lorem ipsum dolor sit amet";
        $pagina->slug = "mapa-do-site";
        $pagina->img = "/imagens/2019-04/institucional.png";
        $pagina->conteudo = "<ul>
            <li><a href='/'>Home</a></li>
            <li><a href='#'>CORE-SP</a>
            <ul>
            <li><a href='/legislacao'>Legisla&ccedil;&atilde;o</a></li>
            </ul>
            </li>
            <li><a href='http://core-sp.implanta.net.br/portaltransparencia/#publico/inicio' target='_blank' rel='noopener'>Portal da Transpar&ecirc;ncia</a></li>
            <li><a href='/licitacoes'>Licita&ccedil;&otilde;es</a></li>
            <li><a href='/cursos'>Cursos</a></li>
            <li><a href='/admin/paginas/balcao-de-oportunidades'>Balc&atilde;o de Oportunidade</a></li>
            </ul>";
        $pagina->idusuario = 1;
        $pagina->save();

        $pagina = new Pagina();
        $pagina->titulo = "Acessibilidade";
        $pagina->subtitulo = "Sob as diretrizes da eMag";
        $pagina->slug = "acessibilidade";
        $pagina->img = "/imagens/2019-04/institucional.png";
        $pagina->conteudo = "<p>Teclando-se <strong>Alt + 1:</strong> Posiciona o cursor no topo da página.</p>
            <p>Teclando-se <strong>Alt + 2:</strong> Posiciona o cursor na busca do site.</p>
            <p>Teclando-se <strong>Alt + 3:</strong> Posiciona o cursor no conteúdo principal da página.</p>
            <p>Teclando-se <strong>Alt + 4:</strong> Posiciona o cursor no rodapé do site.</p>
            <p>Teclando-se <strong>Alt + 5:</strong> Aplica/Remove o contraste da página.</p>";
        $pagina->idusuario = 1;
        $pagina->save();

        $pagina = new Pagina();
        $pagina->titulo = "Perguntas Frequentes";
        $pagina->subtitulo = "CARTILHA DO REPRESENTANTE COMERCIAL";
        $pagina->slug = "perguntas-frequentes";
        $pagina->img = "/imagens/2019-04/institucional.png";
        $pagina->conteudo = "<p><strong>SUM&Aacute;RIO</strong></p>
        <ol>
        <li>O QUE &Eacute; O CORE-SP?&nbsp;</li>
        <li>QUAL A FINALIDADE DO CORE-SP?&nbsp;</li>
        <li>O CORE-SP &Eacute; UM SINDICATO?</li>
        <li>O CORE-SP PODER&Aacute; AJUIZAR A&Ccedil;&Atilde;O PARA DEFENDER&nbsp;OS INTERESSES DE REGISTRADO?&nbsp;</li>
        <li>QUEM DEVE SE REGISTRAR JUNTO AO CORE-SP?&nbsp;</li>
        <li>QUAIS S&Atilde;O OS DOCUMENTOS NECESS&Aacute;RIOS PARA QUE O&nbsp;REPRESENTANTE COMERCIAL REALIZE REGISTRO&nbsp;JUNTO AO CORE-SP?&nbsp;</li>
        <li>QUAL O PRAZO DE REGISTRO DO REPRESENTANTE COMERCIAL?&nbsp;</li>
        <li>PARA REALIZAR O REGISTRO JUNTO AO CORE-SP DEVO REALIZAR&nbsp;O PAGAMENTO DA CONTRIBUI&Ccedil;&Atilde;O SINDICAL?&nbsp;</li>
        <li>QUAIS AS PENALIDADES APLIC&Aacute;VEIS AO REPRESENTANTE COMERCIAL&nbsp;SEM REGISTRO JUNTO AO CORE-SP?&nbsp;</li>
        <li>QUAIS TIPOS DE REGISTRO PODEM SER EFETUADOS&nbsp;JUNTO AO CORE-SP?&nbsp;</li>
        <li>O QUE &Eacute; O REGISTRO DE RESPONS&Aacute;VEL T&Eacute;CNICO?&nbsp;</li>
        <li>O REGISTRO COMO RESPONS&Aacute;VEL T&Eacute;CNICO PERMITE A&nbsp;ATUA&Ccedil;&Atilde;O COMO PESSOA F&Iacute;SICA?&nbsp;</li>
        <li>POSSO SER RESPONS&Aacute;VEL T&Eacute;CNICO DE AT&Eacute; QUANTAS EMPRESAS?&nbsp;</li>
        <li>QUAL O VALOR DA ANUIDADE DO RESPONS&Aacute;VEL T&Eacute;CNICO?</li>
        <li>EXISTE A NECESSIDADE DE REGISTRO DE RESPONS&Aacute;VEL&nbsp;T&Eacute;CNICO PARA O EMPRES&Aacute;RIO INDIVIDUAL?&nbsp;</li>
        <li>A EMPRESA DE REPRESENTA&Ccedil;&Atilde;O COMERCIAL PODE SER&nbsp;OPTANTE DO SIMPLES NACIONAL?&nbsp;</li>
        <li>EMPRESA OPTANTE PELO SIMPLES &Eacute; ISENTA DO&nbsp;PAGAMENTO DAS ANUIDADES DO CORE-SP?&nbsp;</li>
        <li>O MICRO EMPREENDEDOR INDIVIDUAL &ndash; MEI, PODE OBTER&nbsp;REGISTRO JUNTO AO CORE-SP?&nbsp;</li>
        <li>A QUEM COMPETE A DEFINI&Ccedil;&Atilde;O DOS VALORES DAS ANUIDADES?&nbsp;</li>
        <li>NO CASO DE N&Atilde;O MAIS EXERCER A ATIVIDADE DE REPRESENTA&Ccedil;&Atilde;O COMERCIAL, PRECISO CANCELAR O REGISTRO?&nbsp;</li>
        <li>QUAL O VENCIMENTO DAS ANUIDADES DO CORE-SP?&nbsp;</li>
        <li>NA HIP&Oacute;TESE DE N&Atilde;O PAGAMENTO DAS ANUIDADES,&nbsp;A QUAIS RESPONSABILIDADES OS REPRESENTANTES COMERCIAIS&nbsp;ESTAR&Atilde;O SUJEITOS?&nbsp;</li>
        <li>POSSUO D&Eacute;BITOS EM FAVOR DO CORE-SP. POSSO REALIZAR O PAGAMENTO DE ANUIDADES DE MANEIRA PARCELADA?&nbsp;</li>
        <li>O REPRESENTANTE COMERCIAL COM D&Iacute;VIDA JUNTO AO CORE-SP PODE SOLICITAR O CANCELAMENTO DE REGISTRO?&nbsp;</li>
        <li>O CORE-SP OFERTA A POSSIBILIDADE DE ISEN&Ccedil;&Atilde;O DE ANUIDADES A REPRESENTANTES COMERCIAIS?</li>
        <li>A EMPRESA DE REPRESENTA&Ccedil;&Atilde;O COMERCIAL PODE SOLICITAR A SUSPENS&Atilde;O DO REGISTRO JUNTO AO CORE-SP?&nbsp;</li>
        <li>O REPRESENTANTE COMERCIAL AUT&Ocirc;NOMO PODER&Aacute; SOLICITAR A SUSPENS&Atilde;O DO REGISTRO JUNTO AO CORE-SP?&nbsp;</li>
        <li>QUAIS OS RISCOS DA REPRESENTADA CONTRATAR&nbsp;PROFISSIONAL SEM REGISTRO JUNTO AO CORE-SP?&nbsp;</li>
        <li>COMO DEVE SER CALCULADO O VALOR DAS COMISS&Otilde;ES?&nbsp;</li>
        <li>EM QUE MOMENTO O REPRESENTANTE COMERCIAL&nbsp;ADQUIRE DIREITO &Agrave;S COMISS&Otilde;ES?&nbsp;</li>
        <li>O REPRESENTANTE COMERCIAL PODE SER FIADOR&nbsp;DA OBRIGA&Ccedil;&Atilde;O DO CLIENTE?&nbsp;</li>
        <li>AP&Oacute;S A RESCIS&Atilde;O DO CONTRATO, QUAL O PRAZO PARA&nbsp;RECEBER A COMISS&Atilde;O REFERENTE AOS PEDIDOS EM CARTEIRA?&nbsp;</li>
        <li>EM QUAIS SITUA&Ccedil;&Otilde;ES AQUELE QUE DESEJA RESCINDIR&nbsp; O CONTRATO DE REPRESENTA&Ccedil;&Atilde;O COMERCIAL&nbsp; TEM DIREITO AO AVISO PR&Eacute;VIO?&nbsp;</li>
        <li>O REPRESENTANTE COMERCIAL TEM DIREITO AO AVISO PR&Eacute;VIO&nbsp;SE O CONTRATO &Eacute; RESCINDIDO ANTES DE 6 (SEIS) MESES?&nbsp;</li>
        <li>MEU CONTRATO DE REPRESENTA&Ccedil;&Atilde;O COMERCIAL PRODUZIU EFEITOS POR MENOS&nbsp;DE 6 (SEIS) MESES. TENHO DIREITO &Agrave; INDENIZA&Ccedil;&Atilde;O PREVISTA NO&nbsp;ARTIGO 27, AL&Iacute;NEA &ldquo;J&rdquo; DA LEI N&ordm; 4.886/1965?&nbsp;</li>
        <li>QUANDO O REPRESENTANTE COMERCIAL RESCINDE&nbsp;O CONTRATO, ELE POSSUI DIREITO &Agrave; INDENIZA&Ccedil;&Atilde;O DE 1/12?&nbsp;</li>
        <li>NA HIP&Oacute;TESE DE RESCIS&Atilde;O DO CONTRATO PELA REPRESENTADA, O REPRESENTANTE COMERCIAL PODE VIR A PERDER O DIREITO DA&nbsp;INDENIZA&Ccedil;&Atilde;O DE 1/12?&nbsp;</li>
        <li>QUAL O &Iacute;NDICE DEVER&Aacute; SER UTILIZADO ATUALIZAR&nbsp;O C&Aacute;LCULO DA INDENIZA&Ccedil;&Atilde;O DE 1/12?&nbsp;</li>
        <li>INCIDE IMPOSTO DE RENDA SOBRE A INDENIZA&Ccedil;&Atilde;O DE 1/12?&nbsp;</li>
        <li>QUAL O PRAZO PARA AJUIZAMENTO DE A&Ccedil;&Atilde;O&nbsp;&nbsp;DE COBRAN&Ccedil;A DA INDENIZA&Ccedil;&Atilde;O DEVIDA AO&nbsp;REPRESENTANTE COMERCIAL (1/12)?&nbsp;</li>
        <li>QUAIS OS DIREITOS DO REPRESENTANTE SE A&nbsp; REPRESENTADA FALIR?&nbsp;</li>
        <li>O REPRESENTANTE COMERCIAL PODE CONTRATAR PREPOSTOS PARA A EXECU&Ccedil;&Atilde;O DOS SERVI&Ccedil;OS RELACIONADOS COM A REPRESENTA&Ccedil;&Atilde;O?&nbsp;</li>
        </ol>";
        $pagina->idusuario = 6;
        $pagina->save();

        $pagina = new Pagina();
        $pagina->titulo = "Agendamento";
        $pagina->subtitulo = "Uma nova forma de atendimento do CORE-SP";
        $pagina->idpaginacategoria = 1;
        $pagina->slug = "servicos/agendamento";
        $pagina->conteudo = '<p>Agora &eacute; poss&iacute;vel realizar o agendamento online de seu atendimento presencial no CORE-SP!<br /><br />Para realizar o agendamento na seccional mais pr&oacute;xima de voc&ecirc;, basta acessar <a href="/agendamento">esta p&aacute;gina</a> e preencher todas as informa&ccedil;&otilde;es.</p>
        <p>Caso voc&ecirc; j&aacute; tenha realizado o agendamento com o CORE-SP, &eacute; poss&iacute;vel recuperar as informa&ccedil;&otilde;es atrav&eacute;s <a href="/agendamento-consulta">deste link.</a></p>';
        $pagina->idusuario = 1;
        $pagina->save();

        $pagina = new Pagina();
        $pagina->titulo = "Balcão de Oportunidades";
        $pagina->subtitulo = "Juntando empresas e Representantes Comerciais";
        $pagina->idpaginacategoria = 1;
        $pagina->slug = "servicos/balcao-de-oportunidades";
        $pagina->conteudo = '<p>O CORE-SP conta agora com um Balc&atilde;o de Oportunidades, no qual empresas e Representantes Comerciais podem se conectar.</p>
        <p>Para conferir as &uacute;ltimas oportunidades, <a href="/balcao-de-oportunidades">clique aqui.</a></p>';
        $pagina->idusuario = 1;
        $pagina->save();

        $pagina = new Pagina();
        $pagina->titulo = "Cartilha do Representante";
        $pagina->subtitulo = "Lorem ipsum dolor sit amet.";
        $pagina->slug = "cartilha-do-representante";
        $pagina->conteudo = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam ut lacus nisi. Morbi at condimentum lectus. Quisque ac lorem ac mauris accumsan convallis ac non magna. Nullam lobortis consequat libero, ac condimentum nisl faucibus quis. Curabitur non nibh lacus. Phasellus ac vehicula diam, at fringilla nunc. Vestibulum mattis est at sapien bibendum tincidunt. Phasellus at dignissim enim. Nunc eget diam sed enim maximus tincidunt quis id ante. Praesent et ultrices risus. Sed interdum, velit pretium feugiat malesuada, elit odio mattis erat, quis tristique felis urna vitae nisl. Duis a ipsum pharetra mauris scelerisque fringilla non at lorem.</p>';
        $pagina->idusuario = 1;
        $pagina->save();
    }
}
