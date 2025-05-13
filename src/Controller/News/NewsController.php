<?php

namespace App\Controller\News;

use App\Entity\News;
use App\Repository\EventRepository;
use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class NewsController extends AbstractController
{
    public function __construct(
        private readonly NewsRepository $newsRepository,
        private readonly EventRepository $eventRepository,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $formData = $request->request->all();

        $imageFile = $request->files->get('imageFile');

        $news = new News();

        $news->setTitle($formData['title']);
        $news->setDescription($formData['description']);
        $news->setDateTimeAt(new \DateTimeImmutable($formData['dateTimeAt']));
        $news->setShortDescription($formData['shortDescription']);
        $news->setImageFile($imageFile);

        if ($formData['eventId'])
        {
            $news->setEvent($this->eventRepository->find($formData['eventId']));
        }

        $this->newsRepository->save($news, true);

        return $this->json([
            "status" => "success",
            "details" => "News add status successfully."
        ]);
    }
}
