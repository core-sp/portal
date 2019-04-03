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
        $pagina->slug = "legislacao";
        $pagina->img = "imagens/legislacao.png";
        $pagina->conteudo = "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed lobortis nulla non eleifend ullamcorper. Quisque placerat lacinia euismod. Vestibulum sodales fermentum imperdiet. <strong>Ut elementum condimentum risus vel consectetur. Morbi quis justo ac justo venenatis rutrum vitae non mauris. Morbi pellentesque dui sit amet auctor dictum. Etiam id pulvinar massa. </strong></p><p>Duis ultrices, erat fermentum placerat mattis, justo lacus varius massa, ut ullamcorper massa justo non neque.</p><h4>In viverra est quis hendrerit consectetur.</h4><p>Proin erat nunc, consequat feugiat justo vitae, congue iaculis enim. In blandit maximus odio, eu sollicitudin orci pharetra eget. Morbi leo arcu, finibus at ullamcorper ac, mattis non augue.</p><p>Suspendisse iaculis quam id efficitur lacinia. Pellentesque in neque dui. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum quis lacus fringilla, semper leo ac, lacinia quam.</p><p>Fusce varius erat et lacus scelerisque, quis condimentum ante mollis. Suspendisse eu diam sit amet arcu malesuada bibendum. Ut sed elit vestibulum, laoreet justo nec, viverra erat. Duis sodales lectus et dui pretium pretium. Pellentesque sed viverra lectus, id fermentum velit. Sed quis leo nunc. Vivamus sit amet bibendum urna.</p><p>Duis mattis nec mauris quis porta. Mauris consequat hendrerit molestie. Pellentesque sit amet posuere tortor. Nulla leo nulla, commodo laoreet lobortis id, condimentum ut dui. Nulla et elementum ex.</p><p>Nullam ullamcorper tristique neque et cursus. Fusce molestie lacus arcu, nec ultricies metus egestas eget. Interdum et malesuada fames ac ante ipsum primis in faucibus. Donec vestibulum sed nisi pretium venenatis. Curabitur fringilla feugiat ante nec feugiat. Maecenas faucibus arcu eget leo pharetra, non dignissim turpis porttitor. Integer pretium eros id libero pellentesque iaculis.</p>";
        $pagina->idusuario = 1;
        $pagina->save();

        $pagina = new Pagina();
        $pagina->titulo = "Mapa do Site";
        $pagina->slug = "mapa-do-site";
        $pagina->img = "imagens/institucional.png";
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
    }
}
