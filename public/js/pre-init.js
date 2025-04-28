// Definir configurações de constantes para a inicialização

const PORTAL_MODULOS = new Object({
    getLink_: location.protocol + '//' + location.hostname + '/js/',
    getHash_: $('#pre-init').attr('src').replace(this.getLink_ + 'pre-init.js?', ''),
    getVersao_: $('#pre-init').attr('class'),
});

// Evento para receber o local e a subarea antes de inicializar os módulos / scripts

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

    const script = document.createElement('script');
    script.setAttribute('type', "module");
    script.setAttribute('src', PORTAL_MODULOS.getLink_ + 'modulos/init.js?' + PORTAL_MODULOS.getHash_);
    script.setAttribute('id', 'modulo-init');

    $('#pre-init').after(script);
}

criarInit();
