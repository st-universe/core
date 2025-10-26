<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\UnloadBattery;

use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Spacecraft;

final class UnloadBattery implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_USE_BATTERY';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private StuTime $stuTime
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $load = request::postInt('ebattload');

        if ($load < 1) {
            return;
        }

        if (request::postString('fleet_batt') !== false) {
            $msg = [];
            $msg[] = _('Flottenbefehl ausgeführt: Ersatzbatterie entladen');

            foreach ($ship->getFleet()?->getShips() ?? [] as $ship) {
                $msg[] = $this->unloadBattery($ship, $load);
            }
            $game->getInfo()->addInformationArray($msg, true);
            return;
        }

        $game->getInfo()->addInformation($this->unloadBattery($ship, $load));
    }

    private function unloadBattery(Spacecraft $ship, int $load): string
    {
        // cancel conditions
        if (!$ship->hasEnoughCrew()) {
            return sprintf(_('%s: Das Schiff hat zu wenig Crew'), $ship->getName());
        }

        $eps = $this->spacecraftWrapperFactory->wrapSpacecraft($ship)->getEpsSystemData();

        if ($eps === null) {
            return sprintf(_('%s: Kein Energiesystem installiert'), $ship->getName());
        }
        if ($eps->getBattery() === 0) {
            return sprintf(_('%s: Die Ersatzbatterie ist leer'), $ship->getName());
        }
        if (!$eps->isEBattUseable()) {
            return sprintf(_('%s: Die Batterie kann erst wieder am ' . date('d.m.Y H:i', $eps->getBatteryCooldown()) . ' genutzt werden'), $ship->getName());
        }
        if ($eps->getEps() >= $eps->getMaxEps()) {
            return sprintf(_('%s: Der Energiespeicher ist voll'), $ship->getName());
        }
        if (!$ship->isSystemHealthy(SpacecraftSystemTypeEnum::EPS)) {
            return sprintf(_('%s: Das Energienetz ist zerstört und kann nicht geladen werden'), $ship->getName());
        }

        // unload following
        if ($load > $eps->getBattery()) {
            $load = $eps->getBattery();
        }
        if ($load + $eps->getEps() > $eps->getMaxEps()) {
            $load = $eps->getMaxEps() - $eps->getEps();
        }

        $eps->setBattery($eps->getBattery() - $load)
            ->setBatteryCooldown($this->stuTime->time() + $load * 60)
            ->setEps($eps->getEps() + $load)
            ->update();

        return sprintf(
            _('%s: Die Ersatzbatterie wurde um %d Einheiten entladen'),
            $ship->getName(),
            $load
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
