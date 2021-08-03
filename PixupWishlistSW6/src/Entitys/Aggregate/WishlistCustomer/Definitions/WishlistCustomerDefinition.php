<?php declare(strict_types=1);

namespace Pixup\Wishlist\Entitys\Aggregate\WishlistCustomer\Definitions;
use Pixup\Wishlist\Entitys\Aggregate\WishlistCustomer\Collection\WishlistCustomerCollection;
use Pixup\Wishlist\Entitys\Aggregate\WishlistCustomer\Model\WishlistCustomerModel;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class WishlistCustomerDefinition extends EntityDefinition
{

    public const ENTITY_NAME = "pixup_wish_list_customers";

    public function getEntityName(): string{
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return WishlistCustomerCollection::class;
    }

    public function getEntityClass(): string
    {
        return WishlistCustomerModel::class;
    }

    protected function defineFields(): FieldCollection{
        return new FieldCollection([
            (new IdField('id','id'))->addFlags(new PrimaryKey(),new Required()), // this represents the cookieID for cookie Users
            //(new FkField('customer_id','customerId',CustomerDefinition::class))->addFlags(new CascadeDelete()),
            (new IdField('customer_id','customerId')),
            new ManyToOneAssociationField('customer','customer_id',CustomerDefinition::class,'id',true),
            new CreatedAtField(),
            new UpdatedAtField()
        ]);
    }
}
