<?php

declare(strict_types=1);

namespace Stu\Lib\Information;

interface InformationFactoryInterface
{
    public function createInformationWrapper(): InformationWrapper;
}
