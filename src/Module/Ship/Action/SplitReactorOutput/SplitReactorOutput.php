<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SplitReactorOutput;

use request;
use Stu\Component\Ship\System\Data\WarpDriveSystemData;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowInformation\ShowInformation;

final class SplitReactorOutput implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SPLIT_REACTOR_OUTPUT';

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $systemData = $wrapper->getWarpDriveSystemData();
        if ($systemData === null) {
            throw new SanityCheckException('no warpdrive in fleet leader', self::ACTION_IDENTIFIER);
        }

        $game->setView(ShowInformation::VIEW_IDENTIFIER);

        $warpsplit = request::postInt('value');
        if ($warpsplit < 0) {
            $warpsplit = 0;
        }
        if ($warpsplit > 100) {
            $warpsplit = 100;
        }

        $isFleet = request::postIntFatal('fleet') === 1;
        $autoCarryOver = request::postIntFatal('autocarryover') === 1;

        $fleetWrapper = $wrapper->getFleetWrapper();
        if ($isFleet && $fleetWrapper !== null) {

            foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
                $systemData = $wrapper->getWarpDriveSystemData();
                if ($systemData !== null) {
                    $this->setValues($systemData, $warpsplit, $autoCarryOver);
                }
            }
            $this->addGameInfo(true, $warpsplit, $autoCarryOver, $game);
            return;
        }

        $this->setValues($systemData, $warpsplit, $autoCarryOver);
        $this->addGameInfo(false, $warpsplit, $autoCarryOver, $game);
    }

    private function setValues(WarpDriveSystemData $systemData, int $split, bool $autoCarryOver): void
    {
        $systemData
            ->setWarpDriveSplit($split)
            ->setAutoCarryOver($autoCarryOver)
            ->update();
    }

    private function addGameInfo(bool $isFleet, int $warpsplit, bool $autoCarryOver, GameControllerInterface $game): void
    {
        $game->addInformation(sprintf(
            _('%sReaktorleistung geht zu %d Prozent in den Warpantrieb (Übertrag %s)'),
            $isFleet ? 'Flottenbefehl ausgeführt: ' : '',
            100 - $warpsplit,
            $autoCarryOver ? 'aktiviert' : 'deaktiviert'
        ));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
