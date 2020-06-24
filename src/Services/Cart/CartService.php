<?php

namespace App\Services\Cart;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductRepository;
use App\Entity\OrderDetail;
use App\Entity\Order;
use App\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class CartService {

    protected $session;
    protected $productRepository;

    public function __construct(Security $security, EntityManagerInterface $em, SessionInterface $session, ProductRepository $productRepository) {
        $this->session = $session;
        $this->productRepository = $productRepository;
        $this->em = $em;
        $this->security = $security;
    }

    public function add(int $id) {
        $cart = $this->session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }
    
    public function remove(int $id) {
        $cart = $this->session->get('cart');

        if(!empty($cart[$id])) {
            if($cart[$id] == 1) {
                unset($cart[$id]);
            } else {
                $cart[$id] = $cart[$id] - 1;
            }


        }
        $this->session->set('cart', $cart);
    }

    public function emptyCart() {
        $cart = $this->session->get('cart');
        if(!empty($cart)) {
            $this->session->remove('cart');
        }
    }


    public function getFullCart(): array {
        $cart = $this->session->get('cart');
        $cartWithData = [];
        if(is_array($cart)) {
            foreach($cart as $id => $quantity) {
                $cartWithData[] = [
                    'product' => $this->productRepository->find($id),
                    'quantity' => $quantity
                ];
            }
        }
        return $cartWithData;

    }
    public function getTotal(): float {
        $total = 0;
        foreach($this->getFullCart() as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }
        return $total;

    }

    public function getTotalQuantity(): int {
        $amount = 0;

        if(is_integer($amount)) {

            foreach($this->getFullCart() as $item) {
                $amount += $item['quantity'];
            }
        }

        return $amount;
    }

    public function checkout() {
        $cart = $this->session->get('cart');
        $cartWithData = [];
        if(is_array($cart)) {
            foreach($cart as $id => $quantity) {
                $cartWithData[] = [
                    'product' => $this->productRepository->find($id),
                    'quantity' => $quantity
                ];
            }
        }
        // dd($cartWithData[0]['product']);


        // Create order and order_detail per product_id
        
        $order = new Order();

        $em = $this->em;
        $user = $this->security->getUser();
        
        $total = 0;
        foreach($this->getFullCart() as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }
        
        $order->setCustomerId($user->getId());
        $order->setShippingAddress($user->getDefaultShippingAddress());
        $order->setOrderAddress($user->getBillingAddress());
        $order->setOrderDate(new \DateTime());
        $order->setOrderStatus("pending");
        $order->setPrice($total);
        
        $em->persist($order);
        $em->flush();
         
        $orderId = $order->getId(); 
        
        foreach ($cartWithData as $detail) {

            dd($orderId);

            $orderDetails = new OrderDetail();
            $orderDetails->setOrderId($order->getId()); 
            $orderDetails->setProductId($detail['product']->getId()); 
            $orderDetails->setPrice($detail['product']->getPrice()); 
            $orderDetails->setQuantity($detail['quantity']);

            $em->persist($orderDetails);
            $em->flush();
        }

        return $order->getId();
    }
    
}