<?php declare(strict_types=1);

namespace Pixup\Wishlist\ScheduledTask;

use Pixup\Wishlist\Core\Boot;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class clearTableHandler extends ScheduledTaskHandler
{
    /**
     * @var Boot
     */
    private $boot;
    /**
     * @var array|null
     */
    private $config;
    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        Boot $boot
    )
    {
        parent::__construct($scheduledTaskRepository);
        $this->boot = $boot;
        $this->config = $this->boot->getConfig();
    }

    public static function getHandledMessages(): iterable
    {
        return [ clearTable::class ];
    }

    public function run(): void
    {
        echo "cleaning old wishlist cookie customer";
        $deleteAfterDays = $this->config["wishlistDeleteCookie"];
        $wishlistEntityHandler = $this->boot->getFacade()->getWishlistEntityHandler();
        $wishlistEntityHandler->deleteCookieCustomerByExpiredDays((int) $deleteAfterDays);
        echo "removed old wishlist users and thair wishlists";
    }
}
