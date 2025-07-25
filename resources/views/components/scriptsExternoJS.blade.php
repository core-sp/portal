<!-- Scripts principais para funcionamento básico da parte externa -->
 
<script type="text/javascript" src="{{ asset('/js/app.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js" integrity="sha512-gDWSGGWzTc0mD1P0NwDUsZsSrSyLsC8Mv0I14JtDFJXu2Pgo3ik5Uk1+BqxosbEJU2gDUD6W5DCpn7l29Ah9Ag==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript" src="{{ asset('/js/pre-init.js?'.hashScriptJs()) }}" data-modulo-versao="{{ 'v=' . versaoScriptJs() }}" id="pre-init"></script>
<script type="text/javascript" src="{{ asset('/js/externo/site.js?'.hashScriptJs()) }}"></script>