function my_modal(title, url) {
    $('#customModalTitle').text(title);
    $('#customModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

    $.ajax({
        url: url,
        success: function(data){
            $('#customModalBody').html(data);
        },
        error: function(){
            $('#customModalBody').html('<div class="text-danger text-center">Failed to load content.</div>');
        }
    });

    var modal = new bootstrap.Modal(document.getElementById('customModal'), {
        backdrop: 'static',
        keyboard: false
    });
    modal.show();
}
