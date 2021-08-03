<?php declare(strict_types=1);

namespace NetzpBlog6\Core\Content\Aggregate\BlogTranslation;

use NetzpBlog6\Core\Content\Blog\BlogEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class BlogTranslationEntity extends TranslationEntity
{
    protected $blogId;
    protected $title;
    protected $slug;
    protected $contents;
    protected $custom;
    protected $metatitle;
    protected $metadescription;

    /**
     * @var BlogEntity
     */
    protected $blog;

    /**
     * @var array|null
     */
    protected $customFields;

    public function getBlogId(): ?string { return $this->blogId; }
    public function setBlogId(string $value): void { $this->blogId = $value; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $value): void { $this->title = $value; }

    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $value): void { $this->slug = $value; }

    public function getContents(): ?string { return $this->contents; }
    public function setContents(string $value): void { $this->contents = $value; }

    public function getCustom(): ?string { return $this->custom; }
    public function setCustom(string $value): void { $this->custom = $value; }

    public function getMetatitle(): ?string { return $this->metatitle; }
    public function setMetatitle(string $value): void { $this->metatitle = $value; }

    public function getMetadescription(): ?string { return $this->metadescription; }
    public function setMetadescription(string $value): void { $this->metadescription = $value; }

    public function getBlog(): BlogEntity { return $this->blog; }
    public function setBlog(BlogEntity $blog): void { $this->blog = $blog; }

    public function getCustomFields(): ?array { return $this->customFields; }
    public function setCustomFields(?array $customFields): void { $this->customFields = $customFields; }
}
