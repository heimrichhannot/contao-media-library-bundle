<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\String\SimpleTokenParser;
use Contao\DataContainer;
use Contao\File;
use Contao\MemberModel;
use Contao\Model;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;

#[AsCallback(ItemContainer::TABLE, 'fields.file.uploadPathCallback')]
readonly class FileUploadPathCallback
{
    public const SKIP_FIELDS = [
        'password' => true,
        'salt' => true,
        'reset' => true,
        'reset_token' => true,
    ];

    public function __construct(
        private array             $bundleConfig,
        private SimpleTokenParser $parser,
    ) {}

    public function __invoke(string $target, File $file, DataContainer $dc): string
    {
        $activeRecord = $dc->activeRecord;

        if (!$activeRecord instanceof ItemModel) {
            return $target;
        }

        if (!$activeRecord->author || !$activeRecord->title) {
            return $target;
        }

        return $this->mainFileUploadPath($activeRecord) . \ltrim($file->basename, '/');
    }

    public function collectPathContext(
        string                 $author,
        string                 $title,
        ItemModel|null         $item = null,
        MemberModel|Model|null $member = null,
    ): array {
        $context = [
            '##author##' => $author,
            '##title##' => $title,
        ];

        if ($item) {
            $context = $this->rowFill('ml_item', $item->row(), $context);
        }

        if ($member) {
            $context = $this->rowFill('member', $member->row(), $context);
        }

        return $context;
    }

    protected function rowFill(string $prefix, array $row, array $context = []): array
    {
        foreach ($row as $key => $value)
        {
            if (self::SKIP_FIELDS[$key] ?? false) {
                continue;
            }

            $context["##{$prefix}_{$key}##"] = $value;
        }

        return $context;
    }

    public function mainFileUploadPath(ItemModel $item): string
    {
        $author = $item->author ? MemberModel::findByPk($item->author) : null;
        $author = $author instanceof MemberModel ? $author : null;

        $context = $this->collectPathContext(
            author: $author->id,
            title: $item->title,
            item: $item,
        );

        return $this->fileUploadPath($context);
    }

    public function fileUploadPath(array $context): string
    {
        if (!$uploadPath = $this->bundleConfig['file_upload_path'] ?? null) {
            throw new \RuntimeException('The file_upload_path config option is not set.');
        }

        return $this->parser->parse($uploadPath, $context, allowHtml: false);
    }
}