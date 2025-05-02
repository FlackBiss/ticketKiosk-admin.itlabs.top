<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\SessionsInput;
use App\Entity\SessionEvents;
use App\Entity\Sessions;
use App\Repository\EventRepository;
use App\Repository\SessionEventsRepository;
use App\Repository\SessionsRepository;
use App\Repository\TerminalRepository;

readonly class SessionsProcessor implements ProcessorInterface
{
    public function __construct(
        private EventRepository         $eventRepository,
        private TerminalRepository      $terminalRepository,
        private SessionsRepository      $sessionsRepository,
        private SessionEventsRepository $sessionEventsRepository,
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Sessions
    {
        if ($this->terminalRepository->find($data->terminalId) === null)
        {
            throw new \Exception('По введённому id, терминала не существует');
        }
        /** @var SessionsInput $data */
        $startAt = $data->startAt;
        $endAt = $data->endAt;
        $deltaTime = $endAt->getTimestamp() - $startAt->getTimestamp();

        $session = new Sessions();
        $terminalId = $this->terminalRepository->find($data->terminalId);
        $session
            ->setStartAt($startAt)
            ->setEndAt($endAt)
            ->setCountEvents(count($data->allEvent ?? []))
            ->setTerminal($terminalId)
            ->setDeltaTime($deltaTime);
        foreach ($data->allEvent as $item) {

            if ($this->eventRepository->find($item->objectId) === null)
            {
                throw new \Exception('По введённому id, раздела не существует');
            }
            $event = new SessionEvents();

            $event
                ->setDateAt($item->time)
                ->setObjectName($item->objectName)
                ->setDateAt($item->dateAt)
                ->setCoordinates($item->coordinates)
                ->setResponse($item->response);

            $session->addEvent($event);

            $this->sessionsRepository->save($session, true);
            $this->sessionEventsRepository->save($event, true);
        }

        return $session;
    }
}