<?php declare(strict_types=1);

namespace Pixup\Wishlist\Entitys\Collections;

use Pixup\Wishlist\Entitys\Model\WishlistBirthdayModel;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(WishlistBirthdayModel $entity)
 * @method void              set(string $key, WishlistBirthdayModel $entity)
 * @method WishlistBirthdayModel[]    getIterator()
 * @method WishlistBirthdayModel[]    getElements()
 * @method WishlistBirthdayModel|null get(string $key)
 * @method WishlistBirthdayModel|null first()
 * @method WishlistBirthdayModel|null last()
 */
class WishlistBirthdayCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return WishlistBirthdayModel::class;
    }
}
