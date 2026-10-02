<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use request;
use Stu\Component\Building\BuildMenuEnum;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Module\Control\JavascriptExecutionInterface;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildmenuProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(
        private readonly BuildingRepositoryInterface $buildingRepository,
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository,
        private readonly JavascriptExecutionInterface $javascriptExecution
    ) {}

    #[\Override]
    public function setTemplateVariables(
        $entity,
        TemplateInterface $template,
        User $user
    ): void {
        $fieldType = $this->getFieldType();
        if ($fieldType !== null) {
            $this->javascriptExecution->addExecuteJS(sprintf('fieldType = %d;', $fieldType), JavascriptExecutionTypeEnum::ON_AJAX_UPDATE);
        } else {
            $this->javascriptExecution->addExecuteJS('fieldType = null;', JavascriptExecutionTypeEnum::ON_AJAX_UPDATE);
        }

        $menus = [];

        foreach (BuildMenuEnum::cases() as $menu) {

            $id = $menu->value;
            $menus[$id]['name'] = $menu->getDescription();
            $menus[$id]['buildings'] = $this->buildingRepository->getBuildmenuBuildings(
                $entity,
                $user->getId(),
                $menu,
                0,
                request::has('cid') ? request::getIntFatal('cid') : null,
                $fieldType
            );
        }

        $template->setTemplateVar('BUILD_MENUS', $menus);
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
