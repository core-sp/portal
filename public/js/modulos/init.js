const inicio = "modulo-";
const link = PORTAL_MODULOS.getLink_;
const hash = PORTAL_MODULOS.getHash_;
const versao = PORTAL_MODULOS.getVersao_;

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

function opcionais(all){

    $('[type="module"][class^="' + inicio + '"]').each(function(){
        all.push($(this));
    });

    return all;
}

function criarModulos(modulos_principais, pastas_principais){

    return opcionais(modulos_principais.map((element, index) => {
        const script = document.createElement('script');
        script.type = "module";
        script.src = link + pastas_principais[index] + element + '.js?' + hash;
        script.id = inicio + element;
        
        document.getElementById(inicio + "init").after(script);

        return $('#' + script.id);
    }));
}

async function importarModulos(local, all){

    Promise.all(all.map((e) => import(e.attr('src'))))
    .then((modulos) => {
        modulos.forEach((modulo, index) => {
            let temp = all[index].attr('class');
            let e_opcional = (temp !== undefined) && (temp.length > 0);

            if('scripts_para_importar' in modulo)
                criarScriptParaImportar(all[index], modulo.scripts_para_importar);

            document.dispatchEvent(new CustomEvent("LOG_SUCCESS_INIT", {
                detail: {
                    tipo: 0, 
                    situacao: e_opcional ? 2 : 1, 
                    nome: all[index].attr('id').replace(inicio, ''), 
                    url: all[index].attr('src')
                }
            }));

            e_opcional ? modulo.executar(temp.replace(inicio, '')) : modulo.executar(local);
        });
    })
    .catch((err) => {
        document.dispatchEvent(new CustomEvent("LOG_ERROR_INIT", {
            detail: {error: err}
        }));
    });
}

function gerarLogs(){

    console.log('[MÓDULOS / SCRIPTS] # Versão dos módulos / scripts: ' + versao);

    document.addEventListener("LOG_SUCCESS_INIT", (e) => {
        // tipo = chave do array
        // situacao = chave do array
        // nome = string
        // url = modulo.src ou script.src

        const tipos = ['MÓDULOS', 'SCRIPTS'];
        const tipos_min = ['Módulo', 'Script'];
        const situacoes = ['', 'principal', 'opcional', 'importado por principal', 'importado por opcional', 'carregado'];
        const primeiro = '[' + tipos[e.detail.tipo].toUpperCase() + '] # ' + tipos_min[e.detail.tipo] + ' ';
        const segundo = ' ' + situacoes[e.detail.situacao] + ', localizado em: ';

        console.log(primeiro + '"' + e.detail.nome + '"' + segundo + e.detail.url);
    });

    document.addEventListener("LOG_ERROR_INIT", (e) => {
        console.log(e.detail.error);
        alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
    });
}

export default function (local, subarea){
    
    const executar = {
        ok: function(local, subarea) {
            let sub = typeof subarea == "string" ? this[subarea] : [];
            return this.principal.concat(this[local]).concat(sub);
        },
    };

    document.dispatchEvent(new CustomEvent('PRE-INIT', {
        detail: {local: local, subarea: subarea}
    }));
    
    const modulos_ = PORTAL_MODULOS.getObjModulos_;
    const pastas_ = PORTAL_MODULOS.getObjPastas_;

    gerarLogs();

    importarModulos(local, 
        criarModulos(executar.ok.call(modulos_, local, subarea), executar.ok.call(pastas_, local, subarea))
    );
};
