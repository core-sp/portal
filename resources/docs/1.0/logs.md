# Logs

---

- [Eventos](#eventos)
- [Erros](#erros)

<a name="eventos"></a>
## Eventos

<p>A monitoração de eventos dentro do Portal CORE-SP gera logs de eventos diários, com informações de todos os tipos de ações realizados pelos usuários. Os arquivos são armazenados dentro da aplicação, em /storage/logs/usuarios, aonde encontram-se divididos por ano, mês e dia.</p>
<br>
<p>Para visualizar o arquivo de log em tempo real:</p>

<div class="code-toolbar"><pre class="language-php"><code class="language-php"># tail -f storage/logs/usuarios/2019/05/laravel-2019-05-20.log</code></pre></div>

<a name="erros"></a>
## Erros

<p>A monitoração de erros do sistema gera logs diários, que ficam armazenados por apenas 30 dias e depois são descartados. Os arquivos podem ser encontrados dentro da aplicação, em /storage/logs/erros.</p>