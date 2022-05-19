class HuhMlWizard
{
    static openWizardModal(options)
    {
        var opt = options || {},
            maxWidth = (window.getSize().x - 20).toInt(),
            maxHeight = (window.getSize().y - 192).toInt();
        if (!opt.id) opt.id = 'tl_select';
        if (!opt.width || opt.width > maxWidth) opt.width = Math.min(maxWidth, 900);
        if (!opt.height || opt.height > maxHeight) opt.height = maxHeight;
        var M = new SimpleModal({
            'width': opt.width,
            'draggable': false,
            'overlayOpacity': .7,
            'onShow': function() { document.body.setStyle('overflow', 'hidden'); },
            'onHide': function() { document.body.setStyle('overflow', 'auto'); }
        });
        M.addButton(HuhMlLang.closeModal, 'btn', function() {
            var frm = window.frames['simple-modal-iframe'],
                val = [], ul, inp, field, act, it, i, pickerValue, sIndex;
            if (frm === undefined) {
                alert('Could not find the SimpleModal frame');
                return;
            }

            var values = [...frm.document.querySelectorAll('#ctrl_copyright option:checked')].map(option => option.value);

            val.value = values.filter((v, i, a) => a.indexOf(v) === i).join(', ');

            if (opt.callback) {
                opt.callback(val);
            }
            this.hide();
        });
        M.show({
            'title': opt.title,
            'contents': '<iframe src="' + opt.url + '" name="simple-modal-iframe" width="100%" height="' + opt.height + '" frameborder="0"></iframe>',
            'model': 'modal'
        });
    }
}