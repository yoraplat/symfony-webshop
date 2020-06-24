<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProductRepository;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Repository\OrderDetailRepository;


class OrderController extends AbstractController
{
    protected $session;
    protected $productRepository;
    protected $orderDetailRepository;

    public function __construct(SessionInterface $session, ProductRepository $productRepository, OrderDetailRepository $orderDetailRepository) {
        $this->session = $session;
        $this->productRepository = $productRepository;
        $this->orderDetailRepository = $orderDetailRepository;
    }
    /**
     * @Route("/order", name="order")
     */
    public function index()
    {
        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }
    
    /**
     * @Route("/order/{orderId}", name="order_detail")
     */
    public function orderDetail($orderId)
    {
        $userId = $this->getUser()->getId();
        
        $order = $this->getDoctrine()
        ->getRepository(Order::class)
        ->findOneBy(array('customer_id' => $userId, 'id' => $orderId));
        // $order = $this->orderDetailRepository->findByOrderId($orderId);


        $order = $order->getOrderDetails();

        // dd($order);
        
        return $this->render('order/detail.html.twig', [
            'order' => $order,
        ]);
    }
}
