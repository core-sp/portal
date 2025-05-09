function tinyInit(link, hash){
    
    if($('.my-editor').length > 0){
        const tiny = document.createElement('script');

        tiny.setAttribute("type", "text/javascript");
        tiny.setAttribute("src", link + 'interno/tinymce.js?' + hash);
        document.body.appendChild(tiny);
    }
}

function securityInit(link){

    if($('[data-modulo-id="security"]').length > 0){
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

export function executar(link, hash){
    
    recaptcha();
    tinyInit(link, hash);
	securityInit(link);
}
