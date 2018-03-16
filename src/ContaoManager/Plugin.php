<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 13.03.18
 * Time: 15:18
 */

namespace HeimrichHannot\MediaLibraryBundle\ContaoManager;


use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use HeimrichHannot\MediaLibraryBundle\HeimrichHannotContaoMediaLibraryBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(HeimrichHannotContaoMediaLibraryBundle::class)->setLoadAfter([ContaoCoreBundle::class, 'bootstrapper']),
        ];
    }
}