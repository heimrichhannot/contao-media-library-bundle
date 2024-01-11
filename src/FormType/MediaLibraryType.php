<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\FormType;

use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Slug\Slug;
use Contao\Database;
use Contao\DataContainer;
use Contao\Folder;
use Contao\FormModel;
use Contao\Model;
use Contao\StringUtil;
use HeimrichHannot\FileCreditsBundle\HeimrichHannotFileCreditsBundle;
use HeimrichHannot\FileCreditsBundle\Model\FilesModel;
use HeimrichHannot\FormTypeBundle\Event\PrepareFormDataEvent;
use HeimrichHannot\FormTypeBundle\Event\ProcessFormDataEvent;
use HeimrichHannot\FormTypeBundle\Event\StoreFormDataEvent;
use HeimrichHannot\FormTypeBundle\FormType\AbstractFormType;
use HeimrichHannot\FormTypeBundle\FormType\FormContext;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaLibraryType extends AbstractFormType
{
    public const TYPE = 'huh_media_library';
    protected const DEFAULT_FORM_CONTEXT_TABLE = 'tl_ml_product';
    public const PARAMETER_EDIT = 'edit';

    private TranslatorInterface $translator;
    private Slug $slug;
    private RequestStack $requestStack;
    private Security $security;

    public function __construct(TranslatorInterface $translator, Slug $slug, RequestStack $requestStack, Security $security)
    {
        $this->translator = $translator;
        $this->slug = $slug;
        $this->requestStack = $requestStack;
        $this->security = $security;
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
                'type' => 'text',
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

    public function onStoreFormData(StoreFormDataEvent $event): void
    {
        if ($event->getForm()->ml_archive
            && ($archiveModel = ProductArchiveModel::findByPk($event->getForm()->ml_archive)))
        {
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

        parent::onStoreFormData($event);
    }

    public function onProcessFormData(ProcessFormDataEvent $event): void
    {
        parent::onProcessFormData($event);

        if (!class_exists(HeimrichHannotFileCreditsBundle::class)) {
            return;
        }

        if (empty($event->getSubmittedData()['copyright']) || !isset($event->getFiles()['file']['uuid'])) {
            return;
        }

        $fileModel = FilesModel::findByUuid($event->getFiles()['file']['uuid']);

        if (!$fileModel) {
            return;
        }

        $fileModel->copyright = $event->getSubmittedData()['copyright'];
        $fileModel->save();
    }

    protected function evaluateFormContext(): FormContext
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if (!$request->query->has(static::PARAMETER_EDIT)) {
            return FormContext::create(static::DEFAULT_FORM_CONTEXT_TABLE);
        }

        $id = $request->query->get(static::PARAMETER_EDIT);
        if  (!$id || !is_numeric($id)) {
            return FormContext::invalid('tl_md_product', 'Invalid product id.');
        }
        $productModel = ProductModel::findByPk($id);
        if (!$productModel) {
            return FormContext::invalid('tl_md_product', 'Could not find product.');
        }

        if ($this->security->isGranted())

        $archiveModel = ProductArchiveModel::findByPk($productModel->pid);
        if (!$archiveModel || !$archiveModel->allowEdit) {
            return FormContext::invalid('tl_md_product', 'Archive does not allow editing.');
        }

        if (!$this->security->isGranted('edit', $archiveModel)) {
            return FormContext::invalid('tl_md_product', 'You are not allowed to edit this archive.');
        }



        $editParameter = 'edit';

        if ($modelPk = $request->query->get($editParameter))
        {
            /** @var class-string<Model> $modelClass */
            $modelClass = Model::getClassFromTable(static::DEFAULT_FORM_CONTEXT_TABLE);
            $modelInstance = $modelClass::findByPk($modelPk);
            if ($modelInstance === null) {
                return FormContext::invalid(static::DEFAULT_FORM_CONTEXT_TABLE, 'Could not find object.');
            }
            return FormContext::update(static::DEFAULT_FORM_CONTEXT_TABLE, $modelInstance->row());
        }

        return FormContext::create(static::DEFAULT_FORM_CONTEXT_TABLE);
        return parent::evaluateFormContext();
    }


}
