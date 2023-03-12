<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeUserName;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeUserNameRequest implements ChangeUserNameRequestInterface
{
    use CustomControllerHelperTrait;

    public function getName(): string
    {
        return $this->tidyString(
            $this->queryParameter('uname')->string()->defaultsToIfEmpty('')
        );
    }

}
