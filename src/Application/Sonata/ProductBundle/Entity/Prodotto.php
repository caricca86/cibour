<?php
/*
 * This file is part of the <name> project.
 *
 * (c) <yourname> <youremail>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\ProductBundle\Entity;

use CTI\CibourBundle\Entity\Counter;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * This file has been generated by the Sonata product generation command ( http://sonata-project.org/ )
 *
 * References :
 *   working with object : http://www.doctrine-project.org/projects/orm/2.0/docs/reference/working-with-objects/en
 *
 * @author <yourname> <youremail>
 *
 * @UniqueEntity("sku")
 */
class Prodotto extends Product
{
    const MACROREGIONE_NORD = 0;
    const MACROREGIONE_CENTRO = 1;
    const MACROREGIONE_SUD = 2;
    const MACROREGIONE_ISOLE = 3;

    const DIETA_VEGETARIANA = 0;
    const DIETA_VEGANA = 1;

    const METODO_PRODUZIONE_BIOLOGICO = 0;
    const METODO_PRODUZIONE_DINAMICO = 1;

    static $macroregione_choice_list = array(
        '0' => 'Nord',
        '1' => 'Centro',
        '2' => 'Sud',
        '3' => 'Isole'
    );

    static $tipo_dieta_choice_list = array(
        '0' => 'Vegetarian',
        '1' => 'Vegan'
    );

    static $metodo_produzione_choice_list = array(
        '0' => 'Biologico',
        '1' => 'Dinamico'
    );

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var \CTI\CibourBundle\Entity\Produttore
     */
    protected $produttore;

    /**
     * @var string
     */
    protected $macroregione;

    /**
     * @var string
     */
    protected $pezzatura;

    /**
     * @var integer
     */
    protected $tipo_dieta;

    /**
     * @var boolean
     */
    protected $superfood;

    /**
     * @var string
     */
    protected $superfood_description;

    /**
     * @var boolean
     */
    protected $gluten_free;

    /**
     * @var string
     */
    protected $metodo_produzione;

    /**
     * @var \CTI\CibourBundle\Entity\CaratteristicaGustativa
     */
    protected $caratteristica_gustativa;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $abbinamento;

    /**
     * @var integer
     */
    protected $alimentazione;

    /**
     * @var integer
     */
    protected $agricoltura;

    /**
     * @var integer
     */
    protected $ambiente;

    /**
     * @var integer
     */
    protected $artigianato;

    /**
     * @var integer
     */
    protected $arte;

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
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set produttore
     *
     * @param \CTI\CibourBundle\Entity\Produttore $produttore
     * @return Prodotto
     */
    public function setProduttore(\CTI\CibourBundle\Entity\Produttore $produttore = null)
    {
        $this->produttore = $produttore;

        return $this;
    }

    /**
     * Get produttore
     *
     * @return \CTI\CibourBundle\Entity\Produttore
     */
    public function getProduttore()
    {
        return $this->produttore;
    }

    public function getProduttoreCodice()
    {
        return $this->produttore->getCodice();
    }

    /**
     * Set macroregione
     *
     * @param integer $macroregione
     * @return Product
     */
    public function setMacroregione($macroregione)
    {
        $this->macroregione = $macroregione;

        return $this;
    }

    /**
     * Get macroregione
     *
     * @return integer
     */
    public function getMacroregione()
    {
        /*if ($this->macroregione != null) {
            return Prodotto::$macroregione_choice_list[$this->macroregione];
        }*/
        return $this->macroregione;
    }

    /**
     * Set pezzatura
     *
     * @param string $pezzatura
     * @return Product
     */
    public function setPezzatura($pezzatura)
    {
        $this->pezzatura = $pezzatura;

        return $this;
    }

    /**
     * Get pezzatura
     *
     * @return string
     */
    public function getPezzatura()
    {
        return $this->pezzatura;
    }

    /**
     * Set tipo_dieta
     *
     * @param integer $tipoDieta
     * @return Product
     */
    public function setTipoDieta($tipoDieta)
    {
        $this->tipo_dieta = $tipoDieta;

        return $this;
    }

    /**
     * Get tipo_dieta
     *
     * @return integer
     */
    public function getTipoDieta()
    {
        /*if ($this->tipo_dieta != null){
            return Prodotto::$tipo_dieta_choice_list[$this->tipo_dieta];
        }*/

        return $this->tipo_dieta;
    }

    /**
     * Set superfood
     *
     * @param boolean $superfood
     * @return Product
     */
    public function setSuperfood($superfood)
    {
        $this->superfood = $superfood;

        return $this;
    }

    /**
     * Get superfood
     *
     * @return boolean
     */
    public function getSuperfood()
    {
        return $this->superfood;
    }

    /**
     * Set superfood_description
     *
     * @param string $superfoodDescription
     * @return Product
     */
    public function setSuperfoodDescription($superfoodDescription)
    {
        $this->superfood_description = $superfoodDescription;

        return $this;
    }

    /**
     * Get superfood_description
     *
     * @return string
     */
    public function getSuperfoodDescription()
    {
        return $this->superfood_description;
    }

    /**
     * Set gluten_free
     *
     * @param boolean $glutenFree
     * @return Product
     */
    public function setGlutenFree($glutenFree)
    {
        $this->gluten_free = $glutenFree;

        return $this;
    }

    /**
     * Get gluten_free
     *
     * @return boolean
     */
    public function getGlutenFree()
    {
        return $this->gluten_free;
    }

    /**
     * Set metodo_produzione
     *
     * @param string $metodoProduzione
     * @return Product
     */
    public function setMetodoProduzione($metodoProduzione)
    {
        $this->metodo_produzione = $metodoProduzione;

        return $this;
    }

    /**
     * Get metodo_produzione
     *
     * @return integer
     */
    public function getMetodoProduzione()
    {
        /*if ($this->metodo_produzione != null) {
            return Prodotto::$metodo_produzione_choice_list[$this->metodo_produzione];
        }*/

        return $this->metodo_produzione;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->abbinamento = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set caratteristica_gustativa
     *
     * @param \CTI\CibourBundle\Entity\CaratteristicaGustativa $caratteristicaGustativa
     * @return Product
     */
    public function setCaratteristicaGustativa(\CTI\CibourBundle\Entity\CaratteristicaGustativa $caratteristicaGustativa = null)
    {
        $this->caratteristica_gustativa = $caratteristicaGustativa;

        return $this;
    }

    /**
     * Get caratteristica_gustativa
     *
     * @return \CTI\CibourBundle\Entity\CaratteristicaGustativa
     */
    public function getCaratteristicaGustativa()
    {
        return $this->caratteristica_gustativa;
    }

    /**
     * Add abbinamento
     *
     * @param \CTI\CibourBundle\Entity\CaratteristicaGustativa $abbinamento
     * @return Product
     */
    public function addAbbinamento(\CTI\CibourBundle\Entity\CaratteristicaGustativa $abbinamento)
    {
        $this->abbinamento[] = $abbinamento;

        return $this;
    }

    /**
     * Remove abbinamento
     *
     * @param \CTI\CibourBundle\Entity\CaratteristicaGustativa $abbinamento
     */
    public function removeAbbinamento(\CTI\CibourBundle\Entity\CaratteristicaGustativa $abbinamento)
    {
        $this->abbinamento->removeElement($abbinamento);
    }

    /**
     * Get abbinamento
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAbbinamento()
    {
        return $this->abbinamento;
    }

    /**
     * Set alimentazione
     *
     * @param integer $alimentazione
     * @return Prodotto
     */
    public function setAlimentazione($alimentazione)
    {
        $this->alimentazione = $alimentazione;

        return $this;
    }

    /**
     * Get alimentazione
     *
     * @return integer 
     */
    public function getAlimentazione()
    {
        return $this->alimentazione;
    }

    /**
     * Set agricoltura
     *
     * @param integer $agricoltura
     * @return Prodotto
     */
    public function setAgricoltura($agricoltura)
    {
        $this->agricoltura = $agricoltura;

        return $this;
    }

    /**
     * Get agricoltura
     *
     * @return integer 
     */
    public function getAgricoltura()
    {
        return $this->agricoltura;
    }

    /**
     * Set ambiente
     *
     * @param integer $ambiente
     * @return Prodotto
     */
    public function setAmbiente($ambiente)
    {
        $this->ambiente = $ambiente;

        return $this;
    }

    /**
     * Get ambiente
     *
     * @return integer 
     */
    public function getAmbiente()
    {
        return $this->ambiente;
    }

    /**
     * Set artigianato
     *
     * @param integer $artigianato
     * @return Prodotto
     */
    public function setArtigianato($artigianato)
    {
        $this->artigianato = $artigianato;

        return $this;
    }

    /**
     * Get artigianato
     *
     * @return integer 
     */
    public function getArtigianato()
    {
        return $this->artigianato;
    }

    /**
     * Set arte
     *
     * @param integer $arte
     * @return Prodotto
     */
    public function setArte($arte)
    {
        $this->arte = $arte;

        return $this;
    }

    /**
     * Get arte
     *
     * @return integer 
     */
    public function getArte()
    {
        return $this->arte;
    }

    public function getMacroregioneName()
    {
        $macroregione_list = self::getMacroregioneList();

        return $macroregione_list[$this->getMacroregione()];
    }

    public static function getMacroregioneList()
    {
        return array(
            self::MACROREGIONE_NORD => 'Nord',
            self::MACROREGIONE_CENTRO => 'Centro',
            self::MACROREGIONE_SUD => 'Sud',
            self::MACROREGIONE_ISOLE => 'Isole'
        );
    }

    public function getTipoDietaName()
    {
        if ($this->tipo_dieta == null)
        {
            return null;
        }
        $tipo_dieta_list = self::getTipoDietaList();

        return $tipo_dieta_list[$this->getTipoDieta()];
    }

    public static function getTipoDietaList()
    {
        return array(
            self::DIETA_VEGETARIANA => 'Vegetariana',
            self::DIETA_VEGANA => 'Vegana'
        );
    }

    public function getMetodoProduzioneName()
    {
        if ($this->metodo_produzione == null)
        {
            return null;
        }

        $metodo_produzione_list = self::getMetodoProduzioneList();

        return $metodo_produzione_list[$this->getMetodoProduzione()];
    }

    public static function getMetodoProduzioneList()
    {
        return array(
            self::METODO_PRODUZIONE_BIOLOGICO => 'Biologico',
            self::METODO_PRODUZIONE_DINAMICO => 'Dinamico'
        );
    }
}
