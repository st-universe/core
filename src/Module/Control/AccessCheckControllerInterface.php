<?php

declare(strict_types=1);

namespace Stu\Module\Control;

interface AccessCheckControllerInterface extends ControllerInterface
{
    public function getFeatureIdentifier(): AccessGrantedFeatureEnum;
}
