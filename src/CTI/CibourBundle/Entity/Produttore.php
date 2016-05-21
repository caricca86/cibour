<?php

namespace CTI\CibourBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Produttore
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Produttore
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
     * @var string
     *
     * @ORM\Column(name="descrizione", type="string", length=255)
     */
    private $descrizione;

    /**
     * @ORM\OneToMany(targetEntity="Application\Sonata\ProductBundle\Entity\Product", mappedBy="produttore")
     */
    private $prodotti;


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
     * Set descrizione
     *
     * @param string $descrizione
     * @return Produttore
     */
    public function setDescrizione($descrizione)
    {
        $this->descrizione = $descrizione;

        return $this;
    }

    /**
     * Get descrizione
     *
     * @return string 
     */
    public function getDescrizione()
    {
        return $this->descrizione;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->prodotti = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add prodotti
     *
     * @param \Application\Sonata\ProductBundle\Entity\Product $prodotti
     * @return Produttore
     */
    public function addProdotti(\Application\Sonata\ProductBundle\Entity\Product $prodotti)
    {
        $this->prodotti[] = $prodotti;

        return $this;
    }

    /**
     * Remove prodotti
     *
     * @param \Application\Sonata\ProductBundle\Entity\Product $prodotti
     */
    public function removeProdotti(\Application\Sonata\ProductBundle\Entity\Product $prodotti)
    {
        $this->prodotti->removeElement($prodotti);
    }

    /**
     * Get prodotti
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProdotti()
    {
        return $this->prodotti;
    }
}
