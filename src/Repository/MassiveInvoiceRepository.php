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

    public function findOneByRegister()
    {
        $massive_invoices = $this->createQueryBuilder('m')
            ->orderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        if (empty($massive_invoices)) {
            return null;
        } else {
            return $massive_invoices[0];
        }
    }

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
