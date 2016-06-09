<?php

namespace CTI\CibourBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Produttore
 *
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity("codice")
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
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=255)
     * @Assert\NotNull()
     */
    protected $nome;

    /**
     * @var string
     *
     * @ORM\Column(name="codice", type="string", length=5, unique=true)
     * @Assert\NotNull()
     */
    protected $codice;

    /**
     * @var string
     *
     * @ORM\Column(name="descrizione", type="string", length=255)
     * @Assert\NotNull()
     */
    protected $descrizione;

    /**
     * @ORM\OneToMany(targetEntity="Application\Sonata\ProductBundle\Entity\Prodotto", mappedBy="produttore")
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
     * @param \Application\Sonata\ProductBundle\Entity\Prodotto $prodotti
     * @return Produttore
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

    /**
     * Set nome
     *
     * @param string $nome
     * @return Produttore
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
     * Set codice
     *
     * @param string $codice
     * @return Produttore
     */
    public function setCodice($codice)
    {
        $this->codice = $codice;

        return $this;
    }

    /**
     * Get codice
     *
     * @return string 
     */
    public function getCodice()
    {
        return $this->codice;
    }

    public function __toString()
    {
        return $this->nome;
    }
}
