<?php declare(strict_types=1);

namespace App\Service\Helper;

use App\Database\Model\User;
use App\Database\Repository\WidgetRepository;

class WidgetHelper
{
    private WidgetRepository $repository;

    public function __construct()
    {
        $this->repository = new WidgetRepository();
    }
    public function getAllWidgetsForUser(User $user): array
    {
        return $this->repository->findByUser($user);
    }
}
