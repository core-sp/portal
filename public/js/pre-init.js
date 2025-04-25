// Definir os módulos e o caminho dos módulos

const PORTAL_MODULOS = new Object();

document.addEventListener("PRE-INIT", (e) => {

    Object.defineProperty(PORTAL_MODULOS, "getObjModulos_", {
        value: {
            principal: ['init-libs', 'mascaras', 'modal-geral'],
            interno: ['utils', 'filemanager'],
            externo: ['acessibilidade', 'utils'],
            "restrita-rc": ['utils'],
        },
    });

    const pasta_modulos = 'modulos/';
    const caminho_modulos = e.detail.local + '/' + pasta_modulos;
    const caminho_modulos_subarea = typeof e.detail.subarea == "string" ? e.detail.subarea + '/' + pasta_modulos : '';

    Object.defineProperty(PORTAL_MODULOS, "getObjPastas_", {
        value: {
            principal: [pasta_modulos, pasta_modulos, pasta_modulos],
            interno: [caminho_modulos, caminho_modulos],
            externo: [pasta_modulos, caminho_modulos],
            "restrita-rc": [caminho_modulos_subarea],
        },
    });
});

// Cria dinâmicamente a tag <script> do módulo init.js que inicializa tudo.

function criarInit(){

    const pre = 'pre-init';
    let comeco = location.protocol + '//' + location.hostname + '/js/';
    let hash_ = $('#' + pre).attr('src').replace(comeco + pre + '.js?', '');

    const script = document.createElement('script');
    script.setAttribute('type', "module");
    script.setAttribute('src', comeco + 'modulos/init.js?' + hash_);
    script.setAttribute('id', 'modulo-init');
    script.setAttribute('class', $('#' + pre).attr('class'));

    $('#' + pre).after(script);
}

criarInit();
