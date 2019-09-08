<?php

namespace Stu\Lib\ModuleScreen;

use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;

final class ModuleSelectWrapper
{
	private $buildplan;

	public function __construct(?ShipBuildplanInterface $buildplan)
	{
		$this->buildplan = $buildplan;
	}

	public function __get($type): ?BuildplanModuleInterface
	{
		if ($this->buildplan === null) {
			return null;
		}
		$modules = $this->buildplan->getModulesByType($type);
		if ($modules === []) {
			return null;
		}
		return current($modules);
	}
}
