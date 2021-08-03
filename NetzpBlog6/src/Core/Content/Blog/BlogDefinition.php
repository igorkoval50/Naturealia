<?php declare(strict_types=1);

namespace NetzpBlog6\Core\Content\Blog;

use NetzpBlog6\Core\Content\Aggregate\BlogCategory\BlogCategoryDefinition;
use NetzpBlog6\Core\Content\Aggregate\BlogProduct\BlogProductDefinition;
use NetzpBlog6\Core\Content\Aggregate\BlogTag\BlogTagDefinition;
use NetzpBlog6\Core\Content\Author\AuthorDefinition;
use NetzpBlog6\Core\Content\BlogMedia\BlogMediaDefinition;
use NetzpBlog6\Core\Content\Category\CategoryDefinition;
use NetzpBlog6\Core\Content\Item\ItemDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use NetzpBlog6\Core\Content\Aggregate\BlogTranslation\BlogTranslationDefinition;
use Shopware\Core\System\Tag\TagDefinition;

class BlogDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 's_plugin_netzp_blog';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return BlogEntity::class;
    }

    public function getCollectionClass(): string
    {
        return BlogCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),

            (new DateField('postdate', 'postdate'))->addFlags(new Required()),
            new DateField('showfrom', 'showfrom'),
            new DateField('showuntil', 'showuntil'),
            new BoolField('noindex', 'noindex'),
            new IdField('imageid', 'imageid'),
            new IdField('imagepreviewid', 'imagepreviewid'),
            new IdField('categoryid', 'categoryid'),
            new IdField('authorid', 'authorid'),

            (new TranslatedField('title'))->addFlags(new Required()),
            new TranslatedField('teaser'),
            (new TranslatedField('slug'))->addFlags(new Required()),
            (new TranslatedField('contents'))->addFlags(new Required()),
            new TranslatedField('custom'),
            new TranslatedField('metatitle'),
            new TranslatedField('metadescription'),
            new TranslatedField('customFields'),
            new TranslatedField('customFields'),

            new ManyToOneAssociationField('category', 'categoryid', CategoryDefinition::class, 'id', true),
            new ManyToOneAssociationField('author', 'authorid', AuthorDefinition::class, 'id', true),
            new ManyToOneAssociationField('image', 'imageid', MediaDefinition::class, 'id', true),
            new ManyToOneAssociationField('imagepreview', 'imagepreviewid', MediaDefinition::class, 'id', true),
            new TranslationsAssociationField(BlogTranslationDefinition::class, 's_plugin_netzp_blog_id'),

            new ManyToManyAssociationField('categories', CategoryDefinition::class, BlogCategoryDefinition::class, 'blog_id', 'category_id'),
            new ManyToManyAssociationField('products', ProductDefinition::class, BlogProductDefinition::class, 'blog_id', 'product_id'),
            new ManyToManyAssociationField('tags', TagDefinition::class, BlogTagDefinition::class, 'blog_id', 'tag_id'),
            (new OneToManyAssociationField('items', ItemDefinition::class, 'blog_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('blogmedia', BlogMediaDefinition::class, 'blog_id'))->addFlags(new CascadeDelete()),
        ]);
    }
}
