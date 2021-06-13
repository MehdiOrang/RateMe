<?php
namespace App\EntityListener;

use App\Entity\Product;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductEntityListener
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function prePersist(Product $conference, LifecycleEventArgs $event)
    {
        $conference->computeSlug($this->slugger);
    }

    public function preUpdate(Product $conference, LifecycleEventArgs $event)
    {
        $conference->computeSlug($this->slugger);
    }
}
