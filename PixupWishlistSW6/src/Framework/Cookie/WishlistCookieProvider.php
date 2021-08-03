<?php
namespace Pixup\Wishlist\Framework\Cookie;

use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class WishlistCookieProvider implements CookieProviderInterface
{
    private $originalService;

    private const Cookies = [
        'isRequired' => false,
        'snippet_name' => 'pixup-wishlist.cookie.name',
        'snippet_description' => 'pixup-wishlist.cookie.description',
        'entries' => [
            [
                'snippet_name' => 'pixup-wishlist.cookie.name',
                'cookie' => 'pixupWishlist',
                'expiration' => '30'
            ],
            [
                'snippet_name' => 'pixup-wishlist.cookie.accepted',
                'cookie' => 'pixupWishlistAccepted',
                'value' => '1',
                'expiration' => '30',
                'hidden' => true,
            ],
        ],
    ];

    public function __construct(CookieProviderInterface $service)
    {
        $this->originalService = $service;
    }

    public function getCookieGroups(): array
    {
        $array = [];
        foreach($this->originalService->getCookieGroups() as $key=>$value){
            if($value['snippet_name'] == "cookie.groupRequired") {
                $array[$key] = $value;
                //$array[$key]['entries'] = array_merge($value['entries'], self::Cookies['entries']);
            }else
                $array[$key]=$value;
        }
        $array[$key+1] =  self::Cookies;
        return $array;
    }
}
