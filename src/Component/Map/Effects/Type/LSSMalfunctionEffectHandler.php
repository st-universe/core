<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects\Type;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class LSSMalfunctionEffectHandler implements EffectHandlerInterface
{
    public function __construct() {}

    public function handleSpacecraftTick(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        // not needed
    }

    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void
    {
        $spacecraft = $wrapper->get();

        $messages->addInformation(
            sprintf("[color=yellow]Interferenz im Subraum durch %s detektiert <br>
            Langstreckensensoren sind aktiv, liefern jedoch keine verwertbaren Daten[/color]", $spacecraft->getLocation()->getFieldType()->getName()),
            $wrapper->get()->getUser()->getId()
        );
    }
}