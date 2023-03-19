<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Signup;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class SignupRequest implements SignupRequestInterface
{
    use CustomControllerHelperTrait;

    public function getAllianceId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
