<?php

namespace App\Controller;

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
            $entityManager->persist($invoice);
            $entityManager->flush();
            $this->addFlash('success', 'Invoice created.');
            return $this->redirectToRoute('create_invoice', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('invoice/create.html.twig', [
            'form_create_invoice' => $form->createView(),
        ]);
    }
}
