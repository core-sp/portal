const extensao = {
    image_gif: ["0,474946383761", "0,474946383961"],
    image_jpeg: ["0,FFD8"],
    image_png: ["0,89504E470D0A1A0A"],
};

function readBuffer(arquivo, start = 0, end = 2) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = () => {
            resolve(reader.result);
        };
        reader.onerror = reject;
        reader.readAsArrayBuffer(arquivo.slice(start, end));
    });
}

async function verificaMagicNumber(arquivo, arquivo_mimeType) {

    for (const mime of extensao[arquivo_mimeType]) {

        const index_offset = mime.search(',');
        const magic_number = mime.substr(index_offset + 1, mime.length).toLowerCase();
        const offset = parseInt(mime.substr(0, index_offset));
        const len = offset + 8;
        const buffers = await readBuffer(arquivo, offset, len);
        const uint = new Uint8Array(buffers);
        const hex = Array.prototype.map.call(uint, x => ('00' + x.toString(16)).slice(-2)).join('');

        if(hex.substr(0, magic_number.length) == magic_number)
            return true;
    }

    return 'Arquivo não possui conteúdo compatível com a extensão. A extensão pode ter sido renomeada';
}

export function validarUmArquivo(arquivo, mimeType = [], tamanho = 2048){

    if((arquivo === undefined) || (arquivo === null))
        return 'Arquivo inexistente';
      
    if(Math.round((arquivo.size / 1024)) > tamanho)
        return 'Arquivo com tamanho de ' + tamanho + ' MB excedido';

    if(mimeType.length == 0)
        return true;

    if(mimeType.indexOf(arquivo.type) == -1)
        return 'Arquivo com extensão não permitida';

    let arquivo_mimeType = arquivo.type.replace('/', '_');

    if(!(arquivo_mimeType in extensao))
        return 'Não foi possível validar a extensão';

    return verificaMagicNumber(arquivo, arquivo_mimeType);
}
