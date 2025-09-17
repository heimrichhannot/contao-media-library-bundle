<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\FilesModel;
use Contao\Image;
use Contao\StringUtil;
use Contao\Widget;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsHook("parseWidget")]
class ParseWidgetListener
{
    public function __construct(
        private readonly Utils $utils,
        private readonly ContaoFramework $contaoFramework,
        private readonly TranslatorInterface $translator
    ) {}

    public function __invoke(string $buffer, Widget $widget): string
    {
        if (!$this->utils->container()->isBackend()
            || 'tl_ml_product' !== $widget->strTable
            || 'copyright' !== $widget->name)
        {
            return $buffer;
        }

        $product = ProductModel::findByPk((int) $widget->currentRecord);
        if (!$product) {
            return $buffer;
        }

        $uuid = $product->file;

        if (\is_array(StringUtil::deserialize($uuid))) {
            $uuid = \array_values(StringUtil::deserialize($uuid, true))[0] ?? null;
        }

        if (!$uuid) {
            return '';
        }

        /** @var FilesModel|null $fileModel */
        $fileModel = $this->contaoFramework->getAdapter(FilesModel::class)->findByUuid($uuid);

        if (!$fileModel) {
            return '';
        }

        $title = sprintf($GLOBALS['TL_LANG']['tl_files']['editFile'], $fileModel->name);

        $href = $this->utils->routing()->generateBackendRoute([
            'do' => 'files',
            'table' => 'tl_files',
            'act' => 'edit',
            'id' => $fileModel->path,
            'popup' => '1',
            'nb' => '1',
        ]);

        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/heimrichhannotmedialibrary/backend/js/wizard.js';

        $linkId = uniqid('huh_ml_copyright_');
        $scriptTitle = StringUtil::specialchars(\str_replace("'", "\\'", $title));
        $script = <<<HTML
        <script>
            HuhMlLang = {
                "closeModal": "{$this->translator->trans('tl_ml_product.closeModal', [], 'contao_tl_ml_product')}"
            };
            
            $("$linkId").addEvent("click", function(e) {
                e.preventDefault();
                HuhMlWizard.openWizardModal({
                  "id": "tl_listing",
                  "title": "$scriptTitle",
                  "url": "$href",
                  "callback": function(value) {
                      $("ctrl_{$widget->id}").value = value.value;
                  }
                });
            });
        </script>
        HTML;

        return \sprintf(
            '<div class="wizard">%s <a href="%s" id="%s" title="%s" style="position:relative;top:-2px;vertical-align:middle;">%s</a></div>%s',
            $buffer,
            StringUtil::specialcharsUrl($href),
            $linkId,
            StringUtil::specialchars($title),
            Image::getHtml('alias.svg', $title),
            $script
        );
    }
}
