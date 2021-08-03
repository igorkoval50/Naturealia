<?php

namespace Pixup\Wishlist\Entitys\Aggregate\WishlistCustomer\Collection;
use Pixup\Wishlist\Entitys\Aggregate\WishlistCustomer\Model\WishlistCustomerModel;
use Pixup\Wishlist\Entitys\Model\WishlistModel;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
     * @method void              add(WishlistModel $entity)
     * @method void              set(string $key, WishlistModel $entity)
     * @method WishlistCustomerModel[]    getIterator()
     * @method WishlistCustomerModel[]    getElements()
     * @method WishlistCustomerModel|null get(string $key)
     * @method WishlistCustomerModel|null first()
     * @method WishlistCustomerModel|null last()
     */
class WishlistCustomerCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return WishlistCustomerModel::class;
    }
}
