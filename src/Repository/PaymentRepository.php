<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 *
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    //    /**
    //     * @return Payment[] Returns an array of Payment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Payment
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findPaymentsByFields($fields)
    {
        $query = $this->createQueryBuilder('p');
        foreach ($fields as $key => $field) {
            $field_name = $key;
            $field_value = $field;

            switch ($field_name) {
                case 'value':
                    if ($field_value !== NULL) {
                        $query->andWhere('p.value = :value')
                            ->setParameter('value', $field_value);
                    }
                    break;
                case 'month_invoiced':
                    if (!empty($field_value)) {
                        $query->andWhere('p.month_invoiced IN (:month)')
                            ->setParameter('month', $field_value);
                    }
                    break;
                case 'concept':
                    if (!empty($field_value)) {
                        $query->andWhere('p.concept IN (:concept)')
                            ->setParameter('concept', $field_value);
                    }
                    break;
                case 'user':
                    if ($field_value !== NULL) {
                        $id = $field_value->getId();
                        $query->innerJoin('p.invoice', 'i')
                            ->innerJoin('i.user', 'u')
                            ->andWhere('u.id = :id')
                            ->setParameter('id', $id);
                    }
                    break;
                case 'created_at':
                    if ($field_value !== NULL) {
                        $timestamp = $field_value->getTimestamp();
                        $date = date('Y-m-d', $timestamp);
                        $query->andWhere('p.created_at LIKE :date')
                            ->setParameter('date', '%'.$date.'%');
                    }
                    break;
            }
        }
        return $query->getQuery()->getResult();
    }
}
