<?php declare(strict_types=1);
namespace Pixup\Wishlist\Entitys\Model;

use Pixup\Wishlist\Entitys\Aggregate\WishlistCustomer\Collection\WishlistCustomerCollection;
use Pixup\Wishlist\Entitys\Aggregate\WishlistCustomer\Model\WishlistCustomerModel;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;

class WishlistModel extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ProductCollection|null
     */
    protected $products;

    /**
     * @var WishlistCustomerModel
     */
    protected $customer;

    /**
     * @var SalesChannelCollection|null
     */
    protected $salesChannels;

    /**
     * @var WishlistCustomerCollection|null
     */
    protected $subscribers;

    /**
     * @var bool
     */
    protected $private;

    /**
     * @var bool
     */
    protected $editable;

    /**
     * @var bool
     */
    protected $birthday;

    /**
     * @var string|null
     */
    protected $password;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return ProductCollection|null
     */
    public function getProducts(): ?ProductCollection
    {
        return $this->products;
    }

    /**
     * @param ProductCollection|null $products
     */
    public function setProducts(?ProductCollection $products): void
    {
        $this->products = $products;
    }

    /**
     * @return WishlistCustomerModel
     */
    public function getCustomer(): WishlistCustomerModel
    {
        return $this->customer;
    }

    /**
     * @param WishlistCustomerModel $customer
     */
    public function setCustomer(WishlistCustomerModel $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return SalesChannelCollection|null
     */
    public function getSalesChannels(): ?SalesChannelCollection
    {
        return $this->salesChannels;
    }

    /**
     * @param SalesChannelCollection|null $salesChannels
     */
    public function setSalesChannels(?SalesChannelCollection $salesChannels): void
    {
        $this->salesChannels = $salesChannels;
    }

    /**
     * @return WishlistCustomerCollection|null
     */
    public function getSubscribers(): ?WishlistCustomerCollection
    {
        return $this->subscribers;
    }

    /**
     * @param WishlistCustomerCollection|null $subscribers
     */
    public function setSubscribers(?WishlistCustomerCollection $subscribers): void
    {
        $this->subscribers = $subscribers;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param bool $private
     */
    public function setPrivate(bool $private): void
    {
        $this->private = $private;
    }

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->editable;
    }

    /**
     * @param bool $editable
     */
    public function setEditable(bool $editable): void
    {
        $this->editable = $editable;
    }

    /**
     * @return bool
     */
    public function isBirthday(): bool
    {
        return $this->birthday;
    }

    /**
     * @param bool $birthday
     */
    public function setBirthday(bool $birthday): void
    {
        $this->birthday = $birthday;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }


}
