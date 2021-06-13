<?php

namespace App\Tests;

use App\Entity\Review;
use App\SpamChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

 class SpamCheckerTest extends TestCase
 {
    public function testSpamScoreWithInvalidRequest()
     {
        $this->assertTrue(true);
        $review = new Review();
        $review->setCreatedAtValue();
        $context = [];

        $client = new MockHttpClient([new MockResponse('invalid', ['response_headers' => ['x-akismet-debug-help: Invalid key']])]);
        $checker = new SpamChecker($client, 'abcde');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to check for spam: invalid (Invalid key).');
        $checker->getSpamScore($review, $context);
     }


     /**
    * @dataProvider getReviews
    */
   public function testSpamScore(int $expectedScore, ResponseInterface $response, review $review, array $context)
   {
       $client = new MockHttpClient([$response]);
       $checker = new SpamChecker($client, 'abcde');

       $score = $checker->getSpamScore($review, $context);
       $this->assertSame($expectedScore, $score);
   }

   public function getReviews(): iterable
   {
       $review = new Review();
       $review->setCreatedAtValue();
       $context = [];

       $response = new MockResponse('', ['response_headers' => ['x-akismet-pro-tip: discard']]);
       yield 'blatant_spam' => [2, $response, $review, $context];

       $response = new MockResponse('true');
       yield 'spam' => [1, $response, $review, $context];

       $response = new MockResponse('false');
       yield 'ham' => [0, $response, $review, $context];
   }
 }