<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Entity\Invoice;
use App\Entity\Payment;
use App\Form\ReportPaymentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AllowDynamicProperties] class PaymentController extends MainController
{

    /**
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param GeneratePDFController $generatePdfController
     */
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, GeneratePDFController $generatePdfController)
    {
        $this->generatePdfController = $generatePdfController;
        parent::__construct($entityManager, $validator);
    }

    #[Route('/list/payments', name: 'list_payments')]
    public function list(): Response
    {
        return $this->render('payment/list.html.twig', [
            'payments' => $this->entityManager->getRepository(Payment::class)->findAll()
        ]);
    }

    #[Route('/add/payment/{invoice}', name: 'add_payment', methods: ['POST'])]
    public function payment(Request $request, Invoice $invoice): Response {
        $submittedToken = $request->getPayload()->get('token_payment');
        $message = 'Error en envio del formulario';

        $payment_value = $request->request->get('paymentValue');
        $payment_description = $request->request->get('paymentDescription');
        $validations = [
            'paymentValue' => [
                'field' => $payment_value,
                'constraint' => [new NotBlank(message: 'El campo Valor a Pagar no puede estar vacío')]
            ],
            'paymentDescription' => [
                'field' => $payment_description,
                'constraint' => [new NotBlank(message: 'El campo Descripción del Pago no puede estar vacío')]
            ]
        ];

        $errors = $this->validateDataForm($validations);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }
            return $this->redirectToRoute('invoice_show', ['invoice' => $invoice->getId()]);
        } else {
            if ($this->isCsrfTokenValid('payment-invoice', $submittedToken)) {
                $invoice_value = $invoice->getValue();
                $payment_value = (int) $payment_value;
                $credit_invoice = $invoice_value - $payment_value;

                $message = 'El valor del pago no puede ser mayor al de la factura';
                if ($payment_value <= $invoice_value) {

                    if ($credit_invoice < $invoice_value) {
                        $status_invoice = $credit_invoice === 0 ? 'PAGADA' : 'PAGO PARCIAL';
                        $invoice->setValue($credit_invoice);
                        $invoice->setStatus($status_invoice);

                        $payment = new Payment();
                        $payment->setValue($payment_value);
                        $payment->setDescription($payment_description);
                        $payment->setMethod('EFECTIVO');
                        $payment->setMonthInvoiced($invoice->getMonthInvoiced());
                        $payment->setInvoice($invoice);
                        $payment->setCreatedAt(new \DateTime('now'));
                        $payment->setUpdatedAt(new \DateTime('now'));

                        $invoice->addPayment($payment);

                        $this->entityManager->persist($payment);
                        $this->entityManager->persist($invoice);
                        $this->entityManager->flush();
                        $message = 'Pago realizado de forma exitosa';

                        // Si la factura es de tipo suscripcion y se paga completa, debe cambiar el estado de si debe o no el usuario la suscripcion
                        if ($invoice->getConcept() === 'SUSCRIPCION') {
                            $message = 'El usuario realizó un abono al pago de suscripción';

                            if ($status_invoice === 'PAGADA') {
                                $user = $invoice->getUser();
                                $user->setPaidSubscription($status_invoice);
                                $this->entityManager->persist($user);
                                $this->entityManager->flush();

                                $message = 'El usuario pago completamente su suscripción';
                            }
                        }
                    }
                    $this->addFlash('success', $message);
                    return $this->redirectToRoute('list_payments');
                } else {
                    $this->addFlash('error', $message);
                    return $this->redirectToRoute('invoice_show', ['invoice' => $invoice->getId()]);
                }
            } else {
                $this->addFlash('error', $message);
                return $this->redirectToRoute('invoice_show', ['invoice' => $invoice->getId()]);
            }
        }
    }

    #[Route('/payment/generate/report', name: 'report')]
    public function report(Request $request): Response
    {
        $form = $this->createForm(ReportPaymentType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $name_clicked_button = $form->getClickedButton()->getConfig()->getName();
            $report = $form->getData();

            $value = $report['value'];
            $month_invoiced = $report['month_invoiced'];
            $user = $report['user'];
            $concept = $report['concept'];
            $created_at = $report['created_at'];

            $fields = [
                'value' => $value,
                'month_invoiced' => $month_invoiced,
                'user' => $user,
                'concept' => $concept,
                'created_at' => $created_at
            ];
            if ($value === NULL && empty($month_invoiced) && $user === NULL && empty($concept) && $created_at === NULL) {
                $this->addFlash('error', 'Debe seleccionar por lo menos un campo para generar el reporte');
                return $this->redirectToRoute('report');
            } else {
                // Generar reporte en PDF
                if ($name_clicked_button === 'send_pdf') {
                    $payments = $this->entityManager->getRepository(Payment::class)->findPaymentsByFields($fields);
                    if (!empty($payments)) {
                        return $this->generatePdfController->generatePaymentReport($payments);
                    } else {
                        $this->addFlash('error', 'No hay reporte PDF para esta consulta.');
                        return $this->redirectToRoute('report');
                    }
                // Generar reporte en excel
                } else {
                    $payments = $this->entityManager->getRepository(Payment::class)->findPaymentsByFields($fields);
                    if (!empty($payments)) {
                        return $this->generatePdfController->generatePaymentReport($payments);
                    } else {
                        $this->addFlash('error', 'No hay reporte Excel para esta consulta.');
                        return $this->redirectToRoute('report');
                    }
                }
            }
        }
        return $this->render('payment/report.html.twig', [
            'form_report' => $form->createView()
        ]);
    }

}
