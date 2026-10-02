<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class TorpedoFabProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private TorpedoTypeRepositoryInterface $torpedoTypeRepository) {}

    #[\Override]
    public function setTemplateVariables(
        $entity,
        TemplateInterface $template,
        User $user
    ): void {

        $template->setTemplateVar(
            'BUILDABLE_TORPEDO_TYPES',
            $this->torpedoTypeRepository->getForUser($user->getId())
        );
    }
}
