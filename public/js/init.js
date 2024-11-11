const link = location.protocol + '//' + location.hostname + '/js/';
const inicio = "modulo-";
let date = new Date();
let tempo = date.setUTCSeconds(60);

// Inicializa o editor Tiny somente se encontra o seletor para editar conteúdo
function initTinyMCE(){

    if($('textarea.my-editor').length <= 0)
        return false;

    // script local de configuração
    const tiny = document.createElement('script');
    tiny.setAttribute("type", "text/javascript");
    tiny.setAttribute("src", link + 'interno/tinymce.js?' + tempo);
    document.getElementById(inicio + "init").after(tiny);

    if($('#app_config').val() == 'local'){
        // app local sem API-KEY (deve ser incluído no DOM antes do script de configuração do tiny local)
        const tiny_local = document.createElement('script');
        tiny_local.setAttribute("type", "text/javascript");
        tiny_local.setAttribute("src", link + 'tinymce/tinymce.min.js');
        document.getElementById(inicio + "init").after(tiny_local);
        return;
    }

    // CDN tiny com API-KEY (deve ser incluído no DOM antes do script de configuração do tiny local)
    const tiny_cloud = document.createElement('script');
    tiny_cloud.setAttribute("src", 'https://cdn.tiny.cloud/1/' + $('#api-tiny').val() + '/tinymce/5/tinymce.min.js');
    tiny_cloud.setAttribute("referrerpolicy", "origin");
    document.getElementById(inicio + "init").after(tiny_cloud);
}

export default function (local = 'interno'){
    
    const modulos_principais = ['mascaras', 'utils', 'filemanager'];
    const pastas_principais = ['', local + '/', local + '/'];

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
    
    initTinyMCE();

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
