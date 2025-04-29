function tinyInit(link, hash){
    
    if($('.my-editor').length > 0){
        const tiny = document.createElement('script');

        tiny.setAttribute("type", "text/javascript");
        tiny.setAttribute("src", link + 'interno/tinymce.js?' + hash);
        document.body.appendChild(tiny);

        if(typeof tinymce === "object")
            document.dispatchEvent(new CustomEvent("LOG_SUCCESS_INIT", {
                detail: {tipo: 1, situacao: 5, nome: 'TinyMCE Editor', url: tiny.src}
            }));
    }
}

function securityInit(link){

    if($('#modulo-security').length > 0){
        const _lib = document.createElement('script');

        _lib.setAttribute("type", "text/javascript");
        _lib.setAttribute("src", link + 'zxcvbn.js?2017'); // zxcvbn.js?[ano da última atualização]
        _lib.setAttribute("async", true);
        document.body.appendChild(_lib);
    }
}

function recaptcha(){

    if($('#captcha').length > 0){
        const _lib = document.createElement('script');

        _lib.setAttribute("type", "text/javascript");
        _lib.setAttribute("src", 'https://www.google.com/recaptcha/api.js?hl=pt-BR');
        _lib.setAttribute("async", true);
        document.head.appendChild(_lib);

        let observer = new MutationObserver(mutationRecords => {
            $('#captcha').attr('class', 'g-recaptcha');
        });

        observer.observe($('#captcha')[0], {childList: true});
    }
}

export function executar(funcao){
    
	$(document).on('INIT-LIBS', function(e){
        recaptcha();
        tinyInit(e.detail.link, e.detail.hash);
		securityInit(e.detail.link);
    });
}
