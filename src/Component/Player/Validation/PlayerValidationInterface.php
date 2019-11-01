<?php

namespace Stu\Component\Player\Validation;

use Stu\Orm\Entity\UserInterface;

interface PlayerValidationInterface
{
    public function validate(UserInterface $user): bool;
}
