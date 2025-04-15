<?php

namespace Stu\Module\Control;

interface AccessCheckControllerInterface
{
    public function getFeatureIdentifier(): AccessGrantedFeatureEnum;
}
