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

use Sonata\ProductBundle\Admin\ProductAdmin as Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\Component\Product\Pool;
use Sonata\Component\Product\ProductInterface;
use Sonata\Component\Currency\CurrencyDetectorInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;

class ProductAdmin extends Admin
{

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, array('edit'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id      = $admin->getRequest()->get('id');
        $product = $this->getObject($id);

        $menu->addChild(
            $this->trans('product.sidemenu.link_product_edit', array(), 'SonataProductBundle'),
            array('uri' => $admin->generateUrl('edit', array('id' => $id)))
        );

        $menu->addChild(
            $this->trans('product.sidemenu.view_categories', array(), 'SonataProductBundle'),
            array('uri' => $admin->generateUrl('sonata.product.admin.product.category.list', array('id' => $id)))
        );

        $menu->addChild(
            $this->trans('product.sidemenu.view_deliveries', array(), 'SonataProductBundle'),
            array('uri' => $admin->generateUrl('sonata.product.admin.delivery.list', array('id' => $id)))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('image', null, array('label' => 'Immagine'))
            ->addIdentifier('sku')
            ->addIdentifier('name')
            ->add('enabled', null, array('editable' => true))
            ->add('price', 'currency', array('currency' => $this->currencyDetector->getCurrency()->getLabel()))
            ->add('productCategories', null, array('associated_tostring' => 'getCategory'))
        ;
    }
}
