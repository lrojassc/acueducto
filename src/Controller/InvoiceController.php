<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\MassiveInvoice;
use App\Entity\Payment;
use App\Entity\Subscription;
use App\Entity\User;
use App\Form\CreateAdvanceInvoicesType;
use App\Form\CreateInvoiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InvoiceController extends MainController
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

    #[Route('/create/invoice', name: 'create_invoice')]
    public function create(Request $request): Response
    {
        $form = $this->createForm(CreateInvoiceType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user_id = $request->request->get('userInvoice');
            $id_subscription = $request->request->get('serviceUser');
            $invoice = $form->getData();
            $invoice->setYearInvoiced(date('Y'));
            $invoice->setStatus('PENDIENTE');
            $invoice->setCreatedAt(new \DateTime('now'));
            $invoice->setUpdatedAt(new \DateTime('now'));

            $invoice->setUser($this->entityManager->getRepository(User::class)->find($user_id));
            $invoice->setSubscription($this->entityManager->getRepository(Subscription::class)->find($id_subscription));

            $this->entityManager->persist($invoice);
            $this->entityManager->flush();
            $this->addFlash('success', 'Factura creada exitosamente');
            return $this->redirectToRoute('create_invoice');
        }
        return $this->render('invoice/create.html.twig', [
                'form_create_invoice' => $form->createView(),
        ]);
    }

    #[Route('/create/advance-invoices', name: 'create_advance_invoices')]
    public function createAdvanceInvoices(Request $request): Response
    {
        $form = $this->createForm(CreateAdvanceInvoicesType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user_id = $request->request->get('userInvoice');
            $user = $this->entityManager->getRepository(User::class)->find($user_id);
            $data_invoice = $form->getData();
            $value_payment_invoice = $data_invoice->getValue();

            $is_value_payment = !($value_payment_invoice < 5000);
            $month_invoiced = $request->request->all('month_invoiced_payment');

            $validations = [
                'monthInvoiced' => [
                    'field' => $month_invoiced,
                    'constraint' => [
                        new NotBlank(message: 'Debe seleccionar por lo menos un mes')]
                ],
                'valuePayment' => [
                    'field' => $is_value_payment,
                    'constraint' => [
                        new IsTrue(message: 'El valor de las facturas debe ser mayor o igual a $5.000')]
                ]
            ];
            $errors = $this->validateDataForm($validations);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('invoice/create_advance_invoices.html.twig', [
                    'form_create_advance_invoice' => $form->createView(),
                ]);
            } else {
                $subscription_user_id = $request->request->get('serviceUser');
                $paid_month = '';
                foreach ($month_invoiced as $month) {
                    $invoice = new Invoice();
                    $invoice->setUser($user);
                    $invoice->setValue(0);
                    $invoice->setDescription($data_invoice->getDescription());
                    $invoice->setConcept($data_invoice->getConcept());
                    $invoice->setYearInvoiced(date('Y'));
                    $invoice->setStatus('PAGADA');
                    $invoice->setCreatedAt(new \DateTime('now'));
                    $invoice->setUpdatedAt(new \DateTime('now'));
                    $invoice->setSubscription($this->entityManager->getRepository(Subscription::class)->find($subscription_user_id));
                    $invoice->setMonthInvoiced($month);

                    $payment = new Payment();
                    $payment->setInvoice($invoice);
                    $payment->setValue($value_payment_invoice);
                    $payment->setDescription('Pago factura mes de ' . $month . ' adelantado');
                    $payment->setMethod('EFECTIVO');
                    $payment->setMonthInvoiced($month);
                    $payment->setCreatedAt(new \DateTime('now'));
                    $payment->setUpdatedAt(new \DateTime('now'));
                    $this->entityManager->persist($payment);

                    $invoice->addPayment($payment);
                    $this->entityManager->persist($invoice);
                    $this->entityManager->flush();

                    $paid_month .= $month . ' - ';
                }
                $message_type = 'success';
                $message = 'Se acaban de generar y pagar las facturas de ' . rtrim($paid_month, '- ') . ' Para el usuario ' . $user->getName();
                $this->addFlash($message_type, $message);
                return $this->redirectToRoute('create_advance_invoices');
            }
        }
        return $this->render('invoice/create_advance_invoices.html.twig', [
            'form_create_advance_invoice' => $form->createView(),
        ]);
    }

    #[Route('/list/invoices', name: 'list_invoices')]
    public function list(): Response
    {
        $user = $this->security->getUser();
        $roles = $user->getRoles();

        // Comprobar el rol de usuario para mostrar listado de facturas
        if ($roles[0] == 'ROLE_ADMIN') {
            $invoices = $this->entityManager->getRepository(Invoice::class)->findByActiveInvoices();
        } else {
            $invoices = $this->entityManager->getRepository(Invoice::class)->findInvoicesActivesByUser($user->getId());
        }

        $config = $this->getConfig();
        $number_items = $config['number_items'];

        return $this->render('invoice/list.html.twig', [
            'invoices' => $invoices,
            'number_items' => $number_items
        ]);
    }

    #[Route('/invoice/{invoice}', name: 'invoice_show')]
    public function show(Invoice $invoice): Response {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Acceso Denegado');
        return $this->render('invoice/show.html.twig', [
            'edit' => FALSE,
            'invoice' => $invoice,
        ]);
    }

    #[Route('/invoice/delete/', name: 'delete_invoice', methods: 'POST')]
    public function delete(Request $request)
    {
        $data = json_decode($request->getContent());
        $invoice = $this->entityManager->getRepository(Invoice::class)->find($data->invoice);
        $invoice->setStatus('INACTIVO');
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        $response = [
            'message' => 'Factura Eliminada con Exito',
        ];

        return new JsonResponse($response);
    }

    #[Route('/invoice/edit/{invoice}', name: 'edit_invoice')]
    public function edit(Invoice $invoice, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('invoice/show.html.twig', [
            'edit' => TRUE,
            'invoice' => $invoice,
        ]);
    }

    #[Route('/invoice/update/{invoice}', name: 'update_invoice')]
    public function update(Invoice $invoice, Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $value_invoice = str_replace(["$", "."], '', $request->request->get('valueInvoice'));
        $invoice->setValue($value_invoice);
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
        $this->addFlash('success', 'Factura actualiza con exito');
        return $this->redirectToRoute('list_invoices');
    }

    #[Route('/invoice/massive/invoices', name: 'massive_invoices')]
    public function massive()
    {
        $config = $this->getConfig();
        $current_month = $config['bulk_billing_month'];
        $message_type = 'error';
        $message = 'No se pueden volver a generar el masivo de facturas del mes de ' . $current_month;

        $massive_invoice = new MassiveInvoice();
        $users = $this->entityManager->getRepository(User::class)->findUserByStatus('ACTIVO');
        $last_massive_invoice = $this->entityManager->getRepository(MassiveInvoice::class)->findOneByRegister();
        $last_month_massive_invoice = $last_massive_invoice?->getMonth();

        $users_without_invoices = $this->entityManager->getRepository(Invoice::class)->findUserWithoutInvoices($users, $current_month);
        if ($last_month_massive_invoice === NULL || $last_month_massive_invoice != $current_month) {
            foreach ($users_without_invoices as $user) {
                $services_by_user = $user->getSubscriptions();
                foreach ($services_by_user as $service) {
                    if ($service->getStatus() === 'ACTIVO') {
                        $invoice = new Invoice();
                        $invoice->setValue($service->isFullPayment() ? $config['monthly_invoice_value'] : ($config['monthly_invoice_value'] / 2));
                        $invoice->setDescription('Servicio acueducto '. $service->getService());
                        $invoice->setYearInvoiced(date('Y'));
                        $invoice->setMonthInvoiced($current_month);
                        $invoice->setConcept('MENSUALIDAD');
                        $invoice->setStatus('PENDIENTE');
                        $invoice->setUser($user);
                        $invoice->setSubscription($service);
                        $invoice->setCreatedAt(new \DateTime('now'));
                        $invoice->setUpdatedAt(new \DateTime('now'));
                        $this->entityManager->persist($invoice);
                        $this->entityManager->flush();
                    }
                }
            }

            $massive_invoice->setYear(date('Y'));
            $massive_invoice->setMonth($current_month);
            $massive_invoice->setStatus('GENERADO');
            $massive_invoice->setCreatedAt(new \DateTime('now'));
            $massive_invoice->setUpdatedAt(new \DateTime('now'));
            $this->entityManager->persist($massive_invoice);
            $this->entityManager->flush();

            $message_type = 'success';
            $message = 'Se acaba de generar el masivo de facturas del mes de ' . $current_month;
        }

        $this->addFlash($message_type, $message);
        return $this->redirectToRoute('list_invoices');
    }
}
