<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class TorpedoStorageShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public const int TORPEDO_CAPACITY = 200;

    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::TORPEDO_STORAGE;
    }

    #[\Override]
    public function activate(SpacecraftWrapperInterface $wrapper, SpacecraftSystemManagerInterface $manager): void
    {
        //passive system
    }

    #[\Override]
    public function deactivate(SpacecraftWrapperInterface $wrapper): void
    {
        //passive system
    }

    #[\Override]
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        //TODO destroy ship?
        $spacecraft = $wrapper->get();
        if ($spacecraft->getTorpedoCount() > 0) {
            $spacecraft->getCondition()->setIsDestroyed(true);
        }
    }
}
