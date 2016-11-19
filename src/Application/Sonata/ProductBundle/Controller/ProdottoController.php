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

use Sonata\Component\Basket\BasketElementInterface;
use Sonata\Component\Basket\BasketInterface;
use Sonata\ProductBundle\Controller\BaseProductController;
use Symfony\Component\Form\FormView;
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
    public function viewAction($product, $categoryList = false)
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

        $template = sprintf('%s:view.html.twig', $provider->getBaseControllerName());
        if ($categoryList){
            $template = 'CTICibourBundle:Default:categoryList_inEvidenza.html.twig';
        }
        return $this->render(
            $template,
            array(
                'provider'      => $provider,
                'product'       => $product,
                'cheapest_variation' => $provider->getCheapestEnabledVariation($product),
                'currency'      => $currency,
                'form'          => $form,
                'category_list'  => $categoryList,
                'ajax_call'     => $this->getRequest()->isXmlHttpRequest()
            )
        );
    }

    /**
     * @param FormView               $formView
     * @param BasketElementInterface $basketElement
     * @param BasketInterface        $basket
     *
     * @return Response
     */
    public function renderFormBasketElementAction(FormView $formView, BasketElementInterface $basketElement, BasketInterface $basket, $modal = false)
    {
        $view = 'ApplicationSonataProductBundle:Product:form_basket_element.html.twig';
        if ($modal) {
            $view = 'ApplicationSonataProductBundle:Product:form_basket_element_modal.html.twig';
        }

        return $this->render(sprintf($view), array(
            'formView'      => $formView,
            'basketElement' => $basketElement,
            'basket'        => $basket,
            'modal'         => $modal
        ));
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
                'category_list' => 0,
                'ajax_call'     => 1
            )
        );
    }

}
