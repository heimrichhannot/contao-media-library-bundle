let jQuery = require('jquery');

($ => {

    let MediaLibrary = {
        init: function() {
            MediaLibrary.registerEvents();
        },
        registerEvents: function() {
            $(document).on('click','.media-library-download-action', function(){
                console.log('download item');
                MediaLibrary.showOptionsModal($(this));
            });

            $(document).on('click', '.media-library-download-selected', function(){
                MediaLibrary.downloadSelectedItem();
            });

            $(document).on('hide.bs.modal','#mediaLibraryModal', function(){
                setTimeout(function(){
                    $(document).find('#watchlistModal').remove();
                },500);
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
            let file = $(document).find('#mediaLibrary-select option:selected').val();

            if('' == file) {
                alert('Keine Option ausgewählt');
            }
            else {
                import(/* webpackChunkName: "contao-utils-bundle" */ 'contao-utils-bundle').then((utilsBundle) => {
                    window.location.href = utilsBundle.url.addParameterToUri(window.location.href, 'file', file);
                });

                $('#mediaLibraryModal').modal('toggle');
            }
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
                        $('#mediaLibraryModal').modal('toggle');
                    }

                    MediaLibrary.ajaxCompleteCallback();
                }
            });
        },
        ajaxCompleteCallback: function () {
            // remove the loading animation
            $('.loader').remove();
        }
    };


    module.exports = MediaLibrary;

    $(document).ready(function () {
        MediaLibrary.init();
    });
})(jQuery);