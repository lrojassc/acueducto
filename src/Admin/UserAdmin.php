<?php

namespace App\Admin;

use Doctrine\DBAL\Types\TextType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class UserAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('name', null, [
            'attr' => [
                'readonly' => true,
            ]
        ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid->add('name');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->add('id');
        $list->add('name');
        $list->add('document_type');
        $list->add('document_number');
        $list->add('email');
        $list->add('phone_number');
        $list->add('paid_subscription');
        $list->add('full_payment');
        $list->add('address');
        $list->add('city');
        $list->add('municipality');
        $list->add('status');
        $list->add(ListMapper::NAME_ACTIONS, null, [
            ListMapper::TYPE_ACTIONS => [
                'show' => [],
                'edit' => [],
                'delete' => [],
            ],
        ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('name');
    }
}