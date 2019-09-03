<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use request;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleSelectorWrapper
{ #{{{

    private $module = null;
    private $buildplan = null;

    /**
     */
    function __construct($module, $buildplan)
    { #{{{
        $this->module = $module;
        $this->buildplan = $buildplan;
    } # }}}

    /**
     */
    public function isChoosen()
    { #{{{
        $request = request::postArray('mod_' . $this->getModule()->getType());
        if ($this->getBuildplan()) {
            if (array_key_exists($this->getModule()->getId(),
                $this->getBuildplan()->getModulesByType($this->getModule()->getType()))) {
                return true;
            }
        }
        if (!is_array($request) || !array_key_exists($this->getModule()->getId(), $request)) {
            return false;
        }
        return true;
    } # }}}

    /**
     */
    public function getBuildplan()
    { #{{{
        return $this->buildplan;
    } # }}}

    /**
     */
    public function getModule()
    { #{{{
        return $this->module;
    } # }}}

}