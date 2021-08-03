<?php declare(strict_types=1);

namespace NetzpBlog6\Core\Content\Category;

use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class CategoryEntity extends Entity
{
    use EntityIdTrait;

    protected $cmspageid;
    protected $onlyloggedin;
    protected $includeinrss;

    /**
     * @var SalesChannelEntity|null
     */
    protected $saleschannel;
    protected $saleschannelid;

    /**
     * @var CustomerGroupEntity|null
     */
    protected $customergroup;
    protected $customergroupid;

    /**
     * @var array|null
     */
    protected $customFields;

    public function getCmspageid(): ?string { return $this->cmspageid; }
    public function setCmspageid(?string $value): void { $this->cmspageid = $value; }

    public function getSaleschannelid(): ?string { return $this->saleschannelid; }
    public function setSaleschannelid(?string $value): void { $this->saleschannelid = $value; }

    public function getCustomergroupid(): ?string { return $this->customergroupid; }
    public function setCustomergroupid(?string $value): void { $this->customergroupid = $value; }

    public function getOnlyloggedin(): ?bool { return $this->onlyloggedin; }
    public function setOnlyloggedin(?bool $value): void { $this->onlyloggedin = $value; }

    public function getIncludeinrss(): ?bool { return $this->includeinrss; }
    public function setIncludeinrss(?bool $value): void { $this->includeinrss = $value; }

    public function getCustomFields(): ?array { return $this->customFields; }
    public function setCustomFields(?array $customFields): void { $this->customFields = $customFields; }

    public function getSalesChannel(): ?SalesChannelEntity { return $this->saleschannel; }
    public function setSalesChannel(SalesChannelEntity $value): void { $this->saleschannel = $value; }

    public function getCustomerGroup(): ?CustomerGroupEntity { return $this->customergroup; }
    public function setCustomerGroup(CustomerGroupEntity $value): void { $this->customergroup = $value; }
}
