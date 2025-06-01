<?php

namespace Stu\Component\Spacecraft\Trait;

use RuntimeException;
use Stu\Component\Spacecraft\System\Exception\InvalidSystemException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;

trait SpacecraftTrait
{
    private function getThis(): SpacecraftInterface
    {
        if (!$this instanceof SpacecraftInterface) {
            throw new RuntimeException('trait can only be used on spacecraft entities');
        }

        return $this;
    }

    public function getSpacecraftSystem(SpacecraftSystemTypeEnum $type): SpacecraftSystemInterface
    {
        $system = $this->getThis()->getSystems()->get($type->value);
        if ($system === null) {
            throw new InvalidSystemException(sprintf('system type %d does not exist on ship', $type->value));
        }

        return $system;
    }
}
