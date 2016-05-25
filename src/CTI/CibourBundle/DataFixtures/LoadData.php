<?php

namespace CTI\CibourBundle\DataFixtures;

use Application\Sonata\ClassificationBundle\Entity\Category;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for ($i=1; $i++; $i<=10)
        {
            $category = new Category();
            $category->setName('Categoria '.$i);
            $manager->persist($category);
        }

        $manager->flush();
    }
}