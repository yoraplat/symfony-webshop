<?php

namespace App\Services\Payments;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Payment;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use App\Repository\PaymentRepository;
use App\Repository\OrderRepository;
use Symfony\Component\Security\Core\Security;

class MollieService {

    protected $session;
    private $security;
    private $paymentRepository;
    private $orderRepository;
    public function __construct(Security $security, PaymentRepository $paymentRepository, OrderRepository $orderRepository, SessionInterface $session, EntityManagerInterface $em) {
        $this->session = $session;
        $this->em = $em;
        $this->security = $security;
        $this->paymentRepository = $paymentRepository;
        $this->orderRepository = $orderRepository;
        
    }

    public function pay($orderId, $price) {
        
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($_ENV['MOllIE_TEST_API_KEY']);
        
        $root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';

        function getWebhook() {
            if($_ENV['APP_ENV'] == 'prod'){
                    return $root . "payment/webhook";
                } else {
                    return "https://webhook.site/7c73eb8e-e08c-4f26-93a4-02ac670b3163";
                }
        };
        $payment = $mollie->payments->create([
            "amount" => [
                "currency" => "EUR",
                "value" => $price
            ],
            "description" => "Payment for order #id: " . $orderId,
            "redirectUrl" => $root . 'profile',

            
            "webhookUrl"  => getWebhook(),
        ]);

        $createPayment = new Payment();
        $createPayment->setOrderId($orderId);
        $createPayment->setPaymentId($payment->id);
        $createPayment->setStatus("pending");

        $this->em->persist($createPayment);
        $this->em->flush(); 
        
        return $payment->_links->checkout->href;
        
    }

    public function webhook($paymentId) {
        $payment = $this->paymentRepository->findOnePaymentId($paymentId);
        
        
        $order = $this->em
        ->getRepository(Order::class)
        ->findOneById($payment->getOrderId());
        

        if($_ENV['APP_ENV'] == 'dev'){
            $apiToken = $_ENV['MOllIE_TEST_API_KEY'];
        } else {
            $apiToken = $_ENV['MOllIE_LIVE_API_KEY'];
        }
    
        $url = 'https://api.mollie.com/v2/payments/'. $paymentId;
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $url, ['auth_bearer' => $apiToken]);
        $response = $response->toArray();
        $status = $response['status'];

        // Set status in order
        $order->setOrderStatus($status);

        $payment->setStatus($status);
        $this->em->persist($order);
        $this->em->flush();

        $this->em->persist($payment);
        $this->em->flush();
    }
    
}