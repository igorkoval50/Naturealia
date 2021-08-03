<?php

namespace Pixup\Wishlist\Migration;


use Doctrine\DBAL\Connection;

class Migration1547422281Wishlist extends \Shopware\Core\Framework\Migration\MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 154745555;
    }

    public function update(Connection $connection): void
    {

        $connection->executeStatement($this->createWishlistCustomer());
        $connection->executeStatement($this->createWishlist());
        $connection->executeStatement($this->createWishlistProducts());
        $connection->executeStatement($this->createWishlistSalesChannels());
        $connection->executeStatement($this->createWishlistSubscribers());
        $connection->executeStatement($this->createWishlistBirthday());
    }
    private function createWishlistCustomer()
    {
        return <<<SQL
CREATE TABLE IF NOT EXISTS pixup_wish_list_customers (
    id BINARY(16) NOT NULL,
    customer_id BINARY(16) NULL,
    PRIMARY KEY (id),
    created_at DATETIME(3) NOT NULL,
    updated_at DATETIME(3) NULL,
    CONSTRAINT `fk.pixup_wish_list_customer.customer_id` FOREIGN KEY (`customer_id`)
      REFERENCES `customer`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_unicode_ci;
SQL;
    }

    private function createWishlist(){
        return <<<SQL
CREATE TABLE IF NOT EXISTS pixup_wish_list (
    id BINARY(16) NOT NULL,
    customer_id BINARY(16) NOT NULL,
    name VARCHAR(255) NOT NULL,
    private TINYINT(1) NOT NULL DEFAULT 0,
    editable TINYINT(1) NOT NULL DEFAULT 0,
    birthday TINYINT(1) NOT NULL DEFAULT 0,
    password Varchar(255) NULL,
    created_at DATETIME(3) NOT NULL,
    updated_at DATETIME(3) NULL,
    PRIMARY KEY (id,name),
    CONSTRAINT `fk.pixup_wish_list.customer_id` FOREIGN KEY (`customer_id`)
      REFERENCES `pixup_wish_list_customers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_unicode_ci;
SQL;
    }
    private function createWishlistProducts(){
        return <<<SQL
CREATE TABLE IF NOT EXISTS pixup_wish_list_products (
    wishlist_id BINARY(16) NOT NULL,
    product_id BINARY(16) NOT NULL,
    created_at DATETIME(3) NOT NULL,
    updated_at DATETIME(3) NULL,
    PRIMARY KEY (`wishlist_id`, `product_id`),
    CONSTRAINT `fk.pixup_wish_list_products.wishlist_id` FOREIGN KEY (`wishlist_id`)
        REFERENCES `pixup_wish_list`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pixup_wish_list_products.product_id` FOREIGN KEY (`product_id`)
        REFERENCES `product`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_unicode_ci
SQL;
    }
    private function createWishlistSalesChannels(){
        return <<<SQL
CREATE TABLE IF NOT EXISTS pixup_wish_list_sales_channels (
    wishlist_id BINARY(16) NOT NULL,
    sales_channel_id BINARY(16) NOT NULL,
    created_at DATETIME(3) NOT NULL,
    updated_at DATETIME(3) NULL,
    PRIMARY KEY (`wishlist_id`, `sales_channel_id`),
    CONSTRAINT `fk.pixup_wish_list_sales_channels.wishlist_id` FOREIGN KEY (`wishlist_id`)
        REFERENCES `pixup_wish_list`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pixup_wish_list_sales_channels.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
        REFERENCES `sales_channel`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_unicode_ci
SQL;
    }

    private function createWishlistSubscribers(){
        return <<<SQL
CREATE TABLE IF NOT EXISTS pixup_wish_list_subscribers (
    wishlist_id BINARY(16) NOT NULL,
    customer_id BINARY(16) NOT NULL,
    created_at DATETIME(3) NOT NULL,
    updated_at DATETIME(3) NULL,
    PRIMARY KEY (`wishlist_id`, `customer_id`),
    CONSTRAINT `fk.pixup_wish_list_subscribers.wishlist_id` FOREIGN KEY (`wishlist_id`)
        REFERENCES `pixup_wish_list`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pixup_wish_list_subscribers.customer_id` FOREIGN KEY (`customer_id`)
        REFERENCES `pixup_wish_list_customers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_unicode_ci
SQL;
    }
    private function createWishlistBirthday(){
        return <<<SQL
 CREATE TABLE IF NOT EXISTS pixup_wish_list_birthday (
    customer_id BINARY(16) NOT NULL,
    wishlist_id BINARY(16) NOT NULL,
    product_id BINARY(16) NOT NULL,
    PRIMARY KEY (customer_id,wishlist_id,product_id),
    created_at DATETIME(3) NOT NULL,
    updated_at DATETIME(3) NULL,
    CONSTRAINT `fk.pixup_wish_list_birthday.customer_id` FOREIGN KEY (`customer_id`)
      REFERENCES `pixup_wish_list_customers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pixup_wish_list_birthday.wishlist_id` FOREIGN KEY (`wishlist_id`)
      REFERENCES `pixup_wish_list`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.pixup_wish_list_birthday.product_id` FOREIGN KEY (`product_id`)
      REFERENCES `product`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_unicode_ci
SQL;
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
