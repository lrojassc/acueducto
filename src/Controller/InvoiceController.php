<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\User;
use App\Form\CreateInvoiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InvoiceController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/create/invoice', name: 'create_invoice')]
    public function create(Request $request): Response
    {
        $form = $this->createForm(CreateInvoiceType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $invoice = $form->getData();
            $invoice->setYearInvoiced(date('Y'));
            $invoice->setStatus('PENDIENTE');
            $invoice->setCreatedAt(new \DateTime('now'));
            $invoice->setUpdatedAt(new \DateTime('now'));

            $invoice->setUser($invoice->getUser());
            $invoice->setSubscription($invoice->getSubscription());

            $this->entityManager->persist($invoice);
            $this->entityManager->flush();
            $this->addFlash('success', 'Factura creada exitosamente');
            return $this->redirectToRoute('create_invoice');
        }
        return $this->render('invoice/create.html.twig', [
                'form_create_invoice' => $form->createView(),
        ]);
    }

    #[Route('/list/invoices', name: 'list_invoices')]
    public function list(): Response
    {
        return $this->render('invoice/list.html.twig', [
            'invoices' => $this->entityManager->getRepository(Invoice::class)->findByActiveInvoices()
        ]);
    }

    #[Route('/invoice/{invoice}', name: 'invoice_show')]
    public function show(Invoice $invoice): Response {
        return $this->render('invoice/show.html.twig', [
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
}
