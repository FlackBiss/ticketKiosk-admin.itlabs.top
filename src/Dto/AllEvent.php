<?php

namespace App\Dto;

class AllEvent
{
    public ?string $objectName = null;
    public ?\DateTimeImmutable $dateAt;
    public ?string $coordinates = null;
    public ?string $response = null;
}