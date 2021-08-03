<?php declare(strict_types=1);

namespace NetzpBlog6\SeoUrlRoute;

use NetzpBlog6\Core\Content\Blog\BlogDefinition;
use Shopware\Core\Framework\Adapter\Cache\CacheClearer;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlogPostIndexer extends EntityIndexer
{
    protected $container;
    private $iteratorFactory;
    private $repository;
    private $cacheClearer;
    private $blogPostUpdate;
    private $eventDispatcher;

    public function __construct(
        ContainerInterface $container,
        IteratorFactory $iteratorFactory,
        EntityRepositoryInterface $repository,
        CacheClearer $cacheClearer,
        BlogPostUpdater $blogPostUpdater,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->container = $container;
        $this->iteratorFactory = $iteratorFactory;
        $this->repository = $repository;
        $this->cacheClearer = $cacheClearer;
        $this->blogPostUpdate = $blogPostUpdater;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getName(): string
    {
        return 'netzp.blogpost.indexer';
    }

    public function iterate($offset): ?EntityIndexingMessage
    {
        $iterator = $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);
        $ids = $iterator->fetch();
        if (empty($ids)) {
            return null;
        }

        return new BlogPostIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $updates = $event->getPrimaryKeys(BlogDefinition::ENTITY_NAME);
        if (empty($updates)) {
            return null;
        }

        return new BlogPostIndexingMessage(array_values($updates), null, $event->getContext());
    }

    public function handle(EntityIndexingMessage $message): void
    {
        $ids = $message->getData();
        $ids = array_unique(array_filter($ids));

        if (empty($ids)) {
            return;
        }

        $this->eventDispatcher->dispatch(new BlogPostIndexerEvent($ids, $message->getContext()));

        // deprecated sw 6.4
        if (version_compare($this->container->getParameter('kernel.shopware_version'), '6.4.0', '<')) {
            $this->cacheClearer->invalidateIds($ids, BlogDefinition::ENTITY_NAME);
        }
    }
}
