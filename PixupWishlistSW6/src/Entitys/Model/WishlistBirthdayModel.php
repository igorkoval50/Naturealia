<?php declare(strict_types=1);
namespace Pixup\Wishlist\Entitys\Model;

use Pixup\Wishlist\Entitys\Aggregate\WishlistCustomer\Model\WishlistCustomerModel;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class WishlistBirthdayModel extends Entity
{
    /**
     * @var ProductEntity
     */
    protected $product;

    /**
     * @var WishlistCustomerModel
     */
    protected $customer;

    /**
     * @var WishlistModel
     */
    protected $wishlist;

    /**
     * @return ProductEntity
     */
    public function getProduct(): ProductEntity
    {
        return $this->product;
    }

    /**
     * @param ProductEntity $product
     */
    public function setProduct(ProductEntity $product): void
    {
        $this->product = $product;
    }

    /**
     * @return WishlistCustomerModel
     */
    public function getCustomer(): WishlistCustomerModel
    {
        return $this->customer;
    }

    /**
     * @param WishlistCustomerModel $customer
     */
    public function setCustomer(WishlistCustomerModel $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return WishlistModel
     */
    public function getWishlist(): WishlistModel
    {
        return $this->wishlist;
    }

    /**
     * @param WishlistModel $wishlist
     */
    public function setWishlist(WishlistModel $wishlist): void
    {
        $this->wishlist = $wishlist;
    }


}
