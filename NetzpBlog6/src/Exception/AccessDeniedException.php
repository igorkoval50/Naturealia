<?php declare(strict_types=1);

namespace NetzpBlog6\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class AccessDeniedException extends ShopwareHttpException
{
    public function __construct(string $pageId)
    {
        parent::__construct(
            'Access to page with id "{{ pageId }}" denied.',
            ['pageId' => $pageId]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__CMS_PAGE_DENIED';
    }
}
