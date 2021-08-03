<?php declare(strict_types=1);


namespace Pixup\Wishlist\Core;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class Facade extends Factory
{

    public function getCookieHandler(){
        return $this->createWishlistCookieHandler();
    }

    public function getWishlistEntityHandler(){
        return $this->createWishlistEntityHandler();
    }

    public function getProductInformationFetcher(){
        return $this->createProductInformationFetch();
    }

    public function getAddProductEvent(string $productId,string $userID){
        return $this->createAddProductEventClass($productId,$userID);
    }

    public function getRecoEvent(array $products){
        return $this->createGetRecoEventClass($products);
    }
}
