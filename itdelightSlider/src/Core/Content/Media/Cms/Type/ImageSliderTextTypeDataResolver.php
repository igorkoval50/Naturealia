<?php declare(strict_types=1);

namespace ITDelight\SliderText\Core\Content\Media\Cms\Type;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use ITDelight\SliderText\Core\Content\Cms\SalesChannel\Struct\ImageSliderTextItemStruct;
use Shopware\Core\Content\Cms\SalesChannel\Struct\ImageSliderStruct;
use Shopware\Core\Content\Media\Cms\Type\ImageSliderTypeDataResolver;
use Shopware\Core\Content\Media\MediaEntity;

/**
 * Class ImageSliderTextTypeDataResolver
 * @package ITDelight\SliderText\Core\Content\Media\Cms\Type
 */
class ImageSliderTextTypeDataResolver extends ImageSliderTypeDataResolver
{

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'image-slider-text';
    }

    /**
     * @param CmsSlotEntity $slot
     * @param ResolverContext $resolverContext
     * @param ElementDataCollection $result
     */
    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        $imageSlider = new ImageSliderStruct();
        $slot->setData($imageSlider);

        if (($navigation = $config->get('navigation')) && $navigation->isStatic()) {
            $imageSlider->setNavigation($navigation->getValue());
        }

        if ($sliderItems = $config->get('sliderItems')) {
            foreach ($sliderItems->getValue() as $sliderItem) {
                $this->addMedia($slot, $imageSlider, $result, $sliderItem);
            }
        }
    }

    /**
     * @param CmsSlotEntity $slot
     * @param ImageSliderStruct $imageSlider
     * @param ElementDataCollection $result
     * @param array $config
     */
    private function addMedia(
        CmsSlotEntity $slot,
        ImageSliderStruct $imageSlider,
        ElementDataCollection $result,
        array $config
    ): void
    {
        $imageSliderItem = new ImageSliderTextItemStruct();

        if (!empty($config['content'])) {
            $imageSliderItem->setContent($config['content']);
        }

        if (!empty($config['newTab'])) {
            $imageSliderItem->setNewTab($config['newTab']);
        }

        if (!empty($config['imageHref'])) {
            $imageSliderItem->setImageHref($config['imageHref']);
        }

        $searchResult = $result->get('media_' . $slot->getUniqueIdentifier());
        if (!$searchResult) {
            return;
        }

        /** @var MediaEntity|null $media */
        $media = $searchResult->get($config['mediaId']);
        if (!$media) {
            return;
        }

        $imageSliderItem->setMedia($media);
        $imageSlider->addSliderItem($imageSliderItem);
    }
}
