<?php

namespace Stu\Lib\Interaction;

use Stu\Lib\Information\InformationInterface;

interface CustomizedInteractionCheckerInterface
{
    public function check(InformationInterface $information): bool;
}
