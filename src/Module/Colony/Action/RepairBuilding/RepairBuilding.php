<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RepairBuilding;

use request;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class RepairBuilding implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REPAIR';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private PlanetFieldRepositoryInterface $planetFieldRepository, private StorageManagerInterface $storageManager, private PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever, private ColonyRepositoryInterface $colonyRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $context->getUser()->getId()
        );
        $context->setView(ShowColony::VIEW_IDENTIFIER);

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            request::indInt('fid')
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
            $context->getInfo()->addInformation(_('Gebäude im Orbit können nicht repariert werden während die Kolonie blockiert wird'));
            return;
        }

        $integrityInPercent = (int) floor($field->getIntegrity() / $building->getIntegrity() * 100);
        $damageInPercent = 100 - $integrityInPercent;

        if ($damageInPercent === 0) {
            return;
        }

        $changeable = $colony->getChangeable();
        $eps = (int) ceil($building->getEpsCost() * $damageInPercent / 100);

        if ($building->isRemovable() === false && $building->getEpsCost() > $changeable->getEps()) {
            $eps = $changeable->getEps();
        }
        if ($eps > $changeable->getEps()) {
            $context->getInfo()->addInformationf(
                _('Zur Reparatur wird %d Energie benötigt - Es sind jedoch nur %d vorhanden'),
                $eps,
                $changeable->getEps()
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
                $context->getInfo()->addInformationf(
                    _('Es werden %d %s benötigt - Es ist jedoch keines vorhanden'),
                    $amount,
                    $cost->getCommodity()->getName()
                );
                return;
            }
            if ($amount > $storage->getAmount()) {
                $context->getInfo()->addInformationf(
                    _('Es werden %d %s benötigt - Vorhanden sind nur %d'),
                    $amount,
                    $cost->getCommodity()->getName(),
                    $storage->getAmount()
                );
                return;
            }
        }
        foreach ($costs as $cost) {
            $this->storageManager->lowerStorage(
                $colony,
                $cost->getCommodity(),
                (int) ceil($cost->getAmount() * $damageInPercent / 100)
            );
        }
        $changeable->lowerEps($eps);

        $this->colonyRepository->save($colony);

        $field->setIntegrity($building->getIntegrity());

        $this->planetFieldRepository->save($field);

        $context->getInfo()->addInformationf(
            _('%s auf Feld %d wurde repariert'),
            $building->getName(),
            $field->getFieldId()
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
