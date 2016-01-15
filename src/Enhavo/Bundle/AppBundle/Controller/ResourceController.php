<?php
/**
 * ResourceController.php
 *
 * @since 01/08/14
 * @author Gerhard Seidel <gseidel.message@googlemail.com>
 */

namespace Enhavo\Bundle\AppBundle\Controller;

use Enhavo\Bundle\AppBundle\Config\ConfigParser;
use Enhavo\Bundle\AppBundle\Exception\BadMethodCallException;
use Enhavo\Bundle\AppBundle\Exception\PreviewException;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController as BaseController;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResourceController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request)
    {
        $config = $this->get('viewer.config')->parse($request);
        $viewer = $this->get('viewer.factory')->create($config->getType(), 'create');
        $viewer->setBundlePrefix($this->config->getBundlePrefix());
        $viewer->setResourceName($this->config->getResourceName());
        $viewer->setConfig($config);

        $resource = $this->createNew();
        $form = $this->getForm($resource);

        $method = $request->getMethod();
        if (in_array($method, array('POST', 'PUT', 'PATCH'))) {
            if($form->handleRequest($request)->isValid()) {
                $this->domainManager->create($resource);
                $this->dispatchEvent('enhavo_app.create', $resource, array('action' => 'create'));
                return new Response();
            }

            $view = $this->view($form);
            $view->setFormat('json');
            return $this->handleView($view);
        }

        $viewer->setResource($resource);
        $viewer->setForm($form);
        $viewer->dispatchEvent('');

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('create.html'))
            ->setData($viewer->getParameters())
        ;

        return $this->handleView($view);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAction(Request $request)
    {
        $config = $this->get('viewer.config')->parse($request);
        $viewer = $this->get('viewer.factory')->create($config->getType(), 'edit');
        $viewer->setBundlePrefix($this->config->getBundlePrefix());
        $viewer->setResourceName($this->config->getResourceName());
        $viewer->setConfig($config);

        $resource = $this->findOr404($request);
        $form = $this->getForm($resource);
        $method = $request->getMethod();

        if (in_array($method, array('POST', 'PUT', 'PATCH'))) {
            if($form->submit($request, !$request->isMethod('PATCH'))->isValid()) {
                $this->domainManager->update($resource);
                $this->dispatchEvent('enhavo_app.update', $resource, array('action' => 'update'));
                return new Response();
            }

            $view = $this->view($form);
            $view->setFormat('json');
            return $this->handleView($view);
        }

        $viewer->setResource($resource);
        $viewer->setForm($form);
        $viewer->dispatchEvent('');

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('update.html'))
            ->setData($viewer->getParameters())
        ;

        return $this->handleView($view);
    }

    public function indexAction(Request $request)
    {
        $config = $this->get('viewer.config')->parse($request);
        $viewer = $this->get('viewer.factory')->create($config->getType(), 'index');
        $viewer->setBundlePrefix($this->config->getBundlePrefix());
        $viewer->setResourceName($this->config->getResourceName());
        $viewer->setConfig($config);

        $viewer->dispatchEvent('');

        return $this->render($viewer->getTemplate(), $viewer->getParameters());
    }

    public function previewAction(Request $request)
    {
        $config = $this->get('viewer.config')->parse($request);
        $viewer = $this->get('viewer.factory')->create($config->getType(), 'preview');
        $viewer->setBundlePrefix($this->config->getBundlePrefix());
        $viewer->setResourceName($this->config->getResourceName());
        $viewer->setConfig($config);

        $resource = $this->createNew();
        $form = $this->getForm($resource);
        $form->handleRequest($request);

        $strategyName = $config->get('strategy');
        $strategy = $this->get('enhavo_app.preview.strategy_resolver')->getStrategy($strategyName);
        $response = $strategy->getPreviewResponse($resource, $viewer->getConfig());
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function tableAction(Request $request)
    {
        $config = $this->get('viewer.config')->parse($request);
        $viewer = $this->get('viewer.factory')->create($config->getType(), 'table');
        $viewer->setBundlePrefix($this->config->getBundlePrefix());
        $viewer->setResourceName($this->config->getResourceName());
        $viewer->setConfig($config);

        //fire event for permission
        $criteria = $this->config->getCriteria();
        $sorting = $this->config->getSorting();
        $repository = $this->getRepository();

        if ($this->config->isPaginated()) {
            $resources = $this->resourceResolver->getResource(
                $repository,
                'createPaginator',
                array($criteria, $sorting)
            );
            $resources->setCurrentPage($request->get('page', 1), true, true);
            $resources->setMaxPerPage($this->config->getPaginationMaxPerPage());
        } else {
            $resources = $this->resourceResolver->getResource(
                $repository,
                'findBy',
                array($criteria, $sorting, $this->config->getLimit())
            );
        }

        $viewer->setResource($resources);
        $viewer->dispatchEvent('');

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('index.html'))
            ->setData($viewer->getParameters())
        ;

        return $this->handleView($view);
    }

    /**
     *
     */
    public function batchAction(Request $request)
    {
        $criteria = $this->config->getCriteria();
        $sorting = $this->config->getSorting();
        $form = $this->getBatchUpdateForm();
        $repository = $this->getRepository();
        $method = $request->getMethod();

        if (in_array($method, array('POST', 'PUT', 'PATCH'))) {
            if($form->submit($request, !$request->isMethod('PATCH'))->isValid()) {
                $resources = $form->getData();
                foreach($resources as $resource) {
                    $this->domainManager->update($resource);
                }
                return new Response();
            }

            $view = $this->view($form);
            $view->setFormat('json');
            return $this->handleView($view);
        } else {
            if ($this->config->isPaginated()) {
                $resources = $this->resourceResolver->getResource(
                    $repository,
                    'createPaginator',
                    array($criteria, $sorting)
                );
                $resources->setCurrentPage($request->get('page', 1), true, true);
                $resources->setMaxPerPage($this->config->getPaginationMaxPerPage());
            } else {
                $resources = $this->resourceResolver->getResource(
                    $repository,
                    'findBy',
                    array($criteria, $sorting, $this->config->getLimit())
                );
            }
        }

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('update.html'))
            ->setData(array(
                'form' => $form->createView(),
                'data' => $resources,
                'view' => $this->getAdmin()->createView()
            ))
        ;

        return $this->handleView($view);
    }

    public function deleteAction(Request $request)
    {
        $this->isGrantedOr403('delete');
        $this->domainManager->delete($this->findOr404($request));
        return new Response();
    }

    protected function dispatchEvent($eventName, $subject = null, $arguments = array())
    {
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch($eventName, new GenericEvent($subject, $arguments));
    }
}
