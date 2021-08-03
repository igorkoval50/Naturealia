<?php declare(strict_types=1);

namespace Pixup\Wishlist\Entitys\Aggregate\WishlistSubscribers\Definitions;
use Pixup\Wishlist\Entitys\Aggregate\WishlistCustomer\Definitions\WishlistCustomerDefinition;
use Pixup\Wishlist\Entitys\Definitions\WishlistDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class WishlistSubscribersDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = "pixup_wish_list_subscribers";

    public function getEntityName(): string{
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection{
        return new FieldCollection([
            (new FkField('wishlist_id','wishlistId',WishlistDefinition::class))->addFlags(new PrimaryKey(),new Required()),
            (new FkField('customer_id','customerId',WishlistCustomerDefinition::class))->addFlags(new Required()),
            new ManyToOneAssociationField('wishlist','wishlist_id',WishlistDefinition::class),
            new ManyToOneAssociationField('customer','customer_id',WishlistCustomerDefinition::class,'id',true),
            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
