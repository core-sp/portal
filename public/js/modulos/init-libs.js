function calendario(){

    if($('#agenda-institucional, #datepicker').length > 0){
        const dp = document.createElement('link');

        dp.setAttribute("type", "text/css");
        dp.setAttribute("rel", "stylesheet");
        dp.setAttribute("href", location.origin + '/css/datepicker.css');
        document.head.appendChild(dp);
    }
}

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

function graficos(){

    if($('[class^="grafico-"]').length > 0){
        // Gerar PDF do gráfico
        const _lib = document.createElement('script');

        _lib.setAttribute("src", 'https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js');
        document.head.appendChild(_lib);

        const _lib1 = document.createElement('script');

        _lib1.setAttribute("src", 'https://d3js.org/d3.v6.min.js');
        document.head.appendChild(_lib1);

        const _lib2 = document.createElement('script');

        _lib2.setAttribute("src", 'https://cdnjs.cloudflare.com/ajax/libs/billboard.js/3.14.3/billboard.pkgd.min.js');
        _lib2.setAttribute("integrity", 'sha512-1VwrVSqZ+HSX8ZyO3Z+vKTlMM9BiUXbSnQL2JAnLdGL2DVoG+XJTXJEvtZGMt37ymAsP/DZXQStBRIep81mSQA==');
        _lib2.setAttribute("crossorigin", 'anonymous');
        _lib2.setAttribute("referrerpolicy", 'no-referrer');
        document.head.appendChild(_lib2);

        const _lib3 = document.createElement('link');

        _lib3.setAttribute("rel", 'stylesheet');
        _lib3.setAttribute("href", 'https://cdnjs.cloudflare.com/ajax/libs/billboard.js/3.14.3/billboard.min.css');
        _lib3.setAttribute("integrity", 'sha512-+iioEOUqup3ddDw5CE3m87DWYtXSJrYphQzP5sYHdJCcvMULwEelaPgWBV4TFu+XQmL9ZEhfPUvgFWgbQ0hocQ==');
        _lib3.setAttribute("crossorigin", 'anonymous');
        _lib3.setAttribute("referrerpolicy", 'no-referrer');
        document.head.appendChild(_lib3);
    }
}

function galeria(){

    if($('[data-toggle="lightbox"]').length > 0){
        const el = document.createElement('link');

        el.setAttribute("rel", "stylesheet");
        el.setAttribute("href", 'https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.css');
        el.setAttribute("integrity", "sha512-Velp0ebMKjcd9RiCoaHhLXkR1sFoCCWXNp6w4zj1hfMifYB5441C+sKeBl/T/Ka6NjBiRfBBQRaQq65ekYz3UQ==");
        el.setAttribute("crossorigin", "anonymous");
        el.setAttribute("referrerpolicy", "no-referrer");
        document.head.appendChild(el);

        const el_js = document.createElement('script');

        el_js.setAttribute("src", 'https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.min.js');
        el_js.setAttribute("integrity", 'sha512-Y2IiVZeaBwXG1wSV7f13plqlmFOx8MdjuHyYFVoYzhyRr3nH/NMDjTBSswijzADdNzMyWNetbLMfOpIPl6Cv9g==');
        el_js.setAttribute("crossorigin", 'anonymous');
        el_js.setAttribute("referrerpolicy", 'no-referrer');
        document.body.appendChild(el_js);
    }
}

export function executar(link, hash){
    
    calendario();
    galeria();
    graficos();
    recaptcha();
    tinyInit(link, hash);
	securityInit(link);
}
