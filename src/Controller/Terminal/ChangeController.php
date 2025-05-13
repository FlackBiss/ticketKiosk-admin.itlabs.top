<?php

namespace App\Controller\Terminal;

use App\Repository\TerminalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ChangeController extends AbstractController
{
    public function __construct(
        private readonly TerminalRepository $terminalRepository,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $terminal = $this->terminalRepository->find($data['id']);

        if (!$terminal)
        {
            return $this->json('Нет такого вот терминала.', 403);
        }

        $terminal->setKkt($data['kkt']);

        $this->terminalRepository->save($terminal, true);

        return $this->json([
            "status" => "success",
            "details" => "Terminal Kkt status successfully."
        ]);
    }
}
