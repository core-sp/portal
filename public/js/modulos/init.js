const link = location.protocol + '//' + location.hostname + '/js/';
const inicio = "modulo-";
const temp = $('#' + inicio + 'init').attr('src');
const hash = temp.substring(temp.search(/\?/) + 1, temp.length);

function tinyInit(){
    
    if($('.my-editor').length > 0){
    
        const tiny = document.createElement('script');
        tiny.setAttribute("type", "text/javascript");
        tiny.setAttribute("src", link + 'interno/tinymce.js?' + hash);
        document.body.appendChild(tiny);

        if(typeof tinymce === "object")
            console.log('Editor TinyMCE carregado.');
    }
}

function securityInit(){

    if($('#modulo-security').length > 0){
        const _lib = document.createElement('script');
        _lib.setAttribute("type", "text/javascript");
        _lib.setAttribute("src", link + 'zxcvbn.js?2017'); // zxcvbn.js?[ano da última atualização]
        _lib.setAttribute("async", true);
        document.body.appendChild(_lib);
    }
}

function criarScriptParaImportar(modulo_atual, obj_modulos = {modulo:[], local:[]}){

    if((obj_modulos === null) || (typeof obj_modulos !== 'object'))
        return; 

    if((Object.keys(obj_modulos).length === 0) || (obj_modulos.modulo.length === 0))
        return;

    obj_modulos.modulo.forEach((element, index) => {
        const script = document.createElement('script');
        script.type = "module";
        script.src = link + obj_modulos.local[index] + element + '.js?' + hash;
        script.id = inicio + element;

        modulo_atual.after(script);
    });
}

function opcionais(){
    
    tinyInit();
    securityInit();

    const opcionais = $('[type="module"][class^="' + inicio + '"]');
  
    if(opcionais.length == 0)
        return false;

    opcionais.each(function(){

        let funcao = $(this).attr('class').replace(inicio, '');
        let modulo = $(this).attr('id').replace(inicio, '');
        
        import($(this).attr('src'))
        .then((module) => {
            console.log('[MÓDULOS] # Módulo de "' + funcao + ' ' + modulo + '" carregado.');
            console.log('[MÓDULOS] # Local do módulo: ' + $(this).attr('src').replace(link, 'js/'));

            if('scripts_para_importar' in module)
                criarScriptParaImportar(this, module.scripts_para_importar);

            module.executar(funcao);
        })
        .catch((err) => {
            console.log(err);
            alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
        });
    
    });
}

function criarImportarModulos(local, modulos_principais, pastas_principais){

    modulos_principais.forEach((element, index) => {
        const script = document.createElement('script');
        script.type = "module";
        script.src = link + pastas_principais[index] + element + '.js?' + hash;
        script.id = inicio + element;
        
        document.getElementById(inicio + "init").after(script);

        let modulo_criado = $('#' + script.id);

        import(modulo_criado.attr('src'))
        .then((module) => {
            console.log('[MÓDULOS] # Módulo principal "' + element + '" carregado, localizado em: ' + modulo_criado.attr('src') + '.');

            if('scripts_para_importar' in module)
                criarScriptParaImportar(modulo_criado, module.scripts_para_importar);
            
            module.executar(local);
        })
        .catch((err) => {
            console.log(err);
            alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
        });
    });

    opcionais();
}

function getObjModulos(){

    return {
        principal: ['mascaras'],
        interno: ['utils', 'filemanager'],
        externo: ['acessibilidade', 'utils', 'modal-geral'],
        "restrita-rc": ['utils'],
    };
}

function getObjPastas(local, subarea){

    const pasta_modulos = 'modulos/';
    const caminho_modulos = local + '/' + pasta_modulos;
    const caminho_modulos_subarea = typeof subarea == "string" ? subarea + '/' + pasta_modulos : '';
    
    return {
        principal: [pasta_modulos],
        interno: [caminho_modulos, caminho_modulos],
        externo: [pasta_modulos, caminho_modulos, pasta_modulos],
        "restrita-rc": [caminho_modulos_subarea],
    };
}

export default function (local = 'interno', subarea = null){

    const executar = {
        ok: function(local, subarea) {            
            let sub = typeof subarea == "string" ? this[subarea] : [];
            return this.principal.concat(this[local]).concat(sub);
        },
    };

    const modulos_ = getObjModulos();
    const pastas_ = getObjPastas(local, subarea);

    criarImportarModulos(local, executar.ok.call(modulos_, local, subarea), executar.ok.call(pastas_, local, subarea));
};
