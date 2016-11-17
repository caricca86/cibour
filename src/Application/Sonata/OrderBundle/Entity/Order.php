<?php
/**
 * This file is part of the <name> project.
 *
 * (c) <yourname> <youremail>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\OrderBundle\Entity;

use Sonata\OrderBundle\Entity\BaseOrder as BaseOrder;

/**
 * This file has been generated by the Sonata EasyExtends bundle ( http://sonata-project.org/easy-extends )
 *
 * References :
 *   working with object : http://www.doctrine-project.org/projects/orm/2.0/docs/reference/working-with-objects/en
 *
 * @author <yourname> <youremail>
 */
class Order extends BaseOrder
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string
     */
    protected $billingPartitaIva;

    /**
     * @var string
     */
    protected $billingCodiceFiscale;

    /**
     * @var string
     */
    protected $billingProvincia;

    /**
     * @var string
     */
    protected $shippingProvincia;

    /**
     * @var boolean
     */
    protected $fattura = false;

    /**
     * @var boolean
     */
    protected $processato = false;

    /**
     * @var integer
     */
    protected $billingAddressId;

    /**
     * @var integer
     */
    protected $shippingAddressId;

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set billingProvincia
     *
     * @param string $billingProvincia
     * @return Order
     */
    public function setBillingProvincia($billingProvincia)
    {
        $this->billingProvincia = $billingProvincia;

        return $this;
    }

    /**
     * Get billingProvincia
     *
     * @return string 
     */
    public function getBillingProvincia()
    {
        return $this->billingProvincia;
    }

    /**
     * Set shippingProvincia
     *
     * @param string $shippingProvincia
     * @return Order
     */
    public function setShippingProvincia($shippingProvincia)
    {
        $this->shippingProvincia = $shippingProvincia;

        return $this;
    }

    /**
     * Get shippingProvincia
     *
     * @return string 
     */
    public function getShippingProvincia()
    {
        return $this->shippingProvincia;
    }

    /**
     * Set billingPartitaIva
     *
     * @param string $billingPartitaIva
     * @return Order
     */
    public function setBillingPartitaIva($billingPartitaIva)
    {
        $this->billingPartitaIva = $billingPartitaIva;

        return $this;
    }

    /**
     * Get billingPartitaIva
     *
     * @return string 
     */
    public function getBillingPartitaIva()
    {
        return $this->billingPartitaIva;
    }

    /**
     * Set billingCodiceFiscale
     *
     * @param string $billingCodiceFiscale
     * @return Order
     */
    public function setBillingCodiceFiscale($billingCodiceFiscale)
    {
        $this->billingCodiceFiscale = $billingCodiceFiscale;

        return $this;
    }

    /**
     * Get billingCodiceFiscale
     *
     * @return string 
     */
    public function getBillingCodiceFiscale()
    {
        return $this->billingCodiceFiscale;
    }

    /**
     * Get fattura
     *
     * @return boolean
     */
    public function getFattura()
    {
        return $this->fattura;
    }

    /**
     * Set fattura
     *
     * @param boolean $fattura
     * @return Order;
     */
    public function setFattura($fattura)
    {
        $this->fattura = $fattura;

        return $this;
    }

    /**
     * Set billingAddressId
     *
     * @param integer $billingAddressId
     * @return Order
     */
    public function setBillingAddressId($billingAddressId)
    {
        $this->billingAddressId = $billingAddressId;

        return $this;
    }

    /**
     * Get billingAddressId
     *
     * @return integer 
     */
    public function getBillingAddressId()
    {
        return $this->billingAddressId;
    }

    /**
     * Set shippingAddressId
     *
     * @param integer $shippingAddressId
     * @return Order
     */
    public function setShippingAddressId($shippingAddressId)
    {
        $this->shippingAddressId = $shippingAddressId;

        return $this;
    }

    /**
     * Get shippingAddressId
     *
     * @return integer 
     */
    public function getShippingAddressId()
    {
        return $this->shippingAddressId;
    }

    /**
     * Set processato
     *
     * @param boolean $processato
     * @return Order
     */
    public function setProcessato($processato)
    {
        $this->processato = $processato;

        return $this;
    }

    /**
     * Get processato
     *
     * @return boolean 
     */
    public function getProcessato()
    {
        return $this->processato;
    }

    public function getDeliveryWithoutVat()
    {
        return round($this->getDeliveryCost() / ((100 + $this->getDeliveryVat()) / 100), 2); // TODO: Change the autogenerated stub
    }
}
