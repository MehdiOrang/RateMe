<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ReviewRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ProductController extends AbstractController
{

    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    #[Route('/', name: 'homepage')]
    public function index( ProductRepository $productRepository): Response
    {
      return new Response($this->twig->render('product/index.html.twig', [
                       'products' => $productRepository->findAll(),
                   ]));
    }

    #[Route('/product/{slug}', name: 'product')]
    public function show(Product $product, ReviewRepository $reviewRepository): Response
    {
        return new Response($this->twig->render('product/show.html.twig', [
            'product' => $product,
            'reviews' => $reviewRepository->findBy(['product' => $product], ['createdAt' => 'DESC']),
        ]));
    }
}
