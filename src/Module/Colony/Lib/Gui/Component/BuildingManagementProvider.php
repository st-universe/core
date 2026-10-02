<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingManagementProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private PlanetFieldRepositoryInterface $planetFieldRepository, private CommodityRepositoryInterface $commodityRepository) {}

    #[\Override]
    public function setTemplateVariables(
        $entity,
        TemplateInterface $template,
        User $user
    ): void {
        $list = $this->planetFieldRepository->getByColonyWithBuilding($entity);

        $template->setTemplateVar('PLANET_FIELD_LIST', $list);
        $template->setTemplateVar('USEABLE_COMMODITY_LIST', $this->commodityRepository->getByBuildingsOnColony($entity));
    }
}
