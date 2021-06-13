<?php

namespace App\Controller;

use App\SpamChecker;
use App\Entity\Product;
use App\Entity\Review;
use App\Form\ReviewFormType;
use App\Repository\ReviewRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ProductController extends AbstractController
{

    private $twig;
    private $entityManager;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager)
     {
         $this->twig = $twig;
        $this->entityManager = $entityManager;
     }

    #[Route('/', name: 'homepage')]
    public function index( ProductRepository $productRepository): Response
    {
      return new Response($this->twig->render('product/index.html.twig', [
                       'products' => $productRepository->findAll(),
                   ]));
    }

    #[Route('/product/{slug}', name: 'product')]
    public function show(Request $request, Product $product, ReviewRepository $reviewRepository, SpamChecker $spamChecker,string $photoDir): Response
    {
        $review = new Review();
        $form = $this->createForm(ReviewFormType::class, $review);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $review->setProduct($product);
            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $e) {
                    // unable to upload the photo, give up
                }
                $review->setPhotoFilename($filename);
            }

            $this->entityManager->persist($review);
            $context = [
                                'user_ip' => $request->getClientIp(),
                                'user_agent' => $request->headers->get('user-agent'),
                                'referrer' => $request->headers->get('referer'),
                                'permalink' => $request->getUri(),
                            ];
                            if (2 === $spamChecker->getSpamScore($review, $context)) {
                                throw new \RuntimeException('Blatant spam, go away!');
                            }
            $this->entityManager->flush();

            return $this->redirectToRoute('product', ['slug' => $product->getSlug()]);
        }

        return new Response($this->twig->render('product/show.html.twig', [
            'product' => $product,
            'reviews' => $reviewRepository->findBy(['product' => $product], ['createdAt' => 'DESC']),
            'review_form' => $form->createView(),
        ]));
    }
}
