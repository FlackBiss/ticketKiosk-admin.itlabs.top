<?php

namespace App\Controller\Terminal;

use App\Repository\TerminalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class TerminalPingController extends AbstractController
{
    public function __construct(private readonly TerminalRepository $terminalRepository,)
    {
    }

    #[Route('/api/terminals-ping', name: 'api_terminals_ping')]
    public function pingAllTerminals(): JsonResponse
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $data = [];
        foreach ($this->terminalRepository->findAll() as $terminal) {
            $ip = $terminal->getIpAddress();
            $isOnline = false;

            if ($ip) {
                if ($isWindows) {
                    $ping = shell_exec(sprintf('ping -n 1 -w 1000 %s', escapeshellarg($ip)));
                    $isOnline = (stripos($ping, 'TTL=') !== false);
                } else {
                    $ping = shell_exec(sprintf('ping -c 1 -W 1 %s', escapeshellarg($ip)));
                    $isOnline = (str_contains($ping, '1 received') || str_contains($ping, '1 packets received'));
                }
            }

            $data[] = [
                'id' => $terminal->getId(),
                'ip' => $ip,
                'online' => $isOnline,
            ];
        }
        return $this->json($data);
    }
}
