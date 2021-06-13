<?php

namespace App\EventSubscriber;

use App\Repository\ProductRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $productRepository;
    public function __construct(Environment $twig, ProductRepository $productRepository)
       {
           $this->twig = $twig;
           $this->productRepository = $productRepository;
       }


    public function onKernelController(ControllerEvent $event)
    {
        $this->twig->addGlobal('products', $this->productRepository->findAll());

    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
