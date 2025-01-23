<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SplitReactorOutput;

use Override;
use request;
use Stu\Component\Spacecraft\System\Data\WarpDriveSystemData;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowInformation\ShowInformation;

final class SplitReactorOutput implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SPLIT_REACTOR_OUTPUT';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(private SpacecraftLoaderInterface $spacecraftLoader) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
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

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
