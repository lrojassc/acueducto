<?php

namespace App\Controller;

use App\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    #[Route('/list/payments', name: 'list_payments')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        return $this->render('payment/list.html.twig', [
            'payments' => $entityManager->getRepository(Payment::class)->findAll()
        ]);
    }
}
