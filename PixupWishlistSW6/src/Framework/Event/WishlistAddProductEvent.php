<?php declare(strict_types=1);

namespace Pixup\Wishlist\Framework\Event;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareEvent;
use Symfony\Contracts\EventDispatcher\Event;

class WishlistAddProductEvent extends Event
{
    public const NAME = 'wishlist.added';

    /**
     * @var string $productId
     */
    private $productId;

    /**
     * @var string $userID
     */
    private $userID;

    public function __construct(string $product,string $userID)
    {
        $this->productId = $product;
        $this->userID = $userID;
    }
    public function getProduct():string
    {
        return $this->productId;
    }

    public function getUserId():string{
        return $this->userID;
    }
}
