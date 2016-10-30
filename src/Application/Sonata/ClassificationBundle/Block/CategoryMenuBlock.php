<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\ClassificationBundle\Block;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Component\Currency\CurrencyDetectorInterface;
use Sonata\ProductBundle\Repository\BaseProductRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
class CategoryMenuBlock extends BaseBlockService
{
    /**
     * @var EntityRepository
     */
    protected $productRepository;

    /**
     * @param string                    $name
     * @param EngineInterface           $templating
     * @param RegistryInterface         $registry
     * @param CurrencyDetectorInterface $currencyDetector
     */
    public function __construct($name, EngineInterface $templating, RegistryInterface $registry)
    {
        $this->productRepository = $registry->getManager()->getRepository('Application\Sonata\ClassificationBundle\Entity\Category');

        parent::__construct($name, $templating);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $categories = $this->productRepository->findAll();
        if (!$categories)
        {
            return;
        }

        $params = array(
            'context'   => $blockContext,
            'settings'  => $blockContext->getSettings(),
            'block'     => $blockContext->getBlock(),
            'categories'  => $categories,
        );

        return $this->renderResponse($blockContext->getTemplate(), $params, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('number', 'integer', array('required' => true)),
                array('title',  'text',    array('required' => false)),
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Category Menu';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'title'      => 'Category Menu',
            'template'   => 'ApplicationSonataClassificationBundle:Block:category_menu.html.twig',
        ));
    }

    /**
     * Returns the Base ProductRepository.
     *
     * @return BaseProductRepository
     */
    protected function getProductRepository()
    {
        return $this->productRepository;
    }
}