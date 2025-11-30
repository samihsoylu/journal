<?php

declare(strict_types=1);

namespace App\Service\Helper;

use App\Database\Model\User;
use App\Database\Repository\WidgetRepository;

final readonly class WidgetHelper
{
    public function __construct(
        private WidgetRepository $repository,
    ) {}

    public function getAllWidgetsForUser(User $user) : array
    {
        return $this->repository->findByUser($user);
    }
}
