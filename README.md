# Contao Media Library Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-media-library-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-media-library-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-media-library-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-media-library-bundle)

The Contao Media Library Bundle provides archive‑based management of media library items (such as images, videos, and files) and their downloadable files.

## Features

- Organize your media as objects in archives
- Predefined media library product types: `image`, `video`, `file`
- For image archives, define image sizes that are used to automatically create downloads
- Individually add additional DCA fields to the archives and their items
- Automatically generate downloads for images in desired formats and dimensions
- Upload and edit media library items directly from the frontend with a form generator preset form-type
- Integration with [FLARE Bundle](https://github.com/heimrichhannot/conta-flare-bundle) to list media libraries, e.g., in gallery views
- Integration with [Form Type Bundle](https://github.com/heimrichhannot/contao-form-type-bundle) to handle form submissions with media library items
- Integration with [Encore Bundle](https://github.com/heimrichhannot/contao-encore-bundle) to easily add the media library assets to your frontend
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

You can enable edit and delete support for media library products by activating the corresponding options in the archive settings.  
Make sure to configure the member (and/or member group) permissions accordingly.

If a front end member has the required permissions, edit and delete links are automatically added to the template data of reader bundle templates.  
The variables are available as `editLink` and `deleteLink`.

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

Fired when an archive or item is edited, respectively. Can be used modify the DCA or translations.

### Custom Media Library Archive Types

Any class that extends `HeimrichHannot\MediaLibraryBundle\ArchiveType\AbstractArchiveType` will be automatically
registered as a media library archive type and be available in the archive settings.

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
