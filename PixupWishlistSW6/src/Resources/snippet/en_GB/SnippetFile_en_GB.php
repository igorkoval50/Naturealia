<?php

namespace Pixup\Wishlist\Resources\snippet\en_GB;

use Shopware\Core\System\Snippet\Files\SnippetFileInterface;

class SnippetFile_en_GB implements SnippetFileInterface
{
    public function getName(): string
    {
        return 'pixupWishlistGeneral.en-GB';
    }

    public function getPath(): string
    {
        return __DIR__ . '/pixupWishlistGeneral.en-GB.json';
    }

    public function getIso(): string
    {
        return 'en-GB';
    }

    public function getAuthor(): string
    {
        return 'Pixup Media GmbH Wish List';
    }

    public function isBase(): bool
    {
        return false;
    }
}
