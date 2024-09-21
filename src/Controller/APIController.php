<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class APIController
{
    
    public HttpClientInterface $httpClient;


    public function __construct(HttpClientInterface $httpClient) {
        $this->httpClient = $httpClient;
    }


    #[Route('/')]
    public function index(): Response
    {
        return new Response('A rendszer fogadja az API hívásokat', Response::HTTP_OK);
    }

    #[Route('/api/get-lists/{userId1<\d+>}/{userId2<\d+>}', methods: ['GET'])]
    public function getLists(int $userId1, int $userId2)
    {
        try {

            $jsonPlaceholderData = json_decode($this->getJsonPlaceholderData($userId1, $userId2));
            if (empty($jsonPlaceholderData)) {
                return new Response(json_encode(['error' => 'Invalid data from JSONPlaceholder API']), Response::HTTP_NOT_FOUND);
            }
            $jsonPlaceholderDataLength = count($jsonPlaceholderData);

            $chuckNorrisData = $this->getChuchNorrisData($jsonPlaceholderDataLength);
            if (empty($chuckNorrisData)) {
                return new Response(json_encode(['error' => 'Invalid data from ChuckNorris API']), Response::HTTP_NOT_FOUND);
            }

            $content = json_encode([
                'placeholder' => $jsonPlaceholderData, 
                'chuck' => $chuckNorrisData
            ]);

            return new Response($content, Response::HTTP_OK);

        } catch (\Exception $e) {
            return new Response(json_encode(['error' => 'Internal server error', 'description' => $e->getMessage()]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getJsonPlaceholderData(int $userId1, int $userId2)
    {
        $url = 'https://jsonplaceholder.typicode.com/posts?userId=' . $userId1 . '&userId=' . $userId2;
        $response = $this->httpClient->request('GET', $url);
        $jsonPlaceholderData = $response->getContent();

        return $jsonPlaceholderData;
    }

    private function getChuchNorrisData(int $jsonPlaceholderLenght)
    {
        $chuckNorrisRequests = [];
        $chuckNorrisData = [];
        $url = 'https://api.chucknorris.io/jokes/random';

        for ($i = 0; $i < ($jsonPlaceholderLenght / 2); $i++) {
            $chuckNorrisRequests[] = $this->httpClient->request('GET', $url);
        }

        foreach ($chuckNorrisRequests as $request) {
            $chuckNorrisData[] = json_decode($request->getContent(), true);
        }

        return $chuckNorrisData;
    }

}