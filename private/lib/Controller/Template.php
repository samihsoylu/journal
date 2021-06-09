<?php

namespace App\Controller;

use App\Service\TemplateService;
use App\Validator\TemplateValidator;

class Template extends AbstractController
{
    public const TEMPLATES_URL = BASE_URL . '/templates';
    public const TEMPLATE_URL = BASE_URL . '/template';

    public const VIEW_TEMPLATE_URL   = self::TEMPLATE_URL . '/{id:\d+}';
    public const UPDATE_TEMPLATE_URL = self::VIEW_TEMPLATE_URL . '/update';
    public const DELETE_TEMPLATE_URL = self::VIEW_TEMPLATE_URL . '/delete/{antiCsrfToken}';

    private TemplateService $service;
    private TemplateValidator $validator;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in
        $this->redirectLoggedOutUsersToLoginPage();

        $this->service = new TemplateService();
        $this->validator = new TemplateValidator($_POST, $_GET);
    }

    public function indexView(): void
    {
        $this->template->setVariable('templates', $this->service->getAllTemplatesForUser($this->getUserId()));
        $this->template->render('templates/all');
    }

    public function createView(): void
    {
        $this->template->render('templates/all');
    }
}