<?php declare(strict_types=1);
namespace Pixup\Wishlist\Entitys\Definitions;

use Pixup\Wishlist\Entitys\Aggregate\WishlistCustomer\Definitions\WishlistCustomerDefinition;
use Pixup\Wishlist\Entitys\Collections\WishlistBirthdayCollection;
use Pixup\Wishlist\Entitys\Model\WishlistBirthdayModel;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use \Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class WishlistBirthdayDefinition extends EntityDefinition
{
    public const ENTITY_NAME = "pixup_wish_list_birthday";

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }
    public function getCollectionClass(): string
    {
        return WishlistBirthdayCollection::class;
    }

    public function getEntityClass(): string
    {
        return WishlistBirthdayModel::class;
    }

    public function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('customer_id','customerId',WishlistCustomerDefinition::class))->addFlags(new Required(),new CascadeDelete(), new PrimaryKey()),
            (new ManyToOneAssociationField('customer','customer_id',WishlistCustomerDefinition::class,'id',true)),
            (new FkField('wishlist_id','wishlistId',WishlistDefinition::class))->addFlags(new Required(),new CascadeDelete(), new PrimaryKey()),
            (new ManyToOneAssociationField('wishlist','wishlist_id',WishlistDefinition::class,'id',true)),
            (new FkField('product_id','productId',ProductDefinition::class))->addFlags(new Required(),new CascadeDelete(), new PrimaryKey()),
            (new ManyToOneAssociationField('product','product_id',ProductDefinition::class,'id',true)),
            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
