<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SubscriptionController extends MainController
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

    #[Route('/delete/subscription/', name: 'delete_subscription', methods: 'POST')]
    public function delete(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $service = $this->entityManager->getRepository(Subscription::class)->find($data->id);
        $service->setStatus('INACTIVO');
        $service->setUpdatedAt(new \DateTime('now'));
        $this->entityManager->persist($service);
        $this->entityManager->flush();

        $response = [
            'message' => 'Servicio Eliminado Exitosamente',
        ];

        return new JsonResponse($response);
    }

    #[Route('/subscriptions/{user}/service', methods: 'GET')]
    public function getUserServices(User $user): JsonResponse {
        $services_by_user = $this->entityManager->getRepository(Subscription::class)->findServicesActiveByUser($user->getId());
        $services = [];
        foreach ($services_by_user as $service) {
            $services[] = [
                'service' => $service->getService(),
                'id' => $service->getId(),
            ];
        }
        $response = [
            'status' => 200,
            'services' => $services,
        ];
        return new JsonResponse($response);
    }
}