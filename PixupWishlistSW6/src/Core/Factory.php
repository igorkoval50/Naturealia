<?php declare(strict_types=1);


namespace Pixup\Wishlist\Core;

use Pixup\Wishlist\Framework\Event\WishlistAddProductEvent;
use Pixup\Wishlist\Framework\Event\WishlistGetRecoEvent;
use Pixup\Wishlist\Model\ProductInformationFetcher;
use Pixup\Wishlist\Model\WishlistCookieHandler;
use Pixup\Wishlist\Model\WishlistEntityHandler;
use Psr\Container\ContainerInterface;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class Factory
{
    /**
     * @var ContainerInterface
     * @description Shopware Service Container
     */
    private $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return WishlistCookieHandler
     * @description returns a class which is able to handle the wishlist Cookies
     */
    protected function createWishlistCookieHandler(){
        return new WishlistCookieHandler();
    }

    /**
     * @return WishlistEntityHandler
     * @description returns a class which is able to handle the wishListEntity and store / get stuff from the Database
     */
    protected function createWishlistEntityHandler(){
        return new WishlistEntityHandler(
            $this->container->get('pixup_wish_list.repository'),
            $this->container->get('pixup_wish_list_products.repository'),
            $this->container->get('pixup_wish_list_customers.repository'),
            $this->container->get('pixup_wish_list_subscribers.repository'),
            $this->container->get('pixup_wish_list_birthday.repository')
        );
    }

    protected function createProductInformationFetch(){
        return new ProductInformationFetcher($this->container->get('sales_channel.product.repository'));
    }

    protected function createAddProductEventClass(string $productId,string $userID){
        return new WishlistAddProductEvent($productId,$userID);
    }

    protected function createGetRecoEventClass(array $products){
        return new WishlistGetRecoEvent($products);
    }
}
