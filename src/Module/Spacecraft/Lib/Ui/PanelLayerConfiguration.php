<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Ui;

use RuntimeException;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount\SpacecraftCountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Trait\LayerExplorationTrait;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserMapRepositoryInterface;

class PanelLayerConfiguration
{
    use LayerExplorationTrait;

    public function __construct(private UserMapRepositoryInterface $userMapRepository) {}

    public function configureLayers(
        PanelLayerCreationInterface $panelLayerCreation,
        SpacecraftWrapperInterface $wrapper,
        Location $panelCenter,
        User $currentUser,
        bool $tachyonFresh,
        bool $isShipOnLevel
    ): void {

        $spacecraft = $wrapper->get();

        if ($wrapper->get()->getSubspaceState()) {
            $panelLayerCreation->addSubspaceLayer($currentUser->getId(), SubspaceLayerTypeEnum::IGNORE_USER);

            // TODO @hux: hier einfügen, falls ein Target ausgewählt wurde etc.
            //$panelLayerCreation->addSpacecraftSignatureLayer($spacecraftId);
        }

        $isLssMalfunctioning = $spacecraft->getLocation()->getFieldType()->hasEffect(FieldTypeEffectEnum::LSS_MALFUNCTION);
        if ($isLssMalfunctioning) {
            return;
        }

        $panelLayerCreation
            ->addShipCountLayer($tachyonFresh, $spacecraft, SpacecraftCountLayerTypeEnum::ALL, 0)
            ->addBorderLayer($wrapper, $isShipOnLevel)
            ->addAnomalyLayer();

        if ($panelCenter instanceof Map) {
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

    private function createUserMapEntries(SpacecraftWrapperInterface $wrapper, Layer $layer, User $currentUser): void
    {
        $map = $wrapper->get()->getMap();
        if ($map === null) {
            return;
        }

        $cx = $map->getX();
        $cy = $map->getY();
        $range = $wrapper->getLssSystemData()?->getSensorRange() ?? 0;

        if ($this->isUserMapActive($layer, $currentUser)) {
            $this->userMapRepository->insertMapFieldsForUser(
                $currentUser->getId(),
                $layer->getId(),
                $cx,
                $cy,
                $range
            );
        }
    }

    private function isUserMapActive(Layer $layer, User $currentUser): bool
    {
        if (!$currentUser->hasColony()) {
            return false;
        }

        return !$this->hasExplored($currentUser, $layer);
    }
}
