<?php
/**
 * This file is part of the <name> project.
 *
 * (c) <yourname> <youremail>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\ProductBundle\Entity;

use Sonata\ProductBundle\Entity\BaseProduct as BaseProduct;

/**
 * This file has been generated by the Sonata EasyExtends bundle ( http://sonata-project.org/easy-extends )
 *
 * References :
 *   working with object : http://www.doctrine-project.org/projects/orm/2.0/docs/reference/working-with-objects/en
 *
 * @author <yourname> <youremail>
 */
abstract class Product extends BaseProduct
{
    static $macroregione_choice_list = array(
        '0' => 'Nord',
        '1' => 'Centro',
        '2' => 'Sud',
        '3' => 'Isole'
    );

    static $tipo_dieta_choice_list = array(
        '0' => 'Vegetariana',
        '1' => 'Vegana'
    );

    static $meotodo_produzione_choice_list = array(
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
     * @var integer
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
     * @var integer
     */
    protected $metodo_produzione;

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
     * @param integer $metodoProduzione
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
        return $this->metodo_produzione;
    }
}
