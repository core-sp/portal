function tinyInit(link, hash){
    
    if($('.my-editor').length > 0){
        const chave = $('span[id^="chave-tiny-"]');
        const origem = chave.length == 0 ? 
            link + 'tinymce/tinymce.min.js' : 
            'https://cdn.tiny.cloud/1/' + chave.attr('id').replace('chave-tiny-', '') + '/tinymce/5/tinymce.min.js';

        const tiny_p = document.createElement('script');
        tiny_p.setAttribute("src", origem);

        if(chave.length == 0)
            tiny_p.setAttribute("type", "text/javascript");
        
        if(chave.length > 0)
            tiny_p.setAttribute("referrerpolicy", "origin");

        tiny_p.onload = function(){
            const tiny = document.createElement('script');

            tiny.setAttribute("type", "text/javascript");
            tiny.setAttribute("src", link + 'interno/tinymce.js?' + hash);
            document.body.appendChild(tiny);
        }

        document.body.appendChild(tiny_p);
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

    $(document).on('LIB_GRAFICO', function(e){

        if($('#lib-d3, #lib-billboard, #lib-billboard-css, #lib-jspdf').length == 4){
            e.detail.funcao();
            return;
        }

        const _lib2 = document.createElement('script');

        _lib2.setAttribute("src", 'https://cdnjs.cloudflare.com/ajax/libs/billboard.js/3.16.0/billboard.pkgd.min.js');
        _lib2.setAttribute("integrity", 'sha512-ClwsK/z1FXxUxpPcadvKKdjGIt9a3D4uSAD/hfAXUwH1+XjWesMJAnlJMSvxUwMchXms5dUygkuzgiSMfbDfzQ==');
        _lib2.setAttribute("crossorigin", 'anonymous');
        _lib2.setAttribute("referrerpolicy", 'no-referrer');
        _lib2.setAttribute("id", 'lib-billboard');

        _lib2.onload = function(){
            const _lib1 = document.createElement('script');

            _lib1.setAttribute("src", 'https://cdnjs.cloudflare.com/ajax/libs/d3/7.9.0/d3.min.js');
            _lib1.setAttribute("integrity", 'sha512-vc58qvvBdrDR4etbxMdlTt4GBQk1qjvyORR2nrsPsFPyrs+/u5c3+1Ct6upOgdZoIl7eq6k3a1UPDSNAQi/32A==');
            _lib1.setAttribute("crossorigin", 'anonymous');
            _lib1.setAttribute("referrerpolicy", 'no-referrer');
            _lib1.setAttribute("id", 'lib-d3');

            _lib1.onload = function(){
                e.detail.funcao();
            }

            document.head.appendChild(_lib1);
        }

        document.head.appendChild(_lib2);

        const _lib3 = document.createElement('link');

        _lib3.setAttribute("rel", 'stylesheet');
        _lib3.setAttribute("href", 'https://cdnjs.cloudflare.com/ajax/libs/billboard.js/3.16.0/billboard.min.css');
        _lib3.setAttribute("integrity", 'sha512-njOj5MWC/MCTRlxjIsftPrGL3nuexguTsEN1qE8eLU6LZW1ZyccshJ1a2QneVxsyeDjbEqXl9TJNfCFUj8pDDg==');
        _lib3.setAttribute("crossorigin", 'anonymous');
        _lib3.setAttribute("referrerpolicy", 'no-referrer');
        _lib3.setAttribute("id", 'lib-billboard-css');
        document.head.appendChild(_lib3);

        // Gerar PDF do gráfico
        const _lib4 = document.createElement('script');

        _lib4.setAttribute("src", 'https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js');
        _lib4.setAttribute("id", 'lib-jspdf');
        document.head.appendChild(_lib4);
    });
}

function galeria(){

    $(document).on('LIB_GALERIA', function(e){

        if($('#lib-ekko-css, #lib-ekko').length == 2){
            e.detail.funcao(e.detail.propriedade);
            return;
        }

        const el = document.createElement('link');

        el.setAttribute("rel", "stylesheet");
        el.setAttribute("href", 'https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.css');
        el.setAttribute("integrity", "sha512-Velp0ebMKjcd9RiCoaHhLXkR1sFoCCWXNp6w4zj1hfMifYB5441C+sKeBl/T/Ka6NjBiRfBBQRaQq65ekYz3UQ==");
        el.setAttribute("crossorigin", "anonymous");
        el.setAttribute("referrerpolicy", "no-referrer");
        el.setAttribute("id", "lib-ekko-css");
        document.head.appendChild(el);

        const el_js = document.createElement('script');

        el_js.setAttribute("src", 'https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.min.js');
        el_js.setAttribute("integrity", 'sha512-Y2IiVZeaBwXG1wSV7f13plqlmFOx8MdjuHyYFVoYzhyRr3nH/NMDjTBSswijzADdNzMyWNetbLMfOpIPl6Cv9g==');
        el_js.setAttribute("crossorigin", 'anonymous');
        el_js.setAttribute("referrerpolicy", 'no-referrer');
        el_js.setAttribute("id", 'lib-ekko');

        el_js.onload = function(){
            e.detail.funcao(e.detail.propriedade);
        }
        document.body.appendChild(el_js);
    });
}

export function executar(link, hash){
    
    galeria();
    graficos();
    recaptcha();
    tinyInit(link, hash);
	securityInit(link);
}
