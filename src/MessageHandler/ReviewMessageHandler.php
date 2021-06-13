<?php
namespace App\MessageHandler;

use App\Message\ReviewMessage;
use App\Repository\ReviewRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class ReviewMessageHandler implements MessageHandlerInterface
{
    private $spamChecker;
    private $entityManager;
    private $reviewRepository;
    private $bus;
    private $workflow;
    private $mailer;
    private $adminEmail;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, SpamChecker $spamChecker, ReviewRepository $reviewRepository, MessageBusInterface $bus, WorkflowInterface $reviewStateMachine, MailerInterface $mailer, string $adminEmail, LoggerInterface $logger = null)
    {
        $this->entityManager = $entityManager;
        $this->spamChecker = $spamChecker;
        $this->reviewRepository = $reviewRepository;
        $this->bus = $bus;
        $this->workflow = $reviewStateMachine;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->logger = $logger;
    }

    public function __invoke(ReviewMessage $message)
    {
        $review = $this->reviewRepository->find($message->getId());
        if (!$review) {
            return;
        }

        if ($this->workflow->can($review, 'accept')) {
            $score = $this->spamChecker->getSpamScore($review, $message->getContext());
            $transition = 'accept';
            if (2 === $score) {
                $transition = 'reject_spam';
            } elseif (1 === $score) {
                $transition = 'might_be_spam';
            }
            $this->workflow->apply($review, $transition);
            $this->entityManager->flush();

            $this->bus->dispatch($message);
        } elseif ($this->workflow->can($review, 'publish') || $this->workflow->can($review, 'publish_ham')) {
            $this->mailer->send((new NotificationEmail())
                ->subject('New review posted')
                ->htmlTemplate('emails/review_notification.html.twig')
                ->from($this->adminEmail)
                ->to($this->adminEmail)
                ->context(['review' => $review])
            );
        } elseif ($this->logger) {
            $this->logger->debug('Dropping review message', ['review' => $review->getId(), 'state' => $review->getState()]);
        }
    }
}
