<?php

declare(strict_types=1);

namespace Stu\Lib\Information;

class InformationFactory implements InformationFactoryInterface
{
    public function createInformationWrapper(): InformationWrapper
    {
        //TODO use this everywhere instead of new
        return new InformationWrapper();
    }
}
