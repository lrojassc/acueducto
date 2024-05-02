<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class CreateUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre Completo',
                'required' => true,
            ])
            ->add('document_type', ChoiceType::class, [
                'label' => 'Tipo de Documento',
                'required' => true,
                'choices' => [
                    'Cedula de Ciudadania' => 'CC',
                    'NIT' => 'NIT',
                    'Tarjeta de Identidad' => 'TI',
                ]
            ])
            ->add('document_number', IntegerType::class, [
                'label' => 'Numero de Documento',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Correo',
            ])
            ->add('phone_number', IntegerType::class, [
                'label' => 'Numero de Telefono',
                'required' => true,
            ])
            ->add('address', TextType::class, [
                'label' => 'Dirección',
                'required' => true,
            ])
            ->add('city', TextType::class, [
                'label' => 'Ciudad',
                'required' => true,
            ])
            ->add('municipality', TextType::class, [
                'label' => 'Municipio',
                'required' => true,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Estado Del Suscriptor',
                'required' => true,
                'choices' => [
                    'ACTIVO' => 'ACTIVO',
                    'SUSPENDIDO' => 'SUSPENDIDO',
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Crear Usuario',
                'attr' => ['class' => 'btn btn-outline-secondary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
