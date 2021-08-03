<?php
namespace NetzpBlog6\Helper;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use NetzpBlog6\Exception\AccessDeniedException;

class BlogHelper
{
    private $container;
    private $config;

    public function __construct(ContainerInterface $container, SystemConfigService $systemConfigService)
    {
        $this->container = $container;
        $this->config = $systemConfigService;
    }

    public function getPublicBlogPosts(SalesChannelContext $salesChannelContext, Context $context, $limit = null, $offset = null,
                                       $rssCategoryOnly = false)
    {
        $repo = $this->container->get('s_plugin_netzp_blog.repository');

        $criteria = new Criteria();
        $this->addBlogDateFilterAndSorting($criteria, true);
        $criteria->addAssociation('category');
        $criteria->addAssociation('tags');
        $criteria->addAssociation('items');
        $criteria->addAssociation('blogmedia');

        $criteria->addFilter(new EqualsFilter('noindex', false));

        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('categoryid', '00000000000000000000000000000000'),
            new EqualsFilter('category.onlyloggedin', false),
        ]));

        if($rssCategoryOnly) {
            $criteria->addFilter(new EqualsFilter('category.includeinrss', true));

            $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('category.saleschannelid', null),
                new EqualsFilter('category.saleschannelid', '00000000000000000000000000000000'),
                new EqualsFilter('category.saleschannelid', $salesChannelContext->getSalesChannel()->getId()),
            ]));
        }

        $criteria->addSorting(new FieldSorting('items.number', FieldSorting::ASCENDING));
        $criteria->addSorting(new FieldSorting('blogmedia.number', FieldSorting::ASCENDING));

        if($limit) {
            $criteria->setLimit($limit);
        }
        if($offset) {
            $criteria->setOffset($offset);
        }
        $posts = $repo->search($criteria, $context)->getEntities();

        return $posts;
    }

    public function isPluginActive($pluginName, Context $context)
    {
        $pluginRepo = $this->container->get('plugin.repository');
        $pluginCriteria = new Criteria();
        $pluginCriteria->addFilter(new EqualsFilter('name', $pluginName));
        $plugin = $pluginRepo->search($pluginCriteria, $context)->getEntities()->first();

        return $plugin && $plugin->getActive();
    }

    public function getBlogPost($postId, SalesChannelContext $salesChannelContext, Context $context)
    {
        $repo = $this->container->get('s_plugin_netzp_blog.repository');

        $criteria = new Criteria();
        $criteria->addAssociation('products.event');
        $criteria->addAssociation('category');
        $criteria->addAssociation('categories');
        $criteria->addAssociation('tags');
        $criteria->addAssociation('items');
        $criteria->getAssociation('items')->addSorting(new FieldSorting('number', FieldSorting::ASCENDING));
        $criteria->addAssociation('blogmedia');
        $criteria->getAssociation('blogmedia')->addSorting(new FieldSorting('number', FieldSorting::ASCENDING));
        $criteria->addAssociation('blogmedia.media');

        $criteria->addFilter(new EqualsFilter('id', $postId));

        $this->addRestrictionsFilter($criteria, $salesChannelContext);
        $this->addBlogDateFilterAndSorting($criteria);

        $post = $repo->search($criteria, $context)->getEntities()->first();
        if( ! $post) {
            throw new AccessDeniedException($postId);
        }

        $assignedProducts = [];
        foreach($post->getProducts() as $product) {
            array_push($assignedProducts, $product->getId());
        }

        if(count($assignedProducts) > 0) {
            $productRepository = $this->container->get('sales_channel.product.repository');
            $criteria2 = new Criteria($assignedProducts);
            $criteria2->addAssociation('cover');
            $criteria2->addAssociation('event');

            $products = $productRepository->search($criteria2, $salesChannelContext)->getEntities();
            $post->setProducts($products);
        }

        return $post;
    }

    public function addRestrictionsFilter(Criteria $criteria, SalesChannelContext $salesChannelContext)
    {
        $userLoggedIn = $salesChannelContext->getCustomer() != null;
        $userCustomerGroupId = $salesChannelContext->getCurrentCustomerGroup()->getId();

        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('category.saleschannelid', null),
            new EqualsFilter('category.saleschannelid', '00000000000000000000000000000000'),
            new EqualsFilter('category.saleschannelid', $salesChannelContext->getSalesChannel()->getId()),
        ]));

        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('categoryid', '00000000000000000000000000000000'),
            new EqualsFilter('category.onlyloggedin', false),

            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('category.onlyloggedin', true),
                new EqualsFilter('category.onlyloggedin', $userLoggedIn)
            ])
        ]));

        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('category.customergroupid', null),
            new EqualsFilter('category.customergroupid', '00000000000000000000000000000000'),

            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new NotFilter(NotFilter::CONNECTION_AND, [
                    new EqualsFilter('category.customergroupid', '00000000000000000000000000000000')
                ]),
                new EqualsFilter('category.customergroupid', $userCustomerGroupId),
            ])
        ]));
    }

    public function addBlogDateFilterAndSorting(Criteria $criteria, bool $addSorting = false)
    {
        $now = (new \DateTime())->format('Y-m-d');
        $criteria->addFilter(
            new MultiFilter(
                MultiFilter::CONNECTION_OR, [
                    new RangeFilter('showfrom', ['lte' => $now]),
                    new EqualsFilter('showfrom', null)
                ]
            )
        );
        $criteria->addFilter(
            new MultiFilter(
                MultiFilter::CONNECTION_OR, [
                    new RangeFilter('showuntil', ['gte' => $now]),
                    new EqualsFilter('showuntil', null)
                ]
            )
        );

        if($addSorting) {
            $criteria->addSorting(new FieldSorting('postdate', 'desc'));
            $criteria->addSorting(new FieldSorting('title', FieldSorting::ASCENDING));
        }
    }
}
