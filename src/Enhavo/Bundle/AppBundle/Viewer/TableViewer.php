<?php
/**
 * TableViewer.php
 *
 * @since 29/05/15
 * @author gseidel
 */

namespace Enhavo\Bundle\AppBundle\Viewer;

use BaconStringUtils\Slugifier;
use Enhavo\Bundle\AppBundle\Exception\PropertyNotExistsException;
use Enhavo\Bundle\AppBundle\Exception\TableWidgetException;

class TableViewer extends AbstractViewer
{
    const BATCH_ACTION_NONE_SELECTED = 'none_selected';

    public function getDefaultConfig()
    {
        return array(
            'table' => array(
                'sorting' => array(
                    'sortable' => false,
                    'move_after_route' => sprintf('%s_%s_move_after', $this->getBundlePrefix(), $this->getResourceName()),
                    'move_to_page_route' => sprintf('%s_%s_move_to_page', $this->getBundlePrefix(), $this->getResourceName())
                ),
                'batch_actions' => array(
                    'delete' => array(
                        'label'                 => 'table.batch.delete.label',
                        'confirm_message'       => 'table.batch.delete.confirmMessage',
                        'translation_domain'    => 'EnhavoAppBundle',
                        'position'              => 0
                    )
                ),
                'batch_action_route' => sprintf('%s_%s_batch', $this->getBundlePrefix(), $this->getResourceName())
            )
        );
    }

    protected function getColumns()
    {
        $columns = $this->getConfig()->get('table.columns');
        if (!$columns) {
            if ($this->isSortable()) {
                $columns = array(
                    'id' => array(
                        'label' => 'id',
                        'property' => 'id',
                        'width' => 1
                    ),
                    'position' => array(
                        'label' => '',
                        'property' => 'position',
                        'width' => 1,
                        'widget' => array(
                            'type' => 'template',
                            'template' => 'EnhavoAppBundle:Widget:position.html.twig',
                        )
                    )
                );
            } else {
                $columns = array(
                    'id' => array(
                        'label' => 'id',
                        'property' => 'id',
                        'width' => 1
                    )
                );
            }

        }

        foreach($columns as $key => &$column) {
            if(!array_key_exists('width', $column)) {
                $column['width'] = 1;
            }
        }

        foreach($columns as $key => &$column) {
            if(!array_key_exists('widget', $column)) {
                $column['widget'] = [
                    'type' => 'property'
                ];
            }
        }

        if (isset($columns['position']) && !isset($columns['position']['widget'])) {
            $columns['position']['widget'] = 'EnhavoAppBundle:Widget:position.html.twig';
        }

        return $columns;
    }

    protected function getConfigTableWidth()
    {
        $width = $this->getConfig()->get('table.width');
        if($width === null) {
            return 12;
        }
        return $width;
    }

    protected function getSorting()
    {
        $sorting = $this->getConfig()->get('table.sorting');

        if (!$sorting) {
            $sorting = array();
        }

        if (!isset($sorting['sortable'])) {
            $sorting['sortable'] = false;
        }
        if (!isset($sorting['move_after_route'])) {
            $sorting['move_after_route'] = sprintf('%s_%s_move_after', $this->getBundlePrefix(), $this->getResourceName());
        }
        if (!isset($sorting['move_to_page_route'])) {
            $sorting['move_to_page_route'] = sprintf('%s_%s_move_to_page', $this->getBundlePrefix(), $this->getResourceName());
        }

        return $sorting;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        $parameters = array(
            'viewer' => $this,
            'data' => $this->getResource(),
            'columns' => $this->getColumns(),
            'translationDomain' => $this->getTranslationDomain()
        );

        $parameters = array_merge($this->getTemplateVars(), $parameters);

        return $parameters;
    }

    public function getTableWidth()
    {
        return $this->getConfigTableWidth();
    }

    public function isSortable()
    {
        $sorting = $this->getSorting();
        return $sorting['sortable'] === true;
    }

    public function getMoveAfterRoute()
    {
        $sorting = $this->getSorting();
        return $sorting['move_after_route'];
    }

    public function getMoveToPageRoute()
    {
        $sorting = $this->getSorting();
        return $sorting['move_to_page_route'];
    }

    public function getHasBatchActions()
    {
        $batch_actions = $this->getConfig()->get('table.batch_actions');
        $route = $this->getConfig()->get('table.batch_action_route');
        if (!$this->container->get('router')->getRouteCollection()->get($route)) {
            return false;
        }

        return $batch_actions && (count($batch_actions) > 0);
    }

    public function getBatchActions()
    {
        $batch_actions = $this->getConfig()->get('table.batch_actions');
        if (!$batch_actions) {
            $batch_actions = array();
        }

        if (count($batch_actions) > 0) {
            if (!isset($batch_actions[self::BATCH_ACTION_NONE_SELECTED])) {
                $batch_actions[self::BATCH_ACTION_NONE_SELECTED] = array(
                    'label'                 => 'table.batch.noneSelected.label',
                    'confirm_message'       => '',
                    'translation_domain'    => 'EnhavoAppBundle'
                );
            }

            $translator = $this->container->get('translator');
            $slugifier = new Slugifier();
            $pos = 100;
            $batch_actions_parsed = array();
            foreach($batch_actions as $command => $value) {
                $command_parsed = $slugifier->slugify($command);
                $action_parsed = array();

                if (isset($value['translation_domain']) && $value['translation_domain']) {
                    $domain = $value['translation_domain'];
                } else {
                    $domain = $this->getTranslationDomain();
                }

                if (isset($value['label']) && $value['label']) {
                    $action_parsed['label'] = $translator->trans($value['label'], array(), $domain);
                } else {
                    $action_parsed['label'] = $command;
                }

                if (isset($value['confirm_message']) && $value['confirm_message']) {
                    $action_parsed['confirm_message'] = $translator->trans($value['confirm_message'], array(), $domain);
                } else {
                    $action_parsed['confirm_message'] = $translator->trans('table.batch.confirmMessageGeneric', array('%command%' => $command), 'EnhavoAppBundle');
                }

                if (isset($value['position']) && is_int($value['position']) && ($value['position'] >= 0)) {
                    $action_parsed['position'] = $value['position'];
                } else {
                    if ($command == self::BATCH_ACTION_NONE_SELECTED) {
                        $action_parsed['position'] = -1;
                    } else {
                        $action_parsed['position'] = $pos++;
                    }
                }
                $batch_actions_parsed[$command_parsed] = $action_parsed;
            }

            uasort($batch_actions_parsed, array($this, 'cmp'));

            return $batch_actions_parsed;
        }

        return array();
    }

    public function getBatchActionRoute()
    {
        return $this->getConfig()->get('table.batch_action_route');
    }

    /**
     * @param $options
     * @param $property
     * @param $item
     * @return string
     * @throws TableWidgetException
     */
    public function renderWidget($options, $property, $item)
    {
        $collector = $this->container->get('enhavo_app.table_widget_collector');
        $widgets = array();
        $widget = $collector->getWidget($options['type']);
        return $widget->render($options, $property, $item);
    }

    private function cmp($a, $b)
    {
        if ($a['position'] == $b['position']) {
            return 0;
        }
        return $a['position'] < $b['position'] ? -1 : 1;
    }
}
