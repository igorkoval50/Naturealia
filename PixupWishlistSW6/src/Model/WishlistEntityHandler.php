<?php


namespace Pixup\Wishlist\Model;

use Pixup\Wishlist\Entitys\Aggregate\WishlistCustomer\Model\WishlistCustomerModel;
use Pixup\Wishlist\Entitys\Collections\WishlistCollection;
use Pixup\Wishlist\Entitys\Model\WishlistBirthdayModel;
use Pixup\Wishlist\Entitys\Model\WishlistModel;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionCollection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Struct\ArrayEntity;

class WishlistEntityHandler
{
    /**
     * @var EntityRepository
     */
    private $wishListRepository;

    /**
     * @var EntityRepository
     */
    private $wishListProductsRepository;

    /**
     * @var EntityRepository
     */
    private $wishListCustomerRepository;
    /**
     * @var EntityRepository
     */
    private $wishListSubscriberRepository;
    /**
     * @var EntityRepository
     */
    private $wishlistBirthdayRepository;

    public function __construct(EntityRepository $pixupWishlistRepository,
                                EntityRepository $wishListProductsRepository,
                                EntityRepository $wishListCustomerRepository,
                                EntityRepository $wischListSubscriberRepository,
                                EntityRepository $wishListBirthdayRepository
    )
    {
        $this->wishListRepository = $pixupWishlistRepository;
        $this->wishListProductsRepository = $wishListProductsRepository;
        $this->wishListCustomerRepository = $wishListCustomerRepository;
        $this->wishListSubscriberRepository = $wischListSubscriberRepository;
        $this->wishlistBirthdayRepository = $wishListBirthdayRepository;
    }


    public function deleteCookieCustomerByExpiredDays(int $days){
        //get all Customer which did not used there wishlist for $days
        // based on the updatedAt field ( if its not set based on the createdAt field )
        $result = $this->wishListCustomerRepository->search(
            (new Criteria())->addFilter(
                new MultiFilter('OR',[
                    new RangeFilter(
                        'updatedAt',[
                        RangeFilter::LT => date(DATE_ATOM,time() - ($days*24*60*60)),
                    ]),
                    new MultiFilter('AND',[
                        new RangeFilter(
                            'createdAt',[
                            RangeFilter::LT => date(DATE_ATOM,time() - ($days*24*60*60)),
                        ]),
                        new EqualsFilter('updatedAt',null)
                    ])
                ])
            )->addAssociation('customer')
            ,Context::createDefaultContext()
        )->getEntities();
        /**
         * @var WishlistCustomerModel $res
         */
        foreach($result as $res){
            if(empty($res->getCustomer())) {
                $this->wishListCustomerRepository->delete([[
                    "id" => $res->getId()
                ]], Context::createDefaultContext());
            }
        }
    }

    /**
     * @param $wishlistId
     * @param $customerId
     * @param bool $includeSubscried
     * @return bool
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @description checks ( based on wishlistId and customerID ) if the provieded wishlistId is owned by customerId
     *              if includeSubscried is true the function will also return true if the provided wishlistID is subscribed by customerId
     */
    public function checkForOwnWishlist($wishlistId, $customerId, $includeSubscried = false) :bool{
        $criteraia =  (new Criteria())
            ->addFilter(new EqualsFilter('id',$wishlistId));
        if($includeSubscried)
            $criteraia->addAssociation('subscribers')
                ->addFilter(new MultiFilter("OR",
                        [
                            new MultiFilter("AND",
                                [
                                    new EqualsFilter('subscribers.id',$customerId),
                                    new MultiFilter("AND",
                                        [
                                            new MultiFilter("OR",[new EqualsFilter('editable',1),new EqualsFilter('birthday',1)]),
                                            new EqualsFilter('private',0)
                                        ]
                                    )
                                ]
                            ),
                            new EqualsFilter('customer.id',$customerId)
                        ]
                    )
                );
        else
            $criteraia->addFilter(new EqualsFilter('customer.id',$customerId));
        $count = $this->wishListRepository->search(
            $criteraia,
            Context::createDefaultContext()
        )->count();

        if($count > 0)
            return true;
        else
            return false;
    }

    /**
     * @param string $wishlistId
     * @param string $referenceId
     * @param string $salesChannelId
     * @param string $productId
     * @return bool
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @description Notifys the wishlist that a user added a product from a birthday wishlist
     *              so that the wishlist can remove the product later on if the product whas bought by the user
     */
    public function setBirthdayUserAddedProduct(string $wishlistId,string $referenceId, string $salesChannelId, string $productId){
        //check if wishlist is a birthday wishlist
        $criteria = (new Criteria())->addAssociation('salesChannels')->addAssociation('subscribers');
        $criteria->addFilter(new EqualsFilter('id',$wishlistId));
        $criteria->addFilter(new MultiFilter('OR',[
            new EqualsFilter('customer.id',$referenceId),
            new EqualsFilter('subscribers.id',$referenceId)
        ]));
        $criteria->addFilter(new EqualsFilter('birthday',1));
        $criteria->addFilter(new EqualsFilter('salesChannels.id',$salesChannelId));
        $res = $this->wishListRepository->search($criteria,Context::createDefaultContext())->getEntities()->first();
        if($res === null)
            return false;

        $this->wishlistBirthdayRepository->upsert([
            ['customerId'=>$referenceId,'productId'=>$productId,'wishlistId'=>$wishlistId]
        ],Context::createDefaultContext());
        return true;
    }

    /**
     * @param $productId
     * @param $id
     * @return string
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @description removes all entrys from a user that include the givin productId inside the birthday table
     */
    public function removeUserFromBirthdayListByProductId($productId,$id) :string{
        //get birthdayEntry for user
        $res = $this->wishlistBirthdayRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('productId',$productId))->addFilter(new EqualsFilter('customerId',$id))
                ->addAssociation('customer')->addAssociation('wishlist')->addAssociation('product'),
            Context::createDefaultContext()
        )->getEntities();
        /**
         * @var WishlistBirthdayModel $re
         */
        foreach($res as $re) {
            if(empty($re))
                continue;
            $this->wishlistBirthdayRepository->delete([
                ['customerId' => $re->getCustomer()->getId(), 'productId' => $re->getProduct()->getId(), 'wishlistId' => $re->getWishlist()->getId()]
            ], Context::createDefaultContext());
        }
        if(empty($re))
            return "";
        return $re->getWishlist()->getId();
    }

    /**
     * @param $wishlistId
     * @param $id
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @description removes all entrys from user (inside the birthday table) that include the wishlistId
     */
    public function removeUserFromBirthdayListByWishlistId($wishlistId,$id){
        //get birthdayEntry for user
        $res = $this->wishlistBirthdayRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('wishlistId',$wishlistId))->addFilter(new EqualsFilter('customerId',$id))
                ->addAssociation('customer')->addAssociation('wishlist')->addAssociation('product'),
            Context::createDefaultContext()
        )->getEntities();
        foreach($res as $re)
            $this->wishlistBirthdayRepository->delete([
                ['customerId'=>$re->getCustomer()->getId(),'wishlistId'=>$re->getWishlist()->getId(),'productId'=>$re->getProduct()->getId()]
            ],Context::createDefaultContext());
    }

    /**
     * @param array $products
     * @param string $wishListId
     * @param string $id
     * @param string|null $salesChannelId
     * @return bool
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @description deletes a product from a Birthday wishlist ( for example if its bought by a subscribed / owned user )
     */
    public function deleteProductFromBirthdayWishlist(array $products, string $wishListId, string $id,string $salesChannelId = null): bool{
        //check for permissions
        $rights = $this->checkForRights($wishListId,$id,$salesChannelId);
        if(!$rights['canBirthdayWishlist'])
            return false;
        foreach($products as $product) {
            $this->wishListProductsRepository->delete(
                [
                    ['wishlistId' => $wishListId,'productId' => $product]
                ],
                \Shopware\Core\Framework\Context::createDefaultContext()
            );
        }

        return true;
    }

    /**
     * @param string $wishlistId
     * @param string $salesChannelId
     * @param string|null $referenceId
     * @return EntityCollection
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @description return all wishlist that are public and ( if referenceId isset ) owned by user or subscribed by user
     */
    public function getPublicWishlistById(string $wishlistId,string $salesChannelId,string $referenceId = null){
        $criteraia =  (new Criteria())->addAssociation('salesChannels')->addAssociation('products')->addAssociation('subscribers')
            ->addFilter(new EqualsFilter('id',$wishlistId))
            ->addFilter(new EqualsFilter('private',0))
            ->addFilter(new EqualsFilter('salesChannels.id',$salesChannelId));
            // so you only get external wishlists back
        if($referenceId !== null)
        $criteraia->addFilter(new NotFilter('AND',[
                new EqualsFilter('customerId',$referenceId),
                new EqualsFilter('subscribers.id',$referenceId)
            ]));

        return $this->wishListRepository->search(
            $criteraia,
            Context::createDefaultContext()
        )->getEntities();
    }

    /**
     * @param string|null $customerId
     * @param null $cookieId
     * @return string|null
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @description returns the customerID (wishlistCustomer) of a customer based on the cookieID or SW_CustomerID
     */
    public function getWishlistCustomerId(string $customerId=null,$cookieId=null):?string{
        if($customerId==null && $cookieId==null)
            return null;

        $criteria = (new Criteria());
        if($cookieId!==null && $customerId !== null){
            $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR,[
                new EqualsFilter('id',$cookieId),
                new EqualsFilter('customerId',$customerId)
            ]));
        }elseif($cookieId!==null)
            $criteria->addFilter(new EqualsFilter('id',$cookieId));
        elseif($customerId!==null)
            $criteria->addFilter(new EqualsFilter('customerId',$customerId));

        $res = $this->wishListCustomerRepository->search(
            $criteria,
            \Shopware\Core\Framework\Context::createDefaultContext()
        )->getEntities()->first();
        if($res == null)
            return null;
        //update updatedAt Field to specify last usage of user
        if($res->getCustomer() === NULL)
            $this->wishListCustomerRepository->update(
                [
                    ["id"=>$res->getId()]
                ],
                Context::createDefaultContext()
            );
        /**
         * @var WishlistCustomerModel $res
         */

        if($res == null)
            return null;
        //check if customerId isset but not inserted inside the database
        if($res->getCustomer() === NULL && $customerId !== NULL){
            $this->wishListCustomerRepository->update([
                   // ['id' => $res->getId(),'customer'=>["id"=>$customerId]]
                    ['id'=>$res->getId(),"customerId"=>$customerId]
                ],
                Context::createDefaultContext()
            );
        }

        return $res->getId();
    }

    /**
     * @param null $cookieID
     * @param null $customerId
     * @return string
     * @description creates a WishlistCustomer User Entry
     */
    public function createWishlistCustomer($cookieID=null,$customerId =null) :string{
        if($cookieID == null && $customerId == null)
            return false;

        if($cookieID == null)
            $row['customerId'] = $customerId;
        elseif($customerId == null)
            $row = ['id'=>$cookieID];
        else
            $row = ['id'=>$cookieID,'customerId'=>$customerId];
        $customerId = $this->wishListCustomerRepository->create(
            [
                $row
            ],
            \Shopware\Core\Framework\Context::createDefaultContext()
        )->getEventByEntityName("pixup_wish_list_customers")->getIds()[0];
        return $customerId;
    }

    /**
     * @param string $salesChannelId
     * @param string $key
     * @param bool $isUser
     * @return int
     * @description returns the available wishlists count
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public function countWishlists(string $salesChannelId,string $referenceId,bool $includeSubscribedWishlists = false,bool $includeBirthdayWishlists = false): int
    {
        $criteraia =  (new Criteria())->addAssociation('salesChannels')
            ->addFilter(new EqualsFilter('salesChannels.id',$salesChannelId));

        if($includeSubscribedWishlists && $includeBirthdayWishlists)
            $criteraia->addAssociation('subscribers')
                ->addFilter(new MultiFilter("OR",
                        [
                            new MultiFilter("AND",
                                [
                                    new EqualsFilter('subscribers.id',$referenceId),
                                    new MultiFilter("AND",
                                        [
                                            new MultiFilter("OR",[new EqualsFilter('editable',1),new EqualsFilter('birthday',1)]),
                                            new EqualsFilter('private',0)
                                        ]
                                    )
                                ]
                            ),
                            new EqualsFilter('pixup_wish_list.customer.id',$referenceId)
                        ]
                    )
                );
        elseif($includeSubscribedWishlists){
            $criteraia->addAssociation('subscribers')
                ->addFilter(new MultiFilter("OR",
                        [
                            new MultiFilter("AND",
                                [
                                    new EqualsFilter('subscribers.id',$referenceId),
                                    new MultiFilter("AND",
                                        [
                                            new EqualsFilter('editable',1),
                                            new EqualsFilter('private',0)
                                        ]
                                    )
                                ]
                            ),
                            new EqualsFilter('pixup_wish_list.customer.id',$referenceId)
                        ]
                    )
                );
        }else
            $criteraia->addFilter(new EqualsFilter('pixup_wish_list.customer.id',$referenceId));
        $count = $this->wishListRepository->search(
            $criteraia,
            \Shopware\Core\Framework\Context::createDefaultContext()
        )->count();

        return (int)$count;
    }

    /**
     * @param string $salesChannelId //salesChannelID
     * @param bool $isUser // specifiy if the $referenceID is a cookieID or a userID
     * @param string $referenceId // referenceID
     * @param string $productId // productID to check for
     * @return int
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public function countWishlistsWhichIncludeProductId(string $salesChannelId,string $referenceId,string $productId,bool $includeSubscribedWishlists=false,bool $includeBirthdayWishlists=false){
        $criteria = (new Criteria())->addAssociation('salesChannels')->addAssociation('products')
            ->addFilter(new EqualsFilter('salesChannels.id',$salesChannelId))
            ->addFilter(new EqualsFilter('products.id',$productId));

        //count subscribed wishlists which are editable and public
        if($includeSubscribedWishlists && $includeBirthdayWishlists) {
            $criteria->addAssociation('subscribers')
                ->addFilter(
                    new MultiFilter("OR",
                        [
                            new MultiFilter("AND",
                                [
                                    new EqualsFilter('subscribers.id', $referenceId),
                                    new MultiFilter("AND",
                                        [
                                            new MultiFilter("OR",[new EqualsFilter('editable',1),new EqualsFilter('birthday',1)]),
                                            new EqualsFilter('private',0)
                                        ]
                                    )
                                ]
                            ),
                            new EqualsFilter('pixup_wish_list.customer.id', $referenceId)
                        ]
                    )
                );
        }elseif($includeSubscribedWishlists){
            $criteria->addAssociation('subscribers')
                ->addFilter(
                    new MultiFilter("OR",
                        [
                            new MultiFilter("AND",
                                [
                                    new EqualsFilter('subscribers.id', $referenceId),
                                    new MultiFilter("AND",
                                        [
                                            new EqualsFilter('birthday',1),
                                            new EqualsFilter('private',0)
                                        ]
                                    )
                                ]
                            ),
                            new EqualsFilter('pixup_wish_list.customer.id', $referenceId)
                        ]
                    )
                );
        }else
            $criteria->addFilter(new EqualsFilter('pixup_wish_list.customer.id',$referenceId));
        $count = $this->wishListRepository->search(
            $criteria,
            \Shopware\Core\Framework\Context::createDefaultContext()
        )->count();

        return (int)$count;
    }

    /**
     * @param string $productId // single productID
     * @param string $salesChannelId // sales channel ( will only look for wishlist that match that salesChannel )
     * @param string $id // wishlistCustomerId
     * @param string|NULL $wishListId //wishlistID ( if sit it will only search inside that ID )
     * @return bool
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @description checks if the product is on one of the wishlist from the customer($id)
     * this will also return products which are on subscribed wishlists that are editable
     */
    public function checkIfProductExists(string $productId, string $salesChannelId,string $id, string $wishListId = NULL): bool
    {
        $criteria =  (new Criteria())->addAssociation('products')->addAssociation('salesChannels')->addAssociation('subscribers')
            //->addFilter(new EqualsFilter('pixup_wish_list.customer.id',$id))
            ->addFilter(new EqualsFilter('salesChannels.id',$salesChannelId))
            ->addFilter(new EqualsFilter('products.id',$productId))
            ->addFilter(new MultiFilter("OR",
                    [
                        new MultiFilter("AND",
                            [
                                new EqualsFilter('subscribers.id',$id),
                                new MultiFilter("AND",
                                    [
                                        new EqualsFilter('editable',1),
                                        new EqualsFilter('private',0)
                                    ]
                                )
                            ]
                        ),
                        new EqualsFilter('pixup_wish_list.customer.id',$id)
                    ]
                )
            );

        if($wishListId !== NULL)
            $criteria->addFilter(new EqualsFilter('pixup_wish_list.id',$wishListId));

        $count = $this->wishListRepository->search(
            $criteria,
            \Shopware\Core\Framework\Context::createDefaultContext()
        )->count();

        return ($count == null)?false:true;
    }

    /**
     * @param string $wishListId
     * @param string $id
     * @param string|null $salesChannelId
     * @return array [
     *                  'canAddProducts'=>bool,
     *                  'canSeeWishlist'=>bool,
     *                  'canSetWishlistSettings'=>bool,
     *                  'isOwnWishlist'=>bool,
     *                  'canBirthdayWishlist'=>bool
     * ]
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @description checks if te passed $id ( userID ) have the rights to eather edit|see or add Products to the givin $wishlistId
     */
    public function checkForRights(string $wishListId, string $id,string $salesChannelId = null):array{
        $isOwnWishlist = true;
        $cannAddProducts = true;
        $canSeeWishlist = true;
        $canSetWishlistSettings = true;
        $canBirthdayWishlist = false;

        //get own wishlists
        $criteria =  (new Criteria())->addAssociation('salesChannels')
            ->addFilter(new EqualsFilter('pixup_wish_list.customer.id',$id))
            ->addFilter(new EqualsFilter('pixup_wish_list.id',$wishListId));

        if($salesChannelId !== null)
            $criteria->addFilter(new EqualsFilter('salesChannels.id',$salesChannelId));

        $res = $this->wishListRepository->search(
            $criteria,
            \Shopware\Core\Framework\Context::createDefaultContext()
        );

        //check if the wishlistId is subscribed by givin user
        if($res->getEntities()->first() == null){
            $isOwnWishlist = false;
            //check if user is subscriber
            $criteria =  (new Criteria())->addAssociation('salesChannels')->addAssociation('subscribers')
                ->addFilter(new EqualsFilter('pixup_wish_list.subscribers.id',$id))
                ->addFilter(new EqualsFilter('pixup_wish_list.id',$wishListId));
            $res = $this->wishListRepository->search(
                $criteria,
                \Shopware\Core\Framework\Context::createDefaultContext()
            );
            if($res->getEntities()->first() == null){
                $cannAddProducts = false;
                $canSetWishlistSettings = false;
                $canSeeWishlist = false;
            }else{
                /**
                 * @var WishlistModel $wishlist
                 */
                $wishlist = $res->getEntities()->first();
                $cannAddProducts = $wishlist->isEditable();
                $canSeeWishlist = !$wishlist->isPrivate();
                $canBirthdayWishlist = $wishlist->isBirthday();
                $canSetWishlistSettings = false;
            }
        }
        return [
            'canAddProducts'=>$cannAddProducts,
            'canSeeWishlist'=>$canSeeWishlist,
            'canSetWishlistSettings'=>$canSetWishlistSettings,
            'isOwnWishlist'=>$isOwnWishlist,
            'canBirthdayWishlist'=>$canBirthdayWishlist,
        ];
    }

    /**
     * @param array $products // products array [id1,id2]
     * @param string $wishListId
     * @return bool
     */
    public function deleteProductFromWishlist(array $products, string $wishListId, string $id,string $salesChannelId = null): bool
    {
        //check for permissions
        $rights = $this->checkForRights($wishListId,$id,$salesChannelId);
        if(!$rights['canAddProducts'])
            return false;
        foreach($products as $product) {
            $this->wishListProductsRepository->delete(
                [
                    ['wishlistId' => $wishListId, 'productId' => $product]
                ],
                \Shopware\Core\Framework\Context::createDefaultContext()
            );
        }

        return true;
    }

    /**
     * @param string $salesChannelId // salesChannelID (will only return wishlists that match that ID)
     * @param string $referenceId
     * @param string $productId // if this is set the Method will only return wishlists that contain that productID
     * @param string $name // if this is set the method will only return wishlists with the givin name
     * @param string $wishlistId // if this is set the method will only return wishlists which have the provided wishlistId
     * @param bool $includeSubscribed // specifys if subscribed wishlists should be returned too
     * @return EntityCollection
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public function getWishlists(string $salesChannelId, string $referenceId,string $productId = "",string $name="",string $wishlistId = "",bool $includeSubscribed = false) :EntityCollection{
        $criteria = (new Criteria())
            ->addAssociation('salesChannels')
            ->addAssociation('products')
            ->addAssociation('products.prices')
            ->addFilter(new EqualsFilter('salesChannels.id',$salesChannelId))
            ->addSorting(new FieldSorting('pixup_wish_list.name',FieldSorting::ASCENDING))
            ->addSorting(new FieldSorting('products.name',FieldSorting::ASCENDING))
            ->addFilter(new EqualsFilter('pixup_wish_list.customer.id',$referenceId));
        if(!empty($productId))
            $criteria->addFilter(new EqualsFilter('products.id',$productId));
        if(!empty($name))
            $criteria->addFilter(new EqualsFilter('name',$name));
        if(!empty($wishlistId))
            $criteria->addFilter(new EqualsFilter('pixup_wish_list.id',$wishlistId));

        $wishlists =  $this->wishListRepository->search(
           $criteria,
            \Shopware\Core\Framework\Context::createDefaultContext()
        )->getEntities();

        if($includeSubscribed) {
            $subscribedWishlists = $this->getSubscribedWishLists($salesChannelId, $referenceId,$productId,$name,$wishlistId);
            //merge both collection objects
            /**
             * @var WishlistModel $wishlist
             */
            foreach($subscribedWishlists as $wishlist) {
                $wishlist->addExtension("pixupIsSubscribed",new ArrayEntity(['isSubscriber'=>true]));
                $wishlists->add($wishlist);
            }
        }
        return $wishlists;
    }

    /**
     * @param string $wishlistId
     * @return mixed|null
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @description returns a wishlist based on the wishlistId
     */
    public function getWishlistById(string $wishlistId){
        $criteria = (new Criteria())
            ->addAssociation('salesChannels')
            ->addAssociation('products')
            ->addAssociation('subscribers')
            ->addFilter(new EqualsFilter('pixup_wish_list.id',$wishlistId));
        return $this->wishListRepository->search(
            $criteria,
            \Shopware\Core\Framework\Context::createDefaultContext()
        )->getEntities();
    }

    /**
     * @param string $salesChannelId
     * @param string $referenceId
     * @param string|null $productId
     * @param string $name
     * @param string $wishlistId
     * @return EntityCollection
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @description returns all subscribedWishlists from a user ( $referenceId)
     */
    public function getSubscribedWishLists(string $salesChannelId,string $referenceId,string $productId = null,string $name="", string $wishlistId = ""){
        $criteria = (new Criteria())
            ->addAssociation('salesChannels')
            ->addAssociation('products')
            ->addAssociation('subscribers')
            ->addFilter(new EqualsFilter('subscribers.id',$referenceId))
            ->addFilter(new EqualsFilter('salesChannels.id',$salesChannelId))
            ->addSorting(new FieldSorting('pixup_wish_list.name',FieldSorting::ASCENDING))
            ->addSorting(new FieldSorting('products.name',FieldSorting::ASCENDING));
        if(!empty($wishlistId))
            $criteria->addFilter(new EqualsFilter('id',$wishlistId));
        if(!empty($productId))
            $criteria->addFilter(new EqualsFilter('products.id',$productId));
        if(!empty($name))
            $criteria->addFilter(new EqualsFilter('name',$name));
        return $this->wishListRepository->search(
            $criteria,
            \Shopware\Core\Framework\Context::createDefaultContext()
        )->getEntities();
    }

    /**
     * @param array $products // and array filled with productIds [id1,id2,id3]
     * @param string $salesChannelId // the salesChannelId which should be used for wishlist
     * @param string $name  // name of the wishlist
     * @param string $referenceId  // the referenceID
     * @param bool $private  // specify if the wishlist is private or public
     * @param bool $editable  // specifiy if the wishlist is edable from outside ( other users )
     * @param bool $birthday  // specifiy if the wishlist is "consumable" so if this is set to true Products will disapear from the wishlist if subscriber bought an item of that
     * @param string|null $password  // specify if the wishlist needs a password for other users to see it
     * @param string|null $wishListId   // $wishlistID that should be used
     * @return string // wishlistID
     */
    public function createWishlist(array $products, string $salesChannelId,string $name,
                                   string $referenceId,bool $private = true,
                                   bool $editable = false,bool $birthday = false, string $password = null, string $wishListId = null
    ): string
    {
        $wProducts = [];
        foreach($products as $product)
            $wProducts[]['id'] = $product;

        $row = [
            'name' => $name,
            'private' => $private,
            'editable' => $editable,
            'birthday' => $birthday,
            'password' => (empty($password))?null:$password,
            'products' => $wProducts,
            'salesChannels' => [["id"=>$salesChannelId]],
            'customerId' => $referenceId
        ];

        if($wishListId !== null)
            $row['id'] = $wishListId;

        $wishListId = $this->wishListRepository->create(
            [
                $row
            ],Context::createDefaultContext()

        )->getEventByEntityName("pixup_wish_list")->getIds()[0];
        return $wishListId;
    }

    /**
     * @param string $wishlistId
     * @param string $salesChannelId
     * @param string $customerId
     * @param string|null $password
     * @return array
     * @description subscribes to a wishlist
     */
    public function subscribeToWishlist(string $wishlistId, string $salesChannelId ,string $customerId,?string $password=null) :array{
        $responseArr = [
            'success' => true,
            'message' => ''
        ];

        /**
         * @var WishlistModel $wishlist
         */
        $wishlist = $this->getWishlistById($wishlistId)->first();
        if($wishlist == null){
            $responseArr['success'] = false;
            $responseArr['message'] = 10;
            return $responseArr;
        }

        //check if wishlistIs Public and (editable or birthday ) and if it needs a password and if its matches the salesChannel
        $match = false;
        foreach($wishlist->getSalesChannels()->getIds() as $salesChannelID){
            if($salesChannelID == $salesChannelId)
                $match = true;
        }

        if(!$match){
            $responseArr['success'] = false;
            $responseArr['message'] = 9;
            return $responseArr;
        }
        if($wishlist == null) {
            $responseArr['success'] = false;
            $responseArr['message'] = 10;
            return $responseArr;
        }
        if($wishlist->isPrivate()) {
            $responseArr['success'] = false;
            $responseArr['message'] = 11;
            return $responseArr;
        }
        if($wishlist->getPassword() !== null){
            if($wishlist->getPassword() !== $password){
                $responseArr['success'] = false;
                $responseArr['message'] = 12;
                return $responseArr;
            }
        }
        if(!$wishlist->isEditable() && !$wishlist->isBirthday()){
            $responseArr['success'] = false;
            $responseArr['message'] = 13;
            return $responseArr;
        }

        $customer = ['id'=>$customerId];

        $this->wishListRepository->update(
            [
                ['id'=>$wishlistId,'subscribers'=>[$customer]]
            ],Context::createDefaultContext()
        );
        return $responseArr;
    }

    /**
     * @param string $wishlistId
     * @description removes all Subscriber from a wishlit
     */
    public function removeAllSubscriberFromWishlist(string $wishlistId){
        $this->wishListSubscriberRepository->delete([
            ['wishlistId'=>$wishlistId]
        ],Context::createDefaultContext());
    }

    /**
     * @param string $wishlistId
     * @param string $id
     * @description removes a specific user from a subscribed wishlist
     */
    public function removeUserFromSubscriberWishlist(string $wishlistId, string $id){
        $this->wishListSubscriberRepository->delete([
            ['wishlistId'=>$wishlistId, 'customerId'=>$id]
        ],Context::createDefaultContext());
    }

    /**
     * @param string $wishListId
     * @param string $name
     * @param bool $private
     * @param bool $editable
     * @param bool $birthday
     * @param string|null $password
     * @return bool
     * @description edits a exisitng wishlist based on the wishlistId
     */
    public function editWishlist(string $wishListId,string $name,bool $private = true,
                                 bool $editable = false,bool $birthday = false, string $password = null):bool{
        //delete all subscriber if wishlist is set to private
        if($private) {
            try {
                $this->removeAllSubscriberFromWishlist($wishListId);
            }catch(\Exception $e){}
        }
        $row = [
            'id' => $wishListId,
            'name' => $name,
            'private' => $private,
            'editable' => $editable,
            'birthday' => $birthday
        ];
        if($password !== 'default')
            $row['password'] = $password;

        $this->wishListRepository->update([
                $row
            ],\Shopware\Core\Framework\Context::createDefaultContext()
        );
        return true;
    }

    /**
     * @param $wishlistId
     * @return bool
     * @description deletes a wishlist
     */
    public function deleteWishlist($wishlistId):bool{
        $this->wishListRepository->delete([
            ["id" => $wishlistId]
        ], Context::createDefaultContext());

        return true;
    }

    /**
     * @param array $products // products array that should be addded to wishlists [id1,id2,id3]
     * @param string $wishListId // wishlistId to add the products to
     * @return bool
     */
    public function addProductsToWishlist(array $products, string $wishListId): bool
    {
        $wProducts = [];
        foreach($products as $product)
            $wProducts[]['id'] = $product;
        $this->wishListRepository->update([
                ['id'=>$wishListId,'products' => $wProducts]
            ],
            \Shopware\Core\Framework\Context::createDefaultContext()
        );

        return true;
    }

    /**
     * @param string $oldProductId
     * @param string $newProductId
     * @param string $wishListId
     * @param string $id
     * @return bool
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @description replaces an productId on a wishlist with a new one
     */
    public function replaceProductIdFromWishlist(string $oldProductId, string $newProductId,string $wishListId,string $id): bool{
        //get all products from current wishlist
        $criteria = (new Criteria())
            ->addAssociation('products')
            ->addFilter(new EqualsFilter('pixup_wish_list.id',$wishListId))
            ->setLimit(1);
        /**
         * @var WishlistModel $wishlistEntity
         */
         $wishlistEntity = $this->wishListRepository->search(
            $criteria,
            \Shopware\Core\Framework\Context::createDefaultContext()
        )->getEntities()->first();

        /**
         * @var ProductEntity $product
         */
        $products = [];
        $matches = 0;
        //replace matching product with new id
        foreach($wishlistEntity->getProducts()->getIds() as $productId){
            if($productId == $oldProductId) {
                $matches++;
                $products[]['id'] = $newProductId;
            }else
                $products[]['id'] = $productId;
        }
        if($matches === 0)
            return false;

        //delete product
        $this->deleteProductFromWishlist([$oldProductId],$wishListId,$id);

        //update product array
        $this->wishListRepository->update([
                ['id'=>$wishListId,'products' => [['id'=>$newProductId]]]
        ],Context::createDefaultContext());

        return true;
    }
}
