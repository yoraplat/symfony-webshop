<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Order;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile")
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $userId = $this->getUser()->getId();
        $myOrders = $this->getDoctrine()
        ->getRepository(Order::class)
        ->findBy(array('customer_id' => $userId));

        $pagination = $paginator->paginate(
            $myOrders,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('profile/index.html.twig', [
            // 'orders' => $myOrders,
            'pagination' => $pagination,
        ]);
    }
}
