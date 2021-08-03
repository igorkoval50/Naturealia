<?php declare(strict_types=1);

namespace NetzpBlog6\Core\Content\Category;

use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use NetzpBlog6\Core\Content\Aggregate\CategoryTranslation\CategoryTranslationDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class CategoryDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 's_plugin_netzp_blog_category';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return CategoryEntity::class;
    }

    public function getCollectionClass(): string
    {
        return CategoryCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),

            (new TranslatedField('title'))->addFlags(new Required()),
            new TranslatedField('teaser'),
            new TranslatedField('customFields'),

            new StringField('cmspageid', 'cmspageid'),
            new IdField('saleschannel_id', 'saleschannelid'),
            new IdField('customergroup_id', 'customergroupid'),
            new BoolField('onlyloggedin', 'onlyloggedin'),
            new BoolField('includeinrss', 'includeinrss'),

            new ManyToOneAssociationField('saleschannel', 'saleschannel_id', SalesChannelDefinition::class, 'id', true),
            new ManyToOneAssociationField('customergroup', 'customergroup_id', CustomerGroupDefinition::class, 'id', true),

            new TranslationsAssociationField(CategoryTranslationDefinition::class, 's_plugin_netzp_blog_category_id')
        ]);
    }
}
