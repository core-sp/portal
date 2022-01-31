<div class="modal hide fade" id="popup-campanha">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h4 class="d-inline">Alerta de Segurança</h4>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <video id="video-campanha" controls autoplay muted>
                <source src="{{ asset('img/video_comunicado.mp4') }}" type="video/mp4">
                {{--<source src="{{ asset('img/compressed.webm') }}" type="video/webm">--}}
            </video>
        </div>
    </div>
</div>