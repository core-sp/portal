"use strict";

$(document).ready(function(){

    let elemento_init = $('#modulo-init');

    import(elemento_init.attr('src'))
    .then((init) => {
        let subarea = window.location.pathname.search('/representante/') > -1 ? 'restrita-rc' : null;

        init.default('externo', subarea);
        init.opcionais();
        console.log('[MÓDULOS] # Versão dos scripts: ' + elemento_init.attr('class'));
    })
    .catch((err) => {
        console.log(err);
        alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
    });

});
