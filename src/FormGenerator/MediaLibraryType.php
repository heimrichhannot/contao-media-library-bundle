<?php

namespace HeimrichHannot\MediaLibraryBundle\FormGenerator;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Database;
use Contao\DataContainer;
use Contao\Folder;
use Contao\Form;
use Contao\FormModel;
use Contao\MemberModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Validator;
use Contao\Widget;
use HeimrichHannot\FileCreditsBundle\HeimrichHannotFileCreditsBundle;
use HeimrichHannot\FileCreditsBundle\Model\FilesModel;
use HeimrichHannot\FormTypeBundle\Event\LoadFormFieldEvent;
use HeimrichHannot\FormTypeBundle\Event\PrepareFormDataEvent;
use HeimrichHannot\FormTypeBundle\Event\ProcessFormDataEvent;
use HeimrichHannot\FormTypeBundle\Event\StoreFormDataEvent;
use HeimrichHannot\FormTypeBundle\FormType\AbstractFormType;
use HeimrichHannot\FormTypeBundle\FormType\FormContext;
use HeimrichHannot\MediaLibraryBundle\EventListener\DataContainer\FileUploadPathCallback;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use HeimrichHannot\MediaLibraryBundle\Security\Voter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaLibraryType extends AbstractFormType
{
    public const TYPE = 'huh_media_library';
    public const PARAMETER_EDIT = 'edit';
    protected const DEFAULT_FORM_CONTEXT_TABLE = 'tl_ml_item';

    protected array $uploadPathCache = [];

    public function __construct(
        private readonly FileUploadPathCallback     $uploadPath,
        private readonly RequestStack               $requestStack,
        private readonly Security                   $security,
        private readonly TokenChecker               $tokenChecker,
        private readonly TranslatorInterface        $translator,
        private readonly VirtualFilesystemInterface $filesStorage,
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

        $defaultUploadPath = $this->uploadPath->fileUploadPath(
            $this->uploadPath->collectPathTokens('default', 'default')
        );

        $folder = new Folder($defaultUploadPath);
        $uuid = $folder->getModel()->uuid;

        $table = ItemModel::getTable();
        $domain = 'contao_' . $table;

        $fields = [
            [
                'name' => 'title',
                'type' => 'text',
                'label' => $this->translator->trans("{$table}.title.0", [], $domain),
                'mandatory' => '1',
            ],
            [
                'name' => 'file',
                'type' => 'upload',
                'label' => $this->translator->trans("{$table}.file.0", [], $domain),
                'extensions' => 'jpg,jpeg,gif,png',
                'mandatory' => '1',
                'storeFile' => '1',
                'uploadFolder' => $uuid,
                'doNotOverwrite' => '1',
            ],
            [
                'name' => 'text',
                'type' => 'textarea',
                'label' => $this->translator->trans("{$table}.text.0", [], $domain),
            ],
        ];

        if (\class_exists(HeimrichHannotFileCreditsBundle::class))
        {
            $fields[] = [
                'name' => 'copyright',
                'type' => 'textarea',
                'label' => $this->translator->trans("{$table}.copyright.0", [], $domain),
            ];
        }

        $fields[] = [
            'name' => 'additionalFiles',
            'type' => 'upload',
            'label' => $this->translator->trans("{$table}.additionalFiles.0", [], $domain),
            'extensions' => 'jpg,jpeg,gif,png',
            'mandatory' => '1',
            'storeFile' => '1',
            'uploadFolder' => $uuid,
            'doNotOverwrite' => '1',
        ];

        return $fields;
    }

    public function onLoadFormField(LoadFormFieldEvent $event): void
    {
        $widget = $event->getWidget();

        match ($widget->name) {
            'file', 'additionalFiles' => $this->updateWidgetUploadPath($widget, $event->getFormContext()),
            default => null,
        };

        if ($event->getFormContext()->isUpdate())
        {
            $this->contextUpdate_onLoadFormField($event);
        }
    }

    public function getCurrentMemberUploadPath(FormContext $context): ?string
    {
        if (!$request = $this->requestStack->getCurrentRequest()) {
            return null;
        }

        $title = $request->request->get('title', $context->getData()['title'] ?? null);
        $feUsername = $this->tokenChecker->hasFrontendUser() ? $this->tokenChecker->getFrontendUsername() : null;

        if ($title && $feUsername)
        {
            $cacheKey = $feUsername . ':' . $title;

            if (isset($this->uploadPathCache[$cacheKey])) {
                return $this->uploadPathCache[$cacheKey];
            }
        }

        $member = MemberModel::findByUsername($feUsername) ?: null;

        $author = match (true) {
            $member instanceof MemberModel => (string) $member->id,
            $this->tokenChecker->hasBackendUser() => 'be_' . $this->tokenChecker->getBackendUsername(),
            default => \uniqid('anon_', true),
        };

        $slugGenerator = new SlugGenerator();
        $slug = $slugGenerator->generate($title ?: \uniqid('auto_', true));

        $uploadPath = $this->uploadPath->fileUploadPath(
            $this->uploadPath->collectPathTokens(author: $author, title: $slug)
        );

        if (isset($cacheKey)) {
            $this->uploadPathCache[$cacheKey] = $uploadPath;
        }

        return $uploadPath;
    }

    public function updateWidgetUploadPath(Widget $widget, FormContext $context): void
    {
        if (!$uploadPath = $this->getCurrentMemberUploadPath($context)) {
            return;
        }

        $folder = new Folder($uploadPath);

        $widget->uploadFolder = $folder->getModel()->uuid;
    }

    private function contextUpdate_onLoadFormField(LoadFormFieldEvent $event): void
    {
        $widget = $event->getWidget();
        $name = $widget->name;

        if ($name === 'file')
        {
            $widget->mandatory = '';
        }

        if ($name === 'copyright'
            && \class_exists(HeimrichHannotFileCreditsBundle::class)
            && ($fileUuid = $event->getFormContext()->getData()['file'] ?? null)
            && ($fileModel = FilesModel::findByUuid($fileUuid)))
        {
            $widget->value = implode("\n", StringUtil::deserialize($fileModel->copyright, true));
        }
    }

    public function onPrepareFormData(PrepareFormDataEvent $event): void
    {
        if ($archiveModel = ArchiveModel::findByPk($event->form->ml_archive))
        {
            $slugGenerator = new SlugGenerator();

            $event->form->storeValues = '1';
            $event->form->targetTable = ItemModel::getTable();

            $event->data['pid'] = $archiveModel->id;
            $event->data['dateAdded'] = \time();
            $event->data['alias'] = $slugGenerator->generate($event->data['title']);
            $event->data['type'] = $archiveModel->type;
            $event->data['published'] = ($event->form->ml_publish ?? false) ? '1' : '';
        }

        if (!empty($event->data['additionalFiles']))
        {
            $event->data['addAdditionalFiles'] = '1';
        }

        parent::onPrepareFormData($event);
    }

    public function onStoreFormData(StoreFormDataEvent $event): void
    {
        $form = $event->getForm();

        if (!$pid = $form->ml_archive ?? null) {
            return;
        }

        if (!ArchiveModel::findByPk($pid))
            // Validate parent archive exists to prevent downstream errors
        {
            return;
        }

        $table = ItemModel::getTable();

        $fieldNames = Database::getInstance()->getFieldNames($table);
        $data = \array_intersect_key($event->getData(), \array_flip($fieldNames));

        $data = empty($_SESSION['FILES'])
            ? $this->transformRegularFileData($table, $data)
            : $this->transformSessionFileData($table, $data);

        $event->setData($data);
    }

    public function transformSessionFileData(string $table, array $data): array
    {
        Controller::loadDataContainer($table);

        $fields = &$GLOBALS['TL_DCA'][$table]['fields'];

        foreach ($_SESSION['FILES'] as $fieldName => $fileData)
        {
            $field = $fields[$fieldName] ?? null;

            if (!isset($data[$fieldName]) || !$field || !\is_array($field)) {
                continue;
            }

            $fieldData = &$data[$fieldName];

            if (!$uuid = $fileData['uuid'] ?? null) {
                continue;
            }

            $fieldData = StringUtil::uuidToBin($uuid);

            $fieldType = $field['eval']['fieldType'] ?? null;
            $fieldMultiple = \filter_var(
                $field['eval']['multiple'] ?? false,
                \FILTER_VALIDATE_BOOLEAN,
                \FILTER_NULL_ON_FAILURE
            );

            if ($fieldType === 'checkbox' || $fieldMultiple) {
                $fieldData = \serialize([$uuid]);
            }
        }

        return $data;
    }

    public function transformRegularFileData(string $table, array $data): array
    {
        Controller::loadDataContainer($table);

        $fields = &$GLOBALS['TL_DCA'][$table]['fields'];

        foreach ($data as $fieldName => &$value)
        {
            if (!$field = $fields[$fieldName] ?? null) {
                continue;
            }

            if ($field['inputType'] !== 'fileTree') {
                continue;
            }

            $multiple = \filter_var(
                $field['eval']['multiple'] ?? false,
                \FILTER_VALIDATE_BOOLEAN,
                \FILTER_NULL_ON_FAILURE
            );

            if (!$multiple) {
                $value = $this->normalizeFileValue($value);
                continue;
            }

            $values = match (true) {
                \is_string($value) => StringUtil::deserialize($value, true),
                \is_array($value) => $value,
                default => [],
            };

            $values = \array_map(fn (mixed $v) => $this->normalizeFileValue($v), $values);

            $value = \serialize($values);
        }
        unset($value);

        return $data;
    }

    public function normalizeFileValue(mixed $value): ?string
    {
        if (!$value || !\is_string($value)) {
            return null;
        }

        if (Validator::isUuid($value))
        {
            return match (true) {
                Validator::isBinaryUuid($value) => $value,
                Validator::isStringUuid($value) => StringUtil::uuidToBin($value),
                default => null,
            };
        }

        if ($file = $this->filesStorage->get($value)
            ?? $this->filesStorage->get(\preg_replace('/^\/?files\//i', '', $value)))
        {
            return $file->getUuid()?->toBinary();
        }

        return null;
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
            && ($archiveModel = ArchiveModel::findByPk($form->ml_archive))
            && ($detailsJumpTo = PageModel::findByPk($archiveModel->jumpTo))
        ) {
            // Only reader pages take the item alias as an auto item; appending it to a
            // regular page (e.g. an overview) would produce a 404.
            $detailsJumpTo->loadDetails();
            $alias = $event->getSubmittedData()['alias'] ?? null;
            $params = ($detailsJumpTo->requireItem && $alias) ? '/'.$alias : '';

            $url = $detailsJumpTo->getAbsoluteUrl($params);
            $_SESSION['FILES'] = [];
            Controller::redirect($url);
        }
    }

    protected function createAccessDeniedException(?string $message = null): AccessDeniedException
    {
        $message ??= 'No permission to edit product.';

        return new AccessDeniedException($message);
    }

    protected function createPageNotFoundException(?string $message = null): PageNotFoundException
    {
        $message ??= 'Product not found.';

        return new PageNotFoundException($message);
    }

    protected function evaluateFormContext(Form $form): FormContext
    {
        if (!$request = $this->requestStack->getCurrentRequest()) {
            return FormContext::invalid(static::DEFAULT_FORM_CONTEXT_TABLE);
        }

        if ($request->query->has(static::PARAMETER_EDIT))
            // Edit product
        {
            $id = $request->query->get(static::PARAMETER_EDIT);

            return $this->evaluateFormContext_editParameter($id);
        }

        if (!$form->ml_archive || !$mlArchive = ArchiveModel::findByPk($form->ml_archive)) {
            throw $this->createPageNotFoundException('Could not find media library archive.');
        }

        if (!$this->security->isGranted(Voter::PERMISSION_CREATE, $mlArchive)) {
            throw $this->createAccessDeniedException();
        }

        return FormContext::create(static::DEFAULT_FORM_CONTEXT_TABLE);
    }

    protected function evaluateFormContext_editParameter(mixed $id): FormContext
    {
        if  (!\is_numeric($id) || !($productModel = ItemModel::findByPk($id))) {
            throw new PageNotFoundException('Product not found!');
        }

        if (!$this->security->isGranted(Voter::PERMISSION_EDIT, $productModel)) {
            throw $this->createAccessDeniedException();
        }

        return FormContext::update(static::DEFAULT_FORM_CONTEXT_TABLE, $productModel->row());
    }

    public static function getSubscribedServices(): array
    {
        return \array_merge(parent::getSubscribedServices(), [
            'request_stack' => '?request_stack',
        ]);
    }
}