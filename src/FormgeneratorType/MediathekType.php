<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\FormgeneratorType;

use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Slug\Slug;
use Contao\Database;
use Contao\DataContainer;
use Contao\Folder;
use Contao\FormModel;
use Contao\StringUtil;
use HeimrichHannot\FormgeneratorTypeBundle\Event\PrepareFormDataEvent;
use HeimrichHannot\FormgeneratorTypeBundle\Event\StoreFormDataEvent;
use HeimrichHannot\FormgeneratorTypeBundle\FormgeneratorType\FormgeneratorTypeInterface;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediathekType implements FormgeneratorTypeInterface
{
    private TranslatorInterface $translator;
    private Slug $slug;
    private RequestStack $requestStack;

    public function __construct(TranslatorInterface $translator, Slug $slug, RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->slug = $slug;
        $this->requestStack = $requestStack;
    }

    public function getType(): string
    {
        return 'huh_mediathek';
    }

    public function onload(DataContainer $dataContainer, FormModel $formModel): void
    {
        PaletteManipulator::create()
            ->removeField('storeValues')
            ->addLegend('huh_mediathek_legend', 'title_legend')
            ->addField('ml_archive', 'huh_mediathek_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_form');
    }

    public function getDefaultFields(FormModel $formModel): array
    {
        $folder = new Folder('files/media/mediathek');
        $uuid = $folder->getModel()->uuid;

        return [
            [
                'type' => 'text',
                'name' => 'title',
                'label' => $this->translator->trans('tl_ml_product.title.0', [], 'contao_tl_ml_product'),
                'mandatory' => '1',
            ],
            [
                'type' => 'upload',
                'name' => 'file',
                'label' => $this->translator->trans('tl_ml_product.file.0', [], 'contao_tl_ml_product'),
                'extensions' => 'jpg,jpeg,gif,png',
                'mandatory' => '1',
                'storeFile' => '1',
                'uploadFolder' => $uuid,
            ],
            [
                'type' => 'text',
                'name' => 'copyright',
                'label' => $this->translator->trans('tl_ml_product.copyright.0', [], 'contao_tl_ml_product'),
            ],
            [
                'type' => 'textarea',
                'name' => 'text',
                'label' => $this->translator->trans('tl_ml_product.text.0', [], 'contao_tl_ml_product'),
            ],
        ];
    }

    public function onPrepareFormData(PrepareFormDataEvent $event): void
    {
        if ($event->getForm()->ml_archive && ProductArchiveModel::findByPk($event->getForm()->ml_archive)) {
            $event->getForm()->storeValues = '1';
            $event->getForm()->targetTable = ProductModel::getTable();
        }
    }

    public function onStoreFormData(StoreFormDataEvent $event): void
    {
        if ($event->getForm()->ml_archive && ($archiveModel = ProductArchiveModel::findByPk($event->getForm()->ml_archive))) {
            $data = $event->getData();
            $data = array_intersect_key($data, array_flip(Database::getInstance()->getFieldNames(ProductModel::getTable())));
            $data['pid'] = $event->getForm()->ml_archive;
            $data['dateAdded'] = time();
            $data['alias'] = $this->slug->generate($data['title']);
            $data['type'] = $archiveModel->type;

            if (!empty($_SESSION['FILES'])) {
                Controller::loadDataContainer(ProductModel::getTable());

                foreach ($_SESSION['FILES'] as $field => $fieldData) {
                    if (isset($data[$field]) && isset($GLOBALS['TL_DCA'][ProductModel::getTable()]['fields'][$field])) {
                        $data[$field] = StringUtil::uuidToBin($fieldData['uuid']);

                        if (($GLOBALS['TL_DCA'][ProductModel::getTable()]['fields'][$field]['eval']['fieldType'] ?? false) === 'checkbox'
                            || ($GLOBALS['TL_DCA'][ProductModel::getTable()]['fields'][$field]['eval']['multiple'] ?? false) === true
                        ) {
                            $data[$field] = serialize([$fieldData['uuid']]);
                        }
                    }
                }
            }

            $event->setData($data);
        }
    }
}
