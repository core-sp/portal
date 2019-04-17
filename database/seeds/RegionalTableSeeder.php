<?php

use Illuminate\Database\Seeder;
use App\Regional;

class RegionalTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	// São Paulo
        $regional = new Regional();
        $regional->prefixo = 'SEDE';
        $regional->regional = 'São Paulo';
        $regional->endereco = 'Av. Brigadeiro Luís Antônio';
        $regional->bairro = 'Bela Vista';
        $regional->numero = '613';
        $regional->complemento = 'Térreo';
        $regional->cep = '01317-000';
        $regional->telefone = '(11) 3243-5500';
        $regional->fax = '(11) 3243-5520';
        $regional->email = 'corcesp@core-sp.org.br';
        $regional->funcionamento = 'das 09h às 18h';
        $regional->descricao = '<p>Horário de atendimento: 09h às 18h (registro apenas em horário bancário)</p>';
        $regional->save();

        // Campinas
        $regional = new Regional();
        $regional->prefixo = 'ES01';
        $regional->regional = 'Campinas';
        $regional->endereco = 'Rua Alecrins';
        $regional->bairro = 'Cambui';
        $regional->numero = '914';
        $regional->complemento = '3° andar';
        $regional->cep = '13024-411';
        $regional->telefone = '(19) 3236-8867';
        $regional->fax = '(19) 3235-1874';
        $regional->email = 'corcespcampinas@core-sp.org.br';
        $regional->funcionamento = 'das 09h às 18h';
        $regional->responsavel = 'Eduardo / João';
        $regional->descricao = '<p>Em 1966, de acordo com a Resolução n.° 3 baixada pelo Conselho Regional dos Representantes Comerciais do Estado de São Paulo, são criados escritórios seccionais com a finalidade de orientar o representante quanto ao registro no core-sp, examinar documentação e enviá-la para a sede na capital do estado. A informatização e modernização do escritório possibilitaram, com um sistema integrado, efetuar registros no próprio escritório seccional.</p>
        	<p>O ES de Campinas tem como base territorial os seguintes municípios:</p>
        	<p>Campinas, Aguaí, Águas da Prata, Águas de Lindóia, Americana, Amparo, Artur Nogueira, Bragança Paulista, Caconde, Campo Limpo, Capivari, Casa Branca, Cosmópolis, Divinolândia, Elias Fausto, Engenheiro Coelho, Indaiatuba, Itapira, Itatiba, Itobi, Itupeva, Jaguariúna, Jundiaí, Louveira, Mogi Guaçu, Moji Mirim, Mombuca, Monte Alegre do Sul, Monte Mor, Morungaba, Nova Odessa, Paulínia, Pedra Bela, Pedreira, Pinhal, Pinhalzinho, Rafard, Rio das Pedras, Santa Bárbara D’Oeste, Santo Antonio da Posse, Santo Antonio do Jardim, São João da Boa Vista, São Sebastião da Grama, Serra Negra, Socorro, Sumaré, Tapiratiba, Valinhos, Vargem, Vargem Grande do Sul, Várzea Paulista e Vinhedo.</p>';
        $regional->save();

        // Bauru
        $regional = new Regional();
        $regional->prefixo = 'ES02';
        $regional->regional = 'Bauru';
        $regional->endereco = 'Rua Luso Brasileira';
        $regional->bairro = 'Jardim Estoril IV';
        $regional->numero = '4-44';
        $regional->complemento = 'Ed. Metropolitan Square, 4º Andar Salas 411/412';
        $regional->cep = '17016-230';
        $regional->telefone = '(14) 3214-4318';
        $regional->fax = '(14) 3227-2399';
        $regional->email = 'corcespbauru@core-sp.org.br';
        $regional->funcionamento = 'das 09h às 18h';
        $regional->responsavel = 'Wendel';
        $regional->descricao = '<p>Em 1966, de acordo com a Resolução n.° 3 baixada pelo Conselho Regional dos Representantes Comerciais do Estado de São Paulo, são criados escritórios seccionais com a finalidade de orientar o representante quanto ao registro no core-sp, examinar documentação e enviá-la para a sede na capital do estado. A informatização e modernização do escritório possibilitaram, com um sistema integrado, efetuar registros no próprio escritório seccional.</p>
			<p>A base territorial do ES de Bauru abrange os seguintes municípios:</p>
			<p>Bauru, Agudos, Anhembi, Arandu, Arealva, Areiópolis, Avaí, Avaré, Balbinos, Barão de Antonina, Bariri, Barra Bonita, Bernardino de Campos, Bofete, Boracéia, Borborema, Botucatu, Brotas, Cabrália Paulista, Cafelândia, Canitar, Cerqueira César, Dois Córregos, Duartina, Fartura, Gália, Guarantã, Iacanga, Igaraçu do Tietê, Ipaussu, Itaju, Itatinga, Jaú, Lençóis Paulista, Macatuba, Manduri, Mineiro do Tietê, Óleo, Pardinho, Pederneiras, Piraju, Pirajuí, Piratininga, Pongaí, Presidente Alves, Reginópolis, Sabino, Santa Cruz do Rio Pardo, São Manuel, Sarutaiá, Taguaí, Tejupá, Timburi, Torrinha, Uru e Chavantes.</p>';
        $regional->save();

        // Ribeirão Preto
        $regional = new Regional();
        $regional->prefixo = 'ES03';
        $regional->regional = 'Ribeirão Preto';
        $regional->endereco = 'Av. Maurílio Biagi';
        $regional->bairro = 'Santa Cruz do José Jacques';
        $regional->numero = '800';
        $regional->complemento = '3º andar, conj. 311/312/313/314';
        $regional->cep = '14020-750';
        $regional->telefone = '(16) 3964-6633';
        $regional->fax = '(16) 3964-6633';
        $regional->email = 'corcespribeirao@core-sp.org.br';
        $regional->funcionamento = 'das 09h às 18h';
        $regional->responsavel = 'Paula Tavares';
        $regional->descricao = '<p>Em 1966, de acordo com a Resolução n.° 3 baixada pelo Conselho Regional dos Representantes Comerciais do Estado de São Paulo, são criados escritórios seccionais com a finalidade de orientar o representante quanto ao registro no core-sp, examinar documentação e enviá-la para a sede na capital do estado. A informatização e modernização do escritório possibilitaram, com um sistema integrado, efetuar registros no próprio escritório seccional.</p>
        	<p>A base territorial do ES de Ribeirão Preto engloba os municípios:</p>
        	<p>Ribeirão Preto, Altinópolis, Aramina, Barrinha, Batatais, Bebedouro, Brodowski, Buritizal, Cajuru, Cássia dos Coqueiros, Cravinhos, Cristais Paulista, Descalvado, Dumont, Franca, Guará, Guariba, Igarapava, Ipuã, Itirapuã, Ituverava, Jaboticabal, Jardinópolis, Jeriquara, Luís Antonio, Miguelópolis, Mococa, Monte Alto, Monte Azul Paulista, Morro Agudo, Nuporanga, Patrocínio Paulista, Pedregulho, Pitangueiras, Pontal, Porto Ferreira, Pradópolis, Restinga, Ribeirão Corrente, Rifaina, Sales Oliveira, Santa Cruz das Palmeiras, Santa Rita do Passa Quatro, Santa Rosa do Viterbo, Santo Antonio da Alegria, São Joaquim da Barra, São José da Bela Vista, São José do Rio Pardo, São Simão, Serra Azul, Serrana, Sertãozinho, Taiaçu, Taiúva, Tambaú, Terra Roxa, Viradouro e Vista Alegre do Alto.</p>';
        $regional->save();

        // São José dos Campos
        $regional = new Regional();
        $regional->prefixo = 'ES04';
        $regional->regional = 'São José dos Campos';
        $regional->endereco = 'Rua Euclides Miragaia';
        $regional->bairro = 'Centro';
        $regional->numero = '700';
        $regional->complemento = '7° andar, salas 71/72/74';
        $regional->cep = '12245-820';
        $regional->telefone = '(12) 3922-0508';
        $regional->fax = '(12) 3922-8239';
        $regional->email = 'corcespsjcampos@core-sp.org.br';
        $regional->funcionamento = 'das 09h às 18h';
        $regional->responsavel = 'Michele / Mateus';
        $regional->descricao = '<p>Em 1975, de acordo com a Resolução n.° 72 baixada pelo Conselho Regional dos Representantes Comerciais do Estado de São Paulo, é criado o escritório seccional de São José dos Campos. Com a finalidade de substituir a delegacia seccional Taubaté, o escritório de São José dos Campos orienta o representante quanto ao registro no core-sp, examina documentação e envia-a para a sede na capital do estado. A informatização e modernização do escritório possibilitaram, com um sistema integrado, efetuar registros no próprio escritório seccional.</p>
        	<p>Fazem parte da base territorial do ES de São José dos Campos os seguintes municípios:</p>
        	<p>São José dos Campos, Aparecida, Areias, Arujá, Bananal, Caçapava, Cachoeira Paulista, Campos do Jordão, Canas, Caraguatatuba, Cruzeiro, Cunha, Guaratinguetá, Igaratá, Ilhabela, Jacareí, Jambeiro, Lagoinha, Lavrinhas, Lorena, Monteiro Lobato, Natividade da Serra, Paraibuna, Pindamonhangaba, Piquete, Potim, Queluz, Redenção da Serra, Roseira, Salesópolis, Santa Branca, Santa Isabel, Santo Antonio do pinhal, São Bento do Sapucaí, São José do Barreiro, São Luiz do Paraitinga, São Sebastião, Silveiras, Taubaté, Tremembé e Ubatuba.</p>';
        $regional->save();

        // São José do Rio Preto
        $regional = new Regional();
        $regional->prefixo = 'ES05';
        $regional->regional = 'São José do Rio Preto';
        $regional->endereco = 'R. General Glicério';
        $regional->bairro = 'Centro';
        $regional->numero = '3173';
        $regional->complemento = '4° andar';
        $regional->cep = '15015-400';
        $regional->telefone = '(17) 3211-9953';
        $regional->fax = '(17) 3211-9891';
        $regional->email = 'corcespriopreto@core-sp.org.br';
        $regional->funcionamento = 'das 09h às 18h';
        $regional->responsavel = 'Doacir / Cléber';
        $regional->descricao = '<p>Em 1966, de acordo com a Resolução n.° 3 baixada pelo Conselho Regional dos Representantes Comerciais do Estado de São Paulo, são criados escritórios seccionais com a finalidade de orientar o representante quanto ao registro no core-sp, examinar documentação e enviá-la para a sede na capital do estado. A informatização e modernização do escritório possibilitaram, com um sistema integrado, efetuar registros no próprio escritório seccional.</p>
        	<p>O ES de São José do Rio Preto atende aos seguintes municípios:</p>
        	<p>São José do Rio Preto, Adolfo, Altair, Álvares Florence, Américo de Campos, Ariranha Bady Bassitt, Bálsamo, Barbosa, Barretos, Cajobi, Cardoso, Catanduva, Catinguá, Cedral, Colina, Colômbia, Cosmorama, Fernandópolis, Floreal, Guairá, Guapiaçú, Guaraci, Guarani D’Oeste, Ibirá, Icém, Indiaporã, Irapuã, Itajobi, Jaborandi, Jaci, José Bonifácio, Macaubal, Macedônia, Magda, Mendonça, Meridiano, Mira Estrela, Mirassol, Mirassolândia, Monções, Monte Aprazível, Neves Paulista, Nhandeara, Nipoã, Nova Aliança, Nova Granada, Novo Horizonte, Olímpia, Onda Verde, Orindiúva, Palestina, Palmares Paulista, Paraíso, Paulo de Faria, Pedranópolis, Planalto, Pindorama, Pirangi, Poloni, Pontes Gestal, Potirendaba, Riolândia, Sales, Santa Adélia, Sebastianópolis do Sul, Severínia, Tabapuã, Tanabi, Turiúba, Uchôa, União Paulista, Urupês, Valentim Gentil e Votuporanga.</p>';
        $regional->save();

        // Presidente Prudente
        $regional = new Regional();
        $regional->prefixo = 'ES06';
        $regional->regional = 'Presidente Prudente';
        $regional->endereco = 'R. Siqueira Campos';
        $regional->bairro = 'Centro';
        $regional->numero = '669';
        $regional->complemento = '7º Andar, Sala 77';
        $regional->cep = '19010-061';
        $regional->telefone = '(18) 3903-6198';
        $regional->fax = '(18) 3903-6198';
        $regional->email = 'corcespprudente@core-sp.org.br';
        $regional->funcionamento = 'das 09h às 18h';
        $regional->responsavel = 'Sidilene';
        $regional->descricao = '<p>Em 1966, de acordo com a Resolução n.° 3 baixada pelo Conselho Regional dos Representantes Comerciais do Estado de São Paulo, são criados escritórios seccionais com a finalidade de orientar o representante quanto ao registro no core-sp, examinar documentação e enviá-la para a sede na capital do estado. A informatização e modernização do escritório possibilitaram, com um sistema integrado, efetuar registros no próprio escritório seccional.</p>
        	<p>A base territorial do ES de Presidente Prudente abrange os municípios:</p>
        	<p>Presidente Prudente, Adamantina, Alfredo Marcondes, Álvares Machado, Anhumas, Bastos, Caiabu,  Caiuá, Dracena, Emilianópolis, Estrela do Norte, Euclides da Cunha Paulista, Flora Rica, Flórida Paulista, Iacri, Iepê, Indiana, Inúbia Paulista, Irapurú, João Ramalho Junqueirópolis, Lutécia, Marabá Paulista, Mariápolis, Martinópolis, Monte Castelo, Mirante do Paranapanema, Nantes, Narandiba, Nova-Guataporanga, Oswaldo Cruz, Ouro Verde, Pacaembu, Panorama, Parapuã, Paulicéia, Piquerobi, Pirapozinho, Pracinha, Presidente Bernardes, Presidente Epitácio, Presidente Venceslau, Rancharia, Regente Feijó, Ribeirão dos Índios, Rinópolis, Rosana, Sagres, Salmourão, Sandovalina, Santa Mercedes, Santo Anastácio, Santo Expedito, São João do Pau D’Alho, Taciba, Tarabaí, Teodoro Sampaio e Tupi Paulista.</p>';
        $regional->save();

        // Araraquara
        $regional = new Regional();
        $regional->prefixo = 'ES07';
        $regional->regional = 'Araraquara';
        $regional->endereco = 'R. Padre Duarte';
        $regional->bairro = 'Jardim Nova América';
        $regional->numero = '151';
        $regional->complemento = '16° andar, Sala 161/162';
        $regional->cep = '14800-360';
        $regional->telefone = '(16) 3333-4549';
        $regional->fax = '(16) 3333-4549';
        $regional->email = 'corcespararaquara@core-sp.org.br';
        $regional->funcionamento = 'das 09h às 18h';
        $regional->responsavel = 'Amanda Rotondo Piffer';
        $regional->descricao = '<p>Em 1966, de acordo com a Resolução n.° 3 baixada pelo Conselho Regional dos Representantes Comerciais do Estado de São Paulo, são criados escritórios seccionais com a finalidade de orientar o representante quanto ao registro no core-sp, examinar documentação e enviá-la para a sede na capital do estado. A informatização e modernização do escritório possibilitaram, com um sistema integrado, efetuar registros no próprio escritório seccional.</p>
			<p>A base territorial do ES de Araraquara compreende os municípios:</p>
			<p>Araraquara, Américo Brasiliense, Boa Esperança do Sul, Bocaina, Candido Rodrigues, Dobrada,Dourados, Fernando Prestes, Ibaté, Ibitinga, Itápolis, Matão, Nova Europa, Ribeirão Bonito, Rincão, Santa Ernestina, Santa Lúcia, São Carlos, Tabatinga e Taquaratinga.</p>';
        $regional->save();

        // Sorocaba
        $regional = new Regional();
        $regional->prefixo = 'ES08';
        $regional->regional = 'Sorocaba';
        $regional->endereco = 'R. São Bento';
        $regional->bairro = 'Centro';
        $regional->numero = '190';
        $regional->complemento = '11° andar, Sala 113';
        $regional->cep = '18010-031';
        $regional->telefone = '(15) 3233-4322';
        $regional->fax = '(15) 3233-4322';
        $regional->email = 'corcespsorocaba@core-sp.org.br';
        $regional->funcionamento = 'das 09h às 18h';
        $regional->responsavel = 'Débora';
        $regional->descricao = '<p>Em 1966, de acordo com a Resolução n.° 3 baixada pelo Conselho Regional dos Representantes Comerciais do Estado de São Paulo, são criados escritórios seccionais com a finalidade de orientar o representante quanto ao registro no core-sp, examinar documentação e enviá-la para a sede na capital do estado. A informatização e modernização do escritório possibilitaram, com um sistema integrado, efetuar registros no próprio escritório seccional.</p>
        	<p>O ES de Sorocaba atende aos municípios:</p>
        	<p>Sorocaba, Alumínio, Angatuba, Apiaí, Araçariguama, Araçoiaba da Serra, Barra do Chapéu, Barra do Turvo, Boituva, Bom Sucesso de Itararé, Buri, Cabreúva, Campina do Monte Alegre, Capão Bonito, Capela do Alto, Cerquilho, Cesário Lange, Conchas, Coronel Macedo, Eldorado, Guapiara, Guareí, Ibiúna, Iperó, Iporanga, Itaberá, Itaí, Itapetininga, Itapeva, Itapirapuã Paulista, Itaporanga, Itaóca, Itararé, Itu, Jacupiranga, Juquiá, Laranjal Paulista, Mairinque, Paranapanema, Pariquera Açu, Pereiras, Piedade, Pilar do Sul, Porangaba, Porto Feliz, Quadra, Registro, Ribeira, Ribeirão Branco, Ribeirão Vermelho do Sul, Salto, Salto de Pirapora, São Miguel Arcanjo, São Roque, Sarapuí, Sete Barras, Taquarituba, Taquarivaí, Tapiraí, Tatuí, Tietê, Torre de Pedra e Votorantim.</p>';
        $regional->save();

        // Santos
        $regional = new Regional();
        $regional->prefixo = 'ES09';
        $regional->regional = 'Santos';
        $regional->endereco = 'R. João Pessoa';
        $regional->bairro = 'Centro';
        $regional->numero = '69';
        $regional->complemento = '10° andar, cj. 102';
        $regional->cep = '11013-902';
        $regional->telefone = '(13) 3219-7462';
        $regional->fax = '(13) 3219-7462';
        $regional->email = 'corcespsantos@core-sp.org.br';
        $regional->funcionamento = 'das 09h às 18h';
        $regional->responsavel = 'Barbara';
        $regional->descricao = '<p>Em 1966, de acordo com a Resolução n.° 3 baixada pelo Conselho Regional dos Representantes Comerciais do Estado de São Paulo, são criados escritórios seccionais com a finalidade de orientar o representante quanto ao registro no core-sp, examinar documentação e enviá-la para a sede na capital do estado. A informatização e modernização do escritório possibilitaram, com um sistema integrado, efetuar registros no próprio escritório seccional.</p>
        	<p>A base territorial do ES de Santos abrange os seguintes municípios:</p>
        	<p>Santos, Bertioga, Cananéia, Cubatão, Guarujá, Iguape, Ilha Comprida, Itanhaém, Itariri, Miracatu, Mongaguá, Pedro de Toledo, Peruíbe, Praia Grande e São Vicente.</p>';
        $regional->save();

        // Araçatuba
        $regional = new Regional();
        $regional->regional = 'Araçatuba';
        $regional->prefixo = 'ES10';
        $regional->endereco = 'R. Osvaldo Cruz';
        $regional->bairro = 'Centro';
        $regional->numero = '1';
        $regional->complemento = '2° andar, cj. 21/22';
        $regional->cep = '16010-040';
        $regional->telefone = '(18) 3625-2080';
        $regional->fax = '(18) 3625-2080';
        $regional->email = 'corcesparacatuba@core-sp.org.br';
        $regional->funcionamento = 'das 09h às 18h';
        $regional->responsavel = 'Kátia';
        $regional->descricao = '<p>Em 1966, de acordo com a Resolução n.° 3 baixada pelo Conselho Regional dos Representantes Comerciais do Estado de São Paulo, são criados escritórios seccionais com a finalidade de orientar o representante quanto ao registro no core-sp, examinar documentação e enviá-la para a sede na capital do estado. O escritório foi dissolvido em 1975 pela Resolução 77 .A informatização e modernização do escritório possibilitaram, com um sistema integrado, efetuar registros no próprio escritório seccional.</p>
			<p> O ES de Araçatuba tem como base territorial os seguintes municípios:</p>
			<p>Araçatuba, Alto Alegre, Andradina, Aparecida D’Oeste, Auriflama, Avanhandava, Bento de Abreu, Bilac, Birigui, Braúna, Buritama, Castilho, Clementina, Coroados, Dolcinópolis, Estrela D’Oeste, Gabriel Monteiro, Gastão Vidigal, General Salgado, Glicério, Guaraçaí, Guararapes, Guzolândia, Ilha Solteira, Itapura, Jales, Lavínia, Luiziânia, Marinópolis, Mirandópolis, Muritinga do Sul, Nova Independência, Nova Luzitânia, Palmeira d’Oeste, Paranapuã, Penápolis, Pereira Barreto, Piacatu, Ponta Linda, Populina, Promissão, Rubiácea, Santa Albertina, Santa Clara d’Oeste, Santa Fé do Sul, Santa Rita D’Oeste, Santa Salete, Santana da Ponte Pensa, São Francisco, São João das Duas Pontes, Santo antônio do Aracanguá, Santópolis do Aguapeí, Sud Mennucci, Suzanópolis, Três Fronteiras, Turmalina, Urânia, Valparaíso e Vitória Brasil.</p>';
        $regional->save();

        // Rio Claro
        $regional = new Regional();
        $regional->prefixo = 'ES11';
        $regional->regional = 'Rio Claro';
        $regional->endereco = 'R. 06';
        $regional->bairro = 'Centro';
        $regional->numero = '1460';
        $regional->complemento = 'Sala 41';
        $regional->cep = '13500-190';
        $regional->telefone = '(19) 3533-1912';
        $regional->fax = '(19) 3533-1912';
        $regional->email = 'corcesprioclaro@core-sp.org.br';
        $regional->funcionamento = 'das 09h às 18h';
        $regional->responsavel = 'Marta';
        $regional->descricao = '<p>Em 1966, de acordo com a Resolução n.° 3 baixada pelo Conselho Regional dos Representantes Comerciais do Estado de São Paulo, são criados escritórios seccionais com a finalidade de orientar o representante quanto ao registro no core-sp, examinar documentação e enviá-la para a sede na capital do estado. A informatização e modernização do escritório possibilitaram, com um sistema integrado, efetuar registros no próprio escritório seccional.</p>
        	<p>O ES de Rio Claro tem como base territorial os seguintes municípios:</p>
        	<p>Rio Claro, Águas de São Pedro, Analândia, Araras, Charqueada, Conchal, Cordeirópolis, Corumbataí, Ipeúna, Iracemápolis, Itirapina, Leme, Limeira, Piracicaba, Pirassununga, Santa Cruz da Conceição, Santa Gertrudes, Santa Maria da Serra e São Pedro.</p>';
        $regional->save();

        // Marília
        $regional = new Regional();
        $regional->prefixo = 'ES12';
        $regional->regional = 'Marília';
        $regional->endereco = 'Rua Bahia';
        $regional->bairro = 'Centro';
        $regional->numero = '165';
        $regional->complemento = '10° andar, Sala 102';
        $regional->cep = '17500-080';
        $regional->telefone = '(14) 3413-1347';
        $regional->fax = '(14) 3413-1347';
        $regional->email = 'corcespmarilia@core-sp.org.br';
        $regional->funcionamento = 'das 09h às 18h';
        $regional->responsavel = 'Aline / Tânia';
        $regional->descricao = '<p>Em 1982, de acordo com a Resolução n.° 131 baixada pelo Conselho Regional dos Representantes Comerciais do Estado de São Paulo, é criado o escritório seccional de Marília, segundo estudos concluídos apontando a crescente demanda de registros na região. Com a finalidade de orientar o representante quanto ao registro no core-sp, examinar documentação e enviá-la para a sede na capital do estado. A informatização e modernização do escritório possibilitaram, com um sistema integrado, efetuar registros no próprio escritório seccional.</p>
        	<p>Fazem parte da base territorial do ES de Marília os seguintes municípios:</p>
        	<p>Marília, Álvaro de Carvalho, Alvinlândia, Arco Íris, Assis, Borá, Campos Novos Paulista, Cândido Mota, Cruzália, Echaporã, Florínia, Garça, Getulina, Guaimbé, Herculândia, Ibirarema, Júlio Mesquita, Lins, Lucianópolis, Lupércio, Lutécia, Maracaí, Ocauçu, Oriente, Ourinhos, Oscar Bressane, Palmital, Paraguaçu Paulista, Pedrinhas Paulista, Platina, Pompéia, Quatá, Queirós, Quintana, Ribeirão do Sul, Salto Grande, São Pedro do Turvo, Tupã, Ubirajara e Vera Cruz.</p>';
        $regional->save();
    }
}
