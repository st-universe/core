<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapperBase
{ #{{{

    private $module = null;
    protected $rump = null;

    /**
     */
    function __construct($rump, $module)
    { #{{{
        $this->module = $module;
        $this->rump = $rump;
    } # }}}

    /**
     */
    protected function getRump()
    { #{{{
        return $this->rump;
    } # }}}

    /**
     */
    public function getModule()
    { #{{{
        return $this->module;
    } # }}}

}