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
     *      201="Returned when successful",
     *      400="Returned when form validation error occurs",
     *      403="Returned when user don't have authorization"
     *  }
     * )
     * @Rest\Post("/produttori")
     * @Rest\View(statusCode=400)
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
     *      204="Returned when successful",
     *      400="Returned when form validation error occurs",
     *      403="Returned when user don't have authorization",
     *      404="Returned when entity is not found"
     *  }
     * )
     * @Rest\Put("/produttori/{id}")
     * @Rest\View(statusCode=400)
     */
    public function putProduttoreAction($id)
    {
        $entity = $this->getEntity($id);

        return $this->processForm($entity);
    }

    private function processForm(Produttore $entity)
    {
        $statusCode = ($entity->getId() === null) ? 201 : 204;

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new ProduttoreType(), $entity, array('csrf_protection' => false));

        $form->submit($this->getRequest());

        if ($form->isValid()) {

            $em->persist($entity);
            $em->flush();

            $view = View::create();

            $response = new Response();
            $response->setStatusCode($statusCode);

            $view->setResponse($response);

            $view->setData(array('produttore' => $entity));

            return $view;
        }

        return array('' => $form);
    }

    /**
     * @param $id
     * @return Produttore
     */
    private function getEntity($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CTICibourBundle:Produttore')->find($id);

        if(!$entity)
        {
            throw $this->createNotFoundException('Produttore non trovato');
        }

        return $entity;
    }
}