<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ExceptionLog;
use App\Entity\Ticket;
use App\Repository\EventRepository;
use App\Repository\ExceptionLogRepository;
use App\Repository\TerminalRepository;
use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use function Symfony\Component\String\u;

readonly class TicketProcessor implements ProcessorInterface
{
    public function __construct(
        private EventRepository     $eventRepository,
        private TicketRepository $ticketRepository,
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Ticket
    {
        if ($this->eventRepository->find($data->eventId) === null)
        {
            throw new \Exception('По введённому id, мероприятия не существует');
        }

        $ticket = new Ticket();
        $event = $this->eventRepository->find($data->eventId);

        $ticket
            ->setPlace($data->place)
            ->setPrice($data->price)
            ->setEvent($event)
            ->setType($data->type);

        $this->ticketRepository->save($ticket, true);

        return $ticket;
    }
}