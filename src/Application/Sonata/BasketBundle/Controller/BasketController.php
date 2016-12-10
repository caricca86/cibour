<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\BasketBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Faker\Provider\cs_CZ\DateTime;
use Sonata\Component\Delivery\UndeliverableCountryException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Form\Extension\Validator\ViolationMapper\ViolationMapper;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

use Sonata\Component\Customer\AddressInterface;

use CTI\CibourBundle\Entity\CodiceFiscale\Checker;
use CTI\CibourBundle\Entity\CodiceFiscale\Subject;

/**
 * This controller manages the Basket operation and most of the order process
 */
class BasketController extends Controller
{
    /**
     * Shows the basket
     *
     * @param  Form                                       $form
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($form = null)
    {
        $form = $form ?: $this->createForm('sonata_basket_basket', $this->get('sonata.basket'), array(
            'validation_groups' => array('elements')
        ));

        // always validate the basket
        if (!$form->isBound()) {
            if ($violations = $this->get('validator')->validate($form)) {
                $violationMapper = new ViolationMapper();
                foreach ($violations as $violation) {
                    $violationMapper->mapViolation($violation, $form, true);
                }
            }
        }

        $this->get('session')->set('sonata_basket_delivery_redirect', 'sonata_basket_delivery_address');

        $this->get('sonata.seo.page')->setTitle($this->get('translator')->trans('basket_index_title', array(), "SonataBasketBundle"));

        return $this->render('SonataBasketBundle:Basket:index.html.twig', array(
            'basket' => $this->get('sonata.basket'),
            'form'   => $form->createView(),
        ));
    }

    /**
     * Update basket form rendering & saving
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction()
    {
        $form = $this->createForm('sonata_basket_basket', $this->get('sonata.basket'), array('validation_groups' => array('elements')));
        $form->bind($this->get('request'));

        if ($form->isValid()) {
            $basket = $form->getData();
            $basket->reset(false); // remove delivery and payment information
            $basket->clean(); // clean the basket

            // update the basket
            $this->get('sonata.basket.factory')->save($basket);

            return new RedirectResponse($this->generateUrl('sonata_basket_index'));
        }

        return $this->forward('SonataBasketBundle:Basket:index', array(
            'form' => $form
        ));
    }

    /**
     * Adds a product to the basket
     *
     * @throws MethodNotAllowedException
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addProductAction()
    {
        $request = $this->get('request');
        $params  = $request->get('add_basket');

        if ($request->getMethod() != 'POST') {
            throw new MethodNotAllowedException(array('POST'));
        }

        // retrieve the product
        $product = $this->get('sonata.product.set.manager')->findOneBy(array('id' => $params['productId']));

        if (!$product) {
            throw new NotFoundHttpException(sprintf('Unable to find the product with id=%d', $params['productId']));
        }

        // retrieve the custom provider for the product type
        $provider = $this->get('sonata.product.pool')->getProvider($product);

        $formBuilder = $this->get('form.factory')->createNamedBuilder('add_basket', 'form', null, array('data_class' => $this->container->getParameter('sonata.basket.basket_element.class'), 'csrf_protection' => false));
        $provider->defineAddBasketForm($product, $formBuilder);

        // load and bind the form
        $form = $formBuilder->getForm();
        $form->bind($request);

        // if the form is valid add the product to the basket
        if ($form->isValid()) {
            $basket = $this->get('sonata.basket');
            $basketElement = $form->getData();

            $quantity = $basketElement->getQuantity();
            $currency = $this->get('sonata.basket')->getCurrency();
            $price = $provider->calculatePrice($product, $currency, true, $quantity);

            if ($basket->hasProduct($product)) {
                $basketElement = $provider->basketMergeProduct($basket, $product, $basketElement);
            } else {
                $basketElement = $provider->basketAddProduct($basket, $product, $basketElement);
            }

            $this->get('sonata.basket.factory')->save($basket);

            if ($request->isXmlHttpRequest() && $provider->getOption('product_add_modal')) {
                return $this->render('SonataBasketBundle:Basket:add_product_popin.html.twig', array(
                    'basketElement' => $basketElement,
                    'locale'        => $basket->getLocale(),
                    'product'       => $product,
                    'price'         => $price,
                    'currency'      => $currency,
                    'quantity'      => $quantity,
                    'provider'      => $provider,
                ));
            } else {
                return new RedirectResponse($this->generateUrl('sonata_basket_index'));
            }
        }

        // an error occur, forward the request to the view
        return $this->forward('SonataProductBundle:Product:view', array(
            'productId' => $product,
            'slug'      => $product->getSlug(),
        ));
    }

    /**
     * Resets (empties) the basket
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function resetAction()
    {
        $this->get('sonata.basket.factory')->reset($this->get('sonata.basket'));

        return new RedirectResponse($this->generateUrl('sonata_basket_index'));
    }

    /**
     * Displays a header preview of the basket
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function headerPreviewAction()
    {
        return $this->render('SonataBasketBundle:Basket:header_preview.html.twig', array(
            'basket' => $this->get('sonata.basket')
        ));
    }

    /**
     * Order process step 1: retrieve the customer associated with the logged in user if there's one
     * or create an empty customer and put it in the basket
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function authenticationStepAction()
    {
        $customer = $this->get('sonata.customer.selector')->get();

        $basket = $this->get('sonata.basket');
        $basket->setCustomer($customer);

        $this->get('sonata.basket.factory')->save($basket);

        return new RedirectResponse($this->generateUrl('sonata_basket_delivery_address'));
    }

    /**
     * Order process step 5: choose payment mode
     *
     * @throws HttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function paymentStepAction()
    {
        $basket = $this->get('sonata.basket');

        if ($basket->countBasketElements() == 0) {
            return new RedirectResponse($this->generateUrl('sonata_basket_index'));
        }

        $customer = $basket->getCustomer();

        if (!$customer) {
            throw new HttpException('Invalid customer');
        }

        if (null === $basket->getBillingAddress()) {
            // If no payment address is specified, we assume it's the same as the delivery
            $billingAddress = clone $basket->getDeliveryAddress();
            $billingAddress->setType(AddressInterface::TYPE_BILLING);
            $basket->setBillingAddress($billingAddress);
        }

        $form = $this->createForm('sonata_basket_payment', $basket, array(
            'validation_groups' => array('delivery')
        ));

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                // save the basket
                $this->get('sonata.basket.factory')->save($basket);

                return new RedirectResponse($this->generateUrl('sonata_basket_final'));
            }
        }

        $this->get('sonata.seo.page')->setTitle($this->get('translator')->trans('basket_payment_title', array(), "SonataBasketBundle"));

        return $this->render('SonataBasketBundle:Basket:payment_step.html.twig', array(
            'basket' => $basket,
            'form'   => $form->createView(),
            'customer'   => $customer,
        ));
    }

    /**
     * Order process step 3: choose delivery mode
     *
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deliveryStepAction()
    {
        $basket = $this->get('sonata.basket');

        if ($basket->countBasketElements() == 0) {
            return new RedirectResponse($this->generateUrl('sonata_basket_index'));
        }

        $customer = $basket->getCustomer();

        if (!$customer) {
            throw new NotFoundHttpException('customer not found');
        }

        try {
            $form = $this->createForm('sonata_basket_shipping', $basket, array(
                'validation_groups' => array('delivery')
            ));
        } catch (UndeliverableCountryException $ex) {
            $countryName = Intl::getRegionBundle()->getCountryName($ex->getAddress()->getCountryCode());
            $message = $this->get('translator')->trans('undeliverable_country', array('%country%' => $countryName), 'SonataBasketBundle');
            $this->get('session')->getFlashBag()->add('error', $message);

            return new RedirectResponse($this->generateUrl('sonata_basket_index'));
        }

        $template = 'SonataBasketBundle:Basket:delivery_step.html.twig';

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                // save the basket
                $this->get('sonata.basket.factory')->save($form->getData());

                return new RedirectResponse($this->generateUrl('sonata_basket_payment_address'));
            }
        }

        $this->get('sonata.seo.page')->setTitle($this->get('translator')->trans('basket_delivery_title', array(), "SonataBasketBundle"));

        return $this->render($template, array(
            'basket'   => $basket,
            'form'     => $form->createView(),
            'customer' => $customer,
        ));
    }

    /**
     * Order process step 2: choose an address from existing ones or create a new one
     *
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deliveryAddressStepAction()
    {
        $customer = $this->get('sonata.customer.selector')->get();

        if (!$customer) {
            throw new NotFoundHttpException('customer not found');
        }

        $basket = $this->get('sonata.basket');
        $basket->setCustomer($customer);

        if ($basket->countBasketElements() == 0) {
            return new RedirectResponse($this->generateUrl('sonata_basket_index'));
        }

        $addresses = $customer->getAddressesByType(AddressInterface::TYPE_DELIVERY);

        // Show address creation / selection form
        $form = $this->createForm('sonata_basket_address', null, array('addresses' => $addresses, 'type' => AddressInterface::TYPE_DELIVERY));
        $template = 'SonataBasketBundle:Basket:delivery_address_step.html.twig';

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                if ($form->has('useSelected') && $form->get('useSelected')->isClicked()) {
                    $address = $form->get('addresses')->getData();
                } else {
                    $address = $form->getData();
                    $address->setType(AddressInterface::TYPE_DELIVERY);

                    $customer->addAddress($address);

                    $this->get('sonata.customer.manager')->save($customer);

                    $this->get('session')->getFlashBag()->add('sonata_customer_success', 'address_add_success');
                }

                $basket->setCustomer($customer);
                $basket->setDeliveryAddress($address);
                // save the basket
                $this->get('sonata.basket.factory')->save($basket);

                return new RedirectResponse($this->generateUrl('sonata_basket_delivery'));
            }
        }

        // Set URL to be redirected to once edited address
        $this->get('session')->set('sonata_address_redirect', $this->generateUrl('sonata_basket_delivery_address'));

        $this->get('sonata.seo.page')->setTitle($this->get('translator')->trans('basket_delivery_title', array(), "SonataBasketBundle"));

        return $this->render($template, array(
            'form'      => $form->createView(),
            'addresses' => $addresses,
            'basket'    => $basket,
        ));
    }

    /**
     * Order process step 4: choose a billing address from existing ones or create a new one
     *
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function paymentAddressStepAction()
    {
        $basket = $this->get('sonata.basket');

        if ($basket->countBasketElements() == 0) {
            return new RedirectResponse($this->generateUrl('sonata_basket_index'));
        }

        $customer = $basket->getCustomer();

        if (!$customer) {
            throw new NotFoundHttpException('customer not found');
        }

        $addresses = $customer->getAddressesByType(AddressInterface::TYPE_BILLING);

        // Show address creation / selection form
        $form = $this->createForm('sonata_basket_address', null, array('addresses' => $addresses->toArray(), 'type' => AddressInterface::TYPE_BILLING));
        $template = 'SonataBasketBundle:Basket:payment_address_step.html.twig';

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                if ($form->has('useSelected') && $form->get('useSelected')->isClicked()) {
                    $address = $form->get('addresses')->getData();
                } else {
                    $address = $form->getData();
                    $address->setType(AddressInterface::TYPE_BILLING);

                    $cf = $address->getCodiceFiscale();
                    $pIva = $address->getPartitaIva();

                    if ($this->ControllaCF($cf) || $this->controllaPIVA($pIva)) {

                        if ($cf != null) {
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
                                $this->get('session')->set('sonata_address_redirect', $this->generateUrl('sonata_basket_payment_address'));

                                $this->get('sonata.seo.page')->setTitle($this->get('translator')->trans('basket_payment_title', array(), "SonataBasketBundle"));

                                return $this->render($template, array(
                                    'form'      => $form->createView(),
                                    'addresses' => $addresses
                                ));
                            } elseif ($this->controllaPIVA($pIva) || $pIva == null) {
                                try {
                                    $customer->addAddress($address);
                                    $this->get('sonata.customer.manager')->save($customer);
                                } catch (UniqueConstraintViolationException $e) {
                                    $this->get('session')->getFlashBag()->add('sonata_customer_success', "La partita iva inserita esiste già");

                                    $this->get('sonata.seo.page')->setTitle($this->get('translator')->trans('basket_payment_title', array(), "SonataBasketBundle"));

                                    return $this->render($template, array(
                                        'form'      => $form->createView(),
                                        'addresses' => $addresses
                                    ));
                                }

                                $this->get('session')->getFlashBag()->add('sonata_customer_success', 'address_add_success');
                            } else {
                                $this->get('session')->getFlashBag()->add('sonata_customer_success', "Partita Iva errata");
                                $this->get('session')->set('sonata_address_redirect', $this->generateUrl('sonata_basket_payment_address'));

                                $this->get('sonata.seo.page')->setTitle($this->get('translator')->trans('basket_payment_title', array(), "SonataBasketBundle"));

                                return $this->render($template, array(
                                    'form'      => $form->createView(),
                                    'addresses' => $addresses
                                ));
                            }
                        } else {
                            try {
                                $customer->addAddress($address);
                                $this->get('sonata.customer.manager')->save($customer);
                            } catch (UniqueConstraintViolationException $e) {
                                $this->get('session')->getFlashBag()->add('sonata_customer_success', "La partita iva inserita esiste già");

                                $this->get('sonata.seo.page')->setTitle($this->get('translator')->trans('basket_payment_title', array(), "SonataBasketBundle"));

                                return $this->render($template, array(
                                    'form'      => $form->createView(),
                                    'addresses' => $addresses
                                ));
                            }

                            $this->get('session')->getFlashBag()->add('sonata_customer_success', 'address_add_success');
                        }
                    } else {
                        if (!$this->ControllaCF($cf) && $cf != null){
                            $this->get('session')->getFlashBag()->add('sonata_customer_success', 'Codice fiscale errato');
                        }

                        if (!$this->controllaPIVA($pIva) && $pIva != null){
                            $this->get('session')->getFlashBag()->add('sonata_customer_success', 'Partita iva errata');
                        }

                        if ($cf == null && $pIva == null){
                            $this->get('session')->getFlashBag()->add('sonata_customer_success', 'Inserire Codice Fiscale o Partita iva');
                        }

                        $this->get('session')->set('sonata_address_redirect', $this->generateUrl('sonata_basket_payment_address'));

                        $this->get('sonata.seo.page')->setTitle($this->get('translator')->trans('basket_payment_title', array(), "SonataBasketBundle"));

                        return $this->render($template, array(
                            'form'      => $form->createView(),
                            'addresses' => $addresses
                        ));
                    }


                }

                $basket->setCustomer($customer);
                $basket->setBillingAddress($address);
                // save the basket
                $this->get('sonata.basket.factory')->save($basket);

                return new RedirectResponse($this->generateUrl('sonata_basket_payment'));
            }
        }

        // Set URL to be redirected to once edited address
        $this->get('session')->set('sonata_address_redirect', $this->generateUrl('sonata_basket_payment_address'));

        $this->get('sonata.seo.page')->setTitle($this->get('translator')->trans('basket_payment_title', array(), "SonataBasketBundle"));

        return $this->render($template, array(
            'form'      => $form->createView(),
            'addresses' => $addresses
        ));
    }

    /**
     * Order process step 6: order's review & conditions acceptance checkbox
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function finalReviewStepAction()
    {
        $basket = $this->get('sonata.basket');

        $violations = $this
            ->get('validator')
            ->validate($basket, array('elements', 'delivery', 'payment'));

        if ($violations->count() > 0) {
            // basket not valid

            // todo : add flash message rendering in template
            foreach ($violations as $violation) {
                $this->get('session')->getFlashBag()->add('error', 'Error: '.$violation->getMessage());
            }

            return new RedirectResponse($this->generateUrl('sonata_basket_index'));
        }

        if ($this->get('request')->getMethod() == 'POST' ) {
            if ($this->getUser()->getTerms()) {

                return $this->forward('SonataPaymentBundle:Payment:sendbank');

            } elseif ($this->get('request')->get('tac')){

                $em = $this->getDoctrine()->getManager();

                $user = $this->getDoctrine()->getRepository('ApplicationSonataUserBundle:User')->find($this->getUser()->getId());
                $user->setTerms(true);

                $em->persist($user);
                $em->flush();

                // send the basket to the payment callback
                return $this->forward('SonataPaymentBundle:Payment:sendbank');
            }
        }

        $this->get('sonata.seo.page')->setTitle($this->get('translator')->trans('basket_review_title', array(), "SonataBasketBundle"));

        return $this->render('SonataBasketBundle:Basket:final_review_step.html.twig', array(
            'basket'    => $basket,
            'tac_error' => $this->get('request')->getMethod() == 'POST'
        ));
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
