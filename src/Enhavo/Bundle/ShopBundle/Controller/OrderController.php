<?php
/**
 * OrderController.php
 *
 * @since 25/09/16
 * @author gseidel
 */

namespace Enhavo\Bundle\ShopBundle\Controller;

use Enhavo\Bundle\AppBundle\Controller\RequestConfiguration;
use Enhavo\Bundle\AppBundle\Controller\ResourceController;
use Enhavo\Bundle\ShopBundle\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends ResourceController
{
    public function listOrderAction(Request $request)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $template = $configuration->getTemplate('EnhavoShopBundle:Theme:Order/list.html.twig');

        $orders = $this->getOrderProvider()->getOrders($this->getUser());

        return $this->render($template, [
            'orders' => $orders
        ]);
    }

    public function detailOrderAction(Request $request)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $template = $configuration->getTemplate('EnhavoShopBundle:Theme:Order/detail.html.twig');
        $order = $this->getOrder($configuration);

        return $this->render($template, [
            'order' => $order
        ]);
    }

    public function transferOrderAction(Request $request)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $order = $this->getOrder($configuration);
        $this->getCartTransfer()->transfer($order, $this->getCart());

        $route = $configuration->getRedirectRoute('enhavo_shop_theme_cart_summary');
        return $this->redirectToRoute($route);
    }

    public function billingAction(Request $request)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $order = $this->singleResourceProvider->get($configuration, $this->repository);
        $documentManager = $this->get('enhavo_shop.document.manager');
        $response = new Response();

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set(
            'Content-Disposition',
            sprintf('attachment; filename="%s"', $documentManager->generateBillingName($order))
        );

        $response->setContent($documentManager->generateBilling($order));
        return $response;
    }

    private function getOrder(RequestConfiguration $configuration)
    {
        /** @var OrderInterface $order */
        $order = $this->singleResourceProvider->get($configuration, $this->repository);
        
        if($order === null) {
            throw $this->createNotFoundException();
        }

        if($order->getState() === 'cart') {
            throw $this->createNotFoundException();
        }

        if($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $order;
    }

    private function getOrderProvider()
    {
        return $this->get('enhavo_shop.order.order_provider');
    }

    private function getCartTransfer()
    {
        return $this->get('enhavo_shop.cart.cart_transfer');
    }

    private function getCart()
    {
        return $this->get('sylius.cart_provider')->getCart();
    }
}