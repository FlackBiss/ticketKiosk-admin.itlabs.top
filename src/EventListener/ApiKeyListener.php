<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiKeyListener
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (strpos($request->getPathInfo(), '/api/') !== 0) {
            return;
        }

        $key = $request->headers->get('X-API-KEY');

        if (!$key || $key !== $this->apiKey) {
            $response = new JsonResponse(['error' => 'Invalid API key'], 401);
            $event->setResponse($response);
        }
    }
}
