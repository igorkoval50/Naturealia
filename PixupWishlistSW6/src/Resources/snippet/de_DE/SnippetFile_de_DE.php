<?php

namespace Pixup\Wishlist\Resources\snippet\de_DE;

use Shopware\Core\System\Snippet\Files\SnippetFileInterface;

class SnippetFile_de_DE implements SnippetFileInterface
{
    public function getName(): string
    {
        return 'pixupWishlistGeneral.de-DE';
    }

    public function getPath(): string
    {
        return __DIR__ .'/pixupWishlistGeneral.de-DE.json';
    }

    public function getIso(): string
    {
        return 'de-DE';
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
