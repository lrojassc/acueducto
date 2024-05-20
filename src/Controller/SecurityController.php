<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    #[Route('/access-denied', name: 'access_denied')]
    public function index(): Response
    {
        return $this->render('security/access_denied.html.twig', [
            'title' => 'Acceso Denegado',
            'description' => 'Usted no tiene permisos para poder visualizar esta pagina'
        ]);
    }
}
