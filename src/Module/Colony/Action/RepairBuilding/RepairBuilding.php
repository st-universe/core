<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RepairBuilding;

use Colfields;
use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
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

        $fieldId = (int)request::indInt('fid');

        $field = Colfields::getByColonyField($fieldId, $colony->getId());

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

        $cost = $field->getBuilding()->getCosts();
        foreach ($cost as $key => $obj) {
            $obj->setTempCount(round(($obj->getAmount() / 100) * $integrity));
        }
        $ret = calculateCosts($cost, $colony->getStorage(), $colony);
        if ($ret) {
            $game->addInformation($ret);
            return;
        }

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
