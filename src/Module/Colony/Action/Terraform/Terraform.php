<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\Terraform;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Exception\SanityCheckException;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\TerraformingInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\TerraformingRepositoryInterface;

final class Terraform implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TERRAFORM';

    private ColonyLoaderInterface $colonyLoader;

    private TerraformingRepositoryInterface $terraformingRepository;

    private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        TerraformingRepositoryInterface $terraformingRepository,
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->terraformingRepository = $terraformingRepository;
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $user->getId()
        );
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $fieldId = request::indInt('fid');

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            $fieldId
        );
        if ($field === null) {
            return;
        }

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
        if ($field->getFieldType() !== $terraf->getFromFieldTypeId()) {
            return;
        }

        //sanity check if user has researched this terraforming
        $terraformingopts = $this->terraformingRepository->getBySourceFieldTypeAndUser(
            $field->getFieldType(),
            $userId
        );
        if (!array_key_exists($terraf->getId(), $terraformingopts)) {
            throw new SanityCheckException('user tried to perform unresearched terraforming', self::ACTION_IDENTIFIER);
        }


        if ($userId !== UserEnum::USER_NOONE && $terraf->getEnergyCosts() > $colony->getEps()) {
            $game->addInformationf(
                _('Es wird %s Energie benötigt - Vorhanden ist nur %s'),
                $terraf->getEnergyCosts(),
                $colony->getEps()
            );
            return;
        }

        $storage = $colony->getStorage();

        $colonyTerraforming = $this->colonyTerraformingRepository->prototype();

        if ($userId !== UserEnum::USER_NOONE) {
            foreach ($terraf->getCosts() as $obj) {
                $commodityId = $obj->getCommodityId();
                if (!$storage->containsKey($commodityId)) {
                    $game->addInformationf(
                        _('Es werden %s %s benötigt - Es ist jedoch keines vorhanden'),
                        $obj->getAmount(),
                        $obj->getCommodity()->getName()
                    );
                    return;
                }
                if ($obj->getAmount() > $storage[$commodityId]->getAmount()) {
                    $game->addInformationf(
                        _('Es werden %s %s benötigt - Vorhanden sind nur %s'),
                        $obj->getAmount(),
                        $obj->getCommodity()->getName(),
                        $storage[$commodityId]->getAmount()
                    );
                    return;
                }
            }
            foreach ($terraf->getCosts() as $obj) {
                $this->colonyStorageManager->lowerStorage($colony, $obj->getCommodity(), $obj->getAmount());
            }
            $colony->lowerEps($terraf->getEnergyCosts());
            $time = time() + $terraf->getDuration();
        } else {
            $time = time() + 1;
        }

        $colonyTerraforming->setColony($colony);
        $colonyTerraforming->setField($field);
        $colonyTerraforming->setTerraforming($terraf);
        $colonyTerraforming->setFinishDate($time);

        $this->colonyTerraformingRepository->save($colonyTerraforming);

        $field->setTerraforming($terraf);

        $this->planetFieldRepository->save($field);

        $this->colonyRepository->save($colony);

        $game->addInformationf(
            _('%s wird durchgeführt - Fertigstellung: %s'),
            $terraf->getDescription(),
            date('d.m.Y H:i', $time)
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
