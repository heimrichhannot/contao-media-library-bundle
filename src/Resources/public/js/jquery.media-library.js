(function ($) {

    var MediaLibrary = {
        init: function() {
            MediaLibrary.registerEvents();
        },
        registerEvents: function() {
            $(document).on('click','.media-library-download-action', function(){
                MediaLibrary.showOptionsModal($(this));
            });
        },
        showOptionsModal: function(elem) {
            var url = elem.data('action'),
                data = {
                    'options': elem.data('options')
                };

            MediaLibrary.doAjax(url,data);
        },
        doAjax: function(url, data) {
            $.ajax({
                url: url,
                method: 'POST',
                dataType: 'JSON',
                data: data,
                success: function(data) {
                    if(undefined !== data.result.data.modal) {
                        $('body').append(data.result.data.modal);
                        $('.modal').toggle();
                    }
                }
            });
        }
    };

    $(document).ready(function () {
        MediaLibrary.init();
    });


})(jQuery);
