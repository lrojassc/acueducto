<?php

namespace App\Form;

use App\Entity\Invoice;
use App\Entity\Payment;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', IntegerType::class, [
                'label' => 'Valor Pagado',
                'required' => FALSE
            ])
            /*
            ->add('method', ChoiceType::class, [
                'label' => 'Metodo Utilizado',
                'choices' => [
                    'EFECTIVO' => 'EFECTIVO'
                ],
                'multiple' => TRUE,
                'expanded' => TRUE,
                'required' => FALSE
            ])
            */
            ->add('month_invoiced', ChoiceType::class, [
                'label' => 'Mes Facturado',
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
                ],
                'multiple' => TRUE,
                'expanded' => TRUE,
                'required' => FALSE
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
                'label' => 'Usuario Suscriptor',
                'placeholder' => 'Selecciona una opción',
                'required' => FALSE
            ])
            ->add('concept', ChoiceType::class, [
                'label' => 'Concepto del Pago',
                'choices' => [
                    'MENSUALIDAD' => 'MENSUALIDAD',
                    'RECONECCION' => 'RECONECCION',
                    'SUSCRIPCION' => 'SUSCRIPCION',
                ],
                'multiple' => TRUE,
                'expanded' => TRUE,
                'required' => FALSE
            ])
            ->add('created_at', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de Pago',
                'required' => FALSE
            ])
            ->add('send_pdf', SubmitType::class, [
                'label' => 'Generar PDF',
                'attr' => ['class' => 'btn btn-outline-danger', 'style' => 'width: 25%;']
            ])
            ->add('send_excel', SubmitType::class, [
                'label' => 'Generar Excel',
                'attr' => ['class' => 'btn btn-outline-success', 'style' => 'width: 25%;']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
