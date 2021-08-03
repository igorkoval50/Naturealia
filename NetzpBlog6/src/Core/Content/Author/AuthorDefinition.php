<?php declare(strict_types=1);

namespace NetzpBlog6\Core\Content\Author;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use NetzpBlog6\Core\Content\Aggregate\AuthorTranslation\AuthorTranslationDefinition;

class AuthorDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 's_plugin_netzp_blog_author';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return AuthorEntity::class;
    }

    public function getCollectionClass(): string
    {
        return AuthorCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            new IdField('imageid', 'imageid'),

            (new TranslatedField('name'))->addFlags(new Required()),
            new TranslatedField('bio'),
            new TranslatedField('customFields'),

            new ManyToOneAssociationField('image', 'imageid', MediaDefinition::class, 'id', true),
            new TranslationsAssociationField(AuthorTranslationDefinition::class, 's_plugin_netzp_blog_author_id')
        ]);
    }
}
