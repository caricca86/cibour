<?php

namespace CTI\CibourBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CaratteristicaGustativa
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class CaratteristicaGustativa
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=255)
     */
    protected $nome;

    /**
     * @var string
     *
     * @ORM\Column(name="descrizione", type="string", length=255, nullable=true)
     */
    protected $descrizione;

    /**
     * @ORM\OneToMany(targetEntity="Application\Sonata\ProductBundle\Entity\Prodotto", mappedBy="caratteristica_gustativa")
     */
    protected $prodotti;

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
     * Set nome
     *
     * @param string $nome
     * @return CaratteristicaGustativa
     */
    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * Get nome
     *
     * @return string 
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Set descrizione
     *
     * @param string $descrizione
     * @return CaratteristicaGustativa
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
     * @param \Application\Sonata\ProductBundle\Entity\Prodotto $prodotti
     * @return CaratteristicaGustativa
     */
    public function addProdotti(\Application\Sonata\ProductBundle\Entity\Prodotto $prodotti)
    {
        $this->prodotti[] = $prodotti;

        return $this;
    }

    /**
     * Remove prodotti
     *
     * @param \Application\Sonata\ProductBundle\Entity\Prodotto $prodotti
     */
    public function removeProdotti(\Application\Sonata\ProductBundle\Entity\Prodotto $prodotti)
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

    public function __toString()
    {
        return $this->nome;
    }
}
