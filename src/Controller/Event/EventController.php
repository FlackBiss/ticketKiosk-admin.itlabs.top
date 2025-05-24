<?php

namespace App\Controller\Event;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Event;

class EventController extends AbstractController
{
    public function __invoke(EventRepository $eventRepository): JsonResponse
    {
        $events = $eventRepository->findAll();

        usort($events, function (Event $a, Event $b) {
            return $a->getDateTimeAt() <=> $b->getDateTimeAt();
        });

        $events = array_unique($events);

        return $this->json([
            'dates' => array_map(
                fn(Event $event) => $event->getDateTimeAt()->format('Y-m-d'),
                $events
            ),
        ]);
    }
}
