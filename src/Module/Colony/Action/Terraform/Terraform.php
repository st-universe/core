<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\Terraform;

use Colfields;
use FieldTerraforming;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Terraforming;

final class Terraform implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_TERRAFORM';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $user->getId()
        );
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $fieldId = (int)request::indInt('fid');

        $field = Colfields::getByColonyField($fieldId, $colony->getId());

        if ($field->getBuildingId() > 0) {
            return;
        }
        if ($field->getTerraformingId() > 0) {
            return;
        }
        $terraf = new Terraforming(request::getIntFatal('tfid'));
        if ($field->getFieldType() != $terraf->getSource()) {
            return;
        }
        if (!$user->hasResearched($terraf->getResearchId())) {
            return;
        }
        if ($terraf->getLimit() > 0 && $terraf->getLimit() <= Colfields::countInstances('type=' . $terraf->getDestination())) {
            $game->addInformationf(
                _('Dieser Feldtyp ist auf diesem Planetentyp nur %d mal möglich'),
                $terraf->getLimit()
            );
            return;
        }
        if ($terraf->getEpsCost() > $colony->getEps()) {
            $game->addInformationf(
                _('Es wird %s Energie benötigt - Vorhanden ist nur %s'),
                $terraf->getEpsCost(),
                $colony->getEps()
            );
            return;
        }
        $ret = calculateCosts($terraf->getCosts(), $colony->getStorage(), $colony);
        if ($ret) {
            $game->addInformation($ret);
            return;
        }
        $colony->lowerEps($terraf->getEpsCost());
        $time = time() + $terraf->getDuration() + 60;
        FieldTerraforming::addTerraforming($colony->getId(), $field->getId(), $terraf->getId(), $time);
        $field->setTerraformingId($terraf->getId());
        $field->save();
        $colony->save();
        $game->addInformationf(
            _('%s wird durchgeführt - Fertigstellung: %s'),
            $terraf->getDescription(),
            parseDateTime($time)
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
