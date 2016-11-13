<?php

namespace CTI\CibourBundle\Controller;

use Application\Sonata\ProductBundle\Entity\Delivery;
use CTI\CibourBundle\Entity\Counter;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\Component\Currency\CurrencyDetector;
use Sonata\ProductBundle\Entity\ProductSetManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @Route("/privacy", name="privacy")
     * @Template()
     */
    public function privacyAction()
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
            $delivery->setCode('dhl');
            $delivery->setCountryCode('IT');
            $delivery->setEnabled(true);
            $delivery->setZone('Italy');
            $delivery2 = new Delivery();
            $delivery2->setProduct($prodotto);
            $delivery2->setCode('take_away');
            $delivery2->setCountryCode('IT');
            $delivery2->setEnabled(true);
            $delivery2->setZone('Italy');
            $em->persist($prodotto);
            $em->persist($delivery);
            $em->persist($delivery2);
        }

        $em->flush();

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * @Route("/shop/catalog/category/{categorySlug}/{categoryId}/{prodottoSlug}/{prodottoId}", name="catalog_category")
     * @Template()
     */
    public function categoryListAction($categorySlug, $categoryId, $prodottoSlug = null, $prodottoId = null)
    {
        $page        = $this->getRequest()->get('page', 1);
        $displayMax  = $this->getRequest()->get('max', 9);
        $displayMode = $this->getRequest()->get('mode', 'grid');
        $filter      = $this->getRequest()->get('filter');
        $option      = $this->getRequest()->get('option');

        if (!in_array($displayMode, array('grid'))) { // "list" mode will be added later
            throw new NotFoundHttpException(sprintf('Given display_mode "%s" doesn\'t exist.', $displayMode));
        }

        $category = $this->getCategoryManager()->findOneBy(array(
            'id'      => $categoryId,
            'enabled' => true,
        ));

        $this->get('sonata.seo.page')->setTitle($category ? $category->getName() : $this->get('translator')->trans('catalog_index_title'));

        $prodottoRepository = $this->getDoctrine()->getManager()->getRepository('ApplicationSonataProductBundle:Prodotto');

        $sortByForm = $this->createFormBuilder()
            ->add('sortBy', 'choice', array(
                'choices' => array(
                    'trending-now' => 'Trending Now',
                    'recent' => 'Ultimi Arrivi',
                    'most-popular' => 'Most Popular'
                ),
                'label' => false,
            ))->getForm();

        $products = $prodottoRepository->findMostViewedProducts($categoryId, 200);

        $sortByForm->handleRequest($this->getRequest());

        if ($sortByForm->isSubmitted() && $sortByForm->isValid()) {
            $sortBy = $sortByForm->getData()['sortBy'];
            if ($sortBy == 'trending-now')
            {
                $products = $prodottoRepository->findMostSelledProducts($categoryId, 200);
            } elseif ($sortBy == 'recent'){
                $products = $prodottoRepository->findLastActiveProducts($categoryId, 200);
            }
        }

        if ($prodottoId != null)
        {
            $inEvidenza = $prodottoRepository->find($prodottoId);
        } else {
            $inEvidenza = $prodottoRepository->findLastActiveProducts($category->getId(), 1)[0];
        }
        return array(
            'display_mode' => $displayMode,
            'products'        => $products,
            'currency'     => $this->getCurrencyDetector()->getCurrency(),
            'category'     => $category,
            'provider'     => $this->getProviderFromCategory($category),
            'inEvidenza'    => $inEvidenza,
            'sortByForm'    => $sortByForm->createView()
        );
    }

    /**
     * @Route("/shop/catalog/trending-now/{prodottoSlug}/{prodottoId}", name="catalog_trending_now")
     * @Template()
     */
    public function trendingNowListAction($prodottoSlug = null, $prodottoId = null)
    {
        $page        = $this->getRequest()->get('page', 1);
        $displayMax  = $this->getRequest()->get('max', 9);
        $displayMode = $this->getRequest()->get('mode', 'grid');
        $filter      = $this->getRequest()->get('filter');
        $option      = $this->getRequest()->get('option');

        if (!in_array($displayMode, array('grid'))) { // "list" mode will be added later
            throw new NotFoundHttpException(sprintf('Given display_mode "%s" doesn\'t exist.', $displayMode));
        }

        $this->get('sonata.seo.page')->setTitle('Trending Now' );

        $prodottoRepository = $this->getDoctrine()->getManager()->getRepository('ApplicationSonataProductBundle:Prodotto');

        $products = $prodottoRepository->findMostSelledProducts(null, 200);

        if ($prodottoId != null)
        {
            $inEvidenza = $prodottoRepository->find($prodottoId);
        } else {
            $inEvidenza = $products[0];
        }
        return array(
            'display_mode' => $displayMode,
            'products'        => $products,
            'currency'     => $this->getCurrencyDetector()->getCurrency(),
            'inEvidenza'    => $inEvidenza,
            'sortByForm'    => null
        );
    }

    /**
     * @Route("/shop/catalog/recently-added/{prodottoSlug}/{prodottoId}", name="catalog_recently_added")
     * @Template()
     */
    public function ultimiArriviListAction($prodottoSlug = null, $prodottoId = null)
    {
        $page        = $this->getRequest()->get('page', 1);
        $displayMax  = $this->getRequest()->get('max', 9);
        $displayMode = $this->getRequest()->get('mode', 'grid');
        $filter      = $this->getRequest()->get('filter');
        $option      = $this->getRequest()->get('option');

        if (!in_array($displayMode, array('grid'))) { // "list" mode will be added later
            throw new NotFoundHttpException(sprintf('Given display_mode "%s" doesn\'t exist.', $displayMode));
        }

        $this->get('sonata.seo.page')->setTitle('Ultimi Arrivi' );

        $prodottoRepository = $this->getDoctrine()->getManager()->getRepository('ApplicationSonataProductBundle:Prodotto');

        $products = $prodottoRepository->findLastActiveProducts(null, 200);

        if ($prodottoId != null)
        {
            $inEvidenza = $prodottoRepository->find($prodottoId);
        } else {
            $inEvidenza = $products[0];
        }
        return array(
            'display_mode' => $displayMode,
            'products'        => $products,
            'currency'     => $this->getCurrencyDetector()->getCurrency(),
            'inEvidenza'    => $inEvidenza,
            'sortByForm'    => null
        );
    }

    /**
     * @Route("/shop/catalog/search/{prodottoSlug}/{prodottoId}", name="catalog_search")
     * @Template()
     */
    public function searchListAction($prodottoSlug = null, $prodottoId = null)
    {
        $page        = $this->getRequest()->get('page', 1);
        $displayMax  = $this->getRequest()->get('max', 9);
        $displayMode = $this->getRequest()->get('mode', 'grid');
        $filter      = $this->getRequest()->get('filter');
        $option      = $this->getRequest()->get('option');

        if (!in_array($displayMode, array('grid'))) { // "list" mode will be added later
            throw new NotFoundHttpException(sprintf('Given display_mode "%s" doesn\'t exist.', $displayMode));
        }

        $search = $this->get('request')->query->get('search');

        $this->get('sonata.seo.page')->setTitle('Ricerca su: '.$search);

        $prodottoRepository = $this->getDoctrine()->getManager()->getRepository('ApplicationSonataProductBundle:Prodotto');

        $products = $prodottoRepository->createQueryBuilder('p')
            ->where('p.name LIKE :search')
            ->orWhere('p.rawDescription LIKE :search')
            ->orWhere('p.rawShortDescription LIKE :search')
            ->andWhere('p.enabled = true')
            ->setParameter('search', "%$search%")
            ->getQuery()
            ->getResult();

        $sortByForm = $this->createFormBuilder()
            ->add('sortBy', 'choice', array(
                'choices' => array(
                    'trending-now' => 'Trending Now',
                    'recent' => 'Ultimi Arrivi',
                    'most-popular' => 'Most Popular'
                ),
                'label' => false,
            ))->getForm();

        /*$sortByForm->handleRequest($this->getRequest());

        if ($sortByForm->isSubmitted() && $sortByForm->isValid()) {
            $sortBy = $sortByForm->getData()['sortBy'];
            if ($sortBy == 'trending-now')
            {
                $products = $prodottoRepository->findMostSelledProducts($categoryId, 200);
            } elseif ($sortBy == 'recent'){
                $products = $prodottoRepository->findLastActiveProducts($categoryId, 200);
            }
        }*/

        if ($prodottoId != null)
        {
            $inEvidenza = $prodottoRepository->find($prodottoId);
        } else {
            $inEvidenza = $products[0];
        }
        return array(
            'display_mode' => $displayMode,
            'products'        => $products,
            'currency'     => $this->getCurrencyDetector()->getCurrency(),
            'inEvidenza'    => $inEvidenza,
            'sortByForm'    => null,
            'search'        => $search
        );
    }

    /**
     * @Route("/shop/catalog/most-popular/{prodottoSlug}/{prodottoId}", name="catalog_popular")
     * @Template()
     */
    public function mostPopularListAction($prodottoSlug = null, $prodottoId = null)
    {
        $page        = $this->getRequest()->get('page', 1);
        $displayMax  = $this->getRequest()->get('max', 9);
        $displayMode = $this->getRequest()->get('mode', 'grid');
        $filter      = $this->getRequest()->get('filter');
        $option      = $this->getRequest()->get('option');

        if (!in_array($displayMode, array('grid'))) { // "list" mode will be added later
            throw new NotFoundHttpException(sprintf('Given display_mode "%s" doesn\'t exist.', $displayMode));
        }

        $this->get('sonata.seo.page')->setTitle('Ultimi Arrivi' );

        $prodottoRepository = $this->getDoctrine()->getManager()->getRepository('ApplicationSonataProductBundle:Prodotto');

        $products = $prodottoRepository->findMostViewedProducts(null, 200);

        if ($prodottoId != null)
        {
            $inEvidenza = $prodottoRepository->find($prodottoId);
        } else {
            $inEvidenza = $products[0];
        }
        return array(
            'display_mode' => $displayMode,
            'products'        => $products,
            'currency'     => $this->getCurrencyDetector()->getCurrency(),
            'inEvidenza'    => $inEvidenza,
            'sortByForm'    => null
        );
    }

    /**
     * Retrieve Category from its id and slug, if any.
     *
     * @return CategoryInterface|null
     */
    protected function retrieveCategoryFromQueryString()
    {
        $categoryId   = $this->getRequest()->get('category_id');
        $categorySlug = $this->getRequest()->get('category_slug');

        if (!$categoryId || !$categorySlug) {
            return null;
        }

        return $this->getCategoryManager()->findOneBy(array(
            'id'      => $categoryId,
            'enabled' => true,
        ));
    }

    /**
     * Gets the product provider associated with $category if any
     *
     * @param CategoryInterface $category
     *
     * @return null|\Sonata\Component\Product\ProductProviderInterface
     */
    protected function getProviderFromCategory(CategoryInterface $category = null)
    {
        if (null === $category) {
            return null;
        }

        $product = $this->getProductSetManager()->findProductForCategory($category);

        return $product ? $this->getProductPool()->getProvider($product) : null;
    }

    /**
     * @return Pool
     */
    protected function getProductPool()
    {
        return $this->get('sonata.product.pool');
    }

    /**
     * @return ProductSetManager
     */
    protected function getProductSetManager()
    {
        return $this->get('sonata.product.set.manager');
    }

    /**
     * @return CurrencyDetector
     */
    protected function getCurrencyDetector()
    {
        return $this->get('sonata.price.currency.detector');
    }

    /**
     * @return CategoryManager
     */
    protected function getCategoryManager()
    {
        return $this->get('sonata.classification.manager.category');
    }
}
