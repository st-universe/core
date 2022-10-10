<?php

declare(strict_types=1);

namespace Stu\Lib\ColonyStorageCommodityWrapper;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ColonyStorageCommodityCountWrapper
{ #{{{

    const CHECK_ONLY = 'x';

    private $storage = null;
    private $commodityId = null;

    /**
     */
    function __construct(&$storage, $commodityId)
    { #{{{
        $this->storage = $storage;
        $this->commodityId = $commodityId;
    } # }}}

    /**
     */
    public function __get($count)
    { #{{{
        if (!isset($this->storage[$this->commodityId])) {
            return false;
        }
        if ($count == self::CHECK_ONLY) {
            return true;
        }
        if ($this->storage[$this->commodityId]->getAmount() < $count) {
            return false;
        }
        return true;
    } # }}}

    /**
     */
    public function getAmount()
    { #{{{
        if (!isset($this->storage[$this->commodityId])) {
            return 0;
        }
        return $this->storage[$this->commodityId]->getAmount();
    } # }}}

    /**
     */
    public function __call($name, $arg)
    { #{{{
        return $this->__get($name);
    } # }}}

}
