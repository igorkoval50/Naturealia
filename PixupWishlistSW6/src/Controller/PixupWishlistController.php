<?php
namespace Pixup\Wishlist\Controller;
use Pixup\Wishlist\Core\Boot;
use Pixup\Wishlist\Entitys\Model\WishlistModel;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\Product\Configurator\ProductPageConfiguratorLoader;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class PixupWishlistController extends StorefrontController
{
    /**
     * @var Boot
     */
    private $boot;

    /**
     * @var array
     */
    private $config;

    /**
     * @var ProductListingLoader $productLoader
     */
    private $productLoader;

    /**
     * @var ProductPageConfiguratorLoader
     */
    private $productConfiguratorServiceLoader;

    /**
     * @var \Pixup\Wishlist\Model\WishlistEntityHandler
     */
    private $wishListEntityHandler;

    /**
     * @var \Pixup\Wishlist\Model\WishlistCookieHandler
     */
    private $wishListCookieHandler;

    /**
     * @var string|null
     */
    private $customerId;

    /**
     * @var string
     */
    private $salesChannelId;

    /**
     * @var PixupWishlistAjaxController
     */
    private $ajaxController;

    /**
     * @var HeaderPageletLoader
     */
    private $headerPageletLoader;

    public function __construct(
        Boot $boot,
        ProductListingLoader $productLoader,
        ProductPageConfiguratorLoader $productConfiguratorServiceLoader,
        PixupWishlistAjaxController $ajaxController,
        HeaderPageletLoader $headerPageletLoader
    )
    {
        $this->boot = $boot;
        $this->config = $this->boot->getConfig();
        $this->productLoader = $productLoader;
        $this->productConfiguratorServiceLoader = $productConfiguratorServiceLoader;
        $this->headerPageletLoader = $headerPageletLoader;

        $this->wishListEntityHandler = $this->boot->getFacade()->getWishlistEntityHandler();
        $this->wishListCookieHandler = $this->boot->getFacade()->getCookieHandler();
        $this->ajaxController = $ajaxController;
    }

    private function prePatch(SalesChannelContext $context){
        $this->customerId = $this->wishListEntityHandler->getWishlistCustomerId(
            ($context->getCustomer()==null)?null:$context->getCustomer()->getId(),
            $this->wishListCookieHandler->getCookieId()
        );
        $this->salesChannelId = $context->getSalesChannel()->getId();
    }

    /**
     * @Route(
     *     "/pixup/wishlist/ajax/add-product/{productId}/{wishlistId?null}",
     *     name="frontend.pixup.wishlist.ajax.add_product",
     *     options={"seo"="false"},
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     * )
     *\\description: if wishlistId = -1 a new wishlist will be added
     */
    public function ajax_addProduct(string $productId,string $wishlistId, SalesChannelContext $context, Request $request)
    {
        return $this->ajaxController->addProduct($productId,$wishlistId,$context,$request);
    }

    /**
     * @Route(
     *     "/pixup/wishlist/ajax/remove-product/{productId}/{wishlistId?null}",
     *     name="frontend.pixup.wishlist.ajax.remove_product",
     *     options={"seo"="false"},
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     * )
     ** description: if wishlistId = -1 a new wishlist will be added
     */
    public function ajax_removeProduct(string $productId,string $wishlistId, SalesChannelContext $context, Request $request){
        return $this->ajaxController->removeProduct($productId,$wishlistId,$context,$request);
    }

    /**
     * @Route(
     *     "/pixup/wishlist/ajax/get-product-state/{$productId}",
     *     name="frontend.pixup.wishlist.ajax.get_product_state",
     *     options={"seo"="false"},
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     * )
     */
    public function ajax_getProductState(string $productId,SalesChannelContext $context, Request $request){
        return $this->ajaxController->getProductState($productId,$context,$request);
    }

    /**
     * @Route(
     *     "/pixup/wishlist/ajax/get-product-variant/{parentId}/{options}/{wishedGroupId}",
     *     name="frontend.pixup.wishlist.ajax.get_product_variant",
     *     methods={"GET"},
     *     defaults={"XmlHttpRequest": true}
     * )
     ** description: takes the parentId and the options array to return the productId of an Variant
     ** options = [groupID1=>optionID1,groupID2=>optionID2]
     */
    public function ajax_WishlistSwitchVariant(
        string $parentId,
        string $options,
        string $wishedGroupId,
        Request $request,
        SalesChannelContext $salesChannelContext
    ){
        return $this->ajaxController->switchVariant($parentId,$options,$wishedGroupId,$request,$salesChannelContext);
    }

    /**
     * @Route(
     *     "pixup/wishlist/ajax/replace-from-wishlist/{oldId}/{newId}/{wishListId}",
     *     name="frontend.pixup.wishlist.ajax.replace_from_wishlist",
     *     options={"seo"="false"},
     *     methods={"GET"},
     *     defaults={"_format"="html"}
     * )
     ** replaces the oldProductId with the newProductId ( based on the $wishlistId )
     */
    public function ajax_WishlistReplaceProduct(
        string $oldId,
        string $newId,
        string $wishListId,
        Request $request,
        SalesChannelContext $context
    ){
        return $this->ajaxController->replaceProduct($oldId,$newId,$wishListId,$request,$context);
    }

    /**
     * @Route(
     *     "/pixup/wishlist/ajax/get-wishlist/{productId?null}/{wishlistId?null}/{returnView?null}/{returnProducts?false}",
     *     name="frontend.pixup.wishlist.ajax.get_wishlists",
     *     options={"seo"="false"},
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     * )
     ** description returns all wishlists which includes the givin productId/wishlistId ( if productId/wishlist is not set it will return any )
     ** param string $productId // null or a product ID
     ** param string $wishlistId // null or wishlist ID
     *  param returnView // null or bool -- will return a view from the product-collection ( all products from a wishlist )
     ** param SalesChannelContext $context
     ** param Request $request
     */
    public function ajax_getWishlist(string $productId,string $wishlistId,string $returnView,string $returnProducts, SalesChannelContext $context, Request $request){
        $productId = ($productId==='null')?"":$productId;
        $wishlistId = ($wishlistId==='null')?"":$wishlistId;
        $returnView = ($returnView=='null' || $returnView=='false')?false:true;
        $returnProducts = ($returnProducts == "true")?true:false;

        if($returnView){
            $wishlists = $this->getWishlists($context,$productId,$wishlistId);
            $wishlistsPayload = $this->ajaxController->addAdvancedProductInformationToWishlist($wishlists,$context,true);

            return $this->ajaxController->createAjaxResponse([
                'wishlists'=>$wishlists,
                'view'=>$this->renderStorefront('@Storefront/wishlist/box/product-collection-view.html.twig',[
                    'wishlists'=>$wishlistsPayload
                ])->getContent(),
            ]);
        }

        return $this->ajaxController->createAjaxResponse([
            'wishlists'=>$this->getWishlists($context,$productId,$wishlistId,$returnProducts)
        ]);
    }

    /**
     * @Route(
     *     "/pixup/wishlist/ajax/create-wishlist/{name}/{products?null}/{private?string}/{editable?string}/{birthday?string}/{password?null}/{wishListId?null}",
     *     name="frontend.pixup.wishlist.ajax.create_wishlist",
     *     options={"seo"="false"},
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     * )
     */
    public function ajax_createWishlist(string $name,string $products,string $private,
                                        string $editable,string $birthday,string $password,string $wishListId,
                                        SalesChannelContext $context,Request $request
    ){
        return $this->ajaxController->createWishlist($name,$products,$private,$editable,$birthday,$password,$wishListId,$context,$request);
    }

    /**
     * @Route(
     *     "/pixup/wishlist/ajax/edit-wishlist/{name}/{wishListId}/{private?string}/{editable?string}/{birthday?string}/{password?null}",
     *     name="frontend.pixup.wishlist.ajax.edit_wishlist",
     *     options={"seo"="false"},
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     * )
     */
    public function ajax_editWishlist(string $name,string $wishListId,string $private,
                                      string $editable,string $birthday,string $password,
                                      SalesChannelContext $context,Request $request){
        return $this->ajaxController->editWIshlist($name,$wishListId,$private,$editable,$birthday,$password,$context,$request);
    }
    /**
     * @Route(
     *     "/pixup/wishlist/ajax/delete-wishlist/{wishlistId}",
     *     name="frontend.pixup.wishlist.ajax.delete_wishlist",
     *     options={"seo"="false"},
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     * )
     */
    public function ajax_removeWishlist(string $wishlistId, SalesChannelContext $context,Request $request){
       return $this->ajaxController->removeWishlist($wishlistId,$context,$request);
    }

    /**
     **param string $productId
     **param SalesChannelContext $context
     **description returns all wishlists which includes the givin productId ( if productId is not set it will return any )
     * Based on the SalesChannelId and customerID (SalesChannelContext)
     **return array
     */
    private function getWishlists(SalesChannelContext $context,string $productId = "",string $wishlistId="",bool $returnProducts = false) :array{
        $this->prePatch($context);
        if($this->customerId == null)
            return [];
        $this->ajaxController->customerId = $this->customerId;
        $formatedWishlistResponse =  $this->ajaxController->formatWishlistResponseArray(
            $this->wishListEntityHandler->getWishlists(
            $this->salesChannelId,
            $this->customerId,
            (!empty($productId))?$productId:'',
            '',
            $wishlistId,
            true)
        );

        if($returnProducts) {
            $formatedWishlistResponse = $this->ajaxController->addInfoToProduct($formatedWishlistResponse, $context,true);
        }

        return $formatedWishlistResponse;
    }

    /**
     * @Route(
     *     "/pixup/wishlist/{wishlistId?null}/{password?null}",
     *     name="frontend.pixup.wishlist.overview",
     *     options={"seo"="false"},
     *     methods={"GET"},
     *     defaults={"_format"="html"}
     * )
     */
    public function WishlistOverview(string $wishlistId,string $password,SalesChannelContext $context,Request $request){
        $wishlistId = ($wishlistId=='null')?null:$wishlistId;
        $this->prePatch($context);

        $message = "";
        $password = ($password=='null')?null:$password;
        $subscribedToWishlist = false;
        //check if passed wishlistId is own wishlistId
        $wishlistId = ($wishlistId=='null')?null:$wishlistId;
        if($wishlistId !== null) {
            if($this->customerId == null){
                $res = $this->subscribeToWishlist($wishlistId,$context,$password);
            }else {
                $ownWishList = $this->wishListEntityHandler->checkForOwnWishlist($wishlistId, $this->customerId,true);
                if(!$ownWishList)
                    $res = $this->subscribeToWishlist($wishlistId,$context,$password);
            }
            if(isset($res))
                $message = $res['message'];
            else
                $res = [];
            if(isset($res['success']) && $res['success'] == true) {
                $subscribedToWishlist = true;
            }
        }
        $wishlists = $this->getWishlists($context,'');

        $isLoggedIn = ($context->getCustomer()==null)?false:true;

        $wishlists = $this->ajaxController->addAdvancedProductInformationToWishlist($wishlists,$context,true);
        if($wishlistId !== null) {
            $publicWishlists = $this->wishListEntityHandler->getPublicWishlistById($wishlistId, $context->getSalesChannel()->getId(), $this->customerId);
            if ($publicWishlists->first() !== null) {
                //check for password
                /**
                 * @var WishlistModel $pWishlist
                 */
                $pWishlist = $publicWishlists->first();
                if ($pWishlist->getPassword() !== null)
                    if ($pWishlist->getPassword() !== $password)
                        return $this->renderStorefront('@Storefront/storefront/page/wishlist/enterPassword.html.twig',[
                            'wishlistId'=>$wishlistId,
                            "page" => ["header"=>$this->headerPageletLoader->load($request,$context)]
                        ]);

                $publicWishlists = $this->ajaxController->formatWishlistResponseArray($publicWishlists, true);
                $publicWishlists = $this->ajaxController->addAdvancedProductInformationToWishlist($publicWishlists, $context,true);
            }
        }
        if(empty($publicWishlists))
            $publicWishlists = [];

        return $this->renderStorefront('@Storefront/storefront/page/wishlist/index.html.twig',[
            "wishlists"=>$wishlists,
            'isLoggedIn' => $isLoggedIn,
            'wishlistCanBePublic' => $this->config['wishlistCanBePublic'],
            'wishlistBirthday'=> $this->config['wishlistCanBeBirthday'],
            'wishlistCanBePrinted'=> $this->config['printWishlist'],
            'wishlistId' => $wishlistId,
            'message' => $message,
            'subscribedToWishlist' => $subscribedToWishlist,
            'publicWishlist' => $publicWishlists,
            "page" => [
                "header"=>$this->headerPageletLoader->load($request,$context),
                "metaInformation"=>[
                    "metaTitle"=>"Wishlist",
                    "metaDescription"=>"Wishlist",
                    "robots"=>"noindex,nofollow"
                ]
            ]
        ]);
    }

    private function subscribeToWishlist(string $wishlistId,SalesChannelContext $context,$password = null):array{
        $isLoggedIn = ($context->getCustomer()==null)?false:true;

        $responseArr = ['success'=>true,'message'=>''];
        if(!$isLoggedIn){
            if(!$this->config['cookieUserCanSubscribe']) {
                $responseArr['success'] = false;
                $responseArr['message'] = 8;
                return $responseArr;
            }
        }
        $this->prePatch($context);
        if($this->customerId==null) {
            $this->customerId = $this->wishListEntityHandler->createWishlistCustomer($this->wishListCookieHandler->generateCookieId());
            $this->wishListCookieHandler->createWishlistCookie($context->getSalesChannel()->getId(),'',$this->customerId);
        }


        $res =  $this->wishListEntityHandler->subscribeToWishlist($wishlistId,$context->getSalesChannel()->getId(),$this->customerId,$password);
        //create cookie if there was no cookie before
        if($res['success'] == true){
            if(!$isLoggedIn && $this->wishListCookieHandler->getCookieId() == null)
                $this->wishListCookieHandler->createWishlistCookie($context->getSalesChannel()->getId(),$wishlistId,$this->customerId);
        }

        return $res;
    }

    /**
     * @Route(
     *     "/wishlist/productBox/{productId}/{index}/{wishlistId}",
     *     name="frontend.pixup.wishlist.product_box",
     *     options={"seo"="false"},
     *     methods={"GET"},
     *     defaults={"_format"="html"}
     * )
     ** this returns a productBox to load it dynamic inside of the wishlist
     */
    public function WishlistLoadProductBox(
        string $productId,
        string $index,
        string $wishlistId,
        Request $request,
        SalesChannelContext $context
    ){
        $product = $this->productLoader->load(new Criteria([$productId]),$context)->first();
        $configuratorSettings = $this->productConfiguratorServiceLoader->load($product,$context);
        /**
         * @var WishlistModel $wishlist
         */
        $wishlist = $this->wishListEntityHandler->getWishlistById($wishlistId);
        $wishlist = $this->ajaxController->formatWishlistResponseArray($wishlist)[0];
        if($wishlist['isOwnWishlist'] && !$wishlist['isOwnWishlist'] && !$wishlist['editable']){
            $delete = false;
            $move = false;
        }else{
            $delete = true;
            $move = true;
        }
        return $this->renderStorefront('@Storefront/wishlist/box/product-prev.html.twig',[
                    'product'=>$product,
                    'configuratorSettings' => $configuratorSettings,
                    'index' => $index,
                    'wishlistId' => $wishlistId,
                    'external' => false,
                    'delete'=>$delete,
                    'move'=>$move,
        ]);
    }
}
