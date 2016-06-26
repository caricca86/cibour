<?php
/*
 * This file is part of the sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\ProductBundle\Provider;

use Application\Sonata\ProductBundle\Entity\Prodotto;
use Application\Sonata\ProductBundle\Entity\Product;
use JMS\Serializer\SerializerInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Component\Basket\BasketElementInterface;
use Sonata\Component\Delivery\ServiceDeliveryInterface;
use Sonata\Component\Form\Transformer\QuantityTransformer;
use Sonata\Component\Order\OrderElementInterface;
use Sonata\Component\Order\OrderInterface;
use Sonata\Component\Product\ProductInterface;
use Sonata\ProductBundle\Model\BaseProductProvider;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * This file has been generated by the EasyExtends bundle ( http://sonata-project.org/easy-extends )
 *
 * References :
 *   custom repository : http://www.doctrine-project.org/projects/orm/2.0/docs/reference/working-with-objects/en#querying:custom-repositories
 *   query builder     : http://www.doctrine-project.org/projects/orm/2.0/docs/reference/query-builder/en
 *   dql               : http://www.doctrine-project.org/projects/orm/2.0/docs/reference/dql-doctrine-query-language/en
 *
 * @author <yourname> <youremail>
 */
class ProdottoProductProvider extends BaseProductProvider
{
    /**
     * {@inheritdoc}
     */
    public function __construct(SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->setOptions(array(
            'product_add_modal' => true
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseControllerName()
    {
        return 'SonataProductBundle:Prodotto';
    }

    public function createOrderElement(BasketElementInterface $basketElement, $format = 'json')
    {
        /** @var OrderElementInterface $orderElement */
        $orderElement = new $this->orderElementClassName;
        $orderElement->setQuantity($basketElement->getQuantity());
        $orderElement->setUnitPriceExcl($basketElement->getUnitPrice(false));
        $orderElement->setUnitPriceInc($basketElement->getUnitPrice(true));
        $orderElement->setPrice($basketElement->getPrice(true));
        $orderElement->setVatRate($basketElement->getVatRate());
        $orderElement->setDesignation($basketElement->getName());
        $orderElement->setProductType($this->getCode());
        $orderElement->setStatus(OrderInterface::STATUS_PENDING);
        $orderElement->setDeliveryStatus(ServiceDeliveryInterface::STATUS_OPEN);
        $orderElement->setCreatedAt(new \DateTime);
        $orderElement->setOptions($basketElement->getOptions());

        $product = $basketElement->getProduct();
        $orderElement->setProduct($product);
        $orderElement->setDescription($product->getDescription());
        $orderElement->setProductId($product->getId());
        $orderElement->setRawProduct($this->getRawProduct($product, $format));
        $orderElement->setProductSku($basketElement->getProduct()->getSku());

        return $orderElement;
    }

    public function buildEditForm(FormMapper $formMapper, $isVariation = false)
    {
        $formMapper->with('Product');

        $formMapper->add('enabled');

        $formMapper
            ->add('name')
            ->add('sku')
            ->add('produttore')
            ->add('price', 'number')
            ->add('priceIncludingVat')
            ->add('vatRate', 'number')
            ->add('stock', 'integer')
            ->add('macroregione', 'choice', array(
                'choices' => Prodotto::getMacroregioneList()))
            ->add('pezzatura')
            ->add('tipo_dieta', 'choice', array(
                'choices' => Prodotto::getTipoDietaList(),
                'required' => false))
            ->add('superfood')
            ->add('superfood_description')
            ->add('gluten_free')
            ->add('caratteristica_gustativa')
            ->add('abbinamento')
        ;

        if (!$isVariation || in_array('description', $this->variationFields)) {
            $formMapper->add('description', 'sonata_formatter_type', array(
                'source_field'         => 'rawDescription',
                'source_field_options' => array('attr' => array('class' => 'span10', 'rows' => 20)),
                'format_field'         => 'descriptionFormatter',
                'target_field'         => 'description',
                'event_dispatcher'     => $formMapper->getFormBuilder()->getEventDispatcher()
            ));
        }

        if (!$isVariation || in_array('short_description', $this->variationFields)) {
            $formMapper->add('shortDescription', 'sonata_formatter_type', array(
                'source_field'         => 'rawShortDescription',
                'source_field_options' => array('attr' => array('class' => 'span10', 'rows' => 20)),
                'format_field'         => 'shortDescriptionFormatter',
                'target_field'         => 'shortDescription',
                'event_dispatcher'     => $formMapper->getFormBuilder()->getEventDispatcher()
            ));
        }
        $formMapper->add('metodo_produzione', 'choice', array(
            'choices' => Prodotto::getMetodoProduzioneList(),
            'required' => false));
        $formMapper->end();

        $formMapper->with('Pentagramma');
        $formMapper
            ->add('alimentazione')
            ->add('agricoltura')
            ->add('ambiente')
            ->add('artigianato')
            ->add('arte');
        $formMapper->end();


        if (!$isVariation || in_array('image', $this->variationFields) || in_array('gallery', $this->variationFields)) {
            $formMapper->with('Media');

            if (!$isVariation || in_array('image', $this->variationFields)) {
                $formMapper->add('image', 'sonata_type_model_list', array(
                    'required' => false), array('link_parameters' => array('context' => 'sonata_product'))
                );
            }

            if (!$isVariation || in_array('gallery', $this->variationFields)) {
                $formMapper->add('gallery', 'sonata_type_model_list', array(
                    'required' => false), array('link_parameters' => array('context' => 'sonata_product'))
                );
            }

            $formMapper->end();
        }
    }

    /**
     * This function return the form used in the product view page
     *
     * @param  \Sonata\Component\Product\ProductInterface $product      A Sonata product instance
     * @param  \Symfony\Component\Form\FormBuilder        $formBuilder  Symfony form builder
     * @param  boolean                                    $showQuantity Specifies if quantity field will be displayed (default true)
     * @param  array                                      $options      An options array
     * @return void
     */
    public function defineAddBasketForm(ProductInterface $product, FormBuilder $formBuilder, $showQuantity = false, array $options = array())
    {
        $basketElement = $this->createBasketElement($product);

        // create the product form
        $formBuilder
            ->setData($basketElement)
            ->add('productId', 'hidden');

        if ($showQuantity) {
            $formBuilder->add('quantity', 'integer');
        } else {
            $transformer = new QuantityTransformer();
            $formBuilder->add(
                $formBuilder->create('quantity', 'hidden', array('data' => 1))
                    ->addModelTransformer($transformer)
            );
        }
    }
}
