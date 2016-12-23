<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\PaymentBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Sonata\Component\Payment\InvalidTransactionException;
use Sonata\Component\Payment\PaymentHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Sonata\PaymentBundle\Controller\PaymentController as BaseController;

class PaymentController extends BaseController
{
    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function confirmationAction()
    {
        try {
            $order = $this->getPaymentHandler()->handleConfirmation($this->getRequest());
        } catch (EntityNotFoundException $ex) {
            throw new NotFoundHttpException($ex->getMessage());
        } catch (InvalidTransactionException $ex) {
            throw new UnauthorizedHttpException($ex->getMessage());
        }

        $this->container->get('logger')->critical('Ordine Valido? '.$order->isValidated().' Ordine Pendente? '.$order->isPending());

        if ($order->getPaymentMethod() != 'paypal') {
            if (!($order->isValidated() || $order->isPending())) {
                return $this->render('SonataPaymentBundle:Payment:error.html.twig', array(
                    'order' => $order,
                ));
            }
        }

        $this->container->get('logger')->critical('L\'ordine: '. $order->getReference().' è ok!!!!. Metodo pagamento: '.$order->getPaymentMethod());

        $counterManager = $this->get('cibour.counter.manager');
        $productManager = $this->get('cibour.product.prodotto.manager');

        foreach ($order->getOrderElements() as $orderElement)
        {
            $product = $productManager->find($orderElement->getProductId());
            $counterManager->addSaleToProduct($product);
        }
        $this->container->get('logger')->critical('Sto per inviare la mail per l\'ordine: '. $order->getReference().'. Metodo pagamento: '.$order->getPaymentMethod());

        $message = \Swift_Message::newInstance()
            ->setSubject('Conferma Ordine '.$order->getReference())
            ->setFrom(array('ecommerce@cibour.com' => 'Cibour'))
            ->setTo($this->getUser()->getEmail())
            ->setBcc('ecommerce@cibour.com')
            ->setBody(
                $this->container->get('templating')->render(
                // app/Resources/views/Emails/registration.html.twig
                    'CTICibourBundle:Mail:mail_conferma_ordine.html.twig',
                    array('order' => $order)
                ),
                'text/html'
            )
        ;
        $this->container->get('mailer')->send($message);

        $this->container->get('logger')->critical('Mail inviata per ordine: '. $order->getReference().'. Metodo pagamento: '.$order->getPaymentMethod());

        return $this->render('SonataPaymentBundle:Payment:confirmation.html.twig', array(
            'order' => $order,
        ));
    }

    /**
     * this action redirect the user to the bank
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function sendbankAction()
    {
        $basket = $this->getBasket();

        if ($this->get('request')->getMethod() !== 'POST') {
            return $this->redirect($this->generateUrl('sonata_basket_index'));
        }

        if (!$basket->isValid()) {
            $this->get('session')->getFlashBag()->set(
                'error',
                $this->container->get('translator')->trans('basket_not_valid', array(), 'SonataPaymentBundle')
            );

            return $this->redirect($this->generateUrl('sonata_basket_index'));
        }

        $payment = $basket->getPaymentMethod();

        // check if the basket is valid/compatible with the bank gateway
        if (!$payment->isBasketValid($basket)) {
            $this->get('session')->getFlashBag()->set(
                'error',
                $this->container->get('translator')->trans('basket_not_valid_with_current_payment_method', array(), 'SonataPaymentBundle')
            );

            return $this->redirect($this->generateUrl('sonata_basket_index'));
        }



        // transform the basket into order
        $order = $this->getPaymentHandler()->getSendbankOrder($basket);

        $fattura = $this->get('request')->request->get('fattura');
        $regalo = $this->get('request')->request->get('regalo');

        if ($fattura == 'on') {
            $order->setFattura(true);
        } else {
            $order->setFattura(false);
        }

        if ($regalo == 'on') {
            $order->setRegalo(true);
        } else {
            $order->setRegalo(false);
        }

        $this->getBasketFactory()->reset($basket);

        // the payment must handle everything when calling the bank
        return $payment->sendbank($order);
    }
}
