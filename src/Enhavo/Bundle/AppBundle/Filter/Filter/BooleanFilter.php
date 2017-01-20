<?php
/**
 * TextFilter.php
 *
 * @since 19/01/17
 * @author gseidel
 */

namespace Enhavo\Bundle\AppBundle\Filter\Filter;

use Enhavo\Bundle\AppBundle\Filter\FilterInterface;
use Enhavo\Bundle\AppBundle\Type\AbstractType;
use Enhavo\Bundle\AppBundle\Filter\FilterQuery;

class BooleanFilter extends AbstractType implements FilterInterface
{
    public function render($options, $value)
    {
        return $this->renderTemplate('EnhavoAppBundle:Filter:boolean.html.twig', [
            'type' => $this->getType(),
            'value' => $value,
            'label' => $this->getOption('label', $options, ''),
            'translationDomain' => $this->getOption('translationDomain', $options, null),
            'icon' => $this->getOption('icon', $options, ''),
            'name' => $this->getRequiredOption('name', $options),
        ]);
    }

    public function buildQuery(FilterQuery $query, $options, $value)
    {
        $property = $this->getRequiredOption('property', $options);

        $value = (boolean)$value;
        if($value) {
            $query->addWhere($property, FilterQuery::OPERATOR_EQUALS, $value);
        }
    }

    public function getType()
    {
        return 'boolean';
    }
}