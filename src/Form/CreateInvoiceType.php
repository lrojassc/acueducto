<?php

namespace App\Form;

use App\Entity\Invoice;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateInvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', IntegerType::class,  [
                'label' => 'Valor de la Factura',
                'required' => TRUE,
                'constraints' => new NotBlank()
            ])
            ->add('description', TextType::class, [
                'label' => 'Descripción de la Factura',
            ])
            ->add('month_invoiced', ChoiceType::class, [
                'label' => 'Mes de la Factura',
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
            ->add('concept', ChoiceType::class, [
                'label' => 'Concepto',
                'required' => true,
                'choices' => [
                    'MENSUALIDAD' => 'MENSUALIDAD',
                    'RECONECCION' => 'RECONECCION',
                    'SUSCRIPCION' => 'SUSCRIPCION',
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Crear Factura',
                'attr' => ['class' => 'btn btn-outline-secondary']
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
            'csrf_protection' => true,
            'allow_extra_fields' => true
        ]);
    }
}
