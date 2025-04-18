<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\Terraform;

use Override;
use request;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Colony\View\ShowInformation\ShowInformation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\TerraformingInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\TerraformingRepositoryInterface;

final class Terraform implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TERRAFORM';

    public function __construct(
        private PlanetFieldHostProviderInterface $planetFieldHostProvider,
        private TerraformingRepositoryInterface $terraformingRepository,
        private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private StorageManagerInterface $storageManager,
        private ColonyRepositoryInterface $colonyRepository,
        private ComponentRegistrationInterface $componentRegistration
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowInformation::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($game->getUser());
        $host = $field->getHost();

        if ($field->getBuildingId() > 0) {
            return;
        }
        if ($field->getTerraformingId() > 0) {
            return;
        }

        $terraforming = $this->terraformingRepository->find(request::postIntFatal('tfid'));
        if ($terraforming === null) {
            return;
        }
        if ($field->getFieldType() !== $terraforming->getFromFieldTypeId()) {
            return;
        }

        //sanity check if user has researched this terraforming
        $terraformingopts = $this->terraformingRepository->getBySourceFieldTypeAndUser(
            $field->getFieldType(),
            $userId,
            $host->getColonyClass()->getId()
        );
        if (!array_key_exists($terraforming->getId(), $terraformingopts)) {
            throw new SanityCheckException('user tried to perform unresearched terraforming', self::ACTION_IDENTIFIER);
        }

        if ($host instanceof ColonyInterface) {
            if (!$this->doColonyCheckAndConsume($terraforming, $field, $host, $game)) {
                return;
            }
        } else {
            $field->setFieldType($terraforming->getToFieldTypeId());

            $game->addInformationf(
                _('%s wurde durchgeführt'),
                $terraforming->getDescription()
            );
        }

        $game->addExecuteJS('refreshHost();');

        $this->componentRegistration
            ->addComponentUpdate(ColonyComponentEnum::SHIELDING, $host)
            ->addComponentUpdate(ColonyComponentEnum::EPS_BAR, $host)
            ->addComponentUpdate(ColonyComponentEnum::STORAGE, $host);

        $this->planetFieldRepository->save($field);
    }

    private function doColonyCheckAndConsume(
        TerraformingInterface $terraforming,
        PlanetFieldInterface $field,
        ColonyInterface $colony,
        GameControllerInterface $game
    ): bool {

        if ($terraforming->getEnergyCosts() > $colony->getEps()) {
            $game->addInformationf(
                _('Es wird %s Energie benötigt - Vorhanden ist nur %s'),
                $terraforming->getEnergyCosts(),
                $colony->getEps()
            );
            return false;
        }

        $storage = $colony->getStorage();

        foreach ($terraforming->getCosts() as $obj) {
            if ($obj->getAmount() < 0) {
                continue;
            }

            $commodityId = $obj->getCommodityId();
            if (!$storage->containsKey($commodityId)) {
                $game->addInformationf(
                    _('Es werden %s %s benötigt - Es ist jedoch keines vorhanden'),
                    $obj->getAmount(),
                    $obj->getCommodity()->getName()
                );
                return false;
            }

            if ($obj->getAmount() > $storage[$commodityId]->getAmount()) {
                $game->addInformationf(
                    _('Es werden %s %s benötigt - Vorhanden sind nur %s'),
                    $obj->getAmount(),
                    $obj->getCommodity()->getName(),
                    $storage[$commodityId]->getAmount()
                );
                return false;
            }
        }
        foreach ($terraforming->getCosts() as $obj) {
            $amount = $obj->getAmount();
            $commodity = $obj->getCommodity();

            if ($amount < 0) {

                $this->storageManager->upperStorage($colony, $commodity, abs($amount));
            } else {

                $this->storageManager->lowerStorage($colony, $commodity, $amount);
            }
        }

        $colony->lowerEps($terraforming->getEnergyCosts());
        $this->colonyRepository->save($colony);
        $time = time() + $terraforming->getDuration();

        $colonyTerraforming = $this->colonyTerraformingRepository->prototype();
        $colonyTerraforming->setColony($colony);
        $colonyTerraforming->setField($field);
        $colonyTerraforming->setTerraforming($terraforming);
        $colonyTerraforming->setFinishDate($time);

        $this->colonyTerraformingRepository->save($colonyTerraforming);

        $field->setTerraforming($terraforming);

        $game->addInformationf(
            _('%s wird durchgeführt - Fertigstellung: %s'),
            $terraforming->getDescription(),
            date('d.m.Y H:i', $time)
        );

        return true;
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}