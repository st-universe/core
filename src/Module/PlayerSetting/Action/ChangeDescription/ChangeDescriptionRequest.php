<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeDescription;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeDescriptionRequest implements ChangeDescriptionRequestInterface
{
    use CustomControllerHelperTrait;

    public function getDescription(): string
    {
        return $this->tidyString(
            $this->queryParameter('description')->string()->defaultsToIfEmpty('')
        );
    }
}
