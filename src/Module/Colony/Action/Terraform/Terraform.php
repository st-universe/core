<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\Terraform;

use Colfields;
use FieldTerraformingData;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Entity\TerraformingInterface;
use Stu\Orm\Repository\TerraformingRepositoryInterface;

final class Terraform implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_TERRAFORM';

    private $colonyLoader;

    private $terraformingRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        TerraformingRepositoryInterface $terraformingRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->terraformingRepository = $terraformingRepository;
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
        /**
         * @var TerraformingInterface $terraf
         */
        $terraf = $this->terraformingRepository->find(request::getIntFatal('tfid'));
        if ($terraf === null) {
            return;
        }
        if ($field->getFieldType() != $terraf->getFromFieldTypeId()) {
            return;
        }
        if ($terraf->getEnergyCosts() > $colony->getEps()) {
            $game->addInformationf(
                _('Es wird %s Energie benötigt - Vorhanden ist nur %s'),
                $terraf->getEnergyCosts(),
                $colony->getEps()
            );
            return;
        }

        $storage = $colony->getStorage();

        foreach ($terraf->getCosts() as $obj) {
            $commodityId = $obj->getGoodId();
            if (!array_key_exists($commodityId, $storage)) {
                $game->addInformationf(
                    _('Es werden %s %s benötigt - Es ist jedoch keines vorhanden'),
                    $obj->getAmount(),
                    $obj->getGood()->getName()
                );
                return;
            }
            if ($obj->getAmount() > $storage[$commodityId]->getAmount()) {
                $game->addInformationf(
                    _('Es werden %s %s benötigt - Vorhanden sind nur %s'),
                    $obj->getAmount(),
                    $obj->getGood()->getName(),
                    $storage[$commodityId]->getAmount()
                );
                return;
            }
        }

        foreach ($terraf->getCosts() as $obj) {
            $colony->lowerStorage($obj->getGoodId(),$obj->getAmount());
        }
        $colony->resetStorage();
        $colony->lowerEps($terraf->getEnergyCosts());
        $time = time() + $terraf->getDuration() + 60;

        $obj = new FieldTerraformingData();
        $obj->setColonyId($colony->getId());
        $obj->setFieldId($field->getId());
        $obj->setTerraformingId($terraf->getId());
        $obj->setFinishDate($time);
        $obj->save();

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
