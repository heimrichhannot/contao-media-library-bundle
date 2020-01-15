require('alertifyjs/build/css/alertify.css');

import '@hundh/contao-utils-bundle';
import alertify from 'alertifyjs';

class MediaLibraryBundle {
  static onReady() {
    utilsBundle.event.addDynamicEventListener('click', '.media-library-download-action', function(target) {
      MediaLibraryBundle.showOptionsModal(target);
    });

    utilsBundle.event.addDynamicEventListener('change', '#mediaLibrary-select', function() {
      MediaLibraryBundle.showDownloadButton();
    });

    utilsBundle.event.addDynamicEventListener('click', '#mediaLibrary-download-button', function() {
      MediaLibraryBundle.closeModal();
    });
  }

  static showOptionsModal(el) {
    let url  = el.getAttribute('data-action'),
        data = el.getAttribute('data-options');

    MediaLibraryBundle.asyncSubmit(url, data);
  }

  static showDownloadButton() {
    let file = document.querySelector('#mediaLibrary-select option:checked').value;

    MediaLibraryBundle.removeOldButton();

    if (undefined !== file && '' !== file) {
      let button = MediaLibraryBundle.getDownloadButton(file);

      document.querySelector('.media-library-options').appendChild(button);
    }
  }

  static getDownloadButton(file) {
    let button = document.createElement('a');

    button.setAttribute('href', file);
    button.setAttribute('id', 'mediaLibrary-download-button');
    button.setAttribute('download', '');
    button.setAttribute('class', 'btn btn-primary d-inline-block mt-3');
    button.textContent = 'herunterladen';

    return button;
  }

  static asyncSubmit(action, data) {
    let request = new XMLHttpRequest();
    request.open('POST', action, true);
    request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8\n');

    request.onreadystatechange = function() {
      const DONE = 4;
      const OK = 200;

      if (request.readyState === DONE && request.status === OK) {
        let response = JSON.parse(request.response);

        if (response.result.data.modal) {
          let config = JSON.parse(response.result.data.config);

          alertify.defaults.glossary.title = config.alertTitle;
          alertify.defaults.transition     = 'slide';
          alertify.defaults.theme.ok       = config.btnClass;
          alertify.defaults.theme.cancel   = 'btn btn-danger';

          alertify.alert(response.result.data.modal).setting({
            'label': config.abortLabel,
            'message': response.result.data.modal,
          }).show();
        }
      }
    };

    request.send('options=' + data);
  }

  static removeOldButton() {
    let button = document.getElementById('mediaLibrary-download-button');

    if (button) {
      button.remove();
    }
  }

  static closeModal(elem) {
    alertify.closeAll();
  }

}

export {MediaLibraryBundle};