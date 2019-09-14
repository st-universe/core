<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapper4 extends ModuleRumpWrapperBase
{ #{{{

    /**
     */
    public function getValue()
    { #{{{
        return calculateEvadeChance($this->getRump(), current($this->getModule())->getModule());
    } # }}}


    /**
     */
    public function getCallBacks()
    { #{{{
        $callbacks = array('setEvadeChance' => $this->getValue());
        return $callbacks;
    } # }}}

}