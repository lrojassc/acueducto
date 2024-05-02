<?php

namespace App\Form;

use App\Entity\Invoice;
use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateInvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', IntegerType::class,  [
                'label' => 'Valor de la factura',
                'required' => TRUE
            ])
            ->add('description')
            ->add('year_invoiced')
            ->add('month_invoiced')
            ->add('concept', ChoiceType::class, [
                'label' => 'Concepto',
                'required' => true,
                'choices' => [
                    'SUSCRIPCION' => 'SUSCRIPCION',
                    'MENSUALIDAD' => 'MENSUALIDAD',
                    'RECONECCION' => 'RECONECCION',
                ]
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
            ])
            ->add('subscription', EntityType::class, [
                'class' => Subscription::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
    }
}
