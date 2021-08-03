<?php declare(strict_types=1);

namespace NetzpBlog6\Core\Content\BlogMedia;

use NetzpBlog6\Core\Content\Blog\BlogDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class BlogMediaDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 's_plugin_netzp_blog_media';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return BlogMediaEntity::class;
    }

    public function getCollectionClass(): string
    {
        return BlogMediaCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            new IdField('blog_id', 'blogid'),

            new IntField('number', 'number'),

            new FkField('media_id', 'mediaId', MediaDefinition::class),
            new ManyToOneAssociationField('blog', 'blog_id', BlogDefinition::class, 'id', true),
            new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id', true)
        ]);
    }
}
