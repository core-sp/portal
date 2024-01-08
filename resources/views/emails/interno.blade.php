<html>
    <body>
        <div>
            <p>{!! $body !!}</p>
        </div>
        <hr>
        <div style="margin-top:15px;margin-bottom:15px;">
            <img src="{{ $message->embed(public_path().'/img/logo-email-novo.png') }}" alt="CORE-SP" />
        </div>
    </body>
</html>