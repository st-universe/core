<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects\Type;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;

class ShieldMalfunctionEffectHandler implements EffectHandlerInterface
{
    private const SYSTEM_TYPE = SpacecraftSystemTypeEnum::SHIELDS;

    public function __construct(
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private StuRandom $stuRandom
    ) {}

    #[Override]
    public function handleSpacecraftTick(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        $information->addInformation(
            $this->getText($this->depleteShieldEmitters($wrapper))
        );
    }

    #[Override]
    public function addFlightInformation(Location $location, MessageCollectionInterface $messages): void
    {
        $messages->addInformationf(
            "[color=yellow]Partikelinterferenz durch %s in Sektor %s sorgt für Destabilisierung des Schildgitters[/color]",
            $location->getFieldType()->getName(),
            $location->getSectorString()
        );
    }

    #[Override]
    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void
    {
        $spacecraft = $wrapper->get();

        $isShutdown = false;
        if ($spacecraft->getSystemState(self::SYSTEM_TYPE)) {
            $this->spacecraftSystemManager->deactivate($wrapper, self::SYSTEM_TYPE, true);
            $isShutdown = true;
        }

        $depletionAmount = $this->depleteShieldEmitters($wrapper);

        $text = $this->getText($depletionAmount, $isShutdown);
        if ($text === null) {
            return;
        }

        $messages->addMessageBy(
            sprintf(
                "%s: [color=yellow]%s[/color]",
                $spacecraft->getName(),
                $text
            ),
            $wrapper->get()->getUser()->getId()
        );
    }

    private function getText(?int $depletionAmount, bool $isShutdown = false): ?string
    {
        if (!$isShutdown && $depletionAmount === null) {
            return null;
        }

        return sprintf(
            "Schildmatrix %s%s%s",
            $isShutdown ? 'ausgefallen' : '',
            $isShutdown && $depletionAmount !== null ? ' und ' : '',
            $depletionAmount !== null ? sprintf('erleidet Kapazitätsverlust von %s Einheiten', $depletionAmount) : ''
        );
    }

    private function depleteShieldEmitters(SpacecraftWrapperInterface $wrapper): ?int
    {
        $shield = $wrapper->get()->getCondition()->getShield();
        if ($shield < 1) {
            return null;
        }

        $depletionAmount = $this->stuRandom->rand(1, $shield, true, (int)ceil($shield / 5));

        $wrapper->get()->getCondition()->changeShield(-$depletionAmount);

        return $depletionAmount;
    }
}
