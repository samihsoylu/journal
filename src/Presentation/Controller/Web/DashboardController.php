<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Presentation\Controller\Web;

use SamihSoylu\Journal\Application\Service\Contract\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController
{
    public function __construct(
        private UserServiceInterface $userService,
    ) {}

    #[Route(path: '/home', name: 'app_home', methods: ['GET'])]
    public function index(Request $request, UserServiceInterface $userService): Response
    {
        return new JsonResponse(['status' => 'success'], Response::HTTP_ACCEPTED);
    }
}
