<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use request;
use Stu\Component\Building\BuildMenuEnum;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildmenuProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private BuildingRepositoryInterface $buildingRepository, private PlanetFieldRepositoryInterface $planetFieldRepository) {}

    #[Override]
    public function setTemplateVariables(
        $entity,
        GameControllerInterface $game
    ): void {
        $fieldType = $this->getFieldType();
        if ($fieldType !== null) {
            $game->addExecuteJS(sprintf('fieldType = %d;', $fieldType), GameEnum::JS_EXECUTION_AJAX_UPDATE);
        } else {
            $game->addExecuteJS('fieldType = null;', GameEnum::JS_EXECUTION_AJAX_UPDATE);
        }

        foreach (BuildMenuEnum::BUILDMENU_IDS as $id) {

            $menus[$id]['name'] = BuildMenuEnum::getDescription($id);
            $menus[$id]['buildings'] = $this->buildingRepository->getBuildmenuBuildings(
                $entity,
                $game->getUser()->getId(),
                $id,
                0,
                request::has('cid') ? request::getIntFatal('cid') : null,
                $fieldType
            );
        }

        $game->setTemplateVar('BUILD_MENUS', $menus);
    }

    private function getFieldType(): ?int
    {
        if (!request::has('fid')) {
            return null;
        }

        $field = $this->planetFieldRepository->find(request::getIntFatal('fid'));
        if ($field === null) {
            return null;
        }

        return $field->getFieldType();
    }
}
