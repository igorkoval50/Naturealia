<?php declare(strict_types=1);

namespace Pixup\Wishlist\Subscriber;

use Pixup\Wishlist\Core\Boot;
use Pixup\Wishlist\Entitys\Model\WishlistModel;
use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Cms\CmsPageEvents;
use Shopware\Core\Content\Cms\Events\CmsPageLoadedEvent;
use Shopware\Core\Content\Cms\SalesChannel\Struct\ProductListingStruct;

use Shopware\Core\Content\Cms\SalesChannel\Struct\ProductSliderStruct;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;

class ProductDetailSubscriber implements EventSubscriberInterface
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
            ##need to take this methods because they provide a salesChannelContext / userID
            ProductPageLoadedEvent::class => 'addWishlistInformation',
            CmsPageLoadedEvent::class => "addWishlistInformationToProductListing",
            HeaderPageletLoadedEvent::class => "pageLoaded"
        ];
    }

    public function pageLoaded(HeaderPageletLoadedEvent $event){
        //get the count of all products to display it on the header pagelet
        $wishListCookieHandler = $this->boot->getFacade()->getCookieHandler();
        $wishListEntityHandler = $this->boot->getFacade()->getWishlistEntityHandler();
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannel()->getId();
        $id = $wishListEntityHandler->getWishlistCustomerId(
            ($event->getSalesChannelContext()->getCustomer()==null)?null:$event->getSalesChannelContext()->getCustomer()->getId(),
            ($wishListCookieHandler->getCookieId() == null)?null:$wishListCookieHandler->getCookieId()
        );
        $count = 0;
        if($id == null)
            $count = 0;
        else {
            $wishlists = $wishListEntityHandler->getWishlists($salesChannelId, $id, "", "", "", true);
            /**
             * @var WishlistModel $wishlist
             */
            foreach ($wishlists as $wishlist) {
                $count += count($wishlist->getProducts()->getIds());
            }
        }
        $event->getPagelet()->assign([
            "pixup"=>[
                "wishlistProductCount"=>$count,
                "showIconOnProductImage"=>$this->boot->getConfig()['showOnImage']
            ]
        ]);
    }

    public function addWishlistInformationToProductListing(CmsPageLoadedEvent $event){
        $wishListCookieHandler = $this->boot->getFacade()->getCookieHandler();
        $wishListEntityHandler = $this->boot->getFacade()->getWishlistEntityHandler();

        $salesChannelId = $event->getSalesChannelContext()->getSalesChannel()->getId();

        $id = $wishListEntityHandler->getWishlistCustomerId(
            ($event->getSalesChannelContext()->getCustomer()==null)?null:$event->getSalesChannelContext()->getCustomer()->getId(),
            ($wishListCookieHandler->getCookieId() == null)?null:$wishListCookieHandler->getCookieId()
        );

        if($id == null)
            return;
        //get the listing pages
        /**
         * @var CmsPageEntity $cmsEntity
         */
        if($event->getResult() == null)
            return;
        foreach($event->getResult() as $cmsEntity){
            $type =  $cmsEntity->getType();
            if($type !== "product_list" && $type !== "page" )
                continue;
            $sections = $cmsEntity->getSections();
            if($sections == null)
                continue;

            /**
             * @var CmsSectionEntity $section
             */
            foreach($sections as $section){
                $blocks = $section->getBlocks();
                if($blocks == null)
                    continue;
                /**
                 * @var CmsBlockEntity $block
                 */
                foreach($blocks as $block){
                    if($block->getType()!=='product-listing' && $block->getType()!=="product-slider")
                        continue;
                    $slots = $block->getSlots();
                    /**
                     * @var CmsSlotEntity $slot
                     */
                    foreach($slots as $slot){
                        if($slot->getType()!=='product-listing' && $slot->getType()!=='product-slider')
                            continue;

                        /**
                         * @var ProductListingStruct $data
                         */
                        $data = $slot->getData();
                        if($data instanceof ProductListingStruct){
                            if($data->getListing()==null)
                                continue;
                            $products = $data->getListing()->getEntities();
                        }elseif($data instanceof ProductSliderStruct){
                            if($data->getProducts() == null)
                                continue;
                            $products = $data->getProducts();
                        }

                        if($data instanceof ProductSliderStruct || $data instanceof ProductListingStruct)
                            /**
                             * @var ProductEntity $product
                             */
                            foreach($products as $product){
                                $productID = $product->getId();
                                $product->setCustomFields(
                                    [
                                        'isOnWishlist'=>$this->boot->getFacade()->getWishlistEntityHandler()->checkIfProductExists($productID,$salesChannelId,$id),
                                        "showIconOnProductImage"=>$this->boot->getConfig()['showOnImage']
                                    ]
                                );
                            }
                    }
                }
            }
        }
    }

    /**
     * @param ProductPageLoadedEvent $event
     * @description to add the wishlist state to the product detail site
     */
    public function addWishlistInformation(ProductPageLoadedEvent $event):void {
        $wishListCookieHandler = $this->boot->getFacade()->getCookieHandler();
        $wishListEntityHandler = $this->boot->getFacade()->getWishlistEntityHandler();

        $productID = $event->getPage()->getProduct()->getId();
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannel()->getId();

        $id = $wishListEntityHandler->getWishlistCustomerId(
            ($event->getSalesChannelContext()->getCustomer()==null)?null:$event->getSalesChannelContext()->getCustomer()->getId(),
            ($wishListCookieHandler->getCookieId() == null)?null:$wishListCookieHandler->getCookieId()
        );
        if($id == null)
            return;
        $onWishlist = $this->boot->getFacade()->getWishlistEntityHandler()->checkIfProductExists($productID,$salesChannelId,$id);
        $event->getPage()->getProduct()->setExtensions(["pixup_wish_list_state"=>['isOnWishlist'=>$onWishlist]]);
    }
}
