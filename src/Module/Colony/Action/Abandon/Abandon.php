<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\Abandon;

use AccessViolation;
use Colony;
use ColonyShipQueue;
use ColStorage;
use FieldTerraforming;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;

final class Abandon implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_GIVEUP_COLONY';

    private $abandonRequest;

    public function __construct(
        AbandonRequestInterface $abandonRequest
    ) {
        $this->abandonRequest = $abandonRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = new Colony($this->abandonRequest->getColonyId());

        if ($colony->getUserId() != $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $colonyId = $colony->getId();

        $colony->updateColonySurface();
        $colony->setEps(0);
        $colony->setMaxEps(0);
        $colony->setMaxStorage(0);
        $colony->setWorkers(0);
        $colony->setWorkless(0);
        $colony->setMaxBev(0);
        $colony->setImmigrationState(1);
        $colony->setPopulationLimit(0);
        $colony->setUserId(USER_NOONE);
        $colony->setName('');
        $colony->save();

        ColStorage::truncate($colonyId);
        FieldTerraforming::truncate($colonyId);
        ColonyShipQueue::truncate(sprintf('colony_id = %d', $colony));

        $game->addInformation(_('Die Kolonie wurde aufgegeben'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
