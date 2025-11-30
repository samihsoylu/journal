<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Utility\Template;

final class Error extends AbstractController
{
    public function __construct(
        AuthenticationService $authenticationService,
        Template $template,
    ) {
        parent::__construct($authenticationService, $template);
    }

    public function renderNotFoundPage() : never
    {
        $this->renderTemplate('errors/404');
        exit;
    }

    public function methodNotAllowed() : void
    {
        $this->renderTemplate('errors/405');
    }
}
