<?php

namespace App\Repository;

use App\Entity\MassiveInvoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MassiveInvoice>
 *
 * @method MassiveInvoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method MassiveInvoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method MassiveInvoice[]    findAll()
 * @method MassiveInvoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MassiveInvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MassiveInvoice::class);
    }

    //    /**
    //     * @return MassiveInvoice[] Returns an array of MassiveInvoice objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?MassiveInvoice
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
