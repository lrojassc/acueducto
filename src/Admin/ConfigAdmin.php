<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

final class ConfigAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('value_invoice')
            ->add('month_invoiced')
            ->add('value_subscription')
            ->add('number_records_table')
            ->add('created_at')
            ->add('updated_at')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->add('value_invoice')
            ->add('month_invoiced')
            ->add('value_subscription')
            ->add('number_records_table')
            ->add('created_at')
            ->add('updated_at')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('id')
            ->add('value_invoice')
            ->add('month_invoiced')
            ->add('value_subscription')
            ->add('number_records_table')
            ->add('created_at')
            ->add('updated_at')
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('value_invoice')
            ->add('month_invoiced')
            ->add('value_subscription')
            ->add('number_records_table')
            ->add('created_at')
            ->add('updated_at')
        ;
    }
}
