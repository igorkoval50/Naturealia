<?php
namespace NetzpBlog6\Resolver;

use NetzpBlog6\Core\Content\Blog\BlogDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class BlogDetailElementResolver extends AbstractCmsElementResolver
{
    private $systemConfig;

    public function getType(): string
    {
        return 'netzp-blog6-detail';
    }

    public function __construct(SystemConfigService $systemConfig)
    {
        $this->systemConfig = $systemConfig;
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('id', $resolverContext->getRequest()->get('postId')));
        $criteria->addAssociation('netzp_blog.translations');
        $criteria->addAssociation('tags');
        $criteria->addAssociation('items');
        $criteria->getAssociation('items')->addSorting(new FieldSorting('number', FieldSorting::ASCENDING));
        $criteria->addAssociation('blogmedia');
        $criteria->getAssociation('blogmedia')->addSorting(new FieldSorting('number', FieldSorting::ASCENDING));

        $criteriaCollection = new CriteriaCollection();
        $criteriaCollection->add('netzp_blog6_detail', BlogDefinition::class, $criteria);

        return $criteriaCollection;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $blogPost = $result->get('netzp_blog6_detail')->first();
        if($blogPost) {
            $slot->setData($blogPost);
        }
    }
}
