<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects\Type;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class CloakUnuseableEffectHandler implements EffectHandlerInterface
{
    public function __construct(
        private SpacecraftSystemManagerInterface $spacecraftSystemManager
    ) {}

    public function handleSpacecraftTick(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        // not needed
    }

    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void
    {
        $spacecraft = $wrapper->get();
        if ($spacecraft->isCloaked()) {

            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::CLOAK, true);

            $messages->addInformation(
                sprintf("[color=yellow]Tarnung durch %s ausgefallen.[/color]", $spacecraft->getLocation()->getFieldType()->getName()),
                $wrapper->get()->getUser()->getId()
            );
        }
    }
}
