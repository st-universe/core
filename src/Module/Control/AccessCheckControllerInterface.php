<?php

namespace Stu\Module\Control;

interface AccessCheckControllerInterface extends ControllerInterface
{
    public function getFeatureIdentifier(): AccessGrantedFeatureEnum;
}
