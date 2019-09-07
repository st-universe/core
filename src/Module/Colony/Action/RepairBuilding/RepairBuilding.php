<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RepairBuilding;

use Colfields;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class RepairBuilding implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_REPAIR';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $field = Colfields::getByColonyField(
            (int)request::indInt('fid'),
            $colony->getId()
        );

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
        $eps = round(($field->getBuilding()->getEpsCost() / 100) * $integrity);
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

            if (!array_key_exists($commodityId, $storage)) {
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
            $colony->lowerStorage(
                $cost->getGoodId(),
                (int) round(($cost->getAmount() / 100) * $integrity)
            );
        }
        $colony->resetStorage();

        $colony->lowerEps($eps);
        $colony->save();
        $field->setIntegrity($field->getBuilding()->getIntegrity());
        $field->save();

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
