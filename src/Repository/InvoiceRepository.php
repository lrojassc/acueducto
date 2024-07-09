<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 *
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    //    public function findOneBySomeField($value): ?Invoice
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    public function findByActiveInvoices(): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.status != :status')
            ->setParameter('status', 'INACTIVO')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Obtener solo facturas activas
     *
     * @param $user_id
     *
     * @return mixed
     */
    public function findInvoicesActivesByUser($user_id): mixed
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.user = :user_id')
            ->setParameter('user_id', $user_id)
            ->andWhere('i.status != :status')
            ->setParameter('status', 'INACTIVO')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Get user without invoices
     *
     * @param $users
     * @param $current_month
     *
     * @return array
     */
    public function findUserWithoutInvoices($users, $current_month): array
    {
        $users_without_invoices = [];
        foreach ($users as $user) {
            $subscription = $user->getSubscriptions();
            foreach ($subscription as $service) {
                $invoices_by_service = $service->getInvoices();
                $months_billed = [];
                foreach ($invoices_by_service as $invoice) {
                    $month_invoiced = $invoice->getMonthInvoiced();
                    $months_billed[$month_invoiced] = $invoice;
                }
                if (array_key_exists($current_month, $months_billed)) {
                    $user = $user->removeSubscription($service);
                }
            }
            $users_without_invoices[] = $user;
        }
        return $users_without_invoices;
    }
}
