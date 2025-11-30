<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Service\UserService;
use App\Service\WidgetService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Utility\Sanitize;
use App\Utility\Template;
use App\Validator\AccountValidator;

final class Account extends AbstractController
{
    public const string ACCOUNT_URL = BASE_URL . '/account';
    public const string UPDATE_EMAIL_POST_URL = self::ACCOUNT_URL . '/update/email';
    public const string CHANGE_PASSWORD_POST_URL = self::ACCOUNT_URL . '/change-password';
    public const string DELETE_ACCOUNT_POST_URL = self::ACCOUNT_URL . '/delete';
    public const string UPDATE_WIDGETS_POST_URL = self::ACCOUNT_URL . '/widgets/update';
    public const string EXPORT_ENTRIES_POST_URL = self::ACCOUNT_URL . '/export-entries';
    public const string EXPORT_DOWNLOAD_URL = self::EXPORT_ENTRIES_POST_URL . '/download';
    public const string EXPORT_DELETE_POST_URL = self::EXPORT_ENTRIES_POST_URL . '/delete';
    public const string EXPORT_DOWNLOAD_GET_URL = self::EXPORT_DOWNLOAD_URL . '/{fileName}';
    public const string SET_DATE_TIME_ZONE_POST_URL = self::ACCOUNT_URL . '/set-date-time-zone';

    private readonly AccountValidator $validator;

    public function __construct(
        private readonly AuthenticationService $authenticationService,
        Template $template,
        private readonly UserService $userService,
        private readonly WidgetService $widgetService,
    ) {
        parent::__construct($authenticationService, $template);

        // for every action in this controller, the user must be logged in
        $this->redirectLoggedOutUsersToLoginPage();

        $this->validator = new AccountValidator($_POST, [], $authenticationService->getUserSession());
    }

    /**
     * View all available account settings page.
     */
    public function indexView() : void
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
     * Change email post action from the Account settings page.
     */
    public function changeEmail() : never
    {
        $this->validator->validate(__FUNCTION__);

        $newEmail = Sanitize::string($_POST['email'], [Sanitize::OPTION_LOWERCASE, Sanitize::OPTION_STRIP]);

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            'Email was updated to ' . $newEmail,
        );

        $this->userService->changeUserEmail($this->getUserId(), $newEmail);

        $this->changeEmailView();
    }

    public function changeEmailView() : never
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    /**
     * Change password action from the account settings page.
     */
    public function changePassword() : never
    {
        $this->validator->validate(__FUNCTION__);

        $this->userService->changePassword($this->getUserId(), $_POST['currentPassword'], $_POST['newPassword']);

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            'Password changed',
        );

        $this->changePasswordView();
    }

    public function changePasswordView() : never
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    /**
     * Account delete post action on account settings page.
     */
    public function deleteAccount() : never
    {
        $this->validator->validate(__FUNCTION__);

        $this->userService->deleteUserForUser($_POST['password'], $this->getUserId());

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            'Your account has been deleted',
        );

        Redirect::to(BASE_URL . '/');
    }

    public function deleteAccountView() : never
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    /**
     * Update widget settings post action.
     */
    public function updateWidgets() : never
    {
        $this->validator->validate(__FUNCTION__);

        unset($_POST['form_key']);

        $this->widgetService->updateWidgetSettingsForUser(
            $this->getUserId(),
            $_POST,
        );

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            'Widgets updated',
        );

        $this->updateWidgetsView();
    }

    public function updateWidgetsView() : never
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    public function exportEntries() : never
    {
        $this->validator->validate(__FUNCTION__);

        $processId = $this->userService->exportUserEntries($this->getUserId(), $this->getUserEncryptionKey());

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            'Export triggered, process id: ' . $processId,
        );

        Redirect::to(self::ACCOUNT_URL);
    }

    public function exportEntriesView() : never
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    public function downloadEntryExport() : void
    {
        $targetFileName = $this->getRouteParameters()['fileName'];

        $filePath = $this->userService->getZipFilePathForExportedEntriesByUser($this->getUserId(), $targetFileName);

        if ($filePath === null) {
            http_response_code(404);
            new Error($this->authenticationService, $this->template)->renderNotFoundPage();
        }

        $fileName = urlencode(basename($filePath));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Content-Transfer-Encoding: binary');

        readfile($filePath);
    }

    public function deleteEntryExport() : never
    {
        $this->validator->validate(__FUNCTION__);
        unset($_POST['form_key']);

        $targetFileName = $_POST['fileName'];

        $this->userService->deleteExportedEntriesZipFile($this->getUserId(), $targetFileName);

        $this->setNotification(Notification::TYPE_SUCCESS, sprintf('Removed %s successfully', $targetFileName));
        $this->deleteEntryExportView();
    }

    public function deleteEntryExportView() : never
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    public function downloadEntryExportView() : never
    {
        Redirect::to(self::ACCOUNT_URL);
    }

    public function setDateTimeZone() : never
    {
        $this->validator->validate(__FUNCTION__);

        $this->userService->setDateTimeZoneForUser($this->getUserId(), $_POST['timezone']);

        $this->setNotification(Notification::TYPE_SUCCESS, 'Timezone updated');

        $this->authenticationService->updateUserSessionTimezone($_POST['timezone']);

        $this->setDateTimeZoneView();
    }

    public function setDateTimeZoneView() : never
    {
        Redirect::to(self::ACCOUNT_URL);
    }
}
