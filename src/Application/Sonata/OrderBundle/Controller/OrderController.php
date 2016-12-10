<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\OrderBundle\Controller;

use Sonata\Component\Order\OrderElementInterface;
use Sonata\Component\Order\OrderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sonata\OrderBundle\Controller\OrderController as BaseController;
use Sonata\Component\Order\OrderManagerInterface;
use Sonata\Component\Customer\CustomerInterface;

class OrderController extends BaseController
{
    /**
     * @param string $reference
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws AccessDeniedException
     */
    public function viewAction($reference)
    {
        /** @var OrderInterface $order */
        $order = $this->getOrderManager()->findOneBy(array('reference' => $reference));

        if (null === $order) {
            throw new AccessDeniedException();
        }

        $this->checkAccess($order->getCustomer());

        $this->get('sonata.seo.page')->setTitle($this->get('translator')->trans('order_view_title', array(), "SonataOrderBundle"));

        /** @var OrderElementInterface $element */
        foreach ($order->getOrderElements() as $element) {
            $provider = $this->get('sonata.product.pool')->getProvider($element->getProductType());
            $element->setProduct($provider->getProductFromRaw($element, $this->get('sonata.product.pool')->getManager($element->getProductType())->getClass()));
        }

        return $this->render('SonataOrderBundle:Order:view.html.twig', array(
            'order'              => $order,
            'breadcrumb_context' => 'user_order',
        ));
    }

    public function payAction($reference)
    {
        /** @var OrderInterface $order */
        $order = $this->getOrderManager()->findOneBy(array('reference' => $reference));

        if (null === $order) {
            throw new AccessDeniedException();
        }

        $this->checkAccess($order->getCustomer());

        if ($order->getStatus() == OrderInterface::STATUS_STOPPED)
        {
            $basket = $this->get('sonata.basket');

            $transformer = $this->get('sonata.payment.transformer.order');
            $transformer->transformIntoBasket($order, $basket);

            return $this->forward('SonataPaymentBundle:Payment:sendbank');
        }

        $this->get('session')->getFlashBag()->add('error', 'Errore di sistema, impossibile procedere al pagamento');

        return $this->render('SonataOrderBundle:Order:view.html.twig', array(
            'order'              => $order,
            'breadcrumb_context' => 'user_order',
        ));
    }
}
