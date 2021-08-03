<?php
namespace NetzpBlog6\Resolver;

use NetzpBlog6\Core\Content\Author\AuthorDefinition;
use NetzpBlog6\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class BlogStructureElementResolver extends AbstractCmsElementResolver
{
    private $systemConfig;

    public function getType(): string
    {
        return 'netzp-blog6-structure';
    }

    public function __construct(SystemConfigService $systemConfig)
    {
        $this->systemConfig = $systemConfig;
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $config = $slot->getFieldConfig();
        $type = $config->get('type')->getValue();

        $criteriaCollection = new CriteriaCollection();
        if($type == 'categories') {
            $criteria = $this->getCategoriesCriteria();
            $criteriaCollection->add('netzp_blog6_structure', CategoryDefinition::class, $criteria);
        }
        else if($type == 'authors') {
            $criteria = $this->getAuthorsCriteria();
            $criteriaCollection->add('netzp_blog6_structure', AuthorDefinition::class, $criteria);
        }

        return $criteriaCollection;
    }

    private function getCategoriesCriteria()
    {
        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('title', FieldSorting::ASCENDING));

        return $criteria;
    }

    private function getAuthorsCriteria()
    {
        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('name', FieldSorting::ASCENDING));

        return $criteria;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $data = new ArrayEntity();
        $slot->setData($result->get('netzp_blog6_structure'));
    }
}
