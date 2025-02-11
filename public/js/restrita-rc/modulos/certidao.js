function acaoFinal(baixar = false){

    let txt = baixar ? 'Download da Certidão realizado.' : 'Certidão emitida.';

    document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
    document.dispatchEvent(new CustomEvent("MSG_GERAL_CONTEUDO", {
        detail: {texto: txt, timeout: 1500}
    }));

    if(baixar)
        return;

    $('.emitirCertidaoBtn').html('Emitida!').prop('disabled', true);
    $('.baixarCertidaoBtn').html('Cancelada').prop('disabled', true);
}

async function requisicao(baixar = false){

    return baixar ? await fetch('/representante/baixar-certidao?numero=' + $('input[name="numero"]').val(), {
            method: 'GET', 
            headers: {
                'Content-Type': 'application/json;charset=utf-8',
            }
        }) : 
        await fetch('/representante/emitir-certidao', {
            method: 'POST', 
            headers: {
                'Content-Type': 'application/json;charset=utf-8',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
}

async function requestCertidao(baixar = false){

    try {
        let response = await requisicao(baixar);

        if(!response.ok)
            throw new Error(response.status + ", <b>Mensagem:</b> " + response.statusText);

        if(response.headers.get("content-type").search('application/pdf') == -1){
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

        acaoFinal(baixar);

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

function visualizar(){

    $('.emitirCertidaoBtn').on('click', function(){
        document.dispatchEvent(new CustomEvent("MSG_GERAL_CARREGAR"));
        requestCertidao();

        // $('.emitirCertidaoBtn').hide();
        // $('.baixarCertidaoBtn').hide();
    });

    $('.baixarCertidaoBtn').on('click', function(){
        document.dispatchEvent(new CustomEvent("MSG_GERAL_CARREGAR"));
        requestCertidao(true);
    });
}

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}
