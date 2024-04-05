// TinyMCE com File Manager
var public_path = "{{ path('public') }}";  
var css_galeria_popup = ['#tinyGaleria div.g-popup:not(:has([data-gallery]))', '#tinyGaleria :not(.g-popup)'];
var editor_config = {
  license_key: 'gpl',
  path_absolute : "/",
  selector: "textarea.my-editor",
  language: "pt_BR",
  plugins: [
    "advlist autolink lists link image charmap print preview hr anchor pagebreak",
    "searchreplace wordcount visualblocks visualchars code fullscreen",
    "insertdatetime media nonbreaking save table contextmenu directionality",
    "emoticons template paste textcolor colorpicker textpattern"
  ],
  toolbar: "insertfile undo redo | fontsizeselect | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | btnImgPopup",
  image_caption: true,
  relative_urls: false,
  file_picker_callback : function(callback, value, meta) {
    var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
    var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;
    var cmsURL = editor_config.path_absolute + 'laravel-filemanager?editor=tinymce5';
    if(meta.filetype == 'image')
      cmsURL = cmsURL + "&type=Images";
    else
      cmsURL = cmsURL + "&type=Files";
    tinyMCE.activeEditor.windowManager.openUrl({
      url : cmsURL,
      title : 'Filemanager',
      width : x * 0.8,
      height : y * 0.8,
      resizable : "yes",
      close_previous : "no",
      onMessage: (api, message) => {
        callback(message.content);
      }
    });
  },
  setup: function(editor) {
    editor.on('init', function() {
      if(window.location.href.indexOf('admin/posts/create') != -1)
        editor.execCommand('JustifyFull');
      if(window.location.href.indexOf('admin/textos/') != -1)
        editor.editorContainer.style.height = '400px';
      // remove o que não contem imagem quando possui galeria-popup
      if(editor.dom.get("tinyGaleria") != null)
        css_galeria_popup.forEach(function(texto){ 
          editor.dom.remove(editor.dom.select(texto));
        });
    });

    editor.ui.registry.addButton('btnImgPopup', {
      text: 'Imagem pop-up',
      tooltip: 'Selecione a imagem no texto e clique aqui para alterar a imagem para modo pop-up',
      onAction: function () {
        var galeria = editor.dom.get("tinyGaleria");
        var img = editor.selection.getNode();
        var txt = '';

        if(img.hasAttribute("src") && img.parentNode.hasAttribute("data-gallery"))
          txt = 'Imagem já está como pop-up!';
        else if(img.hasAttribute("src") && !img.parentNode.hasAttribute("data-gallery")){
          var img_src = img.getAttribute("src");
          editor.selection.getNode().remove();
          var html = '<div class="col-sm-6 g-popup">';
          html += '<a href="' + img_src + '" target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="eventorc" class="g-popup"> ';
          html += '<img src="' + img_src + '" alt="Core-SP" class="g-popup" /> ';
          html += '</a></div>';
          if(galeria === null){
            editor.insertContent('<div id="tinyGaleria" class="row" style="border-color: red; border-top-style: solid; border-bottom-style: solid;"></div><p></p>');
            galeria = editor.dom.get("tinyGaleria");
          }
          galeria.insertAdjacentHTML("beforeend", html);
          txt = 'Imagem foi adicionada para abrir como pop-up! <br>Insira texto antes ou depois da marcação vermelha. <br>A marcação vermelha não será visível no site. <br>Para desfazer, apague a imagem.';
        }

        // remove o que não contem imagem
        css_galeria_popup.forEach(function(texto){ 
          editor.dom.remove(editor.dom.select(texto));
        });

        // Mensagem informando que já foi adiconada a imagem
        if(txt != '')
          editor.windowManager.open({
            title: 'Imagem pop-up',
            body: {
              type: 'panel',
              items: [
                {
                  type: 'htmlpanel',
                  html: txt,
                }
              ]
            },
            buttons: [
              {
                type: 'cancel',
                text: 'Fechar'
              }
            ]
          });
      }
    });
  },
  init_instance_callback: function(editor) {
    // Aqui justifica o texto que o usuário "cola" tanto ao criar ou editar o post
    editor.on('SetContent', function(e) {
      if(window.location.href.indexOf('admin/posts/') != -1){
        editor.execCommand('SelectAll');
        editor.execCommand('JustifyFull');
        // caso o justificado já esteja selecionado, o comando acima irá tirar o justificado de tudo,
        // então o comando abaixo identifica que não tem justificado selecionado e justifica novamente.
        if(!editor.queryCommandState('JustifyFull'))
          editor.execCommand('JustifyFull');
        editor.selection.collapse();
      }
    });

    editor.on('focusout', function() {
      // remove o que não contem imagem quando possui galeria-popup
      if(editor.dom.get("tinyGaleria") != null)
        css_galeria_popup.forEach(function(texto){ 
          editor.dom.remove(editor.dom.select(texto));
        });
    });
  }

  // file_browser_callback : function(field_name, url, type, win) {
  //   var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
  //   var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;

  //   var cmsURL = editor_config.path_absolute + 'laravel-filemanager?field_name=' + field_name;
  //   if (type == 'image') {
  //     cmsURL = cmsURL + "&type=Images";
  //   } else {
  //     cmsURL = cmsURL + "&type=Files";
  //   }

  //   tinyMCE.activeEditor.windowManager.open({
  //     file : cmsURL,
  //     title : 'Filemanager',
  //     width : x * 0.8,
  //     height : y * 0.8,
  //     resizable : "yes",
  //     close_previous : "no",
  //   });
};

tinymce.init(editor_config);

// Tradução do TinyMCE
tinymce.addI18n('pt_BR',{
  "Redo": "Refazer",
  "Undo": "Desfazer",
  "Cut": "Recortar",
  "Copy": "Copiar",
  "Paste": "Colar",
  "Select all": "Selecionar tudo",
  "New document": "Novo documento",
  "Ok": "Ok",
  "Cancel": "Cancelar",
  "Visual aids": "Ajuda visual",
  "Bold": "Negrito",
  "Italic": "It\u00e1lico",
  "Underline": "Sublinhar",
  "Strikethrough": "Riscar",
  "Superscript": "Sobrescrito",
  "Subscript": "Subscrever",
  "Clear formatting": "Limpar formata\u00e7\u00e3o",
  "Align left": "Alinhar \u00e0 esquerda",
  "Align center": "Centralizar",
  "Align right": "Alinhar \u00e0 direita",
  "Justify": "Justificar",
  "Bullet list": "Lista n\u00e3o ordenada",
  "Numbered list": "Lista ordenada",
  "Decrease indent": "Diminuir recuo",
  "Increase indent": "Aumentar recuo",
  "Close": "Fechar",
  "Formats": "Formatos",
  "Your browser doesn't support direct access to the clipboard. Please use the Ctrl+X\/C\/V keyboard shortcuts instead.": "Seu navegador n\u00e3o suporta acesso direto \u00e0 \u00e1rea de transfer\u00eancia. Por favor use os atalhos Ctrl+X - C - V do teclado",
  "Headers": "Cabe\u00e7alhos",
  "Header 1": "Cabe\u00e7alho 1",
  "Header 2": "Cabe\u00e7alho 2",
  "Header 3": "Cabe\u00e7alho 3",
  "Header 4": "Cabe\u00e7alho 4",
  "Header 5": "Cabe\u00e7alho 5",
  "Header 6": "Cabe\u00e7alho 6",
  "Headings": "Cabe\u00e7alhos",
  "Heading 1": "Cabe\u00e7alho 1",
  "Heading 2": "Cabe\u00e7alho 2",
  "Heading 3": "Cabe\u00e7alho 3",
  "Heading 4": "Cabe\u00e7alho 4",
  "Heading 5": "Cabe\u00e7alho 5",
  "Heading 6": "Cabe\u00e7alho 6",
  "Preformatted": "Preformatado",
  "Div": "Div",
  "Pre": "Pre",
  "Code": "C\u00f3digo",
  "Paragraph": "Par\u00e1grafo",
  "Blockquote": "Aspas",
  "Inline": "Em linha",
  "Blocks": "Blocos",
  "Paste is now in plain text mode. Contents will now be pasted as plain text until you toggle this option off.": "O comando colar est\u00e1 agora em modo texto plano. O conte\u00fado ser\u00e1 colado como texto plano at\u00e9 voc\u00ea desligar esta op\u00e7\u00e3o.",
  "Font Family": "Fonte",
  "Font Sizes": "Tamanho",
  "Class": "Classe",
  "Browse for an image": "Procure uma imagem",
  "OR": "OU",
  "Drop an image here": "Arraste uma imagem aqui",
  "Upload": "Carregar",
  "Block": "Bloco",
  "Align": "Alinhamento",
  "Default": "Padr\u00e3o",
  "Circle": "C\u00edrculo",
  "Disc": "Disco",
  "Square": "Quadrado",
  "Lower Alpha": "a. b. c. ...",
  "Lower Greek": "\u03b1. \u03b2. \u03b3. ...",
  "Lower Roman": "i. ii. iii. ...",
  "Upper Alpha": "A. B. C. ...",
  "Upper Roman": "I. II. III. ...",
  "Anchor": "\u00c2ncora",
  "Name": "Nome",
  "Id": "Id",
  "Id should start with a letter, followed only by letters, numbers, dashes, dots, colons or underscores.": "Id deve come\u00e7ar com uma letra, seguido apenas por letras, n\u00fameros, tra\u00e7os, pontos, dois pontos ou sublinhados.",
  "You have unsaved changes are you sure you want to navigate away?": "Voc\u00ea tem mudan\u00e7as n\u00e3o salvas. Voc\u00ea tem certeza que deseja sair?",
  "Restore last draft": "Restaurar \u00faltimo rascunho",
  "Special character": "Caracteres especiais",
  "Source code": "C\u00f3digo fonte",
  "Insert\/Edit code sample": "Inserir\/Editar c\u00f3digo de exemplo",
  "Language": "Idioma",
  "Code sample": "Exemplo de c\u00f3digo",
  "Color": "Cor",
  "R": "R",
  "G": "G",
  "B": "B",
  "Left to right": "Da esquerda para a direita",
  "Right to left": "Da direita para a esquerda",
  "Emoticons": "Emoticons",
  "Document properties": "Propriedades do documento",
  "Title": "T\u00edtulo",
  "Keywords": "Palavras-chave",
  "Description": "Descri\u00e7\u00e3o",
  "Robots": "Rob\u00f4s",
  "Author": "Autor",
  "Encoding": "Codifica\u00e7\u00e3o",
  "Fullscreen": "Tela cheia",
  "Action": "A\u00e7\u00e3o",
  "Shortcut": "Atalho",
  "Help": "Ajuda",
  "Address": "Endere\u00e7o",
  "Focus to menubar": "Foco no menu",
  "Focus to toolbar": "Foco na barra de ferramentas",
  "Focus to element path": "Foco no caminho do elemento",
  "Focus to contextual toolbar": "Foco na barra de ferramentas contextual",
  "Insert link (if link plugin activated)": "Inserir link (se o plugin de link estiver ativado)",
  "Save (if save plugin activated)": "Salvar (se o plugin de salvar estiver ativado)",
  "Find (if searchreplace plugin activated)": "Procurar (se o plugin de procurar e substituir estiver ativado)",
  "Plugins installed ({0}):": "Plugins instalados ({0}):",
  "Premium plugins:": "Plugins premium:",
  "Learn more...": "Saiba mais...",
  "You are using {0}": "Voc\u00ea est\u00e1 usando {0}",
  "Plugins": "Plugins",
  "Handy Shortcuts": "Atalhos \u00fateis",
  "Horizontal line": "Linha horizontal",
  "Insert\/edit image": "Inserir\/editar imagem",
  "Alternative description": "Inserir descri\u00e7\u00e3o",
  "Source": "Endere\u00e7o da imagem",
  "Dimensions": "Dimens\u00f5es",
  "Constrain proportions": "Manter propor\u00e7\u00f5es",
  "General": "Geral",
  "Advanced": "Avan\u00e7ado",
  "Style": "Estilo",
  "Vertical space": "Espa\u00e7amento vertical",
  "Horizontal space": "Espa\u00e7amento horizontal",
  "Border": "Borda",
  "Insert image": "Inserir imagem",
  "Image": "Imagem",
  "Image list": "Lista de Imagens",
  "Rotate counterclockwise": "Girar em sentido hor\u00e1rio",
  "Rotate clockwise": "Girar em sentido anti-hor\u00e1rio",
  "Flip vertically": "Virar verticalmente",
  "Flip horizontally": "Virar horizontalmente",
  "Edit image": "Editar imagem",
  "Image options": "Op\u00e7\u00f5es de Imagem",
  "Zoom in": "Aumentar zoom",
  "Zoom out": "Diminuir zoom",
  "Crop": "Cortar",
  "Resize": "Redimensionar",
  "Orientation": "Orienta\u00e7\u00e3o",
  "Brightness": "Brilho",
  "Sharpen": "Aumentar nitidez",
  "Contrast": "Contraste",
  "Color levels": "N\u00edveis de cor",
  "Gamma": "Gama",
  "Invert": "Inverter",
  "Apply": "Aplicar",
  "Back": "Voltar",
  "Insert date\/time": "Inserir data\/hora",
  "Date\/time": "data\/hora",
  "Insert link": "Inserir link",
  "Insert\/edit link": "Inserir\/editar link",
  "Text to display": "Texto para mostrar",
  "Url": "Url",
  "Open link in...": "Alvo",
  "Current window": "Nenhum",
  "New window": "Nova janela",
  "Remove link": "Remover link",
  "Anchors": "\u00c2ncoras",
  "Link": "Link",
  "Paste or type a link": "Cole ou digite um Link",
  "The URL you entered seems to be an email address. Do you want to add the required mailto: prefix?": "The URL you entered seems to be an email address. Do you want to add the required mailto: prefix?",
  "The URL you entered seems to be an external link. Do you want to add the required http:\/\/ prefix?": "A URL que voc\u00ea informou parece ser um link externo. Deseja incluir o prefixo http:\/\/?",
  "Link list": "Lista de Links",
  "Insert video": "Inserir v\u00eddeo",
  "Insert\/edit video": "Inserir\/editar v\u00eddeo",
  "Insert\/edit media": "Inserir\/editar imagem",
  "Alternative source": "Fonte alternativa",
  "Poster": "Autor",
  "Paste your embed code below:": "Insira o c\u00f3digo de incorpora\u00e7\u00e3o abaixo:",
  "Embed": "Incorporar",
  "Media": "imagem",
  "Nonbreaking space": "Espa\u00e7o n\u00e3o separ\u00e1vel",
  "Page break": "Quebra de p\u00e1gina",
  "Paste as text": "Colar como texto",
  "Preview": "Pr\u00e9-visualizar",
  "Print": "Imprimir",
  "Save": "Salvar",
  "Find": "Localizar",
  "Replace with": "Substituir por",
  "Replace": "Substituir",
  "Replace all": "Substituir tudo",
  "Prev": "Anterior",
  "Next": "Pr\u00f3ximo",
  "Find and replace": "Localizar e substituir",
  "Could not find the specified string.": "N\u00e3o foi poss\u00edvel encontrar o termo especificado",
  "Match case": "Diferenciar mai\u00fasculas e min\u00fasculas",
  "Whole words": "Palavras inteiras",
  "Spellcheck": "Corretor ortogr\u00e1fico",
  "Ignore": "Ignorar",
  "Ignore all": "Ignorar tudo",
  "Finish": "Finalizar",
  "Add to Dictionary": "Adicionar ao Dicion\u00e1rio",
  "Insert table": "Inserir tabela",
  "Table properties": "Propriedades da tabela",
  "Delete table": "Excluir tabela",
  "Cell": "C\u00e9lula",
  "Row": "Linha",
  "Column": "Coluna",
  "Cell properties": "Propriedades da c\u00e9lula",
  "Merge cells": "Agrupar c\u00e9lulas",
  "Split cell": "Dividir c\u00e9lula",
  "Insert row before": "Inserir linha antes",
  "Insert row after": "Inserir linha depois",
  "Delete row": "Excluir linha",
  "Row properties": "Propriedades da linha",
  "Cut row": "Recortar linha",
  "Copy row": "Copiar linha",
  "Paste row before": "Colar linha antes",
  "Paste row after": "Colar linha depois",
  "Insert column before": "Inserir coluna antes",
  "Insert column after": "Inserir coluna depois",
  "Delete column": "Excluir coluna",
  "Cols": "Colunas",
  "Rows": "Linhas",
  "Width": "Largura",
  "Height": "Altura",
  "Cell spacing": "Espa\u00e7amento da c\u00e9lula",
  "Cell padding": "Espa\u00e7amento interno da c\u00e9lula",
  "Caption": "Legenda",
  "Left": "Esquerdo",
  "Center": "Centro",
  "Right": "Direita",
  "Cell type": "Tipo de c\u00e9lula",
  "Scope": "Escopo",
  "Alignment": "Alinhamento",
  "H Align": "Alinhamento H",
  "V Align": "Alinhamento V",
  "Top": "Superior",
  "Middle": "Meio",
  "Bottom": "Inferior",
  "Header cell": "C\u00e9lula cabe\u00e7alho",
  "Row group": "Agrupar linha",
  "Column group": "Agrupar coluna",
  "Row type": "Tipo de linha",
  "Header": "Cabe\u00e7alho",
  "Body": "Corpo",
  "Footer": "Rodap\u00e9",
  "Border color": "Cor da borda",
  "Insert template": "Inserir modelo",
  "Templates": "Modelos",
  "Template": "Modelo",
  "Text color": "Cor do texto",
  "Background color": "Cor do fundo",
  "Custom...": "Personalizado...",
  "Custom color": "Cor personalizada",
  "No color": "Nenhuma cor",
  "Table of Contents": "\u00edndice de Conte\u00fado",
  "Show blocks": "Mostrar blocos",
  "Show invisible characters": "Exibir caracteres invis\u00edveis",
  "Words: {0}": "Palavras: {0}",
  "{0} words": "{0} palavras",
  "File": "Arquivo",
  "Edit": "Editar",
  "Insert": "Inserir",
  "View": "Visualizar",
  "Format": "Formatar",
  "Table": "Tabela",
  "Tools": "Ferramentas",
  "Powered by {0}": "Distribu\u00eddo por  {0}",
  "No templates defined.": "Modelos ainda não definidos.",
  "Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help": "\u00c1rea de texto formatado. Pressione ALT-F9 para exibir o menu, ALT-F10 para exibir a barra de ferramentas ou ALT-0 para exibir a ajuda"
});