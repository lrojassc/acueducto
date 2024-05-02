<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Subscription;
use App\Entity\User;
use App\Form\CreateUserType;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\Adapter\ArrayAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/create/user', name: 'create_user')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CreateUserType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPaidSubscription('DEBE');
            $user->setFullPayment('SI');
            $user->setPassword($user->getDocumentNumber());
            $user->setCreatedAt(new \DateTime('now'));
            $user->setUpdatedAt(new \DateTime('now'));

            // Crear servicio para el nuevo usuario
            $subscription = new Subscription();
            $subscription->setUser($user);
            $subscription->setService('Residencial 1');
            $subscription->setStatus('ACTIVO');
            $subscription->setCreatedAt(new \DateTime('now'));
            $subscription->setUpdatedAt(new \DateTime('now'));

            // Crear factura asociada a la adquisicion del servicio
            $invoice = new Invoice();
            $invoice->setUser($user);
            $invoice->setValue(700000);
            $invoice->setDescription('Suscripción al servicio de acueducto');
            $invoice->setYearInvoiced(date('Y'));
            $invoice->setMonthInvoiced(date('m'));
            $invoice->setConcept('SUSCRIPCION');
            $invoice->setStatus('PENDIENTE');
            $invoice->setSubscription($subscription);
            $invoice->setCreatedAt(new \DateTime('now'));
            $invoice->setUpdatedAt(new \DateTime('now'));

            // Almacenar informacion en la base de datos
            $user->addSubscription($subscription);
            $user->addInvoice($invoice);
            $entityManager->persist($user);
            $entityManager->persist($subscription);
            $entityManager->persist($invoice);
            $entityManager->flush();

            $this->addFlash('success', 'User created!');
            return $this->redirectToRoute('create_user');
        }
        return $this->render('user/create.html.twig', [
            'form_create_user' => $form->createView(),
        ]);
    }

    #[Route('/list/users', name: 'list_users')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        return $this->render('user/list.html.twig', [
            'users' => $entityManager->getRepository(User::class)->findAll()
        ]);
    }
}
