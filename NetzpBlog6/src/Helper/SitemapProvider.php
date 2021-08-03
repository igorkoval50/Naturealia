<?php declare(strict_types=1);

namespace NetzpBlog6\Helper;

use NetzpBlog6\Core\Content\Blog\BlogCollection;
use NetzpBlog6\Core\Content\Blog\BlogEntity;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Content\Sitemap\Provider\UrlProviderInterface;
use Shopware\Core\Content\Sitemap\Struct\Url;
use Shopware\Core\Content\Sitemap\Struct\UrlResult;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class SitemapProvider implements UrlProviderInterface
{
    public const CHANGE_FREQ = 'daily';

    private $seoUrlPlaceholderHandler;
    private $helper;

    public function __construct(BlogHelper $helper, SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler)
    {
        $this->seoUrlPlaceholderHandler = $seoUrlPlaceholderHandler;
        $this->helper = $helper;
    }

    public function getName(): string
    {
        return 'netzp_blog6';
    }

    public function getUrls(SalesChannelContext $salesChannelContext, int $limit, ?int $offset = null): UrlResult
    {
        $urls = [];
        $url = new Url();
        $posts = $this->getPublicBlogPosts($salesChannelContext, $limit, $offset);
        foreach ($posts as $post) {
            $lastmod = $post->getUpdatedAt() ?: $post->getCreatedAt();

            $newUrl = clone $url;
            $newUrl->setLoc($this->seoUrlPlaceholderHandler->generate(
                'frontend.blog.post',
                ['postId' => $post->getId()]
            ));
            $newUrl->setLastmod($lastmod);
            $newUrl->setChangefreq(self::CHANGE_FREQ);
            $newUrl->setResource(BlogEntity::class);
            $newUrl->setIdentifier($post->getId());

            $urls[] = $newUrl;
        }

        if (\count($urls) < $limit) { // last run
            $nextOffset = null;
        } elseif ($offset === null) { // first run
            $nextOffset = $limit;
        } else { // 1+n run
            $nextOffset = $offset + $limit;
        }

        return new UrlResult($urls, $nextOffset);
    }

    private function getPublicBlogPosts(SalesChannelContext $salesChannelContext, int $limit, ?int $offset): BlogCollection
    {
        return $this->helper->getPublicBlogPosts($salesChannelContext, $salesChannelContext->getContext(), $limit, $offset);
    }
}
