<?php declare(strict_types=1);

namespace ITDelight\SliderText\Core\Content\Cms\SalesChannel\Struct;

use Shopware\Core\Content\Cms\SalesChannel\Struct\ImageSliderItemStruct;

/**
 * Class ImageSliderTextItemStruct
 * @package ITDelight\SliderText\Core\Content\Cms\SalesChannel\Struct
 */
class ImageSliderTextItemStruct extends ImageSliderItemStruct
{
    /** @var string|null */
    protected $content;

    /** @var string|null */
    protected $imageHref;

    public function getApiAlias(): string
    {
        return 'cms_image_slider_text_item';
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string|null
     */
    public function getImageHref(): ?string
    {
        return $this->imageHref;
    }

    /**
     * @param string|null $imageHref
     */
    public function setImageHref(?string $imageHref): void
    {
        $this->imageHref = $imageHref;
    }

}
