<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProductRepository;
use App\Services\Cart\CartService;
use App\Services\Payments\MollieService;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(CartService $cartService)
    {
        return $this->render('cart/index.html.twig', [
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal()
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="cart_add")
     */
    public function add($id, CartService $cartService)
    {
        $cartService->add($id);
        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("cart/remove/{id}", name="cart_remove")
     */
    public function remove($id, CartService $cartService) {
       $cartService->remove($id);
        return $this->redirectToRoute('cart');
    }
    
    /**
     * @Route("cart/empty-cart", name="empty_cart")
     */
    public function emptyCart(CartService $cartService) {
       $cartService->emptyCart();
        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/checkout", name="checkout")
     */
    public function checkout(CartService $cartService, MollieService $mollieService) {
               
        $orderId = $cartService->checkout();
        $price = $cartService->getTotal();


        $price = number_format((float)$price,2, '.', '');
        $payUrl = $mollieService->pay($orderId, $price);

        return $this->redirect($payUrl);
    }

    /** 
     * @Route("/payment/webhook", name="webhook")
     */
    public function catchWebhook(MollieService $mollieService, Request $request) {
        $paymentId = $request->request->get('id');
        $mollieService->webhook($paymentId);
        
        return $this->redirectToRoute('profile');

    }
}
