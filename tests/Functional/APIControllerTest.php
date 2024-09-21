<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

class APIControllerTest extends WebTestCase
{

    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertEquals('A rendszer fogadja az API hívásokat', $client->getResponse()->getContent());
    }

    public function testGetLists()
    {
        $mockResponses = [
            new MockResponse(json_encode([
                ['userId' => 1, 'id' => 1, 'title' => 'Test post 1', 'body' => 'et iusto sed quo iure nvoluptatem occaecati omnis'],
                ['userId' => 2, 'id' => 2, 'title' => 'Test post 2', 'body' => 'quibusdam animi sint suscipit qui sint possimus'],
                ['userId' => 2, 'id' => 2, 'title' => 'Test post 3', 'body' => 'animi sint suscipit qui sint possquibusdamimus'],
                ['userId' => 2, 'id' => 2, 'title' => 'Test post 4', 'body' => 'ollitia nobis aliquid molestiae et ea nemo']
            ])),
            
            new MockResponse(json_encode([ 
                ['value' => 'Chuck Norris joke 1']
            ])),

            new MockResponse(json_encode([
                ['value' => 'Chuck Norris joke 2']
            ])),
        ];
    
        $mockHttpClient = new MockHttpClient($mockResponses);
        
        $client = static::createClient();
        $client->getContainer()->set(HttpClientInterface::class, $mockHttpClient);
        $client->request('GET', '/api/get-lists/1/2');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('placeholder', $responseContent);
        $this->assertArrayHasKey('chuck', $responseContent);
        $this->assertCount(4, $responseContent['placeholder']);
        $this->assertCount(2, $responseContent['chuck']);
        $this->assertEquals('Chuck Norris joke 1', $responseContent['chuck'][0][0]['value']);
        $this->assertEquals('Chuck Norris joke 2', $responseContent['chuck'][1][0]['value']);
    }
    
}