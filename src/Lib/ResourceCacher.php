<?php

namespace Stu\Lib;

use ArrayObject;
use Stu\Orm\Repository\CommodityRepositoryInterface;

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
            $this->resources->offsetSet(CACHE_BUILDING, new ArrayObject);
            $this->resources->offsetSet(CACHE_GOOD, new ArrayObject);
            $this->resources->offsetSet(CACHE_SHIP, new ArrayObject);
            $this->resources->offsetSet(CACHE_CREW, new ArrayObject);
            $this->resources->offsetSet(CACHE_ALLIANCE, new ArrayObject);
            $this->resources->offsetSet(CACHE_FACTION, new ArrayObject);
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
    public function getGood($id)
    { #{{{
        return $this->getObject(CACHE_GOOD, $id);
    } # }}}

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
            case "building":
                $newobj = "Building";
                break;
            case "good":
                global $container;

                $this->registerResource(
                    $obj,
                    $id,
                    $container->get(CommodityRepositoryInterface::class)->find($id)
                );
                return;
            case "ship":
                $newobj = "Ship";
                break;
            case "crew":
                $newobj = "Crew";
                break;
            case "alliance":
                $newobj = "Alliance";
                break;
            case "faction":
                $newobj = "Faction";
                break;
            case "colony":
                $newobj = "Colony";
                break;
            case "rump":
                $newobj = "Shiprump";
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
