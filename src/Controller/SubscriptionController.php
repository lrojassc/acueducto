<?php

namespace App\Controller;

use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SubscriptionController extends MainController
{

    /**
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        parent::__construct($entityManager, $validator);
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
}