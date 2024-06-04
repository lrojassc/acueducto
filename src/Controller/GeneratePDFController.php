<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Payment;
use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GeneratePDFController extends MainController
{

    /**
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param Security $security
     */
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, Security $security)
    {
        parent::__construct($entityManager, $validator, $security);
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
                $observation = 'Felicitaciones usted se encuentra al día';
                if ($invoices_pending == 1) {
                    $observation = 'Por favor realice el pago de forma inmediata';
                } elseif ($invoices_pending >= 2) {
                    $observation = 'AVISO DE SUSPENSIÓN';
                }

                // Si el usuario tiene facturas pagas adelantadas no se genera recibo
                if ($invoices_pending >= 0) {
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
                        'payment_deadline' => 'Hasta el 05 de ' . $this->monthsNumber[date("m", strtotime("+1 month"))],
                        'subscription_debt' => $subscription_debt,
                        'description_subscription' => $description_subscription
                    ];
                }
            }
        }
        $html = $this->renderView('pdf/massive_invoice.html.twig', ['invoices_print' => array_chunk($invoices_print, 2)]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('legal');
        $dompdf->render();
        $output = $dompdf->output();

        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="FacturasMensuales.pdf"');
        return $response;
    }

    #[Route('/pdf/create/invoice-payment-report/{invoice}', name: 'invoice_payment_report')]
    public function generateInvoicePaymentReport(Invoice $invoice): Response
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        $payment = $this->entityManager->getRepository(Payment::class)->findBy(['invoice' => $invoice->getId()]);
        $total_invoices = 0;
        $payment_date = '';
        foreach ($payment as $value) {
            $total_invoices += $value->getValue();
            $payment_date = $value->getCreatedAt();
        }

        $html = $this->renderView('pdf/invoice_payment_report.html.twig', [
            'invoice' => $invoice,
            'total_invoices' => $total_invoices,
            'payment_date' => $payment_date,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter');
        $dompdf->render();
        $output = $dompdf->output();

        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="Factura"' . $invoice->getId() . '".pdf"');
        return $response;
    }

    #[Route('/pdf/create/payment-report/{payment}', name: 'payment_report')]
    public function generateReportPayment(Payment $payment): Response
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        $html = $this->renderView('pdf/payment_report.html.twig', [
            'payment' => $payment
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter');
        $dompdf->render();
        $output = $dompdf->output();

        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="Pago"' . $payment->getId() . '".pdf"');
        return $response;
    }

    #[Route('/pdf/generate-account-status-by-user/{user}', name: 'account_status_by_user')]
    public function generateAccountStatusByUser(User $user): Response
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $total_invoices = $this->getTotalInvoices($user->getInvoices());

        $html = $this->renderView('pdf/account_status_by_user.html.twig', [
            'user' => $user,
            'total_invoices' => $total_invoices
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter');
        $dompdf->render();
        $output = $dompdf->output();

        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="AccountStatus"' . $user->getName() . '".pdf"');
        return $response;
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

    /**
     * Obtener el total de facturas y sus saldos correspondientes
     *
     * @param $invoices_by_user
     *
     * @return array
     */
    public function getTotalInvoices($invoices_by_user): array
    {
        $total_pending_value = $total_made_payments = 0;

        foreach ($invoices_by_user as $invoice) {
            $pending_invoice_value = (int)str_replace(["$", "."], '', $invoice->getValue());
            $total_pending_value += $pending_invoice_value;

            $payment_made = $this->getTotalPayment($invoice->getPayments());
            $total_made_payments += $payment_made;
        }

        $total_invoices = $total_pending_value + $total_made_payments;
        return [
            'total_pending_value' => $total_pending_value,
            'total_made_payments' => $total_made_payments,
            'total_invoices' => $total_invoices,
        ];
    }

    public function getTotalPayment($payments)
    {
        $total_payment = 0;
        foreach ($payments as $payment) {
            $payment_value = $payment->getValue();
            $total_payment += $payment_value;
        }
        return $total_payment;
    }
}
