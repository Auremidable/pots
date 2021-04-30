<?php

namespace App\Repository;

use App\Entity\EventModule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventModule|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventModule|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventModule[]    findAll()
 * @method EventModule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventModule::class);
    }

    // /**
    //  * @return EventModule[] Returns an array of EventModule objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EventModule
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
