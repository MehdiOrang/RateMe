<?php

namespace App\Tests;

use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Your Review');
    }

    public function testProductPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertCount(2, $crawler->filter('h4'));

        $client->clickLink('View');

        $this->assertPageTitleContains('Product Review');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'number2');
        $this->assertSelectorExists('div:contains("review")');
    }

    public function testReviewSubmission()
    {
        $client = static::createClient();
        $client->request('GET', '/product/number2');
        $client->submitForm('Submit', [
            'review_form[author]' => 'Fabien',
            'review_form[rate]' => 2,
            'review_form[text]' => 'Some feedback from an automated functional test',
            'review_form[email]' => $email ='me@automat.ed',
            'review_form[photo]' => dirname(__DIR__, 2).'/public/images/under-construction.gif',
        ]);
        $this->assertResponseRedirects();
        
        // simulate comment validation
        $comment = self::$container->get(CommentRepository::class)->findOneByEmail($email);
         $comment->setState('published');
        self::$container->get(EntityManagerInterface::class)->flush();

        $client->followRedirect();
        $this->assertSelectorExists('div:contains("review")');
    }



}
