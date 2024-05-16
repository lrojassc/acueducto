<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GeneratePDFController extends MainController
{

    /**
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        parent::__construct($entityManager, $validator);
    }

    #[Route('/pdf/create/massive-invoices', name: 'create_massive_invoices')]
    public function generateMassiveInvoices(): Response
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        $users = $this->entityManager->getRepository(User::class)->findUserByStatus('ACTIVO');
        $invoices_print = [];
        foreach ($users as $user) {
            $services_by_user = $this->entityManager->getRepository(Subscription::class)->findServicesActiveByUser($user->getId());

            $month_invoiced = '';
            foreach ($services_by_user as $service) {
                $count_invoice_active = $total_amount_invoices = $value_last_invoice = 0;
                $description_last_invoice = $id_last_invoice = $description_subscription = '';
                $subscription_debt = FALSE;
                $id_pending_invoices = 'No. ';

                $invoices_by_subscription = $this->entityManager->getRepository(Subscription::class)->find($service->getId())->getInvoices();
                foreach ($invoices_by_subscription as $invoice) {
                    // Check if the user is owing the subscription
                    if ($invoice->getConcept() === 'SUSCRIPCION' && $invoice->getStatus() !== 'PAGADA') {
                        $subscription_debt = TRUE;
                        $description_subscription = 'Valor pendiente $' . number_format(num: $invoice->getValue(), thousands_separator: '.');
                    }

                    if ($invoice->getStatus() !== 'PAGADA' && $invoice->getStatus() !== 'INACTIVO') {
                        $count_invoice_active++;
                        $total_amount_invoices += $invoice->getValue();
                        $value_last_invoice = $invoice->getValue();
                        $description_last_invoice = $invoice->getDescription();
                        $month_invoiced = $invoice->getMonthInvoiced();
                        $id_pending_invoices .= $invoice->getId() . ' - ';
                        $id_last_invoice = $invoice->getId();
                    }
                }
                $invoice_pending = rtrim($id_pending_invoices, '- ');
                $invoices_pending = $count_invoice_active - 1;
                $observation = $invoices_pending >= 1 ? 'Por favor realice el pago de forma inmediata'
                    : 'Felicitaciones usted se encuentra al día';

                $invoices_print[] = [
                    'user' => $user->getName(),
                    'address' => $user->getAddress() . ' - ' . $user->getCity(),
                    'user_code' => $user->getId(),
                    'service' => $service->getService(),
                    'total_amount_invoices' => $total_amount_invoices,
                    'arrears' => $invoices_pending,
                    'value_last_invoice' => $value_last_invoice,
                    'description_last_invoice' => $description_last_invoice,
                    'observation' => $observation,
                    'period' => 'Del 01 al 30 de ' . $month_invoiced,
                    'invoice_pending' => $invoice_pending,
                    'id_last_invoice' => $id_last_invoice,
                    'payment_deadline' => 'Hasta el 25 de ' . $this->monthsNumber[date("m", strtotime("+1 month"))],
                    'subscription_debt' => $subscription_debt,
                    'description_subscription' => $description_subscription
                ];
            }
        }
        $html = $this->renderView('pdf/massive_invoice.html.twig', ['invoices_print' => array_chunk($invoices_print, 2)]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('legal');
        $dompdf->render();

        return new Response($dompdf->output(), 200, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Generar reporte de pagos por filtro aplicado desde formulario
     *
     * @param array $payments
     *
     * @return Response
     */
    public function generatePaymentReport(array $payments): Response
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $html = $this->renderView('pdf/filter_payments_report.html.twig', ['payments' => $payments]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter');
        $dompdf->render();
        return new Response($dompdf->output(), 200, ['Content-Type' => 'application/pdf']);
    }
}