<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\CustomerBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sonata\Component\Customer\AddressInterface;
use Sonata\Component\Customer\AddressManagerInterface;
use Sonata\Component\Customer\CustomerManagerInterface;
use Sonata\CustomerBundle\Entity\BaseAddress;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CustomerController
 *
 * @package Sonata\CustomerBundle\Controller
 *
 * @author  Hugo Briand <briand@ekino.com>
 */
class CustomerController extends Controller
{
    /**
     * Lists customer's addresses
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addressesAction()
    {
        $customer = $this->getCustomer();

        $typeCodes = BaseAddress::getTypesList();

        // This allows to specify the display order
        $addresses = array(
            $typeCodes[AddressInterface::TYPE_DELIVERY] => array(),
            $typeCodes[AddressInterface::TYPE_BILLING]  => array(),
            $typeCodes[AddressInterface::TYPE_CONTACT]  => array(),
        );

        if (null === $customer) {
            // Customer not yet created, the user didn't order yet
            $customer = $this->getCustomerManager()->create();
            $customer->setUser($this->getUser());
            $this->getCustomerManager()->save($customer);
        } else {
            $custAddresses = $this->getAddressManager()->findBy(array('customer' => $customer));

            foreach ($custAddresses as $address) {
                $addresses[$address->getTypeCode()][] = $address;
            }
        }

        // Set redirection URL to be to the list of addresses
        $this->get('session')->set('sonata_address_redirect', $this->generateUrl('sonata_customer_addresses'));

        return $this->render('SonataCustomerBundle:Addresses:list.html.twig', array(
                'addresses'          => $addresses,
                'customer'           => $customer,
                'breadcrumb_context' => 'customer_address',
            ));
    }

    /**
     * Adds an address to current customer
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAddressAction()
    {
        return $this->updateAddress();
    }

    /**
     * Controller action to edit address $id
     *
     * @param $id
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAddressAction($id)
    {
        return $this->updateAddress($id);
    }

    /**
     * Deletes address $id
     *
     * @param $id The address to delete
     *
     * @return RedirectResponse
     */
    public function deleteAddressAction($id)
    {
        if ($this->getRequest()->getMethod() !== 'POST') {
            throw new MethodNotAllowedHttpException(array('POST'));
        }

        $address = $this->getAddressManager()->findOneBy(array('id' => $id));

        $this->checkAddress($address);

        $this->getAddressManager()->delete($address);

        $this->get('session')->getFlashBag()->add('sonata_customer_success', 'customer_address_delete');

        return new RedirectResponse($this->generateUrl('sonata_customer_addresses'));
    }

    /**
     * Sets address $id to current
     *
     * @param $id
     *
     * @return RedirectResponse
     */
    public function setCurrentAddressAction($id)
    {
        $address = $this->getAddressManager()->findOneBy(array('id' => $id));
        $this->checkAddress($address);

        $this->getAddressManager()->setCurrent($address);

        return new RedirectResponse($this->generateUrl('sonata_customer_addresses'));
    }

    /**
     * Updates or create an address
     *
     * @param int $id Address id
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    protected function updateAddress($id = null)
    {
        $customer = $this->getCustomer();

        // Show address creation/edition form
        if (null === $id) {
            $form = $this->createForm('sonata_customer_address');
        } else {
            $address = $this->getAddressManager()->findOneBy(array('id' => $id));
            $this->checkAddress($address);

            $form = $this->createForm('sonata_customer_address', $address, array(
                'context' => $this->getRequest()->query->get('context'), 'type' => $address->getType()
            ));
        }

        $template = 'SonataCustomerBundle:Addresses:new.html.twig';

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                $address = $form->getData();

                if ($address->getType() == AddressInterface::TYPE_BILLING){
                    $cf = $address->getCodiceFiscale();
                    $pIva = $address->getPartitaIva();

                    if ($this->ControllaCF($cf) && $this->controllaPIVA($pIva)) {
                        $anno = substr($cf, 6, 2);
                        $tabellamesi = array(
                            "A" => "01",
                            "B" => "02",
                            "C" => "03",
                            "D" => "04",
                            "E" => "05",
                            "H" => "06",
                            "L" => "07",
                            "M" => "08",
                            "P" => "09",
                            "R" => "10",
                            "S" => "11",
                            "T" => "12"
                        );
                        $mese = $tabellamesi[strtoupper(substr($cf, 8, 1))];
                        $giorno = substr($cf, 9, 2);

                        $diff = date_diff(date_create(), date_create("$anno-$mese-$giorno"));

                        if ($diff->y < 18) {
                            $this->get('session')->getFlashBag()->add('sonata_customer_success', "L'acquisto è permesso solo a utenti con età maggiore di 18 anni");

                            return $this->render($template, array(
                                'form'               => $form->createView(),
                                'breadcrumb_context' => 'customer_address',
                            ));

                        } else {
                            try {
                                $customer->addAddress($address);
                                $this->get('sonata.customer.manager')->save($customer);
                            } catch (UniqueConstraintViolationException $e) {
                                $this->get('session')->getFlashBag()->add('sonata_customer_success', "La partita iva inserita esiste già");

                                return $this->render($template, array(
                                    'form'      => $form->createView(),
                                    'addresses' => $addresses
                                ));
                            }

                            $this->get('session')->getFlashBag()->add('sonata_customer_success', $id ? 'address_edit_success' : 'address_add_success');

                            $url = $this->get('session')->get('sonata_address_redirect', $this->generateUrl('sonata_customer_addresses'));

                            return new RedirectResponse($url);
                        }
                    } else {

                        if (!$this->ControllaCF($cf)){
                            $this->get('session')->getFlashBag()->add('sonata_customer_success', 'Codice fiscale errato');
                        }

                        if (!$this->controllaPIVA($pIva)){
                            $this->get('session')->getFlashBag()->add('sonata_customer_success', 'Partita iva errata');
                        }

                        return $this->render($template, array(
                            'form'               => $form->createView(),
                            'breadcrumb_context' => 'customer_address',
                        ));
                    }
                }
                $customer->addAddress($address);

                $this->getCustomerManager()->save($customer);

                $this->get('session')->getFlashBag()->add('sonata_customer_success', $id ? 'address_edit_success' : 'address_add_success');

                $url = $this->get('session')->get('sonata_address_redirect', $this->generateUrl('sonata_customer_addresses'));

                return new RedirectResponse($url);
            }
        }

        return $this->render($template, array(
            'form'               => $form->createView(),
            'breadcrumb_context' => 'customer_address',
        ));
    }

    /**
     * Checks if $address is valid
     *
     * @param AddressInterface $address
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function checkAddress(AddressInterface $address = null)
    {
        if (null === $address
            || $address->getCustomer()->getId() !== $this->getCustomer()->getId()) {
            throw new NotFoundHttpException;
        }
    }

    /**
     * @return \Sonata\Component\Customer\CustomerInterface
     */
    protected function getCustomer()
    {
        $user = $this->getUser();

        return $this->getCustomerManager()->findOneBy(array('user' => $user));
    }

    /**
     * @return AddressManagerInterface
     */
    protected function getAddressManager()
    {
        return $this->get('sonata.address.manager');
    }

    /**
     * @return CustomerManagerInterface
     */
    protected function getCustomerManager()
    {
        return $this->get('sonata.customer.manager');
    }

    function ControllaCF($cf)
    {
        if( $cf === '' )  return false;
        if( strlen($cf) != 16 )
            return false;
        $cf = strtoupper($cf);
        if( preg_match("/^[A-Z0-9]+\$/", $cf) != 1 ){
            return false;
        }

        return true;
    }

    function controllaPIVA($variabile){

        if($variabile=='')
            return false;

        //la p.iva deve essere lunga 11 caratteri
        if(strlen($variabile)!=11)
            return false;

        //la p.iva deve avere solo cifre
        if(!ereg("^[0-9]+$", $variabile))
            return false;

        $primo=0;
        for($i=0; $i<=9; $i+=2)
            $primo+= ord($variabile[$i])-ord('0');

        for($i=1; $i<=9; $i+=2 ){
            $secondo=2*( ord($variabile[$i])-ord('0') );

            if($secondo>9)
                $secondo=$secondo-9;
            $primo+=$secondo;

        }
        if( (10-$primo%10)%10 != ord($variabile[10])-ord('0') )
            return false;

        return true;

    }
}
