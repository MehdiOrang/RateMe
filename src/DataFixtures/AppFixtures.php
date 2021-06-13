<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Review;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;


class AppFixtures extends Fixture
 {

    private $encoderFactory;
    
        public function __construct(EncoderFactoryInterface $encoderFactory)
        {
            $this->encoderFactory = $encoderFactory;
        }
     public function load(ObjectManager $manager)
     {

        $addidas = new Product();
        $addidas->setName('number1');
        $addidas->setBrand('addidas');
        $addidas->setPrice('95');
        $addidas->setFreeShipment(true);
        $manager->persist($addidas);

        $reebok = new Product();
        $reebok->setName('number2');
        $reebok->setBrand('reebok');
        $reebok->setPrice('75');
        $reebok->setFreeShipment(false);
        $manager->persist($reebok);

        $review1 = new Review();
        $review1->setProduct($addidas);
        $review1->setAuthor('Joe');
        $review1->setEmail('joe@example.com');
        $review1->setRate(4);
        $review1->setText('This is a great shoes.');
        $review1->setState('published');
        $manager->persist($review1);

        $review2 = new Review();
        $review2->setProduct($addidas);
        $review2->setAuthor('Jack');
        $review2->setEmail('jack@example.com');
        $review2->setRate(5);
        $review2->setText('This is a great shoes.');
        $manager->persist($review2);
        
        $admin = new Admin();
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setUsername('admin');
        $admin->setPassword($this->encoderFactory->getEncoder(Admin::class)->encodePassword('admin', null));
        $manager->persist($admin);

         $manager->flush();
     }
}