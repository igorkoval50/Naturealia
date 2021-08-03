<?php declare(strict_types=1);

namespace NetzpBlog6\SeoUrlRoute;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\NestedEvent;

class BlogPostIndexerEvent extends NestedEvent
{
    private $context;
    private $ids;

    public function __construct(array $ids, Context $context)
    {
        $this->context = $context;
        $this->ids = $ids;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getIds(): array
    {
        return $this->ids;
    }
}
