<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapper2 extends \Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperBase
{ #{{{

    /**
     */
    public function getValue()
    { #{{{
        return calculateModuleValue($this->getRump(), current($this->getModule())->getModule(), 'getBaseShield');
    } # }}}


    /**
     */
    public function getCallBacks()
    { #{{{
        $callbacks = array('setMaxShield' => $this->getValue());
        return $callbacks;
    } # }}}

}