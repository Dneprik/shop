<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\OrderItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }


    public function existsByArticle(Article $article): bool
    {
        return (bool) $this->createQueryBuilder('oi')
        ->select('1')
        ->andWhere('oi.article = :article')
        ->setParameter('article', $article)
        ->setMaxResults(1)
        ->getQuery()
            ->getOneOrNullResult();
    }
}
