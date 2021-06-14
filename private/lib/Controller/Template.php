<?php

namespace App\Controller;

use App\Service\CategoryService;
use App\Service\TemplateService;
use App\Utility\Redirect;
use App\Utility\Sanitize;
use App\Validator\TemplateValidator;

class Template extends AbstractController
{
    public const TEMPLATES_URL = BASE_URL . '/templates';
    public const TEMPLATE_URL = BASE_URL . '/template';

    public const CREATE_TEMPLATE_URL       = self::TEMPLATE_URL . '/create';
    public const CREATE_TEMPLATE_POST_URL  = self::CREATE_TEMPLATE_URL . '/action';

    public const VIEW_TEMPLATE_URL   = self::TEMPLATE_URL . '/{id:\d+}';
    public const UPDATE_TEMPLATE_URL = self::VIEW_TEMPLATE_URL . '/update';
    public const UPDATE_TEMPLATE_POST_URL  = self::UPDATE_TEMPLATE_URL . '/action';

    public const DELETE_TEMPLATE_URL = self::VIEW_TEMPLATE_URL . '/delete/{antiCsrfToken}';

    private TemplateService $service;
    private TemplateValidator $validator;
    private CategoryService $categoryService;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in
        $this->redirectLoggedOutUsersToLoginPage();

        $this->service = new TemplateService();
        $this->validator = new TemplateValidator($_POST, $_GET);
        
        $this->categoryService = new CategoryService();
    }

    public function indexView(): void
    {
        $this->injectSessionVariableToTemplate();
        $templates = $this->service->getAllTemplatesForUser($this->getUserId());

        $this->template->setVariable('templates', $templates);
        $this->template->render('templates/all');
    }

    public function create(): void
    {
        /** @see TemplateValidator::create() */
        $this->validator->validate(__FUNCTION__);

        $categoryId   = Sanitize::int($_POST['category_id']);
        $templateTitle   = Sanitize::string($_POST['template_title'], 'strip|capitalize');
        $templateContent = Sanitize::string($_POST['template_content'], 'trim');

        $templateId = $this->service->createTemplate($this->getUserId(), $categoryId, $templateTitle, $templateContent);

        $this->setNotification(Notification::TYPE_SUCCESS, "Template {$templateTitle} has been created");

        if (isset($_POST['redirectToTemplatesOverview'])) {
            Redirect::to(self::TEMPLATES_URL);
        }
        Redirect::to(self::TEMPLATE_URL . "/{$templateId}");
    }

    public function createView(): void
    {
        $this->injectSessionVariableToTemplate();
        $categories = $this->categoryService->getAllCategoriesForUser($this->getUserId());

        $this->template->setVariable('categories', $categories);
        $this->template->render('templates/create');
    }

    public function update(): void
    {
        $this->template->setVariable('post', $_POST);

        /** @see TemplateValidator::update() */
        $this->validator->validate(__FUNCTION__);

        $categoryId      = Sanitize::int($_POST['category_id']);
        $templateId      = Sanitize::int($this->getRouteParameters()['id']);
        $templateTitle   = Sanitize::string($_POST['template_title'], 'strip|capitalize');
        $templateContent = Sanitize::string($_POST['template_content'], 'trim');

        $this->service->updateTemplate(
            $this->getUserId(),
            $templateId,
            $categoryId,
            $templateTitle,
            $templateContent
        );

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            "Template {$templateTitle} has been updated"
        );

        Redirect::to(self::TEMPLATES_URL);
    }

    public function updateView(): void
    {
        $this->injectSessionVariableToTemplate();

        $templateId = Sanitize::int($this->getRouteParameters()['id']);

        try {
            $categories = $this->categoryService->getAllCategoriesForUser($this->getUserId());
            $template = $this->service->getTemplateForUser($templateId, $this->getUserId());

            $this->template->setVariables([
                'template' => $template,
                'categories' => $categories,
            ]);
        } catch (UserException $e) {
            $this->template->setVariable(
                Notification::TYPE_ERROR,
                $e->getMessage()
            );
        }

        $this->template->render('template/update');
    }

    public function delete(): void
    {
        $templateId = Sanitize::int($this->getRouteParameters()['id']);

        // setting get variable for validator
        $_GET['form_key'] = $this->getRouteParameters()['antiCsrfToken'];

        /** @see TemplateValidator::delete() */
        $this->validator->validate(__FUNCTION__);

        $this->service->deleteTemplate($templateId, $this->getUserId());

        $this->setNotification(Notification::TYPE_SUCCESS, 'Template was removed');

        $this->deleteView();
    }

    public function deleteView(): void
    {
        $this->injectSessionVariableToTemplate();

        // This is in its own method for the convenience of the error handler.
        Redirect::to(self::TEMPLATES_URL);
    }
}