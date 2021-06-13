<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Review;
use App\Form\ReviewFormType;
use App\Message\ReviewMessage;
use App\Repository\ReviewRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Twig\Environment;

class ProductController extends AbstractController
{

    private $twig;
    private $entityManager;
    private $bus;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager,MessageBusInterface $bus)
     {
         $this->twig = $twig;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
     }

    #[Route('/', name: 'homepage')]
    public function index( ProductRepository $productRepository): Response
    {
      return new Response($this->twig->render('product/index.html.twig', [
                       'products' => $productRepository->findAll(),
                   ]));
    }


    #[Route('/product_header', name: 'product_header')]
    public function conferenceHeader(ProductRepository $productRepository): Response
   {
       $response = new Response($this->twig->render('product/header.html.twig', [
           'products' => $productRepository->findAll(),
       ]));
       $response->setSharedMaxAge(3600);

       return $response;
     }



    // #[Route('/product/{slug}', name: 'product')]
    // public function show(Request $request, Product $product, ReviewRepository $reviewRepository, string $photoDir): Response
    // {
    //     $review = new Review();
    //     $form = $this->createForm(ReviewFormType::class, $review);
    //     $form->handleRequest($request);
    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $review->setProduct($product);
    //         if ($photo = $form['photo']->getData()) {
    //             $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
    //             try {
    //                 $photo->move($photoDir, $filename);
    //             } catch (FileException $e) {
    //                 // unable to upload the photo, give up
    //             }
    //             $review->setPhotoFilename($filename);
    //         }

    //         $this->entityManager->persist($review);
    //         $this->entityManager->flush();
    //         $context = [
    //                             'user_ip' => $request->getClientIp(),
    //                             'user_agent' => $request->headers->get('user-agent'),
    //                             'referrer' => $request->headers->get('referer'),
    //                             'permalink' => $request->getUri(),
    //                         ];
    //         $this->bus->dispatch(new ReviewMessage($review->getId(), $context));


    //         return $this->redirectToRoute('product', ['slug' => $product->getSlug()]);
    //     }

    //     return new Response($this->twig->render('product/show.html.twig', [
    //         'product' => $product,
    //         'reviews' => $reviewRepository->findBy(['product' => $product], ['createdAt' => 'DESC']),
    //         'review_form' => $form->createView(),
    //     ]));
    // }




    #[Route('/product/{slug}', name: 'product')]
    public function show(Request $request, Product $product, ReviewRepository $reviewRepository, NotifierInterface $notifier, string $photoDir): Response
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
                    $this->entityManager->flush();
                    $context = [
                                       'user_ip' => $request->getClientIp(),
                                        'user_agent' => $request->headers->get('user-agent'),
                                        'referrer' => $request->headers->get('referer'),
                                        'permalink' => $request->getUri(),
                                    ];
                    $reviewUrl = $this->generateUrl('review', ['id' => $review->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
                    $this->bus->dispatch(new ReviewMessage($review->getId(), $reviewUrl, $context));
                    $notifier->send(new Notification('Thank you for the feedback; your comment will be posted after moderation.', ['browser']));


                    return $this->redirectToRoute('product', ['slug' => $product->getSlug()]);
                }
        if ($form->isSubmitted()) {
            $notifier->send(new Notification('Can you check your submission? There are some problems with it.', ['browser']));
        }
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $reviewRepository->getReviewPaginator($product, $offset);
        return new Response($this->twig->render('product/show.html.twig', [
              'product' => $product,
              'reviews' => $paginator,
           'previous' => $offset - ReviewRepository::PAGINATOR_PER_PAGE,
           'next' => min(count($paginator), $offset + ReviewRepository::PAGINATOR_PER_PAGE),
           'review_form' => $form->createView(),
         ]));
     }
 }

