<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Ui;

use RuntimeException;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount\SpacecraftCountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

class PanelLayerConfiguration
{
    public function __construct(private UserMapRepositoryInterface $userMapRepository) {}

    public function configureLayers(
        PanelLayerCreationInterface $panelLayerCreation,
        SpacecraftWrapperInterface $wrapper,
        LocationInterface $panelCenter,
        UserInterface $currentUser,
        bool $tachyonFresh,
        bool $isShipOnLevel
    ): void {

        $spacecraft = $wrapper->get();

        if ($wrapper->get()->getSubspaceState()) {
            $panelLayerCreation->addSubspaceLayer($currentUser->getId(), SubspaceLayerTypeEnum::IGNORE_USER);
        }

        $isLssMalfunctioning = $spacecraft->getLocation()->getFieldType()->hasEffect(FieldTypeEffectEnum::LSS_MALFUNCTION);
        if ($isLssMalfunctioning) {
            return;
        }

        $panelLayerCreation
            ->addShipCountLayer($tachyonFresh, $spacecraft, SpacecraftCountLayerTypeEnum::ALL, 0)
            ->addBorderLayer($wrapper->get(), $isShipOnLevel)
            ->addAnomalyLayer();

        if ($panelCenter instanceof MapInterface) {
            $layer = $panelCenter->getLayer();
            if ($layer === null) {
                throw new RuntimeException('this should not happen');
            }
            $panelLayerCreation->addMapLayer($layer);
            $this->createUserMapEntries($wrapper, $layer, $currentUser);
        } else {
            $panelLayerCreation
                ->addSystemLayer()
                ->addColonyShieldLayer();
        }
    }

    private function createUserMapEntries(SpacecraftWrapperInterface $wrapper, LayerInterface $layer, UserInterface $currentUser): void
    {
        $map = $wrapper->get()->getMap();
        if ($map === null) {
            return;
        }

        $cx = $map->getX();
        $cy = $map->getY();
        $range = $wrapper->getSensorRange();

        if ($this->isUserMapActive($layer->getId(), $currentUser)) {
            $this->userMapRepository->insertMapFieldsForUser(
                $currentUser->getId(),
                $layer->getId(),
                $cx,
                $cy,
                $range
            );
        }
    }

    private function isUserMapActive(int $layerId, UserInterface $currentUser): bool
    {
        if (!$currentUser->hasColony()) {
            return false;
        }

        return !$currentUser->hasExplored($layerId);
    }
}
