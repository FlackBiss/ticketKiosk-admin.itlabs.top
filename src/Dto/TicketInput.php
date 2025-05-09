<?php

namespace App\Dto;


class TicketInput
{
    public string $place;
    public float $price;
    public int $eventId;
    public string $type;
    public ?string $uuid;
    public string $email;
    public string $surname;
    public string $name;
}