<?php
/**
 * Created by PhpStorm.
 * User: WEB
 * Date: 01/06/2016
 * Time: 15:33
 */

namespace CTI\CibourBundle\Controller\Api;


use CTI\CibourBundle\Entity\Produttore;
use CTI\CibourBundle\Form\ProduttoreType;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProduttoreApiController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  resource="/api/ecommerce/produttori",
     *  description="Ritorna tutti i produttori",
     *  statusCodes={
     *      201="Returned when successful",
     *      403="Returned when user don't have authorization"
     *  }
     * )
     * @Rest\Get("/produttori")
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     */
    public function getProduttoriAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('CTICibourBundle:Produttore')->findAll();

        return array('produttori' => $entities);
    }

    /**
     * @ApiDoc(
     *  resource="/api/ecommerce/produttori",
     *  description="Ritorna un singolo produttore",
     *  requirements={
     *      {
     *      "name"="id",
     *      "dataType"="integer",
     *      "requirement"="\d+",
     *      "description"="id del materiale probatorio"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when user don't have authorization",
     *      404="Returned when entity is not found"
     *  }
     * )
     *
     * @Rest\Get("/produttori/{id}", requirements={"id" = "\d+"})
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     */
    public function getProduttoreAction($id)
    {
        $entity = $this->getEntity($id);

        return array('produttore' => $entity);
    }

    /**
     * @ApiDoc(
     *  resource="/api/ecommerce/produttori",
     *  description="Crea un nuovo produttore",
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when form validation error occurs",
     *      403="Returned when user don't have authorization"
     *  }
     * )
     * @Rest\Post("/produttori")
     * @Rest\View(statusCode=200)
     */
    public function postProduttoreAction()
    {
        $entity = new Produttore();

        return $this->processForm($entity);
    }

    /**
     * @ApiDoc(
     *  description="Modifica un produttore",
     *  input="CTI\CibourBundle\Entity\Produttore",
     *  requirements={
     *      {
     *      "name"="id",
     *      "dataType"="integer",
     *      "requirement"="\d+",
     *      "description"="id del produttore",
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when form validation error occurs",
     *      403="Returned when user don't have authorization",
     *      404="Returned when entity is not found"
     *  }
     * )
     * @Rest\Put("/produttori/{id}")
     * @Rest\View(statusCode=200)
     */
    public function putProduttoreAction($id)
    {
        $entity = $this->getEntity($id);

        return $this->processForm($entity);
    }

    /**
     * Deletes a product
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="product identifier"}
     *  },
     *  statusCodes={
     *      200="Returned when post is successfully deleted",
     *      400="Returned when an error has occurred while product deletion",
     *      404="Returned when unable to find product"
     *  }
     * )
     *
     * @param integer $id A Product identifier
     *
     * @return \FOS\RestBundle\View\View
     *
     * @throws NotFoundHttpException
     * @Rest\Delete("/produttori/{id}")
     * @Rest\View(statusCode=200)
     */
    public function deleteProduttoreAction($id)
    {
        $produttore = $this->getEntity($id);
        $em = $this->getDoctrine()->getManager();

        try {
            $em->remove($produttore);
            $em->flush();
        } catch (\Exception $e) {
            return View::create(array('error' => $e->getMessage()), 400);
        }

        return array('deleted' => true);
    }

    private function processForm(Produttore $entity)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new ProduttoreType(), $entity, array('csrf_protection' => false));

        $form->submit($this->getRequest());

        if ($form->isValid()) {

            $em->persist($entity);
            $em->flush();

            $view = View::create();

            $response = new Response();

            $view->setResponse($response);

            $view->setData(array('produttore' => $entity));

            return $view;
        }

        $view = View::create();

        $response = new Response();

        $view->setResponse($response);

        $view->setData(array('' => $form));
        $view->setStatusCode(400);

        return $view;
    }

    /**
     * @param $id
     * @return Produttore
     */
    private function getEntity($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CTICibourBundle:Produttore')->findOneBy(array('codice' => $id));

        if(!$entity)
        {
            throw $this->createNotFoundException('Produttore non trovato');
        }

        return $entity;
    }
}