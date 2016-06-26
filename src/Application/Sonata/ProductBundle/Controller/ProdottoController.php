<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\ProductBundle\Controller;

use Sonata\ProductBundle\Controller\BaseProductController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Overwrite methods from the BaseProductController if you want to change the behavior
 * for the current product
 *
 */
class ProdottoController extends BaseProductController
{
    /**
     * @param $product
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function viewAction($product)
    {
        if (!is_object($product)) {
            throw new NotFoundHttpException('invalid product instance');
        }

        $provider = $this->get('sonata.product.pool')->getProvider($product);

        $formBuilder = $this->get('form.factory')->createNamedBuilder('add_basket', 'form', null, array('data_class' => $this->container->getParameter('sonata.basket.basket_element.class'), 'csrf_protection' => false));
        $provider->defineAddBasketForm($product, $formBuilder);

        $form = $formBuilder->getForm()->createView();

        $currency = $this->get('sonata.price.currency.detector')->getCurrency();

        // Add twitter/FB metadata
        $this->updateSeoMeta($product, $currency);

        $this->get('cibour.counter.manager')->addViewToProduct($product);

        return $this->render(
            sprintf('%s:view.html.twig', $provider->getBaseControllerName()),
            array(
                'provider'      => $provider,
                'product'       => $product,
                'cheapest_variation' => $provider->getCheapestEnabledVariation($product),
                'currency'      => $currency,
                'form'          => $form,
            )
        );
    }

    /**
     * @param $product
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function viewAjaxAction($product)
    {
        if (!is_object($product)) {
            throw new NotFoundHttpException('invalid product instance');
        }

        $provider = $this->get('sonata.product.pool')->getProvider($product);

        $formBuilder = $this->get('form.factory')->createNamedBuilder('add_basket', 'form', null, array('data_class' => $this->container->getParameter('sonata.basket.basket_element.class'), 'csrf_protection' => false));
        $provider->defineAddBasketForm($product, $formBuilder);

        $form = $formBuilder->getForm()->createView();

        $currency = $this->get('sonata.price.currency.detector')->getCurrency();

        // Add twitter/FB metadata
        $this->updateSeoMeta($product, $currency);

        $this->get('cibour.counter.manager')->addViewToProduct($product);

        return $this->render(
            sprintf('%s:view.html.twig', $provider->getBaseControllerName()),
            array(
                'provider'      => $provider,
                'product'       => $product,
                'cheapest_variation' => $provider->getCheapestEnabledVariation($product),
                'currency'      => $currency,
                'form'          => $form,
            )
        );
    }

}
