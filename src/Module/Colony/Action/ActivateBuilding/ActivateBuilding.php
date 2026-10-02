<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ActivateBuilding;

use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;

final class ActivateBuilding implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ACTIVATE';

    public function __construct(private PlanetFieldHostProviderInterface $planetFieldHostProvider, private BuildingActionInterface $buildingAction) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($context->getUser());
        $host = $field->getHost();

        $context->setView($host->getDefaultViewIdentifier());

        if ($field->isUnderConstruction()) {
            $field->setActivateAfterBuild(true);
            $context->getInfo()->addInformation("Gebäude wird nach Bau aktiviert");
        } else {
            $this->buildingAction->activate(
                $field,
                $context
            );
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
