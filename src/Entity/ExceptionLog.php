<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\CreatedAtTrait;
use App\Repository\ExceptionLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ExceptionLogRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post()
    ],
    denormalizationContext: ['groups' => ['exceptionLog:write']],
)]
class ExceptionLog
{
    use CreatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['exceptionLog:write'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'exceptionLogs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['exceptionLog:write'])]
    private ?Terminal $terminal = null;

    #[Groups(['exceptionLog:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $log = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['exceptionLog:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['exceptionLog:write'])]
    private ?string $code = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['exceptionLog:write'])]
    private ?string $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTerminal(): ?Terminal
    {
        return $this->terminal;
    }

    public function setTerminal(?Terminal $terminal): static
    {
        $this->terminal = $terminal;

        return $this;
    }

    public function getLog(): ?string
    {
        return $this->log;
    }

    public function setLog(?string $log): static
    {
        $this->log = $log;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}
