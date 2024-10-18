<?php

namespace App\Repository;

use App\Entity\Shoepair;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Shoepair>
 */
class ShoepairRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shoepair::class);
    }

    //    /**
    //     * @return Shoepair[] Returns an array of Shoepair objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findShoeByUser(int $userOwnerId): ?Shoepair
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.user = :userOwnerId')
    //            ->setParameter('userOwnerId', $userOwnerId)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }


    //    public function findOneBySomeField($value): ?Shoepair
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
