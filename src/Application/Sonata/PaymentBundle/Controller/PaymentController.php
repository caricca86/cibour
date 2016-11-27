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

        if (!($order->isValidated() || $order->isPending())) {
            return $this->render('SonataPaymentBundle:Payment:error.html.twig', array(
                'order' => $order,
            ));
        }

        $counterManager = $this->get('cibour.counter.manager');
        $productManager = $this->get('cibour.product.prodotto.manager');

        foreach ($order->getOrderElements() as $orderElement)
        {
            $product = $productManager->find($orderElement->getProductId());
            $counterManager->addSaleToProduct($product);
        }

        $message = \Swift_Message::newInstance()
            ->setSubject('Conferma Ordine '.$order->getReference())
            ->setFrom(array('ecommerce@cibour.com' => 'Cibour'))
            ->setTo($this->getUser()->getEmail())
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

        return $this->render('SonataPaymentBundle:Payment:confirmation.html.twig', array(
            'order' => $order,
        ));
    }
}
