<?php

namespace App\Entity\Model;

use App\Entity\Event;

class DateTimeRangeModel
{
    private \DateTimeInterface $dateFrom;
    private \DateTimeInterface $dateTo;
    private ?Event $event = null;

    public function getDateFrom(): \DateTimeInterface { return $this->dateFrom; }
    public function setDateFrom(\DateTimeInterface $dateFrom): self { $this->dateFrom = $dateFrom; return $this; }

    public function getDateTo(): \DateTimeInterface { return $this->dateTo; }
    public function setDateTo(\DateTimeInterface $dateTo): self { $this->dateTo = $dateTo; return $this; }

    public function getEvent(): ?Event { return $this->event; }
    public function setEvent(?Event $event): self { $this->event = $event; return $this; }
}
