<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Service\UserService;
use App\Service\WidgetService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Utility\Sanitize;
use App\Validator\AccountValidator;

class Account extends AbstractController
{
    public const ACCOUNT_URL = BASE_URL . '/account';
    public const UPDATE_EMAIL_POST_URL = self::ACCOUNT_URL . '/update/email';
    public const CHANGE_PASSWORD_POST_URL = self::ACCOUNT_URL . '/change-password';
    public const DELETE_ACCOUNT_POST_URL = self::ACCOUNT_URL . '/delete';
    public const UPDATE_WIDGETS_POST_URL = self::ACCOUNT_URL . '/widgets/update';
    public const EXPORT_ENTRIES_POST_URL = self::ACCOUNT_URL . '/export-entries';
    public const EXPORT_DOWNLOAD_URL = self::EXPORT_ENTRIES_POST_URL . '/download';
    public const EXPORT_DELETE_POST_URL = self::EXPORT_ENTRIES_POST_URL . '/delete';
    public const EXPORT_DOWNLOAD_GET_URL = self::EXPORT_DOWNLOAD_URL . '/{fileName}';
    public const SET_DATE_TIME_ZONE_POST_URL = self::ACCOUNT_URL . '/set-date-time-zone';
    private UserService $userService;
    private WidgetService $widgetService;
    private AccountValidator $validator;
    private AuthenticationService $authenticationService;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in
        $this->redirectLoggedOutUsersToLoginPage();

        $this->userService = new UserService();
        $this->widgetService = new WidgetService();
        $this->authenticationService = new AuthenticationService();
        $this->validator = new AccountValidator($_POST);
    }

    /**
     * View all available account settings page
     *
     * @return void
     */
    public function indexView(): void
    {
        $user = $this->userService->getUser($this->getUserId());
        $this->template->setVariable('user', $user);

        $enabledWidgets = $this->widgetService->getEnabledWidgetsForUser($this->getUserId());
        $this->template->setVariable('enabledWidgets', $enabledWidgets);

        $exportedFiles = $this->userService->getZipFileNamesForExportedEntriesByUser($this->getUserId());
        $this->template->setVariable('exportedFiles', $exportedFiles);

        $this->renderTemplate('account/index');
    }

    /**
     * Change email post action from the Account settings page
     *
     * @return void
     */
    public function changeEmail(): void
    {
        $this->validator->validate(__FUNCTION__);

        $newEmail = Sanitize::string($_POST['email'], [Sanitize::OPTION_LOWERCASE, Sanitize::OPTION_STRIP]);

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            "Email was updated to {$newEmail}"
        );

        $this->userService->changeUserEmail($this->getUserId(), $newEmail);

        $this->changeEmailView();
    }

    public function changeEmailView(): void
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    /**
     * Change password action from the account settings page
     *
     * @return void
     */
    public function changePassword(): void
    {
        $this->validator->validate(__FUNCTION__);

        $this->userService->changePassword($this->getUserId(), $_POST['currentPassword'], $_POST['newPassword']);

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            'Password changed'
        );

        $this->changePasswordView();
    }

    public function changePasswordView(): void
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    /**
     * Account delete post action on account settings page
     *
     * @return void
     */
    public function deleteAccount(): void
    {
        $this->validator->validate(__FUNCTION__);

        $this->userService->deleteUserForUser($_POST['password'], $this->getUserId());

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            'Your account has been deleted'
        );

        Redirect::to(BASE_URL . '/');
    }

    public function deleteAccountView(): void
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    /**
     * Update widget settings post action
     *
     * @return void
     */
    public function updateWidgets(): void
    {
        $this->validator->validate(__FUNCTION__);

        unset($_POST['form_key']);

        $this->widgetService->updateWidgetSettingsForUser(
            $this->getUserId(),
            $_POST
        );

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            'Widgets updated'
        );

        $this->updateWidgetsView();
    }

    public function updateWidgetsView(): void
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    public function exportEntries(): void
    {
        $this->validator->validate(__FUNCTION__);

        $processId = $this->userService->exportUserEntries($this->getUserId(), $this->getUserEncryptionKey());

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            "Export triggered, process id: {$processId}"
        );

        Redirect::to(self::ACCOUNT_URL);
    }

    public function exportEntriesView(): void
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    public function downloadEntryExport(): void
    {
        $targetFileName = $this->getRouteParameters()['fileName'];

        $filePath = $this->userService->getZipFilePathForExportedEntriesByUser($this->getUserId(), $targetFileName);
        if ($filePath === null) {
            http_response_code(404);
            (new Error())->renderNotFoundPage();
        }

        $fileName = urlencode(basename($filePath));
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename={$fileName}");
        header('Content-Transfer-Encoding: binary');

        readfile($filePath);
    }

    public function deleteEntryExport(): void
    {
        $this->validator->validate(__FUNCTION__);
        unset($_POST['form_key']);

        $targetFileName = $_POST['fileName'];

        $this->userService->deleteExportedEntriesZipFile($this->getUserId(), $targetFileName);

        $this->setNotification(Notification::TYPE_SUCCESS, "Removed {$targetFileName} successfully");
        $this->deleteEntryExportView();
    }

    public function deleteEntryExportView(): void
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    public function downloadEntryExportView(): void
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    public function setDateTimeZone(): void
    {
        $this->validator->validate(__FUNCTION__);

        $this->userService->setDateTimeZoneForUser($this->getUserId(), $_POST['timezone']);

        $this->setNotification(Notification::TYPE_SUCCESS, 'Timezone updated');

        $this->authenticationService->updateUserSessionTimezone($_POST['timezone']);

        $this->setDateTimeZoneView();
    }

    public function setDateTimeZoneView(): void
    {
        Redirect::to(self::ACCOUNT_URL);
    }
}
