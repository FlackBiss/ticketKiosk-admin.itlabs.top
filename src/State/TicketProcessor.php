<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Event;
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

        if ($data->uuid)
        {
            $event = $this->eventRepository->find($data->eventId);

            $schemeData = $event->getSchemeDataJson();

            foreach ($schemeData as &$value) {
                if ($value['uuid'] === $data->uuid) {
                    $value['booked'] = true;
                    break;
                }
            }

            $event->setSchemeData(json_encode($schemeData, true));
            $this->eventRepository->save($event, true);
        }

        $ticket = new Ticket();
        $event = $this->eventRepository->find($data->eventId);

        $ticket
            ->setPlace($data->place)
            ->setPrice($data->price)
            ->setEvent($event)
            ->setType($data->type)
            ->setEmail($data->email)
            ->setSurname($data->surname)
            ->setName($data->name);

        $this->ticketRepository->save($ticket, true);

        return $ticket;
    }
}