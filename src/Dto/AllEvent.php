<?php

namespace App\Dto;

class AllEvent
{
    public ?int $objectId = null;
    public ?string $objectName = null;
    public ?\DateTimeImmutable $dateAt;
    public ?string $coordinates = null;
    public ?string $response = null;
}