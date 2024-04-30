<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\FormType;

use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Slug\Slug;
use Contao\Database;
use Contao\DataContainer;
use Contao\Folder;
use Contao\FormModel;
use Contao\Model;
use Contao\StringUtil;
use HeimrichHannot\FileCreditsBundle\HeimrichHannotFileCreditsBundle;
use HeimrichHannot\FileCreditsBundle\Model\FilesModel;
use HeimrichHannot\FormTypeBundle\Event\CompileFormFieldsEvent;
use HeimrichHannot\FormTypeBundle\Event\LoadFormFieldEvent;
use HeimrichHannot\FormTypeBundle\Event\PrepareFormDataEvent;
use HeimrichHannot\FormTypeBundle\Event\ProcessFormDataEvent;
use HeimrichHannot\FormTypeBundle\Event\StoreFormDataEvent;
use HeimrichHannot\FormTypeBundle\FormType\AbstractFormType;
use HeimrichHannot\FormTypeBundle\FormType\FormContext;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use HeimrichHannot\MediaLibraryBundle\Security\ProductVoter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaLibraryType extends AbstractFormType
{
    public const TYPE = 'huh_media_library';
    protected const DEFAULT_FORM_CONTEXT_TABLE = 'tl_ml_product';
    public const PARAMETER_EDIT = 'edit';

    public function __construct(
        private TranslatorInterface $translator,
        private Slug $slug,
        private Security $security
    )
    {
    }

    public function getType(): string
    {
        return static::TYPE;
    }

    public function onload(DataContainer $dataContainer, FormModel $formModel): void
    {
        PaletteManipulator::create()
            ->removeField('storeValues')
            ->addLegend('huh_media_library_legend', 'title_legend')
            ->addField('ml_archive', 'huh_media_library_legend', PaletteManipulator::POSITION_APPEND)
            ->addField('ml_publish', 'huh_media_library_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_form');
    }

    public function getDefaultFields(FormModel $formModel): array
    {
        if (!$formModel->ml_archive) {
            return [];
        }

        $folder = new Folder('files/media/mediathek');
        $uuid = $folder->getModel()->uuid;

        $fields = [
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
                'type' => 'textarea',
                'name' => 'text',
                'label' => $this->translator->trans('tl_ml_product.text.0', [], 'contao_tl_ml_product'),
            ],
        ];

        if (class_exists(HeimrichHannotFileCreditsBundle::class)) {
            $fields[] = [
                'type' => 'textarea',
                'name' => 'copyright',
                'label' => $this->translator->trans('tl_ml_product.copyright.0', [], 'contao_tl_ml_product'),
            ];
        }

        return $fields;
    }

    public function onPrepareFormData(PrepareFormDataEvent $event): void
    {
        if ($event->getForm()->ml_archive && ProductArchiveModel::findByPk($event->getForm()->ml_archive)) {
            $event->getForm()->storeValues = '1';
            $event->getForm()->targetTable = ProductModel::getTable();
        }

        parent::onPrepareFormData($event);
    }

    public function onLoadFormField(LoadFormFieldEvent $event): void
    {
        $isUpdate = $event->getFormContext()->isUpdate();

        if ($isUpdate && 'file' === $event->getWidget()->name) {
            $event->getWidget()->mandatory = '';
        }

        if ($isUpdate && 'copyright' === $event->getWidget()->name && class_exists(HeimrichHannotFileCreditsBundle::class)) {
            $fileModel = FilesModel::findByUuid($event->getFormContext()->getData()['file']);
            if ($fileModel) {
                $event->getWidget()->value = implode("\n", StringUtil::deserialize($fileModel->copyright, true));
            }
        }
    }

    public function onStoreFormData(StoreFormDataEvent $event): void
    {
        if (!$event->getForm()->ml_archive) {
            return;
        }
        $archiveModel = ProductArchiveModel::findByPk($event->getForm()->ml_archive);
        if (!$archiveModel) {
            return;
        }

        $data = $event->getData();
        $data = array_intersect_key($data, array_flip(Database::getInstance()->getFieldNames(ProductModel::getTable())));
        $data['pid'] = $event->getForm()->ml_archive;
        $data['dateAdded'] = time();
        $data['alias'] = $this->slug->generate($data['title']);
        $data['type'] = $archiveModel->type;

        if ($event->getForm()->ml_publish) {
            $data['published'] = '1';
        }

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

    public function onProcessFormData(ProcessFormDataEvent $event): void
    {
        parent::onProcessFormData($event);

        if (class_exists(HeimrichHannotFileCreditsBundle::class)) {
            $context = $this->getFormContext();

            if ($context->isCreate()) {
                $uuid = $event->getFiles()['file']['uuid'] ?? null;
            } else {
                $uuid = $context->getData()['file'] ?? null;
            }

            if ($uuid && ($fileModel = FilesModel::findByUuid($uuid))) {
                $copyright = preg_split("/\r\n|\n|\r/", $event->getSubmittedData()['copyright'] ?? '');
                $fileModel->copyright = serialize($copyright);
                $fileModel->save();
            }
        }
    }

    protected function evaluateFormContext(): FormContext
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if (!$request->query->has(static::PARAMETER_EDIT)) {
            return FormContext::create(static::DEFAULT_FORM_CONTEXT_TABLE);
        }

        $id = $request->query->get(static::PARAMETER_EDIT);
        if  (!$id || !is_numeric($id) || !($productModel = ProductModel::findByPk($id))) {
            throw new PageNotFoundException('Product not found!');
        }

        if ($this->security->isGranted(ProductVoter::PERMISSION_EDIT, $productModel)) {
            return FormContext::update(static::DEFAULT_FORM_CONTEXT_TABLE, $productModel->row());
        }

        throw new AccessDeniedException('No permission to edit product.');
    }

    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            'request_stack' => '?request_stack',
        ]);
    }


}
