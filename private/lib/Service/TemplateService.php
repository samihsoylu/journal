<?php

declare(strict_types=1);

namespace App\Service;

use App\Database\Model\Template;
use App\Database\Repository\TemplateRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Service\Entry\EntryDateTime;
use App\Service\Helper\CategoryHelper;
use App\Service\Helper\TemplateHelper;
use App\Service\Helper\UserHelper;
use App\Service\Model\TemplateDecorator;
use App\Service\Security\RichContentSanitizer;
use App\Service\Template\EntryTitlePattern;
use DateTimeImmutable;
use DateTimeZone;
use Defuse\Crypto\Key;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class TemplateService
{
    public function __construct(
        private TemplateRepository $repository,
        private TemplateHelper $templateHelper,
        private UserHelper $userHelper,
        private CategoryHelper $categoryHelper,
        private RichContentSanitizer $contentSanitizer = new RichContentSanitizer(),
        private EntryTitlePattern $titlePattern = new EntryTitlePattern(),
        private EntryDateTime $entryDateTime = new EntryDateTime(),
    ) {}

    /**
     * @return Template[]
     */
    public function getAllTemplatesForUser(int $userId) : array
    {
        $user = $this->userHelper->getUserById($userId);

        return $this->templateHelper->getAllTemplatesForUser($user);
    }

    public function getTemplateForUser(int $templateId, int $userId, Key $key, ?string $dateTime = null) : TemplateDecorator
    {
        $template = $this->templateHelper->getTemplateForUser($templateId, $userId);

        $templateContent = $this->contentSanitizer->sanitize($template->getContentDecrypted($key));
        $timezone = $template->getReferencedUser()->getTimezone();
        $timestamp = $this->entryDateTime->parse($dateTime, $timezone) ?? time();
        $date = new DateTimeImmutable('@' . $timestamp)->setTimezone(new DateTimeZone($timezone ?? 'UTC'));

        return new TemplateDecorator(
            $template->getId(),
            $template->getTitle(),
            $template->getReferencedCategory()->getId(),
            $template->getReferencedCategory()->getName(),
            $templateContent,
            $template->getDefaultEntryTitlePattern(),
            $this->titlePattern->render($template->getDefaultEntryTitlePattern(), $template->getTitle(), $date),
        );
    }

    public function createTemplate(int $userId, Key $encryptionKey, int $categoryId, string $templateTitle, string $templateContent, ?string $defaultTitlePattern = null) : void
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);

        $user = $this->userHelper->getUserById($userId);

        $template = new Template();
        $template->setReferencedUser($user)
            ->setReferencedCategory($category)
            ->setTitle($templateTitle)
            ->setContentAndEncrypt($templateContent, $encryptionKey)
            ->setDefaultEntryTitlePattern($this->titlePattern->normalize($defaultTitlePattern, $templateTitle))
        ;

        $this->repository->queue($template);

        try {
            $this->repository->save();
        } catch (UniqueConstraintViolationException) {
            throw InvalidArgumentException::templateAlreadyExists($templateTitle);
        }
    }

    public function updateTemplate(int $userId, Key $encryptionKey, int $categoryId, int $templateId, string $templateTitle, string $templateContent, ?string $defaultTitlePattern = null) : void
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);
        $template = $this->templateHelper->getTemplateForUser($templateId, $userId);

        $pattern = $this->titlePattern->normalize($defaultTitlePattern ?? $template->getDefaultEntryTitlePattern(), $templateTitle);
        $template->setTitle($templateTitle)
            ->setDefaultEntryTitlePattern($pattern)
            ->setContentAndEncrypt($templateContent, $encryptionKey)
            ->setReferencedCategory($category)
        ;

        $this->repository->queue($template);
        $this->repository->save();
    }

    /**
     * Removes an existing template for user.
     */
    public function deleteTemplate(int $templateId, int $userId) : void
    {
        $template = $this->templateHelper->getTemplateForUser($templateId, $userId);

        // queue entry to be removed
        $this->repository->remove($template);

        // executed queued tasks
        $this->repository->save();
    }
}
