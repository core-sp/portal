function desabilitaHabilitaCampoAdd(valor){
    let desabilita = valor == '0';
    let obr = !desabilita;

    if(desabilita)
        $('select[name="campo_rotulo"] option[value=""]').prop('selected', true);

    $('select[name="campo_rotulo"], select[name="campo_required"]').prop('disabled', desabilita).prop('required', obr);
}

function acaoFinal(){

    let txt = 'Download dos inscritos realizado.';

    document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
    document.dispatchEvent(new CustomEvent("MSG_GERAL_CONTEUDO", {
        detail: {texto: txt, timeout: 2000}
    }));
}


async function requisicao(id){

    return await fetch('/admin/cursos/inscritos/download/' + id, {
        method: 'GET', 
        headers: {
            'Content-Type': 'application/json;charset=utf-8',
        }
    });
}

async function requestCSV(id){

    try {
        let response = await requisicao(id);

        if(!response.ok)
            throw new Error(response.status + ", <b>Mensagem:</b> " + response.statusText);

        if(response.headers.get("content-type").search('text/csv') == -1){
            let json = await response.json();

            document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
            document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
                detail: {titulo: '<i class="fas fa-times text-danger"></i> ' + json.titulo, texto: json.mensagem}
            }));
            
            return;
        }

        let blob = await response.blob();
        const cd = response.headers.get("content-disposition");
        const nome = cd.substring(cd.lastIndexOf('=') + 1);
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");

        a.style.display = "none";
        a.href = url;
        a.download = nome;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);

        acaoFinal();

    }catch(erro){
        document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
        document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
            detail: {
                titulo: '<i class="fas fa-times text-danger"></i> Erro!', 
                texto: '<span class="text-danger">' + erro + '</span>'
            }
        }));
        console.log(erro);
    }
}

function editar(){

    if($('select[name="add_campo"]').length > 0)
        desabilitaHabilitaCampoAdd($('select[name="add_campo"]').val());
    
    $('select[name="add_campo"]').change(function(){
        desabilitaHabilitaCampoAdd($(this).val());
    });

    $('.downloadCSV').on('click', function(){
        document.dispatchEvent(new CustomEvent("MSG_GERAL_CARREGAR"));
        requestCSV(this.id);
    });
};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
