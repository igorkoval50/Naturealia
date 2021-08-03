<?php


namespace Pixup\Wishlist\Model;


use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductInformationFetcher
{
    /**
     * @var SalesChannelRepositoryInterface
     */
    private $productRepository;

    public function __construct(SalesChannelRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getVariantProductId(string $productId,SalesChannelContext $salesChannelContext,array $options,$wishedGroupId):?string{
        $variantId = $this->searchForOptions($productId, $salesChannelContext, $options);

        if ($variantId !== null) {
            return $variantId;
        }

        while (\count($options) > 1) {
            foreach ($options as $groupId => $_optionId) {
                if ($groupId !== $wishedGroupId) {
                    unset($options[$groupId]);

                    break;
                }
            }

            $variantId = $this->searchForOptions($productId, $salesChannelContext, $options);

            if ($variantId) {
                return $variantId;
            }
        }
        return null;
    }

    private function searchForOptions(
        string $productId,
        SalesChannelContext $salesChannelContext,
        array $options
    ): ?string {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('product.parentId', $productId))
            ->setLimit(1);

        foreach ($options as $optionId) {
            $criteria->addFilter(new EqualsFilter('product.optionIds', $optionId));
        }

        $ids = $this->productRepository->searchIds($criteria, $salesChannelContext)->getIds();

        return array_shift($ids);
    }

    public function getProduct(string $productId,SalesChannelContext $context){
        $criteria = (new Criteria())
            ->addAssociation('prices')
            ->addFilter(new EqualsFilter('id',$productId));

        return $this->productRepository->search($criteria,$context)->first();
    }
}
