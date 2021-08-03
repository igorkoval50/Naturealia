<?php declare(strict_types=1);

namespace Pixup\Wishlist\Entitys\Collections;

use Pixup\Wishlist\Entitys\Model\WishlistModel;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(WishlistModel $entity)
 * @method void              set(string $key, WishlistModel $entity)
 * @method WishlistModel[]    getIterator()
 * @method WishlistModel[]    getElements()
 * @method WishlistModel|null get(string $key)
 * @method WishlistModel|null first()
 * @method WishlistModel|null last()
 */
class WishlistCollection extends EntityCollection
{
    public function filterByProductId(string $id):self{
        return $this->filter(function (WishlistModel $wishListModel) use ($id) {
            foreach($wishListModel->getProducts() as $product){
                return $product->getId() == $id;
            }
            return false;
        });
    }

    public function filterByCookieId(string $id):self{
        return $this->filter(function(WishlistModel $wishListModel) use ($id){
           return $wishListModel->getType() == 'cookie' && $wishListModel->getCookieId() == $id;
        });
    }

    public function filterByUserId(string $id):self{
        return $this->filter(function(WishlistModel $wishListModel) use ($id){
            return $wishListModel->getType() == 'user' && $wishListModel->getCustomer()->getId() == $id;
        });
    }

    protected function getExpectedClass(): string
    {
        return WishlistModel::class;
    }
}
