<?php
/**
 * BlockType.php
 *
 * @since 23/08/14
 * @author Gerhard Seidel <gseidel.message@googlemail.com>
 */

namespace Enhavo\Bundle\BlockBundle\Form\Type;

use Enhavo\Bundle\FormBundle\Form\Type\DynamicItemType;
use Enhavo\Bundle\FormBundle\Form\Type\PositionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Enhavo\Bundle\BlockBundle\Entity\Block;

class BlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('position', PositionType::class);
        $builder->add('name', HiddenType::class);
        $builder->add('blockType', $options['block_type_form'], $options['block_type_parameters']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Block::class,
            'block_type_form' => null,
            'block_type_parameters' => [],
            'block_property' => 'name',
        ));
    }

    public function getParent()
    {
        return DynamicItemType::class;
    }

    public function getBlockPrefix()
    {
        return 'enhavo_block_block';
    }
} 