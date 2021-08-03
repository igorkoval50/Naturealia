<?php declare(strict_types=1);

namespace NetzpBlog6\Core\Content\Author;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class AuthorEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var MediaEntity|null
     */
    protected $image;
    protected $imageid;

    /**
     * @var array|null
     */
    protected $customFields;

    public function getImageid(): ?string { return $this->imageid; }
    public function setImageid(string $value): void { $this->imageid = $value; }

    public function getImage(): ?MediaEntity { return $this->image; }
    public function setImage(MediaEntity $value): void { $this->image = $value; }

    public function getCustomFields(): ?array { return $this->customFields; }
    public function setCustomFields(?array $customFields): void { $this->customFields = $customFields; }
}
