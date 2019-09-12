<?php

namespace Stu\Lib;

use ArrayObject;

class ResourceCacher
{

    private $resources = null;

    /**
     */
    private function getCache()
    { #{{{
        if ($this->resources === null) {
            $this->resources = new ArrayObject;
            $this->resources->offsetSet(CACHE_USER, new ArrayObject);
            $this->resources->offsetSet(CACHE_SHIP, new ArrayObject);
            $this->resources->offsetSet(CACHE_ALLIANCE, new ArrayObject);
            $this->resources->offsetSet(CACHE_COLONY, new ArrayObject);
            $this->resources->offsetSet(CACHE_FLEET, new ArrayObject);
        }
        return $this->resources;
    } # }}}

    public function getObject($obj, $id)
    {
        if (!$this->isResourceCached($obj, $id)) {
            $this->addResource($obj, (int)$id);
        }
        return $this->getCache()->offsetGet($obj)->offsetGet($id);
    }

    /**
     */
    public function getUser($id)
    { #{{{
        return $this->getObject(CACHE_USER, $id);
    } # }}}

    /**
     */
    public function isResourceCached($obj, $id)
    { #{{{
        return $this->getCache()->offsetGet($obj)->offsetExists($id);
    } # }}}


    private function addResource(&$obj, $id)
    {
        switch ($obj) {
            case "user":
                $newobj = "User";
                break;
            case "ship":
                $newobj = "Ship";
                break;
            case "alliance":
                $newobj = "Alliance";
                break;
            case "colony":
                $newobj = "Colony";
                break;
            case CACHE_FLEET:
                $newobj = "Fleet";
                break;
        }
        $this->registerResource($obj, $id, new $newobj($id));
    }

    /**
     */
    public function registerResource($obj, $id, $resource)
    { #{{{
        $this->getCache()->offsetGet($obj)->offsetSet($id, $resource);
    } # }}}

}
