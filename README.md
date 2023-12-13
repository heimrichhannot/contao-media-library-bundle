# Contao Media Library Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-media-library-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-media-library-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-media-library-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-media-library-bundle)

This bundle offers the archive based handling of media library products of different type and their download items. 
The download items can be generated automatically.

## Features

- organize your media (images, videos, ...) as products in archives if a fully fledged shop system would be too much
- predefined media library content types `image`, `video`, `file`
- in case of image products, images sizes for the creation of downloads according to the downloads can be specified
- manually add download items for products
- configurable dca field palettes for products
- add additional fields differently for each product archive
- [Form Type](https://github.com/heimrichhannot/contao-form-type-bundle) integration 
- [Encore Bundle](https://github.com/heimrichhannot/contao-encore-bundle) integration
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

### Delete ML Products

Submit a `DELETE`-Request to the product's details URL. If the HTTP `DELETE` method is unavailable, use a hidden form field with a name of `_method` and the value `DELETE` instead.

Example for use within a Bootstrap 5 modal:
```html
<form method="post">
    <input type="hidden" name="REQUEST_TOKEN" value="{{ request_token }}">
    <input type="hidden" name="_method" value="DELETE">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
    <button type="submit" class="btn btn-primary">Löschen</button>
</form>
```

#### More on creating a Delete Form

Further, if you need to inject a request token into the respective `HeimrichHannot\ReaderBundle` reader template, you may want to implement an EventListener:

```php
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use HeimrichHannot\ReaderBundle\Event\ReaderBeforeRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MLDeleteFormEventListener implements EventSubscriberInterface
{
    public function __construct(private readonly ContaoCsrfTokenManager $csrfTokenManager) {}

    public static function getSubscribedEvents()
    {
        return [ReaderBeforeRenderEvent::NAME => 'onReaderBeforeRenderEvents'];
    }

    public function onReaderBeforeRenderEvents(ReaderBeforeRenderEvent $event): void
    {
        if ($item['dataContainer'] === 'tl_ml_product')
        {
            $item = $event->getTemplateData();
            $item['request_token'] = $this->csrfTokenManager->getDefaultTokenValue();
            $event->setTemplateData($item);
        }
    }
}
```
