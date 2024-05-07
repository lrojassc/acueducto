<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * @var ValidatorInterface
     */
    protected ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
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

    /**
     * Validar que se cumplan las condiciones para los campos
     *
     * @param array $fields
     *
     * @return array
     */
    public function validateDataForm(array $fields): array
    {
        $count = 0;
        $errors = [];
        foreach ($fields as $field) {
            $errors[$count] = $this->validator->validate($field['field'], $field['constraint']);
            $count ++;
        }

        $message_errors = [];
        if (count($errors) > 0) {
            foreach ($errors as $key => $error) {
                foreach ($error as $item) {
                    $message_errors[$key] = $item->getMessage();
                }
            }
        }
        return $message_errors;
    }
}
