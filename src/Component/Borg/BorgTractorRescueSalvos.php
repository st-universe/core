<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\BorgSphereEvent;

final class BorgTractorRescueSalvos
{
    public function __construct(private BorgAssimilationLifecycle $assimilation, private SpacecraftSystemManagerInterface $systems, private StuRandom $random) {}

    /** @param list<SpacecraftWrapperInterface> $participants */
    public function fire(BorgSphereEvent $event, array $participants, SpacecraftWrapperInterface $target, InformationInterface $information): void
    {
        foreach ($participants as $source) {
            if ($this->fireShip($event, $source, $target, $information)) {
                return;
            }
        }
    }

    private function fireShip(BorgSphereEvent $event, SpacecraftWrapperInterface $source, SpacecraftWrapperInterface $target, InformationInterface $information): bool
    {
        for ($salvo = 0; $salvo < $source->get()->getRump()->getPhaserVolleys(); $salvo++) {
            if (!$this->isStillCaptured($target)) {
                return true;
            }
            if (($source->getEpsSystemData()?->getEps() ?? 0) < 1) {
                return false;
            }
            if ($this->fireSalvo($event, $source, $target, $information)) {
                return true;
            }
        }
        return false;
    }

    private function isStillCaptured(SpacecraftWrapperInterface $target): bool
    {
        return $target->get()->getSystemState(SpacecraftSystemTypeEnum::TRACTOR_BEAM)
            && $target->get()->getTractoredShip()?->isBeingAssimilated() === true;
    }

    private function fireSalvo(BorgSphereEvent $event, SpacecraftWrapperInterface $source, SpacecraftWrapperInterface $target, InformationInterface $information): bool
    {
        $source->getEpsSystemData()?->lowerEps(1)->update();
        $information->addInformationf('Die %s beschießt den Traktoremitter des Schiffs %s mit rotierend modulierten Energiewaffenfrequenzen', $source->get()->getName(), $target->get()->getName());
        if ($this->random->rand(1, 100) > $event->getConfiguration()->getSetting('tractor_rescue_chance_percent')) {
            $information->addInformation('Die modulierte Salve wird von den Schilden abgefangen. Der Traktorstrahl bleibt aktiv');
            return false;
        }
        $system = $target->get()->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM);
        $system->setStatus(max(10, $system->getStatus() - 25));
        $captured = $target->get()->getTractoredShip();
        $this->systems->deactivate($target, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true);
        $this->assimilation->releaseForSource($target->get());
        $information->addInformation($captured?->getAssimilation()?->isComplete()
            ? 'Die modulierte Salve deaktiviert den Traktoremitter. Die Assimilation war jedoch bereits abgeschlossen'
            : 'Die modulierte Salve durchdringt die Schilde und deaktiviert den Traktoremitter. Das erfasste Schiff ist befreit');
        return true;
    }
}
