<?php declare(strict_types=1);

namespace NetzpBlog6\Core\Content\Aggregate\ItemTranslation;

use NetzpBlog6\Core\Content\Item\ItemEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class ItemTranslationEntity extends TranslationEntity
{
    protected $itemId;
    protected $title;
    protected $content;

    /**
     * @var ItemEntity
     */
    protected $item;

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $value): void { $this->title = $value; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(string $value): void { $this->content = $value; }
}
