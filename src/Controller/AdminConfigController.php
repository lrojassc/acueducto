<?php

namespace App\Controller;

use App\Entity\Config;
use App\Form\AdminConfigType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminConfigController extends MainController
{
    #[Route('/admin/config', name: 'admin_config')]
    public function config(Request $request): Response
    {
        $admin_config = $this->entityManager->getRepository(Config::class)->findAll();
        $data_config = !empty($admin_config) ? $admin_config[0] : new Config();

        $form = $this->createForm(AdminConfigType::class, $data_config);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config = $form->getData();
            $config->setCreatedAt(new \DateTime('now'));
            $config->setUpdatedAt(new \DateTime('now'));
            $this->entityManager->persist($config);
            $this->entityManager->flush();
            $this->addFlash('success', 'Configuración Guardada Correctamente');
            return $this->redirectToRoute('admin_config');
        }
        return $this->render('config/index.html.twig', [
            'form_config' => $form->createView(),
        ]);
    }
}
