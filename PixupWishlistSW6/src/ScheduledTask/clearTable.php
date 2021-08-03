<?php declare(strict_types=1);

namespace Pixup\Wishlist\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class clearTable extends ScheduledTask
{

    public static function getTaskName(): string
    {
        return 'pixup.clear_table';
    }

    public static function getDefaultInterval(): int
    {
        return 86400; // 1 Day
    }
}
