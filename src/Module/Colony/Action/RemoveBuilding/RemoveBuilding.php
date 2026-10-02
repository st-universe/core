<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RemoveBuilding;

use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\View\ShowInformation\ShowInformation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;

final class RemoveBuilding implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REMOVE_BUILDING';

    public function __construct(
        private PlanetFieldHostProviderInterface $planetFieldHostProvider,
        private BuildingActionInterface $buildingAction,
        private ComponentRegistrationInterface $componentRegistration
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowInformation::VIEW_IDENTIFIER);

        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($context->getUser());
        $building = $field->getBuilding();

        if ($building === null) {
            return;
        }
        if (!$building->isRemovable()) {
            return;
        }

        $this->buildingAction->remove($field, $context);

        $context->addExecuteJS(sprintf("refreshHost('%s');", $context->getSessionString()));

        $host = $field->getHost();

        $this->componentRegistration
            ->addComponentUpdate(ColonyComponentEnum::SHIELDING, $host)
            ->addComponentUpdate(ColonyComponentEnum::EPS_BAR, $host)
            ->addComponentUpdate(ColonyComponentEnum::STORAGE, $host);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
