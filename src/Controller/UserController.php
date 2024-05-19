<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Subscription;
use App\Entity\User;
use App\Form\CreateUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends MainController
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

    #[Route('/create/user', name: 'create_user')]
    public function create(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        // Este medoto tambien es utilizado para restringir acceso a algunas paginas
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $config = $this->getConfig();
        $form = $this->createForm(CreateUserType::class);
        $submittedToken = $request->getPayload()->get('token_create_user');
        if ($this->isCsrfTokenValid('create-user', $submittedToken)) {
                $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $form->getData();
                $user->setPaidSubscription('DEBE');
                $user->setRoles(['ROLE_USER']);
                $user->setPassword($userPasswordHasher->hashPassword(
                    $user,
                    $form->get('document_number')->getData()
                ));
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
                $invoice->setValue($config['value_subscription']);
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
                return $this->redirectToRoute('list_users');
            }
        }
        return $this->render('user/create.html.twig', [
            'form_create_user' => $form->createView(),
        ]);
    }

    #[Route('/list/users', name: 'list_users')]
    public function list(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $config = $this->getConfig();
        $number_items = $config['number_items'];
        return $this->render('user/list.html.twig', [
            'users' => $this->entityManager->getRepository(User::class)->findAll(),
            'number_items' => $number_items
        ]);
    }

    #[Route('/show/user/{user}', name: 'show_user')]
    public function show(User $user): Response
    {
        $config = $this->getConfig();
        $number_items = $config['number_items'];

        $active_invoices = $this->entityManager->getRepository(Invoice::class)->findInvoicesActivesByUser($user->getId());
        $subscription_status = $user->getPaidSubscription() === 'PAGADA'
            ? 'Esta suscripción se encuentra PAGADA completamente'
            : 'Esta suscripción aún NO SE HA PAGADO completamente';

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'subscription_status' => $subscription_status,
            'invoices' => $active_invoices,
            'number_items' => $number_items
        ]);
    }

    #[Route('/edit/user/{user}', name: 'edit_user')]
    public function edit(User $user, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $active_subscription = $this->entityManager->getRepository(Subscription::class)->findByActiveSubscription($user->getId());
        $form = $this->createForm(CreateUserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setUpdatedAt(new \DateTime('now'));
            $this->entityManager->persist($user);

            $update_services = $this->getServicesByUser($request, 'editActiveServices');
            $new_services = $this->getServicesByUser($request, 'nuevoServicio');
            $subscription_by_user = $this->entityManager->getRepository(Subscription::class)->findByActiveSubscription($user->getId());

            $count = 0;
            // Actualizar servicios activos
            foreach ($update_services as $update_service) {
                if (!empty($update_service)) {
                    $subscription_by_user[$count]->setService($update_service);
                    $subscription_by_user[$count]->setUpdatedAt(new \DateTime('now'));
                    $this->entityManager->persist($subscription_by_user[$count]);
                }
                $count++;
            }

            // Crear nuevos servicios
            foreach ($new_services as $new_service) {
                if (!empty($new_service)) {
                    $new_subscription = new Subscription();
                    $new_subscription->setUser($user);
                    $new_subscription->setService($new_service);
                    $new_subscription->setStatus('ACTIVO');
                    $new_subscription->setCreatedAt(new \DateTime('now'));
                    $new_subscription->setUpdatedAt(new \DateTime('now'));

                    $this->entityManager->persist($new_subscription);
                }
            }
            $this->entityManager->flush();

            $this->addFlash('success', 'Usuario actualizado correctamente');
            return $this->redirectToRoute('show_user', ['user' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'form_edit_user' => $form->createView(),
            'user' => $user,
            'subscriptions' => $active_subscription
        ]);
    }


}
