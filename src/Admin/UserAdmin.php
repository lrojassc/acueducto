<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $form): void
    {
        $form
        ->add('name', null, ['label' => 'Nombre completo'])
        ->add('document_type', null, [
            'label' => 'Tipo de documento',
            'attr' => [
                'readonly' => true,
            ]
        ])
        ->add('document_number', null, ['label' => 'Número de documento'])
        ->add('email', null, ['label' => 'Correo'])
        ->add('phone_number', null, ['label' => 'Teléfono'])
        ->add('paid_subscription', null, [
            'attr' => [
                'readonly' => true,
            ]
        ])
        ->add('address', null, ['label' => 'Direccion'])
        ->add('city', null, ['label' => 'Ciudad'])
        ->add('municipality', null, ['label' => 'Municipio'])
        ->add('status', ChoiceType::class, [
            'label' => 'Estado Del Suscriptor',
            'required' => true,
            'choices' => [
                'ACTIVO' => 'ACTIVO',
                'SUSPENDIDO' => 'SUSPENDIDO',
            ]
        ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid->add('name', null, ['label' => 'Nombre completo']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->add('id', null, ['label' => 'Código']);
        $list->add('name', null, ['label' => 'Nombre']);
        $list->add('document_number', null, ['label' => 'Numero de documento']);
        $list->add('phone_number', null, ['label' => 'Telefono']);
        $list->add('paid_subscription', null, ['label' => 'Estado Suscripcion']);
        $list->add('address', null, ['label' => 'Direccion']);
        $list->add('status');
        $list->add(ListMapper::NAME_ACTIONS, null, [
            ListMapper::TYPE_ACTIONS => [
                'show' => [],
                'edit' => [],
            ],
        ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('name');
        $show->add('document_type');
        $show->add('document_number');
        $show->add('email');
        $show->add('phone_number');
        $show->add('paid_subscription');
        $show->add('address');
        $show->add('city');
        $show->add('municipality');
        $show->add('status');
    }
}