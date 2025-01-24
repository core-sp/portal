async function requestCertidao(){

    try {
        let response = await fetch('/representante/emitir-certidao', {
            method: 'POST', 
            headers: {
                'Content-Type': 'application/json;charset=utf-8',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        if(!response.ok)
            throw new Error(response.status + ", <b>Mensagem:</b> " + response.statusText);

        if(response.headers.get("content-type").search('application/pdf') == -1)
            throw new Error("Retorno de arquivo inválido!");

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

        document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
        document.dispatchEvent(new CustomEvent("MSG_GERAL_CONTEUDO", {
            detail: {texto: 'Certidão emitida!', timeout: 1500}
        }));

        $('.emitirCertidaoBtn').html('Emitida!').prop('disabled', true);
        $('.baixarCertidaoBtn').val('Cancelada').prop('disabled', true);

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
}

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}
