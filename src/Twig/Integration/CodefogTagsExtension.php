<?php

namespace HeimrichHannot\MediaLibraryBundle\Twig\Integration;

use Composer\InstalledVersions;
use Contao\Database;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CodefogTagsExtension extends AbstractExtension
{
    private bool $_isCodefogTagsInstalled;

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ml_cfg_similar_items', $this->cfgSimilarItems(...)),
        ];
    }

    protected function isCodefogTagsInstalled(): bool
    {
        if (!isset($this->_isCodefogTagsInstalled)) {
            $this->_isCodefogTagsInstalled = InstalledVersions::isInstalled('codefog/tags-bundle');
        }

        return $this->_isCodefogTagsInstalled;
    }

    /**
     * Get similar items based on shared tags (requires codefog/tags-bundle).
     *
     * @return ItemModel[]
     */
    public function cfgSimilarItems(ItemModel $item, int $limit = 5): array
    {
        if (!$this->isCodefogTagsInstalled()) {
            return [];
        }

        $db = Database::getInstance();

        $relatedItemIds = $db->prepare("
            SELECT
                rel.ml_item_id as ml_item_id,
                COUNT(DISTINCT rel.cfg_tag_id) AS sharedTagCount
              FROM tl_cfg_tag_ml_item AS base
              JOIN tl_cfg_tag_ml_item AS rel ON base.cfg_tag_id = rel.cfg_tag_id
              JOIN tl_ml_item AS item ON item.id = rel.ml_item_id
             WHERE base.ml_item_id = ?
               AND rel.ml_item_id <> base.ml_item_id
               AND item.pid = ?
             GROUP BY rel.ml_item_id
             ORDER BY sharedTagCount DESC
             LIMIT $limit
        ")
            ->execute($item->id, $item->pid)
            ->fetchEach('ml_item_id');

        return ItemModel::findMultipleByIds($relatedItemIds)?->getModels() ?? [];
    }
}