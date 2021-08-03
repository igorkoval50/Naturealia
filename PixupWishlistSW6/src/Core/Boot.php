<?php declare(strict_types=1);

namespace Pixup\Wishlist\Core;

use Psr\Container\ContainerInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class Boot
{
    private $serviceContainer;
    private $config;

    public function __construct(ContainerInterface $container,SystemConfigService $systemConfigService)
    {
        $this->config = $systemConfigService->get("PixupWishlistSW6")['config'];
        $this->serviceContainer = $container;
    }

    public function getFacade(){
        return new Facade($this->serviceContainer);
    }

    protected function getFactory(){
        return new Factory($this->serviceContainer);
    }

    public function getConfig(){
        return $this->config;
    }
}
