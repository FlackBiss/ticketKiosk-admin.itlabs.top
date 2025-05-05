<?php

namespace App\Controller\Event;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class EventController extends AbstractController
{
    public function __construct(private readonly EventRepository $eventRepository,)
    {
    }

    public function __invoke(): JsonResponse
    {
        $events = $this->eventRepository->findAll();

        $dates = array_map(
            fn($event) => $event->getDateTimeAt(),
            $events
        );

        return $this->json($dates);
    }
}
