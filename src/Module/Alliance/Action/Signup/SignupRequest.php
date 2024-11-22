<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Signup;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class SignupRequest implements SignupRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getAllianceId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
