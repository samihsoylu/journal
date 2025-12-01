<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserException;
use App\Service\AuthenticationService;
use App\Service\CategoryService;
use App\Service\TemplateService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Utility\Sanitize;
use App\Utility\Template as TemplateUtility;
use App\Validator\TemplateValidator;

final class Template extends AbstractController
{
    public const string TEMPLATES_URL = BASE_URL . '/templates';
    public const string TEMPLATE_URL = BASE_URL . '/template';
    public const string CREATE_TEMPLATE_URL = self::TEMPLATE_URL . '/create';
    public const string CREATE_TEMPLATE_POST_URL = self::CREATE_TEMPLATE_URL . '/action';
    public const string VIEW_TEMPLATE_URL = self::TEMPLATE_URL . '/{id:\d+}';
    public const string UPDATE_TEMPLATE_URL = self::VIEW_TEMPLATE_URL . '/update';
    public const string UPDATE_TEMPLATE_POST_URL = self::UPDATE_TEMPLATE_URL . '/action';
    public const string DELETE_TEMPLATE_URL = self::VIEW_TEMPLATE_URL . '/delete/{antiCsrfToken}';
    public const string GET_TEMPLATE_DATA_AS_JSON_URL = self::VIEW_TEMPLATE_URL . '/ajax';

    private readonly TemplateValidator $validator;

    public function __construct(
        AuthenticationService $authenticationService,
        TemplateUtility $template,
        private readonly TemplateService $service,
        private readonly CategoryService $categoryService,
        private readonly Sanitize $sanitize,
    ) {
        parent::__construct($authenticationService, $template);

        // for every action in this controller, the user must be logged in
        $this->redirectLoggedOutUsersToLoginPage();

        $this->validator = new TemplateValidator($_POST, $_GET, $authenticationService->getUserSession());
    }

    public function indexView() : void
    {
        $templates = $this->service->getAllTemplatesForUser($this->getUserId());

        $this->template->setVariable('templates', $templates);
        $this->renderTemplate('template/all');
    }

    public function create() : never
    {
        /** @see TemplateValidator::create() */
        $this->validator->validate(__FUNCTION__);

        $categoryId = $this->sanitize->int($_POST['category_id']);
        $templateTitle = $this->sanitize->string($_POST['template_title']);
        $templateContent = $this->sanitize->string($_POST['entry_content'], [Sanitize::OPTION_TRIM]);

        $this->service->createTemplate($this->getUserId(), $this->getUserEncryptionKey(), $categoryId, $templateTitle, $templateContent);

        $this->setNotification(Notification::TYPE_SUCCESS, sprintf('Template %s has been created', $templateTitle));

        Redirect::to(self::TEMPLATES_URL);
    }

    public function createView() : void
    {
        $categories = $this->categoryService->getAllCategoriesForUser($this->getUserId());

        $this->template->setVariable('categories', $categories);
        $this->renderTemplate('template/create');
    }

    public function update() : never
    {
        $this->template->setVariable('post', $_POST);

        /** @see TemplateValidator::update() */
        $this->validator->validate(__FUNCTION__);

        $categoryId = $this->sanitize->int($_POST['category_id']);
        $templateId = $this->sanitize->int($this->getRouteParameters()['id']);
        $templateTitle = $this->sanitize->string($_POST['template_title']);
        $templateContent = $this->sanitize->string($_POST['entry_content'], [Sanitize::OPTION_TRIM]);

        $this->service->updateTemplate(
            $this->getUserId(),
            $this->getUserEncryptionKey(),
            $categoryId,
            $templateId,
            $templateTitle,
            $templateContent,
        );

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            sprintf('Template %s has been updated', $templateTitle),
        );

        Redirect::to(self::TEMPLATES_URL);
    }

    public function updateView() : void
    {
        $templateId = $this->sanitize->int($this->getRouteParameters()['id']);

        try {
            $categories = $this->categoryService->getAllCategoriesForUser($this->getUserId());
            $template = $this->service->getTemplateForUser($templateId, $this->getUserId(), $this->getUserEncryptionKey());

            $this->template->setVariables([
                'template' => $template,
                'categories' => $categories,
            ]);
        } catch (UserException $userException) {
            $this->template->setVariable(
                Notification::TYPE_ERROR,
                $userException->getMessage(),
            );
        }

        $this->renderTemplate('template/update');
    }

    public function delete() : never
    {
        $templateId = $this->sanitize->int($this->getRouteParameters()['id']);

        // setting get variable for validator
        $_GET['form_key'] = $this->getRouteParameters()['antiCsrfToken'];

        /** @see TemplateValidator::delete() */
        $this->validator->validate(__FUNCTION__);

        $this->service->deleteTemplate($templateId, $this->getUserId());

        $this->setNotification(Notification::TYPE_SUCCESS, 'Template was removed');

        $this->deleteView();
    }

    public function deleteView() : never
    {
        // This is in its own method for the convenience of the error handler.
        Redirect::to(self::TEMPLATES_URL);
    }

    /**
     * Displays a JSON output of the queried template id. Used in AJAX call in create entry page.
     */
    public function getTemplateAsJsonView() : void
    {
        $templateId = $this->sanitize->int($this->getRouteParameters()['id']);

        try {
            $template = $this->service->getTemplateForUser($templateId, $this->getUserId(), $this->getUserEncryptionKey());

            echo json_encode($template, JSON_PRETTY_PRINT);
        } catch (UserException $userException) {
            http_response_code(404);
            echo $userException->getMessage();
        }
    }
}
