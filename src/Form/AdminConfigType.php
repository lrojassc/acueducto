<?php

namespace App\Form;

use App\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value_invoice', TextType::class, [
                'label' => 'Definir Valor de La Factura Mensual',
                'required' => TRUE,
            ])
            ->add('month_invoiced', ChoiceType::class, [
                'label' => 'Definir Mes para Facturas Masivas',
                'required' => true,
                'choices' => [
                    'ENERO' => 'ENERO',
                    'FEBRERO' => 'FEBRERO',
                    'MARZO' => 'MARZO',
                    'ABRIL' => 'ABRIL',
                    'MAYO' => 'MAYO',
                    'JUNIO' => 'JUNIO',
                    'JULIO' => 'JULIO',
                    'AGOSTO' => 'AGOSTO',
                    'SEPTIEMBRE' => 'SEPTIEMBRE',
                    'OCTUBRE' => 'OCTUBRE',
                    'NOVIEMBRE' => 'NOVIEMBRE',
                    'DICIEMBRE' => 'DICIEMBRE',
                ]
            ])
            ->add('value_subscription', TextType::class, [
                'label' => 'Definir Valor del Derecho de Suscripción',
                'required' => TRUE,
            ])
            ->add('number_records_table', TextType::class, [
                'label' => 'Definir Cantidad de Items por Tabla',
                'required' => TRUE,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Guardar Configuración',
                'attr' => ['class' => 'btn btn-outline-secondary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}
