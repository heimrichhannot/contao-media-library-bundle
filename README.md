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

## Editing and deleting products

You can enable edit and delete support for media library products by activating the corresponding options in the archive settings.  
Make sure to configure the member (and/or member group) permissions accordingly.

If a front end member has the required permissions, edit and delete links are automatically added to the template data of reader bundle templates.  
The variables are available as `editLink` and `deleteLink`.
