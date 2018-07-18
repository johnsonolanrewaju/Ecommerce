<?php

namespace Enhavo\Bundle\PageBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $dataClass;

    public function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults( array(
            'class' => $this->dataClass,
        ));
    }

    public function getParent()
    {
        return EntityType::class;
    }

    public function getName()
    {
        return 'enhavo_page_page_choice';
    }
}