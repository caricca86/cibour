<?php

namespace CTI\CibourBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Counter
 *
 * La creazione avviene ApiProdottoController
 * Le visualizzazioni vengono aggiunte dal ProdottoController:view
 * Le vendite vengono aggiunte dal PaymentController:confirmation
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Counter
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
     * @ORM\Column(name="views", type="integer")
     */
    private $views = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="sales", type="integer")
     */
    private $sales = 0;

    /**
     * @ORM\OneToOne(targetEntity="Application\Sonata\ProductBundle\Entity\Product", mappedBy="counter")
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
     * Set views
     *
     * @param integer $views
     * @return Counter
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Get views
     *
     * @return integer 
     */
    public function getViews()
    {
        return $this->views;
    }

    public function addView()
    {
        $this->views = $this->views + 1;

        return $this;
    }

    public function resetViews()
    {
        $this->views = 0;

        return $this;
    }

    /**
     * Set sales
     *
     * @param integer $sales
     * @return Counter
     */
    public function setSales($sales)
    {
        $this->sales = $sales;

        return $this;
    }

    /**
     * Get sales
     *
     * @return integer 
     */
    public function getSales()
    {
        return $this->sales;
    }

    public function addSale()
    {
        $this->sales = $this->sales + 1;

        return $this;
    }

    public function resetSales()
    {
        $this->sales = 0;

        return $this;
    }

    public function resetCounter()
    {
        $this->sales = 0;
        $this->views = 0;

        return $this;
    }
    /**
     * Set product
     *
     * @param \Application\Sonata\ProductBundle\Entity\Product $product
     * @return Counter
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
