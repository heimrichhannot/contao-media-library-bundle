let jQuery = require('jquery');

($ => {

    let MediaLibrary = {
        init: function() {
            MediaLibrary.registerEvents();
        },
        registerEvents: function() {
            $(document).on('click','.media-library-download-action', function(){
                MediaLibrary.showOptionsModal($(this));
            });

            $(document).on('click', '.media-library-download-selected', function(){
                MediaLibrary.downloadSelectedItem();
            });
        },
        showOptionsModal: function(elem) {
            let url = elem.data('action'),
                data = {
                    'options': elem.data('options')
                };

            MediaLibrary.doAjax(url,data);
        },
        downloadSelectedItem: function(){
            let file = $(document).find('#mediaLibrary-select option:selected');

            if('' == file) {
                alert('Keine Option ausgewählt');
            }

            window.location.href = window.location.href + '?file=' + file;
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


    module.exports = mediaLibraryBundle;

    $(document).ready(function () {
        MediaLibrary.init();
    });
})(jQuery);