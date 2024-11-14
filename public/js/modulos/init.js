const link = location.protocol + '//' + location.hostname + '/js/';
const inicio = "modulo-";
let date = new Date();
let tempo = date.setUTCSeconds(60);

function tinyInit(){
    
    if($('.my-editor').length > 0){
    
        const tiny = document.createElement('script');
        tiny.setAttribute("type", "text/javascript");
        tiny.setAttribute("src", link + 'interno/tinymce.js?' + tempo);
        document.body.appendChild(tiny);
    }
}

export default function (local = 'interno'){

    const modulos_principais = ['mascaras', 'utils', 'filemanager'];
    const pasta_modulos = 'modulos/';
    const caminho_modulos = local + '/' + pasta_modulos;
    const pastas_principais = [pasta_modulos, caminho_modulos, caminho_modulos];

    for (let i in modulos_principais) {

        const script = document.createElement('script');
        script.type = "module";
        script.src = link + pastas_principais[i] + modulos_principais[i] + '.js?' + tempo;
        script.id = inicio + modulos_principais[i];
        
        document.getElementById(inicio + "init").after(script);

        import($('#' + inicio + modulos_principais[i]).attr('src'))
        .then((module) => {
            console.log('Módulo principal "' + modulos_principais[i] + '" carregado.');
            module.executar(local);
        })
        .catch((err) => {
            console.log(err);
            alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
        });
    }
};

export function opcionais(){
    
    tinyInit();

    // inicializa os módulos opcionais

    const opcionais = $('[type="module"][class^="' + inicio + '"]');
  
    if(opcionais.length == 0)
        return false;

    console.log('Total de módulos opcionais carregados na atual página: ' + opcionais.length);
    opcionais.each(function(){

        let funcao = $(this).attr('class').replace(inicio, '');
        let modulo = $(this).attr('id').replace(inicio, '');
        
        import($(this).attr('src'))
        .then((module) => {
            console.log('Módulo de "' + funcao + ' ' + modulo + '" carregado.');
            console.log('Local do módulo: ' + $(this).attr('src').replace(link, 'js/'));
            module.executar(funcao);
        })
        .catch((err) => {
            console.log(err);
            alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
        });
    
    });
};
