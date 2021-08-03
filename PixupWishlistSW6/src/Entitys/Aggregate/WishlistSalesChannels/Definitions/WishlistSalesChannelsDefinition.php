<?php declare(strict_types=1);

namespace Pixup\Wishlist\Entitys\Aggregate\WishlistSalesChannels\Definitions;
use Pixup\Wishlist\Entitys\Definitions\WishlistDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class WishlistSalesChannelsDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = "pixup_wish_list_sales_channels";

    public function getEntityName(): string{
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection{
        return new FieldCollection([
            (new FkField('wishlist_id','wishlistId',WishlistDefinition::class))->addFlags(new PrimaryKey(),new Required(),new CascadeDelete()),
            (new FkField('sales_channel_id','salesChannelId',SalesChannelDefinition::class))->addFlags(new PrimaryKey(),new Required(),new CascadeDelete()),
            new ManyToOneAssociationField('wishlist','wishlist_id',WishlistDefinition::class),
            new ManyToOneAssociationField('salesChannel','sales_channel_id',SalesChannelDefinition::class),
            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
