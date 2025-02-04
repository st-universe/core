<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects\Type;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LocationInterface;

class CloakUnuseableEffectHandler implements EffectHandlerInterface
{
    public function __construct(
        private SpacecraftSystemManagerInterface $spacecraftSystemManager
    ) {}

    #[Override]
    public function handleSpacecraftTick(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        // not needed
    }

    #[Override]
    public function addFlightInformation(LocationInterface $location, MessageCollectionInterface $messages): void
    {
        $messages->addInformation(
            sprintf(
                "[color=yellow]Ionische Dispersion durch %s stört die Phasenmodulation von Tarnsystemen in Sektor %s.[/color]",
                $location->getFieldType()->getName(),
                $location->getSectorString()
            )
        );
    }

    #[Override]
    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void
    {
        $spacecraft = $wrapper->get();
        if ($spacecraft->isCloaked()) {

            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::CLOAK, true);

            $messages->addInformation(
                sprintf("[color=yellow]Ionische Dispersion durch %s hat die Phasenmodulation des Tarnsystems gestört<br>Tarnsystem ausgefallen[/color]", $spacecraft->getLocation()->getFieldType()->getName()),
                $wrapper->get()->getUser()->getId()
            );
        }
    }
}
