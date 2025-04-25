"use strict";

$(document).ready(function(){

    let elemento_init = $('#modulo-init');

    import(elemento_init.attr('src'))
    .then((init) => {
        let subarea = window.location.pathname.search('/representante/') > -1 ? 'restrita-rc' : null;

        init.default('externo', subarea);
        document.dispatchEvent(new CustomEvent("LOG_SUCCESS_INIT", {
            detail: {tipo: 0, situacao: 1, nome: 'init', url: elemento_init.attr('src')}
        }));
    })
    .catch((err) => {
        document.dispatchEvent(new CustomEvent("LOG_ERROR_INIT", {
            detail: {error: err}
        }));
    });

});
