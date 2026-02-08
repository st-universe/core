<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FakeWarpSignature;

use request;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class FakeWarpSignature implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_FAKE_WARP_SIGNATURE';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private StuTime $stuTime,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private DatabaseEntryRepositoryInterface $databaseEntryRepository,
        private ShipRumpModuleLevelRepositoryInterface $rumpModuleRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $shipId = request::indInt('id');
        $rumpId = request::indInt('rumpid');
        $rump = $this->spacecraftRumpRepository->find($rumpId);


        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        if ($rump === null) {
            throw new SanityCheckException('Rump not found', self::ACTION_IDENTIFIER);
        }
        $databaseId = $rump->getDatabaseId();

        if (!$databaseId) {
            $game->getInfo()->addInformation("Aktion nicht möglich, da der Rumpf keine Datenbankeinträge hat");
            return;
        }

        $database = $this->databaseEntryRepository->find($databaseId);
        if ($database === null) {
            throw new SanityCheckException('Database entry not found', self::ACTION_IDENTIFIER);
        }

        if (!($database->getCategoryId() == DatabaseCategoryTypeEnum::SHIPRUMP->value)) {
            $game->getInfo()->addInformation("Aktion nicht möglich, da der Rumpf kein Schiffsrumpf ist");
            return;
        }

        if (!$this->databaseUserRepository->findFor(
            $databaseId,
            $userId
        )) {
            $game->getInfo()->addInformation("Aktion nicht möglich, da der Rumpf noch nicht entdeckt wurde");
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null) {
            $game->getInfo()->addInformation(_("Kein EPS-System vorhanden"));
            return;
        }

        $ship = $wrapper->get();
        $warpsystem = $wrapper->getWarpDriveSystemData();

        if ($ship->getRumpId() == $rumpId) {
            $game->getInfo()->addInformation("Aktion nicht möglich, da das Schiff bereits diesen Rumpf hat");
            return;
        }

        if ($warpsystem === null) {
            throw new SanityCheckException('warpsystem = null ', self::ACTION_IDENTIFIER);
        }

        if (!$ship->isWarped()) {
            $game->getInfo()->addInformation("Aktion nicht möglich, Schiff befindet sich nicht im Warp");
            return;
        }


        if ($ship->isSystemHealthy(SpacecraftSystemTypeEnum::WARPDRIVE)) {
            $rumpModule = $this->rumpModuleRepository->getByShipRump(
                $rump
            );
            if ($rumpModule === null) {
                $game->getInfo()->addInformation("Aktion nicht möglich, da der Rumpf keine Warp-Signatur hat");
                return;
            }
            $defaultLevel = $rumpModule->getDefaultLevel(SpacecraftModuleTypeEnum::WARPDRIVE);
            $energy = 25 * $defaultLevel;
            if ($epsSystem->getEps() < $energy) {
                $game->getInfo()->addInformationF('Es wird %s Energie zum ändern der Warp-Signatur benötigt', $energy);
                return;
            }
            $epsSystem->lowerEps($energy)->update();
            $warpsystem->setWarpSignature($rumpId)->update();
            $warpsystem->setWarpSignatureTimer($this->stuTime->time())->update();
        }

        $game->getInfo()->addInformationf(
            'Die %s emittiert nun für 5 Minuten eine Warp-Signatur des Rumpfes %s',
            $ship->getName(),
            $rump->getName()
        );
    }


    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
