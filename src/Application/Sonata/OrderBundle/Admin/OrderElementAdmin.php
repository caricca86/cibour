<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\OrderBundle\Admin;

use Sonata\OrderBundle\Admin\OrderElementAdmin as BaseAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

use Sonata\Component\Currency\CurrencyDetectorInterface;
use Sonata\Component\Product\Pool;

class OrderElementAdmin extends BaseAdmin
{
    /**
     * @var CurrencyDetectorInterface
     */
    protected $currencyDetector;

    /**
     * @var Pool
     */
    protected $productPool;

    /**
     * @param CurrencyDetectorInterface $currencyDetector
     */
    public function setCurrencyDetector(CurrencyDetectorInterface $currencyDetector)
    {
        $this->currencyDetector = $currencyDetector;
    }

    /**
     * @param Pool $productPool
     */
    public function setProductPool(Pool $productPool)
    {
        $this->productPool = $productPool;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->parentAssociationMapping = 'order';
        $this->setTranslationDomain('SonataOrderBundle');
    }

    /**
     * {@inheritdoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with($this->trans('order_element.form.group_main_label', array(), 'SonataOrderBundle'))
                //->add('productType', 'choice', array('choices' => array_keys($this->productPool->getProducts())))
                ->add('quantity')
                ->add('productSku', null, array('label' => 'Codice'))
                ->add('price')
                ->add('vatRate', null, array('label' => 'Percentuale Iva'))
                ->add('designation')
                ->add('description', null, array('required' => false))
                //->add('status', 'sonata_order_status', array('translation_domain' => 'SonataOrderBundle'))
                ->add('deliveryStatus', 'sonata_product_delivery_status', array('translation_domain' => 'SonataDeliveryBundle'))
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $list)
    {
        $list->addIdentifier('productSku', null, array('label' => 'Codice'));

        if (!$list->getAdmin()->isChild()) {
            $list->add('order');
        }

        $list
            ->add('designation', null, array('label' => 'Prodotto'))
            ->add('getStatusName', 'trans', array('name' => 'status', 'catalogue' => 'SonataOrderBundle', 'sortable' => 'status'))
            ->add('getDeliveryStatusName', 'trans', array('name' => 'deliveryStatus', 'catalogue' => 'SonataOrderBundle', 'sortable' => 'deliveryStatus'))
            ->add('getTotalWithVat', 'currency', array('currency' => $this->currencyDetector->getCurrency()->getLabel()))
            ->add('getTotal', 'currency', array('currency' => $this->currencyDetector->getCurrency()->getLabel()))
        ;
    }
}
