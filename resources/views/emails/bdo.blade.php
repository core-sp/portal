<html>
    <body>
        <div style="margin-top:15px;margin-bottom:15px;">
            <img src="{{ $message->embed(public_path().'/img/logo-email-novo.png') }}" alt="CORE-SP" />
        </div>
        <hr>
        <div>
            <p>{!! $body !!}</p>
        </div>
        <hr>
        <div>
            <p>Solicitação enviada pelo <a href="{{ route('site.home') }}">site oficial</a> do CORE-SP.</p>
        </div>
    </body>
</html>