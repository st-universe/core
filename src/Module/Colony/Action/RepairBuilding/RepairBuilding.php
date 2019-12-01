<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RepairBuilding;

use request;
use Stu\Module\Colony\Lib\ColonyStorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class RepairBuilding implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_REPAIR';

    private ColonyLoaderInterface $colonyLoader;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
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
            (int)request::indInt('fid')
        );

        if ($field === null) {
            return;
        }

        if (!$field->hasBuilding()) {
            return;
        }
        if (!$field->isDamaged()) {
            return;
        }
        if ($field->isInConstruction()) {
            return;
        }
        $integrity = round((100 / $field->getBuilding()->getIntegrity()) * $field->getIntegrity());
        $eps = (int)round(($field->getBuilding()->getEpsCost() / 100) * $integrity);
        if ($eps > $colony->getEps()) {
            $game->addInformationf(
                _('Zur Reparatur wird %d Energie benötigt - Es sind jedoch nur %d vorhanden'),
                $eps,
                $colony->getEps()
            );
            return;
        }

        $storage = $colony->getStorage();
        $costs = $field->getBuilding()->getCosts();

        foreach ($costs as $cost) {
            $amount = round(($cost->getAmount() / 100) * $integrity);
            $commodityId = $cost->getGoodId();

            if (!$storage->containsKey($commodityId)) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Es ist jedoch keines vorhanden'),
                    $amount,
                    $cost->getGood()->getName()
                );
                return;
            }
            if ($amount > $storage[$commodityId]->getAmount()) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Vorhanden sind nur %d'),
                    $amount,
                    $cost->getGood()->getName(),
                    $storage[$commodityId]->getAmount()
                );
                return;
            }
        }
        foreach ($costs as $cost) {
            $this->colonyStorageManager->lowerStorage(
                $colony,
                $cost->getGood(),
                (int) round(($cost->getAmount() / 100) * $integrity)
            );
        }
        $colony->clearCache();

        $colony->lowerEps($eps);

        $this->colonyRepository->save($colony);

        $field->setIntegrity($field->getBuilding()->getIntegrity());

        $this->planetFieldRepository->save($field);

        $game->addInformationf(
            _('%s auf Feld %d wurde repariert'),
            $field->getBuilding()->getName(),
            $field->getFieldId()
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
