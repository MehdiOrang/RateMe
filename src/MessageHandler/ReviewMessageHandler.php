<?php
namespace App\MessageHandler;

use App\Message\ReviewMessage;
use App\Repository\ReviewRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ReviewMessageHandler implements MessageHandlerInterface
{
    private $spamChecker;
    private $entityManager;
    private $reviewRepository;

    public function __construct(EntityManagerInterface $entityManager, SpamChecker $spamChecker, ReviewRepository $reviewRepository)
    {
        $this->entityManager = $entityManager;
        $this->spamChecker = $spamChecker;
        $this->reviewRepository = $reviewRepository;
    }

    public function __invoke(ReviewMessage $message)
    {
        $review = $this->reviewRepository->find($message->getId());
        if (!$review) {
            return;
        }

        if (2 === $this->spamChecker->getSpamScore($review, $message->getContext())) {
            $review->setState('spam');
        } else {
            $review->setState('published');
        }

        $this->entityManager->flush();
    }
}
