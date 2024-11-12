const link = location.protocol + '//' + location.hostname + '/js/';
const inicio = "modulo-";

export default function (local = 'interno'){

    let date = new Date();
    let tempo = date.setUTCSeconds(60);
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
