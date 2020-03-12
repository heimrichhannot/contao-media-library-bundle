# Contao Media Library Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-media-library-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-media-library-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-media-library-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-media-library-bundle)

This bundle offers the archive based handling of media library products of different type and their download items. The download items can be generated automatically.

## Features

- `codefog/tags-bundle` integration
- `heimrichhannot/contao-categories-bundle` integration
- `heimrichhannot/contao-multifileupload-bundle` integration
- organize products in archives
- organize download items for every products
- predefined media library content types `image`, `video`, `file`
- automatically generate download items for products
- manually add download items for products
- configurable title of download items
- configurable responsive image sizes for generated download items
- configurable palettes for products
- configurable upload folder for files

## Usage

### Install

Install via composer
 
```
composer require heimrichhannot/contao-media-library-bundle
```

Update database

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

### Setup

1. Create media library archive and set configurations for its contents
2. Create a product in the archive. Download items will be automatically generated on submit if not permitted manually.
3. Optional: manually add download items

### Archive Configuration

Field                     | Description
--------------------------|------------------------------------------|
type                      | Define the type of the archives products |
additionalFields          | Add fields to the palette of the product. To enable a field for this selection add `'additionalField'=>true` to the fields `'eval'` configuration    | 
uploadFolderMode          | Switch between static folder or user dependent folder  |
uploadFolderUserPattern   | Set a pattern by which the user dependent folder will be created and added to the uploadFolder path  |
addProductPatternToUploadFolder    | Add the product title to the uploaderFolder  |
uploadFolderProductPattern | Set a pattern by which the product dependent folder will be created and added to the uploadFolder path |
keepProductTitleForDownloadItems | If selected the product title will be used for the download items. If not selected the name of the responsive image size will be used. |




