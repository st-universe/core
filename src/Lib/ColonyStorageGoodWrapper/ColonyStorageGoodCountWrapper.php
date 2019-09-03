<?php

declare(strict_types=1);

namespace Stu\Lib\ColonyStorageGoodWrapper;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ColonyStorageGoodCountWrapper
{ #{{{

    const CHECK_ONLY = 'x';

    private $storage = null;
    private $goodId = null;

    /**
     */
    function __construct(&$storage, $goodId)
    { #{{{
        $this->storage = $storage;
        $this->goodId = $goodId;
    } # }}}

    /**
     */
    public function __get($count)
    { #{{{
        if (!isset($this->storage[$this->goodId])) {
            return false;
        }
        if ($count == self::CHECK_ONLY) {
            return true;
        }
        if ($this->storage[$this->goodId]->getAmount() < $count) {
            return false;
        }
        return true;
    } # }}}

    /**
     */
    public function __call($name, $arg)
    { #{{{
        return $this->__get($name);
    } # }}}

}