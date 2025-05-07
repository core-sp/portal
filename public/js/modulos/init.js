const attr_id = 'data-modulo-id';
const attr_acao = 'data-modulo-acao';
const link = PORTAL_MODULOS.getLink_;
const hash = PORTAL_MODULOS.getHash_;
const versao = PORTAL_MODULOS.getVersao_;

function chaveMap(script){
    return script.src + '|' + script.dataset.moduloId + '|' + script.dataset.moduloAcao;
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
        script.setAttribute(attr_id, element);

        if($('script[src="' + script.src + '"]').length == 0)
            modulo_atual.after(script);
    });
}

function opcionais(all){

    $('[type="module"][' + attr_acao + ']').each(function(){
        all.set(chaveMap(this), this);
    });

    return all;
}

function criarModulos(modulos_principais, pastas_principais){

    return Array.from(
        opcionais(new Map(
            modulos_principais.map((element, index) => {
                const script = document.createElement('script');
                script.type = "module";
                script.src = link + pastas_principais[index] + element + '.js?' + hash;
                script.setAttribute(attr_id, element);
                
                document.getElementById("modulo-init").after(script);

                return [chaveMap(script), script];
            })
        ))
    .values());
}

async function importarModulos(local, all){

    Promise.all(all.map(e => import(e.src)))
    .then((modulos) => {
        modulos.forEach((modulo, index) => {
            let temp = all[index].dataset.moduloAcao;
            let e_opcional = (temp !== undefined) && (temp.length > 0);
            let msg_acao = e_opcional ? ' --> ' + temp : '';

            if('scripts_para_importar' in modulo)
                criarScriptParaImportar(all[index], modulo.scripts_para_importar);

            document.dispatchEvent(new CustomEvent("LOG_SUCCESS_INIT", {
                detail: {
                    tipo: 0, 
                    situacao: e_opcional ? 2 : 1, 
                    nome: all[index].dataset.moduloId + msg_acao, 
                    url: all[index].src
                }
            }));

            e_opcional ? modulo.executar(temp) : modulo.executar(local);
        });
    })
    .catch((err) => {
        document.dispatchEvent(new CustomEvent("LOG_ERROR_INIT", {
            detail: {error: err}
        }));
    })
    .finally(() => { 
        all = undefined;
        ["getObjModulos_", "getObjPastas_"].forEach(
            obj => Object.keys(PORTAL_MODULOS[obj])
            .forEach(chaves => PORTAL_MODULOS[obj][chaves] = undefined)
        );
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
    
    gerarLogs();

    importarModulos(local, 
        criarModulos(
            executar.ok.call(PORTAL_MODULOS.getObjModulos_, local, subarea), 
            executar.ok.call(PORTAL_MODULOS.getObjPastas_, local, subarea)
        )
    );
};
