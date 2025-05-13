<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\Controller\News\NewsController;
use App\Controller\Terminal\ChangeController;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\NewsRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(order: ['dateTimeAt' => 'DESC']),
    ],
    normalizationContext: ['groups' => ['news:read']]
)]
#[Post(
    controller: NewsController::class,
    openapi: new Model\Operation(
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'multipart/form-data' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'imageFile' => [
                                'type' => 'string',
                                'format' => 'binary',
                            ],
                            'title' => [
                                'type' => 'string',
                            ],
                            'description' => [
                                'type' => 'string',
                            ],
                            'dateTimeAt' => [
                                'type' => 'datetime',
                            ],
                            'shortDescription' => [
                                'type' => 'string',
                            ],
                            'eventId' => [
                                'type' => 'integer',
                            ],
                        ]
                    ]
                ]
            ])
        )
    ),
    deserialize: false)]
#[Get]
#[ORM\Entity(repositoryClass: NewsRepository::class)]
class News
{
    use UpdatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['news:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['news:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['news:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['news:read'])]
    private ?\DateTimeImmutable $dateTimeAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['news:read'])]
    private ?string $image = null;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(inversedBy: 'news')]
    #[Groups(['news:read'])]
    private ?Event $event = null;

    #[Vich\UploadableField(mapping: 'news_images', fileNameProperty: 'image')]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])]
    private ?File $imageFile = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['news:read'])]
    private ?string $shortDescription = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateTimeAt(): ?\DateTimeImmutable
    {
        return $this->dateTimeAt;
    }

    public function setDateTimeAt(\DateTimeImmutable $dateTimeAt): static
    {
        $this->dateTimeAt = $dateTimeAt;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            $this->updatedAt = new DateTime();
        }

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }
}
