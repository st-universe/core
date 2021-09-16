<?php

namespace Stu\Module\PlayerSetting\Action\ChangeSettings;

interface ChangeSettingsRequestInterface
{
    public function getEmailNotification(): int;

    public function getSaveLogin(): int;

    public function getStorageNotification(): int;

    public function getShowOnlineState(): int;

    public function getFleetsFixedDefault(): int;
}
