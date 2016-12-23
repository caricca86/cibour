<?php

namespace CTI\CibourBundle\Controller;

use Application\Sonata\ProductBundle\Entity\Delivery;
use Application\Sonata\ProductBundle\Entity\Prodotto;
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
     * @Route("/storia", name="storia")
     * @Template()
     */
    public function storiaAction()
    {
        return array();
    }

    /**
     * @Route("/cibour", name="cibour")
     * @Template()
     */
    public function cibourAction()
    {
        return array();
    }

    /**
     * @Route("/init", name="init")
     * @Template()
     */
    public function initAction()
    {
        /*$em = $this->getDoctrine()->getManager();
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
            $prodotto->setAlimentazione(0);
            $prodotto->setAgricoltura(0);
            $prodotto->setAmbiente(0);
            $prodotto->setArte(0);
            $prodotto->setArtigianato(0);
            $em->persist($prodotto);
        }

        $em->flush(); */

        $em = $this->getDoctrine()->getManager();

        $order = $em->getRepository('ApplicationSonataOrderBundle:Order')->findOneByReference('161203000001');

        $message = \Swift_Message::newInstance()
            ->setSubject('Conferma Ordine '.$order->getReference())
            ->setFrom(array('ecommerce@cibour.com' => 'Cibour'))
            ->setTo($this->getUser()->getEmail())
            ->setBody(
                $this->container->get('templating')->render(
                // app/Resources/views/Emails/registration.html.twig
                    'CTICibourBundle:Mail:mail_conferma_ordine.html.twig',
                    array('order' => $order)
                ),
                'text/html'
            )
        ;
        $this->container->get('mailer')->send($message);

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
            $inEvidenza = $prodottoRepository->findLastActiveProducts($category->getId(), 1);

            if (count($inEvidenza) > 0){
                $inEvidenza = $inEvidenza[0];
            }
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
            $inEvidenza = array();

            if (count($products) > 0){
                $inEvidenza = $products[0];
            }
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
            $inEvidenza = array();

            if (count($products) > 0){
                $inEvidenza = $products[0];
            }
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
            $inEvidenza = array();

            if (count($products) > 0){
                $inEvidenza = $products[0];
            }
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
            $inEvidenza = array();

            if (count($products) > 0){
                $inEvidenza = $products[0];
            }
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
     * @Route("/shop/diventa-partner", name="partner")
     * @Template()
     */
    public function partnerAction()
    {
        if ($this->get('request')->getMethod() == 'POST')
        {
            $user_type = $_POST['user_type'];
            $nome = trim($_POST['nome']);
            $cognome = trim($_POST['cognome']);
            $rag_soc = trim($_POST['rag_soc']);
            $p_iva = trim($_POST['p_iva']);
            $email = trim($_POST['email']);
            $ip = $_SERVER['REMOTE_ADDR'];
            $phone = trim($_POST['phone']);
            $note = trim($_POST['note']);
            $sito = trim($_POST['sito']);

            $ok = true;
#controllo input obbligatori
            if(!isset($user_type))
            {
                $this->get('session')->getFlashBag()->add('error',
                    'Non hai scelto la tipologia utente.');

                $ok = false;
            }

            if(!isset($nome, $cognome))
            {
                $this->get('session')->getFlashBag()->add('error',
                    'I dati inviati non sono corretti. Inserire nome e cognome');

                $ok = false;
            }

            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $this->get('session')->getFlashBag()->add('error',
                    'Inserire un indirizzo email valido.');

                $ok = false;
            }

            if ($ok)
            {

                $destinatario = "riccardo.cartei7@gmail.com"; //"produttori@latavolaitaliana.org";
                $header = "From: Registrazione Produttore <registrazionenewsletter@latavolaitaliana.org>\r\n";
                $header .= "Cc: Registrazione Produttore <info@latavolaitaliana.org> \r\n";
                $oggetto = "Registrazione Produttore";
                $messaggio = "Un nuovo produttore vuole diventare partner <br><br>
Dati utente: <br><br> NOME: $nome <br> COGNOME: $cognome <br> INDIRIZZO EMAIL: $email <br> 
TIPOLOGIA UTENTE: $user_type <br> RAGIONE SOCIALE: $rag_soc <br> PARTITA IVA/CODICE FISCALE: $p_iva <br> NUMERO DI TELEFONO: $phone <br>
SITO INTERNET: $sito <br>
NOTE: <p>$note</p>";

                $message = \Swift_Message::newInstance()
                    ->setSubject('Registrazione Produttore')
                    ->setFrom(array('registrazionenewsletter@latavolaitaliana.org' => 'Registrazione Produttore'))
                    ->setCc('info@latavolaitaliana.org')
                    ->setTo('produttori@latavolaitaliana.org')
                    ->setBody($messaggio,
                        'text/html'
                    )
                ;
                $this->container->get('mailer')->send($message);

                $this->get('session')->getFlashBag()->add('success',
                    'La richiesta per diventare partner &egrave stata effettuata con successo, 
verrete contattati al più presto.');

                return array();
            } else {
                return array();
            }
        }

        return array();

    }

    /**
     * @Route("/shop/lavora-con-noi", name="lavora_con_noi")
     * @Template()
     */
    public function lavoraConNoiAction()
    {
        if ($this->get('request')->getMethod() == 'POST')
        {
            $user_type = $_POST['user_type'];
            $nome = trim($_POST['nome']);
            $cognome = trim($_POST['cognome']);
            $email = trim($_POST['email']);
            $ip = $_SERVER['REMOTE_ADDR'];
            $phone = trim($_POST['phone']);
            $note = trim($_POST['note']);
            $posizione = trim($_POST['posizione']);
            $allegato = $_FILES['allegato'];


            $ok = true;
#controllo input obbligatori
            if(!isset($user_type))
            {
                $this->get('session')->getFlashBag()->add('error',
                    'Non hai scelto la tipologia utente.');

                $ok = false;
            }

            if(!isset($nome, $cognome))
            {
                $this->get('session')->getFlashBag()->add('error',
                    'I dati inviati non sono corretti. Inserire nome e cognome');

                $ok = false;
            }

            if(!isset($allegato)){
                $this->get('session')->getFlashBag()->add('error',
                    'Inserire il curriculum in allegato');

                $ok = false;
            }

            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $this->get('session')->getFlashBag()->add('error',
                    'Inserire un indirizzo email valido.');

                $ok = false;
            }

            if ($ok)
            {

                $destinatario = "riccardo.cartei7@gmail.com"; //"produttori@latavolaitaliana.org";
                $header = "From: Candidatura Lavorativa <registrazionenewsletter@latavolaitaliana.org>\r\n";
                $header .= "Cc: Candidatura Lavorativa <info@latavolaitaliana.org> \r\n";
                $oggetto = "Registrazione Produttore";
                $messaggio = "Nuova candidatura ricevuta <br><br>
Dati utente: <br><br> NOME: $nome <br> COGNOME: $cognome <br> INDIRIZZO EMAIL: $email <br> 
TIPOLOGIA UTENTE: $user_type <br> POSIZIONE: $posizione <br> NUMERO DI TELEFONO: $phone <br>
NOTE: <p>$note</p>";

                $message = \Swift_Message::newInstance()
                    ->setSubject('Candidatura Lavorativa')
                    ->setFrom(array('registrazionenewsletter@latavolaitaliana.org' => 'Candidatura Lavorativa'))
                    ->setCc('info@latavolaitaliana.org')
                    ->setTo('produttori@latavolaitaliana.org')
                    ->attach(\Swift_Attachment::fromPath($_FILES['allegato']['tmp_name'])->setFilename($_FILES['allegato']['name']))
                    ->setBody($messaggio,
                        'text/html'
                    )
                ;
                $this->container->get('mailer')->send($message);

                $this->get('session')->getFlashBag()->add('success',
                    'La tua candidatura &egrave stata inviata con successo, 
verrai contattato al più presto.');

                return array();
            } else {
                return array();
            }
        }

        return array();

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
