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

class CreateAdvanceInvoicesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', IntegerType::class,  [
                'label' => 'Valor Mensual Para Las Facturas',
                'required' => TRUE,
                'constraints' => new NotBlank()
            ])
            ->add('description', TextType::class, [
                'label' => 'Descripción de la Factura',
            ])
            ->add('concept', ChoiceType::class, [
                'label' => 'Concepto',
                'required' => true,
                'choices' => [
                    'MENSUALIDAD' => 'MENSUALIDAD',
                ]
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
                'label' => 'Seleccione un Usuario Suscriptor',
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Generar Facturas y Pagar',
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
