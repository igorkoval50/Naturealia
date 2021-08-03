<?php


namespace Pixup\Wishlist\Controller;


use Pixup\Wishlist\Core\Boot;
use Pixup\Wishlist\Entitys\Model\WishlistModel;
use Pixup\Wishlist\Framework\Event\WishlistGetRecoEvent;
use Pixup\Wishlist\Model\ResponseFields;
use Pixup\Wishlist\Model\StructEncoder;
use Shopware\Administration\Snippet\CachedSnippetFinder;
use Shopware\Administration\Snippet\SnippetFinder;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Event\NestedEventDispatcher;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Product\Configurator\ProductPageConfiguratorLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;

class PixupWishlistAjaxController extends AbstractController
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
    public $customerId;

    /**
     * @var string
     */
    private $salesChannelId;

    /**
     * @var TraceableEventDispatcher|NestedEventDispatcher
     * @description depends if the user calls from API Controller or StorfrontController
     * // Shopware passes 2 different classes on the event_dispatcher tag
     */
    private $Eventdispatcher;

    /**
     * @var ProductListingLoader $productLoader
     */
    private $productLoader;
    public function __construct(
        Boot $boot,
        ProductPageConfiguratorLoader $productConfiguratorServiceLoader,
        $dispatcher,
        ProductListingLoader $productLoader
    )
    {
        $this->boot = $boot;
        $this->config = $this->boot->getConfig();
        $this->productConfiguratorServiceLoader = $productConfiguratorServiceLoader;
        $this->Eventdispatcher = $dispatcher;
        $this->productLoader = $productLoader;

        $this->wishListEntityHandler = $this->boot->getFacade()->getWishlistEntityHandler();
        $this->wishListCookieHandler = $this->boot->getFacade()->getCookieHandler();
    }

    private function prePatch(SalesChannelContext $context){
        $this->customerId = $this->wishListEntityHandler->getWishlistCustomerId(
            ($context->getCustomer()==null)?null:$context->getCustomer()->getId(),
            $this->wishListCookieHandler->getCookieId()
        );
        $this->salesChannelId = $context->getSalesChannel()->getId();
    }

    /**
     * \\param array $data
     * \\param array $errorCodes
     * \\return JsonResponse
     * \\description creates a json response
     * \\codes
     *  0 = no errors
     *  1 = wishlistId is required
     *  2 = no rightes to add/delete Products
     *  3 = no rights to see wishlist
     *  4 = no rights to set settings for wishlist
     *  5 = no rights to user birthday functinality
     *  6 = customerId not set
     *  7 = wishlistId not valid
     *  8 = you have to be logged in
     *  9 = wishlist not in salesChannel
     * 10 = wishlist not found
     * 11 = wishlist is private
     * 12 = wrong password for wishlist
     * 13 = wishlist is not subscribable ( not editable and no birthday wishlist)
     * 14 = name for wishlist already exsist
     * 15 = somthing went wrong / internal error
     */
    public function createAjaxResponse(array $data,string $locale = "en-GB",array $errorCodes = [["code"=>0,"label"=>""]]){
        $success = $errorCodes[0]['code'] === 0;
        if($success == false){
            foreach($errorCodes as &$error){
                try {
                    $error['label'] = $this->trans("pixup-wishlist.wishlist-page.errorCodes.".$error['code']);
                }catch(\Exception $e){
                    //if not found
                    $error['label'] = '';
                }
            }
        }
        return $this->json([
            "error" => [
                "success" => $success,
                "codes" => $errorCodes
            ],
            "data" => $data,
        ]);
    }

    protected function trans(string $snippet, array $parameters = []): string
    {
        return $this->container
            ->get('translator')
            ->trans($snippet, $parameters);
    }

    /**
     * //@Route(
     *     "/store-api/v{version}/pixup-wishlist/add-product/{productId}/{wishlistId?null}",
     *     name="store-api.action.pixup-wishlist.add-product",
     *     methods={"GET"}
     * )
     *\\params[
     *      "wishlistId" = the id for the wishlist where the product should be added ( if -1 is passed it will create a new wishlist )
     *      "productId" = the productID which should be added to the wishlist
     * ]
     */
    public function addProduct(string $productId,string $wishlistId,SalesChannelContext $context, Request $request): JsonResponse
    {
        //init response variables
        $success = false;
        $wishListRequired = false;
        $productCurrentlyOnWishlist = false;
        $wishlistIdResponse = null;
        $locale = $locale = $request->query->get('locale', 'en-GB');
        $error = [["code"=>0,"label"=>""]];


        //init context info
        $this->prePatch($context);
        if($this->customerId==null)
            $this->customerId = $this->wishListEntityHandler->createWishlistCustomer($this->wishListCookieHandler->generateCookieId());
        $wishlistId = ($wishlistId === 'null')?null:$wishlistId;

        //if wishlistId == -1 create new wishlist
        $wishListCount = ($wishlistId == -1)?0:$this->wishListEntityHandler->countWishlists(
            $this->salesChannelId,
            $this->customerId,
            true,
            false
        );
        //in case only one wishlist exsist ( and its a subscribed wishlist )
        switch($wishListCount){
            case 0:
                //get snippet for first wishlist name
                $locale = $locale = $request->query->get('locale', 'en-GB');
                $wishlistName = $this->trans("pixup-wishlist.firstWishlist");

                $wishlistId = $this->wishListEntityHandler->createWishlist(
                    [$productId],
                    $this->salesChannelId,
                    $wishlistName,
                    $this->customerId);
                $this->wishListCookieHandler->createWishlistCookie($this->salesChannelId,$wishlistId,$this->customerId);

                $success = true;
                $productCurrentlyOnWishlist = true;
                $wishlistIdResponse=$wishlistId;
                break;
            case 1:
                //check if the one selected wishlist is a subscribed wishlist so a popup window can add a "create new wishlist" option
                if($this->wishListEntityHandler->countWishlists($this->salesChannelId,$this->customerId,false) === 0 && empty($wishlistId)){
                    $success = false;
                    $error = [["code"=>1,"label"=>""]];
                    $productCurrentlyOnWishlist = false;
                    $wishListRequired = true;
                }else{
                    $wishlistId = ($wishlistId == null) ? $this->wishListEntityHandler->getWishlists($this->salesChannelId, $this->customerId)->first()->getId() : $wishlistId;
                    $rights = $this->wishListEntityHandler->checkForRights($wishlistId, $this->customerId);
                    if (!$rights['canAddProducts']) {
                        $success = false;
                        $error = [["code"=>2,"label"=>""]];
                        $productCurrentlyOnWishlist = false;
                        $wishlistIdResponse = $wishlistId;
                    } else {
                        $this->wishListEntityHandler->addProductsToWishlist([$productId], $wishlistId);
                        $success = true;
                        $productCurrentlyOnWishlist = true;
                        $wishlistIdResponse = $wishlistId;
                    }
                }
                break;
            default :
                //if there are more then 1 wishlists
                if($wishlistId !== null) {
                    $rights = $this->wishListEntityHandler->checkForRights($wishlistId,$this->customerId);
                    if(!$rights['canAddProducts']){
                        $success = false;
                        $error = [["code"=>2,"label"=>""]];
                        $productCurrentlyOnWishlist = false;
                        $wishlistIdResponse = $wishlistId;
                    }else {
                        $this->wishListEntityHandler->addProductsToWishlist([$productId], $wishlistId);
                        $success = true;
                        $productCurrentlyOnWishlist = true;
                        $wishlistIdResponse = $wishlistId;
                    }
                }else {
                    $wishListRequired = true;
                }
                break;
        }

        //add product added Event ( for PixupMedia GmbH Recommendation Engine )
        //contact us if you have Interests for a Recommendation Engine
        if($success) {
            $event = $this->boot->getFacade()->getAddProductEvent($productId,$this->customerId);
            $this->Eventdispatcher->dispatch($event, $event::NAME);
        }

        return $this->createAjaxResponse([
            'success' => $success,
            'productOnWishlist'=> $productCurrentlyOnWishlist,
            'wishlistId' => $wishlistIdResponse,
            'wishListIdRequired' => $wishListRequired,
            'customerId' => $this->customerId
        ],$locale,$error);
    }

    /**
     * //@Route(
     *     "/store-api/v{version}/pixup-wishlist/delete-product/{productId}/{wishlistId?null}",
     *     name="store-api.action.pixup-wishlist.delete-product",
     *     methods={"GET"}
     * )
     */
    public function removeProduct(string $productId,string $wishlistId,SalesChannelContext $context, Request $request){
        //init response variables
        $success = false;
        $wishListRequired = false;
        $productCurrentlyOnWishlist = false;
        $wishlistIdResponse = null;
        $wishlistId = ($wishlistId == "null" || empty($wishlistId))?null:$wishlistId;
        $locale = $locale = $request->query->get('locale', 'en-GB');
        $error = [["code"=>0,"label"=>""]];

        //init context info
        $this->prePatch($context);
        if($this->customerId==null)
            return $this->createAjaxResponse([
                'success' => $success,
                'productOnWishlist'=> $productCurrentlyOnWishlist,
                'wishlistId' => $wishlistIdResponse,
                'wishListIdRequired' => $wishListRequired,
                'customerId' => $this->customerId
            ],$locale,[['code'=>6,'label'=>'']]);

        $wishListCount = $this->wishListEntityHandler->countWishlistsWhichIncludeProductId(
            $this->salesChannelId,
            $this->customerId,
            $productId,
            true,
            false
        );
        switch($wishListCount){
            case 0:
                $success = true;
                $productCurrentlyOnWishlist = false;
                $wishListRequired = false;
                break;
            case 1:
                if($wishlistId == null)
                    $wishlistId = $this->wishListEntityHandler->getWishlists($this->salesChannelId,$this->customerId,$productId,"","",true)->first()->getId();
                $success = $this->wishListEntityHandler->deleteProductFromWishlist(
                    [$productId],
                    $wishlistId,
                    $this->customerId,
                    $this->salesChannelId
                );

                $productCurrentlyOnWishlist = false;
                $wishlistIdResponse = $wishlistId;
                $wishListRequired = false;
                if($success == false)
                    $error = [["code"=>2,"label"=>""]];
                break;
            default:
                if($wishlistId !== null) {
                    $this->wishListEntityHandler->deleteProductFromWishlist(
                        [$productId],
                        $wishlistId,
                        $this->customerId,
                        $this->salesChannelId
                    );
                    $success = true;
                    $productCurrentlyOnWishlist = false;
                    $wishlistIdResponse = $wishlistId;
                    $wishListRequired = false;
                }else {
                    $wishListRequired = true;
                    $error = [["code"=>1,"label"=>""]];
                }
                break;
        }
        return $this->createAjaxResponse([
            'success' => $success,
            'productOnWishlist'=> $productCurrentlyOnWishlist,
            'wishlistId' => $wishlistIdResponse,
            'wishListIdRequired' => $wishListRequired,
            'customerId' => $this->customerId
        ],$locale,$error);
    }

    /**
     * //@Route(
     *     "/store-api/v{version}/pixup-wishlist/get-product-state/{productId}",
     *     name="store-api.action.pixup-wishlist.get-product-state",
     *     methods={"GET"}
     * )
     *\\ returns a isOnWishlist parameter which defines if a product is on the wishlist of the customer
     */
    public function getProductState(string $productId,SalesChannelContext $context, Request $request){
        $this->prePatch($context);
        $locale = $locale = $request->query->get('locale', 'en-GB');
        return $this->createAjaxResponse([
            'isOnWishlist'=>$this->boot->getFacade()->getWishlistEntityHandler()->checkIfProductExists($productId,$this->salesChannelId,$this->customerId)
        ]);
    }

    /**
     * //@Route(
     *     "/store-api/v{version}/pixup-wishlist/switch-variant/{parentId}/{options}/{wishedGroupId}",
     *     name="store-api.action.pixup-wishlist.switch-variant",
     *     methods={"GET"}
     * )
     *\\ returns a variant (product)ID of an product based on the options(IDS) / parentID and wishedGroupId
     *\\ options = [groupID1=>optionID1,groupID2=>optionID2] ( send json encoded )
     */
    public function switchVariant(
        string $parentId,
        string $options,
        string $wishedGroupId,
        Request $request,
        SalesChannelContext $context
    ){
        $locale = $locale = $request->query->get('locale', 'en-GB');
        $options = json_decode($options,true);
        $productId = $this->boot->getFacade()->getProductInformationFetcher()->getVariantProductId($parentId,$context,$options,$wishedGroupId);
        return $this->createAjaxResponse([
            'success' => true,
            'parameter' => [
                'options' => json_encode($options),
                'parentId' => $parentId,
                'wishedGroupId' => $wishedGroupId
            ],
            'response' => [
                'id' => $productId
            ]
        ]);
    }

    /**
     * //@Route(
     *     "/store-api/v{version}/pixup-wishlist/replace-product/{oldId}/{newId}/{wishListId}",
     *     name="store-api.action.pixup-wishlist.replace-product",
     *     methods={"GET"}
     * )
     *\\ description : replaces the oldProductId with the newProductId ( based on the $wishlistId )
     *\\ returns: success => true or false
     */
    public function replaceProduct(
        string $oldId,
        string $newId,
        string $wishListId,
        Request $request,
        SalesChannelContext $context
    ){
        $this->prePatch($context);
        $locale = $locale = $request->query->get('locale', 'en-GB');
        $rights = $this->wishListEntityHandler->checkForRights($wishListId,$this->customerId);
        if($rights['canAddProducts'])
            return $this->createAjaxResponse(['success' => $this->wishListEntityHandler->replaceProductIdFromWishlist($oldId,$newId,$wishListId,$this->customerId)]);
        $error = [['code'=>2,'label'=>""]];
        return $this->createAjaxResponse(['success'=>false],$locale,$error);
    }

    /**
     * //@Route(
     *     "/store-api/v{version}/pixup-wishlist/get-wishlist/{productId?null}/{wishlistId?null}/{returnProducts?false}",
     *     name="store-api.action.pixup-wishlist.get-wishlist",
     *     methods={"GET"}
     * )
     *\\ returns: array of wishlists see method below getWishlists() for more information
     */
    public function getWishlist(string $productId,string $wishlistId,string $returnProducts, SalesChannelContext $context, Request $request){
        $productId = ($productId==='null')?"":$productId;
        $wishlistId = ($wishlistId==='null')?"":$wishlistId;
        $returnProducts = $returnProducts == "true";

        $pixupRecoConf=[];
        if($this->config['activateWishlistReco'] && !empty($productId)) {
            $event = $this->boot->getFacade()->getRecoEvent([$productId]);
            /**
             * @var WishlistGetRecoEvent $res
             */
            $res = $this->Eventdispatcher->dispatch($event, $event::NAME);

            $products = $this->productLoader->load(new Criteria($res->getProducts()),$context)->getEntities();
            // foreach($res->getProducts() as $k=>$value)
            //     $products[] = $this->productLoader->load(new Criteria($res->getProducts()),$context)->getEntities();
            $pixupRecoConf = $res->getRecoResponse();
        }


        return $this->createAjaxResponse([
            'wishlists'=>$this->getWishlists($context,$productId,$wishlistId,$returnProducts),
            $pixupRecoConf
        ]);
    }

    /**
     *\\ returns: array of wishlists
     *\\        example:
     *\\        [
     *              [
     *                  'id' => (string) UUID,
     *                  'name' => (string) wishlist name,
     *                  'private' => (bool) is private,           // if this is false the wishlist is a public wishlist
     *                  'editable' => (bool) is editable,         // if this is false the wishlist is not editable for subscribers
     *                  'birthday' => (bool) is birthday,         // if this is false the wishlist is not a birthday wishlist
     *                  //'customer' => $wishlist->getCustomer(), // will not be returned at the moment
     *                  'products' => [                           // the products that are currently on the wishlist
     *                      "productId1","productId2","productId3"
     *                  ],
     *                  'password' => (string) ''||'default',     // this is eaither empty ( if no pw is set ) or 'default' if a password is setted
     *                  'subscribed' => (bool) is subscribed,     // if this is set to true it means that its not the own wishlist .. its just a subscribed one
     *                  'external' => $external                   // if this is true it means that this wishlist is external and just viewed ( not subscribed or owned but public )
     *              ],
     *              [
     *                  "id" => "wishlistId2"
     *                  ...
     *              ]
     *          ]
     */
    private function getWishlists(SalesChannelContext $context,string $productId = "",string $wishlistId="",bool $returnProducts = false) :array{
        $this->prePatch($context);
        if($this->customerId == null)
            return [];

        $response = $this->formatWishlistResponseArray(
            $this->wishListEntityHandler->getWishlists(
                $this->salesChannelId,
                $this->customerId,
                (!empty($productId))?$productId:'',
                '',
                $wishlistId,
                true)
        );
        if($returnProducts) {
            $this->addInfoToProduct($response, $context, true);
        }
        return $response;
    }

    public function addInfoToProduct(array $formatedWishlistResponse, SalesChannelContext $context,$formatRecoResoult = false){
        // have to do it like this because shopware is not able to serialize the returnValue of the ProductLoader
        // ( to json so i can return it)
        // and i need the price information of variant products
        /**
         * @TODO change this after shopware added a propper jsonSerialize() function for the return value of productLoader()
         * ( or encode function to return ajax probebly )
         */
        foreach($formatedWishlistResponse as &$wishlist){
            /**
             * @var ProductEntity $product
             */
            foreach($wishlist['products'] as $key=>$product){
                $product = $this->productLoader->load(new Criteria([$product]),$context)->first();
                /**
                 * @var ProductEntity $product
                 */
                $wishlist['products'][$key] = [
                    "id"=>$product->getId(),
                    "productNumber"=>$product->getProductNumber(),
                    "price"=>$product->getPrice()->getCurrencyPrice($context->getCurrency()->getId()),
                    "prices"=>$product->getPrices(),
                    "purchasePrices"=>$product->getPurchasePrices(),
                    "name"=>$product->getTranslation("name"),
                    "parentId"=>$product->getParentId(),
                    "ean"=>$product->getEan(),
                ];
            }
        }
        if($formatRecoResoult){
            foreach($formatedWishlistResponse as &$wishlist){
                if(empty($wishlist['pixupReco']))
                    continue;
                foreach($wishlist['pixupReco']['wishlist']['products'] as $key=>$product){
                    $product = $this->productLoader->load(new Criteria([$product]),$context)->first();
                    $wishlist['pixupReco']['wishlist']['products'][$key] = [
                        "id"=>$product->getId(),
                        "productNumber"=>$product->getProductNumber(),
                        "price"=>$product->getPrice()->getCurrencyPrice($context->getCurrency()->getId()),
                        "prices"=>$product->getPrices(),
                        "purchasePrices"=>$product->getPurchasePrices(),
                        "name"=>$product->getTranslation("name"),
                        "parentId"=>$product->getParentId(),
                        "ean"=>$product->getEan(),
                    ];
                }
            }
        }
        return $formatedWishlistResponse;
    }
    /**
     * @param array $wishlists
     * @param $context
     * @return array
     * @throws \Shopware\Core\Content\Product\Exception\ProductNotFoundException
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * description adds detailed information to a wishlistArray ( this is needed for product variants )
     */
    public function addAdvancedProductInformationToWishlist(array $wishlists,SalesChannelContext $context,$formatRecoResoult=false):array{
        foreach($wishlists as &$wishlist){
            foreach($wishlist['products'] as $key=>$value){
                $wishlist['products'][$key] = $this->productLoader->load(new Criteria([$value]),$context)->first();
                if(empty($wishlist['products'][$key])) {
                    unset($wishlist['products'][$key]);
                    unset($wishlist['configuratorSettings'][$key]);
                    continue;
                }
                $wishlist['configuratorSettings'][$key] = $this->productConfiguratorServiceLoader->load($wishlist['products'][$key],$context);
            }
        }
        if($formatRecoResoult){
            foreach($wishlists as &$wishlist){
                if(empty($wishlist['pixupReco']))
                    continue;
                foreach($wishlist['pixupReco']['wishlist']['products'] as $key=>$product){
                    $wishlist['pixupReco']['wishlist']['products'][$key] = $this->productLoader->load(new Criteria([$product]),$context)->first();
                }
            }
        }
        return (array)$wishlists;
    }

    /**
     * @param EntityCollection $wishlists
     * @param bool $external
     * @return array
     */
    public function formatWishlistResponseArray(EntityCollection $wishlists,bool $external=false,bool $returnProducts=false) :array{
        $wishlistsResponse = [];
        /**
         * @var WishlistModel $wishlist
         */
        foreach($wishlists as $wishlist){
            $subscribedToWishlist = ($wishlist->getExtension('pixupIsSubscribed') === null)?false:
                $wishlist->getExtension('pixupIsSubscribed')->offsetGet('isSubscriber');

            //add Reco Info
            $pixupRecoConf = [];
            if(!empty($wishlist->getProducts()->getIds())) {
                $pixupRecoConf = $this->getPixupReco($wishlist->getProducts()->getIds());
                if(!empty($pixupRecoConf))
                    $pixupRecoConf = array_values($pixupRecoConf)[0];
            }

            $wishlistsResponse[] = [
                'id' => $wishlist->getId(),
                'name' => $wishlist->getName(),
                'private' => $wishlist->isPrivate(),
                'editable' => $wishlist->isEditable(),
                'birthday' => $wishlist->isBirthday(),
                //'customer' => $wishlist->getCustomer(),
                'isOwnWishlist'=> $wishlist->getCustomer()->getId()==$this->customerId,
                'products' => ($returnProducts)?$wishlist->getProducts():$wishlist->getProducts()->getIds(),
                'password' => (empty($wishlist->getPassword()))?'':'default',
                'subscribed' => $subscribedToWishlist,
                'external' => $external,
                'pixupReco'=>$pixupRecoConf
            ];
        }
        return $wishlistsResponse;
    }

    private function getPixupReco(array $products) :array{
        if($this->config['activateWishlistReco']) {
            $event = $this->boot->getFacade()->getRecoEvent($products);
            /**
             * @var WishlistGetRecoEvent $res
             */
            $res = $this->Eventdispatcher->dispatch($event, $event::NAME);
            return $res->getRecoResponse();
        }
        return [];
    }

    /**
     * //@Route(
     *     "/store-api/v{version}/pixup-wishlist/create-wishlist/{name}/{products?null}/{private?string}/{editable?string}/{birthday?string}/{password?null}/{wishListId?null}",
     *     name="store-api.action.pixup-wishlist.create-wishlist",
     *     methods={"GET"}
     * )
     *\\ description : creates a wishlist
     * \\ params : products = array with product IDS json_ecoded example: [productid1,productid2,productid3]
     *\\ returns: array of wishlists see method below getWishlists() for more information
     */
    public function createWishlist(string $name,string $products,string $private,
                                   string $editable,string $birthday,string $password,string $wishListId,
                                   SalesChannelContext $context,Request $request){
        //init response variables
        $wishlistIdResponse = null;
        $locale = $locale = $request->query->get('locale', 'en-GB');

        //init base variabels
        $this->prePatch($context);
        $isLoggedIn = ($context->getCustomer()==null)?false:true;

        if($this->customerId==null)
            $this->customerId = $this->wishListEntityHandler->createWishlistCustomer($this->wishListCookieHandler->generateCookieId());

        if(!$this->config['enableMultiCookieUsage'] && !$isLoggedIn)
            return $this->createAjaxResponse([
                'success' => false,
                'wishlistId' => $wishlistIdResponse,
            ],$locale,[["code"=>8,"label"=>""]]);

        $products = (empty($products = json_decode($products,true)))?[]:$products;

        if($this->config['wishlistCanBePublic']) {
            $password = (empty($password) || $password == "null") ? null : $password;
            $private = ($private=='false')?false:true;
            $editable = ($editable=='false')?false:true;
            if($this->config['wishlistCanBeBirthday'])
                $birthday = ($birthday=='false')?false:true;
            else
                $birthday = false;
        }else {
            $password = null;
            $private = true;
            $editable = false;
            $birthday = false;
        }

        // check if wishlist name already exsist for this user
        $wishlists = $this->wishListEntityHandler->getWishlists($this->salesChannelId,$this->customerId,'',$name);
        $nameExsist = ($wishlists->count()>0);
        if($nameExsist)
            return $this->createAjaxResponse([
                'success' => false,
                'wishlistId' => $wishlistIdResponse,
            ],$locale,[["code"=>14,"label"=>""]]);

        $wishlistIdResponse = $this->wishListEntityHandler->createWishlist($products,$this->salesChannelId,$name,$this->customerId,$private,$editable,$birthday,$password);
        if(!$isLoggedIn)
            $this->wishListCookieHandler->createWishlistCookie($this->salesChannelId,$wishlistIdResponse,$this->customerId);

        return $this->createAjaxResponse([
            'success' => true,
            'wishlistId' => $wishlistIdResponse
        ]);
    }
    /**
     * //@Route(
     *     "/store-api/v{version}/pixup-wishlist/edit-wishlist/{name}/{wishListId}/{private?string}/{editable?string}/{birthday?string}/{password?null}",
     *     name="store-api.action.pixup-wishlist.edit-wishlist",
     *     methods={"GET"}
     * )
     *\\ description : edits an exsisting wishlist
     *\\ NOTE: if the password is set to "default" it will set the default password ( which is already set )
     *\\ returns: wishlistId and success
     */
    public function editWIshlist(string $name,string $wishListId,string $private,
                                 string $editable,string $birthday,string $password,
                                 SalesChannelContext $context,Request $request){
        //init base variabels
        $this->prePatch($context);
        $locale = $locale = $request->query->get('locale', 'en-GB');
        if($this->customerId==null)
            $this->customerId = $this->wishListEntityHandler->createWishlistCustomer($this->wishListCookieHandler->generateCookieId());

        if($this->config['wishlistCanBePublic']){
            $editable = ($editable == 'false')?false:true;
            if($this->config['wishlistCanBeBirthday'])
                $birthday = ($birthday == 'false')?false:true;
            else
                $birthday = false;
            $private = ($private == 'false')?false:true;
            $password = ($password == 'null')?null:$password;
        }else{
            $editable = false;
            $birthday = false;
            $private = true;
            $password = null;
        }

        // check if wishlist can be eddited by user
        $rights = $this->wishListEntityHandler->checkForRights($wishListId,$this->customerId);
        if(!$rights['canSetWishlistSettings']){
            return $this->createAjaxResponse([
                'success' => false,
                'wishlistId' => $wishListId,
                'message' => 4
            ],$locale,[["code"=>4]]);
        }

        // check if wishlist name already exsist for this user
        $wishlists = $this->wishListEntityHandler->getWishlists($this->salesChannelId,$this->customerId,'',$name);
        $nameExsist = ($wishlists->count()>0);
        if($nameExsist && ($wishlists->count() == 1 && $wishlists->first()->getId() !== $wishListId))
            return $this->createAjaxResponse([
                'success' => false,
                'wishlistId' => $wishListId
            ],$locale,[["code"=>14]]);

        // if password is set to "default" it will not change the users password
        // ( if its empty it will set the password to null )
        try {
            $this->wishListEntityHandler->editWishlist($wishListId, $name, $private, $editable,$birthday, $password);
        }catch(\Exception $e){
            return $this->createAjaxResponse([
                'success' => false,
                'wishlistId' => $wishListId
            ],$locale,[["code"=>15]]);
        }
        return $this->createAjaxResponse([
            'success' => true,
            'wishlistId' => $wishListId
        ]);
    }
    /**
     * //@Route(
     *     "/store-api/v{version}/pixup-wishlist/remove-wishlist/{wishlistId}",
     *     name="store-api.action.pixup-wishlist.remove-wishlist",
     *     methods={"GET"}
     * )
     *\\ description : removes a wishlist based on ID ( if customer is only subscriber from wishlist it will remove the customer from the subscriber association )
     *\\ returns: wishlistId and success
     */
    public function removeWishlist(string $wishlistId, SalesChannelContext $context,Request $request){
        //init response var
        $success = false;
        $locale = $locale = $request->query->get('locale', 'en-GB');
        $this->prePatch($context);

        // check if wishlist can be eddited by user
        $rights = $this->wishListEntityHandler->checkForRights($wishlistId,$this->customerId);
        if(!$rights['canSetWishlistSettings']){
            //remove user from subscriber table so he dont see the wishlist anymore
            $this->wishListEntityHandler->removeUserFromSubscriberWishlist($wishlistId,$this->customerId);
            //remove user from birthday list
            $this->wishListEntityHandler->removeUserFromBirthdayListByWishlistId($wishlistId,$this->customerId);
            return $this->createAjaxResponse([
                'success' => true,
                'wishlistId' => $wishlistId,
            ]);
        }
        try {
            $success = $this->wishListEntityHandler->deleteWishlist($wishlistId);
        }catch(\Exception $e){
            $this->createAjaxResponse([
                "success" => false,
                "wishlistId" => $wishlistId
            ],$locale,[["code"=>15]]);
        }
        return $this->createAjaxResponse([
            'success' => $success,
            'wishlistId' => $wishlistId,
        ]);
    }

    /**
     * //@Route(
     *     "/store-api/v{version}/pixup-wishlist/subscribe-wishlist/{wishlistId}/{password?null}",
     *     name="store-api.action.pixup-wishlist.subscribe-wishlist",
     *     methods={"GET"}
     * )
     *\\ description : subscribes to an wishlist ( if its public and editable or birthday )
     *\\ returns: bool success true or false
     */
    public function subscribeToWishlist(string $wishlistId,$password,SalesChannelContext $context, Request $request){
        $isLoggedIn = ($context->getCustomer()==null)?false:true;
        $locale = $locale = $request->query->get('locale', 'en-GB');
        $error = [["code"=>0,"label"=>""]];
        if(!$isLoggedIn){
            if(!$this->config['cookieUserCanSubscribe']) {
                $error = [["code"=>8,"label"=>""]];
                return $this->createAjaxResponse(['success'=>false],$locale,$error);
            }
        }

        if($this->customerId==null) {
            $this->customerId = $this->wishListEntityHandler->createWishlistCustomer($this->wishListCookieHandler->generateCookieId());
            $this->wishListCookieHandler->createWishlistCookie($context->getSalesChannel()->getId(),'',$this->customerId);
        }

        $res =  $this->wishListEntityHandler->subscribeToWishlist($wishlistId,$context->getSalesChannel()->getId(),$this->customerId,$password);

        if($res['success'] == true){
            if(!$isLoggedIn && $this->wishListCookieHandler->getCookieId() == null)
                $this->wishListCookieHandler->createWishlistCookie($context->getSalesChannel()->getId(),$wishlistId,$this->customerId);
        }else
            $error = [["code"=>$res['message'],"label"=>""]];

        return $this->createAjaxResponse(["success"=>$res['success']],$locale,$error);
    }
}
