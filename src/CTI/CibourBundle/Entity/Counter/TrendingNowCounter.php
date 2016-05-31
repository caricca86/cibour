<?php

namespace CTI\CibourBundle\Entity\Counter;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrendigNowCounter
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class TrendingNowCounter
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="counter", type="integer")
     */
    private $counter = 0;

    /**
     * @ORM\OneToOne(targetEntity="Application\Sonata\ProductBundle\Entity\Product")
     */
    private $product;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set counter
     *
     * @param integer $counter
     * @return TrendigNowCounter
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;

        return $this;
    }

    /**
     * Get counter
     *
     * @return integer 
     */
    public function getCounter()
    {
        return $this->counter;
    }

    public function resetCounter()
    {
        $this->counter = 0;
    }

    public function addCounter()
    {
        $this->counter = $this->counter + 1;
    }

    /**
     * Set product
     *
     * @param \Application\Sonata\ProductBundle\Entity\Product $product
     * @return TrendingNowCounter
     */
    public function setProduct(\Application\Sonata\ProductBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \Application\Sonata\ProductBundle\Entity\Product 
     */
    public function getProduct()
    {
        return $this->product;
    }
}
