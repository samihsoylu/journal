<?php declare(strict_types=1);

namespace App\Service\Helper;

use App\Database\Model\Template;
use App\Database\Model\User;
use App\Database\Repository\TemplateRepository;
use App\Exception\UserException\NotFoundException;
use App\Utility\Registry;

class TemplateHelper
{
    private TemplateRepository $repository;

    public function __construct()
    {
        /** @var TemplateRepository $templateRepository */
        $templateRepository = Registry::get(TemplateRepository::class);

        $this->repository = $templateRepository;
    }

    public function getTemplateForUser(int $templateId, $userId): Template
    {
        $template = $this->repository->getById($templateId);
        $this->ensureTemplateIsNotNull($template, $templateId);
        $this->ensureUserOwnsTemplate($template, $userId);

        return $template;
    }

    /**
     * @return Template[]
     */
    public function getAllTemplatesForUser(User $user): array
    {
        return $this->repository->findByUser($user);
    }

    public function getTemplateCountForUser(User $user): int
    {
        $templates = $this->repository->findByUser($user);

        return count($templates);
    }

    public function getTemplateCountForCategory(int $userId, int $categoryId): int
    {
        $templates = $this->getTemplatesForUserByCategory($userId, $categoryId);

        return count($templates);
    }

    /**
     * @return Template[]
     */
    public function getTemplatesForUserByCategory(int $userId, int $categoryId): array
    {
        return $this->repository->findByUserIdAndCategoryId($userId, $categoryId);
    }

    private function ensureTemplateIsNotNull(?Template $template, int $templateId): void
    {
        if ($template === null) {
            throw NotFoundException::entityIdNotFound(Template::getClassName(), $templateId);
        }
    }

    private function ensureUserOwnsTemplate(Template $template, int $userId): void
    {
        if ($template->getReferencedUser()->getId() !== $userId) {
            // found template does not belong to the logged in user
            throw NotFoundException::entityIdNotFound(Template::getClassName(), $template->getId());
        }
    }
}
