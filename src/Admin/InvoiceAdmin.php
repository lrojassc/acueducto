<?php

namespace App\Admin;

use Doctrine\DBAL\Types\TextType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class InvoiceAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('value', TextType::class);
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid->add('value');
        $datagrid->add('year_invoiced');
        $datagrid->add('month_invoiced');
        $datagrid->add('concept');
        $datagrid->add('status');
        $datagrid->add('user.name');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->add('id');
        $list->add('value');
        $list->add('year_invoiced');
        $list->add('month_invoiced');
        $list->add('description');
        $list->add('status');
        $list->add('concept');
        $list->add('subscription.service');
        $list->add('user.name');
        $list->add('user.address');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('value');
    }
}