<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Application\Sonata\DeliveryBundle\Model;

use Sonata\Component\Basket\BasketInterface;
use Sonata\Component\Delivery\BaseServiceDelivery;


/**
 * Class TakeAwayDelivery
 *
 * Custom delivery class example
 *
 * @package Application\Sonata\DeliveryBundle\Model
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class DhlDelivery extends BaseServiceDelivery
{
    public function getPrice()
    {
        return 6.56;
    }

    public function getName()
    {
        return 'Corriere Espresso 8€';
    }

    public function getVatRate()
    {
        return 22;
    }

    /**
     * {@inheritdoc}
     */
    public function isAddressRequired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'dhl';
    }

    public function getEnabled(){
        return true;
    }

    public function getPriority()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal(BasketInterface $basket, $vat = false)
    {
        return parent::getTotal($basket, $vat);
    }


}