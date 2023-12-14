@if(isset($itens_home['popup_video']))
<div class="modal hide fade" id="popup-campanha">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h4 class="d-inline">Atenção!</h4>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <iframe id="video-campanha"
                width="100%"
                height="315" 
                src="{{ $itens_home['popup_video'] }}" 
                title="YouTube video player" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                allowfullscreen
            >
            </iframe>
        </div>
    </div>
</div>
@endif