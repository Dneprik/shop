<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Order $order;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $priceAtPurchase;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'RESTRICT')]
    private ?Article $article = null;

    #[ORM\ManyToOne(targetEntity: SubscriptionPackage::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'RESTRICT')]
    private ?SubscriptionPackage $subscription = null;

    public function __construct(Order $order, string $priceAtPurchase)
    {
        $this->order = $order;
        $this->priceAtPurchase = $priceAtPurchase;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
    public function setOrder(?Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getPriceAtPurchase(): string
    {
        return $this->priceAtPurchase;
    }
    public function setPriceAtPurchase(string $priceAtPurchase): self
    {
        $this->priceAtPurchase = $priceAtPurchase;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }
    public function setArticle(?Article $article): self
    {
        $this->article = $article;
        if ($article !== null) { $this->subscription = null; }
        return $this;
    }

    public function getSubscription(): ?SubscriptionPackage
    {
        return $this->subscription;
    }
    public function setSubscription(?SubscriptionPackage $subscription): self
    {
        $this->subscription = $subscription;
        if ($subscription !== null) { $this->article = null; }
        return $this;
    }


    public function isValidXor(): bool
    {
        return ($this->article !== null) xor ($this->subscription !== null);
    }
}
