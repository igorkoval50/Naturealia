<?php


namespace Pixup\Wishlist\Framework\Event;


use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class WishlistGetRecoEvent extends Event
{
    public const NAME = 'wishlist.getReco';

    /**
     * @var array
     */
    private $products;

    /**
     * @var array $recoProducts --> "pixupReco"=>[
        "wishlist"=>[
            "products"=>$products,
            "label"=>$res->getLabel(),
            "ids"=>$res->getProducts()
        ],
        "config"=>[
            "pixupGetParameterString"=>"px=1".$res->getGetParameterString()
        ]
    ]
     */
    private $recoResponse = [];

    public function __construct(array $products)
    {
        $this->products = $products;
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @param array $products
     */
    public function setProducts(array $products): void
    {
        $this->products = $products;
    }

    /**
     * @return array
     */
    public function getRecoResponse(): array
    {
        return $this->recoResponse;
    }

    /**
     * @param array $recoResponse
     */
    public function setRecoResponse(array $recoResponse): void
    {
        $this->recoResponse = $recoResponse;
    }

}
