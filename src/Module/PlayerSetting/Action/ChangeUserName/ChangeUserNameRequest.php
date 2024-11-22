<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeUserName;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeUserNameRequest implements ChangeUserNameRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getName(): string
    {
        return $this->tidyString(
            $this->parameter('uname')->string()->defaultsToIfEmpty('')
        );
    }
}
