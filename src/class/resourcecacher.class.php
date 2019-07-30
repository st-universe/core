<?php

class ResourceCacher {
	
	private $resources = NULL;

	/**
	 */
	private function getCache() { #{{{
		if ($this->resources === NULL) {
			$this->resources = new ArrayObject;
			$this->resources->offsetSet(CACHE_USER,new ArrayObject);
			$this->resources->offsetSet(CACHE_BUILDING,new ArrayObject);
			$this->resources->offsetSet(CACHE_GOOD,new ArrayObject);
			$this->resources->offsetSet(CACHE_SHIP,new ArrayObject);
			$this->resources->offsetSet(CACHE_CREW,new ArrayObject);
			$this->resources->offsetSet(CACHE_CREWRACES,new ArrayObject);
			$this->resources->offsetSet(CACHE_TRADEPOST,new ArrayObject);
			$this->resources->offsetSet(CACHE_ALLIANCE,new ArrayObject);
			$this->resources->offsetSet(CACHE_FACTION,new ArrayObject);
			$this->resources->offsetSet(CACHE_RESEARCH,new ArrayObject);
			$this->resources->offsetSet(CACHE_MAPFIELD,new ArrayObject);
			$this->resources->offsetSet(CACHE_MODULE,new ArrayObject);
			$this->resources->offsetSet(CACHE_COLONY,new ArrayObject);
			$this->resources->offsetSet(CACHE_RUMP,new ArrayObject);
			$this->resources->offsetSet(CACHE_FLEET,new ArrayObject);
		}
		return $this->resources;
	} # }}}

	public function getObject($obj,$id) {
		if (!$this->isResourceCached($obj,$id)) {
			$this->addResource($obj,$id);
		}
		return $this->getCache()->offsetGet($obj)->offsetGet($id);
	}

	/**
	 */
	public function getGood($id) { #{{{
		return $this->getObject(CACHE_GOOD,$id);
	} # }}}

	/**
	 */
	public function getUser($id) { #{{{
		return $this->getObject(CACHE_USER,$id);
	} # }}}

	/**
	 */
	public function isResourceCached($obj,$id) { #{{{
		return $this->getCache()->offsetGet($obj)->offsetExists($id);
	} # }}}


	private function addResource(&$obj,&$id) {
		switch ($obj) {
			case "user":
				$newobj = "User";
				break;
			case "building":
				$newobj = "Building";
				break;
			case "good":
				$newobj = "Good";
				break;
			case "ship":
				$newobj = "Ship";
				break;
			case "crew":
				$newobj = "Crew";
				break;
			case "crewraces":
				$newobj = "CrewRaces";
				break;
			case "tradepost":
				$newobj = "TradePost";
				break;
			case "alliance":
				$newobj = "Alliance";
				break;
			case "faction":
				$newobj = "Faction";
				break;
			case "research":
				$newobj = "Research";
				break;
			case "mapfield":
				$newobj = "MapFieldType";
				break;
			case "module":
				$newobj = "Modules";
				break;
			case "colony":
				$newobj = "Colony";
				break;
			case "rump":
				$newobj = "ShipRump";
				break;
			case CACHE_FLEET:
				$newobj = "Fleet";
				break;
		}
		$this->registerResource($obj,$id,new $newobj($id));
	}

	/**
	 */
	public function registerResource($obj,$id,$resource) { #{{{
		$this->getCache()->offsetGet($obj)->offsetSet($id,$resource);
	} # }}}

}
