<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'customer_articles')]
#[ORM\UniqueConstraint(name: 'ux_customer_article', columns: ['customer_id', 'article_id'])]
class CustomerArticle
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Customer $customer;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Article $article;

    #[ORM\Column]
    private \DateTimeImmutable $purchasedAt;

    public function __construct(Customer $customer, Article $article)
    {
        $this->customer = $customer;
        $this->article = $article;
        $this->purchasedAt = new \DateTimeImmutable();
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

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): self
    {
        $this->article = $article;
        return $this;
    }

    public function getPurchasedAt(): \DateTimeImmutable
    {
        return $this->purchasedAt;
    }

    public function setPurchasedAt(\DateTimeImmutable $purchasedAt): self
    {
        $this->purchasedAt = $purchasedAt;
        return $this;
    }
}
