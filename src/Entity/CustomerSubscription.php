<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'customer_subscriptions')]
#[ORM\UniqueConstraint(name: 'ux_one_subscription_per_customer', columns: ['customer_id'])]
class CustomerSubscription
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Customer $customer;

    #[ORM\ManyToOne(targetEntity: SubscriptionPackage::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private SubscriptionPackage $subscription;

    #[ORM\Column]
    private \DateTimeImmutable $startedAt;

    #[ORM\Column(length: 16)]
    private string $status;

    public function __construct(Customer $customer, SubscriptionPackage $subscription)
    {
        $this->customer = $customer;
        $this->subscription = $subscription;
        $this->startedAt = new \DateTimeImmutable();
        $this->status = 'ACTIVE';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }
    public function setCustomer(Customer $customer): self
    {
        $this->customer = $customer;
        return $this;
    }


    public function getSubscription(): SubscriptionPackage
    {
        return $this->subscription;
    }
    public function setSubscription(SubscriptionPackage $subscription): self
    {
        $this->subscription = $subscription;
        return $this;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }
    public function setStartedAt(\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;
        return $this;
    }


    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
}
