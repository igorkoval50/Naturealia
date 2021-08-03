<?php declare(strict_types=1);

namespace Pixup\Wishlist;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class PixupWishlistSW6 extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);
        if ($uninstallContext->keepUserData()) {
            return;
        }
        $connection = $this->container->get(Connection::class);
        $connection->executeStatement('DROP TABLE IF EXISTS `pixup_wish_list_birthday`');
        $connection->executeStatement('DROP TABLE IF EXISTS `pixup_wish_list_subscribers`');
        $connection->executeStatement('DROP TABLE IF EXISTS `pixup_wish_list_products`');
        $connection->executeStatement('DROP TABLE IF EXISTS `pixup_wish_list_sales_channels`');
        $connection->executeStatement('DROP TABLE IF EXISTS `pixup_wish_list`');
        $connection->executeStatement('DROP TABLE IF EXISTS `pixup_wish_list_customers`');
    }
}
