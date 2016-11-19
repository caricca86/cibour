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
class TakeAwayDelivery extends BaseServiceDelivery
{
    public function getPrice()
    {
        return 0;
    }

    public function getName()
    {
        return 'Ritiro presso Container èPASTA di P.le Cadorna di fronte Stazione Nord';
    }

    public function getVatRate()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isAddressRequired()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'take_away';
    }

    public function getEnabled(){
        return true;
    }

    public function getPriority()
    {
        return 1;
    }
}