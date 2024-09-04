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
use Contao\Form;
use Contao\FormModel;
use Contao\PageModel;
use Contao\StringUtil;
use HeimrichHannot\FileCreditsBundle\HeimrichHannotFileCreditsBundle;
use HeimrichHannot\FileCreditsBundle\Model\FilesModel;
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
    public const PARAMETER_EDIT = 'edit';
    protected const DEFAULT_FORM_CONTEXT_TABLE = 'tl_ml_product';

    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly Slug $slug,
        protected readonly TranslatorInterface $translator,
        protected readonly Security $security
    ) {}

    public function getType(): string
    {
        return static::TYPE;
    }

    public function onload(DataContainer $dataContainer, FormModel $formModel): void
    {
        PaletteManipulator::create()
            ->removeField('storeValues')
            ->addField('ml_redirectToElement', 'jumpTo')
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

    public function onLoadFormField(LoadFormFieldEvent $event): void
    {
        if ($event->getFormContext()->isUpdate()) {
            $this->contextUpdate_onLoadFormField($event);
        }
    }

    private function contextUpdate_onLoadFormField(LoadFormFieldEvent $event): void
    {
        $widget = $event->getWidget();
        $name = $widget->name;

        if ($name === 'file')
        {
            $widget->mandatory = '';
        }

        if ($name === 'copyright' && \class_exists(HeimrichHannotFileCreditsBundle::class))
        {
            if ($fileModel = FilesModel::findByUuid($event->getFormContext()->getData()['file']))
            {
                $widget->value = implode("\n", StringUtil::deserialize($fileModel->copyright, true));
            }
        }
    }

    public function onPrepareFormData(PrepareFormDataEvent $event): void
    {
        $form = $event->getForm();

        $archiveModel = ProductArchiveModel::findByPk($form->ml_archive);

        if ($archiveModel)
        {
            $form->storeValues = '1';
            $form->targetTable = ProductModel::getTable();

            $data = $event->getData();

            $data['pid'] = $archiveModel->id;
            $data['dateAdded'] = \time();
            $data['alias'] = $this->slug->generate($data['title']);
            $data['type'] = $archiveModel->type;
            $data['published'] = ($form->ml_publish ?? false) ? '1' : '';

            $event->setData($data);
        }

        parent::onPrepareFormData($event);
    }

    public function onStoreFormData(StoreFormDataEvent $event): void
    {
        $form = $event->getForm();

        $pid = $form->ml_archive ?? null;
        if (!$pid) {
            return;
        }

        $archiveModel = ProductArchiveModel::findByPk($pid);
        if (!$archiveModel) {
            return;
        }

        $table = ProductModel::getTable();

        $fieldNames = Database::getInstance()->getFieldNames($table);
        $data = \array_intersect_key($event->getData(), \array_flip($fieldNames));

        if (empty($_SESSION['FILES']))
        {
            $event->setData($data);
            return;
        }

        Controller::loadDataContainer($table);

        foreach ($_SESSION['FILES'] as $fieldName => $fieldData)
        {
            $field = $GLOBALS['TL_DCA'][$table]['fields'][$fieldName] ?? null;

            if (!isset($data[$fieldName]) || empty($field)) {
                continue;
            }

            $data[$fieldName] = StringUtil::uuidToBin($fieldData['uuid']);

            $fieldType = $field['eval']['fieldType'] ?? null;
            $fieldMultiple = $field['eval']['multiple'] ?? null;

            if ($fieldType === 'checkbox' || $fieldMultiple === true) {
                $data[$fieldName] = \serialize([$fieldData['uuid']]);
            }
        }

        $event->setData($data);
    }

    public function onProcessFormData(ProcessFormDataEvent $event): void
    {
        parent::onProcessFormData($event);

        $form = $event->getForm();

        if (\class_exists(HeimrichHannotFileCreditsBundle::class))
        {
            $context = $this->getFormContext($form);

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

        if ($form->ml_redirectToElement
            && ($archiveModel = ProductArchiveModel::findByPk($form->ml_archive))
            && ($detailsJumpTo = PageModel::findByPk($archiveModel->jumpTo))
        ) {
            $url = $detailsJumpTo->getAbsoluteUrl('/'.$event->getSubmittedData()['alias']);
            $_SESSION['FILES'] = array();
            Controller::redirect($url);
        }
    }

    protected function evaluateFormContext(Form $form): FormContext
    {
        $request = $this->requestStack->getCurrentRequest();

        $accessDenied = new AccessDeniedException('No permission to edit product.');

        if ($request->query->has(static::PARAMETER_EDIT))
            // Edit product
        {
            $id = $request->query->get(static::PARAMETER_EDIT);

            if  (!\is_numeric($id) || !($productModel = ProductModel::findByPk($id))) {
                throw new PageNotFoundException('Product not found!');
            }

            if (!$this->security->isGranted(ProductVoter::PERMISSION_EDIT, $productModel)) {
                throw $accessDenied;
            }

            return FormContext::update(static::DEFAULT_FORM_CONTEXT_TABLE, $productModel->row());
        }

        $mlArchive = ProductArchiveModel::findByPk($form->ml_archive);

        if (!$this->security->isGranted(ProductVoter::PERMISSION_CREATE, $mlArchive)) {
            throw $accessDenied;
        }

        return FormContext::create(static::DEFAULT_FORM_CONTEXT_TABLE);
    }

    public static function getSubscribedServices(): array
    {
        return \array_merge(parent::getSubscribedServices(), [
            'request_stack' => '?request_stack',
        ]);
    }
}