<script type="text/javascript" src="{{ asset('/js/app.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.1/jquery-ui.min.js" integrity="sha512-MSOo1aY+3pXCOCdGAYoBZ6YGI0aragoQsg1mKKBHXCYPIWxamwOE7Drh+N5CPgGI5SA9IEKJiPjdfqWFWmZtRA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>console.log('{{ logVersaoScriptCss() }}');</script>
<script type="text/javascript" src="{{ asset('/js/pre-init.js?'.hashScriptJs()) }}" data-modulo-versao="{{ 'v=' . versaoScriptJs() }}" id="pre-init"></script>
<script type="text/javascript" src="{{ asset('/js/' . $local_final . '.js?'.hashScriptJs()) }}"></script>