<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeDescription;

interface ChangeDescriptionRequestInterface
{
    public function getDescription(): string;
}
