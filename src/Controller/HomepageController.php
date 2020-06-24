<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Product;

class HomepageController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $products = $this->getDoctrine()
        ->getRepository(Product::class)
        ->findAll();
        
        
        return $this->render('homepage/index.html.twig', [
            'products' => $products,
        ]);
    }
}
