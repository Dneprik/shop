<?php

namespace App\Repository;

use App\Entity\SubscriptionPackage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubscriptionPackage>
 */
class SubscriptionPackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionPackage::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.isDeleted = false')
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
