<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Repository\BorgAssimilationRepositoryInterface;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class BorgAssimilationRelease
{
    private BorgAssimilationNanites $nanites;
    private BorgDataNodeReward $dataNodes;

    public function __construct(
        private BorgAssimilationRepositoryInterface $repository,
        CommodityRepositoryInterface $commodities,
        StorageManagerInterface $storage,
        private PrivateMessageSenderInterface $messages,
        StuRandom $random,
        private BorgSphereRepositoryInterface $events
    ) {
        $this->nanites = new BorgAssimilationNanites($commodities, $storage, $random);
        $this->dataNodes = new BorgDataNodeReward($commodities, $storage, $random);
    }

    public function release(SpacecraftAssimilated $entry, int $now, bool $rewardDataNode = true): void
    {
        if (!$entry->isActive()) {
            return;
        }
        $source = $entry->getSource();
        $ship = $entry->getSpacecraft();
        $entry->freeze($now);
        if ($source?->getTractoredShip() === $ship) {
            $source->setTractoredShip(null);
            $source->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        }
        $this->repository->save($entry);
        if ($ship->getCondition()->isDestroyed()) {
            return;
        }
        $amount = $this->nanites->grant($entry, $entry->getProgress());
        $text = sprintf('Die %s wurde aus der Assimilation befreit. Der Assimilationsfortschritt bleibt bei %s%%. %d Borg-Naniten wurden dem Lagerraum hinzugefügt', $ship->getName(), number_format($entry->getProgress() / 100, 2, ',', ''), $amount);
        if ($rewardDataNode && $source !== null) {
            $text .= $this->dataNodes->grant($ship, $this->events->getByShip($source)?->getConfiguration());
        }
        $this->messages->send($source?->getUser()->getId() ?? UserConstants::USER_NOONE, $ship->getUser()->getId(), $text, PrivateMessageFolderTypeEnum::SPECIAL_SHIP, $ship);
    }
}
