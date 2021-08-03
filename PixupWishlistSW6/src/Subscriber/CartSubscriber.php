<?php


namespace Pixup\Wishlist\Subscriber;


use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pixup\Wishlist\Core\Boot;
use Shopware\Core\Checkout\Cart\Event\LineItemRemovedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class CartSubscriber implements EventSubscriberInterface
{
    private $boot;

    public function __construct(Boot $boot,RequestStack $requestStack)
    {
        $this->boot = $boot;
        //set sessionID because shopware does not set it if its an API call
        $token = substr(bin2hex($requestStack->getCurrentRequest()->headers->get("sw-context-token")),0,32);
        if(isset($token) && !isset($_SESSION['_sf2_attributes']['sessionId'])) {
            $_SESSION['_sf2_attributes'] = array('sessionId' =>$token);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            LineItemRemovedEvent::class => "removeFromBirthdayist",
            CheckoutOrderPlacedEvent::class => "removeProductFromWishlist",
            CheckoutCartPageLoadedEvent::class => "addInfoToCheckoutCart",
            OffcanvasCartPageLoadedEvent::class => "addInfoToOffcanvasCart"
        ];
    }

    /**
     * @param CheckoutCartPageLoadedEvent $event
     * @description adds information to lineItem
     */
    private function addInfoToCart(PageLoadedEvent $event){
        $wishListCookieHandler = $this->boot->getFacade()->getCookieHandler();
        $wishListEntityHandler = $this->boot->getFacade()->getWishlistEntityHandler();

        $salesChannelId = $event->getSalesChannelContext()->getSalesChannel()->getId();

        $id = $wishListEntityHandler->getWishlistCustomerId(
            ($event->getSalesChannelContext()->getCustomer()==null)?null:$event->getSalesChannelContext()->getCustomer()->getId(),
            ($wishListCookieHandler->getCookieId() == null)?null:$wishListCookieHandler->getCookieId()
        );

        if($id == null)
            return;
        if($this->boot->getConfig()['showOnCart']) {
            /**
             * @var LineItem $lineItem
             */
            foreach ($event->getPage()->getCart()->getLineItems() as $lineItem) {
                $lineItem->setExtensions(array_merge($lineItem->getExtensions()??[],[
                    'isOnWishlist' => $this->boot->getFacade()->getWishlistEntityHandler()->checkIfProductExists($lineItem->getId(), $salesChannelId, $id)
                ]));
            }
        }
    }

    /**
     * @param OffcanvasCartPageLoadedEvent $event
     * @description adds configuration value to pagestruct
     */
    public function addInfoToOffcanvasCart(OffcanvasCartPageLoadedEvent $event): void{
        $event->getPage()->setExtensions(array_merge($event->getPage()->getExtensions()??[],[
            "pixup"=>[
                "showOnCart"=>$this->boot->getConfig()['showOnOffcanvasCart'],
                "showOnCartImage"=>$this->boot->getConfig()['showOnOffcanvasCartImage'],
            ]
        ]));
        $this->addInfoToCart($event);
    }
    /**
     * @param CheckoutCartPageLoadedEvent $event
     * @description adds configuration value to pagestruct
     */
    public function addInfoToCheckoutCart(CheckoutCartPageLoadedEvent $event): void{
        $event->getPage()->setExtensions(array_merge($event->getPage()->getExtensions()??[],[
            "pixup"=>[
                "showOnCart"=>$this->boot->getConfig()['showOnCart'],
                "showOnCartImage"=>$this->boot->getConfig()['showOnCartImage'],
            ]
        ]));
        $this->addInfoToCart($event);
    }

    /**
     * @param LineItemRemovedEvent $event
     * @description will check the birthday table and removes entrys from there if a lineItem is removed that whas added
     * from a birthday wishlist
     */
    public function removeFromBirthdayist(LineItemRemovedEvent $event) :void{
        $wishListEntityHandler = $this->boot->getFacade()->getWishlistEntityHandler();
        $wishListCookieHandler = $this->boot->getFacade()->getCookieHandler();
        $context = $event->getContext();
        $salesChannelId = $context->getSalesChannel()->getId();
        $productId = $event->getLineItem()->getId();
        $id = $wishListEntityHandler->getWishlistCustomerId(
            ($context->getCustomer()==null)?null:$context->getCustomer()->getId(),
            ($wishListCookieHandler->getCookieId() == null)?null:$wishListCookieHandler->getCookieId()
        );
        if($id == null)
            return;

        $wishListEntityHandler->removeUserFromBirthdayListByProductId($productId,$id);
    }

    /**
     * @param CheckoutOrderPlacedEvent $event
     * @description removes a product from the subscribed or owned birthday wishlist if customer completly orderd a item
     */
    public function removeProductFromWishlist(CheckoutOrderPlacedEvent $event){
        $wishListEntityHandler = $this->boot->getFacade()->getWishlistEntityHandler();
        $wishListCookieHandler = $this->boot->getFacade()->getCookieHandler();
        $salesChannelId = $event->getSalesChannelId();

        if($event->getOrder()->getOrderCustomer()->getCustomer()->getGuest())
            $customer = null;
        else
            $customer = $event->getOrder()->getOrderCustomer()->getCustomer();
        $id = $wishListEntityHandler->getWishlistCustomerId(
            ($customer==null)?null:$customer->getId(),
            ($wishListCookieHandler->getCookieId() == null)?null:$wishListCookieHandler->getCookieId()
        );

        try {
            /**
             * @var OrderLineItemEntity $orderLineItem
             */
            foreach($event->getOrder()->getLineItems()->getElements() as $orderLineItem){
                $wishlistId = $wishListEntityHandler->removeUserFromBirthdayListByProductId($orderLineItem->getProductId(),$id);
                if(!empty($wishlistId))
                    $wishListEntityHandler->deleteProductFromBirthdayWishlist([$orderLineItem->getProductId()],$wishlistId,$id,$salesChannelId);
            }
        }catch(\Exception $e){

        }
    }
}
