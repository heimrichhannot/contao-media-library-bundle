<?php

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Input;
use Contao\Message;
use Contao\ModuleModel;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class ModuleContainer
{
    public const TABLE = 'tl_module';

    public const PRODUCT_LIST_TYPE = 'ml_product_list';

    public function __construct(
        private TranslatorInterface $translator,
    ) {}

    #[AsCallback(self::TABLE, 'config.onload')]
    public function onConfigLoadCallback(DataContainer $dc = null): void
    {
        if (!$dc || !$dc->id || !Input::get('act') !== 'edit') {
            return;
        }

        if (!$module = ModuleModel::findByPk($dc->id)) {
            return;
        }

        if (self::PRODUCT_LIST_TYPE !== $module->type) {
            return;
        }

        Message::addInfo($this->translator->trans('huh.mediaLibrary.backend.module.notice_internal'));
    }
}