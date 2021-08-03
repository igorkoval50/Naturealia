<?php declare(strict_types=1);

namespace NetzpBlog6\Core\Content\Item;

use NetzpBlog6\Core\Content\Blog\BlogEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ItemEntity extends Entity
{
    use EntityIdTrait;

    protected $number;

    /**
     * @var MediaEntity|null
     */
    protected $image;
    protected $imageid;

    /**
     * @var BlogEntity|null
     */
    protected $blog;
    protected $blogId;

    public function getNumber(): ?int { return $this->number; }
    public function setNumber(int $value): void { $this->number = $value; }

    public function getImageId(): ?string { return $this->imageid; }
    public function setImageId(string $value): void { $this->imageid = $value; }

    public function getImage(): ?MediaEntity { return $this->image; }
    public function setImage(MediaEntity $value): void { $this->image = $value; }

    public function getBlogId(): ?string { return $this->blogId; }
    public function setBlogId(string $value): void { $this->blogId = $value; }

    public function getBlog(): ?BlogEntity { return $this->blog; }
    public function setBlog(BlogEntity $value): void { $this->blog = $value; }
}
