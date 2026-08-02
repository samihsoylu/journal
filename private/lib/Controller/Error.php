<?php

declare(strict_types=1);

namespace App\Controller;

final class Error extends AbstractController
{
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
