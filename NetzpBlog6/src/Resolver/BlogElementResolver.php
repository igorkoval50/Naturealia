<?php
namespace NetzpBlog6\Resolver;

use NetzpBlog6\Core\Content\Blog\BlogDefinition;
use NetzpBlog6\Helper\BlogHelper;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\EntityAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;

class BlogElementResolver extends AbstractCmsElementResolver
{
    private $systemConfig;
    private $helper;

    public function getType(): string
    {
        return 'netzp-blog6';
    }

    public function __construct(SystemConfigService $systemConfig, BlogHelper $helper)
    {
        $this->systemConfig = $systemConfig;
        $this->helper = $helper;
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $config = $slot->getFieldConfig();
        $categoryId = $config->has('category') ? $config->get('category')->getValue() : '00000000000000000000000000000000';
        $categoryId = $categoryId != null ? $categoryId : '00000000000000000000000000000000';
        $categoryId = $resolverContext->getRequest()->get('c', $categoryId);

        $authorId = $config->has('author') ? $config->get('author')->getValue() : '00000000000000000000000000000000';
        $authorId = $authorId != null ? $authorId : '00000000000000000000000000000000';
        $authorId = $resolverContext->getRequest()->get('a', $authorId);

        $tags = $config->has('tags') && $config->get('tags')->getValue() !== null ? $config->get('tags')->getValue() : [];

        $sortOrder = $config->get('sortOrder')->getValue();
        $maxNumberOfPosts = (int)$config->get('numberOfPosts')->getValue();
        $page = $this->getPage($resolverContext->getRequest());
        if($maxNumberOfPosts < 1) $maxNumberOfPosts = 99999; // show "all"

        $criteria = $this->getSearchCriteria($categoryId, $authorId, $tags, $sortOrder, $resolverContext);
        $this->setPagination($criteria, $page, $maxNumberOfPosts);

        $this->setCategoryFilter($criteria, $resolverContext->getRequest());
        $this->setAuthorFilter($criteria, $resolverContext->getRequest());
        $this->setTagsFilter($criteria, $resolverContext->getRequest());

        $criteriaCollection = new CriteriaCollection();
        $criteriaCollection->add('netzp_blog6', BlogDefinition::class, $criteria);

        return $criteriaCollection;
    }

    private function getPage(Request $request): int
    {
        $page = $request->query->getInt('p', 1);
        if ($request->isMethod(Request::METHOD_POST)) {
            $page = $request->request->getInt('p', $page);
        }

        return $page <= 0 ? 1 : $page;
    }

    private function getSearchCriteria($categoryId, $authorId, $tags, $sortOrder, ResolverContext $resolverContext)
    {
        $criteria = new Criteria();
        $criteria->addAssociation('netzp_blog.translations');
        $criteria->addAssociation('category');
        $criteria->addAssociation('categories');
        $criteria->addAssociation('tags');

        $this->helper->addBlogDateFilterAndSorting($criteria, false); // without sorting
        $this->helper->addRestrictionsFilter($criteria, $resolverContext->getSalesChannelContext());

        if($categoryId != '00000000000000000000000000000000') {
            $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('categoryid', $categoryId),
                new EqualsAnyFilter('categories.id', [$categoryId]),
            ]));
        }

        if($authorId != '00000000000000000000000000000000') {
            $criteria->addFilter(
                new EqualsFilter('authorid', $authorId)
            );
        }

        if($tags != null && count($tags) > 0) {
            $criteria->addFilter(new EqualsAnyFilter('tags.id', $tags));
        }

        $criteria->addSorting(new FieldSorting(
            'postdate', $sortOrder == 'asc' ? FieldSorting::ASCENDING : FieldSorting::DESCENDING)
        );
        $criteria->addSorting(new FieldSorting('title', FieldSorting::ASCENDING));

        return $criteria;
    }

    private function setPagination(Criteria $criteria, $page, $maxNumberOfPosts)
    {
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);
        $criteria->setOffset(($page - 1) * $maxNumberOfPosts);
        $criteria->setLimit($maxNumberOfPosts);
    }

    private function setCategoryFilter(Criteria $criteria, Request $request)
    {
        $criteria->addAggregation(
            new EntityAggregation('categories', 'category.id', 's_plugin_netzp_blog_category')
        );

        $ids = $this->getCategoryIds($request);
        if (empty($ids)) {
            return;
        }

        $criteria->addPostFilter(
            new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsAnyFilter('category.id', $ids),
                new EqualsAnyFilter('categories.id', $ids)
            ])
        );
    }

    private function setAuthorFilter(Criteria $criteria, Request $request)
    {
        $criteria->addAggregation(
            new EntityAggregation('authors', 'author.id', 's_plugin_netzp_blog_author')
        );

        $ids = $this->getAuthorIds($request);
        if (empty($ids)) {
            return;
        }

        $criteria->addPostFilter(
            new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsAnyFilter('author.id', $ids)
            ])
        );
    }

    private function setTagsFilter(Criteria $criteria, Request $request)
    {
        $criteria->addAggregation(
            new EntityAggregation('tags', 'tags.id', 'tag')
        );

        $ids = $this->getTagIds($request);
        if (empty($ids)) {
            return;
        }

        $criteria->addPostFilter(
            new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsAnyFilter('tags.id', $ids)
            ])
        );
    }

    private function getCategoryIds(Request $request): array
    {
        $ids = $request->query->get('categories', '');
        if ($request->isMethod(Request::METHOD_POST)) {
            $ids = $request->request->get('categories', '');
        }
        $ids = explode('|', $ids);

        return array_filter($ids);
    }

    private function getAuthorIds(Request $request): array
    {
        $ids = $request->query->get('authors', '');
        if ($request->isMethod(Request::METHOD_POST)) {
            $ids = $request->request->get('authors', '');
        }
        $ids = explode('|', $ids);

        return array_filter($ids);
    }

    private function getTagIds(Request $request): array
    {
        $ids = $request->query->get('tags', '');
        if ($request->isMethod(Request::METHOD_POST)) {
            $ids = $request->request->get('tags', '');
        }
        $ids = explode('|', $ids);

        return array_filter($ids);
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $salesChannelId = $resolverContext->getSalesChannelContext()->getSalesChannel()->getId();
        $pluginConfig = $this->systemConfig->get('NetzpBlog6.config', $salesChannelId);

        $data = new ArrayEntity();
        $data->setUniqueIdentifier(Uuid::randomHex()); // prevent "Notice: Undefined index: id" when HTTP Cache is enabled ;-(
        $slot->setData($data);

        $data->set('result', $result->get('netzp_blog6'));
        $data->set('pluginConfig', $pluginConfig);
    }
}
