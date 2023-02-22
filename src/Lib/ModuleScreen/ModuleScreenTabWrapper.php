<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleScreenTabWrapper
{ #{{{
    private $tabs = null;

    /**
     */
    public function register($tab)
    { #{{{
        $this->tabs[$tab->getModuleType()] = $tab;
    } # }}}

    /**
     */
    public function __get($moduleType)
    { #{{{
        return $this->tabs[$moduleType];
    } # }}}
}
