<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects\Type;

use Stu\Component\Game\TimeConstants;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;

class NfsMalfunctionCooldownEffectHandler implements EffectHandlerInterface
{
    private const SYSTEM_TYPE = SpacecraftSystemTypeEnum::NBS;

    public function __construct(
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private StuRandom $stuRandom,
        private StuTime $stuTime
    ) {}

    #[\Override]
    public function handleSpacecraftTick(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        // not needed
    }

    #[\Override]
    public function addFlightInformation(Location $location, MessageCollectionInterface $messages): void
    {
        $messages->addInformationf(
            "[color=yellow]Subraumverzerrungen durch %s induzieren Rückkopplung in den Nahbereichsensoren in Sektor %s[/color]",
            $location->getFieldType()->getName(),
            $location->getSectorString()
        );
    }

    #[\Override]
    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void
    {
        $spacecraft = $wrapper->get();

        $system = $spacecraft->getSystems()[self::SYSTEM_TYPE->value] ?? null;
        if ($system === null) {
            return;
        }

        $cooldown = $this->stuTime->time() + $this->stuRandom->rand(
            TimeConstants::TEN_MINUTES_IN_SECONDS,
            2 * TimeConstants::TEN_MINUTES_IN_SECONDS
        );

        $actualCooldown = $system->getCooldown();
        if (
            $actualCooldown !== null
            && $actualCooldown > $cooldown
        ) {
            return;
        }

        $system->setCooldown($cooldown);

        $shutdownText = '';
        if ($spacecraft->getSystemState(self::SYSTEM_TYPE)) {
            $this->spacecraftSystemManager->deactivate($wrapper, self::SYSTEM_TYPE, true);
            $shutdownText = 'ausgefallen und ';
        }

        $messages->addMessageBy(
            sprintf(
                "%s: [color=yellow]%s %sin Rekalibrierungsphase[/color]",
                $spacecraft->getName(),
                self::SYSTEM_TYPE->getDescription(),
                $shutdownText
            ),
            $wrapper->get()->getUser()->getId()
        );
    }
}
