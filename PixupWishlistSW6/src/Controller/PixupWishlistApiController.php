<?php declare(strict_types=1);

namespace Pixup\Wishlist\Controller;

use Pixup\Wishlist\Core\Boot;
use Pixup\Wishlist\Model\StructEncoder;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Product\Configurator\ProductPageConfiguratorLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
/**
 * @RouteScope(scopes={"store-api"})
 */
class PixupWishlistApiController extends PixupWishlistAjaxController
{
    public function __construct(
        Boot $boot,
        ProductPageConfiguratorLoader $productConfiguratorServiceLoader,
        StructEncoder $encoder,
        RequestStack $requestStack,
        ProductListingLoader $productLoader,
        $dispatcher
    )
    {
        //set sessionID because shopware does not set it if its an API call
        $token = substr(bin2hex($requestStack->getCurrentRequest()->headers->get("sw-context-token")),0,32);
        if(isset($token) && !isset($_SESSION['_sf2_attributes']['sessionId'])) {
            $_SESSION['_sf2_attributes'] = array('sessionId' =>$token);
        }
        parent::__construct($boot,$productConfiguratorServiceLoader,$dispatcher,$productLoader);
    }

    /**
     * @Route(
     *     "/store-api/pixup-wishlist/add-product/{productId}/{wishlistId?null}",
     *     name="store-api.action.pixup-wishlist.add-product",
     *     methods={"GET"}
     * )
     *\\params[
     *      "wishlistId" = the id for the wishlist where the product should be added ( if -1 is passed it will create a new wishlist )
     *      "productId" = the productID which should be added to the wishlist
     * ]
     */
    public function addProduct_Entry(string $productId,string $wishlistId,SalesChannelContext $context, Request $request): JsonResponse
    {
        return $this->addProduct($productId,$wishlistId,$context,$request);
    }

    /**
     * @Route(
     *     "/store-api/pixup-wishlist/remove-product/{productId}/{wishlistId?null}",
     *     name="store-api.action.pixup-wishlist.delete-product",
     *     methods={"GET"}
     * )
     */
    public function removeProduct_Entry(string $productId,string $wishlistId,SalesChannelContext $context, Request $request){
        return $this->removeProduct($productId,$wishlistId,$context,$request);
    }

    /**
     * @Route(
     *     "/store-api/pixup-wishlist/get-product-state/{productId}",
     *     name="store-api.action.pixup-wishlist.get-product-state",
     *     methods={"GET"}
     * )
     *\\ returns a isOnWishlist parameter which defines if a product is on the wishlist of the customer
     */
    public function getProductState_Entry(string $productId,SalesChannelContext $context, Request $request){
        return $this->getProductState($productId,$context,$request);
    }

    /**
     * @Route(
     *     "/store-api/pixup-wishlist/switch-variant/{parentId}/{options}/{wishedGroupId}",
     *     name="store-api.action.pixup-wishlist.switch-variant",
     *     methods={"GET"}
     * )
     *\\ returns a variant (product)ID of an product based on the options(IDS) / parentID and wishedGroupId
     *\\ options = [groupID1=>optionID1,groupID2=>optionID2] ( send json encoded )
     */
    public function switchVariant_Entry(
        string $parentId,
        string $options,
        string $wishedGroupId,
        Request $request,
        SalesChannelContext $context
    ){
        return $this->switchVariant($parentId,$options,$wishedGroupId,$request,$context);
    }

    /**
     * @Route(
     *     "/store-api/pixup-wishlist/replace-product/{oldId}/{newId}/{wishListId}",
     *     name="store-api.action.pixup-wishlist.replace-product",
     *     methods={"GET"}
     * )
     *\\ description : replaces the oldProductId with the newProductId ( based on the $wishlistId )
     *\\ returns: success => true or false
     */
    public function replaceProduct_Entry(
        string $oldId,
        string $newId,
        string $wishListId,
        Request $request,
        SalesChannelContext $context
    ){
        return $this->replaceProduct($oldId,$newId,$wishListId,$request,$context);
    }

    /**
     * @Route(
     *     "/store-api/pixup-wishlist/get-wishlist/{productId?null}/{wishlistId?null}/{returnProducts?false}",
     *     name="store-api.action.pixup-wishlist.get-wishlist",
     *     methods={"GET"}
     * )
     *
     *\\ returns: array of wishlists see method below getWishlists() for more information
     */
    public function getWishlist_Entry(string $productId,string $wishlistId,string $returnProducts, SalesChannelContext $context, Request $request){
        return $this->getWishlist($productId,$wishlistId,$returnProducts,$context,$request);
    }

    /**
     * @Route(
     *     "/store-api/pixup-wishlist/create-wishlist/{name}/{products?null}/{private?string}/{editable?string}/{birthday?string}/{password?null}/{wishListId?null}",
     *     name="store-api.action.pixup-wishlist.create-wishlist",
     *     methods={"GET"}
     * )
     *\\ description : creates a wishlist
     * \\ params : products = array with product IDS json_ecoded example: [productid1,productid2,productid3]
     *\\ returns: array of wishlists see method below getWishlists() for more information
     */
    public function createWishlist_Entry(string $name,string $products,string $private,
                                   string $editable,string $birthday,string $password,string $wishListId,
                                   SalesChannelContext $context,Request $request){
        return $this->createWishlist($name,$products,$private,$editable,$birthday,$password,$wishListId,$context,$request);
    }
    /**
     * @Route(
     *     "/store-api/pixup-wishlist/edit-wishlist/{name}/{wishListId}/{private?string}/{editable?string}/{birthday?string}/{password?null}",
     *     name="store-api.action.pixup-wishlist.edit-wishlist",
     *     methods={"GET"}
     * )
     *\\ description : edits an exsisting wishlist
     *\\ NOTE: if the password is set to "default" it will set the default password ( which is already set )
     *\\ returns: wishlistId and success
     */
    public function editWIshlist_Entry(string $name,string $wishListId,string $private,
                                 string $editable,string $birthday,string $password,
                                 SalesChannelContext $context,Request $request){
        return $this->editWIshlist($name,$wishListId,$private,$editable,$birthday,$password,$context,$request);
    }
    /**
     * @Route(
     *     "/store-api/pixup-wishlist/remove-wishlist/{wishlistId}",
     *     name="store-api.action.pixup-wishlist.remove-wishlist",
     *     methods={"GET"}
     * )
     *\\ description : removes a wishlist based on ID ( if customer is only subscriber from wishlist it will remove the customer from the subscriber association )
     *\\ returns: wishlistId and success
     */
    public function removeWishlist_Entry(string $wishlistId, SalesChannelContext $context,Request $request){
        return $this->removeWishlist($wishlistId,$context,$request);
    }

    /**
     * @Route(
     *     "/store-api/pixup-wishlist/subscribe-wishlist/{wishlistId}/{password?null}",
     *     name="store-api.action.pixup-wishlist.subscribe-wishlist",
     *     methods={"GET"}
     * )
     *\\ description : subscribes to an wishlist ( if its public and editable or birthday )
     *\\ returns: bool success true or false
     */
    public function subscribeToWishlist_Entry(string $wishlistId,$password,SalesChannelContext $context, Request $request){
        return $this->subscribeToWishlist($wishlistId,$password,$context,$request);
    }

}
