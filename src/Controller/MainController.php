<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MainController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * @var ValidatorInterface
     */
    protected ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    protected array $monthsNumber = [
        '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO',
        '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTUBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE'
    ];

    /**
     * Validar que se cumplan las condiciones para los campos
     *
     * @param array $fields
     *
     * @return array
     */
    public function validateDataForm(array $fields): array
    {
        $count = 0;
        $errors = [];
        foreach ($fields as $field) {
            $errors[$count] = $this->validator->validate($field['field'], $field['constraint']);
            $count ++;
        }

        $message_errors = [];
        if (count($errors) > 0) {
            foreach ($errors as $key => $error) {
                foreach ($error as $item) {
                    $message_errors[$key] = $item->getMessage();
                }
            }
        }
        return $message_errors;
    }

    public function getServicesByUser($request, $search): array
    {
        $services = [];
        for ($i = 1; $i <= 5; $i++) {
            $services['service_'.$i] = $request->request->get($search.$i);
            if ($services['service_'.$i] === NULL) {
                unset($services['service_'.$i]);
            }
        }
        return $services;
    }
}