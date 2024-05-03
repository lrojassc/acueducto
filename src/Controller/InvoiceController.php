<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\User;
use App\Form\CreateInvoiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InvoiceController extends AbstractController
{
    #[Route('/create/invoice', name: 'create_invoice')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
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

            $entityManager->persist($invoice);
            $entityManager->flush();
            $this->addFlash('success', 'Factura creada exitosamente');
            return $this->redirectToRoute('create_invoice');
        }
        return $this->render('invoice/create.html.twig', [
                'form_create_invoice' => $form->createView(),
        ]);
    }

    #[Route('/list/invoices', name: 'list_invoices')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        return $this->render('invoice/list.html.twig', [
            'invoices' => $entityManager->getRepository(Invoice::class)->findAll()
        ]);
    }
}
