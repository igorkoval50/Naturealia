<?php

namespace Pixup\Wishlist\Entitys\Aggregate\WishlistCustomer\Model;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class WishlistCustomerModel extends Entity
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var CustomerEntity|null
     */
    protected $customer;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return CustomerEntity|null
     */
    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    /**
     * @param CustomerEntity|null $customer
     */
    public function setCustomer(?CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

}
