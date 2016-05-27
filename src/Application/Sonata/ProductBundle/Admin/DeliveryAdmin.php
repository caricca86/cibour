<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\ProductBundle\Admin;

use Sonata\ProductBundle\Admin\DeliveryAdmin as Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;

class DeliveryAdmin extends Admin
{


    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        if (!$this->isChild()) {
            $formMapper->add('product', 'sonata_type_model_list', array(), array(
                'admin_code' => 'sonata.product.admin.product'
            ));
        }

        $formMapper
            ->add('enabled')
            ->add('code', 'sonata_delivery_choice')
            ->add('perItem')
            ->add('countryCode', 'country')
        ;
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $list
     */
    public function configureListFields(ListMapper $list)
    {
        if (!$this->isChild()) {
            $list
                ->addIdentifier('id')
                ->addIdentifier('product', null, array(
                    'admin_code' => 'sonata.product.admin.product'
                ));
        }

        $list
            ->addIdentifier('code')
            ->add('enabled')
            ->add('perItem')
            ->add('countryCode')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('code')
            ->add('countryCode')
        ;
    }
}
