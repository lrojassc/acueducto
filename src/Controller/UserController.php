<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Subscription;
use App\Entity\User;
use App\Form\CreateUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends MainController
{

    /**
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        parent::__construct($entityManager, $validator);
    }

    #[Route('/create/user', name: 'create_user')]
    public function create(Request $request): Response
    {
        $form = $this->createForm(CreateUserType::class);
        $submittedToken = $request->getPayload()->get('token_create_user');
        if ($this->isCsrfTokenValid('create-user', $submittedToken)) {
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
                $invoice->setMonthInvoiced($this->monthsNumber[date('m')]);
                $invoice->setConcept('SUSCRIPCION');
                $invoice->setStatus('PENDIENTE');
                $invoice->setSubscription($subscription);
                $invoice->setCreatedAt(new \DateTime('now'));
                $invoice->setUpdatedAt(new \DateTime('now'));

                // Almacenar informacion en la base de datos
                $user->addSubscription($subscription);
                $user->addInvoice($invoice);
                $this->entityManager->persist($user);
                $this->entityManager->persist($subscription);
                $this->entityManager->persist($invoice);
                $this->entityManager->flush();

                $this->addFlash('success', 'Usuario creado correctamente');
                return $this->redirectToRoute('create_user');
            }
        }
        return $this->render('user/create.html.twig', [
            'form_create_user' => $form->createView(),
        ]);
    }

    #[Route('/list/users', name: 'list_users')]
    public function list(): Response
    {
        return $this->render('user/list.html.twig', [
            'users' => $this->entityManager->getRepository(User::class)->findAll()
        ]);
    }

    #[Route('/show/user/{user}', name: 'show_user')]
    public function show(User $user): Response
    {
        $active_invoices = $this->entityManager->getRepository(Invoice::class)->findInvoicesActivesByUser($user->getId());
        $subscription_status = $user->getPaidSubscription() === 'PAGADA'
            ? 'Esta suscripción se encuentra PAGADA completamente'
            : 'Esta suscripción aún NO SE HA PAGADO completamente';

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'subscription_status' => $subscription_status,
            'invoices' => $active_invoices
        ]);
    }

    #[Route('/edit/user/{user}', name: 'edit_user')]
    public function edit(User $user, Request $request): Response
    {
        $form = $this->createForm(CreateUserType::class, $user);
        $active_subscription = $this->entityManager->getRepository(Subscription::class)->findByActiveSubscription($user->getId());
        $submittedToken = $request->getPayload()->get('token_edit_user');
        if ($this->isCsrfTokenValid('edit-user', $submittedToken)) {
            $form->handleRequest($request);
            $user->setPaidSubscription('DEBE');
        }
        return $this->render('user/edit.html.twig', [
            'form_edit_user' => $form->createView(),
            'user' => $user,
            'subscriptions' => $active_subscription
        ]);
    }
}
