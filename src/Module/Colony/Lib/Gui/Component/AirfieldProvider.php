<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class AirfieldProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository) {}

    #[\Override]
    public function setTemplateVariables(
        $entity,
        TemplateInterface $template,
        User $user
    ): void {

        $template->setTemplateVar(
            'STARTABLE_SHIPS',
            $this->spacecraftRumpRepository->getStartableByColony($entity->getId())
        );
        $template->setTemplateVar(
            'BUILDABLE_SHIPS',
            $this->spacecraftRumpRepository->getBuildableByUserAndBuildingFunction(
                $user->getId(),
                BuildingFunctionEnum::AIRFIELD
            )
        );
    }
}
