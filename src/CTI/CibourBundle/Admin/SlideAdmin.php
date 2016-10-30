<?php

namespace CTI\CibourBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class SlideAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('enabled')
            ->add('nome')
            ->add('titolo')
            ->add('testo')
            ->add('link')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('enabled')
            ->add('nome')
            ->add('titolo')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('enabled', null, array('required' => false))
            ->add('nome')
            ->add('titolo')
            ->add('testo')
            ->add('link')
            ->add('image', 'sonata_type_model_list', array(
                'required' => false
            ), array(
                'link_parameters' => array(
                    'context'  => 'cibour_slide',
                    'filter'   => array('context' => array('value' => 'cibour_slide')),
                    'provider' => 'sonata.media.provider.image'
                )
            ));
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('enabled')
            ->add('nome')
            ->add('titolo')
            ->add('testo')
            ->add('link')
            ->add('image')
        ;
    }
}
