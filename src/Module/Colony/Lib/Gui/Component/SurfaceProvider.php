<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use request;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;

final class SurfaceProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory) {}

    #[\Override]
    public function setTemplateVariables(
        $entity,
        TemplateInterface $template,
        User $user
    ): void {
        $template->setTemplateVar(
            'SURFACE',
            $this->colonyLibFactory->createColonySurface($entity, request::getInt('buildingid') !== 0 ? request::getInt('buildingid') : null)
        );
    }
}
