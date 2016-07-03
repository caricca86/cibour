<?php

namespace CTI\CibourBundle\Entity;

use Application\Sonata\ProductBundle\Entity\Product;
use Sonata\CoreBundle\Model\BaseEntityManager;


class CounterManager extends BaseEntityManager
{
    public function addViewToProduct(Product $product)
    {
        $counter =  $product->getCounter();

        if (!$counter) {
            $em = $this->getEntityManager();
            $counter = new Counter();
            $product->setCounter($counter);
        }

        $counter->addView();

        $this->save($counter);
    }

    public function addSaleToProduct(Product $product)
    {
        $counter =  $product->getCounter();

        if (!$counter) {
            $em = $this->getEntityManager();
            $counter = new Counter();
            $product->setCounter($counter);
        }

        $counter->addSale();

        $this->save($counter);
    }
}