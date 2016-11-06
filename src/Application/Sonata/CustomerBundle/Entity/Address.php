<?php
/**
 * This file is part of the <name> project.
 *
 * (c) <yourname> <youremail>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\CustomerBundle\Entity;

use CTI\CibourBundle\Entity\CodiceFiscale\Subject;
use Sonata\CustomerBundle\Entity\BaseAddress as BaseAddress;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * This file has been generated by the Sonata EasyExtends bundle ( http://sonata-project.org/easy-extends )
 *
 * References :
 *   working with object : http://www.doctrine-project.org/projects/orm/2.0/docs/reference/working-with-objects/en
 *
 * @author <yourname> <youremail>
 */
class Address extends BaseAddress
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string
     */
    protected $provincia;

    /**
     * @var string
     */
    protected $partita_iva;

    /**
     * @var string
     */
    protected $codice_fiscale;

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
     * Set provincia
     *
     * @param string $provincia
     * @return Address
     */
    public function setProvincia($provincia)
    {
        $this->provincia = $provincia;

        return $this;
    }

    /**
     * Get provincia
     *
     * @return string 
     */
    public function getProvincia()
    {
        return $this->provincia;
    }

    /**
     * Set partita_iva
     *
     * @param string $partitaIva
     * @return Address
     */
    public function setPartitaIva($partitaIva)
    {
        $this->partita_iva = $partitaIva;

        return $this;
    }

    /**
     * Get partita_iva
     *
     * @return string 
     */
    public function getPartitaIva()
    {
        return $this->partita_iva;
    }

    /**
     * Set codice_fiscale
     *
     * @param string $codiceFiscale
     * @return Address
     */
    public function setCodiceFiscale($codiceFiscale)
    {
        $this->codice_fiscale = $codiceFiscale;

        return $this;
    }

    /**
     * Get codice_fiscale
     *
     * @return string 
     */
    public function getCodiceFiscale()
    {
        return $this->codice_fiscale;
    }

    public function isCodiceFiscaleLegal()
    {
        return true;
    }
}
