<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RepairBuilding;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class RepairBuilding implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_REPAIR';

    private ColonyLoaderInterface $colonyLoader;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->planetFieldTypeRetriever = $planetFieldTypeRetriever;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            (int) request::indInt('fid')
        );

        if ($field === null) {
            return;
        }

        $building =  $field->getBuilding();
        if ($building === null) {
            return;
        }
        if (!$field->isDamaged()) {
            return;
        }
        if ($field->isUnderConstruction()) {
            return;
        }

        if (
            $this->planetFieldTypeRetriever->isOrbitField($field)
            && $colony->isBlocked()
        ) {
            $game->addInformation(_('Gebäude im Orbit können nicht repariert werden während die Kolonie blockiert wird'));
            return;
        }

        $integrityInPercent = (int) floor($field->getIntegrity() / $building->getIntegrity() * 100);
        $damageInPercent = 100 - $integrityInPercent;

        if ($damageInPercent === 0) {
            return;
        }

        $eps = (int) ceil($building->getEpsCost() * $damageInPercent / 100);
        if ($building->isRemovable() === false && $building->getEpsCost() > $colony->getEps()) {
            $eps = $colony->getEps();
        }
        if ($eps > $colony->getEps()) {
            $game->addInformationf(
                _('Zur Reparatur wird %d Energie benötigt - Es sind jedoch nur %d vorhanden'),
                $eps,
                $colony->getEps()
            );
            return;
        }

        $storages = $colony->getStorage();
        $costs = $building->getCosts();

        foreach ($costs as $cost) {
            $amount = (int) ceil($cost->getAmount() * $damageInPercent / 100);

            $commodityId = $cost->getCommodityId();

            $storage = $storages->get($commodityId);
            if ($storage === null) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Es ist jedoch keines vorhanden'),
                    $amount,
                    $cost->getCommodity()->getName()
                );
                return;
            }
            if ($amount > $storage->getAmount()) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Vorhanden sind nur %d'),
                    $amount,
                    $cost->getCommodity()->getName(),
                    $storage->getAmount()
                );
                return;
            }
        }
        foreach ($costs as $cost) {
            $this->colonyStorageManager->lowerStorage(
                $colony,
                $cost->getCommodity(),
                (int) ceil($cost->getAmount() * $damageInPercent / 100)
            );
        }
        $colony->lowerEps($eps);

        $this->colonyRepository->save($colony);

        $field->setIntegrity($building->getIntegrity());

        $this->planetFieldRepository->save($field);

        $game->addInformationf(
            _('%s auf Feld %d wurde repariert'),
            $building->getName(),
            $field->getFieldId()
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
