<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

class ModuleRumpWrapper1 extends ModuleRumpWrapperBase
{ #{{{

    /**
     */
    public function getValue()
    { #{{{
        return calculateModuleValue($this->getRump(), current($this->getModule())->getModule(), 'getBaseHull');
    } # }}}


    /**
     */
    public function getCallBacks()
    { #{{{
        $callbacks = array(
            'setMaxHuelle' => $this->getValue(),
            'setHuell' => $this->getValue()
        );
        return $callbacks;
    } # }}}

}