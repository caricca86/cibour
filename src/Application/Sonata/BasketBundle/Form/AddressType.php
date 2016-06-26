<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\BasketBundle\Form;

use Sonata\Component\Basket\BasketInterface;

use Sonata\BasketBundle\Form\AddressType as BaseAddressType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Hugo Briand <briand@ekino.com>
 *
 * Address form type (used for deliveryAddressStep in Order process)
 */
class AddressType extends BaseAddressType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $addresses = $options['addresses'];

        if (count($addresses) > 0) {
            $defaultAddress = current($addresses);

            foreach ($addresses as $address) {
                if ($address->getCurrent()) {
                    $defaultAddress = $address;
                    break;
                }
            }

            $builder->add('addresses', 'entity', array(
                    'choices'  => $addresses,
                    'preferred_choices' => array($defaultAddress),
                    'class'    => $this->addressClass,
                    'property' => 'addressArrayForRender',
                    'expanded' => true,
                    'multiple' => false,
                    'mapped'   => false,
                ))
                ->add('useSelected', 'submit', array(
                        'attr' => array(
                            'class' => 'btn btn-primary',
                            'style' => 'margin-bottom:20px;'
                        ),
                        'translation_domain' => 'SonataBasketBundle',
                        'validation_groups'  => false
                    )
                );
        } else {
            $builder->add('name', null, array('required' => !count($addresses)));

            if (isset($options['types'])) {
                $builder->add('type', 'choice', array(
                        'choices' => $options['types'],
                        'translation_domain' => 'SonataCustomerBundle')
                );
            }

            $builder
                ->add('firstname', null, array('required' => !count($addresses)))
                ->add('lastname', null, array('required' => !count($addresses)))
                ->add('codice_fiscale', null, array('required' => false))
                ->add('partita_iva', null, array('required' => false))
                ->add('address1', null, array('required' => !count($addresses)))
                ->add('address2')
                ->add('postcode', null, array('required' => !count($addresses)))
                ->add('city', null, array('required' => !count($addresses)))
                ->add('provincia', null, array('required' => !count($addresses)))
                ->add('phone')
            ;

            $countries = $this->getBasketDeliveryCountries();

            $countryOptions = array('required' => !count($addresses));

            if (count($countries) > 0) {
                $countryOptions['choices'] = $countries;
            }

            $builder->add('countryCode', 'country', $countryOptions);
        }


    }

    /**
     * Returns basket elements delivery countries
     *
     * @return array
     */
    protected function getBasketDeliveryCountries()
    {
        $countries = array();

        foreach ($this->basket->getBasketElements() as $basketElement) {
            $product = $basketElement->getProduct();

            foreach ($product->getDeliveries() as $delivery) {
                $code = $delivery->getCountryCode();

                if (!isset($countries[$code])) {
                    $countries[$code] = Intl::getRegionBundle()->getCountryName($code);
                }
            }
        }

        return $countries;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  => $this->addressClass,
            'addresses'   => array(),
            'validation_groups' => array('front'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_basket_address';
    }
}
