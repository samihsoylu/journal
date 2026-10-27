<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Service\Security\TwoFactorChange;
use App\Service\Security\TwoFactorService;
use App\Service\Security\TwoFactorStore;
use App\Utility\Redirect;
use App\Utility\Template;
use App\Validator\SecurityValidator;

final class Security extends AbstractController
{
    public const string SECURITY_URL = BASE_URL . '/account/security';
    public const string SECURITY_BEGIN_URL = self::SECURITY_URL . '/begin';
    public const string SECURITY_CONFIRM_URL = self::SECURITY_URL . '/confirm';
    public const string SECURITY_CANCEL_URL = self::SECURITY_URL . '/cancel';
    public const string SECURITY_DISABLE_URL = self::SECURITY_URL . '/disable';
    public const string SECURITY_REGENERATE_URL = self::SECURITY_URL . '/regenerate';

    public function __construct(
        private readonly AuthenticationService $auth,
        Template $template,
        private readonly TwoFactorService $twoFactor,
        private readonly TwoFactorStore $store,
    ) {
        parent::__construct($auth, $template);
    }

    public function indexView() : void
    {
        $this->redirectLoggedOutUsersToLoginPage();
        $id = $this->getUserId();
        $this->template->setVariables(['enabled' => $this->store->find($id)?->enabled() ?? false, 'setup' => $this->twoFactor->setup($id, $this->getUserEncryptionKey())]);
        $this->renderTemplate('account/security');
    }

    public function begin() : never
    {
        $this->validate(__FUNCTION__);
        $this->twoFactor->beginSetup($this->getUserId(), $this->getUserEncryptionKey(), $_POST['password']);
        Redirect::to(self::SECURITY_URL);
    }

    public function confirm() : void
    {
        $this->validate(__FUNCTION__);
        $this->displayRecovery($this->twoFactor->confirm($this->getUserId(), $this->getUserEncryptionKey(), $_POST['code']));
    }

    public function regenerate() : void
    {
        $this->validate(__FUNCTION__);
        $this->displayRecovery($this->twoFactor->regenerate($this->getUserId(), $this->getUserEncryptionKey(), $_POST['password'], $_POST['code']));
    }

    public function disable() : never
    {
        $this->validate(__FUNCTION__);
        $state = $this->twoFactor->disable($this->getUserId(), $this->getUserEncryptionKey(), $_POST['password'], $_POST['code']);
        $this->auth->refreshSecuritySession($state);
        Redirect::to(self::SECURITY_URL);
    }

    public function cancel() : never
    {
        $this->validate(__FUNCTION__);
        $this->twoFactor->cancelSetup();
        Redirect::to(self::SECURITY_URL);
    }

    public function beginView() : void
    {
        $this->indexView();
    }

    public function confirmView() : void
    {
        $this->indexView();
    }

    public function regenerateView() : void
    {
        $this->indexView();
    }

    public function disableView() : void
    {
        $this->indexView();
    }

    public function cancelView() : void
    {
        $this->indexView();
    }

    private function validate(string $operation) : void
    {
        $this->redirectLoggedOutUsersToLoginPage();
        new SecurityValidator($_POST, [], $this->auth->getUserSession())->validate($operation);
    }

    private function displayRecovery(TwoFactorChange $change) : void
    {
        $this->auth->refreshSecuritySession($change);
        $this->template->setVariable('recoveryCodes', $change->codes);
        $this->renderTemplate('account/recovery');
    }
}
