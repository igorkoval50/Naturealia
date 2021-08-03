<?php declare(strict_types=1);

namespace NetzpBlog6\Core\Content\Blog;

use DateTimeImmutable;
use NetzpBlog6\Core\Content\Author\AuthorEntity;
use NetzpBlog6\Core\Content\BlogMedia\BlogMediaCollection;
use NetzpBlog6\Core\Content\Category\CategoryCollection;
use NetzpBlog6\Core\Content\Category\CategoryEntity;
use NetzpBlog6\Core\Content\Item\ItemCollection;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\Tag\TagCollection;
use NetzpBlog6\Core\Content\Aggregate\BlogTranslation\BlogTranslationCollection;

class BlogEntity extends Entity
{
    use EntityIdTrait;

    protected $postdate;
    protected $showfrom;
    protected $showuntil;
    protected $noindex;

    /**
     * @var BlogTranslationCollection|null
     */
    protected $translations;

    /**
     * @var ItemCollection|null
     */
    protected $items;

    /**
     * @var BlogMediaCollection|null
     */
    protected $blogmedia;

    /**
     * @var MediaEntity|null
     */
    protected $image;
    protected $imageid;

    /**
     * @var MediaEntity|null
     */
    protected $imagepreview;
    protected $imagepreviewid;

    /**
     * @var CategoryEntity|null
     */
    protected $category;
    protected $categoryid;

    /**
     * @var AuthorEntity|null
     */
    protected $author;
    protected $authorid;

    /**
     * @var CategoryCollection|null
     */
    protected $categories;

    /**
     * @var ProductCollection|null
     */
    protected $products;

    /**
     * @var TagCollection|null
     */
    protected $tags;

    /**
     * @var array|null
     */
    protected $customFields;

    public function getPostdate(): ?DateTimeImmutable { return $this->postdate; }
    public function setPostdate(DateTimeImmutable $value): void { $this->postdate = $value; }

    public function getShowfrom(): ?DateTimeImmutable { return $this->showfrom; }
    public function setShowfrom(DateTimeImmutable $value): void { $this->showfrom = $value; }

    public function getShowuntil(): ?DateTimeImmutable { return $this->showuntil; }
    public function setShowuntil(DateTimeImmutable $value): void { $this->showuntil = $value; }

    public function getNoindex(): ?bool { return $this->noindex; }
    public function setNoindex(?bool $value): void { $this->noindex = $value; }

    public function getImageid(): ?string { return $this->imageid; }
    public function setImageid(string $value): void { $this->imageid = $value; }

    public function getImage(): ?MediaEntity { return $this->image; }
    public function setImage(MediaEntity $value): void { $this->image = $value; }

    public function getImagepreviewid(): ?string { return $this->imagepreviewid; }
    public function setImagepreviewid(string $value): void { $this->imagepreviewid = $value; }

    public function getImagepreview(): ?MediaEntity { return $this->imagepreview; }
    public function setImagepreview(MediaEntity $value): void { $this->imagepreview = $value; }

    public function getCategoryid(): ?string { return $this->categoryid; }
    public function setCategoryid(string $value): void { $this->categoryid = $value; }

    public function getCategory(): ?CategoryEntity { return $this->category;}
    public function setCategory(CategoryEntity $value): void { $this->category = $value; }

    public function getAuthorid(): ?string { return $this->authorid; }
    public function setAuthorid(string $value): void { $this->authorid = $value; }

    public function getAuthor(): ?AuthorEntity { return $this->author;}
    public function setAuthor(AuthorEntity $value): void { $this->author = $value; }

    public function getCategories(): ?CategoryCollection { return $this->categories; }
    public function setCategories(CategoryCollection $categories): void { $this->categories = $categories; }

    public function getProducts(): ?ProductCollection { return $this->products; }
    public function setProducts(ProductCollection $products): void { $this->products = $products; }

    public function getTags(): ?TagCollection { return $this->tags; }
    public function setTags(TagCollection $tags): void { $this->tags = $tags; }

    public function getItems(): ?ItemCollection { return $this->items; }
    public function setItems(ItemCollection $value): void { $this->items = $value; }

    public function getBlogMedia(): ?BlogMediaCollection { return $this->blogmedia; }
    public function setBlogMedia(BlogMediaCollection $value): void { $this->blogmedia = $value; }

    public function getCustomFields(): ?array { return $this->customFields; }
    public function setCustomFields(?array $customFields): void { $this->customFields = $customFields; }

    public function getTranslations(): ?BlogTranslationCollection { return $this->translations; }
    public function setTranslations(?BlogTranslationCollection $translations): void { $this->translations = $translations; }
}
