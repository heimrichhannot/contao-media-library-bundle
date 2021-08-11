# Contao Media Library Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-media-library-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-media-library-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-media-library-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-media-library-bundle)

This bundle offers the archive based handling of media library products of different type and their download items. The download items can be generated automatically.

## Features

- organize your media (images, videos, ...) as products in archives if a fully fledged shop system would be too much
- predefined media library content types `image`, `video`, `file`
- in case of image products, images sizes for the creation of downloads according to the downloads can be specified
- manually add download items for products
- configurable dca field palettes for products
- add additional fields differently for each product archive
- optional: `codefog/tags-bundle` integration for tagging products (activate in product archive)
- optional: `heimrichhannot/contao-categories-bundle` integration for categorizing products (activate in product archive)

## Usage

### Install

Install via composer
 
```
composer require heimrichhannot/contao-media-library-bundle
```

Update database

### Setup

1. Create a media library archive and set configurations for its content.
2. Create a product in the archive. Download items will be automatically generated on submit if not permitted manually.
3. Optional: manually add download items

### Configuration

```yaml
huh_media_library:

  # If true, the filenames of the generated product downloads will be sanitized.
  sanitize_download_filenames: false
```

### Installation with frontend assets using webpack

If you want to add the frontend assets (JS & CSS) to your project using webpack, please
add [foxy/foxy](https://github.com/fxpio/foxy) to the depndencies of your project's `composer.json` and add the following to its `config` section:

```json
"foxy": {
  "manager": "yarn",
  "manager-version": "^1.5.0"
}
```

Using this, foxy will automatically add the needed yarn packages to your project's `node_modules` folder.

If you want to specify which frontend assets to use on a per page level, you can use [heimrichhannot/contao-encore-bundle](https://github.com/heimrichhannot/contao-encore-bundle).
