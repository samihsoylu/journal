<?php


namespace App\Service;


use App\Database\Model\Widget;
use App\Database\Repository\WidgetRepository;
use App\Service\Helper\UserHelper;

class WidgetService
{
    private WidgetRepository $repository;
    private UserHelper $userHelper;

    public function __construct()
    {
        $this->repository = new WidgetRepository();
        $this->userHelper = new UserHelper();
    }

    public function getEnabledWidgetsForUser(int $userId): array
    {
        $user = $this->userHelper->getUserById($userId);
        $widgets = $this->repository->findByUser($user);

        $enabledWidgets = [];
        foreach ($widgets as $widget) {
            if ($widget->isEnabled()) {
                $enabledWidgets[$widget->getName()] = true;
            }
        }

        return $enabledWidgets;
    }

    public function updateWidgetSettingsForUser(int $userId, array $checkedWidgets): void
    {
        $user = $this->userHelper->getUserById($userId);
        $widgets = $this->repository->findByUser($user);

        $existingWidgets = [];
        foreach ($widgets as $widget) {
            $widget->setEnabled(true);
            if (!array_key_exists($widget->getName(), $checkedWidgets)) {
                $widget->setEnabled(false);
            }

            $this->repository->queue($widget);
            $existingWidgets[] = $widget->getName();
        }

        foreach ($checkedWidgets as $widgetName => $widgetValue) {
            if (in_array($widgetName, $existingWidgets, true)) {
                continue;
            }

            $widget = new Widget();
            $widget->setReferencedUser($user);
            $widget->setName($widgetName);
            $widget->setEnabled(true);
            $this->repository->queue($widget);
        }

        $this->repository->save();
    }
}