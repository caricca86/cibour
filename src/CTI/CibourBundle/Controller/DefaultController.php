<?php

namespace CTI\CibourBundle\Controller;

use Application\Sonata\ProductBundle\Entity\Delivery;
use CTI\CibourBundle\Entity\Counter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/init", name="init")
     * @Template()
     */
    public function initAction()
    {
        $em = $this->getDoctrine()->getManager();
        $prodotti = $em->getRepository('ApplicationSonataProductBundle:Prodotto')->findAll();
        foreach ($prodotti as $prodotto)
        {
            $prodotto->setEnabled(true);
            $prodotto->setCounter(new Counter());
            $prodotto->setStock(10);
            $delivery = new Delivery();
            $delivery->setProduct($prodotto);
            $delivery->setCode('one_day');
            $delivery->setCountryCode('IT');
            $delivery->setEnabled(true);
            $delivery->setZone('Italy');
            $delivery2 = new Delivery();
            $delivery2->setProduct($prodotto);
            $delivery2->setCode('one_day_take_away');
            $delivery2->setCountryCode('IT');
            $delivery2->setEnabled(true);
            $delivery2->setZone('Italy');
            $delivery3 = new Delivery();
            $delivery3->setProduct($prodotto);
            $delivery3->setCode('three_days_take_away');
            $delivery3->setCountryCode('IT');
            $delivery3->setEnabled(true);
            $delivery3->setZone('Italy');
            $delivery4 = new Delivery();
            $delivery4->setProduct($prodotto);
            $delivery4->setCode('three_days');
            $delivery4->setCountryCode('IT');
            $delivery4->setEnabled(true);
            $delivery4->setZone('Italy');
            $delivery5 = new Delivery();
            $delivery5->setProduct($prodotto);
            $delivery5->setCode('seven_days_take_away');
            $delivery5->setCountryCode('IT');
            $delivery5->setEnabled(true);
            $delivery5->setZone('Italy');
            $delivery6 = new Delivery();
            $delivery6->setProduct($prodotto);
            $delivery6->setCode('sevens_day');
            $delivery6->setCountryCode('IT');
            $delivery6->setEnabled(true);
            $delivery6->setZone('Italy');
            $em->persist($prodotto);
            $em->persist($delivery);
            $em->persist($delivery2);
            $em->persist($delivery3);
            $em->persist($delivery4);
            $em->persist($delivery5);
            $em->persist($delivery6);
        }

        $em->flush();

        return $this->redirect($this->generateUrl('homepage'));
    }
}
