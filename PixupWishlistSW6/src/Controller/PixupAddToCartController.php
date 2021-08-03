<?php


namespace Pixup\Wishlist\Controller;


use Symfony\Component\HttpFoundation\Request;
use Pixup\Wishlist\Core\Boot;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Promotion\Cart\PromotionItemBuilder;
use Shopware\Core\Content\Product\Cart\ProductLineItemFactory;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\CartLineItemController;
use Shopware\Storefront\Page\Product\Configurator\ProductPageConfiguratorLoader;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;


/**
 * @RouteScope(scopes={"storefront"})
 */
class PixupAddToCartController extends CartLineItemController
{
    /**
     * @var Boot
     */
    private $boot;

    public function __construct(
        Boot $boot,
        CartService $cartService,
        SalesChannelRepositoryInterface $productRepository,
        PromotionItemBuilder $promotionItemBuilder,
        ProductLineItemFactory $productLineItemFactory
    )
    {
        parent::__construct($cartService,$productRepository,$promotionItemBuilder,$productLineItemFactory);
        $this->boot = $boot;
    }

    /**
     * @Route(
     *     "/pixup/wishlist/add/addProductToCart/{wishlistId?null}/{productId?null}",
     *     name="frontend.pixup.wishlist.ajax.addProductToCart",
     *     methods={"POST"},
     *     defaults={"XmlHttpRequest"=true}
     * )
     ** will add a product to cart and set the customer in a table so that the wishlist plugin can notice that
     ** the customer puts somthing in the wishlist to track and use the birthday functionality later one
     */
    public function index(string $wishlistId,string $productId, Cart $cart, RequestDataBag $requestDataBag, Request $request, SalesChannelContext $salesChannelContext){
        //return $this->forward(CartLineItemController::class."::addLineItems");
        if($wishlistId == 'null' || empty($wishlistId) || $productId == 'null' || empty($productId))
            return $this->addLineItems($cart,$requestDataBag,$request,$salesChannelContext);

        $wishListEntityHandler = $this->boot->getFacade()->getWishlistEntityHandler();
        $wishListCookieHandler = $this->boot->getFacade()->getCookieHandler();
        $id = $wishListEntityHandler->getWishlistCustomerId(
            ($salesChannelContext->getCustomer()==null)?null:$salesChannelContext->getCustomer()->getId(),
            ($wishListCookieHandler->getCookieId() == null)?null:$wishListCookieHandler->getCookieId()
        );

        $wishListEntityHandler->setBirthdayUserAddedProduct($wishlistId,$id,$salesChannelContext->getSalesChannel()->getId(),$productId);
        return $this->addLineItems($cart,$requestDataBag,$request,$salesChannelContext);
    }
}
