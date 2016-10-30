<?php
/**
 * Created by PhpStorm.
 * User: WEB
 * Date: 02/05/2016
 * Time: 09:56
 */

namespace CTI\CibourBundle\Block;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HomeSliderBlock extends BaseBlockService
{
    protected $em;
    /**
     * @param string          $name
     * @param EngineInterface $templating
     */
    public function __construct($name, EngineInterface $templating, RegistryInterface $em)
    {
        parent::__construct($name, $templating);
        $this->em = $em->getManager();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $settings = $blockContext->getSettings();

        $slides = $this->em->getRepository('CTI\CibourBundle\Entity\Slide')->findByEnabled(true);

        return $this->renderResponse($blockContext->getTemplate(), array(
            'block'     => $blockContext->getBlock(),
            'settings'  => $settings,
            'slides'    => $slides
        ), $response);
    }

    /**
     * @param FormMapper $form
     * @param BlockInterface $block
     *
     * @return void
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block)
    {
        $form
            ->add('title', 'text')
            ->add('galleryHasMedias', 'sonata_type_collection', array(
                'cascade_validation' => true,
            ), array(
                    'edit'              => 'inline',
                    'inline'            => 'table',
                    'sortable'          => 'position',
                    'link_parameters'   => array('context' => 'default'),
                    'admin_code'        => 'sonata.media.admin.gallery_has_media'
                )
            );
    }

    /**
     * @param ErrorElement $errorElement
     * @param BlockInterface $block
     *
     * @return void
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        parent::validateBlock($errorElement, $block); // TODO: Change the autogenerated stub
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Home Slder';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'title' => 'Home Slider',
            'template' => 'CTICibourBundle:Block:home_slider.html.twig'
        ));
    }

}