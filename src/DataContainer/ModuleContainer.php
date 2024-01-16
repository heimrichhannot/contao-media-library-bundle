<?php

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Input;
use Contao\Message;
use Contao\ModuleModel;
use HeimrichHannot\MediaLibraryBundle\Controller\FrontendModule\ProductListModuleController;
use Symfony\Contracts\Translation\TranslatorInterface;

class ModuleContainer
{
    public function __construct(
        private TranslatorInterface $translator,
    )
    {
    }

    /**
     * @Callback(table="tl_module", target="config.onload")
     */
    public function onConfigLoadCallback(DataContainer $dc = null): void
    {
        if ('edit' !== Input::get('act') || !$dc || !$dc->id || !($module = ModuleModel::findByPk($dc->id))) {
            return;
        }

        if (ProductListModuleController::TYPE !== $module->type) {
            return;
        }

        Message::addInfo($this->translator->trans('huh.mediaLibrary.backend.module.notice_internal'));
    }
}