<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

/**
 * @access public
 */
class MyWrapper
{ #{{{

    private $modSels = null;

    /**
     */
    public function register($modSel)
    { #{{{
        $this->modeSels[$modSel->getModuleType()] = $modSel;
    } # }}}

    /**
     */
    public function __get($moduleType)
    { #{{{
        return $this->modSels[$moduleType];
    } # }}}

}
