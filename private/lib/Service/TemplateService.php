<?php declare(strict_types=1);

namespace App\Service;

use App\Database\Model\Template;
use App\Database\Repository\TemplateRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Service\Helper\CategoryHelper;
use App\Service\Helper\TemplateHelper;
use App\Service\Helper\UserHelper;
use App\Service\Model\TemplateDecorator;
use App\Utility\Registry;
use Defuse\Crypto\Key;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class TemplateService
{
    private TemplateRepository $repository;
    private TemplateHelper $templateHelper;
    private UserHelper $userHelper;
    private CategoryHelper $categoryHelper;

    public function __construct()
    {
        /** @var TemplateRepository $repository */
        $repository = Registry::get(TemplateRepository::class);
        $this->repository     = $repository;

        $this->templateHelper = new TemplateHelper();
        $this->userHelper     = new UserHelper();
        $this->categoryHelper = new CategoryHelper();
    }

    /**
     * @return Template[]
     */
    public function getAllTemplatesForUser(int $userId): array
    {
        $user = $this->userHelper->getUserById($userId);

        return $this->templateHelper->getAllTemplatesForUser($user);
    }

    public function getTemplateForUser(int $templateId, int $userId, Key $key, bool $getEntryContentAsMarkup = false): TemplateDecorator
    {
        $template = $this->templateHelper->getTemplateForUser($templateId, $userId);

        $templateContent = $template->getContentAsMarkup($key);
        if ($getEntryContentAsMarkup === false) {
            $templateContent = $template->getContentDecrypted($key);
        }

        return new TemplateDecorator(
            $template->getId(),
            $template->getTitle(),
            $template->getReferencedCategory()->getId(),
            $template->getReferencedCategory()->getName(),
            $templateContent,
        );
    }

    public function createTemplate(int $userId, Key $encryptionKey, int $categoryId, string $templateTitle, string $templateContent)
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);

        $user = $this->userHelper->getUserById($userId);

        $template = new Template();
        $template->setReferencedUser($user)
                 ->setReferencedCategory($category)
                 ->setTitle($templateTitle)
                 ->setContentAndEncrypt($templateContent, $encryptionKey);

        $this->repository->queue($template);

        try {
            $this->repository->save();
        } catch (UniqueConstraintViolationException $e) {
            throw InvalidArgumentException::templateAlreadyExists($templateTitle);
        }
    }

    public function updateTemplate(int $userId, Key $encryptionKey, int $categoryId, int $templateId, string $templateTitle, string $templateContent): void
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);
        $template = $this->templateHelper->getTemplateForUser($templateId, $userId);

        $template->setTitle($templateTitle)
                 ->setContentAndEncrypt($templateContent, $encryptionKey)
                 ->setReferencedCategory($category);

        $this->repository->queue($template);
        $this->repository->save();
    }

    /**
     * Removes an existing template for user
     *
     * @return void
     */
    public function deleteTemplate(int $templateId, int $userId): void
    {
        $template = $this->templateHelper->getTemplateForUser($templateId, $userId);

        // queue entry to be removed
        $this->repository->remove($template);

        // executed queued tasks
        $this->repository->save();
    }
}
