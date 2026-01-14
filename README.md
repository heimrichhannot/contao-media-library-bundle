# Contao Media Library Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-media-library-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-media-library-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-media-library-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-media-library-bundle)

The Contao Media Library Bundle provides archive‑based management of media library items (such as images, videos, and files) and their downloadable files.

## Features

- Organize your media as objects in archives
- Predefined media library item types: `image`, `video`, `file`
- For image archives, define image sizes to automatically create downloads in desired formats and dimensions
- Upload and edit media library items directly from the frontend with a form generator preset form-type
- Add custom DCA fields to individual archives and their items
- Integration with [FLARE Bundle](https://github.com/heimrichhannot/conta-flare-bundle) to list media libraries, e.g., in gallery views
- Integration with [Form Type Bundle](https://github.com/heimrichhannot/contao-form-type-bundle) to handle frontend form submissions with media library items
- Optional: Integration with [Codefog Tags Bundle](https://github.com/codefog/tags-bundle) to tag items
- Optional: Integration with [H & H Categories Bundle](https://github.com/heimrichhannot/contao-categories-bundle) to categorize items
- Optional: Integration with [H & H Filecredits Bundle (private)](https://github.com/heimrichhannot/contao-filecredits-bundle) to ease assignment of file credits to the files of items

---

## Installation

Install the bundle via Composer and update the database afterwards.

```bash
composer require heimrichhannot/contao-media-library-bundle
```

## Setup

1. Create a media library archive and configure its settings.
2. Create an entry in this archive.
3. Optional: Manually add additional files or file variants.

## Configuration

```yaml
huh_media_library:
    # Default upload path for media library items when using the frontend form
    file_upload_path: 'files/media-library/##author##/##title##'
```

---

## Editing and deleting products

Enable edit and delete functionality for media library items by turning on the corresponding options in the archive settings.
Also verify that member (and/or member group) permissions are configured appropriately.

### Editing items directly from the frontend

1. Set up your custom upload and/or edit form in the form-generator using the supplied `MediaLibraryType` form-type.
   - You may use the identical form for both actions, uploading and editing, given that you want the same fields to be present.
   - Otherwise, create separate forms for each action or refer to the
     [contao-form-type-bundle's documentation](https://github.com/heimrichhannot/contao-form-type-bundle)
     for information on how to modify a form programatically.
2. Create an upload and/or edit page with a form content element configured to use the previously set up form.
   The page will then automatically assure that any accessing frontend member is authorized to upload or edit the individual media library item.
3. In a flare reader template, you may use the below snippet to display an edit link for a media library item.
   - An endpoint for deleting items is road-mapped; full functionality is not available yet.

```twig
{% if is_granted('ml_item_edit', model) %}
    {% set pageEdit = archive.related('editJumpTo') %}
    {% if pageEdit %}
        <a href="{{ pageEdit.absoluteUrl }}?edit={{ model.id }}">edit</a>
    {% endif %}
{% endif %}
```

---

## Developers

### Events

#### Modify Palette

`HeimrichHannot\MediaLibraryBundle\Event\`**`ArchivePaletteEvent`**
<br>`HeimrichHannot\MediaLibraryBundle\Event\`**`ItemPaletteEvent`**

Fired when the palette of an archive or item is generated. Can be used to add additional fields.

#### Backend Editing

`HeimrichHannot\MediaLibraryBundle\Event\`**`ArchiveEditEvent`**
<br>`HeimrichHannot\MediaLibraryBundle\Event\`**`ItemEditEvent`**

Fired when an archive or item is edited, respectively. Can be used to modify the DCA or translations.

### Custom Media Library Archive Types

Any class that extends `HeimrichHannot\MediaLibraryBundle\ArchiveType\AbstractArchiveType` will be automatically
registered as a media library archive type and be available in the archive settings.

#### Minimal working Boilerplate

```php
<?php # src/MediaLibrary/MyMediaLibraryArchive.php

namespace App\MediaLibrary;

use HeimrichHannot\MediaLibraryBundle\ArchiveType\AbstractArchiveType;

class MyMediaLibraryArchive extends AbstractArchiveType
{
    public const TYPE = 'app_myMlArchiveType';

    public static function getAlias(): string
    {
        return self::TYPE;
    }
}
```

#### Extended Example

```php
<?php # src/MediaLibrary/MyMediaLibraryArchive.php

namespace App\MediaLibrary;

use HeimrichHannot\MediaLibraryBundle\ArchiveType\AbstractArchiveType;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class MyMediaLibraryArchive extends AbstractArchiveType
{
    public const TYPE = 'app_myMlArchiveType';

    public static function getAlias(): string
    {
        return self::TYPE;
    }

    /* ================ IMPLEMENT CONFIG METHODS ================ *\
     *  The following methods have default implementations in     *
     *  AbstractArchiveType, but can be overridden if necessary.  *
    \* ========================================================== */

    public function getItemPalette(ArchiveModel $archive, ItemModel $item): string
    {
        return parent::getItemPalette($archive, $item);
    }

    public function getArchivePalette(ArchiveModel $archive): string
    {
        return parent::getArchivePalette($archive);
    }

    public function supportsImageSizeDownloads(ArchiveModel $archive): bool
    {
        return parent::supportsImageSizeDownloads($archive);
    }

    /* ================ EXAMPLE EVENT LISTENERS  ================ *\
     *  The following methods are examples of event listeners     *
     *  that you can implement to customize the behavior of your  *
     *  media library archive types or even existing ones.        *
    \* ========================================================== */

    #[AsEventListener]
    public function alterTranslations(ItemEditEvent $event): void
    {
        if ($event->archiveType !== self::TYPE) {
            return;
        }

        $trans = &$GLOBALS['TL_LANG'][ItemModel::getTable()];
        $trans['file'] = ['Vorschaubild', 'Wählen Sie ein Thumbnail für das Archiv aus.'];
        $trans['addAdditionalFiles'] = ['Dateien zum Herunterladen anbieten', 'Bieten Sie Dateien zum Download an.'];
        $trans['additionalFiles'] = ['Herunterladbare Dateien auswählen', 'Hier können Sie die Dateien auswählen, die zum Download angeboten werden sollen.'];
    }

    #[AsEventListener]
    public function onItemPalette(ItemPaletteEvent $event): void
    {
        if ($event->archiveType !== self::TYPE) {
            return;
        }

        $event->prefix = PaletteManipulator::create()
            ->removeField('tags')
            ->removeField('text')
            ->applyToString($event->prefix);

        $event->suffix = '{details_legend},tags,text;' . $event->suffix;
    }
}
```
