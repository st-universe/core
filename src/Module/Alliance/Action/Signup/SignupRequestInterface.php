<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Signup;

interface SignupRequestInterface
{
    public function getAllianceId(): int;
}
