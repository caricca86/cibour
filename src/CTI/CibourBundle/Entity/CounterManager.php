<?php

namespace CTI\CibourBundle\Entity;

use Application\Sonata\ProductBundle\Entity\Product;
use Sonata\CoreBundle\Model\BaseEntityManager;


class CounterManager extends BaseEntityManager
{
    public function addViewToProduct(Product $product)
    {
        $counter =  $this->getRepository()->findOneBy(array('product' => $product));

        if (!$counter) {
            $em = $this->getEntityManager();
            $counter = new Counter();
            $counter->setProduct($product);
        }

        $counter->addView();

        $this->save($counter);
    }

    public function addSaleToProduct(Product $product)
    {
        $counter =  $this->getRepository()->findOneBy(array('product' => $product));

        if (!$counter) {
            $em = $this->getEntityManager();
            $counter = new Counter();
            $counter->setProduct($product);
        }

        $counter->addSale();

        $this->save($counter);
    }
}