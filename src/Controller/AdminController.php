<?php
namespace App\Controller;

use App\Entity\Review;
use App\Message\ReviewMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Workflow\Registry;
use Twig\Environment;

#[Route('/admin')]
class AdminController extends AbstractController
{
    private $twig;
    private $entityManager;
    private $bus;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager, MessageBusInterface $bus)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
    }

    #[Route('/review/{id}', name: 'review')]
    public function review(Request $request, Review $review, Registry $registry): Response
    {
        $accepted = !$request->query->get('reject');

        $machine = $registry->get($review);
        if ($machine->can($review, 'publish')) {
            $transition = $accepted ? 'publish' : 'reject';
        } elseif ($machine->can($review, 'publish_ham')) {
            $transition = $accepted ? 'publish_ham' : 'reject_ham';
        } else {
            return new Response('Comment already reviewed or not in the right state.');
        }

        $machine->apply($review, $transition);
        $this->entityManager->flush();

        if ($accepted) {
            $reviewUrl = $this->generateUrl('review', ['id' => $review->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->bus->dispatch(new ReviewMessage($review->getId(), $reviewUrl));
        }

        return $this->render('admin/review.html.twig', [
            'transition' => $transition,
            'review' => $review,
        ]);
    }
}
