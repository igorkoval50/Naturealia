<?php declare(strict_types=1);
namespace Pixup\Wishlist\Entitys\Definitions;

use Pixup\Wishlist\Entitys\Aggregate\WishlistCustomer\Definitions\WishlistCustomerDefinition;
use Pixup\Wishlist\Entitys\Aggregate\WishlistProducts\Definitions\WishlistProductsDefinition;
use Pixup\Wishlist\Entitys\Aggregate\WishlistSalesChannels\Definitions\WishlistSalesChannelsDefinition;
use Pixup\Wishlist\Entitys\Aggregate\WishlistSubscribers\Definitions\WishlistSubscribersDefinition;
use Pixup\Wishlist\Entitys\Model\WishlistModel;
use Pixup\Wishlist\Entitys\Collections\WishlistCollection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use \Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class WishlistDefinition extends EntityDefinition
{
    public const ENTITY_NAME = "pixup_wish_list";

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }
    public function getCollectionClass(): string
    {
        return WishlistCollection::class;
    }

    public function getEntityClass(): string
    {
        return WishlistModel::class;
    }

    public function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id','id'))->addFlags(new PrimaryKey(),new Required()),
            (new FkField('customer_id','customerId',WishlistCustomerDefinition::class))->addFlags(new Required())->addFlags(new CascadeDelete()),
            (new ManyToOneAssociationField('customer','customer_id',WishlistCustomerDefinition::class,'id',true)),
            (new StringField('name','name'))->addFlags(new Required()),
            (new BoolField('private','private'))->addFlags(new Required()),
            (new BoolField('editable','editable'))->addFlags(new Required()),
            (new BoolField('birthday','birthday'))->addFlags(new Required()),
            new StringField('password','password'),
            new CreatedAtField(),
            new UpdatedAtField(),
            new ManyToManyAssociationField(
                'products',
                ProductDefinition::class,
                WishlistProductsDefinition::class,
                'wishlist_id',
                'product_id'
            ),
            new ManyToManyAssociationField(
                'subscribers',
                WishlistCustomerDefinition::class,
                WishlistSubscribersDefinition::class,
                'wishlist_id',
                'customer_id'
            ),
            (new ManyToManyAssociationField(
                'salesChannels',
                SalesChannelDefinition::class,
                WishlistSalesChannelsDefinition::class,
                'wishlist_id',
                'sales_channel_id'
            ))->addFlags(new Required())
        ]);
    }
}
