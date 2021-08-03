<?php declare(strict_types=1);

namespace NetzpBlog6\Subscriber;

use NetzpBlog6\Core\SearchResult;
use NetzpBlog6\Helper\BlogHelper;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Struct\StructCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Product\ProductPageCriteriaEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class FrontendSubscriber implements EventSubscriberInterface
{
    const SEARCH_TYPE_BLOG = 10;

    private $container;
    private $config;
    private $helper;

    public function __construct(ContainerInterface $container, SystemConfigService $config, BlogHelper $helper) {
        $this->container = $container;
        $this->config = $config;
        $this->helper = $helper;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageCriteriaEvent::class   => 'onProductCriteriaLoaded',
            ProductPageLoadedEvent::class     => 'loadProductPage',
            'netzp.search.register'           => 'registerSearchProvider'
        ];
    }

    public function onProductCriteriaLoaded(ProductPageCriteriaEvent $event): void
    {
        $this->addBlogCriteria($event->getCriteria());
    }

    private function addBlogCriteria(Criteria $criteria)
    {
        $criteria->addAssociation('blogs');
        $blogAssociation = $criteria->getAssociation('blogs');
        $blogAssociation->addAssociation('tags');
        $blogAssociation->addAssociation('items');
        $blogAssociation->addAssociation('blogmedia');

        $this->helper->addBlogDateFilterAndSorting($blogAssociation);
    }

    public function loadProductPage(ProductPageLoadedEvent $event): void
    {
        $product = $event->getPage()->getProduct();
        $productBlogs = $product->getExtension('blogs');
        $parentId = $product->getParentId();

        if($parentId) {
            $repo = $this->container->get('product.repository');
            $criteria = new Criteria([$parentId]);
            $this->addBlogCriteria($criteria);
            $parentProduct = $repo->search($criteria, $event->getContext())->getEntities()->first();
            $parentBlogs = $parentProduct->getExtension('blogs');
            if($parentBlogs->count() > 0) {
                $product->addExtension('blogs',
                    new StructCollection(array_merge($parentBlogs->getElements(), $productBlogs->getElements()))
                );
            }
        }

        $event->getPage()->assign([
            'netzp_blogposts' => []
        ]);
    }

    public function registerSearchProvider(Event $event)
    {
        $pluginConfig = $this->config->get('NetzpBlog6.config');
        if((bool)$pluginConfig['searchBlog'] === false) {
            return;
        }

        $event->setData([
            'key'      => 'blog',
            'label'    => 'netzp.blog.searchLabel',
            'function' => [$this, 'doSearch']
        ]);
    }

    public function doSearch(string $query, SalesChannelContext $salesChannelContext, bool $isSuggest = false): array
    {
        $results = [];
        $blogEntries = $this->getBlogEntries($query, $salesChannelContext, $isSuggest);

        foreach ($blogEntries->getEntities() as $blogEntry) {
            $tmpResult = new SearchResult();
            $tmpResult->setType(static::SEARCH_TYPE_BLOG);
            $tmpResult->setId($blogEntry->getId());
            $tmpResult->setTitle($blogEntry->getTranslated()['title'] ?? '');
            $tmpResult->setDescription($blogEntry->getTranslated()['teaser'] ?? '');

            if($blogEntry->getImagepreview()) {
                $tmpResult->setMedia($blogEntry->getImagepreview());
            }
            else if($blogEntry->getImage()) {
                $tmpResult->setMedia($blogEntry->getImage());
            }
            $tmpResult->setTotal($blogEntries->getTotal());
            $tmpResult->addExtension('slug', new ArrayStruct(['value' => $blogEntry->getTranslated()['slug'] ?? '']));
            $results[] = $tmpResult;
        }

        return $results;
    }

    private function getBlogEntries($query, SalesChannelContext $salesChannelContext, bool $isSuggest = false)
    {
        $query = trim($query);
        $words = explode(' ', $query);

        if (count($words) > 0) {
            $repo = $this->container->get('s_plugin_netzp_blog.repository');
            $criteria = new Criteria();
            $criteria->addAssociation('category');
            $criteria->addAssociation('image');
            $criteria->addAssociation('imagepreview');

            $this->helper->addBlogDateFilterAndSorting($criteria);
            $this->helper->addRestrictionsFilter($criteria, $salesChannelContext);

            if($isSuggest) {
                $criteria->setLimit(10);
                $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);
            }

            $filter = [];
            foreach ($words as $word) {
                $filter[] = new ContainsFilter('title', $word);
                $filter[] = new ContainsFilter('teaser', $word);
                $filter[] = new ContainsFilter('contents', $word);
            }
            $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, $filter));


            return $repo->search($criteria, $salesChannelContext->getContext());
        }

        return null;
    }
}
