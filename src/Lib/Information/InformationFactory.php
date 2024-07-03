<?php

declare(strict_types=1);

namespace Stu\Lib\Information;

use Override;

class InformationFactory implements InformationFactoryInterface
{
    #[Override]
    public function createInformationWrapper(): InformationWrapper
    {
        //TODO use this everywhere instead of new
        return new InformationWrapper();
    }
}
