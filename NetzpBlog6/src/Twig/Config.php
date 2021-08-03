<?php declare(strict_types=1);

namespace NetzpBlog6\Twig;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Config extends AbstractExtension
{
    protected $container;
    private $config;

    public function __construct(ContainerInterface $container, SystemConfigService $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    public function getFunctions(): array
    {
        // don't register if on sw 6.4
        if (version_compare($this->container->getParameter('kernel.shopware_version'), '6.4.0', '>=')) {
            return [];
        }

        // backwards compatibility < sw 6.4; twig function does not exists there ;-(
        return [
            new TwigFunction('config', [$this, 'config'], ['needs_context' => true])
        ];
    }

    public function config(array $context, string $key)
    {
        return $this->config->get($key, $this->getSalesChannelId($context));
    }

    private function getSalesChannelId(array $context): ?string
    {
        if (!isset($context['context'])) {
            return null;
        }

        $context = $context['context'];

        if (!$context instanceof SalesChannelContext) {
            return null;
        }

        return $context->getSalesChannel()->getId();
    }
}
