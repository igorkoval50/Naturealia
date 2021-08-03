<?php declare(strict_types=1);

namespace NetzpBlog6\SeoUrlRoute;

use Shopware\Core\Content\Seo\SeoUrlUpdater;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BlogPostUpdater implements EventSubscriberInterface
{
    private $seoUrlUpdater;

    public function __construct(SeoUrlUpdater $seoUrlUpdater)
    {
        $this->seoUrlUpdater = $seoUrlUpdater;
    }

    public static function getSubscribedEvents()
    {
        return [
            's_plugin_netzp_blog.written' => 'blogPostWritten',
            BlogPostIndexerEvent::class   => 'handleIndexerEvent'
        ];
    }

    public function blogPostWritten(EntityWrittenEvent $event)
    {
        $this->seoUrlUpdater->update(BlogPostSeoUrlRoute::ROUTE_NAME, $event->getIds());
    }

    public function handleIndexerEvent(BlogPostIndexerEvent $event)
    {
        $this->seoUrlUpdater->update(BlogPostSeoUrlRoute::ROUTE_NAME, $event->getIds());
    }
}
