<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapper7 extends ModuleRumpWrapperBase
{ #{{{

    /**
     */
    public function getValue()
    { #{{{
        return calculateModuleValue($this->getRump(), current($this->getModule())->getModule(), 'getBaseDamage');
    } # }}}


    /**
     */
    public function getCallBacks()
    { #{{{
        $callbacks = array('setBaseDamage' => $this->getValue());
        return $callbacks;
    } # }}}

}